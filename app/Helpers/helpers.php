<?php

use App\Core\Csrf;
use App\Core\Session;
use App\Core\Response;
use App\Services\SettingsService;
use App\Services\AuthService;
use App\Services\AdminAuthService;

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('esc')) {
    function esc(?string $value): string
    {
        return e($value);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::getToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::getFormField();
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return AuthService::user();
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        return AuthService::check();
    }
}

if (!function_exists('admin')) {
    function admin(): ?array
    {
        return AdminAuthService::admin();
    }
}

if (!function_exists('admin_check')) {
    function admin_check(): bool
    {
        return AdminAuthService::check();
    }
}

if (!function_exists('flash')) {
    function flash(string $type): ?string
    {
        return Session::getFlash($type);
    }
}

if (!function_exists('has_flash')) {
    function has_flash(string $type): bool
    {
        return Session::hasFlash($type);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::getOld($key, $default);
    }
}

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return SettingsService::get($key, $default);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(config('app.url', ''), '/');
        $path = '/' . ltrim($path, '/');
        return $base . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302): void
    {
        Response::redirect($path, $status);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $file = $parts[0] ?? '';
        $filePath = dirname(__DIR__, 2) . '/config/' . $file . '.php';

        if (!file_exists($filePath)) {
            return $default;
        }

        static $cachedConfigs = [];
        if (!isset($cachedConfigs[$file])) {
            $cachedConfigs[$file] = require $filePath;
        }

        $current = $cachedConfigs[$file];
        for ($i = 1; $i < count($parts); $i++) {
            $segment = $parts[$i];
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float|int|string $amount, string $currency = 'USD', string $symbol = '$'): string
    {
        return $symbol . number_format((float) $amount, 2);
    }
}
