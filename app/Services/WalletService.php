<?php

namespace App\Services;

use App\Database\Database;
use RuntimeException;

class WalletService
{
    /**
     * Get or initialize user's wallet
     */
    public static function getWallet(int $userId): array
    {
        $wallet = Database::fetch("SELECT * FROM `wallets` WHERE `user_id` = :uid LIMIT 1", [':uid' => $userId]);
        if (!$wallet) {
            $curr = CurrencyService::getDefaultCurrency();
            Database::execute(
                "INSERT INTO `wallets` (`user_id`, `balance`, `spent`, `currency`) VALUES (:uid, 0.0000, 0.0000, :curr)",
                [':uid' => $userId, ':curr' => $curr['code'] ?? 'USD']
            );
            $wallet = Database::fetch("SELECT * FROM `wallets` WHERE `user_id` = :uid LIMIT 1", [':uid' => $userId]);
        }
        return $wallet;
    }

    /**
     * Concurrency-safe atomic deduction for an order.
     * Note: Expects caller to manage or participate in a Database transaction.
     * Uses SELECT ... FOR UPDATE on user's wallet.
     *
     * @return array ['success' => bool, 'error' => ?string, 'balance_before' => float, 'balance_after' => float]
     */
    public static function deductForOrder(int $userId, string|float $amount, string $orderReference, string $description): array
    {
        $amt = (float)$amount;
        if ($amt <= 0) {
            return ['success' => false, 'error' => 'Invalid deduction amount.', 'balance_before' => 0, 'balance_after' => 0];
        }

        // Lock wallet row for update
        $wallet = Database::fetch("SELECT * FROM `wallets` WHERE `user_id` = :uid FOR UPDATE", [':uid' => $userId]);
        if (!$wallet) {
            return ['success' => false, 'error' => 'User wallet not found.', 'balance_before' => 0, 'balance_after' => 0];
        }

        $balanceBefore = (float)$wallet['balance'];
        if ($balanceBefore < $amt) {
            return [
                'success' => false,
                'error' => 'Insufficient funds in wallet. Please add funds to proceed.',
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore
            ];
        }

        $balanceAfter = $balanceBefore - $amt;
        $spentAfter = (float)$wallet['spent'] + $amt;

        // Deduct balance and update spent
        Database::execute(
            "UPDATE `wallets` SET `balance` = :bal, `spent` = :spent, `updated_at` = NOW() WHERE `id` = :id",
            [':bal' => $balanceAfter, ':spent' => $spentAfter, ':id' => $wallet['id']]
        );

        // Record wallet transaction
        Database::execute(
            "INSERT INTO `wallet_transactions` (`wallet_id`, `user_id`, `type`, `amount`, `balance_before`, `balance_after`, `reference_id`, `description`, `created_at`)
             VALUES (:wid, :uid, 'order', :amt, :bb, :ba, :ref, :desc, NOW())",
            [
                ':wid' => $wallet['id'],
                ':uid' => $userId,
                ':amt' => $amt,
                ':bb' => $balanceBefore,
                ':ba' => $balanceAfter,
                ':ref' => $orderReference,
                ':desc' => $description
            ]
        );

        return [
            'success' => true,
            'error' => null,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter
        ];
    }

