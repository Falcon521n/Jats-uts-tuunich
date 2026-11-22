<section class="content-page">
    <p class="eyebrow">TU SELECCIÓN</p>
    <h1>Tu carrito</h1>
    <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <?php if ($lines === []): ?>
        <div class="empty-state"><h3>Tu carrito está vacío.</h3><p>Encuentra una pieza que te guste en nuestro catálogo.</p><a class="button button-dark" href="index.php">Ver catálogo</a></div>
    <?php else: ?>
        <form method="post" class="cart-list">
            <?= csrf_field() ?><input type="hidden" name="action" value="update">
            <?php foreach ($lines as $line): ?>
                <article class="cart-line" data-unit-price="<?= e(number_format((float) $line['price'], 2, '.', '')) ?>">
                    <div class="cart-thumb">
                        <?php if (($imageUrl = product_image_url($line['image_url'])) !== null): ?><img src="<?= e($imageUrl) ?>" alt=""><?php else: ?><span aria-hidden="true">✳</span><?php endif; ?>
                    </div>
                    <div class="cart-description"><h2><?= e($line['name']) ?></h2><p><?= !$line['is_active'] ? 'Ya no está a la venta' : ((int) $line['stock'] > 0 ? 'Disponible' : 'Agotado') ?></p></div>
                    <span class="product-price">$<?= number_format((float) $line['price'], 2) ?></span>
                    <label class="quantity-field">Cantidad
                        <input type="number" name="quantities[<?= (int) $line['id'] ?>]" min="1" value="<?= (int) $line['quantity'] ?>" data-cart-quantity <?= !$line['is_active'] ? 'disabled' : '' ?>>
                    </label>
                    <strong data-line-total>$<?= number_format((float) $line['subtotal'], 2) ?></strong>
                    <button class="text-button" type="submit" form="remove-<?= (int) $line['id'] ?>">Quitar</button>
                </article>
            <?php endforeach; ?>
            <button type="submit" class="button button-light">Actualizar cantidades</button>
        </form>
        <div class="checkout-summary">
            <div class="summary-total"><span>Total</span><strong data-cart-total>$<?= number_format($total, 2) ?></strong></div>
            <form method="post" class="stack-form checkout-form" data-validate>
                <?= csrf_field() ?><input type="hidden" name="action" value="checkout"><input type="hidden" name="payment_method" value="debit">
                <fieldset>
                    <legend>Pago con tarjeta</legend>
                    <label>Número de tarjeta
                        <input name="card_number" inputmode="numeric" autocomplete="off" maxlength="16" pattern="[0-9]{16}" placeholder="1234567891234567">
                    </label>
                    <label>Vencimiento
                        <input name="card_expiration" inputmode="numeric" autocomplete="off" maxlength="4" pattern="[0-9]{4}" placeholder="1234">
                    </label>
                    <label>CVV
                        <input name="card_security_code" type="password" inputmode="numeric" autocomplete="off" maxlength="3" pattern="[0-9]{3}" placeholder="123">
                    </label>
                    <label>Titular de la tarjeta
                        <input name="cardholder" autocomplete="cc-name" maxlength="241" placeholder="<?= e($cardholderName) ?>">
                    </label>
                </fieldset>
                <label class="check-option"><input type="checkbox" name="confirm" value="yes" required> Confirmo mi compra</label>
                <button class="button button-dark" type="submit">Finalizar compra</button>
                <small>Solo se aceptan los datos de demostración indicados. El titular debe coincidir con el nombre de tu cuenta. No ingreses datos de una tarjeta real; no se guardan datos de tarjeta.</small>
            </form>
        </div>
        <?php foreach ($lines as $line): ?>
            <form id="remove-<?= (int) $line['id'] ?>" method="post" class="hidden-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int) $line['id'] ?>">
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
