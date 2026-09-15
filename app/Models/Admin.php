<?php

namespace App\Models;

use App\Database\Database;

class Admin extends Model
{
    protected static string $table = 'admins';

    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT a.*, r.name as role_name, r.display_name as role_display_name, r.permissions 
                FROM `admins` a 
                JOIN `roles` r ON a.role_id = r.id 
                WHERE a.email = :email LIMIT 1";
        return Database::fetch($sql, [':email' => strtolower($email)]);
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $bindings = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = :{$key}";
            $bindings[":{$key}"] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE `admins` SET " . implode(', ', $fields) . ", `updated_at` = NOW() WHERE `id` = :id";
        return Database::execute($sql, $bindings);
    }
}
