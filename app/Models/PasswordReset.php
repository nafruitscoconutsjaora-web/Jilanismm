<?php

namespace App\Models;

use App\Database\Database;

class PasswordReset extends Model
{
    protected static string $table = 'password_resets';

    public static function createToken(string $email): string
    {
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        // Clear previous tokens for this email
        Database::execute(
            "DELETE FROM `password_resets` WHERE `email` = :email",
            [':email' => strtolower($email)]
        );

        Database::execute(
            "INSERT INTO `password_resets` (`email`, `token_hash`, `expires_at`, `created_at`) 
             VALUES (:email, :token_hash, :expires_at, NOW())",
            [
                ':email' => strtolower($email),
                ':token_hash' => $tokenHash,
                ':expires_at' => $expiresAt,
            ]
        );

        return $rawToken;
    }

    public static function verify(string $rawToken): ?array
    {
        $tokenHash = hash('sha256', $rawToken);
        $record = Database::fetch(
            "SELECT * FROM `password_resets` 
             WHERE `token_hash` = :token_hash AND `used_at` IS NULL AND `expires_at` > NOW() 
             LIMIT 1",
            [':token_hash' => $tokenHash]
        );

        return $record ?: null;
    }

    public static function markUsed(int $id): bool
    {
        return Database::execute(
            "UPDATE `password_resets` SET `used_at` = NOW() WHERE `id` = :id",
            [':id' => $id]
        );
    }
}
