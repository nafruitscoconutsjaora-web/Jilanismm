<?php

namespace App\Services\Payment;

class PayPalGateway implements PaymentGatewayInterface
{
    public function createPayment(array $payment, array $gatewayConfig): array
    {
        $creds = json_decode($gatewayConfig['credentials'] ?? '{}', true) ?: [];
        $clientId = trim($creds['client_id'] ?? '');
        $secret = trim($creds['client_secret'] ?? '');
        $mode = trim($creds['mode'] ?? 'sandbox');

        if (empty($clientId) || empty($secret)) {
            return [
                'success' => false,
                'redirect_url' => null,
                'action_type' => 'error',
                'details' => [],
                'error' => 'PayPal Client ID and Secret are not configured in Admin > Payment Gateways.'
            ];
        }

        $baseUrl = ($mode === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        // 1. Get OAuth Access Token
        $ch = curl_init($baseUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_USERPWD => $clientId . ':' . $secret,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $tokenRes = curl_exec($ch);
        $tokenCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $tokenData = json_decode($tokenRes, true);
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            return [
                'success' => false,
                'redirect_url' => null,
                'action_type' => 'error',
                'details' => [],
                'error' => 'PayPal OAuth authentication failed: ' . ($tokenData['error_description'] ?? 'Invalid credentials')
            ];
        }

        // 2. Create Order
        $appUrl = rtrim($_SERVER['APP_URL'] ?? 'http://localhost:3000', '/');
        $returnUrl = $appUrl . '/payment/callback/paypal?txn=' . urlencode($payment['transaction_id']);
        $cancelUrl = $appUrl . '/wallet/add-funds?cancelled=1';

        $orderPayload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $payment['transaction_id'],
                    'amount' => [
                        'currency_code' => strtoupper($payment['currency'] ?? 'USD'),
                        'value' => number_format((float)$payment['net_amount'], 2, '.', '')
                    ],
                    'description' => 'Wallet Deposit #' . $payment['transaction_id']
                ]
            ],
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'brand_name' => 'SMM Panel',
                'user_action' => 'PAY_NOW'
            ]
        ];

        $ch = curl_init($baseUrl . '/v2/checkout/orders');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($orderPayload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $orderRes = curl_exec($ch);
        $orderCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $orderData = json_decode($orderRes, true);
        $approveUrl = null;

        if (isset($orderData['links'])) {
            foreach ($orderData['links'] as $link) {
                if (($link['rel'] ?? '') === 'approve') {
                    $approveUrl = $link['href'];
                    break;
                }
            }
        }

        if (!$approveUrl) {
            return [
                'success' => false,
                'redirect_url' => null,
                'action_type' => 'error',
                'details' => [],
                'error' => 'PayPal order creation failed: ' . ($orderData['message'] ?? 'Unknown error')
            ];
        }

        return [
            'success' => true,
            'redirect_url' => $approveUrl,
            'action_type' => 'redirect',
            'details' => ['paypal_order_id' => $orderData['id'] ?? null],
            'error' => null
        ];
    }

    public function verifyCallback(array $payment, array $requestData, array $gatewayConfig): array
    {
        $paypalToken = $requestData['token'] ?? '';
        if (empty($paypalToken)) {
            return [
                'success' => false,
                'is_completed' => false,
                'gateway_transaction_id' => null,
                'error' => 'Missing PayPal token in return query.'
            ];
        }

        $creds = json_decode($gatewayConfig['credentials'] ?? '{}', true) ?: [];
        $clientId = trim($creds['client_id'] ?? '');
        $secret = trim($creds['client_secret'] ?? '');
        $mode = trim($creds['mode'] ?? 'sandbox');
        $baseUrl = ($mode === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        // 1. Get OAuth Access Token
        $ch = curl_init($baseUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_USERPWD => $clientId . ':' . $secret,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20
        ]);
        $tokenRes = curl_exec($ch);
        curl_close($ch);
        $accessToken = json_decode($tokenRes, true)['access_token'] ?? null;

        if (!$accessToken) {
            return ['success' => false, 'is_completed' => false, 'gateway_transaction_id' => null, 'error' => 'Authentication failed.'];
        }

        // 2. Capture payment
        $ch = curl_init($baseUrl . '/v2/checkout/orders/' . urlencode($paypalToken) . '/capture');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20
        ]);
        $captureRes = curl_exec($ch);
        curl_close($ch);

        $captureData = json_decode($captureRes, true);
        $status = $captureData['status'] ?? '';

        $isCompleted = ($status === 'COMPLETED');
        $captureId = $captureData['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalToken;

        return [
            'success' => true,
            'is_completed' => $isCompleted,
            'gateway_transaction_id' => $captureId,
            'error' => $isCompleted ? null : 'PayPal status: ' . $status
        ];
    }

    public function verifyWebhook(string $rawPayload, array $headers, array $gatewayConfig): array
    {
        $event = json_decode($rawPayload, true);
        if (!$event) {
            return ['success' => false, 'error' => 'Invalid webhook JSON.'];
        }

        $eventType = $event['event_type'] ?? '';
        if ($eventType === 'CHECKOUT.ORDER.APPROVED' || $eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            $resource = $event['resource'] ?? [];
            $customId = $resource['custom_id'] ?? ($resource['purchase_units'][0]['reference_id'] ?? null);

            return [
                'success' => true,
                'payment_transaction_id' => $customId,
                'is_completed' => true,
                'gateway_transaction_id' => $resource['id'] ?? null,
                'error' => null
            ];
        }

        return [
            'success' => true,
            'payment_transaction_id' => null,
            'is_completed' => false,
            'error' => 'Ignored event: ' . $eventType
        ];
    }
}
