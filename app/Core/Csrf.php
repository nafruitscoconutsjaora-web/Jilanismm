<?php

namespace App\Core;

class Csrf
{
    private const SESSION_KEY = '__csrf_token';

    public static function generateToken(): string
    {
        Session::start();
        $token = Session::get(self::SESSION_KEY);
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }
        return $token;
    }

    public static function getToken(): string
    {
        return self::generateToken();
    }

    public static function validate(?string $token): bool
    {
        Session::start();
        $stored = Session::get(self::SESSION_KEY);
        if (!$stored || empty($token)) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public static function getFormField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
