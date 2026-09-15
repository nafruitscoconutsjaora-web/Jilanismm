<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">User Management</h1>
            <p class="mt-1 text-xs text-zinc-500">Inspect user accounts, adjust balances, modify status, and manage credentials.</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs">
        <form action="/admin/users" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Search by name, email, or referral code..." 
                       class="w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
            </div>
            <div class="w-full sm:w-44">
                <select name="status" class="w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-700 shadow-xs focus:border-rose-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="banned" <?= ($status ?? '') === 'banned' ? 'selected' : '' ?>>Banned</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-zinc-900 px-5 py-2 text-xs font-semibold text-white hover:bg-zinc-800">
                Filter
            </button>
            <?php if (!empty($search) || !empty($status)): ?>
                <a href="/admin/users" class="rounded-xl border border-zinc-200 px-4 py-2 text-xs font-semibold text-zinc-600 hover:bg-zinc-50 flex items-center justify-center">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Users Table -->
    <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs overflow-hidden">
        <?php if (!empty($users)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                    <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th class="px-5 py-3.5">ID</th>
                            <th class="px-5 py-3.5">Client</th>
                            <th class="px-5 py-3.5">Referral</th>
                            <th class="px-5 py-3.5">Balance</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Registered</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <?php foreach ($users as $u): ?>
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
                                <td class="px-5 py-3.5 text-xs text-zinc-400"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                                <td class="px-5 py-3.5 text-right space-x-2">
                                    <!-- Balance Adjustment Modal Trigger -->
                                    <button type="button" onclick="openBalanceModal(<?= e($u['id']) ?>, '<?= e(addslashes($u['name'])) ?>', <?= (float)($u['balance'] ?? 0) ?>)" 
                                            class="rounded-lg border border-zinc-200 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">
                                        Funds &plusmn;
                                    </button>

                                    <!-- Status Toggle Form -->
                                    <form action="/admin/users/<?= e($u['id']) ?>/status" method="POST" class="inline-block">
                                        <?= csrf_field() ?>
                                        <select name="status" onchange="this.form.submit()" class="rounded-lg border border-zinc-200 py-1 text-xs font-semibold text-zinc-700 bg-white">
                                            <option value="active" <?= $u['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= $u['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            <option value="banned" <?= $u['status'] === 'banned' ? 'selected' : '' ?>>Banned</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-12 text-center text-xs text-zinc-400">
                No users match your criteria.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Balance Adjustment Modal -->
<div id="balanceModal" class="fixed inset-0 z-50 hidden bg-zinc-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-zinc-200">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
            <h3 class="text-base font-bold text-zinc-900">Adjust User Balance</h3>
            <button type="button" onclick="closeBalanceModal()" class="text-zinc-400 hover:text-zinc-600">&times;</button>
        </div>
        <form id="balanceForm" action="" method="POST" class="mt-4 space-y-4">
            <?= csrf_field() ?>
            <div>
                <span class="text-xs text-zinc-500">Target User:</span>
                <span id="modalUserName" class="block font-bold text-zinc-900 text-sm"></span>
                <span id="modalUserBalance" class="block text-xs text-zinc-500 mt-0.5"></span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Adjustment Type</label>
                <div class="mt-1 flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="action" value="credit" checked class="text-rose-600 focus:ring-rose-500">
                        <span class="ml-2 text-xs font-semibold text-emerald-700">Credit (Add Funds)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="action" value="debit" class="text-rose-600 focus:ring-rose-500">
                        <span class="ml-2 text-xs font-semibold text-rose-700">Debit (Deduct Funds)</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="modalAmount" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Amount ($)</label>
                <input type="number" step="0.01" min="0.01" id="modalAmount" name="amount" required 
                       class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm focus:border-rose-500 focus:outline-none" placeholder="10.00">
            </div>

            <div>
                <label for="modalDescription" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Reason / Audit Memo</label>
                <input type="text" id="modalDescription" name="description" required 
                       class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm focus:border-rose-500 focus:outline-none" placeholder="Manual adjustment or deposit top-up">
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-zinc-100">
                <button type="button" onclick="closeBalanceModal()" class="rounded-xl border border-zinc-200 px-4 py-2 text-xs font-semibold text-zinc-600 hover:bg-zinc-50">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700">
                    Confirm Adjustment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openBalanceModal(userId, userName, currentBalance) {
    document.getElementById('balanceForm').action = '/admin/users/' + userId + '/balance';
    document.getElementById('modalUserName').textContent = userName + ' (ID #' + userId + ')';
    document.getElementById('modalUserBalance').textContent = 'Current: $' + currentBalance.toFixed(2);
    document.getElementById('balanceModal').classList.remove('hidden');
}
function closeBalanceModal() {
    document.getElementById('balanceModal').classList.add('hidden');
}
</script>
