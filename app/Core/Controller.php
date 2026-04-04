<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            echo 'View não encontrada';
            return;
        }

        $layout = isset($data['layout']) ? (string)$data['layout'] : '';
        if ($layout !== '') {
            $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';
            if (is_file($layoutFile)) {
                include $layoutFile;
                return;
            }
        }

        // Auto-detect mobile layout for /m/ routes
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        if (str_starts_with($requestPath, '/m/') || $requestPath === '/m') {
            $mobileLayout = __DIR__ . '/../Views/layouts/mobile.php';
            if (is_file($mobileLayout)) {
                include $mobileLayout;
                return;
            }
        }

        include __DIR__ . '/../Views/layouts/main.php';
    }
}
