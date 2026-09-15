<?php

namespace App\Models;

use App\Database\Database;

class Setting extends Model
{
    protected static string $table = 'settings';

    public static function getByKey(string $key): ?string
    {
        $row = Database::fetch("SELECT `setting_value` FROM `settings` WHERE `setting_key` = :key LIMIT 1", [':key' => $key]);
        return $row ? $row['setting_value'] : null;
    }

    public static function getAll(): array
    {
        $rows = Database::query("SELECT `setting_key`, `setting_value` FROM `settings`");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public static function set(string $key, ?string $value, string $group = 'general'): bool
    {
        $sql = "INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) 
                VALUES (:key, :value, :group) 
                ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `setting_group` = VALUES(`setting_group`), `updated_at` = NOW()";
        return Database::execute($sql, [
            ':key' => $key,
            ':value' => $value,
            ':group' => $group,
        ]);
    }
}
