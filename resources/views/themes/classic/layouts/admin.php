<?php
$currentAdmin = admin();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$mMode = setting('maintenance_mode', 'disabled');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin Portal') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="h-full antialiased text-zinc-900">
    <div class="flex h-full">
        <!-- Admin Sidebar (Dark Charcoal/Zinc with Crimson Accents) -->
        <aside class="hidden lg:flex lg:w-64 lg:flex-col lg:bg-zinc-950 lg:text-zinc-300 lg:border-r lg:border-zinc-800">
            <div class="flex h-16 items-center px-6 border-b border-zinc-800">
                <a href="/admin/dashboard" class="flex items-center space-x-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-600 text-white font-bold text-sm shadow-sm">A</span>
                    <div>
                        <span class="text-base font-bold tracking-tight text-white"><?= e(setting('site_name', 'SMM Elite')) ?></span>
                        <span class="block text-[10px] uppercase font-semibold tracking-wider text-rose-400">Admin Control</span>
                    </div>
                </a>
            </div>

            <!-- Nav Links -->
            <nav class="mt-4 flex-1 space-y-1 px-3">
                <a href="/admin/dashboard" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= $path === '/admin/dashboard' ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Dashboard</span>
                </a>
                <a href="/admin/users" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= $path === '/admin/users' ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>User Accounts</span>
                </a>
                <a href="/admin/orders" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/orders') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Orders</span>
                </a>
                <a href="/admin/services" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/services') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Services</span>
                </a>
                <a href="/admin/categories" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/categories') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Categories</span>
                </a>
                <a href="/admin/providers" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/providers') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>API Providers</span>
                </a>
                <a href="/admin/payments" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/payments') || str_starts_with($path, '/admin/gateways') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Payments & Gateways</span>
                </a>
                <a href="/admin/wallets" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/wallets') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Wallets & Ledgers</span>
                </a>
                <a href="/admin/currencies" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/currencies') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Currencies & Rates</span>
                </a>
                <a href="/admin/coupons" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/coupons') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Coupons & Promos</span>
                </a>
                <a href="/admin/referrals" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/referrals') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Referrals & Affiliates</span>
                </a>
                <a href="/admin/reports" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/reports') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Financial Reports</span>
                </a>
                <a href="/admin/tickets" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= str_starts_with($path, '/admin/tickets') ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Support Tickets</span>
                </a>
                <a href="/admin/notifications" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= $path === '/admin/notifications' ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>Broadcast Notices</span>
                </a>
                <a href="/admin/settings" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl <?= $path === '/admin/settings' ? 'bg-rose-600 text-white font-semibold' : 'hover:bg-zinc-900 hover:text-white' ?>">
                    <span>System Settings</span>
                </a>
            </nav>

            <div class="border-t border-zinc-800 p-3 space-y-2">
                <a href="/" target="_blank" class="flex items-center px-3 py-2 text-xs text-zinc-400 hover:text-zinc-200 rounded-lg hover:bg-zinc-900">
                    <span>View Public Website &UpperRightArrow;</span>
                </a>
                <a href="/admin/logout" class="flex items-center px-3 py-2 text-xs font-medium text-rose-400 hover:bg-zinc-900 hover:text-rose-300 rounded-lg">
                    Sign Out Admin
                </a>
            </div>
        </aside>

        <!-- Main Content Column -->
        <div class="flex flex-1 flex-col overflow-y-auto">
            <!-- Header Bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-zinc-200 bg-white px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-bold tracking-tight text-zinc-800">Administrator Console</span>
                    <!-- Maintenance Badge -->
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?= $mMode === 'enabled' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' ?>">
                        <span class="mr-1.5 h-1.5 w-1.5 rounded-full <?= $mMode === 'enabled' ? 'bg-amber-500' : 'bg-emerald-500' ?>"></span>
                        Maintenance: <?= strtoupper($mMode) ?>
                    </span>
                </div>

                <div class="flex items-center space-x-3">
                    <span class="text-xs text-zinc-500">Logged in as <strong class="text-zinc-800"><?= e($currentAdmin['name'] ?? 'Admin') ?></strong></span>
                    <a href="/admin/logout" class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50 shadow-xs">
                        Sign Out
                    </a>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <?php require dirname(__DIR__) . '/components/alerts.php'; ?>
                <?= $content ?>
            </main>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
