<?php

namespace App\Services;

use App\Database\Database;
use App\Services\Provider\ProviderFactory;

class OrderService
{
    /**
     * Create and process a new customer order with strict server-side validation,
     * atomic wallet deduction, safe provider submission, and automated failure refund.
     */
    public static function createOrder(int $userId, int $serviceId, string $link, int $quantity, ?string $couponCode = null): array
    {
        // 1. Reload service strictly from database
        $service = Database::fetch(
            "SELECT s.*, c.name as category_name, c.status as category_status 
             FROM `services` s 
             JOIN `categories` c ON s.category_id = c.id 
             WHERE s.id = :id LIMIT 1",
            [':id' => $serviceId]
        );

        if (!$service) {
            return ['success' => false, 'error' => 'Selected service was not found.'];
        }

        if ($service['status'] !== 'active') {
            return ['success' => false, 'error' => 'This service is currently inactive.'];
        }

        if ($service['category_status'] !== 'active') {
            return ['success' => false, 'error' => 'This service category is currently unavailable.'];
        }

        // 2. Validate Link
        $cleanLink = trim($link);
        if (empty($cleanLink)) {
            return ['success' => false, 'error' => 'Please provide a valid destination link or username.'];
        }

        // 3. Validate Quantity limits
        $min = (int)$service['min_quantity'];
        $max = (int)$service['max_quantity'];
        if ($quantity < $min) {
            return ['success' => false, 'error' => "Minimum quantity for this service is " . number_format($min) . "."];
        }
        if ($quantity > $max) {
            return ['success' => false, 'error' => "Maximum quantity for this service is " . number_format($max) . "."];
        }

        // 4. Server-Authoritative Price and Charge Calculation
        $customerPrice = (float)($service['customer_price'] > 0 ? $service['customer_price'] : $service['price_per_k']);
        $charge = PricingService::calculateOrderCharge($quantity, $customerPrice);
        $floatCharge = (float)$charge;

        // 4b. Coupon Validation & Discount Calculation
        $couponId = null;
        $discountAmount = 0.0;
        $payableAmount = $floatCharge;

        $cleanCouponCode = trim((string)$couponCode);
        if (!empty($cleanCouponCode)) {
            $couponCheck = CouponService::validateCoupon($cleanCouponCode, $userId, $floatCharge);
            if (!$couponCheck['valid']) {
                return ['success' => false, 'error' => $couponCheck['error']];
            }
            $couponId = (int)$couponCheck['coupon_id'];
            $discountAmount = (float)$couponCheck['discount'];
            $payableAmount = (float)$couponCheck['final_amount'];
        }

        // 5. Begin Database Transaction for Wallet & Internal Order Creation
        Database::beginTransaction();
        try {
            // Deduct payable amount from wallet with row locking
            $deduction = WalletService::deductForOrder(
                $userId,
                $payableAmount,
                'ORDER-PENDING',
                "Order for {$service['name']} (Qty: {$quantity}" . ($discountAmount > 0 ? ", Discount: \${$discountAmount}" : "") . ")"
            );

            if (!$deduction['success']) {
                Database::rollBack();
                return ['success' => false, 'error' => $deduction['error'] ?? 'Insufficient balance.'];
            }

            // Create Order in pending state
            Database::execute(
                "INSERT INTO `orders` (
                    `user_id`, `service_id`, `link`, `quantity`, `charge`, `coupon_id`, `discount_amount`, `start_count`, `remains`,
                    `provider_id`, `status`, `created_at`
                 ) VALUES (
                    :uid, :sid, :link, :qty, :charge, :cid, :disc, 0, :remains,
                    :pid, 'pending', NOW()
                 )",
                [
                    ':uid' => $userId,
                    ':sid' => $service['id'],
                    ':link' => $cleanLink,
                    ':qty' => $quantity,
                    ':charge' => $payableAmount,
                    ':cid' => $couponId,
                    ':disc' => $discountAmount,
                    ':remains' => $quantity,
                    ':pid' => $service['provider_id'] ?: null
                ]
            );

            $orderId = (int)Database::lastInsertId();

            // Record Coupon Usage
            if ($couponId !== null && $discountAmount > 0) {
                CouponService::recordUsage($couponId, $userId, $orderId, $discountAmount);
            }

            // Record Initial Order Status History
            Database::execute(
                "INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `changed_by_type`, `source`, `note`, `reason`, `created_at`)
                 VALUES (:oid, 'created', 'pending', 'user', 'order_form', 'Order created by customer', 'Initial order placement', NOW())",
                [':oid' => $orderId]
            );

            // Update transaction reference with actual order ID
            Database::execute(
                "UPDATE `wallet_transactions` SET `reference_id` = :ref WHERE `user_id` = :uid AND `reference_id` = 'ORDER-PENDING' ORDER BY `id` DESC LIMIT 1",
                [':ref' => 'ORDER-' . $orderId, ':uid' => $userId]
            );

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            return ['success' => false, 'error' => 'Order processing error: ' . $e->getMessage()];
        }

