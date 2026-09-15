<?php
/** @var array $admin */
/** @var array $provider */
/** @var array $providerServices */
/** @var array $categories */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <div class="flex items-center space-x-2">
                <a href="/admin/providers" class="text-xs font-semibold text-zinc-500 hover:text-zinc-900">&larr; Providers</a>
                <span class="text-zinc-300">/</span>
                <span class="text-xs font-bold text-zinc-900"><?= e($provider['name']) ?></span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 mt-1">Synced Upstream Services (<?= count($providerServices) ?>)</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Direct raw catalog synced via API from <?= e($provider['name']) ?>. Import or map these directly to your customer catalog.</p>
        </div>
        <div class="flex items-center space-x-3">
            <form action="/admin/providers/sync/<?= (int)$provider['id'] ?>" method="POST" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Re-Sync Provider Services
                </button>
            </form>
        </div>
    </div>

    <!-- Search / Filter -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs">
        <input type="text" id="provSearch" placeholder="Search synced services by external ID, name, category..." class="w-full text-xs rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
    </div>

    <!-- Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="provTable">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-6">API Service ID</th>
                        <th class="py-3 px-4">Service Name</th>
                        <th class="py-3 px-4">Upstream Category</th>
                        <th class="py-3 px-4 text-right">Provider Rate</th>
                        <th class="py-3 px-4 text-center">Min / Max</th>
                        <th class="py-3 px-4 text-center">Mapping Status</th>
                        <th class="py-3 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($providerServices)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-zinc-400">
                                No services found for this provider. Click "Re-Sync Provider Services" above to fetch the catalog.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($providerServices as $ps): ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors prov-row"
                                data-name="<?= strtolower(e($ps['name'])) ?>"
                                data-id="<?= e($ps['provider_service_id']) ?>"
                                data-cat="<?= strtolower(e($ps['category'] ?? '')) ?>">
                                <td class="py-3.5 px-6 font-mono font-bold text-zinc-900">
                                    #<?= e($ps['provider_service_id']) ?>
                                </td>
                                <td class="py-3.5 px-4 max-w-sm">
                                    <div class="font-bold text-zinc-900 text-xs truncate" title="<?= e($ps['name']) ?>"><?= e($ps['name']) ?></div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[9px] font-mono text-zinc-400"><?= e($ps['type'] ?? 'default') ?></span>
                                        <?php if (!empty($ps['refill'])): ?>
                                            <span class="text-[9px] font-semibold bg-emerald-50 text-emerald-700 px-1 rounded">Refill</span>
                                        <?php endif; ?>
                                        <?php if (!empty($ps['dripfeed'])): ?>
                                            <span class="text-[9px] font-semibold bg-blue-50 text-blue-700 px-1 rounded">Drip</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600">
                                    <?= e($ps['category'] ?? 'Uncategorized') ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-zinc-900">
                                    $<?= number_format((float)($ps['rate'] ?? 0), 4) ?> <?= e($provider['currency']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono text-zinc-600 text-[11px]">
                                    <?= number_format((int)$ps['min']) ?> - <?= number_format((int)$ps['max']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (!empty($ps['mapped_service_id'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Mapped to #<?= (int)$ps['mapped_service_id'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-100 text-zinc-600">
                                            Not Mapped
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                    <button type="button" onclick='openMapModal(<?= json_encode($ps, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                                        <?= !empty($ps['mapped_service_id']) ? 'Re-Map Service' : 'Import / Map' ?>
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

<!-- Map Service Modal -->
<div id="mapServiceModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-zinc-200">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Map Upstream Service to Panel</h3>
            <button type="button" onclick="closeMapModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="/admin/providers/map" method="POST" class="space-y-4 mt-4">
            <?= csrf_field() ?>
            <input type="hidden" id="mapProviderServiceId" name="provider_service_id" value="">

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Target Panel Category</label>
                <select name="category_id" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Public Service Name</label>
                <input type="text" id="mapServiceName" name="service_name" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Markup Type</label>
                    <select name="markup_type" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Markup Value</label>
                    <input type="number" name="markup_value" step="0.01" value="20" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2">
                </div>
            </div>

            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200 text-xs text-zinc-600 space-y-1">
                <div class="flex justify-between">
                    <span>Original Provider Rate:</span>
                    <span class="font-bold text-zinc-900" id="mapOriginalRate">$0.00</span>
                </div>
                <div class="flex justify-between">
                    <span>Provider Service ID:</span>
                    <span class="font-mono text-zinc-700" id="mapExtId">#0</span>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeMapModal()" class="px-4 py-2 rounded-xl text-xs font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">Import & Publish Service</button>
            </div>
        </form>
    </div>
</div>

<script>
function openMapModal(ps) {
    document.getElementById('mapProviderServiceId').value = ps.id;
    document.getElementById('mapServiceName').value = ps.name;
    document.getElementById('mapExtId').textContent = '#' + ps.provider_service_id;
    document.getElementById('mapOriginalRate').textContent = '$' + parseFloat(ps.rate).toFixed(4) + ' <?= e($provider['currency']) ?>';
    document.getElementById('mapServiceModal').classList.remove('hidden');
}
function closeMapModal() {
    document.getElementById('mapServiceModal').classList.add('hidden');
}

// Search
const pSearch = document.getElementById('provSearch');
const pRows = document.querySelectorAll('.prov-row');
if (pSearch) {
    pSearch.addEventListener('input', () => {
        const q = pSearch.value.toLowerCase().trim();
        pRows.forEach(r => {
            const name = r.dataset.name || '';
            const id = r.dataset.id || '';
            const cat = r.dataset.cat || '';
            r.style.display = (!q || name.includes(q) || id.includes(q) || cat.includes(q)) ? '' : 'none';
        });
    });
}
</script>
