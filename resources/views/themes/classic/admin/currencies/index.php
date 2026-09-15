<?php
/** @var array $admin */
/** @var array $currencies */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Currencies & Exchange Rates</h1>
            <p class="text-sm text-zinc-500 mt-1">Configure supported currencies and conversion multipliers relative to panel base USD.</p>
        </div>
        <button type="button" onclick="openCreateModal()" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Currency
        </button>
    </div>

    <!-- Currencies Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-6">Code</th>
                        <th class="py-3 px-4">Currency Name</th>
                        <th class="py-3 px-4 text-center">Symbol</th>
                        <th class="py-3 px-4 text-right">Exchange Rate (1 USD =)</th>
                        <th class="py-3 px-4 text-center">Default</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($currencies)): ?>
                        <tr><td colspan="7" class="py-8 text-center text-zinc-400">No currencies found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($currencies as $c): ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-zinc-900">
                                    <?= e($c['code']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-zinc-900">
                                    <?= e($c['name']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-800 text-sm">
                                    <?= e($c['symbol']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-zinc-900">
                                    <?= number_format((float)$c['exchange_rate'], 4) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (!empty($c['is_default'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Default Base
                                        </span>
                                    <?php else: ?>
                                        <form action="/admin/currencies/set-default/<?= (int)$c['id'] ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="text-[11px] text-zinc-400 hover:text-zinc-800 underline">Set Default</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border <?= $c['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                        <?= ucfirst($c['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                    <button type="button" onclick='openEditModal(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-lg transition-colors">
                                        Edit Rate
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Currency Modal -->
<div id="createCurrencyModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-zinc-200">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Add New Currency</h3>
            <button type="button" onclick="closeCreateModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="/admin/currencies/create" method="POST" class="space-y-4 mt-4 text-xs">
            <?= csrf_field() ?>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">ISO Code</label>
                    <input type="text" name="code" required placeholder="EUR, INR, BRL" maxlength="10" class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono uppercase">
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Symbol</label>
                    <input type="text" name="symbol" required placeholder="€, ₹, R$" maxlength="5" class="w-full rounded-xl border border-zinc-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Currency Name</label>
                <input type="text" name="name" required placeholder="Euro, Indian Rupee" class="w-full rounded-xl border border-zinc-300 px-3 py-2">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Rate (1 USD = X)</label>
                    <input type="number" name="exchange_rate" step="0.0001" value="1.0000" required class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Status</label>
                    <select name="status" class="w-full rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-xl font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl font-semibold bg-rose-600 hover:bg-rose-700 text-white">Save Currency</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Currency Modal -->
<div id="editCurrencyModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-zinc-200">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900" id="editCurrTitle">Edit Currency</h3>
            <button type="button" onclick="closeEditModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editCurrencyForm" action="" method="POST" class="space-y-4 mt-4 text-xs">
            <?= csrf_field() ?>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Symbol</label>
                    <input type="text" id="editCurrSymbol" name="symbol" required class="w-full rounded-xl border border-zinc-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Exchange Rate (1 USD = X)</label>
                    <input type="number" id="editCurrRate" name="exchange_rate" step="0.0001" required class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Status</label>
                <select id="editCurrStatus" name="status" class="w-full rounded-xl border border-zinc-300 px-3 py-2">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl font-semibold bg-rose-600 hover:bg-rose-700 text-white">Update Rate</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createCurrencyModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createCurrencyModal').classList.add('hidden');
}
function openEditModal(c) {
    document.getElementById('editCurrencyForm').action = '/admin/currencies/update/' + c.id;
    document.getElementById('editCurrTitle').textContent = 'Edit ' + c.name + ' (' + c.code + ')';
    document.getElementById('editCurrSymbol').value = c.symbol;
    document.getElementById('editCurrRate').value = c.exchange_rate;
    document.getElementById('editCurrStatus').value = c.status || 'active';
    document.getElementById('editCurrencyModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editCurrencyModal').classList.add('hidden');
}
</script>
