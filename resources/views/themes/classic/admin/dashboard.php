<div class="space-y-8">
    <!-- Header with Quick Action Maintenance Toggle -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl">System Dashboard</h1>
            <p class="mt-1 text-xs text-zinc-500">Core operational metrics, client registrations, and global system health.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <form action="/admin/maintenance" method="POST" class="inline-block">
                <?= csrf_field() ?>
                <button type="submit" class="inline-flex items-center rounded-xl px-4 py-2 text-xs font-semibold shadow-xs transition-colors <?= $maintenanceMode === 'enabled' ? 'bg-amber-600 text-white hover:bg-amber-700' : 'border border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50' ?>">
                    <span class="mr-2 h-2 w-2 rounded-full <?= $maintenanceMode === 'enabled' ? 'bg-white animate-ping' : 'bg-emerald-500' ?>"></span>
                    <?= $maintenanceMode === 'enabled' ? 'Disable Maintenance' : 'Enable Maintenance' ?>
                </button>
            </form>
            <a href="/admin/users" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-rose-700">
                Manage Users
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Users -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Total Registered Users</span>
            <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">
                <?= number_format($totalUsers) ?>
            </div>
            <div class="mt-2 text-xs text-zinc-500">
                <span class="text-emerald-600 font-semibold"><?= $activeUsers ?></span> Active &bull; 
                <span class="text-rose-600 font-semibold"><?= $suspendedUsers ?></span> Inactive / Banned
            </div>
        </div>

        <!-- Total Orders -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Total Orders Processed</span>
            <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">
                <?= number_format($totalOrders) ?>
            </div>
            <div class="mt-2 text-xs text-zinc-400">Platform automated orders</div>
        </div>

        <!-- Client Wallets -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Total User Balances</span>
            <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">
                <?= format_currency($totalBalance) ?>
            </div>
            <div class="mt-2 text-xs text-zinc-400">Total user wallet funds held</div>
        </div>

        <!-- Maintenance Mode State -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Maintenance Mode</span>
            <div class="mt-2 text-2xl font-extrabold tracking-tight <?= $maintenanceMode === 'enabled' ? 'text-amber-600' : 'text-emerald-600' ?>">
                <?= strtoupper($maintenanceMode) ?>
            </div>
            <div class="mt-2 text-xs text-zinc-400"><?= $maintenanceMode === 'enabled' ? 'Public traffic paused' : 'System live to public' ?></div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-zinc-900">Recent User Registrations</h2>
            <a href="/admin/users" class="text-xs font-semibold text-rose-600 hover:text-rose-700">View All Users &rarr;</a>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs overflow-hidden">
            <?php if (!empty($recentUsers)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                        <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3.5">ID</th>
                                <th class="px-5 py-3.5">Name / Email</th>
                                <th class="px-5 py-3.5">Referral Code</th>
                                <th class="px-5 py-3.5">Balance</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <?php foreach ($recentUsers as $u): ?>
                                <tr class="hover:bg-zinc-50/80">
                                    <td class="px-5 py-3.5 font-mono text-xs text-zinc-400">#<?= e($u['id']) ?></td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-zinc-900"><?= e($u['name']) ?></div>
                                        <div class="text-xs text-zinc-500"><?= e($u['email']) ?></div>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-xs text-zinc-600"><?= e($u['referral_code'] ?? '—') ?></td>
                                    <td class="px-5 py-3.5 font-semibold text-zinc-900"><?= format_currency($u['balance'] ?? 0.00) ?></td>
                                    <td class="px-5 py-3.5">
                                        <span class="rounded-md px-2 py-0.5 text-xs font-semibold <?= $u['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                                            <?= strtoupper(e($u['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-zinc-400"><?= date('M j, Y H:i', strtotime($u['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-12 text-center text-xs text-zinc-400">
                    No users registered in database yet.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- System Diagnostics -->
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
        <h2 class="text-sm font-bold uppercase tracking-wider text-zinc-400 mb-4">Environment Diagnostics</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-100">
                <span class="text-zinc-400 block font-medium">PHP Version</span>
                <span class="font-mono font-bold text-zinc-800"><?= PHP_VERSION ?></span>
            </div>
            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-100">
                <span class="text-zinc-400 block font-medium">Database</span>
                <span class="font-mono font-bold text-zinc-800">MariaDB / MySQL 8+ (PDO)</span>
            </div>
            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-100">
                <span class="text-zinc-400 block font-medium">Server Protocol</span>
                <span class="font-mono font-bold text-zinc-800"><?= $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1' ?></span>
            </div>
            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-100">
                <span class="text-zinc-400 block font-medium">Server Time</span>
                <span class="font-mono font-bold text-zinc-800"><?= date('Y-m-d H:i:s T') ?></span>
            </div>
        </div>
    </div>
</div>
