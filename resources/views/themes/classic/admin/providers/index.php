<?php
/** @var array $admin */
/** @var array $providers */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Upstream API Providers</h1>
            <p class="text-sm text-zinc-500 mt-1">Connect external SMM providers (standard SMM v2 APIs) for automated service sync and order fulfillment.</p>
        </div>
        <button type="button" onclick="openCreateModal()" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add API Provider
        </button>
    </div>

    <!-- Providers Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-6">ID</th>
                        <th class="py-3 px-4">Provider Name</th>
                        <th class="py-3 px-4">API Endpoint</th>
                        <th class="py-3 px-4">API Key</th>
                        <th class="py-3 px-4 text-right">Live Balance</th>
                        <th class="py-3 px-4 text-center">Services Synced</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($providers)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-zinc-400">No providers configured yet. Click "Add API Provider" above.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($providers as $p): ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-zinc-900">#<?= (int)$p['id'] ?></td>
                                <td class="py-3.5 px-4 font-bold text-zinc-900 text-sm">
                                    <?= e($p['name']) ?>
                                    <?php if (!empty($p['description'])): ?>
                                        <div class="text-[11px] font-normal text-zinc-400"><?= e($p['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-zinc-600 max-w-xs truncate" title="<?= e($p['api_url']) ?>">
                                    <?= e($p['api_url']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-zinc-400">
                                    ••••••••<?= substr($p['api_key'] ?? '', -4) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-bold text-zinc-900 font-mono text-xs">
                                        <?= number_format((float)($p['balance'] ?? 0), 2) ?> <?= e($p['currency'] ?? 'USD') ?>
                                    </div>
                                    <form action="/admin/providers/balance/<?= (int)$p['id'] ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-[10px] text-rose-600 hover:text-rose-700 underline font-semibold">
                                            Sync Balance
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="/admin/providers/services/<?= (int)$p['id'] ?>" class="inline-flex items-center px-2 py-0.5 rounded-full bg-zinc-100 hover:bg-zinc-200 text-zinc-800 font-bold text-[11px]">
                                        <?= (int)($p['service_count'] ?? 0) ?> services &rarr;
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border <?= $p['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right whitespace-nowrap space-x-1">
                                    <!-- Sync Services Button -->
                                    <form action="/admin/providers/sync/<?= (int)$p['id'] ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2.5 py-1 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-lg transition-colors">
                                            Sync Services
                                        </button>
                                    </form>

                                    <!-- Edit Button -->
                                    <button type="button" onclick='openEditModal(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-lg transition-colors">
                                        Edit
                                    </button>

                                    <!-- Status Toggle Form -->
                                    <form action="/admin/providers/status/<?= (int)$p['id'] ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2 py-1 text-xs font-semibold rounded-lg border transition-colors <?= $p['status'] === 'active' ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' ?>">
                                            <?= $p['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Provider Modal -->
<div id="createProviderModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-zinc-200">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Add API Provider</h3>
            <button type="button" onclick="closeCreateModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="/admin/providers/create" method="POST" class="space-y-4 mt-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Provider Name</label>
                <input type="text" name="name" required placeholder="e.g. JustAnotherPanel, SMMKings" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">API URL (Endpoint)</label>
                <input type="url" name="api_url" required placeholder="https://provider.example.com/api/v2" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2 font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">API Key</label>
                <input type="password" name="api_key" required placeholder="Your provider API secret key" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2 font-mono">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Provider Currency</label>
                    <select name="currency" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="INR">INR (₹)</option>
                        <option value="RUB">RUB (₽)</option>
                        <option value="BRL">BRL (R$)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Status</label>
                    <select name="status" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Internal Description</label>
                <textarea name="description" rows="2" placeholder="Optional notes about this provider..." class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2"></textarea>
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-xl text-xs font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">Save Provider</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Provider Modal -->
<div id="editProviderModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-zinc-200">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Edit Provider</h3>
            <button type="button" onclick="closeEditModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editProviderForm" action="" method="POST" class="space-y-4 mt-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Provider Name</label>
                <input type="text" id="editName" name="name" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">API URL</label>
                <input type="url" id="editApiUrl" name="api_url" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2 font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">API Key (Leave blank to keep unchanged)</label>
                <input type="password" name="api_key" placeholder="••••••••••••••••" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2 font-mono">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Provider Currency</label>
                    <select id="editCurrency" name="currency" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="INR">INR</option>
                        <option value="RUB">RUB</option>
                        <option value="BRL">BRL</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Status</label>
                    <select id="editStatus" name="status" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Description</label>
                <textarea id="editDescription" name="description" rows="2" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2"></textarea>
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-xs font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createProviderModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createProviderModal').classList.add('hidden');
}
function openEditModal(p) {
    document.getElementById('editProviderForm').action = '/admin/providers/edit/' + p.id;
    document.getElementById('editName').value = p.name;
    document.getElementById('editApiUrl').value = p.api_url;
    document.getElementById('editCurrency').value = p.currency || 'USD';
    document.getElementById('editStatus').value = p.status || 'active';
    document.getElementById('editDescription').value = p.description || '';
    document.getElementById('editProviderModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editProviderModal').classList.add('hidden');
}
</script>