    /**
     * Concurrency-safe, idempotent refund for an order.
     * Guaranteed: never allows one order to be refunded multiple times or exceed original charge.
     */
    public static function refundOrder(int $userId, int $orderId, ?float $refundAmount = null, string $reason = 'Order cancelled / provider rejected', ?string $targetStatus = null): array
    {
        $hasOwnTx = false;
        if (!Database::inTransaction()) {
            Database::beginTransaction();
            $hasOwnTx = true;
        }

        try {
            // Lock order row
            $order = Database::fetch("SELECT * FROM `orders` WHERE `id` = :id AND `user_id` = :uid FOR UPDATE", [':id' => $orderId, ':uid' => $userId]);
            if (!$order) {
                if ($hasOwnTx) Database::rollBack();
                return ['success' => false, 'error' => 'Order not found.'];
            }

            $charge = (float)$order['charge'];
            $alreadyRefunded = (float)($order['refunded_amount'] ?? 0);
            $maxRefundable = max(0.0, $charge - $alreadyRefunded);

            if ($maxRefundable <= 0.0001) {
                if ($hasOwnTx) Database::rollBack();
                return ['success' => false, 'error' => 'This order has already been fully refunded.'];
            }

            $amountToRefund = ($refundAmount !== null && $refundAmount > 0) ? min($refundAmount, $maxRefundable) : $maxRefundable;

            // Lock wallet row
            $wallet = Database::fetch("SELECT * FROM `wallets` WHERE `user_id` = :uid FOR UPDATE", [':uid' => $userId]);
            if (!$wallet) {
                if ($hasOwnTx) Database::rollBack();
                return ['success' => false, 'error' => 'Wallet not found.'];
            }

            $balanceBefore = (float)$wallet['balance'];
            $balanceAfter = $balanceBefore + $amountToRefund;
            $spentAfter = max(0.0, (float)$wallet['spent'] - $amountToRefund);

            // Credit wallet
            Database::execute(
                "UPDATE `wallets` SET `balance` = :bal, `spent` = :spent, `updated_at` = NOW() WHERE `id` = :id",
                [':bal' => $balanceAfter, ':spent' => $spentAfter, ':id' => $wallet['id']]
            );

            // Update order record
            $newRefundedTotal = $alreadyRefunded + $amountToRefund;
            $newStatus = $targetStatus ?: (($newRefundedTotal >= $charge - 0.0001) ? 'refunded' : 'partial');

            Database::execute(
                "UPDATE `orders` SET `refunded_amount` = :ref_amt, `status` = :st, `updated_at` = NOW() WHERE `id` = :id",
                [':ref_amt' => $newRefundedTotal, ':st' => $newStatus, ':id' => $orderId]
            );

            // Insert wallet transaction
            Database::execute(
                "INSERT INTO `wallet_transactions` (`wallet_id`, `user_id`, `type`, `amount`, `balance_before`, `balance_after`, `reference_id`, `description`, `created_at`)
                 VALUES (:wid, :uid, 'refund', :amt, :bb, :ba, :ref, :desc, NOW())",
                [
                    ':wid' => $wallet['id'],
                    ':uid' => $userId,
                    ':amt' => $amountToRefund,
                    ':bb' => $balanceBefore,
                    ':ba' => $balanceAfter,
                    ':ref' => 'ORDER-' . $orderId,
                    ':desc' => "Refund for Order #{$orderId}: {$reason}"
                ]
            );

            // Record status history
            Database::execute(
                "INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `changed_by_type`, `source`, `note`, `reason`, `created_at`)
                 VALUES (:oid, :old_st, :new_st, 'system', 'refund_engine', :note, :reason, NOW())",
                [
                    ':oid' => $orderId,
                    ':old_st' => $order['status'],
                    ':new_st' => $newStatus,
                    ':note' => "Refunded " . number_format($amountToRefund, 4) . " USD to user wallet",
                    ':reason' => $reason
                ]
            );

            if ($hasOwnTx) {
                Database::commit();
            }

            return [
                'success' => true,
                'refunded_amount' => $amountToRefund,
                'new_status' => $newStatus,
                'balance_after' => $balanceAfter
            ];
        } catch (\Throwable $e) {
            if ($hasOwnTx) Database::rollBack();
            return ['success' => false, 'error' => 'Refund error: ' . $e->getMessage()];
        }
    }

