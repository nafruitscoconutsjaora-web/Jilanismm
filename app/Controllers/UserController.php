<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Models\User;
use App\Models\Notification;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Services\SettingsService;
use App\Validation\Validator;

class UserController
{
    public function dashboard(Request $request): void
    {
        $user = AuthService::user();
        $wallet = User::getWallet($user['id']);

        // Fetch real order metrics for this user
        $totalOrders = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM `orders` WHERE `user_id` = :uid", [':uid' => $user['id']])['cnt'] ?? 0);
        $pendingOrders = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM `orders` WHERE `user_id` = :uid AND `status` = 'pending'", [':uid' => $user['id']])['cnt'] ?? 0);
        $completedOrders = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM `orders` WHERE `user_id` = :uid AND `status` = 'completed'", [':uid' => $user['id']])['cnt'] ?? 0);

        // Fetch latest 5 orders
        $recentOrders = Database::query(
            "SELECT o.*, s.name as service_name 
             FROM `orders` o 
             LEFT JOIN `services` s ON o.service_id = s.id 
             WHERE o.user_id = :uid 
             ORDER BY o.id DESC LIMIT 5",
            [':uid' => $user['id']]
        );

        // Unread notifications
        $notifications = NotificationService::getUserNotifications($user['id'], 5);
        $unreadCount = NotificationService::getUnreadCount($user['id']);

        View::render('user/dashboard', [
            'title' => 'User Dashboard - ' . config('app.name'),
            'user' => $user,
            'wallet' => $wallet,
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders,
            'recentOrders' => $recentOrders,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ], 'user');
    }

    public function profile(Request $request): void
    {
        $user = AuthService::user();
        View::render('user/profile', [
            'title' => 'My Profile - ' . config('app.name'),
            'user' => $user,
        ], 'user');
    }

    public function updateProfile(Request $request): void
    {
        $user = AuthService::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'email' => "required|email|unique:users,email,{$user['id']}",
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError());
            Response::redirect('/profile');
            return;
        }

        User::update($user['id'], [
            'name' => trim($request->input('name')),
            'email' => strtolower(trim($request->input('email'))),
        ]);

        Session::setFlash('success', 'Profile updated successfully.');
        Response::redirect('/profile');
    }

    public function updatePassword(Request $request): void
    {
        $user = AuthService::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError());
            Response::redirect('/profile');
            return;
        }

        if (!password_verify($request->input('current_password'), $user['password'])) {
            Session::setFlash('error', 'Your current password does not match.');
            Response::redirect('/profile');
            return;
        }

        $hashed = password_hash($request->input('new_password'), PASSWORD_BCRYPT, ['cost' => 12]);
        User::update($user['id'], ['password' => $hashed]);

        Session::setFlash('success', 'Password updated successfully.');
        Response::redirect('/profile');
    }

    public function settings(Request $request): void
    {
        $user = AuthService::user();
        View::render('user/settings', [
            'title' => 'Account Settings - ' . config('app.name'),
            'user' => $user,
        ], 'user');
    }

    public function services(Request $request): void
    {
        $user = AuthService::user();
        $categories = Database::query("SELECT * FROM `categories` WHERE `status` = 'active' ORDER BY `sort_order` ASC");
        $services = Database::query(
            "SELECT s.*, c.name as category_name 
             FROM `services` s 
             LEFT JOIN `categories` c ON s.category_id = c.id 
             WHERE s.status = 'active' 
             ORDER BY c.sort_order ASC, s.sort_order ASC"
        );

        View::render('user/services', [
            'title' => 'Services Directory - ' . config('app.name'),
            'user' => $user,
            'categories' => $categories,
            'services' => $services,
        ], 'user');
    }

    public function orders(Request $request): void
    {
        $user = AuthService::user();
        $status = $request->input('status', 'all');

        $where = "user_id = :uid";
        $params = [':uid' => $user['id']];

        if ($status !== 'all' && in_array($status, ['pending', 'in_progress', 'completed', 'partial', 'canceled', 'refunded'], true)) {
            $where .= " AND status = :status";
            $params[':status'] = $status;
        }

        $orders = Database::query(
            "SELECT o.*, s.name as service_name 
             FROM `orders` o 
             LEFT JOIN `services` s ON o.service_id = s.id 
             WHERE {$where} 
             ORDER BY o.id DESC",
            $params
        );

        View::render('user/orders', [
            'title' => 'My Orders - ' . config('app.name'),
            'user' => $user,
            'orders' => $orders,
            'currentStatus' => $status,
        ], 'user');
    }

    public function wallet(Request $request): void
    {
        $user = AuthService::user();
        $wallet = User::getWallet($user['id']);
        $transactions = Database::query(
            "SELECT * FROM `wallet_transactions` WHERE `user_id` = :uid ORDER BY `id` DESC LIMIT 20",
            [':uid' => $user['id']]
        );

        View::render('user/wallet', [
            'title' => 'My Wallet - ' . config('app.name'),
            'user' => $user,
            'wallet' => $wallet,
            'transactions' => $transactions,
        ], 'user');
    }

    public function notifications(Request $request): void
    {
        $user = AuthService::user();
        $notifications = NotificationService::getUserNotifications($user['id'], 50);

        View::render('user/notifications', [
            'title' => 'Notifications - ' . config('app.name'),
            'user' => $user,
            'notifications' => $notifications,
        ], 'user');
    }

    public function markNotificationRead(Request $request, array $params): void
    {
        $user = AuthService::user();
        $id = (int) ($params['id'] ?? 0);
        if ($id > 0) {
            NotificationService::markAsRead($id, $user['id']);
        }
        Session::setFlash('success', 'Notification marked as read.');
        Response::redirect('/notifications');
    }

    public function markAllNotificationsRead(Request $request): void
    {
        $user = AuthService::user();
        NotificationService::markAllAsRead($user['id']);
        Session::setFlash('success', 'All notifications marked as read.');
        Response::redirect('/notifications');
    }

    public function referrals(Request $request): void
    {
        $user = AuthService::user();
        $referralRate = SettingsService::get('referral_commission_rate', '5.00');
        $referrals = Database::query(
            "SELECT r.*, u.name as referred_name, u.email as referred_email, u.created_at as joined_at 
             FROM `referrals` r 
             JOIN `users` u ON r.referred_id = u.id 
             WHERE r.referrer_id = :uid 
             ORDER BY r.id DESC",
            [':uid' => $user['id']]
        );

        View::render('user/referrals', [
            'title' => 'Affiliate / Referrals - ' . config('app.name'),
            'user' => $user,
            'referralRate' => $referralRate,
            'referrals' => $referrals,
        ], 'user');
    }

    public function support(Request $request): void
    {
        $user = AuthService::user();
        $tickets = Database::query(
            "SELECT * FROM `tickets` WHERE `user_id` = :uid ORDER BY `id` DESC",
            [':uid' => $user['id']]
        );

        View::render('user/support', [
            'title' => 'Support Tickets - ' . config('app.name'),
            'user' => $user,
            'tickets' => $tickets,
        ], 'user');
    }
}
