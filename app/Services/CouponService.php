<?php

namespace App\Services;

use App\Database\Database;
use Throwable;

class CouponService
{
    /**
     * Validate a coupon against a customer order and calculate exact discount
     */
    public static function validateCoupon(string $code, int $userId, float $orderAmount): array
    {
        $cleanCode = strtoupper(trim($code));
        if (empty($cleanCode)) {
            return ['valid' => false, 'error' => 'Please enter a coupon code.'];
        }

        if (SettingsService::get('coupon_system', 'enabled') === 'disabled') {
            return ['valid' => false, 'error' => 'The coupon system is currently disabled.'];
        }

        if ($orderAmount <= 0) {
            return ['valid' => false, 'error' => 'Order amount must be greater than zero.'];
        }

        $coupon = Database::fetch(
            "SELECT * FROM `coupons` WHERE UPPER(`code`) = :code LIMIT 1",
            [':code' => $cleanCode]
        );

        if (!$coupon) {
            return ['valid' => false, 'error' => 'Invalid or expired coupon code.'];
        }

        if ($coupon['status'] !== 'active') {
            return ['valid' => false, 'error' => 'This coupon is no longer active.'];
        }

        $now = time();

        // Check start date
        if (!empty($coupon['start_date'])) {
            $startTs = strtotime($coupon['start_date']);
            if ($startTs && $now < $startTs) {
                return ['valid' => false, 'error' => 'This coupon is not yet active.'];
            }
        }

        // Check expiry date
        if (!empty($coupon['expires_at'])) {
            $expiryTs = strtotime($coupon['expires_at']);
            if ($expiryTs && $now > $expiryTs) {
                return ['valid' => false, 'error' => 'This coupon has expired.'];
            }
        }

        // Check minimum order amount
        $minOrder = (float)($coupon['min_order_amount'] > 0 ? $coupon['min_order_amount'] : ($coupon['min_deposit'] ?? 0));
        if ($minOrder > 0 && $orderAmount < $minOrder) {
            return [
                'valid' => false, 
                'error' => 'Minimum order amount for this coupon is $' . number_format($minOrder, 2) . '.'
            ];
        }

        // Check global usage limit
        $maxUses = (int)($coupon['max_uses'] ?? 0);
        $usedCount = (int)($coupon['used_count'] ?? 0);
        if ($maxUses > 0 && $usedCount >= $maxUses) {
            return ['valid' => false, 'error' => 'This coupon has reached its total usage limit.'];
        }

        // Check per-user limit
        $perUserLimit = (int)($coupon['per_user_limit'] ?? 1);
        if ($perUserLimit > 0) {
            $userUsage = Database::fetch(
                "SELECT COUNT(*) as cnt FROM `coupon_usage` WHERE `coupon_id` = :cid AND `user_id` = :uid",
                [':cid' => $coupon['id'], ':uid' => $userId]
            );
            $userUsedCount = (int)($userUsage['cnt'] ?? 0);
            if ($userUsedCount >= $perUserLimit) {
                return ['valid' => false, 'error' => 'You have already redeemed this coupon the maximum allowed times.'];
            }
        }

        // Calculate discount
        $rawDiscount = 0.0;
        $discountValue = (float)$coupon['amount'];

        if ($coupon['type'] === 'percentage') {
            $rawDiscount = ($discountValue / 100.0) * $orderAmount;
            $maxDiscount = !empty($coupon['max_discount']) ? (float)$coupon['max_discount'] : 0.0;
            if ($maxDiscount > 0 && $rawDiscount > $maxDiscount) {
                $rawDiscount = $maxDiscount;
            }
        } else {
            // Fixed amount
            $rawDiscount = min($discountValue, $orderAmount);
        }

        $discount = round(max(0.0, min($rawDiscount, $orderAmount)), 4);
        $finalPayable = round(max(0.0, $orderAmount - $discount), 4);

        return [
            'valid' => true,
            'coupon_id' => (int)$coupon['id'],
            'code' => $coupon['code'],
            'type' => $coupon['type'],
            'discount_value' => $discountValue,
            'discount' => $discount,
            'original_amount' => $orderAmount,
            'final_amount' => $finalPayable
        ];
    }

