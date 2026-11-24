<section class="content-page">
    <p class="eyebrow">TU ESPACIO</p><h1>Mi cuenta</h1>
    <div class="account-grid">
        <section class="panel-card">
            <h2>Mis datos</h2>
            <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>
            <form method="post" class="stack-form" data-validate>
                <?= csrf_field() ?>
                <label>Nombre<input name="name" required maxlength="120" value="<?= e($profile['name']) ?>"></label>
                <label>Apellidos<input name="last_name" required maxlength="120" value="<?= e($profile['last_name']) ?>"></label>
                <label>Correo<input type="email" value="<?= e($profile['email']) ?>" disabled></label>
                <label>Teléfono<input type="tel" name="phone" maxlength="30" value="<?= e($profile['phone']) ?>"></label>
                <label>Dirección<textarea name="address" rows="3" maxlength="500"><?= e($profile['address']) ?></textarea></label>
                <button class="button button-dark" type="submit">Guardar datos</button>
            </form>
        </section>
    </div>
    <section class="panel-card history-panel">
        <div class="section-heading"><div><p class="eyebrow">TUS COMPRAS</p><h2>Historial</h2></div>
            <form method="get" class="inline-form"><label>Periodo
                <select name="period" onchange="this.form.submit()">
                    <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>Todo</option>
                    <option value="30" <?= $period === '30' ? 'selected' : '' ?>>Últimos 30 días</option>
                    <option value="365" <?= $period === '365' ? 'selected' : '' ?>>Último año</option>
                </select>
            </label></form>
        </div>
        <?php if ($history === []): ?><p class="form-intro">Todavía no tienes compras registradas.</p><?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Venta</th><th>Fecha</th><th>Productos</th><th>Pago</th><th>Total</th></tr></thead><tbody>
                <?php foreach ($history as $sale): ?><tr><td>#<?= (int) $sale['id'] ?></td><td><?= e($sale['created_at']) ?></td><td><?= e($sale['items']) ?></td><td><?= e(payment_label($sale['payment_method'])) ?></td><td>$<?= number_format((float) $sale['total'], 2) ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</section>
