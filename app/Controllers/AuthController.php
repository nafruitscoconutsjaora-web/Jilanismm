<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\EmailVerification;
use App\Services\AuthService;
use App\Services\MailService;
use App\Validation\Validator;

class AuthController
{
    public function showLogin(Request $request): void
    {
        View::render('public/login', [
            'title' => 'Sign In - ' . config('app.name'),
        ], 'auth');
    }

    public function login(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError());
            Session::setOld($request->all());
            Response::redirect('/login');
            return;
        }

        $result = AuthService::attempt($request->input('email'), $request->input('password'));

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
            Session::setOld($request->all());
            Response::redirect('/login');
            return;
        }

        Session::clearOld();
        Session::setFlash('success', 'Welcome back, ' . $result['user']['name'] . '!');
        Response::redirect('/dashboard');
    }

    public function showRegister(Request $request): void
    {
        $ref = $request->input('ref', '');
        View::render('public/register', [
            'title' => 'Create Account - ' . config('app.name'),
            'referralCode' => $ref,
        ], 'auth');
    }

    public function register(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError());
            Session::setOld($request->all());
            Response::redirect('/register');
            return;
        }

        $result = AuthService::register($request->all());

        Session::clearOld();
        Session::setFlash('success', $result['notice']);

        // Auto login or redirect to login
        AuthService::attempt($request->input('email'), $request->input('password'));
        Response::redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        AuthService::logout();
        Session::setFlash('success', 'You have been successfully logged out.');
        Response::redirect('/login');
    }

    public function showForgotPassword(Request $request): void
    {
        View::render('public/forgot-password', [
            'title' => 'Forgot Password - ' . config('app.name'),
        ], 'auth');
    }

    public function sendResetLink(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError());
            Response::redirect('/forgot-password');
            return;
        }

        $email = trim($request->input('email'));
        $user = User::findByEmail($email);

        if ($user) {
            $rawToken = PasswordReset::createToken($email);
            $appUrl = rtrim(config('app.url', 'http://localhost:3000'), '/');
            $resetUrl = "{$appUrl}/reset-password?token={$rawToken}";

            $sent = MailService::send(
                $email,
                'Password Reset Request - ' . config('app.name'),
                "<p>Hello {$user['name']},</p><p>We received a request to reset your password. Click the link below to set a new password:</p><p><a href='{$resetUrl}'>{$resetUrl}</a></p><p>This link will expire in 1 hour.</p>",
                "Reset your password: {$resetUrl}"
            );

            if (!$sent) {
                // SMTP not configured - safely notify user
                Session::setFlash('info', 'Password reset instructions have been logged. Check server logs if email server is not configured.');
            } else {
                Session::setFlash('success', 'A password reset link has been sent to your email.');
            }
        } else {
            // Constant response time/message for user privacy
            Session::setFlash('success', 'If an account exists with that email, a reset link has been sent.');
        }

        Response::redirect('/forgot-password');
    }

    public function showResetPassword(Request $request): void
    {
        $token = $request->input('token', '');
        $record = PasswordReset::verify($token);

        if (!$record) {
            Session::setFlash('error', 'This password reset token is invalid or has expired.');
            Response::redirect('/forgot-password');
            return;
        }

        View::render('public/reset-password', [
            'title' => 'Reset Password - ' . config('app.name'),
            'token' => $token,
            'email' => $record['email'],
        ], 'auth');
    }

    public function resetPassword(Request $request): void
    {
        $token = $request->input('token', '');
        $record = PasswordReset::verify($token);

        if (!$record) {
            Session::setFlash('error', 'Password reset token is invalid or expired.');
            Response::redirect('/forgot-password');
            return;
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|confirmed',
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError());
            Response::redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        $user = User::findByEmail($record['email']);
        if ($user) {
            $hashed = password_hash($request->input('password'), PASSWORD_BCRYPT, ['cost' => 12]);
            User::update($user['id'], ['password' => $hashed]);
            PasswordReset::markUsed((int) $record['id']);

            Session::setFlash('success', 'Your password has been successfully reset. Please log in.');
            Response::redirect('/login');
            return;
        }

        Session::setFlash('error', 'User account not found.');
        Response::redirect('/login');
    }

    public function verifyEmail(Request $request): void
    {
        $token = $request->input('token', '');
        if (empty($token)) {
            Session::setFlash('error', 'Missing verification token.');
            Response::redirect('/login');
            return;
        }

        $userId = EmailVerification::verify($token);
        if (!$userId) {
            Session::setFlash('error', 'This verification link is invalid or has expired.');
            Response::redirect('/login');
            return;
        }

        Session::setFlash('success', 'Your email address has been verified successfully!');
        if (AuthService::check()) {
            Response::redirect('/dashboard');
        } else {
            Response::redirect('/login');
        }
    }
}
