<?php

namespace App\Models;

use App\Database\Database;

class Order extends Model
{
    protected static string $table = 'orders';

    public static function countByStatus(?int $userId = null): array
    {
        $where = $userId !== null ? "WHERE `user_id` = :uid" : "";
        $params = $userId !== null ? [':uid' => $userId] : [];

        $rows = Database::query(
            "SELECT `status`, COUNT(*) as cnt FROM `orders` {$where} GROUP BY `status`",
            $params
        );

        $counts = [
            'total' => 0,
            'pending' => 0,
            'processing' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'partial' => 0,
            'cancelled' => 0,
            'failed' => 0,
            'refunded' => 0
        ];

        foreach ($rows as $r) {
            $st = $r['status'];
            if ($st === 'canceled') $st = 'cancelled';
            $c = (int)$r['cnt'];
            $counts[$st] = ($counts[$st] ?? 0) + $c;
            $counts['total'] += $c;
        }

        return $counts;
    }
}
