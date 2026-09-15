<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Authentication') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="flex min-h-full flex-col justify-center bg-zinc-50 py-12 sm:px-6 lg:px-8 antialiased">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="/" class="flex items-center justify-center space-x-2.5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-600 to-red-600 text-white font-bold text-xl shadow-md">
                S
            </span>
            <span class="text-2xl font-bold tracking-tight text-zinc-900">
                <?= e(setting('site_name', 'SMM Elite')) ?>
            </span>
        </a>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <?php require dirname(__DIR__) . '/components/alerts.php'; ?>
        <div class="bg-white px-6 py-8 shadow-sm border border-zinc-200/80 rounded-2xl sm:px-10">
            <?= $content ?>
        </div>
        <div class="mt-6 text-center text-xs text-zinc-400">
            &copy; <?= date('Y') ?> <?= e(setting('site_name', 'SMM Elite')) ?>. Secure Environment.
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
