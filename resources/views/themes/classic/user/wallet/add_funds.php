<?php
/** @var array $user */
/** @var array $wallet */
/** @var array $gateways */
/** @var array $recentPayments */
?>

<div class="max-w-5xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Add Funds to Wallet</h1>
            <p class="text-sm text-zinc-500 mt-1">Select your preferred payment method to top up your account balance instantly.</p>
        </div>
        <div class="bg-white border border-zinc-200 rounded-2xl px-4 py-2.5 shadow-xs flex items-center space-x-3">
            <div class="h-9 w-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base">
                $
            </div>
            <div>
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Current Balance</div>
                <div class="text-base font-bold text-zinc-900">$<?= number_format((float)($wallet['balance'] ?? 0), 4) ?> <span class="text-xs font-medium text-zinc-400">USD</span></div>
            </div>
        </div>
    </div>

    <!-- Deposit Form & Gateway Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Payment Form -->
        <div class="lg:col-span-2 bg-white border border-zinc-200/80 rounded-2xl p-6 sm:p-8 shadow-xs">
            <form action="/wallet/add-funds" method="POST" id="addFundsForm" class="space-y-6">
                <?= csrf_field() ?>

                <!-- Gateway Selector Cards -->
                <div>
                    <label class="block text-sm font-semibold text-zinc-800 mb-3">Select Payment Gateway</label>
                    <?php if (empty($gateways)): ?>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
                            No payment gateways are currently active. Please contact site administrator.
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="gatewayCards">
                            <?php foreach ($gateways as $idx => $gw): ?>
                                <label class="relative flex cursor-pointer rounded-xl border border-zinc-200 p-4 hover:border-zinc-300 transition-all focus:outline-none gateway-radio-label <?= $idx === 0 ? 'border-rose-500 bg-rose-50/20 ring-2 ring-rose-500/20' : 'bg-white' ?>">
                                    <input type="radio" name="gateway_id" value="<?= (int)$gw['id'] ?>" class="sr-only" <?= $idx === 0 ? 'checked' : '' ?>
                                           data-min="<?= (float)$gw['min_amount'] ?>"
                                           data-max="<?= (float)$gw['max_amount'] ?>"
                                           data-fee-pct="<?= (float)$gw['fee_percentage'] ?>"
                                           data-fee-fix="<?= (float)$gw['fee_fixed'] ?>"
                                           data-currency="<?= e($gw['currency']) ?>"
                                           data-instructions="<?= e($gw['instructions']) ?>">
                                    <div class="flex w-full items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-8 w-8 rounded-lg bg-zinc-100 flex items-center justify-center font-bold text-zinc-700 text-xs uppercase">
                                                <?= substr($gw['name'], 0, 2) ?>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-zinc-900"><?= e($gw['name']) ?></div>
                                                <div class="text-xs text-zinc-500 font-medium mt-0.5">
                                                    Fee: <?= (float)$gw['fee_percentage'] > 0 ? (float)$gw['fee_percentage'] . '%' : '0%' ?>
                                                    <?= (float)$gw['fee_fixed'] > 0 ? ' + $' . number_format((float)$gw['fee_fixed'], 2) : '' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Deposit Amount -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="deposit_amount" class="block text-sm font-semibold text-zinc-800">Deposit Amount (USD)</label>
                        <span class="text-xs text-zinc-500 font-medium" id="limitBadge">Min: $1.00 | Max: $1,000.00</span>
                    </div>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400 font-bold text-sm">$</div>
                        <input type="number" id="deposit_amount" name="amount" required step="0.01" min="1" placeholder="50.00" class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 pl-8 pr-4 py-3 text-sm text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition-all">
                    </div>
                </div>

                <!-- Summary Breakdown Box -->
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/80 p-4 space-y-2 text-xs">
                    <div class="flex justify-between text-zinc-600">
                        <span>Deposit to Wallet:</span>
                        <span class="font-semibold text-zinc-900" id="summaryDeposit">$0.00</span>
                    </div>
                    <div class="flex justify-between text-zinc-600">
                        <span>Gateway Fee:</span>
                        <span class="font-semibold text-zinc-900" id="summaryFee">$0.00</span>
                    </div>
                    <div class="border-t border-zinc-200 pt-2 flex justify-between text-sm font-bold text-zinc-900">
                        <span>Total To Pay:</span>
                        <span class="text-rose-600 font-extrabold" id="summaryTotal">$0.00 USD</span>
                    </div>
                </div>

                <button type="submit" class="w-full flex items-center justify-center rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-semibold py-3.5 px-6 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                    Proceed to Payment &rarr;
                </button>
            </form>
        </div>

        <!-- Right: Gateway Instructions & Security Info -->
        <div class="space-y-6">
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
                <h3 class="text-base font-bold text-zinc-900 pb-3 border-b border-zinc-100">Instructions & Guidelines</h3>
                <div class="mt-4 text-xs text-zinc-600 leading-relaxed space-y-3" id="gwInstructions">
                    Select a payment method on the left to review its transaction limits and processing speed.
                </div>
            </div>

            <div class="bg-gradient-to-br from-zinc-900 to-zinc-800 text-white rounded-2xl p-5 shadow-xs">
                <div class="text-rose-400 text-xs font-bold uppercase tracking-wider mb-2">Automated Instant Crediting</div>
                <p class="text-xs text-zinc-300 leading-relaxed">
                    Online gateways (Stripe, PayPal, Crypto) credit your wallet automatically upon confirmation. For bank wire transfers, staff verify your deposit reference upon wire receipt.
                </p>
            </div>
        </div>
    </div>

    <!-- Recent Payments History Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-zinc-900">Recent Payment Transactions</h2>
            <a href="/wallet" class="text-xs font-semibold text-rose-600 hover:text-rose-700">View All Wallet History &rarr;</a>
        </div>

        <?php if (empty($recentPayments)): ?>
            <div class="p-8 text-center text-xs text-zinc-500">No payment records found.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                            <th class="py-3 px-6">Transaction Ref</th>
                            <th class="py-3 px-4">Gateway</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4 text-right">Total Paid</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-6 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                        <?php foreach ($recentPayments as $p): ?>
                            <tr>
                                <td class="py-3.5 px-6 font-mono font-bold text-zinc-900"><?= e($p['transaction_id']) ?></td>
                                <td class="py-3.5 px-4"><?= e($p['gateway_name']) ?></td>
                                <td class="py-3.5 px-4 text-right font-bold text-zinc-900">$<?= number_format((float)$p['amount'], 2) ?></td>
                                <td class="py-3.5 px-4 text-right text-zinc-600">$<?= number_format((float)$p['net_amount'], 2) ?></td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php
                                    $st = $p['status'];
                                    $badge = match ($st) {
                                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-zinc-100 text-zinc-700 border-zinc-200'
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border <?= $badge ?>">
                                        <?= ucfirst($st) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right text-zinc-400 font-mono"><?= date('M j, Y H:i', strtotime($p['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const radioLabels = document.querySelectorAll('.gateway-radio-label');
    const amountInput = document.getElementById('deposit_amount');
    const limitBadge = document.getElementById('limitBadge');
    const summaryDeposit = document.getElementById('summaryDeposit');
    const summaryFee = document.getElementById('summaryFee');
    const summaryTotal = document.getElementById('summaryTotal');
    const gwInstructions = document.getElementById('gwInstructions');

    function getSelectedRadio() {
        return document.querySelector('input[name="gateway_id"]:checked');
    }

    function updateGatewaySelection() {
        radioLabels.forEach(label => {
            const radio = label.querySelector('input[type="radio"]');
            if (radio.checked) {
                label.classList.add('border-rose-500', 'bg-rose-50/20', 'ring-2', 'ring-rose-500/20');
                label.classList.remove('border-zinc-200', 'bg-white');

                const min = parseFloat(radio.dataset.min);
                const max = parseFloat(radio.dataset.max);
                limitBadge.textContent = `Min: $${min.toFixed(2)} | Max: $${max.toFixed(2)}`;
                amountInput.min = min;
                amountInput.max = max;

                gwInstructions.textContent = radio.dataset.instructions || 'Standard payment gateway with automated verification.';
            } else {
                label.classList.remove('border-rose-500', 'bg-rose-50/20', 'ring-2', 'ring-rose-500/20');
                label.classList.add('border-zinc-200', 'bg-white');
            }
        });
        calculateFees();
    }

    function calculateFees() {
        const radio = getSelectedRadio();
        if (!radio) return;

        const amt = parseFloat(amountInput.value) || 0;
        const feePct = parseFloat(radio.dataset.feePct) || 0;
        const feeFix = parseFloat(radio.dataset.feeFix) || 0;

        const fee = (amt * (feePct / 100.0)) + feeFix;
        const total = amt + fee;

        summaryDeposit.textContent = `$${amt.toFixed(2)}`;
        summaryFee.textContent = `$${fee.toFixed(2)}`;
        summaryTotal.textContent = `$${total.toFixed(2)} USD`;
    }

    radioLabels.forEach(label => {
        label.addEventListener('click', () => {
            const radio = label.querySelector('input[type="radio"]');
            radio.checked = true;
            updateGatewaySelection();
        });
    });

    amountInput.addEventListener('input', calculateFees);

    updateGatewaySelection();
});
</script>
