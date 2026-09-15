<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function getUserNotifications(int $userId, int $limit = 20): array
    {
        return Notification::forUser($userId, $limit);
    }

    public static function getUnreadCount(int $userId): int
    {
        return Notification::unreadCountForUser($userId);
    }

    public static function markAsRead(int $id, int $userId): bool
    {
        return Notification::markAsRead($id, $userId);
    }

    public static function markAllAsRead(int $userId): bool
    {
        return Notification::markAllAsReadForUser($userId);
    }

    public static function send(int $userId, string $title, string $message, string $type = 'info'): int
    {
        return self::sendToUser($userId, $title, $message, $type);
    }

    public static function sendToUser(int $userId, string $title, string $message, string $type = 'info'): int
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }

    public static function broadcast(string $title, string $message, string $type = 'info'): int
    {
        return Notification::create([
            'user_id' => null,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }
}
