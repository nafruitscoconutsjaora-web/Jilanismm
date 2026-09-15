<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        $baseDir = dirname(__DIR__, 2) . '/resources/views/themes/classic/';
        $viewFile = $baseDir . $view . '.php';

        if (!file_exists($viewFile)) {
            // Log missing view
            error_log("View not found: {$viewFile}");
            http_response_code(500);
            echo "View [{$view}] not found.";
            exit;
        }

        extract($data);

        if ($layout === null) {
            require $viewFile;
            return;
        }

        // Buffer view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = $baseDir . 'layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }

        require $layoutFile;
    }
}
