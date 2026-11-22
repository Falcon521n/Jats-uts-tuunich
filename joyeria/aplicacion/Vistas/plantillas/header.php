<?php
$user = current_user();
$notice = consume_flash();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Joyería artesanal e industrial con historias que perduran.">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="estilos/estilos.css">
    <script defer src="scripts/validaciones.js"></script>
    <script defer src="scripts/carrito.js"></script>
    <script defer src="scripts/catalogo.js"></script>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="Tuunich, inicio">
            <span class="brand-mark" aria-hidden="true">T</span>
            <span>TUUNICH<small>JOYERÍA CON HISTORIA</small></span>
        </a>
        <nav class="main-nav" aria-label="Navegación principal">
            <a href="index.php">Catálogo</a>
            <?php if ($user === null): ?>
                <a href="index.php#acceso">Iniciar sesión</a><a href="registro.php">Crear cuenta</a>
            <?php elseif ($user['role'] === 'customer'): ?>
                <a href="carrito.php">Carrito</a><a href="cuenta.php">Mi cuenta</a>
            <?php else: ?>
                <a href="admin_productos.php">Productos</a><a href="admin_usuarios.php">Usuarios</a><a href="admin_reportes.php">Reportes</a>
            <?php endif; ?>
        </nav>
        <?php if ($user !== null): ?>
            <form method="post" action="logout.php" class="logout-form">
                <?= csrf_field() ?><button class="header-link" type="submit">Salir, <?= e($user['name']) ?></button>
            </form>
        <?php endif; ?>
    </header>
    <main class="page-main">
        <?php if ($notice !== null): ?>
            <div class="notice notice-<?= e($notice['type']) ?>" role="status"><?= e($notice['message']) ?></div>
        <?php endif; ?>
