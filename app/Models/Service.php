<?php

namespace App\Models;

use App\Database\Database;

class Service extends Model
{
    protected static string $table = 'services';

    public static function allActiveWithCategory(): array
    {
        return Database::query(
            "SELECT s.*, c.name as category_name, c.icon as category_icon, p.name as provider_name 
             FROM `services` s 
             JOIN `categories` c ON s.category_id = c.id 
             LEFT JOIN `providers` p ON s.provider_id = p.id 
             WHERE s.status = 'active' AND c.status = 'active' 
             ORDER BY c.sort_order ASC, s.sort_order ASC, s.id ASC"
        );
    }

    public static function allAdminWithRelations(): array
    {
        return Database::query(
            "SELECT s.*, c.name as category_name, p.name as provider_name 
             FROM `services` s 
             JOIN `categories` c ON s.category_id = c.id 
             LEFT JOIN `providers` p ON s.provider_id = p.id 
             ORDER BY s.id DESC"
        );
    }
}
