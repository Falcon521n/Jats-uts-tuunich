<section class="content-page">
    <p class="eyebrow">ADMINISTRACIÓN</p><h1>Usuarios y crédito</h1>
    <?php foreach ($errors as $error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endforeach; ?>
    <section class="panel-card">
        <h2><?= $editUser ? 'Editar usuario' : 'Crear usuario o vendedor' ?></h2>
        <form method="post" class="stack-form" data-validate>
            <?= csrf_field() ?><input type="hidden" name="action" value="save">
            <?php if ($editUser): ?><input type="hidden" name="user_id" value="<?= (int) $editUser['id'] ?>"><?php endif; ?>
            <div class="form-row">
                <label>Nombre<input name="name" required maxlength="120" value="<?= e($_POST['name'] ?? $editUser['name'] ?? '') ?>"></label>
                <label>Apellidos<input name="last_name" required maxlength="120" value="<?= e($_POST['last_name'] ?? $editUser['last_name'] ?? '') ?>"></label>
                <label>Correo<input type="email" name="email" required maxlength="190" value="<?= e($_POST['email'] ?? $editUser['email'] ?? '') ?>"></label>
                <label>Teléfono<input type="tel" name="phone" maxlength="30" value="<?= e($_POST['phone'] ?? $editUser['phone'] ?? '') ?>"></label>
            </div>
            <div class="form-row">
                <label>Contraseña <?= $editUser ? '(dejar vacía para conservar)' : '' ?><input type="password" name="password" <?= $editUser ? '' : 'required minlength="10"' ?> autocomplete="new-password"></label>
                <label>Rol<select name="role" required>
                    <?php foreach (['customer' => 'Cliente', 'seller' => 'Vendedor', 'admin' => 'Administrador'] as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= ($_POST['role'] ?? $editUser['role'] ?? 'customer') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select></label>
                <label>Límite de crédito<input type="number" name="credit_limit" min="0" max="99999999.99" step="0.01" value="<?= e($_POST['credit_limit'] ?? $editUser['credit_limit'] ?? '0.00') ?>"></label>
            </div>
            <fieldset><legend>Estado</legend>
                <label class="check-option"><input type="radio" name="is_active" value="1" <?= (string) ($_POST['is_active'] ?? $editUser['is_active'] ?? '1') === '1' ? 'checked' : '' ?>> Activo</label>
                <label class="check-option"><input type="radio" name="is_active" value="0" <?= (string) ($_POST['is_active'] ?? $editUser['is_active'] ?? '1') === '0' ? 'checked' : '' ?>> Inactivo</label>
            </fieldset>
            <button class="button button-dark" type="submit">Guardar usuario</button>
            <?php if ($editUser): ?><a class="text-link" href="admin_usuarios.php">Cancelar edición</a><?php endif; ?>
        </form>
    </section>

    <section class="panel-card history-panel"><h2>Registrar abono de crédito</h2>
        <form method="post" class="form-row" data-validate>
            <?= csrf_field() ?><input type="hidden" name="action" value="payment">
            <label>Cliente<select name="customer_id" required><option value="">Selecciona un cliente</option>
                <?php foreach ($customers as $customer): ?><option value="<?= (int) $customer['id'] ?>"><?= e($customer['name'] . ' ' . $customer['last_name'] . ' · saldo $' . number_format((float) $customer['balance'], 2)) ?></option><?php endforeach; ?>
            </select></label>
            <label>Abono<input type="number" name="amount" min="0.01" max="99999999.99" step="0.01" required></label>
            <label>Nota<input name="note" maxlength="255"></label>
            <button class="button button-dark" type="submit">Registrar abono</button>
        </form>
    </section>

    <section class="panel-card history-panel"><h2>Usuarios</h2>
        <div class="table-wrap"><table><thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Límite</th><th>Saldo</th><th></th></tr></thead><tbody>
            <?php foreach ($users as $item): ?><tr>
                <td><?= e($item['name'] . ' ' . $item['last_name']) ?></td><td><?= e($item['email']) ?></td>
                <td><?= e(['customer' => 'Cliente', 'seller' => 'Vendedor', 'admin' => 'Administrador'][$item['role']] ?? $item['role']) ?></td>
                <td><?= $item['is_active'] ? 'Activo' : 'Inactivo' ?></td>
                <td>$<?= number_format((float) $item['credit_limit'], 2) ?></td><td>$<?= number_format((float) $item['balance'], 2) ?></td>
                <td><a class="text-link" href="?editar=<?= (int) $item['id'] ?>">Editar</a></td>
            </tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
</section>
