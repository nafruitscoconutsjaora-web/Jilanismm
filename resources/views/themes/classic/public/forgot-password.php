<div class="mb-6 text-center">
    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Reset your password</h2>
    <p class="mt-1 text-xs text-zinc-500">Enter your email and we'll send you instructions to reset your account password</p>
</div>

<form action="/forgot-password" method="POST" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label for="email" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Email address</label>
        <div class="mt-1.5">
            <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required autocomplete="email" 
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" 
                   placeholder="you@example.com">
        </div>
    </div>

    <div class="pt-2">
        <button type="submit" class="flex w-full justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-colors">
            Send Reset Instructions &rarr;
        </button>
    </div>
</form>

<div class="mt-6 border-t border-zinc-100 pt-4 text-center text-xs text-zinc-500">
    Remember your password? 
    <a href="/login" class="font-semibold text-rose-600 hover:text-rose-700">Back to Sign In</a>
</div>
