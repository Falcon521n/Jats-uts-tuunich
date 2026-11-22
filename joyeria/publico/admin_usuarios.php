<?php

declare(strict_types=1);

require __DIR__ . '/../aplicacion/bootstrap.php';

(new Aplicacion\Controladores\AdminUserController($pdo))->index();
