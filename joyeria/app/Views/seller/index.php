<section class="content-page">
    <p class="eyebrow">PANEL DE VENTAS</p><h1>Registrar venta</h1>
    <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <section class="panel-card">
        <form method="post" class="form-row seller-sale-form" data-validate>
            <?= csrf_field() ?>
            <label>Cliente<select name="customer_id" required><option value="">Selecciona un cliente</option>
                <?php foreach ($customers as $customer): ?><option value="<?= (int) $customer['id'] ?>"><?= e($customer['name'] . ' ' . $customer['last_name'] . ' · ' . $customer['email']) ?></option><?php endforeach; ?>
            </select></label>
            <label>Producto<select name="product_id" required><option value="">Selecciona una pieza</option>
                <?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>"><?= e($product['name'] . ' · ' . $product['stock'] . ' disponibles · $' . number_format((float) $product['price'], 2)) ?></option><?php endforeach; ?>
            </select></label>
            <label>Cantidad<input type="number" name="quantity" min="1" max="10000" value="1" required></label>
            <fieldset><legend>Método de pago</legend>
                <label class="check-option"><input type="radio" name="payment_method" value="debit" required> Débito</label>
                <label class="check-option"><input type="radio" name="payment_method" value="credit"> Crédito</label>
                <label class="check-option"><input type="radio" name="payment_method" value="deposit"> Depósito</label>
            </fieldset>
            <button class="button button-dark" type="submit">Registrar venta</button>
        </form>
    </section>
    <section class="panel-card history-panel"><p class="eyebrow">ACTIVIDAD</p><h2>Mis ventas</h2>
        <?php if ($sales === []): ?><p class="form-intro">Aún no tienes ventas registradas.</p><?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Venta</th><th>Fecha</th><th>Cliente</th><th>Productos</th><th>Método</th><th>Total</th></tr></thead><tbody>
                <?php foreach ($sales as $sale): ?><tr><td>#<?= (int) $sale['id'] ?></td><td><?= e($sale['created_at']) ?></td><td><?= e($sale['customer_name']) ?></td><td><?= e($sale['items']) ?></td><td><?= e(payment_label($sale['payment_method'])) ?></td><td>$<?= number_format((float) $sale['total'], 2) ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</section>
