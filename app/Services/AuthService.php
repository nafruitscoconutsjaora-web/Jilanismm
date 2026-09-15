<?php

namespace App\Services;

use App\Core\Session;
use App\Models\User;
use App\Models\EmailVerification;
use App\Database\Database;

class AuthService
{
    private const SESSION_USER_ID = '__auth_user_id';
    private static ?array $cachedUser = null;

    public static function check(): bool
    {
        Session::start();
        return Session::has(self::SESSION_USER_ID) && !empty(Session::get(self::SESSION_USER_ID));
    }

    public static function id(): ?int
    {
        Session::start();
        return Session::get(self::SESSION_USER_ID);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser === null) {
            $userId = self::id();
            self::$cachedUser = User::find($userId);
            if (!self::$cachedUser) {
                self::logout();
                return null;
            }
        }

        return self::$cachedUser;
    }

    public static function attempt(string $email, string $password): array
    {
        $user = User::findByEmail($email);
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        if ($user['status'] === 'banned') {
            return ['success' => false, 'error' => 'Your account has been banned. Please contact support.'];
        }

        if ($user['status'] === 'suspended') {
            return ['success' => false, 'error' => 'Your account is suspended. Please contact support.'];
        }

        if ($user['status'] === 'inactive') {
            return ['success' => false, 'error' => 'Your account is inactive.'];
        }

        // Section 10 & 14: Regenerate session ID to prevent fixation
        Session::regenerate();
        Session::set(self::SESSION_USER_ID, (int) $user['id']);
        self::$cachedUser = $user;

        // Update login stats
        User::update($user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ]);

        return ['success' => true, 'user' => $user];
    }

    public static function register(array $data): array
    {
        // Check referrer if code provided
        $referredById = null;
        if (!empty($data['referral_code'])) {
            $referrer = User::findByReferralCode(trim($data['referral_code']));
            if ($referrer) {
                $referredById = $referrer['id'];
            }
        }

        // Generate referral code for new user
        $newReferralCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        // Hash password securely
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $userId = User::create([
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'password' => $hashedPassword,
            'referral_code' => $newReferralCode,
            'referred_by' => $referredById,
            'status' => 'active',
        ]);

        // Send Email Verification token
        $rawToken = EmailVerification::createToken($userId);
        $appUrl = rtrim(config('app.url', 'http://localhost:3000'), '/');
        $verifyUrl = "{$appUrl}/verify-email?token={$rawToken}";

        $emailSent = MailService::send(
            $data['email'],
            'Verify Your Email Address - ' . config('app.name'),
            "<p>Hello {$data['name']},</p><p>Please verify your email address by clicking the link below:</p><p><a href='{$verifyUrl}'>{$verifyUrl}</a></p><p>This link expires in 24 hours.</p>",
            "Hello {$data['name']},\nPlease verify your email: {$verifyUrl}\nExpires in 24 hours."
        );

        // Section 9: If SMTP is not configured, do not pretend email was sent
        $verificationNotice = $emailSent 
            ? 'A verification link has been sent to your email.' 
            : 'Account created! (Email service is currently offline, your account is ready for sign in).';

        return [
            'success' => true,
            'user_id' => $userId,
            'email_sent' => $emailSent,
            'notice' => $verificationNotice,
            'verification_url' => $verifyUrl,
        ];
    }

    public static function logout(): void
    {
        self::$cachedUser = null;
        Session::remove(self::SESSION_USER_ID);
        Session::destroy();
    }
}
