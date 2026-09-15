<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Affiliate Program</h1>
        <p class="mt-1 text-sm text-zinc-500">Invite new users and receive commission on their order volume.</p>
    </div>

    <!-- Referral Link Card -->
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 sm:p-8 shadow-xs">
        <h2 class="text-lg font-bold text-zinc-900 mb-2">Your Unique Referral Link</h2>
        <p class="text-xs text-zinc-500 mb-4">Share this link to earn a <?= e($referralRate) ?>% lifetime commission on active deposits.</p>

        <?php 
            $refUrl = rtrim(config('app.url', 'http://localhost:3000'), '/') . '/register?ref=' . urlencode($user['referral_code'] ?? '');
        ?>
        <div class="flex max-w-xl items-center space-x-2">
            <input type="text" id="ref-link" value="<?= e($refUrl) ?>" readonly 
                   class="block w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2.5 font-mono text-xs text-zinc-800 focus:outline-none">
            <button type="button" data-target="ref-link" class="copy-btn rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 whitespace-nowrap">
                Copy Link
            </button>
        </div>
    </div>

    <!-- Referrals Table -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-zinc-900">Referred Users</h2>
        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-xs overflow-hidden">
            <?php if (!empty($referrals)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                        <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3.5">User</th>
                                <th class="px-5 py-3.5">Date Joined</th>
                                <th class="px-5 py-3.5">Commission Rate</th>
                                <th class="px-5 py-3.5">Total Earned</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <?php foreach ($referrals as $ref): ?>
                                <tr class="hover:bg-zinc-50/80">
                                    <td class="px-5 py-3.5 font-medium text-zinc-900"><?= e($ref['referred_name']) ?></td>
                                    <td class="px-5 py-3.5 text-xs text-zinc-400"><?= date('M j, Y', strtotime($ref['joined_at'])) ?></td>
                                    <td class="px-5 py-3.5 text-xs font-semibold text-zinc-700"><?= e($ref['commission_rate']) ?>%</td>
                                    <td class="px-5 py-3.5 font-semibold text-emerald-600"><?= format_currency($ref['total_earned']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-12 text-center text-xs text-zinc-400">
                    You haven't referred any clients yet. Share your referral link to begin earning commissions.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
