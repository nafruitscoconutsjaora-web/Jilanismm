<?php

namespace App\Services;

use App\Database\Database;

class CurrencyService
{
    private static ?array $currencyCache = null;

    /**
     * Get the default panel currency
     */
    public static function getDefaultCurrency(): array
    {
        $curr = Database::fetch("SELECT * FROM `currencies` WHERE `is_default` = 1 AND `status` = 'active' LIMIT 1");
        if ($curr) {
            return $curr;
        }

        // Fallback to USD
        return [
            'id' => 1,
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'rate' => '1.000000',
            'is_default' => 1,
            'status' => 'active'
        ];
    }

    /**
     * Get all currencies
     */
    public static function getAllCurrencies(bool $activeOnly = false): array
    {
        $where = $activeOnly ? "WHERE `status` = 'active'" : "";
        return Database::query("SELECT * FROM `currencies` {$where} ORDER BY `is_default` DESC, `code` ASC");
    }

    /**
     * Find currency by code
     */
    public static function findByCode(string $code): ?array
    {
        $code = strtoupper(trim($code));
        return Database::fetch("SELECT * FROM `currencies` WHERE `code` = :code LIMIT 1", [':code' => $code]);
    }

    /**
     * Get exchange rate from one currency to another using exact decimal logic
     */
    public static function getExchangeRate(string $fromCurrency, string $toCurrency): string
    {
        $from = strtoupper(trim($fromCurrency));
        $to = strtoupper(trim($toCurrency));

        if ($from === $to) {
            return '1.000000';
        }

        // 1. Check explicit exchange_rates table pair
        $pair = Database::fetch(
            "SELECT `rate` FROM `exchange_rates` WHERE `from_currency` = :from AND `to_currency` = :to LIMIT 1",
            [':from' => $from, ':to' => $to]
        );
        if ($pair && !empty($pair['rate']) && (float)$pair['rate'] > 0) {
            return (string)$pair['rate'];
        }

        // 2. Check inverse pair
        $inversePair = Database::fetch(
            "SELECT `rate` FROM `exchange_rates` WHERE `from_currency` = :from AND `to_currency` = :to LIMIT 1",
            [':from' => $to, ':to' => $from]
        );
        if ($inversePair && !empty($inversePair['rate']) && (float)$inversePair['rate'] > 0) {
            if (function_exists('bcdiv')) {
                return bcdiv('1', (string)$inversePair['rate'], 6);
            }
            return number_format(1 / (float)$inversePair['rate'], 6, '.', '');
        }

        // 3. Check rates relative to default currency (USD) from currencies table
        $fromObj = self::findByCode($from);
        $toObj = self::findByCode($to);

        $fromRate = $fromObj ? (string)$fromObj['rate'] : '1.000000';
        $toRate = $toObj ? (string)$toObj['rate'] : '1.000000';

        if ((float)$fromRate <= 0) $fromRate = '1.000000';
        if ((float)$toRate <= 0) $toRate = '1.000000';

        // Rate = toRate / fromRate
        if (function_exists('bcdiv')) {
            return bcdiv($toRate, $fromRate, 6);
        }
        return number_format((float)$toRate / (float)$fromRate, 6, '.', '');
    }

    /**
     * Convert an amount from one currency to another
     */
    public static function convert(string|float $amount, string $fromCurrency, string $toCurrency): string
    {
        $rate = self::getExchangeRate($fromCurrency, $toCurrency);
        $strAmount = number_format((float)$amount, 6, '.', '');

        if (function_exists('bcmul')) {
            return bcmul($strAmount, $rate, 4);
        }
        return number_format((float)$strAmount * (float)$rate, 4, '.', '');
    }

    /**
     * Format money string with currency symbol
     */
    public static function format(string|float $amount, string $currencyCode = 'USD', int $decimals = 2): string
    {
        $curr = self::findByCode($currencyCode);
        $symbol = $curr ? $curr['symbol'] : '$';
        $num = number_format((float)$amount, $decimals, '.', ',');
        return $symbol . $num;
    }

    /**
     * Update exchange rate
     */
    public static function updateCurrency(int $id, string $code, string $name, string $symbol, string $rate, string $status, bool $isDefault = false): bool
    {
        if ($isDefault) {
            Database::execute("UPDATE `currencies` SET `is_default` = 0");
            $rate = '1.000000';
        }

        return Database::execute(
            "UPDATE `currencies` SET `code` = :code, `name` = :name, `symbol` = :symbol, `rate` = :rate, `status` = :status, `is_default` = :is_default WHERE `id` = :id",
            [
                ':id' => $id,
                ':code' => strtoupper(trim($code)),
                ':name' => trim($name),
                ':symbol' => trim($symbol),
                ':rate' => number_format((float)$rate, 6, '.', ''),
                ':status' => $status === 'inactive' ? 'inactive' : 'active',
                ':is_default' => $isDefault ? 1 : 0
            ]
        );
    }

    /**
     * Add new currency
     */
    public static function createCurrency(string $code, string $name, string $symbol, string $rate, string $status = 'active', bool $isDefault = false): bool
    {
        if ($isDefault) {
            Database::execute("UPDATE `currencies` SET `is_default` = 0");
            $rate = '1.000000';
        }

        return Database::execute(
            "INSERT INTO `currencies` (`code`, `name`, `symbol`, `rate`, `is_default`, `status`) VALUES (:code, :name, :symbol, :rate, :is_default, :status)",
            [
                ':code' => strtoupper(trim($code)),
                ':name' => trim($name),
                ':symbol' => trim($symbol),
                ':rate' => number_format((float)$rate, 6, '.', ''),
                ':is_default' => $isDefault ? 1 : 0,
                ':status' => $status === 'inactive' ? 'inactive' : 'active'
            ]
        );
    }
}
