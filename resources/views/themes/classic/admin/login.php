<div class="mb-6 text-center">
    <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-900 text-rose-500 font-extrabold text-xl shadow-md mb-3">
        A
    </div>
    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Admin Control Gateway</h2>
    <p class="mt-1 text-xs text-zinc-500">Authorized personnel authentication only</p>
</div>

<form action="/admin/login" method="POST" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label for="email" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Admin Email</label>
        <div class="mt-1.5">
            <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required autocomplete="email" 
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" 
                   placeholder="admin@smmpanel.local">
        </div>
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Passphrase</label>
        <div class="mt-1.5 relative">
            <input type="password" id="password" name="password" required autocomplete="current-password" 
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 pr-16" 
                   placeholder="••••••••">
            <button type="button" data-input="password" class="toggle-password absolute right-3 top-2.5 text-xs text-zinc-400 hover:text-zinc-600">
                Show
            </button>
        </div>
    </div>

    <div class="pt-2">
        <button type="submit" class="flex w-full justify-center rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2">
            Verify & Enter Console &rarr;
        </button>
    </div>
</form>

<div class="mt-6 border-t border-zinc-100 pt-4 text-center">
    <a href="/login" class="text-xs text-zinc-400 hover:text-rose-600">
        &larr; Return to Client Sign In
    </a>
</div>
