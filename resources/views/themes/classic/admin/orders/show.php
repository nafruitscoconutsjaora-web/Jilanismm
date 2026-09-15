<?php
/** @var array $admin */
/** @var array $order */
/** @var array $apiLogs */
?>

<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="/admin/orders" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-900">
            &larr; Back to Orders Manager
        </a>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-zinc-400">Order Placed:</span>
            <span class="text-xs font-mono font-bold text-zinc-800"><?= date('M j, Y H:i:s', strtotime($order['created_at'])) ?></span>
        </div>
    </div>

    <!-- Main Order Details Card -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-100">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-extrabold text-zinc-900">Order #<?= (int)$order['id'] ?></h1>
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
                <div class="text-xs text-zinc-500 mt-1">
                    Customer: <strong class="text-zinc-900"><?= e($order['username'] ?? 'User #' . $order['user_id']) ?></strong> (<?= e($order['email'] ?? '') ?>)
                </div>
            </div>

            <div class="text-right">
                <div class="text-xs uppercase tracking-wider text-zinc-400 font-semibold">Financial Ledger</div>
                <div class="text-2xl font-bold text-zinc-900">$<?= number_format((float)$order['charge'], 4) ?> <span class="text-xs font-medium text-zinc-400">USD</span></div>
                <?php if ((float)($order['refunded_amount'] ?? 0) > 0): ?>
                    <div class="text-xs font-bold text-purple-700 mt-0.5">Refunded: $<?= number_format((float)$order['refunded_amount'], 4) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6 text-xs">
            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-100">
                <div class="text-zinc-400 font-semibold uppercase">Service</div>
                <div class="text-zinc-900 font-bold mt-1"><?= e($order['service_name']) ?></div>
                <div class="text-zinc-500 mt-0.5">Category: <?= e($order['category_name']) ?></div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-100">
                <div class="text-zinc-400 font-semibold uppercase">Quantity & Progress</div>
                <div class="text-zinc-900 font-bold font-mono mt-1">Total: <?= number_format((int)$order['quantity']) ?></div>
                <div class="text-zinc-500 font-mono mt-0.5">Remains: <?= number_format((int)$order['remains']) ?></div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-100">
                <div class="text-zinc-400 font-semibold uppercase">Counters</div>
                <div class="text-zinc-900 font-bold font-mono mt-1">Start Count: <?= number_format((int)$order['start_count']) ?></div>
                <div class="text-zinc-500 mt-0.5">Execution: <?= e($order['service_type'] ?? 'standard') ?></div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-xl border border-zinc-100">
                <div class="text-zinc-400 font-semibold uppercase">Provider Dispatch</div>
                <div class="text-zinc-900 font-bold mt-1"><?= e($order['provider_name'] ?? 'Manual / None') ?></div>
                <div class="text-zinc-500 font-mono mt-0.5">Ext ID: #<?= e($order['provider_order_id'] ?? 'N/A') ?></div>
            </div>
        </div>

        <!-- Target Destination Box -->
        <div class="mt-6 bg-zinc-50 p-4 rounded-xl border border-zinc-100">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold mb-1">Target Link</div>
            <div class="font-mono text-xs text-zinc-900 break-all select-all font-semibold">
                <?= e($order['link']) ?>
            </div>
        </div>
    </div>

    <!-- Admin Actions Row: Update Status & Issue Refund -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Update Status Box -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
            <h2 class="text-sm font-bold text-zinc-900 pb-3 border-b border-zinc-100 uppercase tracking-wider">Update Order Status</h2>
            <form action="/admin/orders/status/<?= (int)$order['id'] ?>" method="POST" class="space-y-4 mt-4 text-xs">
                <?= csrf_field() ?>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Status</label>
                    <select name="status" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs">
                        <?php foreach (['pending', 'processing', 'in_progress', 'completed', 'partial', 'cancelled', 'failed'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= $order['status'] === $opt ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $opt)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Start Count</label>
                        <input type="number" name="start_count" value="<?= (int)$order['start_count'] ?>" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Remains</label>
                        <input type="number" name="remains" value="<?= (int)$order['remains'] ?>" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Admin Audit Note</label>
                    <input type="text" name="note" placeholder="Reason or update details..." class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs">
                </div>

                <button type="submit" class="w-full py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white font-semibold rounded-xl transition-colors">
                    Save Status & Counts
                </button>
            </form>
        </div>

        <!-- Issue Refund Box -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
            <h2 class="text-sm font-bold text-zinc-900 pb-3 border-b border-zinc-100 uppercase tracking-wider">Manual / Partial Refund</h2>
            <form action="/admin/orders/refund/<?= (int)$order['id'] ?>" method="POST" class="space-y-4 mt-4 text-xs" onsubmit="return confirm('Refund this amount directly to user wallet?')">
                <?= csrf_field() ?>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block font-semibold text-zinc-700">Refund Amount (USD)</label>
                        <span class="text-[11px] text-zinc-400">Full charge: $<?= number_format((float)$order['charge'], 4) ?></span>
                    </div>
                    <input type="number" name="refund_amount" step="0.0001" max="<?= (float)$order['charge'] ?>" value="<?= (float)$order['charge'] ?>" required class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs font-mono font-bold">
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Refund Reason</label>
                    <input type="text" name="reason" placeholder="e.g. Upstream provider failed to deliver, partial cancellation" required class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs">
                </div>

                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-[11px]">
                    This will immediately credit the customer's wallet ledger with a <code>refund</code> transaction.
                </div>

                <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl transition-colors">
                    Execute Ledger Refund
                </button>
            </form>
        </div>
    </div>

    <!-- Status History Timeline Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
        <h2 class="text-sm font-bold text-zinc-900 mb-4 uppercase tracking-wider">Status Transition History</h2>
        <?php if (empty($order['history'])): ?>
            <p class="text-xs text-zinc-400">No transitions recorded.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-2.5 px-4">Timestamp</th>
                            <th class="py-2.5 px-4">Old Status</th>
                            <th class="py-2.5 px-4">New Status</th>
                            <th class="py-2.5 px-4">Source</th>
                            <th class="py-2.5 px-4">Note / Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <?php foreach ($order['history'] as $h): ?>
                            <tr>
                                <td class="py-2.5 px-4 font-mono text-zinc-400"><?= date('Y-m-d H:i:s', strtotime($h['created_at'])) ?></td>
                                <td class="py-2.5 px-4 text-zinc-500"><?= e($h['old_status']) ?></td>
                                <td class="py-2.5 px-4 font-bold text-zinc-900"><?= e($h['new_status']) ?></td>
                                <td class="py-2.5 px-4 font-mono text-[11px]"><?= e($h['source']) ?></td>
                                <td class="py-2.5 px-4 text-zinc-700"><?= e($h['note'] ?: $h['reason']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Provider API Logs -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
        <h2 class="text-sm font-bold text-zinc-900 mb-4 uppercase tracking-wider">Provider API Transmission Logs (Last 10)</h2>
        <?php if (empty($apiLogs)): ?>
            <p class="text-xs text-zinc-400">No external provider API communications recorded for this order.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($apiLogs as $log): ?>
                    <div class="border border-zinc-200 rounded-xl p-3 text-xs bg-zinc-50/50">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-zinc-900"><?= e($log['action']) ?></span>
                                <span class="font-mono text-[10px] text-zinc-500">(HTTP <?= (int)$log['http_code'] ?> - <?= (int)$log['duration_ms'] ?>ms)</span>
                            </div>
                            <span class="font-mono text-zinc-400 text-[11px]"><?= date('M j, Y H:i:s', strtotime($log['created_at'])) ?></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 font-mono text-[11px]">
                            <div>
                                <span class="text-zinc-400 font-semibold block text-[10px]">REQUEST</span>
                                <pre class="bg-white p-2 rounded border border-zinc-200 overflow-x-auto text-zinc-700"><?= e($log['request_payload'] ?? '') ?></pre>
                            </div>
                            <div>
                                <span class="text-zinc-400 font-semibold block text-[10px]">RESPONSE</span>
                                <pre class="bg-white p-2 rounded border border-zinc-200 overflow-x-auto text-zinc-700"><?= e($log['response_payload'] ?? '') ?></pre>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
