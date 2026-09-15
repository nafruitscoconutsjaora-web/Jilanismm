<?php

namespace App\Services\Payment;

class ManualBankGateway implements PaymentGatewayInterface
{
    public function createPayment(array $payment, array $gatewayConfig): array
    {
        $instructions = $gatewayConfig['instructions'] ?? 'Please wire funds to our designated bank account.';
        $appUrl = rtrim($_SERVER['APP_URL'] ?? 'http://localhost:3000', '/');

        return [
            'success' => true,
            'redirect_url' => $appUrl . '/payment/checkout/' . urlencode($payment['transaction_id']),
            'action_type' => 'manual',
            'details' => [
                'instructions' => $instructions,
                'reference' => $payment['transaction_id'],
                'amount' => $payment['net_amount'],
                'currency' => $payment['currency']
            ],
            'error' => null
        ];
    }

    public function verifyCallback(array $payment, array $requestData, array $gatewayConfig): array
    {
        // Manual payments require administrative review
        return [
            'success' => true,
            'is_completed' => false,
            'gateway_transaction_id' => $requestData['bank_ref'] ?? null,
            'error' => 'Manual wire deposits are subject to staff verification.'
        ];
    }

    public function verifyWebhook(string $rawPayload, array $headers, array $gatewayConfig): array
    {
        return [
            'success' => false,
            'error' => 'Webhooks not supported for manual bank transfers.'
        ];
    }
}