    /**
     * Atomically record coupon usage inside an existing or new transaction
     */
    public static function recordUsage(int $couponId, int $userId, int $orderId, float $discountReceived): bool
    {
        $hasOwnTx = false;
        if (!Database::inTransaction()) {
            Database::beginTransaction();
            $hasOwnTx = true;
        }

        try {
            // Lock coupon row for update
            $coupon = Database::fetch(
                "SELECT * FROM `coupons` WHERE `id` = :id FOR UPDATE",
                [':id' => $couponId]
            );

            if (!$coupon) {
                if ($hasOwnTx) Database::rollBack();
                return false;
            }

            // Increment usage counter
            Database::execute(
                "UPDATE `coupons` SET `used_count` = `used_count` + 1, `updated_at` = NOW() WHERE `id` = :id",
                [':id' => $couponId]
            );

            // Record in coupon_usage
            Database::execute(
                "INSERT INTO `coupon_usage` (`coupon_id`, `user_id`, `order_id`, `discount_received`, `used_at`)
                 VALUES (:cid, :uid, :oid, :disc, NOW())",
                [
                    ':cid' => $couponId,
                    ':uid' => $userId,
                    ':oid' => $orderId,
                    ':disc' => $discountReceived
                ]
            );

            if ($hasOwnTx) {
                Database::commit();
            }
            return true;
        } catch (Throwable $e) {
            if ($hasOwnTx) Database::rollBack();
            error_log("[CouponService::recordUsage] Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin: List coupons with usage summary
     */
    public static function getAllCoupons(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status']) && in_array($filters['status'], ['active', 'inactive'], true)) {
            $where[] = "c.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "c.code LIKE :search";
            $params[':search'] = '%' . trim($filters['search']) . '%';
        }

        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

        $sql = "SELECT c.*, 
                       COALESCE(SUM(cu.discount_received), 0) as total_discount_given,
                       MAX(cu.used_at) as last_used_at
                FROM `coupons` c
                LEFT JOIN `coupon_usage` cu ON c.id = cu.coupon_id
                {$whereClause}
                GROUP BY c.id
                ORDER BY c.id DESC";

        return Database::query($sql, $params);
    }

    public static function getCouponById(int $id): ?array
    {
        return Database::fetch(
            "SELECT c.*, 
                    COALESCE(SUM(cu.discount_received), 0) as total_discount_given,
                    MAX(cu.used_at) as last_used_at
             FROM `coupons` c
             LEFT JOIN `coupon_usage` cu ON c.id = cu.coupon_id
             WHERE c.id = :id
             GROUP BY c.id
             LIMIT 1",
            [':id' => $id]
        );
    }

    public static function createCoupon(array $data): array
    {
        $code = strtoupper(trim($data['code'] ?? ''));
        if (empty($code)) {
            return ['success' => false, 'error' => 'Coupon code is required.'];
        }

        // Validate uniqueness
        $existing = Database::fetch("SELECT `id` FROM `coupons` WHERE UPPER(`code`) = :code LIMIT 1", [':code' => $code]);
        if ($existing) {
            return ['success' => false, 'error' => 'A coupon with this code already exists.'];
        }

        $type = in_array($data['type'] ?? '', ['percentage', 'fixed'], true) ? $data['type'] : 'fixed';
        $amount = (float)($data['amount'] ?? $data['value'] ?? 0);
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Discount amount must be greater than zero.'];
        }

        if ($type === 'percentage' && $amount > 100) {
            return ['success' => false, 'error' => 'Percentage discount cannot exceed 100%.'];
        }

        $minOrder = max(0.0, (float)($data['min_order_amount'] ?? $data['min_deposit'] ?? 0));
        $maxDiscount = !empty($data['max_discount']) ? (float)$data['max_discount'] : null;
        $maxUses = max(0, (int)($data['max_uses'] ?? $data['usage_limit_total'] ?? 0));
        $perUserLimit = max(1, (int)($data['per_user_limit'] ?? $data['usage_limit_per_user'] ?? 1));
        $status = ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $startDate = !empty($data['start_date']) ? date('Y-m-d H:i:s', strtotime($data['start_date'])) : null;
        $expiresAt = !empty($data['expires_at']) ? date('Y-m-d H:i:s', strtotime($data['expires_at'])) : null;

        Database::execute(
            "INSERT INTO `coupons` (
                `code`, `type`, `amount`, `min_order_amount`, `max_discount`, 
                `start_date`, `max_uses`, `per_user_limit`, `used_count`, `expires_at`, `status`, `created_at`
            ) VALUES (
                :code, :type, :amt, :min_ord, :max_disc, 
                :start_d, :max_u, :per_u, 0, :exp, :st, NOW()
            )",
            [
                ':code' => $code,
                ':type' => $type,
                ':amt' => $amount,
                ':min_ord' => $minOrder,
                ':max_disc' => $maxDiscount,
                ':start_d' => $startDate,
                ':max_u' => $maxUses,
                ':per_u' => $perUserLimit,
                ':exp' => $expiresAt,
                ':st' => $status
            ]
        );

        return ['success' => true, 'coupon_id' => (int)Database::lastInsertId()];
    }

