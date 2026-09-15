<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Order History</h1>
            <p class="mt-1 text-sm text-zinc-500">Track and monitor status progression for all campaigns.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-2">
            <a href="/user/services" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-rose-700">
                + New Order
            </a>
        </div>
    </div>

    <!-- Status Filter Pills -->
    <div class="flex flex-wrap gap-2 text-xs">
        <a href="/orders" class="rounded-lg px-3 py-1.5 font-medium <?= $currentStatus === 'all' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:bg-zinc-50 border border-zinc-200' ?>">All</a>
        <a href="/orders?status=pending" class="rounded-lg px-3 py-1.5 font-medium <?= $currentStatus === 'pending' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:bg-zinc-50 border border-zinc-200' ?>">Pending</a>
        <a href="/orders?status=in_progress" class="rounded-lg px-3 py-1.5 font-medium <?= $currentStatus === 'in_progress' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:bg-zinc-50 border border-zinc-200' ?>">In Progress</a>
        <a href="/orders?status=completed" class="rounded-lg px-3 py-1.5 font-medium <?= $currentStatus === 'completed' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:bg-zinc-50 border border-zinc-200' ?>">Completed</a>
        <a href="/orders?status=canceled" class="rounded-lg px-3 py-1.5 font-medium <?= $currentStatus === 'canceled' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:bg-zinc-50 border border-zinc-200' ?>">Canceled</a>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white shadow-xs">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Service</th>
                        <th class="px-5 py-3.5">Target Link</th>
                        <th class="px-5 py-3.5">Quantity</th>
                        <th class="px-5 py-3.5">Charge</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    <?php foreach ($orders as $ord): ?>
                        <tr class="hover:bg-zinc-50/80">
                            <td class="px-5 py-3.5 font-mono text-xs text-zinc-400">#<?= e($ord['id']) ?></td>
                            <td class="px-5 py-3.5 font-semibold text-zinc-900"><?= e($ord['service_name'] ?? 'Service #' . $ord['service_id']) ?></td>
                            <td class="px-5 py-3.5 font-mono text-xs text-rose-600 truncate max-w-xs"><?= e($ord['link']) ?></td>
                            <td class="px-5 py-3.5 font-medium text-zinc-700"><?= number_format($ord['quantity']) ?></td>
                            <td class="px-5 py-3.5 font-semibold text-zinc-900"><?= format_currency($ord['charge']) ?></td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 capitalize">
                                    <?= e($ord['status']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-zinc-400"><?= date('M j, Y H:i', strtotime($ord['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="rounded-2xl border border-zinc-200 bg-white p-16 text-center shadow-xs">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-400 mb-3">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <h3 class="text-base font-bold text-zinc-900">No Orders Found</h3>
            <p class="mt-1 text-xs text-zinc-500 max-w-sm mx-auto">
                No orders match your filter criteria. Select a service to launch your first delivery campaign.
            </p>
            <div class="mt-5">
                <a href="/user/services" class="inline-flex rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700">
                    Browse Services Catalog
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
