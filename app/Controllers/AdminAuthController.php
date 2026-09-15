<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AdminAuthService;
use App\Validation\Validator;

class AdminAuthController
{
    public function showLogin(Request $request): void
    {
        View::render('admin/login', [
            'title' => 'Admin Portal Sign In',
        ], 'auth');
    }

    public function login(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError());
            Response::redirect('/admin/login');
            return;
        }

        $result = AdminAuthService::attempt($request->input('email'), $request->input('password'));

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
            Response::redirect('/admin/login');
            return;
        }

        Session::setFlash('success', 'Welcome to the Admin Control Panel, ' . $result['admin']['name'] . '.');
        Response::redirect('/admin/dashboard');
    }

    public function logout(Request $request): void
    {
        AdminAuthService::logout();
        Session::setFlash('success', 'Logged out from Admin Portal.');
        Response::redirect('/admin/login');
    }
}
