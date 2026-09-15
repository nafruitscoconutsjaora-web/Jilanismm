<div class="space-y-8">
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl">
                Welcome back, <?= e($user['name']) ?>
            </h1>
            <p class="mt-1 text-sm text-zinc-500">
                Account ID: <span class="font-mono font-medium text-zinc-700">#<?= e($user['id']) ?></span> &bull; 
                Status: <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700"><?= strtoupper(e($user['status'])) ?></span>
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="/wallet" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-rose-700 transition-colors">
                + Add Funds
            </a>
            <a href="/user/services" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50 transition-colors">
                Browse Services
            </a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Balance -->
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Current Balance</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 font-bold text-sm">$</span>
            </div>
            <div class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-900">
                <?= format_currency($wallet['balance'] ?? 0.00, $wallet['currency'] ?? 'USD', setting('currency_symbol', '$')) ?>
            </div>
            <a href="/wallet" class="mt-3 block text-xs font-medium text-rose-600 hover:text-rose-700">
                Deposit funds &rarr;
            </a>
        </div>

        <!-- Total Spent -->
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Total Spent</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 font-bold text-sm">&darr;</span>
            </div>
            <div class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-900">
                <?= format_currency($wallet['spent'] ?? 0.00, $wallet['currency'] ?? 'USD', setting('currency_symbol', '$')) ?>
            </div>
            <div class="mt-3 text-xs text-zinc-400">Lifetime orders value</div>
        </div>

        <!-- Orders Count -->
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Total Orders</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 font-bold text-sm">#</span>
            </div>
            <div class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-900">
                <?= number_format($totalOrders) ?>
            </div>
            <div class="mt-3 text-xs text-zinc-500">
                <span class="text-amber-600 font-semibold"><?= $pendingOrders ?></span> Pending &bull; 
                <span class="text-emerald-600 font-semibold"><?= $completedOrders ?></span> Completed
            </div>
        </div>

        <!-- Referral Code -->
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Referral Code</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 font-bold text-xs">%</span>
            </div>
            <div class="mt-3 font-mono text-2xl font-bold tracking-tight text-zinc-900">
                <?= e($user['referral_code'] ?? 'N/A') ?>
            </div>
            <a href="/referrals" class="mt-3 block text-xs font-medium text-rose-600 hover:text-rose-700">
                View affiliate stats &rarr;
            </a>
        </div>
    </div>

    <!-- Main Content: Recent Orders & Notifications Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Orders (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-zinc-900">Recent Orders</h2>
                <a href="/orders" class="text-xs font-semibold text-rose-600 hover:text-rose-700">View All Orders &rarr;</a>
            </div>

            <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-xs overflow-hidden">
                <?php if (!empty($recentOrders)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                            <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                <tr>
                                    <th class="px-5 py-3.5">ID</th>
                                    <th class="px-5 py-3.5">Service</th>
                                    <th class="px-5 py-3.5">Charge</th>
                                    <th class="px-5 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                <?php foreach ($recentOrders as $ord): ?>
                                    <tr class="hover:bg-zinc-50/80">
                                        <td class="px-5 py-3.5 font-mono text-xs text-zinc-400">#<?= e($ord['id']) ?></td>
                                        <td class="px-5 py-3.5 font-medium text-zinc-900"><?= e($ord['service_name'] ?? 'Custom Order') ?></td>
                                        <td class="px-5 py-3.5 font-semibold text-zinc-900"><?= format_currency($ord['charge']) ?></td>
                                        <td class="px-5 py-3.5">
                                            <span class="inline-flex rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 capitalize">
                                                <?= e($ord['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Real Clean Empty State -->
                    <div class="p-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-400 mb-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <h3 class="text-sm font-bold text-zinc-800">No Orders Placed Yet</h3>
                        <p class="mt-1 text-xs text-zinc-500 max-w-sm mx-auto">
                            When you place orders for social media campaigns, they will be listed here with live status tracking.
                        </p>
                        <div class="mt-5">
                            <a href="/user/services" class="inline-flex rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700">
                                Explore Services &rarr;
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Latest Notifications & Announcements (1 col) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-zinc-900">Notifications</h2>
                <a href="/notifications" class="text-xs font-semibold text-rose-600 hover:text-rose-700">All (<?= $unreadCount ?>)</a>
            </div>

            <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-xs space-y-4">
                <?php if (!empty($notifications)): ?>
                    <div class="divide-y divide-zinc-100">
                        <?php foreach ($notifications as $notif): ?>
                            <div class="py-3 first:pt-0 last:pb-0">
                                <div class="flex items-start justify-between">
                                    <h4 class="text-xs font-bold text-zinc-900"><?= e($notif['title']) ?></h4>
                                    <span class="text-[10px] text-zinc-400"><?= date('M j', strtotime($notif['created_at'])) ?></span>
                                </div>
                                <p class="mt-1 text-xs text-zinc-600 leading-relaxed"><?= e($notif['message']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="py-8 text-center text-xs text-zinc-400">
                        No new notifications. Everything is up to date.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
