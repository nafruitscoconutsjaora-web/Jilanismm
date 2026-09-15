<?php
/** @var array $admin */
/** @var array $orders */
/** @var array $pagination */
/** @var string $currentStatus */
/** @var string $search */
/** @var array $counts */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Orders Fulfillment Manager</h1>
            <p class="text-sm text-zinc-500 mt-1">Audit customer orders, upstream provider dispatch states, start counts, and financial balances.</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-2 border-b border-zinc-200/80 scrollbar-none">
        <?php
        $tabs = [
            'all' => 'All (' . ($counts['total'] ?? 0) . ')',
            'pending' => 'Pending (' . ($counts['pending'] ?? 0) . ')',
            'processing' => 'Processing (' . ($counts['processing'] ?? 0) . ')',
            'in_progress' => 'In Progress (' . ($counts['in_progress'] ?? 0) . ')',
            'completed' => 'Completed (' . ($counts['completed'] ?? 0) . ')',
            'partial' => 'Partial (' . ($counts['partial'] ?? 0) . ')',
            'cancelled' => 'Cancelled (' . ($counts['cancelled'] ?? 0) . ')',
            'failed' => 'Failed (' . ($counts['failed'] ?? 0) . ')',
            'refunded' => 'Refunded (' . ($counts['refunded'] ?? 0) . ')',
        ];
        ?>
        <?php foreach ($tabs as $key => $label): ?>
            <a href="/admin/orders?status=<?= $key ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors <?= $currentStatus === $key ? 'bg-zinc-900 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 border border-zinc-200/80' ?>">
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search Form -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs">
        <form action="/admin/orders" method="GET" class="flex items-center gap-3">
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <div class="relative flex-1">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by Order ID, username, link, service..." class="w-full text-xs rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl transition-colors">
                Filter
            </button>
            <?php if (!empty($search)): ?>
                <a href="/admin/orders?status=<?= e($currentStatus) ?>" class="text-xs text-zinc-500 hover:text-zinc-800 px-2 py-2">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-4 sm:px-6">Order ID</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Service</th>
                        <th class="py-3 px-4">Target Link</th>
                        <th class="py-3 px-4 text-right">Quantity</th>
                        <th class="py-3 px-4 text-right">Charge</th>
                        <th class="py-3 px-4">Provider / Ext ID</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center text-zinc-400">No orders found matching the filter criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <?php
                            $st = strtolower($o['status']);
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
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-zinc-900">
                                    #<?= (int)$o['id'] ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-zinc-900"><?= e($o['username'] ?? 'User #' . $o['user_id']) ?></div>
                                    <div class="text-[10px] text-zinc-400"><?= e($o['email'] ?? '') ?></div>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="truncate text-zinc-900 font-semibold" title="<?= e($o['service_name']) ?>"><?= e($o['service_name']) ?></div>
                                    <div class="text-[10px] text-zinc-400">ID #<?= (int)$o['service_id'] ?> (<?= e($o['category_name']) ?>)</div>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="truncate font-mono text-[11px] bg-zinc-100 px-2 py-0.5 rounded" title="<?= e($o['link']) ?>"><?= e($o['link']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-zinc-900">
                                    <?= number_format((int)$o['quantity']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-zinc-900">
                                    $<?= number_format((float)$o['charge'], 4) ?>
                                    <?php if ((float)($o['refunded_amount'] ?? 0) > 0): ?>
                                        <div class="text-[10px] text-amber-600 font-normal">-$<?= number_format((float)$o['refunded_amount'], 4) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs">
                                    <?php if (!empty($o['provider_name'])): ?>
                                        <div class="font-semibold text-zinc-900"><?= e($o['provider_name']) ?></div>
                                        <div class="text-[10px] text-zinc-400">Ext ID: #<?= e($o['provider_order_id'] ?? 'N/A') ?></div>
                                    <?php else: ?>
                                        <span class="text-zinc-400 italic">Manual</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border <?= $badgeClass ?>">
                                        <?= ucfirst(str_replace('_', ' ', $st)) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <a href="/admin/orders/<?= (int)$o['id'] ?>" class="px-2.5 py-1 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-lg transition-colors inline-block">
                                        Inspect &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($pagination['last_page'] ?? 1) > 1): ?>
            <div class="flex items-center justify-between px-6 py-4 border-t border-zinc-100 bg-zinc-50/50">
                <div class="text-xs text-zinc-500 font-medium">
                    Showing <?= count($orders) ?> of <?= $pagination['total'] ?> orders (Page <?= $pagination['page'] ?> of <?= $pagination['last_page'] ?>)
                </div>
                <div class="flex items-center space-x-1">
                    <?php if ($pagination['page'] > 1): ?>
                        <a href="/admin/orders?status=<?= e($currentStatus) ?>&page=<?= $pagination['page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($pagination['page'] < $pagination['last_page']): ?>
                        <a href="/admin/orders?status=<?= e($currentStatus) ?>&page=<?= $pagination['page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
