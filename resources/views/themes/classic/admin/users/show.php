<?php
/** @var array $user */
/** @var array $wallet */
/** @var array $orders */
/** @var array $transactions */
/** @var array $tickets */
/** @var array $referrals */
?>

<div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center justify-between pb-2">
        <a href="/admin/users" class="text-xs text-rose-600 hover:underline font-semibold flex items-center">
            &larr; Back to All Users
        </a>
        <div class="flex items-center space-x-3">
            <span class="text-xs text-zinc-400">User ID: <strong class="font-mono text-zinc-900">#<?= (int)$user['id'] ?></strong></span>
        </div>
    </div>

    <!-- User Header Card -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 pb-6 border-b border-zinc-100">
            <div class="flex items-center space-x-4">
                <div class="h-14 w-14 rounded-2xl bg-zinc-900 text-white flex items-center justify-center font-bold text-xl shadow-xs">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-xl font-bold text-zinc-900"><?= e($user['name']) ?></h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase <?= $user['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                            <?= e($user['status']) ?>
                        </span>
                    </div>
                    <div class="text-xs text-zinc-500 font-mono mt-0.5"><?= e($user['email']) ?></div>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-zinc-400">
                        <span>Joined: <?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                        <span>•</span>
                        <span>Ref Code: <strong class="font-mono text-zinc-700"><?= e($user['referral_code'] ?? 'None') ?></strong></span>
                        <?php if (!empty($user['last_login_ip'])): ?>
                            <span>•</span>
                            <span>Last IP: <?= e($user['last_login_ip']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Pill -->
            <div class="flex items-center space-x-4 bg-zinc-50 border border-zinc-200/80 rounded-2xl p-4">
                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-zinc-400">Current Balance</div>
                    <div class="text-xl font-bold font-mono text-zinc-900">$<?= number_format((float)($wallet['balance'] ?? 0), 4) ?></div>
                </div>
                <div class="h-8 w-px bg-zinc-200"></div>
                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-zinc-400">Total Spent</div>
                    <div class="text-xl font-bold font-mono text-rose-600">$<?= number_format((float)($wallet['spent'] ?? 0), 2) ?></div>
                </div>
            </div>
        </div>

        <!-- Controls: Status & Balance Actions -->
        <div class="pt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Change Status Form -->
            <form action="/admin/users/status" method="POST" class="flex items-center space-x-2">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <label class="text-xs text-zinc-600 font-semibold whitespace-nowrap">Account Status:</label>
                <select name="status" onchange="this.form.submit()" class="rounded-xl border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-900 font-semibold focus:border-rose-500 focus:outline-none">
                    <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= $user['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="banned" <?= $user['status'] === 'banned' ? 'selected' : '' ?>>Banned</option>
                </select>
            </form>

            <!-- Quick Adjust Funds Trigger Form -->
            <form action="/admin/wallets/adjust" method="POST" class="flex items-center justify-end space-x-2">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <select name="action" class="rounded-xl border border-zinc-300 bg-white px-2 py-1.5 text-xs text-zinc-900 font-semibold">
                    <option value="credit">+ Add Funds</option>
                    <option value="debit">- Deduct Funds</option>
                </select>
                <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount" required class="w-24 rounded-xl border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 font-mono">
                <input type="text" name="reason" placeholder="Reason (e.g. Compensation)" required class="rounded-xl border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900">
                <button type="submit" class="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl transition-colors">
                    Adjust
                </button>
            </form>
        </div>
    </div>

    <!-- Orders & Transactions Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- User Orders -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-zinc-900">Recent Orders (<?= count($orders) ?>)</h3>
                <a href="/admin/orders?search=<?= (int)$user['id'] ?>" class="text-xs text-rose-600 font-semibold hover:underline">View in Orders Desk &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Service</th>
                            <th class="py-3 px-3 text-right">Charge</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-zinc-400">No orders placed by this user yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $ord): ?>
                                <tr class="hover:bg-zinc-50/70 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-zinc-900">
                                        <a href="/admin/orders/<?= (int)$ord['id'] ?>" class="hover:text-rose-600">#<?= (int)$ord['id'] ?></a>
                                    </td>
                                    <td class="py-3 px-4 text-zinc-800 truncate max-w-[160px]">
                                        <?= e($ord['service_name']) ?>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-bold text-zinc-900">
                                        $<?= number_format((float)$ord['charge'], 2) ?>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-zinc-100 text-zinc-700">
                                            <?= e($ord['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right text-zinc-400 whitespace-nowrap">
                                        <?= date('M d, H:i', strtotime($ord['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Wallet Transactions -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-zinc-900">Wallet Transactions</h3>
                <span class="text-xs text-zinc-400 font-mono"><?= count($transactions) ?> records</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Balance After</th>
                            <th class="py-3 px-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-zinc-400">No transactions recorded.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $tx): ?>
                                <tr class="hover:bg-zinc-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="font-bold uppercase text-[10px] px-2 py-0.5 rounded-md <?= in_array($tx['type'], ['deposit', 'manual_add', 'refund', 'referral_bonus']) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                                            <?= e($tx['type']) ?>
                                        </span>
                                        <div class="text-[10px] text-zinc-400 truncate max-w-[140px] mt-0.5"><?= e($tx['description'] ?? '') ?></div>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold <?= in_array($tx['type'], ['deposit', 'manual_add', 'refund', 'referral_bonus']) ? 'text-emerald-600' : 'text-zinc-900' ?>">
                                        <?= in_array($tx['type'], ['deposit', 'manual_add', 'refund', 'referral_bonus']) ? '+' : '-' ?>$<?= number_format((float)$tx['amount'], 2) ?>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-zinc-600">
                                        $<?= number_format((float)$tx['balance_after'], 2) ?>
                                    </td>
                                    <td class="py-3 px-4 text-right text-zinc-400 whitespace-nowrap">
                                        <?= date('M d, H:i', strtotime($tx['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
