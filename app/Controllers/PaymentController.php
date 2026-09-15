<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\AuthService;
use App\Services\PaymentService;
use App\Services\Payment\PaymentGatewayFactory;

class PaymentController
{
    public function addFunds(Request $request): void
    {
        $user = AuthService::user();
        $wallet = User::getWallet($user['id']);
        $gateways = PaymentGateway::allActive();
        $recentPayments = Payment::forUser($user['id'], 10);

        View::render('user/wallet/add_funds', [
            'title' => 'Add Funds - ' . config('app.name'),
            'user' => $user,
            'wallet' => $wallet,
            'gateways' => $gateways,
            'recentPayments' => $recentPayments
        ], 'user');
    }

    public function createPayment(Request $request): void
    {
        $user = AuthService::user();
        $gatewayId = (int)$request->input('gateway_id', 0);
        $amount = (float)$request->input('amount', 0);

        if ($gatewayId <= 0) {
            Session::setFlash('error', 'Please select a valid payment gateway.');
            Response::redirect('/wallet/add-funds');
            return;
        }

        if ($amount <= 0) {
            Session::setFlash('error', 'Please enter a valid deposit amount.');
            Response::redirect('/wallet/add-funds');
            return;
        }

        $res = PaymentService::initiatePayment($user['id'], $gatewayId, $amount);

        if (!$res['success']) {
            Session::setFlash('error', $res['error'] ?? 'Payment initiation failed.');
            Response::redirect('/wallet/add-funds');
            return;
        }

        if (!empty($res['redirect_url'])) {
            Response::redirect($res['redirect_url']);
            return;
        }

        Response::redirect('/payment/checkout/' . urlencode($res['payment']['transaction_id']));
    }

    public function checkout(Request $request, array $params): void
    {
        $user = AuthService::user();
        $txn = trim($params['txn'] ?? '');

        $payment = Database::fetch(
            "SELECT p.*, g.name as gateway_name, g.instructions, g.code as gateway_code 
             FROM `payments` p 
             JOIN `payment_gateways` g ON p.gateway_id = g.id 
             WHERE p.transaction_id = :txn AND p.user_id = :uid LIMIT 1",
            [':txn' => $txn, ':uid' => $user['id']]
        );

        if (!$payment) {
            Session::setFlash('error', 'Payment invoice not found.');
            Response::redirect('/wallet');
            return;
        }

        View::render('user/wallet/checkout', [
            'title' => "Payment Invoice {$payment['transaction_id']} - " . config('app.name'),
            'user' => $user,
            'payment' => $payment
        ], 'user');
    }

    public function callback(Request $request, array $params): void
    {
        $gatewayCode = strtolower(trim($params['gateway'] ?? ''));
        $txn = trim($request->input('txn', ''));

        $payment = Database::fetch(
            "SELECT p.*, g.code, g.name as gateway_name, g.credentials 
             FROM `payments` p 
             JOIN `payment_gateways` g ON p.gateway_id = g.id 
             WHERE p.transaction_id = :txn LIMIT 1",
            [':txn' => $txn]
        );

        if (!$payment) {
            Session::setFlash('error', 'Payment record not found.');
            Response::redirect('/wallet');
            return;
        }

        try {
            $adapter = PaymentGatewayFactory::make($payment['code']);
            $gatewayConfig = Database::fetch("SELECT * FROM `payment_gateways` WHERE `code` = :code LIMIT 1", [':code' => $payment['code']]);
            $verification = $adapter->verifyCallback($payment, $request->all(), $gatewayConfig ?: []);

            if ($verification['success'] && $verification['is_completed']) {
                $completed = PaymentService::completePayment(
                    $payment['transaction_id'],
                    $verification['gateway_transaction_id'] ?? null,
                    'Verified via gateway callback'
                );

                if ($completed['success']) {
                    Session::setFlash('success', "Deposit of \${$payment['amount']} was successfully verified and credited to your wallet!");
                } else {
                    Session::setFlash('warning', 'Payment received. Wallet update status: ' . ($completed['error'] ?? 'Pending'));
                }
            } else {
                Session::setFlash('error', $verification['error'] ?? 'Payment verification was not completed by provider.');
            }
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Verification error: ' . $e->getMessage());
        }

        Response::redirect('/wallet');
    }

    public function webhook(Request $request, array $params): void
    {
        $gatewayCode = strtolower(trim($params['gateway'] ?? ''));
        $gateway = Database::fetch("SELECT * FROM `payment_gateways` WHERE `code` = :code LIMIT 1", [':code' => $gatewayCode]);

        if (!$gateway) {
            Response::json(['error' => 'Gateway not found'], 404);
            return;
        }

        $rawBody = file_get_contents('php://input') ?: '';
        $headers = getallheaders() ?: [];

        try {
            $adapter = PaymentGatewayFactory::make($gatewayCode);
            $result = $adapter->verifyWebhook($rawBody, $headers, $gateway);

            if ($result['success'] && $result['is_completed'] && !empty($result['payment_transaction_id'])) {
                PaymentService::completePayment(
                    $result['payment_transaction_id'],
                    $result['gateway_transaction_id'] ?? null,
                    'Verified via secure webhook'
                );
            }

            Response::json(['status' => 'success', 'result' => $result]);
        } catch (\Throwable $e) {
            Response::json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
