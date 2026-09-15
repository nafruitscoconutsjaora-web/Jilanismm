<?php

namespace App\Services;

class PricingService
{
    /**
     * Compute comprehensive separated pricing attributes from provider cost to customer selling price
     *
     * @param string|float $providerPrice The original provider price per 1000
     * @param string $providerCurrency The currency the provider charges in (e.g. USD)
     * @param string $panelCurrency The panel's target currency (e.g. USD, EUR, INR)
     * @param string $markupType 'percentage' or 'fixed'
     * @param string|float $markupValue The markup percentage (e.g. 25 for 25%) or fixed amount (e.g. 1.50)
     * @param string $rounding '2_decimals', '4_decimals', 'ceil', 'floor'
     * @return array
     */
    public static function calculateCustomerPrice(
        string|float $providerPrice,
        string $providerCurrency = 'USD',
        string $panelCurrency = 'USD',
        string $markupType = 'percentage',
        string|float $markupValue = '0',
        string $rounding = '4_decimals'
    ): array {
        $pPrice = (float)$providerPrice;
        $exchangeRate = (float)CurrencyService::getExchangeRate($providerCurrency, $panelCurrency);
        if ($exchangeRate <= 0) {
            $exchangeRate = 1.0;
        }

        // 1. Converted Provider Cost = provider price * exchange rate
        $convertedCost = $pPrice * $exchangeRate;

        // 2. Apply Markup
        $mVal = (float)$markupValue;
        if ($markupType === 'fixed') {
            $customerRaw = $convertedCost + $mVal;
        } else {
            // Percentage markup: e.g. cost = 100, markup = 20% -> 120
            $markupAmount = $convertedCost * ($mVal / 100.0);
            $customerRaw = $convertedCost + $markupAmount;
        }

        if ($customerRaw < 0) {
            $customerRaw = 0;
        }

        // 3. Rounding
        $finalPrice = match ($rounding) {
            '2_decimals' => round($customerRaw, 2),
            'whole' => ceil($customerRaw),
            '3_decimals' => round($customerRaw, 3),
            default => round($customerRaw, 4)
        };

        return [
            'provider_original_price' => number_format($pPrice, 4, '.', ''),
            'provider_currency' => strtoupper($providerCurrency),
            'exchange_rate' => number_format($exchangeRate, 6, '.', ''),
            'converted_provider_cost' => number_format($convertedCost, 4, '.', ''),
            'markup_type' => $markupType === 'fixed' ? 'fixed' : 'percentage',
            'markup_value' => number_format($mVal, 4, '.', ''),
            'customer_price' => number_format($finalPrice, 4, '.', ''),
            'panel_currency' => strtoupper($panelCurrency)
        ];
    }

    /**
     * Compute total charge for an order based on quantity and rate per 1000
     * Never trusts browser values. Calculated strictly with high precision.
     *
     * @param int $quantity
     * @param string|float $customerPricePerK Price per 1,000 units
     * @return string Exact decimal string with 4 decimal places
     */
    public static function calculateOrderCharge(int $quantity, string|float $customerPricePerK): string
    {
        $price = (float)$customerPricePerK;
        if ($quantity <= 0 || $price <= 0) {
            return '0.0000';
        }

        if (function_exists('bcmul') && function_exists('bcdiv')) {
            $raw = bcmul((string)$quantity, number_format($price, 6, '.', ''), 6);
            return bcdiv($raw, '1000', 4);
        }

        $charge = ($quantity * $price) / 1000.0;
        return number_format($charge, 4, '.', '');
    }
}
