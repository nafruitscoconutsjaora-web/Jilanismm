<?php
$currentUser = auth();
$userWallet = \App\Models\User::getWallet($currentUser['id']);
$unreadNotifCount = \App\Services\NotificationService::getUnreadCount($currentUser['id']);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'User Panel') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="h-full antialiased text-zinc-900">
    <!-- Mobile sidebar backdrop -->
    <div id="mobile-sidebar-backdrop" class="fixed inset-0 z-40 bg-zinc-900/50 backdrop-blur-xs hidden lg:hidden"></div>

    <!-- Mobile Sidebar -->
    <div id="mobile-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white p-6 shadow-xl transition-transform duration-300 -translate-x-full lg:hidden flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-6 border-b border-zinc-100">
                <a href="/dashboard" class="flex items-center space-x-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-600 text-white font-bold text-lg">S</span>
                    <span class="text-xl font-bold tracking-tight text-zinc-900"><?= e(setting('site_name', 'SMM Elite')) ?></span>
                </a>
                <button id="close-sidebar" class="text-zinc-400 hover:text-zinc-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav class="mt-6 space-y-1">
                <a href="/dashboard" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/dashboard' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-700 hover:bg-zinc-50' ?>">
                    <span>Dashboard</span>
                </a>
                <a href="/orders/new" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/orders/new' ? 'bg-rose-600 text-white font-semibold' : 'text-rose-600 hover:bg-rose-50 font-semibold' ?>">
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        New Order
                    </span>
                </a>
                <a href="/user/services" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/user/services' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-700 hover:bg-zinc-50' ?>">
                    <span>Services Directory</span>
                </a>
                <a href="/orders" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/orders' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-700 hover:bg-zinc-50' ?>">
                    <span>Orders History</span>
                </a>
                <a href="/wallet" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/wallet' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-700 hover:bg-zinc-50' ?>">
                    <span>Add Funds / Wallet</span>
                </a>
                <a href="/notifications" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/notifications' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-700 hover:bg-zinc-50' ?>">
                    <span>Notifications</span>
                    <?php if ($unreadNotifCount > 0): ?>
                        <span class="rounded-full bg-rose-600 px-2 py-0.5 text-xs text-white"><?= $unreadNotifCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="/referrals" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/referrals' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-700 hover:bg-zinc-50' ?>">
                    <span>Affiliates</span>
                </a>
                <a href="/tickets" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= (str_starts_with($path, '/tickets') || $path === '/support') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-700 hover:bg-zinc-50' ?>">
                    <span>Support Tickets</span>
                </a>
            </nav>
        </div>
        <div class="border-t border-zinc-100 pt-4">
            <a href="/profile" class="flex items-center px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 rounded-xl">Profile</a>
            <a href="/logout" class="flex items-center px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 rounded-xl font-medium">Log out</a>
        </div>
    </div>

    <!-- Desktop Shell -->
    <div class="flex h-full">
        <!-- Desktop Sidebar -->
        <aside class="hidden lg:flex lg:w-64 lg:flex-col lg:border-r lg:border-zinc-200/80 lg:bg-white lg:pb-4">
            <div class="flex h-16 items-center px-6 border-b border-zinc-100">
                <a href="/dashboard" class="flex items-center space-x-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-600 to-red-600 text-white font-bold text-lg shadow-sm">S</span>
                    <span class="text-xl font-bold tracking-tight text-zinc-900"><?= e(setting('site_name', 'SMM Elite')) ?></span>
                </a>
            </div>

            <!-- Balance Card in Sidebar -->
            <div class="p-4 mx-4 mt-4 rounded-2xl bg-gradient-to-br from-rose-500 to-red-700 text-white shadow-md">
                <div class="text-xs uppercase tracking-wider text-rose-100 font-medium">Available Balance</div>
                <div class="mt-1 text-2xl font-bold">
                    <?= format_currency($userWallet['balance'] ?? 0.00, $userWallet['currency'] ?? 'USD', setting('currency_symbol', '$')) ?>
                </div>
                <div class="mt-3">
                    <a href="/wallet" class="inline-flex w-full items-center justify-center rounded-xl bg-white/20 hover:bg-white/30 px-3 py-1.5 text-xs font-semibold backdrop-blur-sm transition-colors">
                        Deposit Funds &rarr;
                    </a>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="mt-4 flex-1 space-y-1 px-3">
                <a href="/dashboard" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/dashboard' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' ?>">
                    <span>Dashboard</span>
                </a>
                <a href="/orders/new" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/orders/new' ? 'bg-rose-600 text-white font-semibold' : 'text-rose-600 hover:bg-rose-50 font-semibold' ?>">
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        New Order
                    </span>
                </a>
                <a href="/user/services" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/user/services' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' ?>">
                    <span>Services Directory</span>
                </a>
                <a href="/orders" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/orders' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' ?>">
                    <span>Orders History</span>
                </a>
                <a href="/wallet" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/wallet' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' ?>">
                    <span>Add Funds / Wallet</span>
                </a>
                <a href="/notifications" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/notifications' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' ?>">
                    <span>Notifications</span>
                    <?php if ($unreadNotifCount > 0): ?>
                        <span class="rounded-full bg-rose-600 px-2 py-0.5 text-xs font-semibold text-white"><?= $unreadNotifCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="/referrals" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= $path === '/referrals' ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' ?>">
                    <span>Affiliates</span>
                </a>
                <a href="/tickets" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl <?= (str_starts_with($path, '/tickets') || $path === '/support') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' ?>">
                    <span>Support Tickets</span>
                </a>
            </nav>

            <div class="border-t border-zinc-100 p-3">
                <a href="/profile" class="flex items-center px-3 py-2 text-sm text-zinc-600 hover:bg-zinc-50 rounded-xl">My Profile</a>
                <a href="/settings" class="flex items-center px-3 py-2 text-sm text-zinc-600 hover:bg-zinc-50 rounded-xl">Settings</a>
                <a href="/logout" class="flex items-center px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 rounded-xl">Sign Out</a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col overflow-y-auto">
            <!-- Top Navigation Bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-zinc-200/80 bg-white/95 px-4 backdrop-blur-sm sm:px-6 lg:px-8">
                <div class="flex items-center space-x-3">
                    <button id="sidebar-toggle" class="text-zinc-500 hover:text-zinc-700 lg:hidden p-2 rounded-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <span class="text-sm font-semibold text-zinc-500 hidden sm:inline">Client Area</span>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Notifications Icon -->
                    <a href="/notifications" class="relative p-2 text-zinc-500 hover:text-rose-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <?php if ($unreadNotifCount > 0): ?>
                            <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-600"></span>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-2 rounded-full p-1 text-sm focus:outline-none">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-700 font-semibold text-xs">
                                <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 2)) ?>
                            </span>
                            <span class="hidden text-sm font-medium text-zinc-700 md:inline-block"><?= e($currentUser['name'] ?? 'User') ?></span>
                            <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="user-dropdown" class="absolute right-0 mt-2 w-48 rounded-xl bg-white p-1.5 shadow-lg border border-zinc-100 hidden z-50">
                            <div class="px-3 py-2 text-xs text-zinc-500 border-b border-zinc-100">
                                Logged in as <strong class="text-zinc-800"><?= e($currentUser['email']) ?></strong>
                            </div>
                            <a href="/profile" class="block rounded-lg px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50">Profile</a>
                            <a href="/settings" class="block rounded-lg px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50">Settings</a>
                            <a href="/logout" class="block rounded-lg px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50">Sign Out</a>
                        </div>
                    </div>
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
