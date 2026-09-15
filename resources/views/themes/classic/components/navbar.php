<nav class="sticky top-0 z-40 w-full border-b border-rose-100 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <!-- Logo -->
        <a href="/" class="flex items-center space-x-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-600 to-red-600 text-white font-bold text-lg shadow-sm">
                S
            </span>
            <span class="text-xl font-bold tracking-tight text-zinc-900">
                <?= e(setting('site_name', 'SMM Elite')) ?>
            </span>
        </a>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex md:items-center md:space-x-8 text-sm font-medium text-zinc-600">
            <a href="/" class="hover:text-rose-600 transition-colors">Home</a>
            <a href="/services" class="hover:text-rose-600 transition-colors">Services & Pricing</a>
            <a href="/terms" class="hover:text-rose-600 transition-colors">Terms</a>
            <a href="/contact" class="hover:text-rose-600 transition-colors">Contact</a>
        </div>

        <!-- Auth Actions -->
        <div class="flex items-center space-x-3">
            <?php if (auth_check()): ?>
                <a href="/dashboard" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700 transition-colors">
                    Dashboard
                </a>
            <?php elseif (admin_check()): ?>
                <a href="/admin/dashboard" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 transition-colors">
                    Admin Portal
                </a>
            <?php else: ?>
                <a href="/login" class="text-sm font-medium text-zinc-700 hover:text-rose-600 px-3 py-2 transition-colors">
                    Sign In
                </a>
                <a href="/register" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700 transition-colors">
                    Get Started
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
