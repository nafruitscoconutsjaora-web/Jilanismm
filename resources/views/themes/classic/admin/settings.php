<div class="space-y-8 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">System Configuration</h1>
        <p class="mt-1 text-xs text-zinc-500">Configure global metadata, maintenance switches, and operational defaults.</p>
    </div>

    <form action="/admin/settings" method="POST" class="space-y-8">
        <?= csrf_field() ?>

        <!-- Brand & Communication -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-zinc-900 border-b border-zinc-100 pb-3">Brand & Communication</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="site_name" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Platform Name</label>
                    <input type="text" id="site_name" name="site_name" value="<?= e($settings['site_name'] ?? 'SMM Elite') ?>" required 
                           class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="support_email" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Support Email</label>
                    <input type="email" id="support_email" name="support_email" value="<?= e($settings['support_email'] ?? 'support@smmpanel.local') ?>" required 
                           class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label for="site_description" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Meta Description</label>
                <textarea id="site_description" name="site_description" rows="2" 
                          class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none"><?= e($settings['site_description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Currency & Rates -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-zinc-900 border-b border-zinc-100 pb-3">Currency & Economics</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="default_currency" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Default Currency</label>
                    <input type="text" id="default_currency" name="default_currency" value="<?= e($settings['default_currency'] ?? 'USD') ?>" required 
                           class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 uppercase focus:border-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="currency_symbol" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Currency Symbol</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" value="<?= e($settings['currency_symbol'] ?? '$') ?>" required 
                           class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="referral_rate" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Affiliate Commission (%)</label>
                    <input type="number" step="0.1" min="0" max="100" id="referral_rate" name="referral_rate" value="<?= e($settings['referral_rate'] ?? '5.0') ?>" required 
                           class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- System Controls & Security Switches -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-zinc-900 border-b border-zinc-100 pb-3">Access & Operations Switches</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="maintenance_mode" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Maintenance Mode</label>
                    <select id="maintenance_mode" name="maintenance_mode" class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
                        <option value="disabled" <?= ($settings['maintenance_mode'] ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled (Normal Traffic)</option>
                        <option value="enabled" <?= ($settings['maintenance_mode'] ?? '') === 'enabled' ? 'selected' : '' ?>>Enabled (Lock Public Access)</option>
                    </select>
                </div>

                <div>
                    <label for="registration_enabled" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Client Registrations</label>
                    <select id="registration_enabled" name="registration_enabled" class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
                        <option value="enabled" <?= ($settings['registration_enabled'] ?? '') === 'enabled' ? 'selected' : '' ?>>Enabled (Allow Signups)</option>
                        <option value="disabled" <?= ($settings['registration_enabled'] ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled (Closed Registration)</option>
                    </select>
                </div>

                <div>
                    <label for="email_verification" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Email Verification</label>
                    <select id="email_verification" name="email_verification" class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
                        <option value="disabled" <?= ($settings['email_verification'] ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled (Instant Access)</option>
                        <option value="enabled" <?= ($settings['email_verification'] ?? '') === 'enabled' ? 'selected' : '' ?>>Enabled (Require Confirmation)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-rose-700 transition-colors">
                Save System Settings
            </button>
        </div>
    </form>
</div>
