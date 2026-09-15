<div class="mx-auto max-w-4xl space-y-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Account Profile</h1>
        <p class="mt-1 text-sm text-zinc-500">Manage your personal information and password credentials.</p>
    </div>

    <!-- Personal Details Form -->
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 sm:p-8 shadow-xs">
        <h2 class="text-lg font-bold text-zinc-900 mb-4">Personal Details</h2>
        <form action="/profile" method="POST" class="space-y-4 max-w-xl">
            <?= csrf_field() ?>

            <div>
                <label for="name" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Full Name</label>
                <div class="mt-1.5">
                    <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required 
                           class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Email Address</label>
                <div class="mt-1.5">
                    <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required 
                           class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
                </div>
                <div class="mt-1 text-xs text-zinc-400">
                    Verification status: <?= $user['email_verified_at'] ? '<span class="text-emerald-600 font-semibold">Verified</span>' : '<span class="text-amber-600 font-semibold">Pending Verification</span>' ?>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-rose-700">
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password Form -->
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 sm:p-8 shadow-xs">
        <h2 class="text-lg font-bold text-zinc-900 mb-4">Update Password</h2>
        <form action="/profile/password" method="POST" class="space-y-4 max-w-xl">
            <?= csrf_field() ?>

            <div>
                <label for="current_password" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Current Password</label>
                <div class="mt-1.5">
                    <input type="password" id="current_password" name="current_password" required 
                           class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
                </div>
            </div>

            <div>
                <label for="new_password" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">New Password (min 8 chars)</label>
                <div class="mt-1.5">
                    <input type="password" id="new_password" name="new_password" minlength="8" required 
                           class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
                </div>
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Confirm New Password</label>
                <div class="mt-1.5">
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" minlength="8" required 
                           class="block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="rounded-xl bg-zinc-900 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-zinc-800">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