    public static function updateCoupon(int $id, array $data): array
    {
        $coupon = Database::fetch("SELECT * FROM `coupons` WHERE `id` = :id LIMIT 1", [':id' => $id]);
        if (!$coupon) {
            return ['success' => false, 'error' => 'Coupon not found.'];
        }

        $code = strtoupper(trim($data['code'] ?? ''));
        if (empty($code)) {
            return ['success' => false, 'error' => 'Coupon code is required.'];
        }

        // Uniqueness check excluding current
        $existing = Database::fetch("SELECT `id` FROM `coupons` WHERE UPPER(`code`) = :code AND `id` != :id LIMIT 1", [':code' => $code, ':id' => $id]);
        if ($existing) {
            return ['success' => false, 'error' => 'Another coupon with this code already exists.'];
        }

        $type = in_array($data['type'] ?? '', ['percentage', 'fixed'], true) ? $data['type'] : 'fixed';
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Discount amount must be greater than zero.'];
        }

        if ($type === 'percentage' && $amount > 100) {
            return ['success' => false, 'error' => 'Percentage discount cannot exceed 100%.'];
        }

        $minOrder = max(0.0, (float)($data['min_order_amount'] ?? 0));
        $maxDiscount = !empty($data['max_discount']) ? (float)$data['max_discount'] : null;
        $maxUses = max(0, (int)($data['max_uses'] ?? 0));
        $perUserLimit = max(1, (int)($data['per_user_limit'] ?? 1));
        $status = ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $startDate = !empty($data['start_date']) ? date('Y-m-d H:i:s', strtotime($data['start_date'])) : null;
        $expiresAt = !empty($data['expires_at']) ? date('Y-m-d H:i:s', strtotime($data['expires_at'])) : null;

        Database::execute(
            "UPDATE `coupons` SET 
                `code` = :code, `type` = :type, `amount` = :amt, 
                `min_order_amount` = :min_ord, `max_discount` = :max_disc,
                `start_date` = :start_d, `max_uses` = :max_u, `per_user_limit` = :per_u, 
                `expires_at` = :exp, `status` = :st, `updated_at` = NOW()
             WHERE `id` = :id",
            [
                ':id' => $id,
                ':code' => $code,
                ':type' => $type,
                ':amt' => $amount,
                ':min_ord' => $minOrder,
                ':max_disc' => $maxDiscount,
                ':start_d' => $startDate,
                ':max_u' => $maxUses,
                ':per_u' => $perUserLimit,
                ':exp' => $expiresAt,
                ':st' => $status
            ]
        );

        return ['success' => true];
    }

    public static function deleteCoupon(int $id): array
    {
        $coupon = Database::fetch("SELECT * FROM `coupons` WHERE `id` = :id LIMIT 1", [':id' => $id]);
        if (!$coupon) {
            return ['success' => false, 'error' => 'Coupon not found.'];
        }

        // Check if has usage history: soft deactivate if used, or delete if zero uses
        $usage = Database::fetch("SELECT COUNT(*) as cnt FROM `coupon_usage` WHERE `coupon_id` = :id", [':id' => $id]);
        if ((int)($usage['cnt'] ?? 0) > 0) {
            Database::execute("UPDATE `coupons` SET `status` = 'inactive', `updated_at` = NOW() WHERE `id` = :id", [':id' => $id]);
            return ['success' => true, 'message' => 'Coupon has existing usage records and was safely deactivated instead of deleted.'];
        }

        Database::execute("DELETE FROM `coupons` WHERE `id` = :id", [':id' => $id]);
        return ['success' => true, 'message' => 'Coupon deleted successfully.'];
    }

    public static function toggleStatus(int $id): array
    {
        $coupon = Database::fetch("SELECT `id`, `status` FROM `coupons` WHERE `id` = :id LIMIT 1", [':id' => $id]);
        if (!$coupon) {
            return ['success' => false, 'error' => 'Coupon not found.'];
        }

        $newStatus = $coupon['status'] === 'active' ? 'inactive' : 'active';
        Database::execute("UPDATE `coupons` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id", [':st' => $newStatus, ':id' => $id]);

        return ['success' => true, 'new_status' => $newStatus];
    }

    public static function getCouponUsageHistory(int $couponId, int $limit = 50): array
    {
        return Database::query(
            "SELECT cu.*, u.name as user_name, u.email as user_email, o.service_id, s.name as service_name
             FROM `coupon_usage` cu
             JOIN `users` u ON cu.user_id = u.id
             LEFT JOIN `orders` o ON cu.order_id = o.id
             LEFT JOIN `services` s ON o.service_id = s.id
             WHERE cu.coupon_id = :cid
             ORDER BY cu.used_at DESC
             LIMIT {$limit}",
            [':cid' => $couponId]
        );
    }
}
