<?php

namespace App\Models;

use App\Database\Database;

abstract class Model
{
    protected static string $table = '';

    public static function find(int|string $id): ?array
    {
        $table = static::$table;
        return Database::fetch("SELECT * FROM `{$table}` WHERE `id` = :id LIMIT 1", [':id' => $id]);
    }

    public static function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $table = static::$table;
        $dir = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        return Database::query("SELECT * FROM `{$table}` ORDER BY `{$orderBy}` {$dir}");
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        $table = static::$table;
        $row = Database::fetch("SELECT COUNT(*) as cnt FROM `{$table}` WHERE {$where}", $params);
        return (int) ($row['cnt'] ?? 0);
    }
}
