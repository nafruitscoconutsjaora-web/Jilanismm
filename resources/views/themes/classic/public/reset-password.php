<div class="mb-6 text-center">
    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Set new password</h2>
    <p class="mt-1 text-xs text-zinc-500">Enter a secure new password for <strong><?= e($email) ?></strong></p>
</div>

<form action="/reset-password" method="POST" class="space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div>
        <label for="password" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">New Password</label>
        <div class="mt-1.5 relative">
            <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8"
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 pr-16" 
                   placeholder="••••••••">
            <button type="button" data-input="password" class="toggle-password absolute right-3 top-2.5 text-xs text-zinc-400 hover:text-zinc-600">
                Show
            </button>
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Confirm New Password</label>
        <div class="mt-1.5">
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" minlength="8"
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" 
                   placeholder="••••••••">
        </div>
    </div>

    <div class="pt-2">
        <button type="submit" class="flex w-full justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-colors">
            Update Password & Sign In &rarr;
        </button>
    </div>
</form>
