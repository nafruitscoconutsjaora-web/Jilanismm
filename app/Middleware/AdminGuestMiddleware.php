<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AdminAuthService;

class AdminGuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool
    {
        if (AdminAuthService::check()) {
            Response::redirect('/admin/dashboard');
            return false;
        }

        return true;
    }
}
