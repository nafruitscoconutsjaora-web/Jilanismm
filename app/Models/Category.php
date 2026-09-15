<?php

namespace App\Models;

use App\Database\Database;

class Category extends Model
{
    protected static string $table = 'categories';

    public static function allActiveWithCount(): array
    {
        return Database::query(
            "SELECT c.*, COUNT(s.id) as services_count 
             FROM `categories` c 
             LEFT JOIN `services` s ON c.id = s.category_id AND s.status = 'active'
             WHERE c.status = 'active' 
             GROUP BY c.id 
             ORDER BY c.sort_order ASC, c.name ASC"
        );
    }

    public static function allWithCount(): array
    {
        return Database::query(
            "SELECT c.*, COUNT(s.id) as services_count 
             FROM `categories` c 
             LEFT JOIN `services` s ON c.id = s.category_id 
             GROUP BY c.id 
             ORDER BY c.sort_order ASC, c.id DESC"
        );
    }
}
