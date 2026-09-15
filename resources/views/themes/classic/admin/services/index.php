<?php
/** @var array $admin */
/** @var array $services */
/** @var array $categories */
/** @var array $providers */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Services Catalog Management</h1>
            <p class="text-sm text-zinc-500 mt-1">Configure service rates, upstream API provider mappings, margin markups, and customer limits.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="/admin/providers" class="inline-flex items-center px-3.5 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-semibold rounded-xl transition-colors">
                Manage Providers &rarr;
            </a>
            <button type="button" onclick="openCreateModal()" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Service
            </button>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-center gap-3">
        <div class="relative flex-1 w-full">
            <input type="text" id="serviceSearch" placeholder="Filter by ID, name or provider..." class="w-full pl-3 pr-4 py-2 text-xs bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
        </div>
        <div class="w-full sm:w-56">
            <select id="categoryFilter" class="w-full py-2 px-3 text-xs bg-zinc-50 border border-zinc-200 rounded-xl font-medium text-zinc-800">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c['name']) ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Services Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="servicesTable">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-4 sm:px-6">ID</th>
                        <th class="py-3 px-4">Service Name</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Provider / External ID</th>
                        <th class="py-3 px-4 text-right">Provider Cost</th>
                        <th class="py-3 px-4 text-right">Markup</th>
                        <th class="py-3 px-4 text-right">Customer Price</th>
                        <th class="py-3 px-4 text-center">Limits</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="10" class="py-8 text-center text-zinc-400">No services configured yet. Click "Add New Service" above.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $s): ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors service-row"
                                data-name="<?= strtolower(e($s['name'])) ?>"
                                data-id="<?= (int)$s['id'] ?>"
                                data-provider="<?= strtolower(e($s['provider_name'] ?? '')) ?>"
                                data-category="<?= e($s['category_name'] ?? '') ?>">
                                <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-zinc-900">#<?= (int)$s['id'] ?></td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="font-bold text-zinc-900 text-xs truncate" title="<?= e($s['name']) ?>"><?= e($s['name']) ?></div>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <?php if ($s['refill']): ?>
                                            <span class="text-[9px] font-semibold bg-emerald-50 text-emerald-700 px-1 rounded">Refill</span>
                                        <?php endif; ?>
                                        <?php if ($s['dripfeed']): ?>
                                            <span class="text-[9px] font-semibold bg-blue-50 text-blue-700 px-1 rounded">Drip</span>
                                        <?php endif; ?>
                                        <span class="text-[9px] font-mono text-zinc-400"><?= e($s['service_type']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="rounded bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-700">
                                        <?= e($s['category_name'] ?? 'None') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs">
                                    <?php if (!empty($s['provider_name'])): ?>
                                        <span class="font-semibold text-zinc-900"><?= e($s['provider_name']) ?></span>
                                        <span class="text-zinc-400">(#<?= e($s['provider_service_id']) ?>)</span>
                                    <?php else: ?>
                                        <span class="text-zinc-400 italic">Manual / None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-zinc-600">
                                    $<?= number_format((float)($s['provider_price'] ?? 0), 4) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-xs text-rose-600 font-semibold">
                                    <?php if (($s['markup_type'] ?? 'percentage') === 'percentage'): ?>
                                        +<?= (float)($s['markup_percentage'] ?? 0) ?>%
                                    <?php else: ?>
                                        +$<?= number_format((float)($s['markup_fixed'] ?? 0), 4) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-zinc-900 font-bold text-xs">
                                    $<?= number_format((float)($s['customer_price'] > 0 ? $s['customer_price'] : $s['price_per_k']), 4) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono text-[11px] text-zinc-500">
                                    <?= number_format((int)$s['min_quantity']) ?> - <?= number_format((int)$s['max_quantity']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border <?= $s['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                        <?= ucfirst($s['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap space-x-1">
                                    <!-- Edit Button -->
                                    <button type="button" onclick='openEditModal(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-lg transition-colors">
                                        Edit
                                    </button>

                                    <!-- Status Toggle Form -->
                                    <form action="/admin/services/status/<?= (int)$s['id'] ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2 py-1 text-xs font-semibold rounded-lg border transition-colors <?= $s['status'] === 'active' ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' ?>">
                                            <?= $s['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>

                                    <!-- Delete Form -->
                                    <form action="/admin/services/delete/<?= (int)$s['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Delete this service? This cannot be undone.')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2 py-1 border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-semibold rounded-lg transition-colors">
                                            Delete
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

<!-- Create Service Modal -->
<div id="createServiceModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-zinc-200 my-8">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Add New Service</h3>
            <button type="button" onclick="closeCreateModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="/admin/services/create" method="POST" class="space-y-4 mt-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Category</label>
                    <select name="category_id" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Service Type</label>
                    <select name="service_type" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="default">Default (Standard)</option>
                        <option value="custom_comments">Custom Comments</option>
                        <option value="mentions">Mentions</option>
                        <option value="package">Package</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Service Name</label>
                <input type="text" name="name" required placeholder="e.g. Instagram Followers [HQ Real - Instant]" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">API Provider</label>
                    <select name="provider_id" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="">-- No Provider (Manual) --</option>
                        <?php foreach ($providers as $p): ?>
                            <option value="<?= (int)$p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['currency']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Provider Service ID</label>
                    <input type="text" name="provider_service_id" placeholder="e.g. 1042" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Markup Type</label>
                    <select name="markup_type" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Markup Value</label>
                    <input type="number" name="markup_value" step="0.01" value="20" placeholder="20" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Base Price / 1k (Manual)</label>
                    <input type="number" name="price_per_k" step="0.0001" value="1.0000" placeholder="1.00" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Min Quantity</label>
                    <input type="number" name="min_quantity" value="10" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Max Quantity</label>
                    <input type="number" name="max_quantity" value="10000" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Description & Guidelines</label>
                <textarea name="description" rows="3" placeholder="Service specifications, start time, drop rates..." class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2"></textarea>
            </div>

            <div class="flex items-center space-x-6">
                <label class="flex items-center space-x-2 text-xs text-zinc-700 cursor-pointer">
                    <input type="checkbox" name="refill" value="1" class="rounded border-zinc-300 text-rose-600 focus:ring-rose-500">
                    <span>Refill Button</span>
                </label>
                <label class="flex items-center space-x-2 text-xs text-zinc-700 cursor-pointer">
                    <input type="checkbox" name="dripfeed" value="1" class="rounded border-zinc-300 text-rose-600 focus:ring-rose-500">
                    <span>Drip-Feed Supported</span>
                </label>
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-xl text-xs font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">Create Service</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Service Modal -->
<div id="editServiceModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-zinc-200 my-8">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Edit Service</h3>
            <button type="button" onclick="closeEditModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editServiceForm" action="" method="POST" class="space-y-4 mt-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Category</label>
                    <select id="editCategoryId" name="category_id" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Service Type</label>
                    <select id="editServiceType" name="service_type" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="default">Default (Standard)</option>
                        <option value="custom_comments">Custom Comments</option>
                        <option value="mentions">Mentions</option>
                        <option value="package">Package</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Service Name</label>
                <input type="text" id="editServiceName" name="name" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">API Provider</label>
                    <select id="editProviderId" name="provider_id" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="">-- No Provider (Manual) --</option>
                        <?php foreach ($providers as $p): ?>
                            <option value="<?= (int)$p['id'] ?>"><?= e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Provider Service ID</label>
                    <input type="text" id="editProviderServiceId" name="provider_service_id" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Markup Type</label>
                    <select id="editMarkupType" name="markup_type" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Markup Value</label>
                    <input type="number" id="editMarkupValue" name="markup_value" step="0.01" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Base Price / 1k</label>
                    <input type="number" id="editPricePerK" name="price_per_k" step="0.0001" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Min Quantity</label>
                    <input type="number" id="editMinQuantity" name="min_quantity" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Max Quantity</label>
                    <input type="number" id="editMaxQuantity" name="max_quantity" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Description</label>
                <textarea id="editDescription" name="description" rows="3" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                <label class="flex items-center space-x-2 text-xs text-zinc-700 cursor-pointer">
                    <input type="checkbox" id="editRefill" name="refill" value="1" class="rounded border-zinc-300 text-rose-600 focus:ring-rose-500">
                    <span>Refill</span>
                </label>
                <label class="flex items-center space-x-2 text-xs text-zinc-700 cursor-pointer">
                    <input type="checkbox" id="editDripfeed" name="dripfeed" value="1" class="rounded border-zinc-300 text-rose-600 focus:ring-rose-500">
                    <span>Drip-Feed</span>
                </label>
                <div>
                    <select id="editStatus" name="status" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
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
    document.getElementById('createServiceModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createServiceModal').classList.add('hidden');
}
function openEditModal(s) {
    document.getElementById('editServiceForm').action = '/admin/services/edit/' + s.id;
    document.getElementById('editCategoryId').value = s.category_id;
    document.getElementById('editServiceName').value = s.name;
    document.getElementById('editServiceType').value = s.service_type || 'default';
    document.getElementById('editProviderId').value = s.provider_id || '';
    document.getElementById('editProviderServiceId').value = s.provider_service_id || '';
    document.getElementById('editMarkupType').value = s.markup_type || 'percentage';
    document.getElementById('editMarkupValue').value = (s.markup_type === 'fixed' ? s.markup_fixed : s.markup_percentage) || 0;
    document.getElementById('editPricePerK').value = s.price_per_k || 1.0;
    document.getElementById('editMinQuantity').value = s.min_quantity;
    document.getElementById('editMaxQuantity').value = s.max_quantity;
    document.getElementById('editDescription').value = s.description || '';
    document.getElementById('editRefill').checked = !!s.refill;
    document.getElementById('editDripfeed').checked = !!s.dripfeed;
    document.getElementById('editStatus').value = s.status || 'active';
    document.getElementById('editServiceModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editServiceModal').classList.add('hidden');
}

// Live filtering
const search = document.getElementById('serviceSearch');
const catFilter = document.getElementById('categoryFilter');
const rows = document.querySelectorAll('.service-row');

function applyFilter() {
    const q = (search.value || '').toLowerCase().trim();
    const c = catFilter.value;
    rows.forEach(r => {
        const name = r.dataset.name || '';
        const id = r.dataset.id || '';
        const prov = r.dataset.provider || '';
        const cat = r.dataset.category || '';

        const matchesQ = !q || name.includes(q) || id.includes(q) || prov.includes(q);
        const matchesC = (c === 'all') || (cat === c);

        r.style.display = (matchesQ && matchesC) ? '' : 'none';
    });
}
if (search) search.addEventListener('input', applyFilter);
if (catFilter) catFilter.addEventListener('change', applyFilter);
</script>
