<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\SettingsService;
use App\Services\AdminAuthService;

class MaintenanceMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool
    {
        $path = $request->getPath();

        // Admin paths are never blocked by maintenance mode
        if (str_starts_with($path, '/admin')) {
            return true;
        }

        // If authenticated admin visits public site, allow
        if (AdminAuthService::check()) {
            return true;
        }

        $mode = SettingsService::get('maintenance_mode', 'disabled');
        if ($mode === 'enabled') {
            http_response_code(503);
            header('Retry-After: 3600');
            View::render('public/maintenance', [
                'title' => 'Under Maintenance - ' . config('app.name'),
                'siteName' => SettingsService::get('site_name', 'SMM Panel'),
            ], 'main');
            exit;
        }

        return true;
    }
}
