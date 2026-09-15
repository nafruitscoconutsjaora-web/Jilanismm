<?php
/** @var array $user */
/** @var array $payment */
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="/wallet/add-funds" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-900">
            &larr; Back to Payment Gateways
        </a>
        <a href="/wallet" class="inline-flex items-center text-xs font-semibold text-rose-600 hover:text-rose-700">
            My Wallet &rarr;
        </a>
    </div>

    <!-- Invoice Card -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-100">
            <div>
                <span class="text-xs uppercase tracking-wider text-rose-600 font-bold">Deposit Invoice</span>
                <h1 class="text-2xl font-extrabold text-zinc-900 mt-1">Ref: <?= e($payment['transaction_id']) ?></h1>
                <p class="text-xs text-zinc-400 mt-1">Generated on <?= date('M j, Y H:i:s', strtotime($payment['created_at'])) ?></p>
            </div>
            <div class="text-right">
                <div class="text-xs text-zinc-400 font-medium">Payment Method</div>
                <div class="text-base font-bold text-zinc-900"><?= e($payment['gateway_name']) ?></div>
            </div>
        </div>

        <!-- Wire / Manual Instructions Box -->
        <div class="mt-6 bg-zinc-50 border border-zinc-200/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-wider">Wire Transfer Instructions</h3>
            <div class="text-xs text-zinc-700 leading-relaxed whitespace-pre-line bg-white p-4 rounded-xl border border-zinc-200">
                <?= e($payment['instructions'] ?? 'Please wire funds to the designated account.') ?>
            </div>

            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-xs text-rose-900 space-y-1">
                <div class="font-bold">Important Notice:</div>
                <p>Include your Transaction Reference <strong class="font-mono bg-white px-1.5 py-0.5 rounded border border-rose-300"><?= e($payment['transaction_id']) ?></strong> in the payment remarks/memo. Once our finance team verifies the funds, your wallet balance will be updated automatically.</p>
            </div>
        </div>

        <!-- Amounts Breakdown -->
        <div class="mt-6 border-t border-zinc-100 pt-6 space-y-3 text-sm">
            <div class="flex justify-between text-zinc-600">
                <span>Wallet Credit Amount:</span>
                <span class="font-bold text-zinc-900">$<?= number_format((float)$payment['amount'], 2) ?> USD</span>
            </div>
            <div class="flex justify-between text-zinc-600">
                <span>Transaction Processing Fee:</span>
                <span class="font-bold text-zinc-900">$<?= number_format((float)$payment['fee'], 2) ?> USD</span>
            </div>
            <div class="flex justify-between text-base font-extrabold text-zinc-900 border-t border-zinc-200 pt-3">
                <span>Total Amount To Send:</span>
                <span class="text-rose-600">$<?= number_format((float)$payment['net_amount'], 2) ?> <?= e($payment['currency']) ?></span>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-between pt-6 border-t border-zinc-100">
            <a href="/wallet" class="px-5 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl">
                Return to Wallet
            </a>
            <span class="text-xs text-zinc-400">Current Status: <strong class="text-amber-600"><?= ucfirst($payment['status']) ?></strong></span>
        </div>
    </div>
</div>
