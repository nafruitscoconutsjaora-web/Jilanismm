<?php

declare(strict_types=1);

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Safe default, errors logged instead

set_exception_handler(function (Throwable $e) {
    $logDir = dirname(__DIR__) . '/storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }
    
    $logMessage = sprintf(
        "[%s] Exception: %s in %s:%d\nStack trace:\n%s\n\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    file_put_contents($logDir . '/app.log', $logMessage, FILE_APPEND);

    http_response_code(500);
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Server Error</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #fafafa; color: #18181b; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .card { background: white; border: 1px solid #e4e4e7; border-radius: 12px; padding: 32px; max-width: 440px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; }
            h1 { font-size: 20px; font-weight: 700; color: #b91c1c; margin-bottom: 8px; }
            p { color: #71717a; font-size: 14px; line-height: 1.5; margin-bottom: 20px; }
            a { display: inline-block; background: #b91c1c; color: white; padding: 8px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Something went wrong</h1>
            <p>An unexpected server error occurred. Our team has been notified. Please try again in a few moments.</p>
            <a href="/">Return Home</a>
        </div>
    </body>
    </html>';
    exit;
});

// Bootstrap application
/** @var \App\Core\Router $router */
$router = require dirname(__DIR__) . '/bootstrap/app.php';

// Dispatch current request
$request = new \App\Core\Request();
$router->dispatch($request);
