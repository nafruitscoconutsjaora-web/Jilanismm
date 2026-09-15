<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class PaymentGatewayFactory
{
    public static function make(string $code): PaymentGatewayInterface
    {
        $code = strtolower(trim($code));

        return match ($code) {
            'stripe' => new StripeGateway(),
            'paypal' => new PayPalGateway(),
            'manual_bank', 'bank_transfer', 'manual' => new ManualBankGateway(),
            default => new ManualBankGateway()
        };
    }
}
