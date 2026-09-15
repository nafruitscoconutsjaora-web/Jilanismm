<footer class="border-t border-zinc-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
            <div class="space-y-4 md:col-span-2">
                <div class="flex items-center space-x-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-600 text-white font-bold text-base shadow-sm">
                        S
                    </span>
                    <span class="text-lg font-bold tracking-tight text-zinc-900">
                        <?= e(setting('site_name', 'SMM Elite')) ?>
                    </span>
                </div>
                <p class="max-w-md text-sm text-zinc-500 leading-relaxed">
                    <?= e(setting('site_description', 'High performance social media growth management, order automation, and real-time delivery.')) ?>
                </p>
                <div class="text-xs text-zinc-400">
                    &copy; <?= date('Y') ?> <?= e(setting('site_name', 'SMM Elite')) ?>. All rights reserved.
                </div>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Platform</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-zinc-600">
                    <li><a href="/services" class="hover:text-rose-600 transition-colors">Services List</a></li>
                    <li><a href="/register" class="hover:text-rose-600 transition-colors">Sign Up</a></li>
                    <li><a href="/login" class="hover:text-rose-600 transition-colors">Client Login</a></li>
                    <li><a href="/admin/login" class="hover:text-rose-600 transition-colors">Admin Gateway</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Legal & Support</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-zinc-600">
                    <li><a href="/terms" class="hover:text-rose-600 transition-colors">Terms of Service</a></li>
                    <li><a href="/privacy" class="hover:text-rose-600 transition-colors">Privacy Policy</a></li>
                    <li><a href="/contact" class="hover:text-rose-600 transition-colors">Customer Support</a></li>
                    <li><span class="text-zinc-400"><?= e(setting('support_email', 'support@smmpanel.local')) ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