        // 6. Provider Dispatch (Outside financial transaction to protect wallet consistency)
        $finalStatus = 'pending';
        $providerOrderId = null;
        $providerError = null;

        if (!empty($service['provider_id']) && !empty($service['provider_service_id'])) {
            try {
                $provider = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $service['provider_id']]);
                if ($provider && $provider['status'] === 'active') {
                    $adapter = ProviderFactory::make($provider);
                    $providerResponse = $adapter->createOrder([
                        'service' => $service['provider_service_id'],
                        'link' => $cleanLink,
                        'quantity' => $quantity,
                        'internal_order_id' => $orderId
                    ]);

                    if ($providerResponse['success'] && !empty($providerResponse['order_id'])) {
                        $providerOrderId = $providerResponse['order_id'];
                        $finalStatus = 'processing';

                        Database::execute(
                            "UPDATE `orders` SET `provider_order_id` = :poid, `status` = 'processing', `updated_at` = NOW() WHERE `id` = :id",
                            [':poid' => $providerOrderId, ':id' => $orderId]
                        );

                        Database::execute(
                            "INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `changed_by_type`, `source`, `note`, `reason`, `created_at`)
                             VALUES (:oid, 'pending', 'processing', 'provider', 'provider_api', :note, 'Dispatched to provider', NOW())",
                            [':oid' => $orderId, ':note' => "Provider assigned Order #{$providerOrderId}"]
                        );
                    } else {
                        // Provider Rejected: Record failure and execute automated refund
                        $providerError = $providerResponse['error'] ?? 'Provider rejected the request';
                        $finalStatus = 'failed';

                        Database::execute(
                            "UPDATE `orders` SET `status` = 'failed', `error_message` = :err, `updated_at` = NOW() WHERE `id` = :id",
                            [':err' => $providerError, ':id' => $orderId]
                        );

                        Database::execute(
                            "INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `changed_by_type`, `source`, `note`, `reason`, `created_at`)
                             VALUES (:oid, 'pending', 'failed', 'provider', 'provider_api', :note, :reason, NOW())",
                            [':oid' => $orderId, ':note' => 'Provider error: ' . $providerError, ':reason' => 'Automated failure detection']
                        );

                        // AUTOMATED REFUND FOR FAILED ORDER
                        WalletService::refundOrder($userId, $orderId, $payableAmount, "Automatic refund: Provider rejected order ({$providerError})");
                        $finalStatus = 'refunded';
                    }
                }
            } catch (\Throwable $e) {
                // Provider connection exception
                $providerError = $e->getMessage();
                Database::execute(
                    "UPDATE `orders` SET `status` = 'failed', `error_message` = :err, `updated_at` = NOW() WHERE `id` = :id",
                    [':err' => $providerError, ':id' => $orderId]
                );
                WalletService::refundOrder($userId, $orderId, $payableAmount, "Automatic refund: Provider communication failure ({$providerError})");
                $finalStatus = 'refunded';
            }
        }

        return [
            'success' => true,
            'order_id' => $orderId,
            'charge' => $payableAmount,
            'discount' => $discountAmount,
            'status' => $finalStatus,
            'provider_order_id' => $providerOrderId,
            'error' => $providerError
        ];
    }

    /**
     * Update order status with transition validation, logging, and refund handling
     */
    public static function updateOrderStatus(int $orderId, string $newStatus, string $source = 'admin', ?int $changedById = null, ?string $reason = null): array
    {
        $order = Database::fetch("SELECT * FROM `orders` WHERE `id` = :id LIMIT 1", [':id' => $orderId]);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        $oldStatus = $order['status'];
        $normalizedNewStatus = strtolower(trim($newStatus));

        if ($normalizedNewStatus === 'canceled') {
            $normalizedNewStatus = 'cancelled';
        }

        $allowedStatuses = ['pending', 'processing', 'in_progress', 'completed', 'partial', 'cancelled', 'failed', 'refunded'];
        if (!in_array($normalizedNewStatus, $allowedStatuses, true)) {
            return ['success' => false, 'error' => 'Invalid status value specified.'];
        }

        if ($oldStatus === $normalizedNewStatus) {
            return ['success' => true, 'message' => 'Status is already ' . $newStatus];
        }

        Database::execute(
            "UPDATE `orders` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id",
            [':st' => $normalizedNewStatus, ':id' => $orderId]
        );

        Database::execute(
            "INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `changed_by_type`, `source`, `changed_by_id`, `note`, `reason`, `created_at`)
             VALUES (:oid, :old_st, :new_st, :ch_type, :source, :ch_id, :note, :reason, NOW())",
            [
                ':oid' => $orderId,
                ':old_st' => $oldStatus,
                ':new_st' => $normalizedNewStatus,
                ':ch_type' => $source === 'admin' ? 'admin' : ($source === 'provider' ? 'provider' : 'system'),
                ':source' => $source,
                ':ch_id' => $changedById,
                ':note' => "Status changed from {$oldStatus} to {$normalizedNewStatus}",
                ':reason' => $reason ?: 'Status updated via ' . $source
            ]
        );

        // If status moved to cancelled or refunded by admin, refund user if not yet refunded
        if (in_array($normalizedNewStatus, ['cancelled', 'refunded'], true)) {
            $alreadyRefunded = (float)($order['refunded_amount'] ?? 0);
            $charge = (float)$order['charge'];
            if ($alreadyRefunded < $charge) {
                WalletService::refundOrder((int)$order['user_id'], $orderId, null, $reason ?: "Order {$normalizedNewStatus} by {$source}");
            }
        }

        return ['success' => true, 'old_status' => $oldStatus, 'new_status' => $normalizedNewStatus];
    }

    /**
     * Get paginated customer orders with search and status filtering
     */
    public static function getUserOrders(int $userId, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $params = [':uid' => $userId];
        $whereClauses = ["o.user_id = :uid"];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $whereClauses[] = "o.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $whereClauses[] = "(o.id = :search_id OR o.link LIKE :search_link OR s.name LIKE :search_name)";
            $params[':search_id'] = is_numeric($filters['search']) ? (int)$filters['search'] : 0;
            $params[':search_link'] = '%' . trim($filters['search']) . '%';
            $params[':search_name'] = '%' . trim($filters['search']) . '%';
        }

        $where = implode(' AND ', $whereClauses);
        $offset = max(0, ($page - 1) * $perPage);

        $countSql = "SELECT COUNT(*) as cnt FROM `orders` o JOIN `services` s ON o.service_id = s.id WHERE {$where}";
        $total = Database::fetch($countSql, $params)['cnt'] ?? 0;

        $sql = "SELECT o.*, s.name as service_name, c.name as category_name 
                FROM `orders` o 
                JOIN `services` s ON o.service_id = s.id 
                JOIN `categories` c ON s.category_id = c.id 
                WHERE {$where} 
                ORDER BY o.id DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $orders = Database::query($sql, $params);

        return [
            'data' => $orders,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int)ceil($total / $perPage)
        ];
    }

    /**
     * Get order details with security check ensuring user ownership if userId provided
     */
    public static function getOrderDetails(int $orderId, ?int $userId = null): ?array
    {
        $params = [':id' => $orderId];
        $userCheck = "";
        if ($userId !== null) {
            $userCheck = "AND o.user_id = :uid";
            $params[':uid'] = $userId;
        }

        $order = Database::fetch(
            "SELECT o.*, s.name as service_name, s.service_type, c.name as category_name, u.email as user_email, u.name as user_name,
                    p.name as provider_name
             FROM `orders` o 
             JOIN `services` s ON o.service_id = s.id 
             JOIN `categories` c ON s.category_id = c.id 
             JOIN `users` u ON o.user_id = u.id
             LEFT JOIN `providers` p ON o.provider_id = p.id
             WHERE o.id = :id {$userCheck} LIMIT 1",
            $params
        );

        if (!$order) {
            return null;
        }

        // Get status history
        $history = Database::query(
            "SELECT * FROM `order_status_history` WHERE `order_id` = :id ORDER BY `id` ASC",
            [':id' => $orderId]
        );

        $order['history'] = $history;
        return $order;
    }

    /**
     * Get admin orders listing with advanced filtering
     */
    public static function getAdminOrders(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $params = [];
        $whereClauses = ["1=1"];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $whereClauses[] = "o.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $whereClauses[] = "o.user_id = :uid";
            $params[':uid'] = (int)$filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $whereClauses[] = "(o.id = :search_id OR o.link LIKE :search_link OR u.email LIKE :search_email OR s.name LIKE :search_name OR o.provider_order_id = :search_poid)";
            $params[':search_id'] = is_numeric($filters['search']) ? (int)$filters['search'] : 0;
            $params[':search_poid'] = trim($filters['search']);
            $params[':search_link'] = '%' . trim($filters['search']) . '%';
            $params[':search_email'] = '%' . trim($filters['search']) . '%';
            $params[':search_name'] = '%' . trim($filters['search']) . '%';
        }

        $where = implode(' AND ', $whereClauses);
        $offset = max(0, ($page - 1) * $perPage);

        $countSql = "SELECT COUNT(*) as cnt FROM `orders` o 
                     JOIN `services` s ON o.service_id = s.id 
                     JOIN `users` u ON o.user_id = u.id 
                     WHERE {$where}";
        $total = Database::fetch($countSql, $params)['cnt'] ?? 0;

        $sql = "SELECT o.*, s.name as service_name, u.email as user_email, u.name as user_name, p.name as provider_name 
                FROM `orders` o 
                JOIN `services` s ON o.service_id = s.id 
                JOIN `users` u ON o.user_id = u.id 
                LEFT JOIN `providers` p ON o.provider_id = p.id 
                WHERE {$where} 
                ORDER BY o.id DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $orders = Database::query($sql, $params);

        return [
            'data' => $orders,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int)ceil($total / $perPage)
        ];
    }
}
