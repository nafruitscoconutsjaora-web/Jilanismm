<div class="mx-auto max-w-4xl space-y-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Account Preferences</h1>
        <p class="mt-1 text-sm text-zinc-500">Configure notification preferences and display localization.</p>
    </div>

    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 sm:p-8 shadow-xs">
        <h2 class="text-lg font-bold text-zinc-900 mb-4">Localization & Currency</h2>
        <div class="space-y-4 max-w-xl text-sm">
            <div>
                <label class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Default Billing Currency</label>
                <div class="mt-1.5">
                    <input type="text" value="<?= e(setting('default_currency', 'USD')) ?> (<?= e(setting('currency_symbol', '$')) ?>)" disabled 
                           class="block w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2 text-sm text-zinc-500 cursor-not-allowed">
                </div>
                <p class="mt-1 text-xs text-zinc-400">Currency is set globally by platform administration.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Timezone</label>
                <div class="mt-1.5">
                    <input type="text" value="UTC (Server Standard)" disabled 
                           class="block w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2 text-sm text-zinc-500 cursor-not-allowed">
                </div>
            </div>
        </div>
    </div>
</div>
