<?php

declare(strict_types=1);

require_once __DIR__ . '/Soporte/functions.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'Aplicacion\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

try {
    $pdo = require __DIR__ . '/../configuracion/database.php';
} catch (PDOException | RuntimeException $exception) {
    error_log('Database initialization failed: ' . $exception->getMessage());
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    exit(
        '<!doctype html><html lang="es"><meta charset="utf-8"><title>Servicio no disponible</title>'
        . '<body><h1>Servicio temporalmente no disponible</h1>'
        . '<p>Verifica que MariaDB local esté iniciado, que la base joyeria exista y que las variables DB_* sean correctas.</p></body></html>'
    );
}
