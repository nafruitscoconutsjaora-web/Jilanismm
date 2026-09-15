<div class="space-y-8 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Wallet & Balance</h1>
            <p class="mt-1 text-sm text-zinc-500">Monitor your ledger transactions, balance allocations, and automated funding history.</p>
        </div>
        <div>
            <a href="/wallet/add-funds" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Deposit Funds
            </a>
        </div>
    </div>

    <!-- Balance Summary Banner -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="rounded-2xl bg-gradient-to-br from-rose-600 to-red-700 p-6 text-white shadow-sm">
            <span class="text-xs uppercase tracking-wider text-rose-200 font-medium">Available Balance</span>
            <div class="mt-2 text-3xl font-extrabold tracking-tight">
                $<?= number_format((float)($wallet['balance'] ?? 0.00), 4) ?> <span class="text-xs font-medium text-rose-200"><?= e($wallet['currency'] ?? 'USD') ?></span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <span class="text-rose-100">Ready for instant order placement</span>
                <a href="/order/new" class="font-bold underline hover:text-white">Place Order &rarr;</a>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs">
            <span class="text-xs uppercase tracking-wider text-zinc-400 font-semibold">Total Spent</span>
            <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">
                $<?= number_format((float)($wallet['spent'] ?? 0.00), 4) ?>
            </div>
            <div class="mt-2 text-xs text-zinc-400">Delivered social media campaigns</div>
        </div>

        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xs">
            <span class="text-xs uppercase tracking-wider text-zinc-400 font-semibold">Account Funding</span>
            <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">
                $<?= number_format((float)($wallet['balance'] ?? 0) + (float)($wallet['spent'] ?? 0), 4) ?>
            </div>
            <div class="mt-2 text-xs text-zinc-400">Cumulative deposits credited</div>
        </div>
    </div>

    <!-- Quick Deposit Banner -->
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 sm:p-8 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="space-y-1">
            <h2 class="text-lg font-bold text-zinc-900">Need to top up your balance?</h2>
            <p class="text-xs text-zinc-500 max-w-xl">
                Fund your wallet via Credit Card (Stripe), PayPal, Cryptocurrency, or Bank Wire. Balances update immediately with zero delay.
            </p>
        </div>
        <a href="/wallet/add-funds" class="whitespace-nowrap px-5 py-3 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
            Go to Deposit Gateways &rarr;
        </a>
    </div>

    <!-- Transaction History Table -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-zinc-900">Ledger Activity Log</h2>
        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-xs overflow-hidden">
            <?php if (!empty($transactions)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                        <thead class="bg-zinc-50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3.5">ID</th>
                                <th class="px-5 py-3.5">Type</th>
                                <th class="px-5 py-3.5 text-right">Amount</th>
                                <th class="px-5 py-3.5 text-right">Balance Before</th>
                                <th class="px-5 py-3.5 text-right">Balance After</th>
                                <th class="px-5 py-3.5">Reference & Description</th>
                                <th class="px-5 py-3.5 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700 text-xs">
                            <?php foreach ($transactions as $tx): ?>
                                <?php
                                $type = $tx['type'];
                                $isCredit = in_array($type, ['deposit', 'refund', 'manual_add', 'referral_bonus'], true);
                                $badgeClass = match ($type) {
                                    'deposit' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'refund' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'order' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'manual_add' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'manual_deduct' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-zinc-100 text-zinc-700 border-zinc-200'
                                };
                                ?>
                                <tr class="hover:bg-zinc-50/80 transition-colors">
                                    <td class="px-5 py-3.5 font-mono text-zinc-400">#<?= (int)$tx['id'] ?></td>
                                    <td class="px-5 py-3.5">
                                        <span class="rounded-md border px-2 py-0.5 font-semibold <?= $badgeClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $type)) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold <?= $isCredit ? 'text-emerald-600' : 'text-rose-600' ?>">
                                        <?= $isCredit ? '+' : '-' ?>$<?= number_format((float)$tx['amount'], 4) ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono text-zinc-500">
                                        $<?= number_format((float)$tx['balance_before'], 4) ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono font-bold text-zinc-900">
                                        $<?= number_format((float)$tx['balance_after'], 4) ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="text-zinc-900 font-semibold"><?= e($tx['description']) ?></div>
                                        <?php if (!empty($tx['reference_id'])): ?>
                                            <div class="font-mono text-[10px] text-zinc-400 mt-0.5">Ref: <?= e($tx['reference_id']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-zinc-400 font-mono"><?= date('M j, Y H:i:s', strtotime($tx['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-10 text-center text-xs text-zinc-400">
                    No transactions recorded in wallet ledger yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
