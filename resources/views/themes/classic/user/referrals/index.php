<?php
/** @var array $user */
/** @var string $referralUrl */
/** @var array $data */
?>

<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Affiliate & Referral Program</h1>
            <p class="text-sm text-zinc-500 mt-1">Earn passive income by recommending our platform. Get credited instantly on every deposit.</p>
        </div>
        <div class="flex items-center space-x-3 bg-white border border-zinc-200 rounded-2xl px-4 py-2.5 shadow-xs">
            <div class="h-9 w-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base">
                $
            </div>
            <div>
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Total Affiliate Earnings</div>
                <div class="text-base font-bold text-zinc-900 font-mono">
                    $<?= number_format((float)($data['total_earnings'] ?? 0), 4) ?> <span class="text-xs font-medium text-zinc-400">USD</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Link Box Card -->
    <div class="bg-gradient-to-br from-zinc-900 via-zinc-900 to-zinc-950 text-white rounded-3xl p-6 sm:p-8 shadow-sm relative overflow-hidden">
        <div class="relative z-10 max-w-3xl space-y-4">
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                <?= number_format((float)$data['commission_rate'], 1) ?>% Lifetime Commission on All Deposits
            </div>
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Share Your Exclusive Invite Link</h2>
            <p class="text-sm text-zinc-300 leading-relaxed">
                When anyone signs up using your link, they are automatically permanently mapped as your referral. Every time they deposit funds into their wallet, <?= number_format((float)$data['commission_rate'], 1) ?>% is automatically credited to your balance.
            </p>

            <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="flex-1 relative">
                    <input type="text" id="referralUrlInput" readonly value="<?= e($referralUrl) ?>" class="w-full rounded-xl bg-zinc-800/90 border border-zinc-700 px-4 py-3 text-xs sm:text-sm font-mono text-zinc-100 selection:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>
                <button type="button" id="copyReferralBtn" onclick="copyReferralLink()" class="inline-flex items-center justify-center px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    <span id="copyBtnText">Copy Link</span>
                </button>
            </div>
            <div id="copySuccessAlert" class="hidden text-xs text-emerald-400 font-semibold flex items-center pt-1">
                <svg class="h-4 w-4 mr-1 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Copied to clipboard! Share it with your friends or followers.
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Total Referred Friends</div>
            <div class="mt-2 flex items-baseline justify-between">
                <div class="text-3xl font-extrabold text-zinc-900"><?= number_format((int)$data['total_referred']) ?></div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-600">Users</span>
            </div>
            <p class="text-xs text-zinc-500 mt-2">Active accounts registered via your invite link.</p>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Commission Rate</div>
            <div class="mt-2 flex items-baseline justify-between">
                <div class="text-3xl font-extrabold text-rose-600"><?= number_format((float)$data['commission_rate'], 1) ?>%</div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700">Lifetime</span>
            </div>
            <p class="text-xs text-zinc-500 mt-2">Applied immediately whenever referrals deposit.</p>
        </div>

        <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Credited to Balance</div>
            <div class="mt-2 flex items-baseline justify-between">
                <div class="text-3xl font-extrabold text-emerald-600 font-mono">$<?= number_format((float)$data['total_earnings'], 2) ?></div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">Available</span>
            </div>
            <p class="text-xs text-zinc-500 mt-2">Ready to spend on orders immediately.</p>
        </div>
    </div>

    <!-- Referral History & Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Commission Earnings Log -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-zinc-900">Commission Earnings</h3>
                <span class="text-xs text-zinc-400"><?= count($data['logs']) ?> records</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-4">From Referral</th>
                            <th class="py-3 px-4">Deposit Amount</th>
                            <th class="py-3 px-4 text-right">Bonus Credited</th>
                            <th class="py-3 px-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php if (empty($data['logs'])): ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-zinc-400">No commissions earned yet. Start inviting friends!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['logs'] as $log): ?>
                                <tr class="hover:bg-zinc-50/70 transition-colors">
                                    <td class="py-3 px-4 font-bold text-zinc-900">
                                        <?= e($log['referred_name']) ?>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-zinc-600">
                                        $<?= number_format((float)$log['order_amount'], 2) ?>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                        +$<?= number_format((float)$log['reward_amount'], 4) ?>
                                    </td>
                                    <td class="py-3 px-4 text-right text-zinc-400 whitespace-nowrap">
                                        <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Invited Users List -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-zinc-900">Referred Customers</h3>
                <span class="text-xs text-zinc-400"><?= count($data['referred_users']) ?> users</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-4">User</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Joined On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php if (empty($data['referred_users'])): ?>
                            <tr>
                                <td colspan="3" class="py-8 text-center text-zinc-400">No users registered with your link yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['referred_users'] as $refUser): ?>
                                <tr class="hover:bg-zinc-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-zinc-900"><?= e($refUser['name']) ?></div>
                                        <div class="text-[11px] text-zinc-400"><?= e(substr($refUser['email'], 0, 3) . '***@' . explode('@', $refUser['email'])[1] ?? '') ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Active
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right text-zinc-400 whitespace-nowrap">
                                        <?= date('M d, Y', strtotime($refUser['created_at'])) ?>
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

<script>
function copyReferralLink() {
    const input = document.getElementById('referralUrlInput');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const alertBox = document.getElementById('copySuccessAlert');
        const btnText = document.getElementById('copyBtnText');
        alertBox.classList.remove('hidden');
        btnText.textContent = 'Copied!';
        setTimeout(() => {
            alertBox.classList.add('hidden');
            btnText.textContent = 'Copy Link';
        }, 3000);
    });
}
</script>
