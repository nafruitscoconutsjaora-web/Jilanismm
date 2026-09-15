<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    private static ?array $cachedSettings = null;

    public static function load(): array
    {
        if (self::$cachedSettings === null) {
            try {
                self::$cachedSettings = Setting::getAll();
            } catch (\Throwable $e) {
                self::$cachedSettings = [];
            }
        }
        return self::$cachedSettings;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = self::load();
        return $settings[$key] ?? $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general'): bool
    {
        $res = Setting::set($key, $value, $group);
        if ($res) {
            self::$cachedSettings[$key] = $value;
        }
        return $res;
    }

    public static function updateMany(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value, $group);
        }
    }
}
