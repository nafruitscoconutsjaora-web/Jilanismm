<?php
/** @var array $user */
/** @var array $orders */
/** @var array $pagination */
/** @var string $currentStatus */
/** @var string $search */
/** @var array $counts */
?>

<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Top Title & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Orders History</h1>
            <p class="text-sm text-zinc-500 mt-1">Track the live progress, start counts, and execution states of your social media orders.</p>
        </div>
        <div>
            <a href="/order/new" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Place New Order
            </a>
        </div>
    </div>

    <!-- Status Filter Pills -->
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
            <a href="/orders?status=<?= $key ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors <?= $currentStatus === $key ? 'bg-zinc-900 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 border border-zinc-200/80' ?>">
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search Box -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs">
        <form action="/orders" method="GET" class="flex items-center gap-3">
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search orders by ID, target link, or service name..." class="w-full pl-9 pr-4 py-2.5 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-semibold rounded-xl transition-colors">
                Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="/orders?status=<?= e($currentStatus) ?>" class="px-3 py-2.5 text-sm font-medium text-zinc-500 hover:text-zinc-800">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Orders Cards Grid / Table -->
    <?php if (empty($orders)): ?>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-12 text-center shadow-xs">
            <div class="h-12 w-12 rounded-2xl bg-zinc-100 text-zinc-400 mx-auto flex items-center justify-center mb-4">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h3 class="text-base font-bold text-zinc-900">No orders found</h3>
            <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">There are no orders matching your selected status filter or search criteria.</p>
            <div class="mt-6">
                <a href="/order/new" class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-xs font-semibold rounded-xl hover:bg-rose-700">
                    Place a New Order
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3.5 px-4 sm:px-6">Order ID</th>
                            <th class="py-3.5 px-4">Service</th>
                            <th class="py-3.5 px-4">Target Link</th>
                            <th class="py-3.5 px-4 text-right">Quantity</th>
                            <th class="py-3.5 px-4 text-right">Charge</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Date & Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="py-4 px-4 sm:px-6 font-bold text-zinc-900">
                                    #<?= (int)$order['id'] ?>
                                </td>
                                <td class="py-4 px-4 max-w-xs">
                                    <div class="truncate text-zinc-900 font-semibold" title="<?= e($order['service_name']) ?>">
                                        <?= e($order['service_name']) ?>
                                    </div>
                                    <div class="text-xs text-zinc-400">Category: <?= e($order['category_name']) ?></div>
                                </td>
                                <td class="py-4 px-4 max-w-xs">
                                    <div class="truncate text-xs font-mono text-zinc-600 bg-zinc-100 px-2 py-1 rounded-md" title="<?= e($order['link']) ?>">
                                        <?= e($order['link']) ?>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-right font-mono text-zinc-900">
                                    <?= number_format((int)$order['quantity']) ?>
                                </td>
                                <td class="py-4 px-4 text-right font-bold text-zinc-900">
                                    $<?= number_format((float)$order['charge'], 4) ?>
                                    <?php if ((float)($order['refunded_amount'] ?? 0) > 0): ?>
                                        <div class="text-[10px] text-amber-600 font-normal">Refunded $<?= number_format((float)$order['refunded_amount'], 4) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4 text-center">
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
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border <?= $badgeClass ?>">
                                        <?= ucfirst(str_replace('_', ' ', $st)) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <div class="text-xs text-zinc-400 mb-1"><?= date('M j, Y H:i', strtotime($order['created_at'])) ?></div>
                                    <a href="/orders/<?= (int)$order['id'] ?>" class="inline-flex items-center text-xs font-bold text-rose-600 hover:text-rose-700">
                                        View Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if (($pagination['last_page'] ?? 1) > 1): ?>
                <div class="flex items-center justify-between px-6 py-4 border-t border-zinc-100 bg-zinc-50/50">
                    <div class="text-xs text-zinc-500 font-medium">
                        Showing <?= count($orders) ?> of <?= $pagination['total'] ?> orders (Page <?= $pagination['page'] ?> of <?= $pagination['last_page'] ?>)
                    </div>
                    <div class="flex items-center space-x-1">
                        <?php if ($pagination['page'] > 1): ?>
                            <a href="/orders?status=<?= e($currentStatus) ?>&page=<?= $pagination['page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50">Previous</a>
                        <?php endif; ?>
                        <?php if ($pagination['page'] < $pagination['last_page']): ?>
                            <a href="/orders?status=<?= e($currentStatus) ?>&page=<?= $pagination['page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
