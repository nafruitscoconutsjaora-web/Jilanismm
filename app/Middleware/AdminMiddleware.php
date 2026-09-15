<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AdminAuthService;

class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool
    {
        if (!AdminAuthService::check()) {
            Session::setFlash('error', 'Admin authorization required.');
            Response::redirect('/admin/login');
            return false;
        }

        $admin = AdminAuthService::admin();
        if ($admin && $admin['status'] !== 'active') {
            AdminAuthService::logout();
            Session::setFlash('error', 'Your administrator account is inactive.');
            Response::redirect('/admin/login');
            return false;
        }

        return true;
    }
}
