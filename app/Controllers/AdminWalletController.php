<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Services\AuthService;
use App\Services\WalletService;

class AdminWalletController
{
    public function index(Request $request): void
    {
        $admin = AuthService::admin();
        $page = max(1, (int)$request->input('page', 1));
        $search = trim($request->input('search', ''));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(u.email LIKE :s_email OR u.name LIKE :s_name)";
            $params[':s_email'] = "%{$search}%";
            $params[':s_name'] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as cnt FROM `wallets` w JOIN `users` u ON w.user_id = u.id WHERE {$whereClause}",
            $params
        )['cnt'] ?? 0;

        $wallets = Database::query(
            "SELECT w.*, u.name as user_name, u.email as user_email 
             FROM `wallets` w 
             JOIN `users` u ON w.user_id = u.id 
             WHERE {$whereClause} 
             ORDER BY w.balance DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $recentTransactions = Database::query(
            "SELECT wt.*, u.email as user_email 
             FROM `wallet_transactions` wt 
             JOIN `users` u ON wt.user_id = u.id 
             ORDER BY wt.id DESC LIMIT 15"
        );

        View::render('admin/wallets/index', [
            'title' => 'Manage Wallets - Admin SMM Panel',
            'admin' => $admin,
            'wallets' => $wallets,
            'recentTransactions' => $recentTransactions,
            'total' => $total,
            'page' => $page,
            'lastPage' => (int)ceil($total / $perPage),
            'search' => $search
        ], 'admin');
    }

    public function adjustBalance(Request $request): void
    {
        $admin = AuthService::admin();
        $userId = (int)$request->input('user_id', 0);
        $action = $request->input('action') === 'deduct' ? 'deduct' : 'add';
        $amount = (float)$request->input('amount', 0);
        $reason = trim($request->input('reason', 'Admin adjustment'));

        if ($userId <= 0 || $amount <= 0) {
            Session::setFlash('error', 'Valid user and positive amount are required.');
            Response::redirect('/admin/wallets');
            return;
        }

        $res = WalletService::adminAdjust($userId, $action, $amount, $reason, (int)$admin['id']);

        if ($res['success']) {
            $verb = $action === 'add' ? 'credited' : 'deducted';
            Session::setFlash('success', "Successfully {$verb} $" . number_format($amount, 4) . ". New balance: $" . number_format($res['balance_after'], 4));
        } else {
            Session::setFlash('error', $res['error'] ?? 'Adjustment failed.');
        }

        Response::redirect('/admin/wallets');
    }
}
