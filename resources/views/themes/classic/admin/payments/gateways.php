<?php
/** @var array $admin */
/** @var array $gateways */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <div class="flex items-center space-x-2">
                <a href="/admin/payments" class="text-xs font-semibold text-zinc-500 hover:text-zinc-900">&larr; Payments</a>
                <span class="text-zinc-300">/</span>
                <span class="text-xs font-bold text-zinc-900">Gateways</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 mt-1">Payment Gateways Configuration</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Manage automated deposit channels, merchant API credentials, transaction fees, and minimum limits.</p>
        </div>
    </div>

    <!-- Gateways Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($gateways as $gw): ?>
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-xl bg-zinc-100 flex items-center justify-center font-bold text-zinc-700 text-sm uppercase">
                                <?= substr($gw['code'], 0, 3) ?>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-zinc-900"><?= e($gw['name']) ?></h3>
                                <span class="text-xs font-mono text-zinc-400">code: <?= e($gw['code']) ?> (<?= e($gw['currency']) ?>)</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border <?= $gw['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                            <?= ucfirst($gw['status']) ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4 text-xs">
                        <div class="bg-zinc-50 p-3 rounded-xl border border-zinc-100">
                            <span class="text-zinc-400 font-semibold uppercase text-[10px]">Processing Fee</span>
                            <div class="font-bold text-zinc-900 mt-0.5">
                                <?= (float)$gw['fee_percentage'] ?>% <?= (float)$gw['fee_fixed'] > 0 ? '+ $' . number_format((float)$gw['fee_fixed'], 2) : '' ?>
                            </div>
                        </div>
                        <div class="bg-zinc-50 p-3 rounded-xl border border-zinc-100">
                            <span class="text-zinc-400 font-semibold uppercase text-[10px]">Deposit Limits</span>
                            <div class="font-bold text-zinc-900 mt-0.5 font-mono">
                                $<?= number_format((float)$gw['min_amount'], 2) ?> - $<?= number_format((float)$gw['max_amount'], 2) ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($gw['instructions'])): ?>
                        <div class="mt-3 text-xs text-zinc-600 bg-zinc-50/50 p-3 rounded-xl border border-zinc-100 line-clamp-2">
                            <?= e($gw['instructions']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pt-4 border-t border-zinc-100 flex items-center justify-end">
                    <button type="button" onclick='openGatewayModal(<?= json_encode($gw, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-4 py-2 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl transition-colors">
                        Configure Settings &rarr;
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Edit Gateway Modal -->
<div id="gatewayModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-zinc-200 my-8">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900" id="gwModalTitle">Configure Gateway</h3>
            <button type="button" onclick="closeGatewayModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="gatewayForm" action="" method="POST" class="space-y-4 mt-4 text-xs">
            <?= csrf_field() ?>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Display Name</label>
                    <input type="text" id="gwName" name="name" required class="w-full rounded-xl border border-zinc-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Status</label>
                    <select id="gwStatus" name="status" class="w-full rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Min Deposit (USD)</label>
                    <input type="number" id="gwMin" name="min_amount" step="0.01" required class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Max Deposit (USD)</label>
                    <input type="number" id="gwMax" name="max_amount" step="0.01" required class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Fee Percentage (%)</label>
                    <input type="number" id="gwFeePct" name="fee_percentage" step="0.01" class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Fixed Fee ($)</label>
                    <input type="number" id="gwFeeFix" name="fee_fixed" step="0.01" class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Instructions / Bank Wire Details</label>
                <textarea id="gwInstructions" name="instructions" rows="3" class="w-full rounded-xl border border-zinc-300 px-3 py-2"></textarea>
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeGatewayModal()" class="px-4 py-2 rounded-xl font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl font-semibold bg-rose-600 hover:bg-rose-700 text-white">Save Configuration</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGatewayModal(gw) {
    document.getElementById('gatewayForm').action = '/admin/gateways/edit/' + gw.id;
    document.getElementById('gwModalTitle').textContent = 'Configure ' + gw.name;
    document.getElementById('gwName').value = gw.name;
    document.getElementById('gwStatus').value = gw.status || 'active';
    document.getElementById('gwMin').value = gw.min_amount;
    document.getElementById('gwMax').value = gw.max_amount;
    document.getElementById('gwFeePct').value = gw.fee_percentage;
    document.getElementById('gwFeeFix').value = gw.fee_fixed;
    document.getElementById('gwInstructions').value = gw.instructions || '';
    document.getElementById('gatewayModal').classList.remove('hidden');
}
function closeGatewayModal() {
    document.getElementById('gatewayModal').classList.add('hidden');
}
</script>
