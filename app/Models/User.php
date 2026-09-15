<?php

namespace App\Models;

use App\Database\Database;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch("SELECT * FROM `users` WHERE `email` = :email LIMIT 1", [':email' => strtolower($email)]);
    }

    public static function findByReferralCode(string $code): ?array
    {
        return Database::fetch("SELECT * FROM `users` WHERE `referral_code` = :code LIMIT 1", [':code' => $code]);
    }

    public static function create(array $data): int
    {
        $sql = "INSERT INTO `users` (`name`, `email`, `password`, `referral_code`, `referred_by`, `status`, `created_at`) 
                VALUES (:name, :email, :password, :referral_code, :referred_by, :status, NOW())";
        
        Database::execute($sql, [
            ':name' => $data['name'],
            ':email' => strtolower($data['email']),
            ':password' => $data['password'],
            ':referral_code' => $data['referral_code'] ?? bin2hex(random_bytes(4)),
            ':referred_by' => $data['referred_by'] ?? null,
            ':status' => $data['status'] ?? 'active',
        ]);

        $userId = (int) Database::lastInsertId();

        // Automatically initialize wallet
        Database::execute(
            "INSERT INTO `wallets` (`user_id`, `balance`, `spent`, `currency`, `created_at`) VALUES (:user_id, 0.0000, 0.0000, 'USD', NOW())",
            [':user_id' => $userId]
        );

        return $userId;
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

        $sql = "UPDATE `users` SET " . implode(', ', $fields) . ", `updated_at` = NOW() WHERE `id` = :id";
        return Database::execute($sql, $bindings);
    }

    public static function getWallet(int $userId): ?array
    {
        return Database::fetch("SELECT * FROM `wallets` WHERE `user_id` = :user_id LIMIT 1", [':user_id' => $userId]);
    }

    public static function paginate(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (`name` LIKE :search OR `email` LIKE :search2 OR `referral_code` LIKE :search3)";
            $params[':search'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
            $params[':search3'] = "%{$search}%";
        }

        $countRow = Database::fetch("SELECT COUNT(*) as total FROM `users` WHERE {$where}", $params);
        $total = (int) ($countRow['total'] ?? 0);

        $sql = "SELECT u.*, w.balance, w.spent, w.currency 
                FROM `users` u 
                LEFT JOIN `wallets` w ON u.id = w.user_id 
                WHERE {$where} 
                ORDER BY u.id DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $users = Database::query($sql, $params);

        return [
            'data' => $users,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }
}
