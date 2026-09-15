<div class="mb-6 text-center">
    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Create your account</h2>
    <p class="mt-1 text-xs text-zinc-500">Instant access to enterprise SMM tools and discounted rates</p>
</div>

<form action="/register" method="POST" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label for="name" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Full Name</label>
        <div class="mt-1.5">
            <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" required autocomplete="name" 
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" 
                   placeholder="Alex Morgan">
        </div>
    </div>

    <div>
        <label for="email" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Email address</label>
        <div class="mt-1.5">
            <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required autocomplete="email" 
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" 
                   placeholder="alex@example.com">
        </div>
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Password (min 8 chars)</label>
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
        <label for="password_confirmation" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Confirm Password</label>
        <div class="mt-1.5">
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" minlength="8"
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" 
                   placeholder="••••••••">
        </div>
    </div>

    <div>
        <label for="referral_code" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">
            Referral Code <span class="text-zinc-400 font-normal lowercase">(optional)</span>
        </label>
        <div class="mt-1.5">
            <input type="text" id="referral_code" name="referral_code" value="<?= e(old('referral_code', $referralCode ?? '')) ?>" 
                   class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 uppercase font-mono" 
                   placeholder="INVITE CODE">
        </div>
    </div>

    <div class="text-xs text-zinc-500 leading-relaxed pt-1">
        By registering, you agree to our <a href="/terms" class="text-rose-600 hover:underline">Terms of Service</a> and <a href="/privacy" class="text-rose-600 hover:underline">Privacy Policy</a>.
    </div>

    <div class="pt-2">
        <button type="submit" class="flex w-full justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
            Create Account &rarr;
        </button>
    </div>
</form>

<div class="mt-6 border-t border-zinc-100 pt-4 text-center text-xs text-zinc-500">
    Already have an account? 
    <a href="/login" class="font-semibold text-rose-600 hover:text-rose-700">Sign in here</a>
</div>
