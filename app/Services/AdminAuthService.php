<?php

namespace App\Services;

use App\Core\Session;
use App\Models\Admin;

class AdminAuthService
{
    private const SESSION_ADMIN_ID = '__admin_auth_id';
    private static ?array $cachedAdmin = null;

    public static function check(): bool
    {
        Session::start();
        return Session::has(self::SESSION_ADMIN_ID) && !empty(Session::get(self::SESSION_ADMIN_ID));
    }

    public static function id(): ?int
    {
        Session::start();
        return Session::get(self::SESSION_ADMIN_ID);
    }

    public static function admin(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedAdmin === null) {
            $adminId = self::id();
            self::$cachedAdmin = Admin::find($adminId);
            if (!self::$cachedAdmin || self::$cachedAdmin['status'] !== 'active') {
                self::logout();
                return null;
            }
        }

        return self::$cachedAdmin;
    }

    public static function attempt(string $email, string $password): array
    {
        $admin = Admin::findByEmail($email);
        if (!$admin) {
            return ['success' => false, 'error' => 'Invalid administrator credentials.'];
        }

        if (!password_verify($password, $admin['password'])) {
            return ['success' => false, 'error' => 'Invalid administrator credentials.'];
        }

        if ($admin['status'] !== 'active') {
            return ['success' => false, 'error' => 'This administrator account is disabled.'];
        }

        // Regenerate session ID
        Session::regenerate();
        Session::set(self::SESSION_ADMIN_ID, (int) $admin['id']);
        self::$cachedAdmin = $admin;

        // Update login stats
        Admin::update($admin['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ]);

        return ['success' => true, 'admin' => $admin];
    }

    public static function logout(): void
    {
        self::$cachedAdmin = null;
        Session::remove(self::SESSION_ADMIN_ID);
        // Only destroy admin session part or full if not user
        Session::destroy();
    }
}
