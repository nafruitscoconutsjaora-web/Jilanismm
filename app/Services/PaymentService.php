<?php

namespace App\Services;

use App\Database\Database;
use App\Services\Payment\PaymentGatewayFactory;

class PaymentService
{
    /**
     * Initiate a new pending payment with full server-side validation and fee calculation
     */
    public static function initiatePayment(int $userId, int $gatewayId, float $amount): array
    {
        $gateway = Database::fetch("SELECT * FROM `payment_gateways` WHERE `id` = :id LIMIT 1", [':id' => $gatewayId]);
        if (!$gateway || $gateway['status'] !== 'active') {
            return ['success' => false, 'error' => 'Selected payment gateway is unavailable.'];
        }

        $min = (float)$gateway['min_amount'];
        $max = (float)$gateway['max_amount'];

        if ($amount < $min) {
            return ['success' => false, 'error' => "Minimum deposit for {$gateway['name']} is $" . number_format($min, 2) . "."];
        }

        if ($amount > $max) {
            return ['success' => false, 'error' => "Maximum deposit for {$gateway['name']} is $" . number_format($max, 2) . "."];
        }

        // Calculate fee
        $feePct = (float)$gateway['fee_percentage'];
        $feeFixed = (float)$gateway['fee_fixed'];
        $calculatedFee = ($amount * ($feePct / 100.0)) + $feeFixed;
        $netAmount = $amount + $calculatedFee;

        // Unique transaction reference
        $transactionId = 'PAY-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();

        Database::execute(
            "INSERT INTO `payments` (
                `user_id`, `gateway_id`, `transaction_id`, `amount`, `fee`, `net_amount`,
                `currency`, `status`, `created_at`
             ) VALUES (
                :uid, :gid, :txn, :amt, :fee, :net,
                :curr, 'pending', NOW()
             )",
            [
                ':uid' => $userId,
                ':gid' => $gateway['id'],
                ':txn' => $transactionId,
                ':amt' => $amount,
                ':fee' => $calculatedFee,
                ':net' => $netAmount,
                ':curr' => $gateway['currency'] ?? 'USD'
            ]
        );

        $paymentId = (int)Database::lastInsertId();
        $payment = Database::fetch("SELECT * FROM `payments` WHERE `id` = :id LIMIT 1", [':id' => $paymentId]);

        // Request gateway initiation
        $adapter = PaymentGatewayFactory::make($gateway['code']);
        $result = $adapter->createPayment($payment, $gateway);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Gateway initiation failed.',
                'payment' => $payment
            ];
        }

        return [
            'success' => true,
            'payment' => $payment,
            'redirect_url' => $result['redirect_url'] ?? null,
            'action_type' => $result['action_type'] ?? 'redirect',
            'details' => $result['details'] ?? []
        ];
    }

    /**
     * Strictly idempotent payment fulfillment.
     * Guaranteed: never credits wallet twice even if webhooks or user callbacks fire simultaneously.
     */
    public static function completePayment(string $transactionId, ?string $gatewayTxnId = null, ?string $gatewayResponse = null): array
    {
        Database::beginTransaction();
        try {
            $payment = Database::fetch(
                "SELECT p.*, g.name as gateway_name 
                 FROM `payments` p 
                 JOIN `payment_gateways` g ON p.gateway_id = g.id 
                 WHERE p.transaction_id = :txn FOR UPDATE",
                [':txn' => $transactionId]
            );

            if (!$payment) {
                Database::rollBack();
                return ['success' => false, 'error' => 'Payment transaction not found.'];
            }

            if ($payment['status'] === 'completed') {
                Database::commit();
                return ['success' => true, 'already_completed' => true, 'payment' => $payment];
            }

            // Update status to completed
            Database::execute(
                "UPDATE `payments` 
                 SET `status` = 'completed', `gateway_response` = :resp, `updated_at` = NOW() 
                 WHERE `id` = :id",
                [
                    ':id' => $payment['id'],
                    ':resp' => $gatewayResponse ?: ($gatewayTxnId ? "Gateway Txn: {$gatewayTxnId}" : 'Verified')
                ]
            );

            // Credit wallet (with internal duplicate prevention)
            $creditRes = WalletService::credit(
                (int)$payment['user_id'],
                (float)$payment['amount'],
                'deposit',
                $payment['transaction_id'],
                "Deposit via {$payment['gateway_name']} (Ref: {$payment['transaction_id']})"
            );

            if (!$creditRes['success']) {
                Database::rollBack();
                return ['success' => false, 'error' => $creditRes['error'] ?? 'Wallet credit failed.'];
            }

            // Send in-app notification
            NotificationService::send(
                (int)$payment['user_id'],
                'Deposit Successful',
                'Your deposit of $' . number_format($payment['amount'], 2) . ' via ' . $payment['gateway_name'] . ' was credited to your wallet.',
                'success'
            );

            Database::commit();

            // Process referral bonus if user was invited by an affiliate
            try {
                ReferralService::processDepositCommission((int)$payment['user_id'], (float)$payment['amount'], $payment['transaction_id']);
            } catch (\Throwable $refErr) {
                error_log("Referral commission error: " . $refErr->getMessage());
            }

            return ['success' => true, 'payment' => $payment];
        } catch (\Throwable $e) {
            Database::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Admin manually approves a pending bank/wire transfer
     */
    public static function adminApproveManualPayment(int $paymentId, int $adminId): array
    {
        $payment = Database::fetch("SELECT * FROM `payments` WHERE `id` = :id LIMIT 1", [':id' => $paymentId]);
        if (!$payment) {
            return ['success' => false, 'error' => 'Payment record not found.'];
        }

        if ($payment['status'] === 'completed') {
            return ['success' => false, 'error' => 'Payment is already marked as completed.'];
        }

        return self::completePayment($payment['transaction_id'], "Manual approval by Admin #{$adminId}", "Manually verified by admin ID: {$adminId}");
    }

    /**
     * Admin rejects or cancels a payment
     */
    public static function adminRejectPayment(int $paymentId, int $adminId, string $reason): array
    {
        $payment = Database::fetch("SELECT * FROM `payments` WHERE `id` = :id LIMIT 1", [':id' => $paymentId]);
        if (!$payment) {
            return ['success' => false, 'error' => 'Payment record not found.'];
        }

        if ($payment['status'] === 'completed') {
            return ['success' => false, 'error' => 'Cannot reject an already completed payment.'];
        }

        Database::execute(
            "UPDATE `payments` SET `status` = 'failed', `gateway_response` = :resp, `updated_at` = NOW() WHERE `id` = :id",
            [':id' => $paymentId, ':resp' => "Rejected by admin #{$adminId}: " . $reason]
        );

        NotificationService::send(
            (int)$payment['user_id'],
            'Deposit Rejected',
            "Your deposit payment #{$payment['transaction_id']} was rejected: {$reason}",
            'warning'
        );

        return ['success' => true];
    }
}
