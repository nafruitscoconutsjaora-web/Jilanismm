<?php

namespace App\Models;

use App\Database\Database;

class EmailVerification extends Model
{
    protected static string $table = 'email_verifications';

    public static function createToken(int $userId): string
    {
        // 32-byte cryptographically secure random raw token
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + 86400); // 24 hours

        // Invalidate any existing unused tokens for this user
        Database::execute(
            "DELETE FROM `email_verifications` WHERE `user_id` = :user_id AND `used_at` IS NULL",
            [':user_id' => $userId]
        );

        Database::execute(
            "INSERT INTO `email_verifications` (`user_id`, `token_hash`, `expires_at`, `created_at`) 
             VALUES (:user_id, :token_hash, :expires_at, NOW())",
            [
                ':user_id' => $userId,
                ':token_hash' => $tokenHash,
                ':expires_at' => $expiresAt,
            ]
        );

        return $rawToken;
    }

    public static function verify(string $rawToken): ?int
    {
        $tokenHash = hash('sha256', $rawToken);
        $record = Database::fetch(
            "SELECT * FROM `email_verifications` 
             WHERE `token_hash` = :token_hash AND `used_at` IS NULL AND `expires_at` > NOW() 
             LIMIT 1",
            [':token_hash' => $tokenHash]
        );

        if (!$record) {
            return null;
        }

        // Mark as used (single use)
        Database::execute(
            "UPDATE `email_verifications` SET `used_at` = NOW() WHERE `id` = :id",
            [':id' => $record['id']]
        );

        // Update user email_verified_at
        Database::execute(
            "UPDATE `users` SET `email_verified_at` = NOW(), `updated_at` = NOW() WHERE `id` = :user_id",
            [':user_id' => $record['user_id']]
        );

        return (int) $record['user_id'];
    }
}
