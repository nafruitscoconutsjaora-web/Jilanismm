<div class="mb-6 text-center">
    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Sign in to your account</h2>
    <p class="mt-1 text-xs text-zinc-500">Access your dashboard, wallet, and order management</p>
</div>

<form action="/login" method="POST" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label for="email" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Email address</label>
        <div class="mt-1.5">
            <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required autocomplete="email" 
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" 
                   placeholder="you@example.com">
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label for="password" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Password</label>
            <a href="/forgot-password" class="text-xs font-medium text-rose-600 hover:text-rose-700">Forgot password?</a>
        </div>
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
        <button type="submit" class="flex w-full justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
            Sign In &rarr;
        </button>
    </div>
</form>

<div class="mt-6 border-t border-zinc-100 pt-4 text-center text-xs text-zinc-500">
    Don't have an account? 
    <a href="/register" class="font-semibold text-rose-600 hover:text-rose-700">Create one now</a>
</div>

<div class="mt-3 text-center">
    <a href="/admin/login" class="text-[11px] text-zinc-400 hover:text-zinc-600">
        Staff / Admin Portal &rarr;
    </a>
</div>
