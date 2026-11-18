<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$pdo = require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/AccountModel.php';

function read_secret(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    $terminalMode = null;
    if (DIRECTORY_SEPARATOR === '/' && function_exists('posix_isatty') && posix_isatty(STDIN) && function_exists('shell_exec')) {
        $terminalMode = shell_exec('stty -g 2>/dev/null');
        if (is_string($terminalMode) && trim($terminalMode) !== '') {
            shell_exec('stty -echo');
        }
    }

    try {
        $value = fgets(STDIN);
    } finally {
        if (is_string($terminalMode) && trim($terminalMode) !== '') {
            shell_exec('stty ' . escapeshellarg(trim($terminalMode)));
            fwrite(STDOUT, PHP_EOL);
        }
    }

    return trim(is_string($value) ? $value : '');
}

fwrite(STDOUT, 'Nombre del administrador: ');
$nameInput = fgets(STDIN);
fwrite(STDOUT, 'Correo del administrador: ');
$emailInput = fgets(STDIN);
$name = trim(is_string($nameInput) ? $nameInput : '');
$email = strtolower(trim(is_string($emailInput) ? $emailInput : ''));
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Nombre o correo no válido.\n");
    exit(1);
}

$password = read_secret('Contraseña (mínimo 10 caracteres): ');
$confirmation = read_secret('Repite la contraseña: ');
if (strlen($password) < 10 || strlen($password) > 200 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "La contraseña no es válida o no coincide.\n");
    exit(1);
}

try {
    (new App\Models\AccountModel($pdo))->createAdminUser(
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT)
    );
    fwrite(STDOUT, "Administrador creado correctamente.\n");
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        fwrite(STDERR, "Ese correo ya tiene una cuenta.\n");
        exit(1);
    }

    throw $exception;
}
