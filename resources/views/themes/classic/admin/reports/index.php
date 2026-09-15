<?php
/** @var array $finance */
/** @var array $ordersByStatus */
/** @var array $topServices */
/** @var array $gatewayBreakdown */
/** @var array $topSpenders */
/** @var array $dailyRevenue */
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Financial & Operational Reports</h1>
            <p class="text-sm text-zinc-500 mt-1">Platform analytics, revenue breakdowns, top performing services, and gateway transaction volumes.</p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-zinc-200 hover:bg-zinc-50 text-zinc-700 text-xs font-semibold rounded-xl shadow-xs transition-colors">
            <svg class="h-4 w-4 mr-1.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Export / Print Report
        </button>
    </div>

    <!-- Core Financial Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Gross Client Deposits</div>
            <div class="text-2xl font-bold font-mono text-emerald-600 mt-2">
                $<?= number_format((float)$finance['gross_deposits'], 2) ?>
            </div>
            <div class="text-xs text-zinc-400 mt-1"><?= number_format((int)$finance['deposits_count']) ?> successful deposits</div>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Order Billing Volume</div>
            <div class="text-2xl font-bold font-mono text-zinc-900 mt-2">
                $<?= number_format((float)$finance['gross_orders'], 2) ?>
            </div>
            <div class="text-xs text-zinc-400 mt-1"><?= number_format((int)$finance['orders_count']) ?> orders placed</div>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Platform Liability (Balances)</div>
            <div class="text-2xl font-bold font-mono text-amber-600 mt-2">
                $<?= number_format((float)$finance['user_liability'], 2) ?>
            </div>
            <div class="text-xs text-zinc-400 mt-1">Outstanding in user wallets</div>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Total Order Refunds</div>
            <div class="text-2xl font-bold font-mono text-rose-600 mt-2">
                $<?= number_format((float)$finance['total_refunded'], 2) ?>
            </div>
            <div class="text-xs text-zinc-400 mt-1">Returned to wallet balances</div>
        </div>
    </div>

    <!-- Secondary Metrics: Coupons, Referrals, Gateway Fees -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Promotional Discounts</div>
                <div class="text-lg font-bold font-mono text-zinc-900 mt-1">$<?= number_format((float)$finance['coupon_discounts'], 2) ?></div>
            </div>
            <div class="p-2.5 rounded-xl bg-purple-50 text-purple-600 font-bold text-xs">Coupons</div>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Affiliate Commissions</div>
                <div class="text-lg font-bold font-mono text-zinc-900 mt-1">$<?= number_format((float)$finance['referral_rewards'], 2) ?></div>
            </div>
            <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600 font-bold text-xs">Referrals</div>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Gateway Processing Fees</div>
                <div class="text-lg font-bold font-mono text-zinc-900 mt-1">$<?= number_format((float)$finance['total_deposit_fees'], 2) ?></div>
            </div>
            <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600 font-bold text-xs">Gateway</div>
        </div>
    </div>

    <!-- 14-Day Revenue Trend & Orders by Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- 14-Day Daily Revenue -->
        <div class="lg:col-span-2 bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-zinc-900">Recent Daily Order Revenue (Last 14 Days)</h3>
                <span class="text-xs text-zinc-400">Excluding canceled</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4 text-center">Orders Count</th>
                            <th class="py-3 px-4 text-right">Daily Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php if (empty($dailyRevenue)): ?>
                            <tr>
                                <td colspan="3" class="py-8 text-center text-zinc-400">No recent orders recorded.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dailyRevenue as $dr): ?>
                                <tr class="hover:bg-zinc-50/70 transition-colors">
                                    <td class="py-3 px-4 font-bold text-zinc-900">
                                        <?= date('l, M d, Y', strtotime($dr['log_date'])) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-zinc-700">
                                        <?= number_format((int)$dr['orders_count']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                        $<?= number_format((float)$dr['total_revenue'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Breakdown by Status -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100">
                <h3 class="text-base font-bold text-zinc-900">Orders Status Distribution</h3>
            </div>
            <div class="p-5 space-y-4">
                <?php if (empty($ordersByStatus)): ?>
                    <p class="text-xs text-zinc-400">No orders logged.</p>
                <?php else: ?>
                    <?php foreach ($ordersByStatus as $obs): ?>
                        <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-100 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold uppercase tracking-wider text-zinc-800"><?= e($obs['status']) ?></span>
                                <div class="text-[10px] text-zinc-400"><?= number_format((int)$obs['count']) ?> orders</div>
                            </div>
                            <div class="text-right font-mono font-bold text-zinc-900">
                                $<?= number_format((float)$obs['total_charge'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top 10 Services & Gateway Volume -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top 10 Services -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-zinc-900">Top 10 Most Popular Services</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-4">Service</th>
                            <th class="py-3 px-3 text-center">Orders</th>
                            <th class="py-3 px-4 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php if (empty($topServices)): ?>
                            <tr>
                                <td colspan="3" class="py-8 text-center text-zinc-400">No service metrics yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topServices as $ts): ?>
                                <tr class="hover:bg-zinc-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-zinc-900 truncate max-w-[220px]"><?= e($ts['name']) ?></div>
                                        <div class="text-[10px] text-zinc-400"><?= e($ts['category_name'] ?? 'General') ?></div>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-zinc-700">
                                        <?= number_format((int)$ts['orders_count']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-zinc-900">
                                        $<?= number_format((float)$ts['revenue'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Gateway Breakdown -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-zinc-900">Gateway Deposit Volumes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-4">Gateway</th>
                            <th class="py-3 px-3 text-center">Txns</th>
                            <th class="py-3 px-3 text-right">Fees</th>
                            <th class="py-3 px-4 text-right">Net Volume</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php if (empty($gatewayBreakdown)): ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-zinc-400">No gateway deposits completed yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($gatewayBreakdown as $gb): ?>
                                <tr class="hover:bg-zinc-50/70 transition-colors">
                                    <td class="py-3 px-4 font-bold text-zinc-900">
                                        <?= e($gb['name']) ?>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-zinc-700">
                                        <?= number_format((int)$gb['transactions_count']) ?>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono text-zinc-500">
                                        $<?= number_format((float)$gb['fees'], 2) ?>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                        $<?= number_format((float)$gb['volume'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Spenders Leaderboard -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-zinc-900">Top 10 Client Spenders</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-4">Client</th>
                        <th class="py-3 px-4 text-center">Orders Placed</th>
                        <th class="py-3 px-4 text-right">Remaining Balance</th>
                        <th class="py-3 px-4 text-right">Total Lifetime Spent</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($topSpenders)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-zinc-400">No client spending data yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topSpenders as $ts): ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zinc-900"><?= e($ts['name']) ?></div>
                                    <div class="text-[10px] text-zinc-400 font-mono"><?= e($ts['email']) ?></div>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-zinc-700">
                                    <?= number_format((int)$ts['orders_count']) ?>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-medium text-zinc-600">
                                    $<?= number_format((float)$ts['balance'], 2) ?>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-rose-600">
                                    $<?= number_format((float)$ts['spent'], 2) ?>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <a href="/admin/users/<?= (int)$ts['id'] ?>" class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-lg text-xs font-semibold">
                                        View Account &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
