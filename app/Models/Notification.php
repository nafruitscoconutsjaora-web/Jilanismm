<?php

namespace App\Models;

use App\Database\Database;

class Notification extends Model
{
    protected static string $table = 'notifications';

    public static function forUser(int $userId, int $limit = 20): array
    {
        $sql = "SELECT * FROM `notifications` 
                WHERE `user_id` = :user_id OR (`user_id` IS NULL AND `admin_id` IS NULL)
                ORDER BY `id` DESC LIMIT {$limit}";
        return Database::query($sql, [':user_id' => $userId]);
    }

    public static function unreadCountForUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) as cnt FROM `notifications` 
                WHERE (`user_id` = :user_id OR (`user_id` IS NULL AND `admin_id` IS NULL)) 
                AND `is_read` = 0";
        $row = Database::fetch($sql, [':user_id' => $userId]);
        return (int) ($row['cnt'] ?? 0);
    }

    public static function markAsRead(int $id, int $userId): bool
    {
        $sql = "UPDATE `notifications` SET `is_read` = 1, `read_at` = NOW() 
                WHERE `id` = :id AND (`user_id` = :user_id OR `user_id` IS NULL)";
        return Database::execute($sql, [':id' => $id, ':user_id' => $userId]);
    }

    public static function markAllAsReadForUser(int $userId): bool
    {
        $sql = "UPDATE `notifications` SET `is_read` = 1, `read_at` = NOW() 
                WHERE (`user_id` = :user_id OR `user_id` IS NULL) AND `is_read` = 0";
        return Database::execute($sql, [':user_id' => $userId]);
    }

    public static function create(array $data): int
    {
        $sql = "INSERT INTO `notifications` (`user_id`, `admin_id`, `title`, `message`, `type`, `is_read`, `created_at`) 
                VALUES (:user_id, :admin_id, :title, :message, :type, 0, NOW())";
        Database::execute($sql, [
            ':user_id' => $data['user_id'] ?? null,
            ':admin_id' => $data['admin_id'] ?? null,
            ':title' => $data['title'],
            ':message' => $data['message'],
            ':type' => $data['type'] ?? 'info',
        ]);
        return (int) Database::lastInsertId();
    }
}
