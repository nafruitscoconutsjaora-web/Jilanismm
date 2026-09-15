<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Database\Database;
use App\Services\AdminAuthService;

class AdminReportController
{
    public function index(Request $request): void
    {
        $admin = AdminAuthService::admin();

        // 1. Financial Analytics
        $depositsData = Database::fetch(
            "SELECT 
                COUNT(*) as count, 
                COALESCE(SUM(amount), 0) as gross_volume,
                COALESCE(SUM(fee), 0) as total_fees
             FROM `payments` WHERE `status` = 'completed'"
        );

        $ordersVolume = Database::fetch(
            "SELECT 
                COUNT(*) as count,
                COALESCE(SUM(charge), 0) as gross_charge
             FROM `orders` WHERE `status` NOT IN ('canceled')"
        );

        $refundsVolume = Database::fetch(
            "SELECT COALESCE(SUM(amount), 0) as total_refunded 
             FROM `wallet_transactions` WHERE `type` = 'refund'"
        );

        $couponSavings = Database::fetch(
            "SELECT COALESCE(SUM(discount_amount), 0) as total_discount 
             FROM `orders` WHERE `coupon_id` IS NOT NULL"
        );

        $referralPayouts = Database::fetch(
            "SELECT COALESCE(SUM(reward_amount), 0) as total_referral_rewards 
             FROM `referral_logs` WHERE `status` = 'credited'"
        );

        $outstandingBalance = Database::fetch(
            "SELECT COALESCE(SUM(balance), 0) as user_liability, COALESCE(SUM(spent), 0) as all_time_spent 
             FROM `wallets`"
        );

        // 2. Orders by Status
        $ordersByStatus = Database::query(
            "SELECT `status`, COUNT(*) as count, COALESCE(SUM(charge), 0) as total_charge 
             FROM `orders` 
             GROUP BY `status`"
        );

        // 3. Top 10 Services
        $topServices = Database::query(
            "SELECT s.id, s.name, c.name as category_name, 
                    COUNT(o.id) as orders_count, 
                    COALESCE(SUM(o.charge), 0) as revenue,
                    COALESCE(SUM(o.quantity), 0) as total_quantity
             FROM `orders` o 
             JOIN `services` s ON o.service_id = s.id 
             LEFT JOIN `categories` c ON s.category_id = c.id
             GROUP BY s.id, s.name, c.name 
             ORDER BY orders_count DESC LIMIT 10"
        );

        // 4. Payment Gateway Breakdown
        $gatewayBreakdown = Database::query(
            "SELECT g.name, g.code, 
                    COUNT(p.id) as transactions_count,
                    COALESCE(SUM(p.amount), 0) as volume,
                    COALESCE(SUM(p.fee), 0) as fees
             FROM `payments` p
             JOIN `payment_gateways` g ON p.gateway_id = g.id
             WHERE p.status = 'completed'
             GROUP BY g.id, g.name, g.code
             ORDER BY volume DESC"
        );

        // 5. Top 10 Customer Spenders
        $topSpenders = Database::query(
            "SELECT u.id, u.name, u.email, w.spent, w.balance,
                    (SELECT COUNT(*) FROM `orders` o WHERE o.user_id = u.id) as orders_count
             FROM `users` u
             JOIN `wallets` w ON u.id = w.user_id
             ORDER BY w.spent DESC LIMIT 10"
        );

        // 6. Recent Daily Revenue (Last 14 Days)
        $dailyRevenue = Database::query(
            "SELECT DATE(created_at) as log_date, 
                    COUNT(*) as orders_count, 
                    COALESCE(SUM(charge), 0) as total_revenue 
             FROM `orders` 
             WHERE `created_at` >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
               AND `status` NOT IN ('canceled')
             GROUP BY DATE(created_at) 
             ORDER BY log_date DESC"
        );

        View::render('admin/reports/index', [
            'title' => 'Financial & Operational Reports - Admin Panel',
            'admin' => $admin,
            'finance' => [
                'gross_deposits' => (float)($depositsData['gross_volume'] ?? 0),
                'total_deposit_fees' => (float)($depositsData['total_fees'] ?? 0),
                'deposits_count' => (int)($depositsData['count'] ?? 0),
                'gross_orders' => (float)($ordersVolume['gross_charge'] ?? 0),
                'orders_count' => (int)($ordersVolume['count'] ?? 0),
                'total_refunded' => (float)($refundsVolume['total_refunded'] ?? 0),
                'coupon_discounts' => (float)($couponSavings['total_discount'] ?? 0),
                'referral_rewards' => (float)($referralPayouts['total_referral_rewards'] ?? 0),
                'user_liability' => (float)($outstandingBalance['user_liability'] ?? 0),
                'all_time_spent' => (float)($outstandingBalance['all_time_spent'] ?? 0),
            ],
            'ordersByStatus' => $ordersByStatus,
            'topServices' => $topServices,
            'gatewayBreakdown' => $gatewayBreakdown,
            'topSpenders' => $topSpenders,
            'dailyRevenue' => $dailyRevenue,
        ], 'admin');
    }
}
