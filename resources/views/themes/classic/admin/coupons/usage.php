<?php
/** @var array $coupon */
/** @var array $history */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <div class="flex items-center space-x-2">
                <a href="/admin/coupons" class="text-xs text-rose-600 hover:text-rose-700 font-semibold">&larr; Back to Coupons</a>
                <span class="text-zinc-300">/</span>
                <span class="text-xs text-zinc-500 font-mono font-bold"><?= e($coupon['code']) ?></span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 mt-1">Coupon Usage Records</h1>
            <p class="text-sm text-zinc-500 mt-1">Audit log of customers who applied code <strong class="text-zinc-900 font-mono"><?= e($coupon['code']) ?></strong> and discounts applied.</p>
        </div>
        <div class="flex items-center space-x-3 bg-white border border-zinc-200 rounded-2xl px-4 py-2.5 shadow-xs">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Total Discount Credited</div>
                <div class="text-base font-bold text-emerald-600 font-mono">
                    $<?= number_format((float)($coupon['total_discount_given'] ?? 0), 4) ?> USD
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-5">Redemption ID</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Associated Order</th>
                        <th class="py-3 px-4">Service</th>
                        <th class="py-3 px-4 text-right">Discount Received</th>
                        <th class="py-3 px-5 text-right">Redeemed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-zinc-400">No users have redeemed this coupon yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3.5 px-5 font-mono text-zinc-500">
                                    #<?= (int)$h['id'] ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-zinc-900"><?= e($h['user_name']) ?></div>
                                    <div class="text-[11px] text-zinc-400"><?= e($h['user_email']) ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($h['order_id'])): ?>
                                        <a href="/admin/orders/<?= (int)$h['order_id'] ?>" class="font-mono font-bold text-rose-600 hover:text-rose-700 underline">
                                            #<?= (int)$h['order_id'] ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-zinc-400">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-800">
                                    <?= e($h['service_name'] ?? 'Direct Checkout') ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600">
                                    $<?= number_format((float)$h['discount_received'], 4) ?>
                                </td>
                                <td class="py-3.5 px-5 text-right text-zinc-500 whitespace-nowrap">
                                    <?= date('M d, Y H:i:s', strtotime($h['used_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
