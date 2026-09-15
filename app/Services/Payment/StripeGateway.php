<?php

namespace App\Services\Payment;

class StripeGateway implements PaymentGatewayInterface
{
    public function createPayment(array $payment, array $gatewayConfig): array
    {
        $creds = json_decode($gatewayConfig['credentials'] ?? '{}', true) ?: [];
        $secretKey = trim($creds['secret_key'] ?? '');

        if (empty($secretKey)) {
            return [
                'success' => false,
                'redirect_url' => null,
                'action_type' => 'error',
                'details' => [],
                'error' => 'Stripe Secret Key is not configured in Admin > Payment Gateways.'
            ];
        }

        $appUrl = rtrim($_SERVER['APP_URL'] ?? 'http://localhost:3000', '/');
        $successUrl = $appUrl . '/payment/callback/stripe?txn=' . urlencode($payment['transaction_id']) . '&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $appUrl . '/wallet/add-funds?cancelled=1';

        $unitAmount = (int)round((float)$payment['net_amount'] * 100);

        // Make real Stripe API call
        $postFields = [
            'payment_method_types[]' => 'card',
            'mode' => 'payment',
            'client_reference_id' => $payment['transaction_id'],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items[0][price_data][currency]' => strtolower($payment['currency'] ?? 'usd'),
            'line_items[0][price_data][product_data][name]' => 'Wallet Funds Deposit (Ref: ' . $payment['transaction_id'] . ')',
            'line_items[0][price_data][unit_amount]' => $unitAmount,
            'line_items[0][quantity]' => 1,
            'metadata[payment_id]' => $payment['id'],
            'metadata[user_id]' => $payment['user_id']
        ];

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postFields),
            CURLOPT_USERPWD => $secretKey . ':',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err || $httpCode >= 400) {
            $decoded = json_decode($res, true);
            $msg = $decoded['error']['message'] ?? ($err ?: 'Stripe API returned HTTP ' . $httpCode);
            return [
                'success' => false,
                'redirect_url' => null,
                'action_type' => 'error',
                'details' => [],
                'error' => $msg
            ];
        }

        $session = json_decode($res, true);
        $checkoutUrl = $session['url'] ?? null;

        if (!$checkoutUrl) {
            return [
                'success' => false,
                'redirect_url' => null,
                'action_type' => 'error',
                'details' => [],
                'error' => 'Unable to generate Stripe checkout session.'
            ];
        }

        return [
            'success' => true,
            'redirect_url' => $checkoutUrl,
            'action_type' => 'redirect',
            'details' => ['session_id' => $session['id'] ?? null],
            'error' => null
        ];
    }

    public function verifyCallback(array $payment, array $requestData, array $gatewayConfig): array
    {
        $creds = json_decode($gatewayConfig['credentials'] ?? '{}', true) ?: [];
        $secretKey = trim($creds['secret_key'] ?? '');
        $sessionId = trim($requestData['session_id'] ?? '');

        if (empty($secretKey) || empty($sessionId)) {
            return [
                'success' => false,
                'is_completed' => false,
                'gateway_transaction_id' => null,
                'error' => 'Missing session parameter or gateway secret key.'
            ];
        }

        // Server-side verification with Stripe API
        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . urlencode($sessionId));
        curl_setopt_array($ch, [
            CURLOPT_USERPWD => $secretKey . ':',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'is_completed' => false,
                'gateway_transaction_id' => null,
                'error' => 'Failed to verify session with Stripe.'
            ];
        }

        $session = json_decode($res, true);
        $paymentStatus = $session['payment_status'] ?? '';
        $clientRef = $session['client_reference_id'] ?? '';

        if ($clientRef !== $payment['transaction_id']) {
            return [
                'success' => false,
                'is_completed' => false,
                'gateway_transaction_id' => null,
                'error' => 'Payment reference mismatch.'
            ];
        }

        $isCompleted = ($paymentStatus === 'paid');

        return [
            'success' => true,
            'is_completed' => $isCompleted,
            'gateway_transaction_id' => $session['payment_intent'] ?? $sessionId,
            'error' => $isCompleted ? null : 'Payment has not been marked as paid by Stripe.'
        ];
    }

    public function verifyWebhook(string $rawPayload, array $headers, array $gatewayConfig): array
    {
        $creds = json_decode($gatewayConfig['credentials'] ?? '{}', true) ?: [];
        $webhookSecret = trim($creds['webhook_secret'] ?? '');

        if (empty($webhookSecret)) {
            return ['success' => false, 'error' => 'Stripe webhook secret not configured.'];
        }

        $sigHeader = $headers['HTTP_STRIPE_SIGNATURE'] ?? ($headers['Stripe-Signature'] ?? '');
        if (empty($sigHeader)) {
            return ['success' => false, 'error' => 'Missing Stripe-Signature header.'];
        }

        // Parse signature header: t=...,v1=...
        $items = explode(',', $sigHeader);
        $sigMap = [];
        foreach ($items as $item) {
            $parts = explode('=', trim($item), 2);
            if (count($parts) === 2) {
                $sigMap[$parts[0]] = $parts[1];
            }
        }

        $timestamp = $sigMap['t'] ?? '';
        $expectedSignature = $sigMap['v1'] ?? '';

        if (empty($timestamp) || empty($expectedSignature)) {
            return ['success' => false, 'error' => 'Malformed Stripe-Signature header.'];
        }

        // Recompute HMAC sha256: hash_hmac('sha256', $timestamp . '.' . $rawPayload, $webhookSecret)
        $signedPayload = $timestamp . '.' . $rawPayload;
        $calculatedSig = hash_hmac('sha256', $signedPayload, $webhookSecret);

        if (!hash_equals($calculatedSig, $expectedSignature)) {
            return ['success' => false, 'error' => 'Stripe webhook signature verification failed.'];
        }

        $event = json_decode($rawPayload, true);
        $eventType = $event['type'] ?? '';

        if ($eventType === 'checkout.session.completed') {
            $session = $event['data']['object'] ?? [];
            $txnId = $session['client_reference_id'] ?? ($session['metadata']['payment_id'] ?? null);

            return [
                'success' => true,
                'payment_transaction_id' => $txnId,
                'is_completed' => ($session['payment_status'] === 'paid'),
                'gateway_transaction_id' => $session['payment_intent'] ?? null,
                'error' => null
            ];
        }

        return [
            'success' => true,
            'payment_transaction_id' => null,
            'is_completed' => false,
            'error' => 'Unhandled Stripe event type: ' . $eventType
        ];
    }
}
