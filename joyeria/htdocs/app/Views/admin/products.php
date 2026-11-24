<section class="content-page">
    <p class="eyebrow">ADMINISTRACIÓN</p><h1>Productos</h1>
    <?php foreach ($errors as $error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endforeach; ?>
    <section class="panel-card">
        <h2><?= $product ? 'Editar producto' : 'Agregar producto' ?></h2>
        <form method="post" enctype="multipart/form-data" class="stack-form" data-validate>
            <?= csrf_field() ?><input type="hidden" name="action" value="save">
            <?php if ($product): ?><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><?php endif; ?>
            <label>Nombre<input name="name" required maxlength="160" value="<?= e($_POST['name'] ?? $product['name'] ?? '') ?>"></label>
            <label>Descripción<textarea name="description" rows="4" required maxlength="5000"><?= e($_POST['description'] ?? $product['description'] ?? '') ?></textarea></label>
            <div class="form-row">
                <label>Categoría<select name="category" required>
                    <option value="">Selecciona</option>
                    <?php foreach ($categories as $option): ?>
                        <option value="<?= e($option['name']) ?>" <?= ($_POST['category'] ?? $product['category'] ?? '') === $option['name'] ? 'selected' : '' ?>><?= e(ucfirst($option['name'])) ?></option>
                    <?php endforeach; ?>
                </select></label>
                <label>Tipo de pieza<input name="piece_type" required maxlength="80" placeholder="Anillo, collar..." value="<?= e($_POST['piece_type'] ?? $product['piece_type'] ?? '') ?>"></label>
                <label>Material<input name="material" required maxlength="80" placeholder="Plata, oro..." value="<?= e($_POST['material'] ?? $product['material'] ?? '') ?>"></label>
            </div>
            <div class="form-row">
                <label>Precio<input type="number" name="price" min="0" max="99999999.99" step="0.01" required value="<?= e($_POST['price'] ?? $product['price'] ?? '') ?>"></label>
                <label>Existencias<input type="number" name="stock" min="0" max="1000000" step="1" required value="<?= e($_POST['stock'] ?? $product['stock'] ?? '0') ?>"></label>
                <label>Nombre de artesano/a (opcional)<input name="artisan_name" maxlength="120" value="<?= e($_POST['artisan_name'] ?? $product['artisan_name'] ?? '') ?>"></label>
            </div>
            <label>Imagen (JPG, PNG o WebP, máximo 5 MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
            <?php if ($product && ($currentImage = product_image_url($product['image_url'])) !== null): ?><p class="form-intro">Imagen actual: <a class="text-link" href="<?= e($currentImage) ?>" target="_blank" rel="noopener">ver</a></p><?php endif; ?>
            <button class="button button-dark" type="submit">Guardar producto</button>
            <?php if ($product): ?><a class="text-link" href="admin_products.php">Cancelar edición</a><?php endif; ?>
        </form>
    </section>
    <section class="panel-card history-panel"><h2>Catálogo</h2>
        <div class="table-wrap"><table><thead><tr><th>Producto</th><th>Categoría</th><th>Tipo / material</th><th>Precio</th><th>Existencia</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            <?php foreach ($products as $item): ?><tr>
                <td><?= e($item['name']) ?></td><td><?= e(ucfirst($item['category'])) ?></td><td><?= e($item['piece_type'] . ' / ' . $item['material']) ?></td>
                <td>$<?= number_format((float) $item['price'], 2) ?></td><td><?= (int) $item['stock'] ?></td><td><?= $item['is_active'] ? 'Activo' : 'Inactivo' ?></td>
                <td class="action-cell"><a class="text-link" href="?edit=<?= (int) $item['id'] ?>">Editar</a>
                    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>"><button class="text-button" type="submit"><?= $item['is_active'] ? 'Desactivar' : 'Activar' ?></button></form>
                </td>
            </tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
</section>
