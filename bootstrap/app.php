<?php

// Autoloader for App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load helpers
require_once dirname(__DIR__) . '/app/Helpers/helpers.php';

// Load Environment Variables
\App\Core\Env::load(dirname(__DIR__) . '/.env');

// Start secure session
\App\Core\Session::start();

// Initialize Router
$router = new \App\Core\Router();
require_once dirname(__DIR__) . '/routes/web.php';

return $router;
