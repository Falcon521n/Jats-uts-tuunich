<section class="form-page">
    <p class="eyebrow">BIENVENIDA, BIENVENIDO</p>
    <h1>Crear cuenta</h1>
    <p class="form-intro">Regístrate para guardar tus datos y consultar tus compras.</p>
    <?php foreach ($errors as $error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endforeach; ?>
    <form method="post" class="stack-form" data-validate>
        <?= csrf_field() ?>
        <div class="form-row">
            <label>Nombre<input name="name" required maxlength="120" autocomplete="given-name" value="<?= e($form['name']) ?>"></label>
            <label>Apellidos<input name="last_name" required maxlength="120" autocomplete="family-name" value="<?= e($form['last_name']) ?>"></label>
        </div>
        <label>Correo electrónico<input type="email" name="email" required maxlength="190" autocomplete="email" value="<?= e($form['email']) ?>"></label>
        <label>Teléfono<input type="tel" name="phone" maxlength="30" autocomplete="tel" value="<?= e($form['phone']) ?>"></label>
        <label>Contraseña<input type="password" name="password" required minlength="10" maxlength="200" autocomplete="new-password"></label>
        <label>Dirección<textarea name="address" rows="3" maxlength="500" autocomplete="street-address"><?= e($form['address']) ?></textarea></label>
        <label class="check-option"><input type="checkbox" name="terms" value="accepted" required> Acepto los términos y condiciones</label>
        <button class="button button-dark" type="submit">Crear mi cuenta</button>
    </form>
    <p class="form-switch">¿Ya tienes cuenta? <a class="text-link" href="login.php">Inicia sesión</a></p>
</section>
