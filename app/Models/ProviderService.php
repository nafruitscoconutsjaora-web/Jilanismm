<?php

namespace App\Models;

use App\Database\Database;

class ProviderService extends Model
{
    protected static string $table = 'provider_services';

    public static function forProvider(int $providerId): array
    {
        return Database::query(
            "SELECT ps.*, s.id as mapped_service_id, s.name as mapped_service_name, s.customer_price as mapped_customer_price 
             FROM `provider_services` ps 
             LEFT JOIN `services` s ON ps.provider_id = s.provider_id AND ps.remote_service_id = s.provider_service_id 
             WHERE ps.provider_id = :pid 
             ORDER BY ps.category ASC, ps.name ASC",
            [':pid' => $providerId]
        );
    }
}
