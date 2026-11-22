<?php

declare(strict_types=1);

namespace Aplicacion\Controladores;

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
        require __DIR__ . '/../Vistas/plantillas/header.php';
        require __DIR__ . '/../Vistas/' . $view . '.php';
        require __DIR__ . '/../Vistas/plantillas/footer.php';
    }
}
