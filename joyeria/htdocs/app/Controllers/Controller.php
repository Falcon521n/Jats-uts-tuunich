<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

abstract class Controller
{
    public function __construct(protected PDO $pdo)
    {
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $pageTitle = $pageTitle ?? 'Tuunich | Joyería con historia';
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/' . $view . '.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
