<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name')) ?></title>
    <meta name="description" content="<?= e(setting('site_description', 'High performance SMM panel platform')) ?>">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="flex min-h-full flex-col bg-zinc-50 text-zinc-900 antialiased">
    <?php require dirname(__DIR__) . '/components/navbar.php'; ?>

    <main class="flex-1">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <?php require dirname(__DIR__) . '/components/alerts.php'; ?>
        </div>
        <?= $content ?>
    </main>

    <?php require dirname(__DIR__) . '/components/footer.php'; ?>

    <script src="/assets/js/app.js"></script>
</body>
</html>
