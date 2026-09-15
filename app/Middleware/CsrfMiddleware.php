<?php

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool
    {
        if ($request->isPost()) {
            $token = $request->input('_csrf_token') ?? $request->header('x-csrf-token');

            if (!Csrf::validate($token)) {
                if ($request->isAjax()) {
                    Response::json(['success' => false, 'error' => 'CSRF token mismatch.'], 419);
                }

                Session::setFlash('error', 'Your session expired or the request could not be validated. Please try again.');
                $referer = $_SERVER['HTTP_REFERER'] ?? '/';
                Response::redirect($referer);
                return false;
            }
        }

        return true;
    }
}
