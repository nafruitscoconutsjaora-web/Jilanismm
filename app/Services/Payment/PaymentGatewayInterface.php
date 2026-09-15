<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create payment session or prepare gateway instructions
     * Returns: ['success' => bool, 'redirect_url' => ?string, 'action_type' => string, 'details' => array, 'error' => ?string]
     */
    public function createPayment(array $payment, array $gatewayConfig): array;

    /**
     * Verify payment upon user return or callback
     * Returns: ['success' => bool, 'is_completed' => bool, 'gateway_transaction_id' => ?string, 'error' => ?string]
     */
    public function verifyCallback(array $payment, array $requestData, array $gatewayConfig): array;

    /**
     * Verify and process asynchronous webhook
     * Returns: ['success' => bool, 'payment_transaction_id' => ?string, 'is_completed' => bool, 'error' => ?string]
     */
    public function verifyWebhook(string $rawPayload, array $headers, array $gatewayConfig): array;
}
