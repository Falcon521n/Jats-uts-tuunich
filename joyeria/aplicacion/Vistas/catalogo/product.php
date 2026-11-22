<section class="detail-page">
    <div class="detail-image">
        <?php if (($imageUrl = product_image_url($product['image_url'])) !== null): ?>
            <img class="product-photo" src="<?= e($imageUrl) ?>" alt="<?= e($product['name']) ?>">
        <?php else: ?><span class="product-placeholder" aria-hidden="true">✳</span><?php endif; ?>
    </div>
    <div class="detail-copy">
        <p class="eyebrow"><?= e(ucfirst($product['category'])) ?> · <?= e($product['piece_type']) ?></p>
        <h1><?= e($product['name']) ?></h1>
        <p class="detail-price">$<?= number_format((float) $product['price'], 2) ?></p>
        <p class="detail-description"><?= nl2br(e($product['description'])) ?></p>
        <dl class="product-specs">
            <div><dt>Material</dt><dd><?= e($product['material']) ?></dd></div>
            <div><dt>Disponibilidad</dt><dd><?= (int) $product['stock'] > 0 ? 'Disponible' : 'Agotado' ?></dd></div>
            <?php if ($product['artisan_name']): ?><div><dt>Artesano/a</dt><dd><?= e($product['artisan_name']) ?></dd></div><?php endif; ?>
        </dl>
        <?php if ((int) $product['stock'] > 0): ?>
            <?php if (current_user() !== null && current_user()['role'] === 'customer'): ?>
                <form action="carrito.php" method="post" class="add-cart-form" data-validate>
                    <?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <label>Cantidad<input type="number" name="quantity" min="1" value="1" required></label>
                    <button class="button button-dark" type="submit">Agregar al carrito</button>
                </form>
            <?php elseif (current_user() === null): ?>
                <p class="form-intro"><a class="text-link" href="index.php#acceso">Inicia sesión</a> o <a class="text-link" href="registro.php">crea una cuenta</a> para agregar esta pieza al carrito.</p>
            <?php else: ?><p class="form-intro">El carrito está disponible para cuentas de cliente.</p><?php endif; ?>
        <?php endif; ?>
    </div>
</section>
