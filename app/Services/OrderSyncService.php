<?php

namespace App\Services;

use App\Database\Database;
use App\Services\Provider\ProviderFactory;
use App\Services\WalletService;
use App\Services\NotificationService;
use Throwable;

class OrderSyncService
{
    /**
     * Run order synchronization batch
     *
     * @param int $batchSize Number of orders to check in this run
     * @return array Summary of processed orders
     */
    public static function run(int $batchSize = 50): array
    {
        $stats = [
            'total_checked' => 0,
            'updated' => 0,
            'completed' => 0,
            'partial' => 0,
            'cancelled' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        // Fetch active orders requiring provider check
        // Orders with a provider and remote order ID in active lifecycle
        $orders = Database::query(
            "SELECT o.*, s.name as service_name, p.id as provider_table_id, p.name as provider_name, p.status as provider_status
             FROM `orders` o
             JOIN `services` s ON o.service_id = s.id
             JOIN `providers` p ON o.provider_id = p.id
             WHERE o.provider_id IS NOT NULL 
               AND o.provider_order_id IS NOT NULL 
               AND o.provider_order_id != ''
               AND o.status IN ('pending', 'processing', 'in_progress')
             ORDER BY o.last_checked_at ASC, o.id ASC
             LIMIT {$batchSize}"
        );

        if (empty($orders)) {
            return $stats;
        }

        // Cache initialized provider adapters
        $providerAdapters = [];

        foreach ($orders as $order) {
            $stats['total_checked']++;
            $orderId = (int)$order['id'];
            $userId = (int)$order['user_id'];
            $providerId = (int)$order['provider_id'];
            $remoteOrderId = trim((string)$order['provider_order_id']);

            // Touch last_checked_at so it doesn't starve other orders if an error occurs
            Database::execute("UPDATE `orders` SET `last_checked_at` = NOW() WHERE `id` = :id", [':id' => $orderId]);

            if ($order['provider_status'] !== 'active') {
                $stats['skipped']++;
                continue;
            }

            try {
                if (!isset($providerAdapters[$providerId])) {
                    $providerRecord = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $providerId]);
                    if (!$providerRecord || empty($providerRecord['api_url']) || empty($providerRecord['api_key'])) {
                        $stats['skipped']++;
                        continue;
                    }
                    $providerAdapters[$providerId] = ProviderFactory::make($providerRecord);
                }

                $adapter = $providerAdapters[$providerId];
                $res = $adapter->getOrderStatus($remoteOrderId);

                if (!$res['success']) {
                    $err = $res['error'] ?? 'Unknown error checking order status';
                    $stats['errors'][] = "Order #{$orderId}: {$err}";
                    continue;
                }

                $remoteStatus = strtolower(trim((string)$res['status']));
                $startCount = isset($res['start_count']) ? (int)$res['start_count'] : (int)$order['start_count'];
                $remains = isset($res['remains']) ? (int)$res['remains'] : (int)$order['remains'];

                $currentStatus = $order['status'];
                $mappedStatus = self::mapRemoteStatus($remoteStatus);

                if (!$mappedStatus) {
                    // Provider returned unrecognized status string (e.g. empty or unknown)
                    continue;
                }

                // Update start_count and remains if changed
                if ($startCount !== (int)$order['start_count'] || $remains !== (int)$order['remains']) {
                    Database::execute(
                        "UPDATE `orders` SET `start_count` = :sc, `remains` = :rem WHERE `id` = :id",
                        [':sc' => $startCount, ':rem' => $remains, ':id' => $orderId]
                    );
                }

                if ($mappedStatus === $currentStatus) {
                    continue;
                }

                $stats['updated']++;

                // Handle status transitions
                switch ($mappedStatus) {
                    case 'completed':
                        Database::execute(
                            "UPDATE `orders` SET `status` = 'completed', `remains` = 0, `updated_at` = NOW() WHERE `id` = :id",
                            [':id' => $orderId]
                        );
                        self::recordHistory($orderId, $currentStatus, 'completed', 'provider', 'Provider reported order completed');
                        NotificationService::send(
                            $userId,
                            'Order Completed',
                            "Your order #{$orderId} for {$order['service_name']} has been completed successfully.",
                            'success'
                        );
                        $stats['completed']++;
                        break;

                    case 'in_progress':
                    case 'processing':
                        Database::execute(
                            "UPDATE `orders` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id",
                            [':st' => $mappedStatus, ':id' => $orderId]
                        );
                        self::recordHistory($orderId, $currentStatus, $mappedStatus, 'provider', "Provider updated status to {$mappedStatus}");
                        break;

                    case 'partial':
                        // Calculate partial refund based on remains vs total quantity
                        $totalQty = (int)$order['quantity'];
                        $charge = (float)$order['charge'];
                        $remainsCount = max(0, min($remains, $totalQty));
                        
                        $refundAmount = 0.0;
                        if ($totalQty > 0 && $remainsCount > 0) {
                            $ratio = $remainsCount / $totalQty;
                            $refundAmount = round($charge * $ratio, 4);
                        }

                        if ($refundAmount > 0.0001) {
                            WalletService::refundOrder(
                                $userId,
                                $orderId,
                                $refundAmount,
                                "Partial delivery ({$remainsCount} / {$totalQty} undelivered)",
                                'partial'
                            );
                        } else {
                            Database::execute(
                                "UPDATE `orders` SET `status` = 'partial', `updated_at` = NOW() WHERE `id` = :id",
                                [':id' => $orderId]
                            );
                        }

                        self::recordHistory($orderId, $currentStatus, 'partial', 'provider', "Provider marked partial. Remains: {$remainsCount}. Refund: \${$refundAmount}");
                        NotificationService::send(
                            $userId,
                            'Order Partially Completed',
                            "Your order #{$orderId} was partially completed. {$remainsCount} remains undelivered. " . ($refundAmount > 0 ? "A refund of \${$refundAmount} was credited to your wallet." : ""),
                            'info'
                        );
                        $stats['partial']++;
                        break;

                    case 'cancelled':
                        // Full refund for cancelled order
                        WalletService::refundOrder(
                            $userId,
                            $orderId,
                            null,
                            "Provider cancelled order",
                            'cancelled'
                        );
                        self::recordHistory($orderId, $currentStatus, 'cancelled', 'provider', 'Provider cancelled order');
                        NotificationService::send(
                            $userId,
                            'Order Cancelled',
                            "Your order #{$orderId} was cancelled by the provider. The full amount has been refunded to your wallet.",
                            'warning'
                        );
                        $stats['cancelled']++;
                        break;

                    case 'failed':
                        // Full refund for failed order
                        WalletService::refundOrder(
                            $userId,
                            $orderId,
                            null,
                            "Provider reported order failure",
                            'failed'
                        );
                        self::recordHistory($orderId, $currentStatus, 'failed', 'provider', 'Provider reported failure');
                        NotificationService::send(
                            $userId,
                            'Order Failed',
                            "Your order #{$orderId} failed to process at the provider. The full amount has been refunded to your wallet.",
                            'error'
                        );
                        $stats['failed']++;
                        break;
                }
            } catch (Throwable $e) {
                $stats['errors'][] = "Order #{$orderId} exception: " . $e->getMessage();
                error_log("[OrderSync] Exception on order #{$orderId}: " . $e->getMessage());
            }
        }

        return $stats;
    }

    private static function mapRemoteStatus(string $remote): ?string
    {
        $clean = strtolower(trim($remote));
        return match ($clean) {
            'completed', 'complete', 'finish', 'finished' => 'completed',
            'processing' => 'processing',
            'in progress', 'inprogress', 'in_progress', 'active' => 'in_progress',
            'pending' => 'pending',
            'partial' => 'partial',
            'canceled', 'cancelled' => 'cancelled',
            'fail', 'failed', 'error' => 'failed',
            default => null
        };
    }

    private static function recordHistory(int $orderId, string $oldStatus, string $newStatus, string $source, string $note): void
    {
        Database::execute(
            "INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `changed_by_type`, `source`, `note`, `reason`, `created_at`)
             VALUES (:oid, :old_st, :new_st, :src_type, 'cron_sync', :note, :note, NOW())",
            [
                ':oid' => $orderId,
                ':old_st' => $oldStatus,
                ':new_st' => $newStatus,
                ':src_type' => $source === 'provider' ? 'provider' : 'system',
                ':note' => $note
            ]
        );
    }
}
