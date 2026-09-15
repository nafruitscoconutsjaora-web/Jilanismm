<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AdminAuthService;
use App\Services\AuthService;
use App\Services\SettingsService;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool
    {
        // Enforce maintenance mode for user routes
        if (SettingsService::get('maintenance_mode', 'disabled') === 'enabled' && !AdminAuthService::check()) {
            http_response_code(503);
            header('Retry-After: 3600');
            View::render('public/maintenance', [
                'title' => 'Under Maintenance - ' . config('app.name'),
                'siteName' => SettingsService::get('site_name', 'SMM Panel'),
            ], 'main');
            exit;
        }

        if (!AuthService::check()) {
            Session::setFlash('error', 'Please log in to access your dashboard.');
            Response::redirect('/login');
            return false;
        }

        $user = AuthService::user();
        if ($user && in_array($user['status'], ['banned', 'suspended'], true)) {
            AuthService::logout();
            Session::setFlash('error', 'Your account has been ' . $user['status'] . '. Please contact support.');
            Response::redirect('/login');
            return false;
        }

        return true;
    }
}
