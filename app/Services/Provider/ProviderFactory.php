<?php

namespace App\Services\Provider;

use App\Database\Database;
use InvalidArgumentException;

class ProviderFactory
{
    /**
     * Resolve provider adapter for a provider record or ID
     */
    public static function make(array|int|string $provider): ProviderInterface
    {
        $data = is_array($provider) ? $provider : Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $provider]);

        if (!$data) {
            throw new InvalidArgumentException("Provider not found.");
        }

        // Standard SMM API adapter (supported by 99% of SMM providers)
        return new StandardSmmProvider($data);
    }
}
