<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Models\User;
use App\Services\AdminAuthService;
use App\Services\NotificationService;
use App\Services\SettingsService;
use App\Validation\Validator;

class AdminController
{
    public function dashboard(Request $request): void
    {
        $admin = AdminAuthService::admin();

        // Real metrics from database
        $totalUsers = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM `users`")['cnt'] ?? 0);
        $activeUsers = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM `users` WHERE `status` = 'active'")['cnt'] ?? 0);
        $suspendedUsers = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM `users` WHERE `status` IN ('suspended', 'banned')")['cnt'] ?? 0);
        $totalOrders = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM `orders`")['cnt'] ?? 0);
        $totalBalance = (float) (Database::fetch("SELECT SUM(balance) as total FROM `wallets`")['total'] ?? 0.0);

        // Recent users
        $recentUsers = Database::query("SELECT u.*, w.balance FROM `users` u LEFT JOIN `wallets` w ON u.id = w.user_id ORDER BY u.id DESC LIMIT 5");

        // Maintenance state
        $maintenanceMode = SettingsService::get('maintenance_mode', 'disabled');

        View::render('admin/dashboard', [
            'title' => 'Admin Dashboard - ' . config('app.name'),
            'admin' => $admin,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'suspendedUsers' => $suspendedUsers,
            'totalOrders' => $totalOrders,
            'totalBalance' => $totalBalance,
            'recentUsers' => $recentUsers,
            'maintenanceMode' => $maintenanceMode,
        ], 'admin');
    }

    public function users(Request $request): void
    {
        $admin = AdminAuthService::admin();
        $page = max(1, (int) $request->input('page', 1));
        $search = trim((string) $request->input('q', ''));

        $usersData = User::paginate($page, 15, $search);

        View::render('admin/users', [
            'title' => 'User Management - Admin',
            'admin' => $admin,
            'users' => $usersData['data'],
            'pagination' => $usersData,
            'search' => $search,
        ], 'admin');
    }

    public function updateUserStatus(Request $request): void
    {
        $userId = (int) $request->input('user_id');
        $status = $request->input('status');

        if (!in_array($status, ['active', 'inactive', 'suspended', 'banned'], true)) {
            Session::setFlash('error', 'Invalid status value.');
            Response::redirect('/admin/users');
            return;
        }

        User::update($userId, ['status' => $status]);
        Session::setFlash('success', "User #{$userId} status updated to {$status}.");
        Response::redirect('/admin/users');
    }

    public function showUser(Request $request, array $params): void
    {
        $admin = AdminAuthService::admin();
        $userId = (int)($params['id'] ?? 0);
        $user = User::findById($userId);

        if (!$user) {
            Session::setFlash('error', 'User not found.');
            Response::redirect('/admin/users');
            return;
        }

        $wallet = User::getWallet($userId);
        $orders = Database::query(
            "SELECT o.*, s.name as service_name 
             FROM `orders` o 
             LEFT JOIN `services` s ON o.service_id = s.id 
             WHERE o.user_id = :uid 
             ORDER BY o.id DESC LIMIT 20",
            [':uid' => $userId]
        );

        $transactions = Database::query(
            "SELECT * FROM `wallet_transactions` WHERE `user_id` = :uid ORDER BY `id` DESC LIMIT 30",
            [':uid' => $userId]
        );

        $tickets = Database::query(
            "SELECT * FROM `tickets` WHERE `user_id` = :uid ORDER BY `id` DESC LIMIT 10",
            [':uid' => $userId]
        );

        $referrals = Database::query(
            "SELECT id, name, email, created_at FROM `users` WHERE `referred_by` = :uid ORDER BY `id` DESC LIMIT 10",
            [':uid' => $userId]
        );

        View::render('admin/users/show', [
            'title' => "Manage User: {$user['name']} - Admin",
            'admin' => $admin,
            'user' => $user,
            'wallet' => $wallet,
            'orders' => $orders,
            'transactions' => $transactions,
            'tickets' => $tickets,
            'referrals' => $referrals,
        ], 'admin');
    }

    public function settings(Request $request): void
    {
        $admin = AdminAuthService::admin();
        $settings = SettingsService::load();

        View::render('admin/settings', [
            'title' => 'System Settings - Admin',
            'admin' => $admin,
            'settings' => $settings,
        ], 'admin');
    }

    public function updateSettings(Request $request): void
    {
        $keys = [
            'site_name',
            'site_description',
            'support_email',
            'default_currency',
            'currency_symbol',
            'maintenance_mode',
            'user_registration',
            'email_verification',
            'referral_system',
            'referral_commission_rate',
        ];

        foreach ($keys as $key) {
            if ($request->input($key) !== null) {
                SettingsService::set($key, trim($request->input($key)));
            }
        }

        Session::setFlash('success', 'System settings saved successfully.');
        Response::redirect('/admin/settings');
    }

    public function toggleMaintenance(Request $request): void
    {
        $current = SettingsService::get('maintenance_mode', 'disabled');
        $newMode = ($current === 'enabled') ? 'disabled' : 'enabled';
        SettingsService::set('maintenance_mode', $newMode, 'system');

        Session::setFlash('success', "Maintenance mode is now {$newMode}.");
        $referer = $_SERVER['HTTP_REFERER'] ?? '/admin/dashboard';
        Response::redirect($referer);
    }

    public function notifications(Request $request): void
    {
        $admin = AdminAuthService::admin();
        $notifications = Database::query("SELECT * FROM `notifications` ORDER BY `id` DESC LIMIT 50");

        View::render('admin/notifications', [
            'title' => 'Notifications - Admin',
            'admin' => $admin,
            'notifications' => $notifications,
        ], 'admin');
    }

    public function sendNotification(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'message' => 'required|min:5',
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError());
            Response::redirect('/admin/notifications');
            return;
        }

        $userId = $request->input('user_id');
        $userId = !empty($userId) ? (int)$userId : null;

        if ($userId) {
            NotificationService::sendToUser($userId, $request->input('title'), $request->input('message'), $request->input('type', 'info'));
            Session::setFlash('success', "Notification dispatched to user #{$userId}.");
        } else {
            NotificationService::broadcast($request->input('title'), $request->input('message'), $request->input('type', 'info'));
            Session::setFlash('success', 'Broadcast notification dispatched to all users.');
        }

        Response::redirect('/admin/notifications');
    }

    // Foundations for upcoming Modules (Part 2+)
    public function placeholderModule(Request $request, array $params): void
    {
        $module = $params['module'] ?? 'Module';
        $admin = AdminAuthService::admin();

        View::render('admin/placeholder', [
            'title' => ucfirst($module) . ' Management - Admin',
            'admin' => $admin,
            'moduleName' => ucfirst($module),
        ], 'admin');
    }
}
