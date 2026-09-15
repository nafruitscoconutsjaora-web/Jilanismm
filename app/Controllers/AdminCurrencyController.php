<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Services\AuthService;
use App\Services\CurrencyService;

class AdminCurrencyController
{
    public function index(Request $request): void
    {
        $admin = AuthService::admin();
        $currencies = CurrencyService::getAllCurrencies();

        View::render('admin/currencies/index', [
            'title' => 'Currencies & Exchange Rates - Admin SMM Panel',
            'admin' => $admin,
            'currencies' => $currencies
        ], 'admin');
    }

    public function create(Request $request): void
    {
        $code = strtoupper(trim($request->input('code', '')));
        $name = trim($request->input('name', ''));
        $symbol = trim($request->input('symbol', '$'));
        $rate = (float)$request->input('exchange_rate', 1.0);
        $status = $request->input('status') === 'inactive' ? 'inactive' : 'active';

        if (empty($code) || empty($name) || $rate <= 0) {
            Session::setFlash('error', 'Currency code, name, and valid exchange rate are required.');
            Response::redirect('/admin/currencies');
            return;
        }

        $existing = Database::fetch("SELECT `id` FROM `currencies` WHERE `code` = :c LIMIT 1", [':c' => $code]);
        if ($existing) {
            Session::setFlash('error', "Currency code '{$code}' already exists.");
            Response::redirect('/admin/currencies');
            return;
        }

        Database::execute(
            "INSERT INTO `currencies` (`code`, `name`, `symbol`, `exchange_rate`, `is_default`, `status`, `created_at`) 
             VALUES (:c, :n, :s, :r, 0, :st, NOW())",
            [':c' => $code, ':n' => $name, ':s' => $symbol, ':r' => $rate, ':st' => $status]
        );

        Session::setFlash('success', "Currency {$code} created successfully.");
        Response::redirect('/admin/currencies');
    }

    public function update(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $rate = (float)$request->input('exchange_rate', 0);
        $symbol = trim($request->input('symbol', '$'));
        $status = $request->input('status') === 'inactive' ? 'inactive' : 'active';

        if ($rate <= 0) {
            Session::setFlash('error', 'Exchange rate must be greater than zero.');
            Response::redirect('/admin/currencies');
            return;
        }

        Database::execute(
            "UPDATE `currencies` SET `exchange_rate` = :r, `symbol` = :s, `status` = :st, `updated_at` = NOW() WHERE `id` = :id",
            [':r' => $rate, ':s' => $symbol, ':st' => $status, ':id' => $id]
        );

        Session::setFlash('success', 'Currency rate updated successfully.');
        Response::redirect('/admin/currencies');
    }

    public function setDefault(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $curr = Database::fetch("SELECT * FROM `currencies` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$curr) {
            Session::setFlash('error', 'Currency not found.');
            Response::redirect('/admin/currencies');
            return;
        }

        Database::execute("UPDATE `currencies` SET `is_default` = 0");
        Database::execute("UPDATE `currencies` SET `is_default` = 1, `exchange_rate` = 1.0000, `status` = 'active' WHERE `id` = :id", [':id' => $id]);

        Session::setFlash('success', "{$curr['code']} is now the panel base currency.");
        Response::redirect('/admin/currencies');
    }
}
