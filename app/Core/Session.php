<?php

namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                   (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        session_set_cookie_params([
            'lifetime' => 86400 * 7, // 7 days
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('SMM_SESSION_ID');
        session_start();
        self::$started = true;

        // Session timeout and activity tracking
        $now = time();
        if (isset($_SESSION['__last_activity']) && ($now - $_SESSION['__last_activity'] > 7200)) { // 2 hours idle
            self::destroy();
            session_start();
        }
        $_SESSION['__last_activity'] = $now;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        self::start();
        if (!headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            session_destroy();
            self::$started = false;
        }
    }

    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION['__flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string
    {
        self::start();
        if (isset($_SESSION['__flash'][$type])) {
            $msg = $_SESSION['__flash'][$type];
            unset($_SESSION['__flash'][$type]);
            return $msg;
        }
        return null;
    }

    public static function hasFlash(string $type): bool
    {
        self::start();
        return isset($_SESSION['__flash'][$type]);
    }

    public static function setOld(array $data): void
    {
        self::start();
        // Exclude passwords from old input
        unset($data['password'], $data['password_confirmation'], $data['_csrf_token']);
        $_SESSION['__old_input'] = $data;
    }

    public static function getOld(string $key, mixed $default = ''): mixed
    {
        self::start();
        $val = $_SESSION['__old_input'][$key] ?? $default;
        return $val;
    }

    public static function clearOld(): void
    {
        self::start();
        unset($_SESSION['__old_input']);
    }
}
