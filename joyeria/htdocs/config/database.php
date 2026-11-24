<?php

declare(strict_types=1);

$localConfigFile = __DIR__ . '/database.local.php';
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    foreach (['host', 'database', 'username', 'password'] as $key) {
        if (!is_array($localConfig) || !isset($localConfig[$key]) || !is_string($localConfig[$key])) {
            throw new RuntimeException('Invalid database.local.php configuration.');
        }
    }

    $host = $localConfig['sql205.infinityfree.com'];
    $database = $localConfig['if0_43518410_sistema_tuunich'];
    $username = $localConfig['if0_43518410'];
    $password = $localConfig['QZLIauaOwQxt2FI'];
} else {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $database = getenv('DB_NAME') ?: 'joyeria';
    $username = getenv('DB_USER') ?: 'tuunich_local';
    $password = getenv('DB_PASSWORD');
    $password = $password === false ? '' : $password;
}

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $database);

return new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
