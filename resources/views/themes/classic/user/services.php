<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Services Catalog</h1>
            <p class="mt-1 text-sm text-zinc-500">Live directory of all marketing automation channels, rates, and parameters.</p>
        </div>
        <a href="/order/new" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Place an Order
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-center gap-4">
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="serviceSearch" placeholder="Search services by name or ID..." class="w-full pl-9 pr-4 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
        </div>

        <div class="w-full sm:w-64">
            <select id="categoryFilter" class="w-full py-2 px-3 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all font-medium text-zinc-800">
                <option value="all">All Categories</option>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= e($c['name']) ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <?php if (!empty($services)): ?>
        <div class="overflow-x-auto rounded-2xl border border-zinc-200/80 bg-white shadow-xs">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm" id="servicesTable">
                <thead class="bg-zinc-50/80 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">ID</th>
                        <th class="py-3.5 px-4">Service Name</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4 text-right">Rate / 1000</th>
                        <th class="py-3.5 px-4 text-center">Min / Max Qty</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php foreach ($services as $svc): ?>
                        <tr class="hover:bg-zinc-50/80 transition-colors service-row"
                            data-name="<?= strtolower(e($svc['name'])) ?>"
                            data-id="<?= (int)$svc['id'] ?>"
                            data-category="<?= e($svc['category_name'] ?? '') ?>">
                            <td class="py-4 px-4 sm:px-6 font-mono text-xs font-bold text-zinc-900">
                                #<?= (int)$svc['id'] ?>
                            </td>
                            <td class="py-4 px-4 max-w-md">
                                <div class="font-bold text-zinc-900 text-sm"><?= e($svc['name']) ?></div>
                                <?php if (!empty($svc['description'])): ?>
                                    <div class="text-xs text-zinc-500 mt-1 line-clamp-2"><?= e($svc['description']) ?></div>
                                <?php endif; ?>
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <?php if (!empty($svc['refill'])): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Refill</span>
                                    <?php endif; ?>
                                    <?php if (!empty($svc['dripfeed'])): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Drip-Feed</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">
                                    <?= e($svc['category_name'] ?? 'General') ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right font-bold text-zinc-900 font-mono text-sm">
                                $<?= number_format((float)($svc['customer_price'] > 0 ? $svc['customer_price'] : $svc['price_per_k']), 4) ?>
                            </td>
                            <td class="py-4 px-4 text-center text-xs text-zinc-600 font-mono">
                                <?= number_format((int)$svc['min_quantity']) ?> - <?= number_format((int)$svc['max_quantity']) ?>
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                <a href="/order/new?service_id=<?= (int)$svc['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                                    Order Now &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="rounded-2xl border border-zinc-200 bg-white p-12 text-center shadow-xs">
            <h3 class="text-base font-bold text-zinc-900">No Services Available</h3>
            <p class="mt-1 text-xs text-zinc-500 max-w-sm mx-auto">
                No active service categories or packages have been published yet.
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('serviceSearch');
    const category = document.getElementById('categoryFilter');
    const rows = document.querySelectorAll('.service-row');

    function filterServices() {
        const query = (search.value || '').toLowerCase().trim();
        const cat = (category.value || 'all');

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const id = row.dataset.id || '';
            const rowCat = row.dataset.category || '';

            const matchesQuery = !query || name.includes(query) || id.includes(query);
            const matchesCat = (cat === 'all') || (rowCat === cat);

            if (matchesQuery && matchesCat) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (search) search.addEventListener('input', filterServices);
    if (category) category.addEventListener('change', filterServices);
});
</script>
