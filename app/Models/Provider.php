<?php

namespace App\Models;

use App\Database\Database;

class Provider extends Model
{
    protected static string $table = 'providers';

    public static function allWithServiceCount(): array
    {
        return Database::query(
            "SELECT p.*, COUNT(ps.id) as provider_services_count, COUNT(s.id) as mapped_services_count 
             FROM `providers` p 
             LEFT JOIN `provider_services` ps ON p.id = ps.provider_id 
             LEFT JOIN `services` s ON p.id = s.provider_id 
             GROUP BY p.id 
             ORDER BY p.id DESC"
        );
    }
}
