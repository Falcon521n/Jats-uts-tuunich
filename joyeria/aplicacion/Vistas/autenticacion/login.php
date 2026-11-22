<section class="form-page narrow">
    <p class="eyebrow">QUÉ BUENO VERTE</p>
    <h1>Iniciar sesión</h1>
    <p class="form-intro">Accede a tu cuenta para continuar.</p>
    <?php foreach ($errors as $error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endforeach; ?>
    <form method="post" class="stack-form" data-validate>
        <?= csrf_field() ?>
        <label>Usuario o correo electrónico<input type="text" name="identifier" required maxlength="190" autocomplete="username" value="<?= e($identifier) ?>"></label>
        <label>Contraseña
            <span class="password-control"><input type="password" name="password" required autocomplete="current-password"><span class="check-option"><input type="checkbox" data-show-password> Mostrar</span></span>
        </label>
        <button class="button button-dark" type="submit">Entrar</button>
    </form>
    <p class="form-switch">¿Aún no tienes cuenta? <a class="text-link" href="registro.php">Regístrate</a></p>
</section>
