<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Models\User;
use App\Services\AuthService;
use App\Services\OrderService;
use App\Services\PricingService;
use App\Services\CouponService;

class OrderController
{
    public function newOrder(Request $request): void
    {
        $user = AuthService::user();
        $wallet = User::getWallet($user['id']);

        $categories = Database::query(
            "SELECT c.*, COUNT(s.id) as service_count 
             FROM `categories` c 
             JOIN `services` s ON c.id = s.category_id AND s.status = 'active'
             WHERE c.status = 'active' 
             GROUP BY c.id 
             ORDER BY c.sort_order ASC, c.name ASC"
        );

        $services = Database::query(
            "SELECT s.*, c.name as category_name 
             FROM `services` s 
             JOIN `categories` c ON s.category_id = c.id 
             WHERE s.status = 'active' AND c.status = 'active' 
             ORDER BY c.sort_order ASC, s.sort_order ASC, s.name ASC"
        );

        $preselectedServiceId = (int)$request->input('service_id', 0);

        View::render('user/orders/new', [
            'title' => 'New Order - ' . config('app.name'),
            'user' => $user,
            'wallet' => $wallet,
            'categories' => $categories,
            'services' => $services,
            'preselectedServiceId' => $preselectedServiceId
        ], 'user');
    }

    public function placeOrder(Request $request): void
    {
        $user = AuthService::user();
        $serviceId = (int)$request->input('service_id', 0);
        $link = trim($request->input('link', ''));
        $quantity = (int)$request->input('quantity', 0);

        $isAjax = $request->header('X-Requested-With') === 'XMLHttpRequest' || str_contains($request->header('Accept', ''), 'application/json');

        if ($serviceId <= 0) {
            $error = 'Please select a valid service.';
            if ($isAjax) { Response::json(['success' => false, 'error' => $error], 422); return; }
            Session::setFlash('error', $error);
            Response::redirect('/order/new');
            return;
        }

        if (empty($link)) {
            $error = 'Please enter a target link or username for the order.';
            if ($isAjax) { Response::json(['success' => false, 'error' => $error], 422); return; }
            Session::setFlash('error', $error);
            Response::redirect('/order/new?service_id=' . $serviceId);
            return;
        }

        if ($quantity <= 0) {
            $error = 'Please enter a valid quantity.';
            if ($isAjax) { Response::json(['success' => false, 'error' => $error], 422); return; }
            Session::setFlash('error', $error);
            Response::redirect('/order/new?service_id=' . $serviceId);
            return;
        }

        $couponCode = trim((string)$request->input('coupon_code', ''));

        // Invoke Order Engine
        $result = OrderService::createOrder($user['id'], $serviceId, $link, $quantity, $couponCode);

        if (!$result['success']) {
            if ($isAjax) { Response::json(['success' => false, 'error' => $result['error']], 400); return; }
            Session::setFlash('error', $result['error']);
            Response::redirect('/order/new?service_id=' . $serviceId);
            return;
        }

        $orderId = $result['order_id'];
        $chargeFormatted = number_format((float)$result['charge'], 4);

        if ($result['status'] === 'refunded') {
            $msg = "Order #{$orderId} was rejected by provider and immediately refunded ({$result['error']}). Your wallet balance was restored.";
            if ($isAjax) { Response::json(['success' => true, 'order_id' => $orderId, 'message' => $msg, 'refunded' => true]); return; }
            Session::setFlash('warning', $msg);
            Response::redirect('/orders/' . $orderId);
            return;
        }

        $successMsg = "Order #{$orderId} placed successfully! Total charge: \${$chargeFormatted}.";
        if ($isAjax) {
            Response::json([
                'success' => true,
                'order_id' => $orderId,
                'charge' => $result['charge'],
                'status' => $result['status'],
                'message' => $successMsg
            ]);
            return;
        }

        Session::setFlash('success', $successMsg);
        Response::redirect('/orders/' . $orderId);
    }

