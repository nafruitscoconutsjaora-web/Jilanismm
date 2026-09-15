<?php

namespace App\Models;

use App\Database\Database;

class Payment extends Model
{
    protected static string $table = 'payments';

    public static function forUser(int $userId, int $limit = 20): array
    {
        return Database::query(
            "SELECT p.*, g.name as gateway_name 
             FROM `payments` p 
             JOIN `payment_gateways` g ON p.gateway_id = g.id 
             WHERE p.user_id = :uid 
             ORDER BY p.id DESC LIMIT {$limit}",
            [':uid' => $userId]
        );
    }
}
