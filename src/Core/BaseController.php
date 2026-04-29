<?php

namespace App\Core;

use App\Core\Auth;

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        // Inject auth info ke semua view secara otomatis
        $data['authUser'] = Auth::user();
        $data['authRole'] = Auth::role();

        extract($data);

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View tidak ditemukan: {$view}");
        }

        require __DIR__ . '/../Views/layouts/header.php';
        require $viewPath;
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
