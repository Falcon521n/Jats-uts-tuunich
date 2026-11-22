<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$pdo = require __DIR__ . '/../configuracion/database.php';
require_once __DIR__ . '/../aplicacion/Modelos/AccountModel.php';

try {
    $accounts = new Aplicacion\Modelos\AccountModel($pdo);
    if (!$accounts->adminExists()) {
        $accounts->createAdminUser(
            'admin',
            'Administrador',
            'admin@local.test',
            '$2y$12$bMEpsHbT5CrYWf91NNgu/uakJzVlv6VMEJkpq43iVhpi8/o8t8mOy'
        );
        fwrite(STDOUT, "Administrador local creado: admin\n");
    } else {
        fwrite(STDOUT, "La cuenta de administrador local ya existe.\n");
    }
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        fwrite(STDERR, "El usuario admin o el correo admin@local.test ya está registrado.\n");
        exit(1);
    }

    throw $exception;
}
