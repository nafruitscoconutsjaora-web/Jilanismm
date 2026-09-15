<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900"><?= e($sectionName ?? 'Module') ?></h1>
        <p class="mt-1 text-xs text-zinc-500">System infrastructure and database schema are ready.</p>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-12 text-center shadow-xs max-w-xl mx-auto my-8">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 mb-4">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <h3 class="text-lg font-bold text-zinc-900"><?= e($sectionName ?? 'Module') ?> Management</h3>
        <p class="mt-2 text-sm text-zinc-500 leading-relaxed">
            <?= e($description ?? 'This module table structure is initialized in the database schema and will be fully wired in the subsequent implementation stage.') ?>
        </p>
        <div class="mt-6">
            <a href="/admin/dashboard" class="rounded-xl bg-zinc-900 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-800">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>
</div>
