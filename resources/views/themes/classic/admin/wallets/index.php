<?php
/** @var array $admin */
/** @var array $wallets */
/** @var array $recentTransactions */
/** @var int $total */
/** @var int $page */
/** @var int $lastPage */
/** @var string $search */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">User Wallets & Ledgers</h1>
            <p class="text-sm text-zinc-500 mt-1">Audit customer balances, execute manual balance adjustments with audit reasons, and inspect ledger histories.</p>
        </div>
        <button type="button" onclick="openAdjustModal()" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Adjust Balance
        </button>
    </div>

    <!-- Search Form -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs">
        <form action="/admin/wallets" method="GET" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search wallet by user email or name..." class="w-full text-xs rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl">Search</button>
            <?php if (!empty($search)): ?>
                <a href="/admin/wallets" class="text-xs text-zinc-500 hover:text-zinc-800">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Wallets Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="px-6 py-3.5 border-b border-zinc-100 bg-zinc-50/50">
            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-600">Customer Wallet Balances</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-6">User ID</th>
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4 text-right">Available Balance</th>
                        <th class="py-3 px-4 text-center">Currency</th>
                        <th class="py-3 px-4">Last Ledger Activity</th>
                        <th class="py-3 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($wallets)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-zinc-400">No wallets found matching query.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($wallets as $w): ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-zinc-900">#<?= (int)$w['user_id'] ?></td>
                                <td class="py-3.5 px-4 font-bold text-zinc-900"><?= e($w['user_name'] ?? 'User') ?></td>
                                <td class="py-3.5 px-4 font-mono text-zinc-600"><?= e($w['user_email'] ?? '') ?></td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-zinc-900 text-sm">
                                    $<?= number_format((float)$w['balance'], 4) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="rounded bg-zinc-100 px-2 py-0.5 font-mono text-[10px] text-zinc-600 font-bold"><?= e($w['currency'] ?? 'USD') ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-400 font-mono text-[11px]">
                                    <?= date('M j, Y H:i', strtotime($w['updated_at'] ?? $w['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                    <button type="button" onclick='openAdjustModalForUser(<?= (int)$w['user_id'] ?>, "<?= e(addslashes($w['user_email'])) ?>", <?= (float)$w['balance'] ?>)' class="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-lg transition-colors">
                                        Adjust &plusmn;
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($lastPage > 1): ?>
            <div class="flex items-center justify-between px-6 py-4 border-t border-zinc-100 bg-zinc-50/50">
                <div class="text-xs text-zinc-500 font-medium">Page <?= $page ?> of <?= $lastPage ?> (<?= $total ?> users)</div>
                <div class="flex items-center space-x-1">
                    <?php if ($page > 1): ?>
                        <a href="/admin/wallets?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $lastPage): ?>
                        <a href="/admin/wallets?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Global Ledger Activity -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="px-6 py-4 border-b border-zinc-100">
            <h2 class="text-sm font-bold text-zinc-900">Recent Global Wallet Ledger Transactions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-2.5 px-6">ID</th>
                        <th class="py-2.5 px-4">User</th>
                        <th class="py-2.5 px-4">Type</th>
                        <th class="py-2.5 px-4 text-right">Amount</th>
                        <th class="py-2.5 px-4 text-right">Balance After</th>
                        <th class="py-2.5 px-4">Description</th>
                        <th class="py-2.5 px-6 text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($recentTransactions)): ?>
                        <tr><td colspan="7" class="py-6 text-center text-zinc-400">No ledger entries recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <?php
                            $isCredit = in_array($tx['type'], ['deposit', 'refund', 'manual_add'], true);
                            ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3 px-6 font-mono text-zinc-400">#<?= (int)$tx['id'] ?></td>
                                <td class="py-3 px-4 font-mono text-zinc-800"><?= e($tx['user_email']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="rounded px-2 py-0.5 font-semibold text-[10px] uppercase bg-zinc-100 text-zinc-700">
                                        <?= e($tx['type']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold <?= $isCredit ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= $isCredit ? '+' : '-' ?>$<?= number_format((float)$tx['amount'], 4) ?>
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-zinc-600">
                                    $<?= number_format((float)$tx['balance_after'], 4) ?>
                                </td>
                                <td class="py-3 px-4 text-zinc-600"><?= e($tx['description']) ?></td>
                                <td class="py-3 px-6 text-right font-mono text-zinc-400 text-[11px]"><?= date('Y-m-d H:i:s', strtotime($tx['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Adjust Balance Modal -->
<div id="adjustModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-zinc-200">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Adjust User Balance</h3>
            <button type="button" onclick="closeAdjustModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="/admin/wallets/adjust" method="POST" class="space-y-4 mt-4 text-xs" onsubmit="return confirm('Execute this wallet adjustment?')">
            <?= csrf_field() ?>
            <div>
                <label class="block font-semibold text-zinc-700 mb-1">User ID</label>
                <input type="number" id="adjUserId" name="user_id" required class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono">
                <span id="adjUserHint" class="text-[11px] text-zinc-400 mt-1 block"></span>
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Operation</label>
                <select name="action" class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-semibold">
                    <option value="add">Credit (+) Add Funds</option>
                    <option value="deduct">Debit (-) Deduct Funds</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Amount (USD)</label>
                <input type="number" name="amount" step="0.0001" min="0.0001" required placeholder="10.0000" class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono font-bold">
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Audit Reason / Description</label>
                <input type="text" name="reason" required placeholder="e.g. Manual bank wire verified, promotional bonus, penalty deduction" class="w-full rounded-xl border border-zinc-300 px-3 py-2">
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeAdjustModal()" class="px-4 py-2 rounded-xl font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl font-semibold bg-rose-600 hover:bg-rose-700 text-white">Execute Ledger Transaction</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdjustModal() {
    document.getElementById('adjUserId').value = '';
    document.getElementById('adjUserHint').textContent = '';
    document.getElementById('adjustModal').classList.remove('hidden');
}
function openAdjustModalForUser(userId, userEmail, balance) {
    document.getElementById('adjUserId').value = userId;
    document.getElementById('adjUserHint').textContent = userEmail + ' (Current: $' + balance.toFixed(4) + ')';
    document.getElementById('adjustModal').classList.remove('hidden');
}
function closeAdjustModal() {
    document.getElementById('adjustModal').classList.add('hidden');
}
</script>