    public function index(Request $request): void
    {
        $user = AuthService::user();
        $page = max(1, (int)$request->input('page', 1));
        $status = trim($request->input('status', 'all'));
        $search = trim($request->input('search', ''));

        $filters = [
            'status' => $status,
            'search' => $search
        ];

        $ordersData = OrderService::getUserOrders($user['id'], $filters, $page, 12);
        $counts = \App\Models\Order::countByStatus($user['id']);

        View::render('user/orders/index', [
            'title' => 'Order History - ' . config('app.name'),
            'user' => $user,
            'orders' => $ordersData['data'],
            'pagination' => $ordersData,
            'currentStatus' => $status,
            'search' => $search,
            'counts' => $counts
        ], 'user');
    }

    public function show(Request $request, array $params): void
    {
        $user = AuthService::user();
        $orderId = (int)($params['id'] ?? 0);

        if ($orderId <= 0) {
            Response::redirect('/orders');
            return;
        }

        $order = OrderService::getOrderDetails($orderId, $user['id']);
        if (!$order) {
            Session::setFlash('error', 'Order not found or access denied.');
            Response::redirect('/orders');
            return;
        }

        View::render('user/orders/show', [
            'title' => "Order #{$orderId} Details - " . config('app.name'),
            'user' => $user,
            'order' => $order
        ], 'user');
    }

    public function apiServiceDetails(Request $request, array $params): void
    {
        $serviceId = (int)($params['id'] ?? 0);
        $service = Database::fetch(
            "SELECT s.*, c.name as category_name 
             FROM `services` s 
             JOIN `categories` c ON s.category_id = c.id 
             WHERE s.id = :id AND s.status = 'active' LIMIT 1",
            [':id' => $serviceId]
        );

        if (!$service) {
            Response::json(['success' => false, 'error' => 'Service not found'], 404);
            return;
        }

        Response::json([
            'success' => true,
            'service' => [
                'id' => $service['id'],
                'name' => $service['name'],
                'category_name' => $service['category_name'],
                'customer_price' => (float)($service['customer_price'] > 0 ? $service['customer_price'] : $service['price_per_k']),
                'min_quantity' => (int)$service['min_quantity'],
                'max_quantity' => (int)$service['max_quantity'],
                'description' => $service['description'] ?? '',
                'dripfeed' => (bool)$service['dripfeed'],
                'refill' => (bool)$service['refill'],
                'cancel_allowed' => (bool)$service['cancel_allowed']
            ]
        ]);
    }

    public function validateCoupon(Request $request): void
    {
        $user = AuthService::user();
        $code = trim((string)$request->input('code', ''));
        $serviceId = (int)$request->input('service_id', 0);
        $quantity = (int)$request->input('quantity', 0);

        if (empty($code)) {
            Response::json(['valid' => false, 'error' => 'Please enter a coupon code.'], 422);
            return;
        }

        $service = Database::fetch("SELECT * FROM `services` WHERE `id` = :id AND `status` = 'active' LIMIT 1", [':id' => $serviceId]);
        if (!$service) {
            Response::json(['valid' => false, 'error' => 'Please select an active service first.'], 422);
            return;
        }

        if ($quantity <= 0) {
            Response::json(['valid' => false, 'error' => 'Please enter a valid quantity first.'], 422);
            return;
        }

        $customerPrice = (float)($service['customer_price'] > 0 ? $service['customer_price'] : $service['price_per_k']);
        $charge = (float)PricingService::calculateOrderCharge($quantity, $customerPrice);

        $result = CouponService::validateCoupon($code, (int)$user['id'], $charge);
        if (!$result['valid']) {
            Response::json(['valid' => false, 'error' => $result['error']], 422);
            return;
        }

        Response::json([
            'valid' => true,
            'code' => $result['code'],
            'type' => $result['type'],
            'discount' => $result['discount'],
            'original_amount' => $result['original_amount'],
            'final_amount' => $result['final_amount'],
            'message' => "Coupon '{$result['code']}' applied! You save $" . number_format((float)$result['discount'], 4)
        ]);
    }
}
