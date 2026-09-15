<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool
    {
        if (AuthService::check()) {
            Response::redirect('/dashboard');
            return false;
        }

        return true;
    }
}
