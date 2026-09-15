<?php
/** @var array $user */
/** @var array $order */
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Top Action Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="/orders" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-900 transition-colors">
            &larr; Back to Order History
        </a>
        <a href="/order/new?service_id=<?= (int)$order['service_id'] ?>" class="inline-flex items-center px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl transition-colors">
            Re-order This Service &rarr;
        </a>
    </div>

    <!-- Order Header Card -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-100">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-extrabold tracking-tight text-zinc-900">Order #<?= (int)$order['id'] ?></h1>
                    <?php
                    $st = strtolower($order['status']);
                    $badgeClass = match ($st) {
                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'processing', 'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'partial' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                        'refunded' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'cancelled', 'canceled' => 'bg-zinc-100 text-zinc-600 border-zinc-200',
                        'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-zinc-100 text-zinc-700 border-zinc-200'
                    };
                    ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border <?= $badgeClass ?>">
                        <?= ucfirst(str_replace('_', ' ', $st)) ?>
                    </span>
                </div>
                <p class="text-xs text-zinc-400 mt-1">Placed on <?= date('F j, Y \a\t H:i:s T', strtotime($order['created_at'])) ?></p>
            </div>

            <div class="text-right">
                <div class="text-xs uppercase tracking-wider text-zinc-400 font-semibold">Total Charge</div>
                <div class="text-2xl font-bold text-zinc-900">$<?= number_format((float)$order['charge'], 4) ?> <span class="text-xs font-medium text-zinc-400">USD</span></div>
            </div>
        </div>

        <!-- Refund Alert Banner (if applicable) -->
        <?php if ((float)($order['refunded_amount'] ?? 0) > 0 || in_array($st, ['refunded', 'failed'], true)): ?>
            <div class="mt-6 rounded-xl border border-purple-200 bg-purple-50/80 p-4 text-xs text-purple-900 flex items-start space-x-3">
                <svg class="h-5 w-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <span class="font-bold">Automatic Wallet Refund Applied</span>
                    <p class="mt-0.5 text-purple-800">
                        An amount of <strong>$<?= number_format((float)($order['refunded_amount'] > 0 ? $order['refunded_amount'] : $order['charge']), 4) ?></strong> was restored to your available wallet balance.
                        <?php if (!empty($order['error_message'])): ?>
                            <br><span class="text-purple-700 italic">Reason: <?= e($order['error_message']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Key Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-100">
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Service</div>
                <div class="text-sm font-bold text-zinc-900 mt-1"><?= e($order['service_name']) ?></div>
                <div class="text-xs text-zinc-500 mt-0.5"><?= e($order['category_name']) ?></div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-100">
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Quantity</div>
                <div class="text-sm font-bold text-zinc-900 mt-1 font-mono"><?= number_format((int)$order['quantity']) ?></div>
                <div class="text-xs text-zinc-500 mt-0.5">Remains: <?= number_format((int)$order['remains']) ?></div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-100">
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Start Count</div>
                <div class="text-sm font-bold text-zinc-900 mt-1 font-mono"><?= number_format((int)$order['start_count']) ?></div>
                <div class="text-xs text-zinc-500 mt-0.5">Initial Counter</div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-100">
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Last Updated</div>
                <div class="text-xs font-bold text-zinc-800 mt-1"><?= date('M j, Y H:i:s', strtotime($order['updated_at'] ?? $order['created_at'])) ?></div>
                <div class="text-[10px] text-zinc-400 mt-0.5">Auto-synced</div>
            </div>
        </div>

        <!-- Target Link Box -->
        <div class="mt-6 bg-zinc-50 p-4 rounded-xl border border-zinc-100">
            <div class="text-xs uppercase tracking-wider text-zinc-400 font-semibold mb-1">Target Destination URL / Username</div>
            <div class="font-mono text-sm text-zinc-900 break-all select-all">
                <?= e($order['link']) ?>
            </div>
        </div>
    </div>

    <!-- Status History Timeline -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 sm:p-8 shadow-xs">
        <h2 class="text-base font-bold text-zinc-900 mb-6 flex items-center space-x-2">
            <svg class="h-5 w-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Order Timeline & Execution History</span>
        </h2>

        <?php if (empty($order['history'])): ?>
            <p class="text-xs text-zinc-500">No status transitions recorded yet.</p>
        <?php else: ?>
            <div class="relative pl-6 border-l-2 border-zinc-200 space-y-6">
                <?php foreach ($order['history'] as $h): ?>
                    <div class="relative">
                        <!-- Circle dot -->
                        <div class="absolute -left-[31px] top-1 h-3.5 w-3.5 rounded-full bg-rose-600 border-2 border-white shadow-xs"></div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <span class="text-sm font-bold text-zinc-900">
                                <?= ucfirst($h['new_status']) ?>
                                <?php if (!empty($h['old_status']) && $h['old_status'] !== 'created'): ?>
                                    <span class="text-xs font-normal text-zinc-400">(from <?= $h['old_status'] ?>)</span>
                                <?php endif; ?>
                            </span>
                            <span class="text-xs text-zinc-400 font-mono"><?= date('M j, Y H:i:s', strtotime($h['created_at'])) ?></span>
                        </div>
                        <?php if (!empty($h['note'])): ?>
                            <p class="text-xs text-zinc-600 mt-1"><?= e($h['note']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($h['reason'])): ?>
                            <div class="text-[11px] text-zinc-400 mt-0.5 italic">Reason: <?= e($h['reason']) ?> (source: <?= e($h['source']) ?>)</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
