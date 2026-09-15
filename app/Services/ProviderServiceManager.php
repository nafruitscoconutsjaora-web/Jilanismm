<?php

namespace App\Services;

use App\Database\Database;
use App\Services\Provider\ProviderFactory;

class ProviderServiceManager
{
    /**
     * Import or synchronize services from a provider API
     */
    public static function syncServicesFromProvider(int $providerId): array
    {
        $provider = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $providerId]);
        if (!$provider) {
            return ['success' => false, 'error' => 'Provider not found.', 'imported' => 0, 'updated' => 0];
        }

        $adapter = ProviderFactory::make($provider);
        $res = $adapter->getServices();

        if (!$res['success']) {
            return [
                'success' => false,
                'error' => $res['error'] ?? 'Failed to retrieve services from provider API.',
                'imported' => 0,
                'updated' => 0
            ];
        }

        $services = $res['services'];
        $imported = 0;
        $updated = 0;

        foreach ($services as $svc) {
            $remoteId = (string)($svc['service'] ?? $svc['id'] ?? '');
            if (empty($remoteId)) {
                continue;
            }

            $name = trim($svc['name'] ?? 'Untitled Service');
            $rate = (float)($svc['rate'] ?? 0);
            $min = (int)($svc['min'] ?? 10);
            $max = (int)($svc['max'] ?? 10000);
            $category = trim($svc['category'] ?? 'Default');
            $rawData = json_encode($svc, JSON_UNESCAPED_SLASHES);

            $existing = Database::fetch(
                "SELECT `id` FROM `provider_services` WHERE `provider_id` = :pid AND `remote_service_id` = :rid LIMIT 1",
                [':pid' => $providerId, ':rid' => $remoteId]
            );

            if ($existing) {
                Database::execute(
                    "UPDATE `provider_services` 
                     SET `name` = :name, `rate` = :rate, `min` = :min, `max` = :max, `category` = :cat, `raw_data` = :raw, `synced_at` = NOW() 
                     WHERE `id` = :id",
                    [
                        ':id' => $existing['id'],
                        ':name' => $name,
                        ':rate' => $rate,
                        ':min' => $min,
                        ':max' => $max,
                        ':cat' => $category,
                        ':raw' => $rawData
                    ]
                );
                $updated++;
            } else {
                Database::execute(
                    "INSERT INTO `provider_services` (`provider_id`, `remote_service_id`, `name`, `rate`, `min`, `max`, `category`, `raw_data`, `synced_at`) 
                     VALUES (:pid, :rid, :name, :rate, :min, :max, :cat, :raw, NOW())",
                    [
                        ':pid' => $providerId,
                        ':rid' => $remoteId,
                        ':name' => $name,
                        ':rate' => $rate,
                        ':min' => $min,
                        ':max' => $max,
                        ':cat' => $category,
                        ':raw' => $rawData
                    ]
                );
                $imported++;
            }
        }

        return [
            'success' => true,
            'error' => null,
            'total' => count($services),
            'imported' => $imported,
            'updated' => $updated
        ];
    }

    /**
     * Map a provider service to a customer panel service with strict cost vs price separation
     */
    public static function mapToPanelService(array $data): array
    {
        $providerServiceId = (int)($data['provider_service_id'] ?? 0);
        $provSvc = Database::fetch("SELECT * FROM `provider_services` WHERE `id` = :id LIMIT 1", [':id' => $providerServiceId]);
        if (!$provSvc) {
            return ['success' => false, 'error' => 'Provider service not found.'];
        }

        $provider = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $provSvc['provider_id']]);
        if (!$provider) {
            return ['success' => false, 'error' => 'Associated provider not found.'];
        }

        $categoryId = (int)($data['category_id'] ?? 0);
        $category = Database::fetch("SELECT `id` FROM `categories` WHERE `id` = :id LIMIT 1", [':id' => $categoryId]);
        if (!$category) {
            return ['success' => false, 'error' => 'Valid category must be selected.'];
        }

        $name = trim($data['name'] ?? $provSvc['name']);
        $serviceType = trim($data['service_type'] ?? 'default');
        $minQty = (int)($data['min_quantity'] ?? $provSvc['min']);
        $maxQty = (int)($data['max_quantity'] ?? $provSvc['max']);
        $description = trim($data['description'] ?? '');
        $status = ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        // Pricing logic
        $providerCurrency = $provider['currency'] ?? 'USD';
        $defaultCurrency = CurrencyService::getDefaultCurrency();
        $panelCurrency = $defaultCurrency['code'] ?? 'USD';

        $markupType = ($data['markup_type'] ?? 'percentage') === 'fixed' ? 'fixed' : 'percentage';
        $markupValue = (float)($data['markup_value'] ?? 0);

        // Calculate pricing
        $calc = PricingService::calculateCustomerPrice(
            $provSvc['rate'],
            $providerCurrency,
            $panelCurrency,
            $markupType,
            $markupValue
        );

        // If manual customer price override is specified and > 0, use it
        $finalCustomerPrice = (float)($data['customer_price'] ?? 0);
        if ($finalCustomerPrice <= 0) {
            $finalCustomerPrice = (float)$calc['customer_price'];
        }

        // Check if an existing mapped service exists
        $existingServiceId = (int)($data['service_id'] ?? 0);

        if ($existingServiceId > 0) {
            Database::execute(
                "UPDATE `services` SET 
                    `category_id` = :cat_id,
                    `name` = :name,
                    `service_type` = :stype,
                    `provider_id` = :pid,
                    `provider_service_id` = :psid,
                    `provider_original_price` = :pop,
                    `provider_currency` = :pcurr,
                    `exchange_rate` = :erate,
                    `converted_provider_cost` = :cpc,
                    `markup_type` = :mtype,
                    `markup_value` = :mval,
                    `customer_price` = :cprice,
                    `price_per_k` = :price_per_k,
                    `panel_currency` = :pancurr,
                    `min_quantity` = :min_q,
                    `max_quantity` = :max_q,
                    `description` = :desc,
                    `status` = :status,
                    `updated_at` = NOW()
                 WHERE `id` = :id",
                [
                    ':id' => $existingServiceId,
                    ':cat_id' => $categoryId,
                    ':name' => $name,
                    ':stype' => $serviceType,
                    ':pid' => $provider['id'],
                    ':psid' => $provSvc['remote_service_id'],
                    ':pop' => $calc['provider_original_price'],
                    ':pcurr' => $calc['provider_currency'],
                    ':erate' => $calc['exchange_rate'],
                    ':cpc' => $calc['converted_provider_cost'],
                    ':mtype' => $calc['markup_type'],
                    ':mval' => $calc['markup_value'],
                    ':cprice' => $finalCustomerPrice,
                    ':price_per_k' => $finalCustomerPrice,
                    ':pancurr' => $calc['panel_currency'],
                    ':min_q' => $minQty,
                    ':max_q' => $maxQty,
                    ':desc' => $description,
                    ':status' => $status
                ]
            );
            $serviceId = $existingServiceId;
        } else {
            Database::execute(
                "INSERT INTO `services` (
                    `category_id`, `name`, `service_type`, `provider_id`, `provider_service_id`,
                    `provider_original_price`, `provider_currency`, `exchange_rate`, `converted_provider_cost`,
                    `markup_type`, `markup_value`, `customer_price`, `price_per_k`, `panel_currency`,
                    `min_quantity`, `max_quantity`, `description`, `status`, `created_at`
                 ) VALUES (
                    :cat_id, :name, :stype, :pid, :psid,
                    :pop, :pcurr, :erate, :cpc,
                    :mtype, :mval, :cprice, :price_per_k, :pancurr,
                    :min_q, :max_q, :desc, :status, NOW()
                 )",
                [
                    ':cat_id' => $categoryId,
                    ':name' => $name,
                    ':stype' => $serviceType,
                    ':pid' => $provider['id'],
                    ':psid' => $provSvc['remote_service_id'],
                    ':pop' => $calc['provider_original_price'],
                    ':pcurr' => $calc['provider_currency'],
                    ':erate' => $calc['exchange_rate'],
                    ':cpc' => $calc['converted_provider_cost'],
                    ':mtype' => $calc['markup_type'],
                    ':mval' => $calc['markup_value'],
                    ':cprice' => $finalCustomerPrice,
                    ':price_per_k' => $finalCustomerPrice,
                    ':pancurr' => $calc['panel_currency'],
                    ':min_q' => $minQty,
                    ':max_q' => $maxQty,
                    ':desc' => $description,
                    ':status' => $status
                ]
            );
            $serviceId = (int)Database::lastInsertId();
        }

        return [
            'success' => true,
            'service_id' => $serviceId,
            'pricing' => $calc
        ];
    }
}
