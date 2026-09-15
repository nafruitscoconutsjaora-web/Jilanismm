<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-zinc-200 pb-6 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">Services & Rates</h1>
            <p class="mt-1 text-sm text-zinc-500">Live directory of all active marketing services, delivery speeds, and pricing per 1,000 units.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="/register" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-rose-700 transition-colors">
                Sign Up to Order &rarr;
            </a>
        </div>
    </div>

    <?php if (!empty($services)): ?>
        <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white shadow-xs">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Service Details</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Rate / 1k</th>
                        <th class="px-6 py-4">Min / Max</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    <?php foreach ($services as $svc): ?>
                        <tr class="hover:bg-zinc-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-zinc-400">#<?= e($svc['id']) ?></td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-zinc-900"><?= e($svc['name']) ?></div>
                                <?php if (!empty($svc['description'])): ?>
                                    <div class="text-xs text-zinc-500 mt-1 max-w-md"><?= e($svc['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700">
                                    <?= e($svc['category_name'] ?? 'General') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-zinc-900">
                                <?= format_currency($svc['price_per_k']) ?>
                            </td>
                            <td class="px-6 py-4 text-xs text-zinc-600">
                                <?= number_format($svc['min_quantity']) ?> - <?= number_format($svc['max_quantity']) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/login" class="rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">
                                    Order
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- Real Clean Empty State -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-16 text-center shadow-xs max-w-xl mx-auto my-12">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 mb-4">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-zinc-900">No Services Available Yet</h3>
            <p class="mt-2 text-sm text-zinc-500 leading-relaxed">
                The service inventory is currently being synchronized with API providers. Check back shortly or register to get notified upon catalog launch.
            </p>
            <div class="mt-6 flex justify-center space-x-3">
                <a href="/" class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">
                    Return Home
                </a>
                <a href="/register" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700">
                    Create Account
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
