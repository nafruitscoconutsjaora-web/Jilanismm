<?php

/**
 * 1-Minute SMM Order Status Sync Cron Job
 * 
 * Usage:
 * CLI: php cron/sync-orders.php
 * Web: https://domain.com/cron/sync-orders.php?key=YOUR_CRON_KEY (if configured)
 */

declare(strict_types=1);

// 1. CLI or Authorized Secret Verification
$isCli = (php_sapi_name() === 'cli' || defined('STDIN'));
$cronKeyParam = $_GET['key'] ?? null;

// Require bootstrap
require_once __DIR__ . '/../bootstrap/app.php';

use App\Services\OrderSyncService;
use App\Services\SettingsService;

$configuredCronKey = config('app.cron_key', SettingsService::get('cron_secret_key', 'smm_secure_cron_token_2026'));

if (!$isCli) {
    if (empty($cronKeyParam) || !hash_equals((string)$configuredCronKey, (string)$cronKeyParam)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized cron invocation.']);
        exit(1);
    }
}

// 2. Concurrency Lock: Prevent overlapping runs
$lockFile = dirname(__DIR__) . '/storage/cron_sync.lock';
$fp = fopen($lockFile, 'c+');

if (!$fp) {
    $msg = "[" . date('Y-m-d H:i:s') . "] CRON ERROR: Unable to access lock file {$lockFile}" . PHP_EOL;
    echo $msg;
    error_log($msg);
    exit(1);
}

// Non-blocking exclusive lock check
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $msg = "[" . date('Y-m-d H:i:s') . "] CRON SKIP: Previous order sync process is still active. Skipping run to prevent race conditions." . PHP_EOL;
    echo $msg;
    fclose($fp);
    exit(0);
}

// Register safe cleanup on script termination
register_shutdown_function(function () use ($fp) {
    if (is_resource($fp)) {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
});

// 3. Execution
$startTime = microtime(true);
$runDate = date('Y-m-d H:i:s');
echo "==========================================" . PHP_EOL;
echo "[{$runDate}] SMM Order Synchronization Cron Started" . PHP_EOL;

try {
    $stats = OrderSyncService::run(50);

    $duration = round(microtime(true) - $startTime, 3);
    echo "Sync Completed in {$duration}s" . PHP_EOL;
    echo "Checked: {$stats['total_checked']} | Updated: {$stats['updated']} | Completed: {$stats['completed']} | Partial: {$stats['partial']} | Cancelled: {$stats['cancelled']} | Failed: {$stats['failed']} | Skipped: {$stats['skipped']}" . PHP_EOL;

    if (!empty($stats['errors'])) {
        echo "Errors encountered (" . count($stats['errors']) . "):" . PHP_EOL;
        foreach ($stats['errors'] as $err) {
            echo " - {$err}" . PHP_EOL;
        }
    }
    echo "==========================================" . PHP_EOL;

    if (!$isCli) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'duration' => $duration,
            'stats' => $stats,
            'timestamp' => $runDate
        ]);
    }
} catch (\Throwable $e) {
    $msg = "[" . date('Y-m-d H:i:s') . "] CRON CRITICAL EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $msg;
    error_log($msg);
} finally {
    flock($fp, LOCK_UN);
    fclose($fp);
}
