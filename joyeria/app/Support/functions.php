<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_role(string ...$roles): array
{
    $user = current_user();
    if ($user === null) {
        flash('error', 'Inicia sesión para continuar.');
        redirect('login.php');
    }

    global $pdo;
    $account = (new App\Models\UserModel($pdo))->findStatus((int) $user['id']);
    if (!$account || !$account['is_active']) {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('error', 'Tu sesión terminó. Inicia sesión de nuevo.');
        redirect('login.php');
    }

    if ($account['role'] !== $user['role']) {
        session_regenerate_id(true);
        $_SESSION['user']['role'] = $account['role'];
        $_SESSION['user']['name'] = $account['name'];
        $user = $_SESSION['user'];
    }

    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('No tienes permiso para acceder a esta página.');
    }

    return $user;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('El formulario expiró. Recarga la página e inténtalo de nuevo.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consume_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $message;
}

function post_string(string $key, int $maxLength = 500): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }

    return trim(substr($value, 0, $maxLength));
}

function positive_int(mixed $value, int $maximum = 100000): ?int
{
    $validated = filter_var($value, FILTER_VALIDATE_INT);
    if ($validated === false || $validated < 1 || $validated > $maximum) {
        return null;
    }

    return $validated;
}

function valid_money(string $value): ?string
{
    if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $value)) {
        return null;
    }

    return number_format((float) $value, 2, '.', '');
}

function money_to_cents(string $value): int
{
    $parts = explode('.', $value, 2);

    return ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
}

function product_image_url(?string $value): ?string
{
    return $value !== null && preg_match('~^uploads/[a-f0-9]{32}\.(?:jpg|png|webp)$~D', $value)
        ? $value
        : null;
}

function payment_label(string $method): string
{
    return [
        'debit' => 'Débito',
        'credit' => 'Crédito',
        'deposit' => 'Depósito',
    ][$method] ?? $method;
}