    /**
     * Idempotent credit to wallet (e.g. for verified payment deposit)
     */
    public static function credit(int $userId, string|float $amount, string $type, string $referenceId, string $description): array
    {
        $amt = (float)$amount;
        if ($amt <= 0) {
            return ['success' => false, 'error' => 'Amount must be greater than zero.'];
        }

        $hasOwnTx = false;
        if (!Database::inTransaction()) {
            Database::beginTransaction();
            $hasOwnTx = true;
        }

        try {
            // Idempotency check: verify if transaction with this referenceId already exists
            if (!empty($referenceId)) {
                $existingTx = Database::fetch(
                    "SELECT `id` FROM `wallet_transactions` WHERE `user_id` = :uid AND `type` = :type AND `reference_id` = :ref LIMIT 1",
                    [':uid' => $userId, ':type' => $type, ':ref' => $referenceId]
                );
                if ($existingTx) {
                    if ($hasOwnTx) Database::commit();
                    return ['success' => true, 'already_processed' => true, 'message' => 'Transaction already recorded.'];
                }
            }

            $wallet = Database::fetch("SELECT * FROM `wallets` WHERE `user_id` = :uid FOR UPDATE", [':uid' => $userId]);
            if (!$wallet) {
                self::getWallet($userId);
                $wallet = Database::fetch("SELECT * FROM `wallets` WHERE `user_id` = :uid FOR UPDATE", [':uid' => $userId]);
            }

            $balanceBefore = (float)$wallet['balance'];
            $balanceAfter = $balanceBefore + $amt;

            Database::execute(
                "UPDATE `wallets` SET `balance` = :bal, `updated_at` = NOW() WHERE `id` = :id",
                [':bal' => $balanceAfter, ':id' => $wallet['id']]
            );

            Database::execute(
                "INSERT INTO `wallet_transactions` (`wallet_id`, `user_id`, `type`, `amount`, `balance_before`, `balance_after`, `reference_id`, `description`, `created_at`)
                 VALUES (:wid, :uid, :type, :amt, :bb, :ba, :ref, :desc, NOW())",
                [
                    ':wid' => $wallet['id'],
                    ':uid' => $userId,
                    ':type' => $type,
                    ':amt' => $amt,
                    ':bb' => $balanceBefore,
                    ':ba' => $balanceAfter,
                    ':ref' => $referenceId,
                    ':desc' => $description
                ]
            );

            if ($hasOwnTx) {
                Database::commit();
            }

            return [
                'success' => true,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter
            ];
        } catch (\Throwable $e) {
            if ($hasOwnTx) Database::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Admin manual balance adjustment (add or deduct)
     */
    public static function adminAdjust(int $userId, string $action, string|float $amount, string $reason, int $adminId): array
    {
        $amt = (float)$amount;
        if ($amt <= 0) {
            return ['success' => false, 'error' => 'Amount must be greater than zero.'];
        }

        Database::beginTransaction();
        try {
            $wallet = Database::fetch("SELECT * FROM `wallets` WHERE `user_id` = :uid FOR UPDATE", [':uid' => $userId]);
            if (!$wallet) {
                self::getWallet($userId);
                $wallet = Database::fetch("SELECT * FROM `wallets` WHERE `user_id` = :uid FOR UPDATE", [':uid' => $userId]);
            }

            $balanceBefore = (float)$wallet['balance'];

            if ($action === 'deduct') {
                if ($balanceBefore < $amt) {
                    Database::rollBack();
                    return ['success' => false, 'error' => 'User does not have sufficient balance for this deduction.'];
                }
                $balanceAfter = $balanceBefore - $amt;
                $txType = 'manual_deduct';
            } else {
                $balanceAfter = $balanceBefore + $amt;
                $txType = 'manual_add';
            }

            Database::execute(
                "UPDATE `wallets` SET `balance` = :bal, `updated_at` = NOW() WHERE `id` = :id",
                [':bal' => $balanceAfter, ':id' => $wallet['id']]
            );

            Database::execute(
                "INSERT INTO `wallet_transactions` (`wallet_id`, `user_id`, `type`, `amount`, `balance_before`, `balance_after`, `reference_id`, `description`, `created_at`)
                 VALUES (:wid, :uid, :type, :amt, :bb, :ba, :ref, :desc, NOW())",
                [
                    ':wid' => $wallet['id'],
                    ':uid' => $userId,
                    ':type' => $txType,
                    ':amt' => $amt,
                    ':bb' => $balanceBefore,
                    ':ba' => $balanceAfter,
                    ':ref' => 'ADMIN-' . $adminId,
                    ':desc' => "Admin Adjustment: " . $reason
                ]
            );

            Database::commit();
            return ['success' => true, 'balance_after' => $balanceAfter];
        } catch (\Throwable $e) {
            Database::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get paginated transactions for user
     */
    public static function getUserTransactions(int $userId, int $page = 1, int $perPage = 15): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $total = Database::fetch(
            "SELECT COUNT(*) as cnt FROM `wallet_transactions` WHERE `user_id` = :uid",
            [':uid' => $userId]
        )['cnt'] ?? 0;

        $items = Database::query(
            "SELECT * FROM `wallet_transactions` WHERE `user_id` = :uid ORDER BY `id` DESC LIMIT {$perPage} OFFSET {$offset}",
            [':uid' => $userId]
        );

        return [
            'data' => $items,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }
}
