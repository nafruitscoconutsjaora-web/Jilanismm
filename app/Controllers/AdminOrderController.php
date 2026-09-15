<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Models\Order;
use App\Services\AuthService;
use App\Services\OrderService;
use App\Services\WalletService;

class AdminOrderController
{
    public function index(Request $request): void
    {
        $admin = AuthService::admin();
        $page = max(1, (int)$request->input('page', 1));
        $status = trim($request->input('status', 'all'));
        $search = trim($request->input('search', ''));
        $userId = (int)$request->input('user_id', 0);

        $filters = [
            'status' => $status,
            'search' => $search,
            'user_id' => $userId
        ];

        $ordersData = OrderService::getAdminOrders($filters, $page, 20);
        $counts = Order::countByStatus();

        View::render('admin/orders/index', [
            'title' => 'Manage Orders - Admin SMM Panel',
            'admin' => $admin,
            'orders' => $ordersData['data'],
            'pagination' => $ordersData,
            'currentStatus' => $status,
            'search' => $search,
            'counts' => $counts
        ], 'admin');
    }

    public function show(Request $request, array $params): void
    {
        $admin = AuthService::admin();
        $orderId = (int)($params['id'] ?? 0);

        $order = OrderService::getOrderDetails($orderId);
        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            Response::redirect('/admin/orders');
            return;
        }

        // Fetch API logs for this order
        $apiLogs = Database::query(
            "SELECT * FROM `provider_api_logs` WHERE `order_id` = :oid ORDER BY `id` DESC LIMIT 10",
            [':oid' => $orderId]
        );

        View::render('admin/orders/show', [
            'title' => "Order #{$orderId} - Admin SMM Panel",
            'admin' => $admin,
            'order' => $order,
            'apiLogs' => $apiLogs
        ], 'admin');
    }

    public function updateStatus(Request $request, array $params): void
    {
        $admin = AuthService::admin();
        $orderId = (int)($params['id'] ?? 0);
        $newStatus = trim($request->input('status', ''));
        $reason = trim($request->input('reason', 'Updated manually by admin'));

        $res = OrderService::updateOrderStatus($orderId, $newStatus, 'admin', (int)$admin['id'], $reason);

        if ($res['success']) {
            Session::setFlash('success', "Order #{$orderId} status updated to {$newStatus}.");
        } else {
            Session::setFlash('error', $res['error'] ?? 'Failed to update order status.');
        }

        Response::redirect('/admin/orders/' . $orderId);
    }

    public function refund(Request $request, array $params): void
    {
        $orderId = (int)($params['id'] ?? 0);
        $order = Database::fetch("SELECT * FROM `orders` WHERE `id` = :id LIMIT 1", [':id' => $orderId]);

        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            Response::redirect('/admin/orders');
            return;
        }

        $reason = trim($request->input('reason', 'Admin initiated refund'));
        $amount = (float)$request->input('amount', 0);
        $refundAmount = $amount > 0 ? $amount : null;

        $res = WalletService::refundOrder((int)$order['user_id'], $orderId, $refundAmount, $reason);

        if ($res['success']) {
            Session::setFlash('success', "Order #{$orderId} successfully refunded $" . number_format($res['refunded_amount'], 4) . " to customer wallet.");
        } else {
            Session::setFlash('error', $res['error'] ?? 'Refund failed.');
        }

        Response::redirect('/admin/orders/' . $orderId);
    }
}
