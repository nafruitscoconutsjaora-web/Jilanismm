<?php
/** @var array $overview */
/** @var array $settings */
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Affiliate & Referral Network</h1>
            <p class="text-sm text-zinc-500 mt-1">Configure global referral commissions, view affiliate performance, and audit payout records.</p>
        </div>
    </div>

    <!-- Configuration Card -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
        <h3 class="text-base font-bold text-zinc-900 mb-4">Program Configuration</h3>
        <form action="/admin/referrals/settings" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1.5">System Status</label>
                <select name="referral_system_enabled" class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2.5 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    <option value="1" <?= $settings['enabled'] === '1' ? 'selected' : '' ?>>Enabled (Active)</option>
                    <option value="0" <?= $settings['enabled'] === '0' ? 'selected' : '' ?>>Disabled (Inactive)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1.5">Deposit Commission (%)</label>
                <div class="relative">
                    <input type="number" step="0.1" min="0" max="100" name="referral_commission_percent" value="<?= e($settings['commission_percent']) ?>" required class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2.5 text-xs text-zinc-900 font-bold focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 pr-8">
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 font-bold text-xs">%</div>
                </div>
            </div>
            <div>
                <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    Save Referral Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Network Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Total Commissions Paid</div>
            <div class="text-2xl font-bold font-mono text-emerald-600 mt-2">
                $<?= number_format((float)$overview['stats']['total_payouts'], 2) ?>
            </div>
            <div class="text-xs text-zinc-400 mt-1"><?= number_format((int)$overview['stats']['total_rewards_count']) ?> payouts processed</div>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Deposit Volume Driven</div>
            <div class="text-2xl font-bold font-mono text-zinc-900 mt-2">
                $<?= number_format((float)$overview['stats']['total_volume'], 2) ?>
            </div>
            <div class="text-xs text-zinc-400 mt-1">From referred customers</div>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Referred Users Joined</div>
            <div class="text-2xl font-bold text-zinc-900 mt-2">
                <?= number_format((int)$overview['stats']['total_referred_users']) ?>
            </div>
            <div class="text-xs text-zinc-400 mt-1">Through affiliate codes</div>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Current Commission Rate</div>
            <div class="text-2xl font-bold text-rose-600 mt-2">
                <?= number_format((float)$settings['commission_percent'], 1) ?>%
            </div>
            <div class="text-xs text-zinc-400 mt-1">Instant wallet credit</div>
        </div>
    </div>

    <!-- Top Affiliates & Recent Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Top Affiliates Table -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100">
                <h3 class="text-base font-bold text-zinc-900">Top Affiliates</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-4">Affiliate</th>
                            <th class="py-3 px-3 text-center">Invited</th>
                            <th class="py-3 px-4 text-right">Earned</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php if (empty($overview['top_referrers'])): ?>
                            <tr>
                                <td colspan="3" class="py-8 text-center text-zinc-400">No referral earnings logged yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($overview['top_referrers'] as $top): ?>
                                <tr class="hover:bg-zinc-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-zinc-900"><?= e($top['name']) ?></div>
                                        <div class="text-[10px] text-zinc-400 font-mono"><?= e($top['referral_code']) ?></div>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-zinc-700">
                                        <?= (int)$top['total_invited'] ?>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                        $<?= number_format((float)$top['total_earned'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Referral Payouts -->
        <div class="lg:col-span-2 bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100">
                <h3 class="text-base font-bold text-zinc-900">Recent Commission Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-4">Referrer</th>
                            <th class="py-3 px-4">Referred User</th>
                            <th class="py-3 px-4 text-right">Deposit</th>
                            <th class="py-3 px-4 text-center">Rate</th>
                            <th class="py-3 px-4 text-right">Commission</th>
                            <th class="py-3 px-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php if (empty($overview['recent_logs'])): ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-zinc-400">No referral commission payouts recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($overview['recent_logs'] as $r): ?>
                                <tr class="hover:bg-zinc-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-zinc-900"><?= e($r['referrer_name']) ?></div>
                                        <div class="text-[10px] text-zinc-400"><?= e($r['referrer_email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-zinc-800"><?= e($r['referred_name']) ?></div>
                                        <div class="text-[10px] text-zinc-400"><?= e($r['referred_email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-zinc-600">
                                        $<?= number_format((float)$r['order_amount'], 2) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-zinc-600">
                                        <?= number_format((float)$r['commission_rate'], 1) ?>%
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                        +$<?= number_format((float)$r['reward_amount'], 4) ?>
                                    </td>
                                    <td class="py-3 px-4 text-right text-zinc-400 whitespace-nowrap">
                                        <?= date('M d, H:i', strtotime($r['created_at'])) ?>
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
