<?php
/** @var array $admin */
/** @var array $payments */
/** @var int $total */
/** @var int $page */
/** @var int $lastPage */
/** @var string $status */
/** @var string $search */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Payment Transactions</h1>
            <p class="text-sm text-zinc-500 mt-1">Audit incoming deposit requests, gateways, net ledger credits, and manual wire approvals.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="/admin/gateways" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                Configure Gateways &rarr;
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-2 border-b border-zinc-200/80 scrollbar-none">
        <?php foreach (['all' => 'All Transactions', 'completed' => 'Completed', 'pending' => 'Pending Review', 'failed' => 'Failed / Cancelled'] as $k => $lbl): ?>
            <a href="/admin/payments?status=<?= $k ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors <?= $status === $k ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 border border-zinc-200 hover:bg-zinc-100' ?>">
                <?= e($lbl) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search Form -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs">
        <form action="/admin/payments" method="GET" class="flex items-center gap-3">
            <input type="hidden" name="status" value="<?= e($status) ?>">
            <div class="relative flex-1">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by transaction ID, user email, or name..." class="w-full text-xs rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl">Filter</button>
            <?php if (!empty($search)): ?>
                <a href="/admin/payments?status=<?= e($status) ?>" class="text-xs text-zinc-500 hover:text-zinc-800">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-6">Txn ID / Ref</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Gateway</th>
                        <th class="py-3 px-4 text-right">Credit Amount</th>
                        <th class="py-3 px-4 text-right">Fee</th>
                        <th class="py-3 px-4 text-right">Net Charged</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Created Date</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center text-zinc-400">No payment transactions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                            <?php
                            $st = $p['status'];
                            $badge = match ($st) {
                                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-zinc-100 text-zinc-700 border-zinc-200'
                            };
                            ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-zinc-900">
                                    <?= e($p['transaction_id']) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-zinc-900"><?= e($p['user_name'] ?? 'User') ?></div>
                                    <div class="text-[10px] text-zinc-400 font-mono"><?= e($p['user_email'] ?? '') ?></div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-zinc-800">
                                    <?= e($p['gateway_name']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600">
                                    +$<?= number_format((float)$p['amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-zinc-500">
                                    $<?= number_format((float)$p['fee'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-zinc-900">
                                    $<?= number_format((float)$p['net_amount'], 2) ?> <?= e($p['currency']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border <?= $badge ?>">
                                        <?= ucfirst($st) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-500 font-mono text-[11px]">
                                    <?= date('M j, Y H:i', strtotime($p['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-6 text-right whitespace-nowrap space-x-1">
                                    <?php if ($st === 'pending'): ?>
                                        <!-- Approve Form -->
                                        <form action="/admin/payments/approve/<?= (int)$p['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Approve this deposit and credit user wallet?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                                                Approve
                                            </button>
                                        </form>

                                        <!-- Reject Form -->
                                        <form action="/admin/payments/reject/<?= (int)$p['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Reject this payment?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="px-2.5 py-1 bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 text-xs font-semibold rounded-lg transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-zinc-400 text-[11px] italic">Finalized</span>
                                    <?php endif; ?>
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
                <div class="text-xs text-zinc-500 font-medium">
                    Showing page <?= $page ?> of <?= $lastPage ?> (<?= $total ?> records)
                </div>
                <div class="flex items-center space-x-1">
                    <?php if ($page > 1): ?>
                        <a href="/admin/payments?status=<?= e($status) ?>&page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $lastPage): ?>
                        <a href="/admin/payments?status=<?= e($status) ?>&page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
