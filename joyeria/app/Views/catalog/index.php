<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">PIEZAS QUE CUENTAN HISTORIAS</p>
        <h1>Encuentra algo<br>hecho para <em>perdurar.</em></h1>
        <p class="hero-text">Joyería artesanal e industrial elegida para acompañarte todos los días. Cada pieza tiene algo que decir.</p>
        <a class="button button-dark" href="#catalogo">Explorar colección <span aria-hidden="true">↓</span></a>
        <div class="hero-note"><span class="note-line"></span> Hecho con intención. Elegido para ti.</div>
    </div>
    <div class="hero-art" role="img" aria-label="Ilustración decorativa de una joya dorada">
        <span class="hero-sun"></span><span class="hero-ring"></span><span class="hero-stone"></span>
        <span class="hero-caption">COLECCIÓN<br>ATEMPORAL&nbsp; — &nbsp;01</span>
    </div>
    <span class="hero-index">01 / 02</span>
</section>

<section class="catalog section-wrap" id="catalogo">
    <div class="section-heading">
        <div><p class="eyebrow">HECHAS PARA ENCONTRARTE</p><h2>Piezas con <em>carácter.</em></h2></div>
        <p class="section-intro">De manos artesanas a diseños contemporáneos, descubre la pieza que habla de ti.</p>
    </div>
    <form class="catalog-controls catalog-filter-form" method="get" action="index.php" data-catalog-filter>
        <div class="filter-grid">
            <label>Buscar
                <input type="search" name="buscar" placeholder="Nombre o descripción" value="<?= e($search) ?>">
            </label>
            <label>Categoría
                <select name="categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categories as $option): ?>
                        <option value="<?= e($option['name']) ?>" <?= $category === $option['name'] ? 'selected' : '' ?>><?= e(ucfirst($option['name'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Tipo de pieza
                <select name="tipo">
                    <option value="">Todos</option>
                    <?php foreach ($types as $pieceType): ?>
                        <option value="<?= e($pieceType) ?>" <?= $type === $pieceType ? 'selected' : '' ?>><?= e($pieceType) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <?php if ($materials !== []): ?>
            <fieldset class="material-options">
                <legend>Material</legend>
                <?php foreach ($materials as $material): ?>
                    <label class="check-option">
                        <input type="checkbox" name="materiales[]" value="<?= e($material) ?>" <?= in_array($material, $selectedMaterials, true) ? 'checked' : '' ?>>
                        <?= e($material) ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        <?php endif; ?>
        <div class="filter-actions"><button type="submit" class="button button-dark">Aplicar filtros</button><a class="text-link" href="index.php#catalogo">Limpiar</a></div>
    </form>

    <?php if ($products === []): ?>
        <div class="empty-state"><span class="empty-icon" aria-hidden="true">✳</span>
            <h3><?= $totalProducts > 0 ? 'No encontramos esa pieza.' : 'Estamos preparando algo especial.' ?></h3>
            <p><?= $totalProducts > 0 ? 'Prueba con otros filtros.' : 'Muy pronto encontrarás aquí nuestra selección de joyería.' ?></p>
        </div>
    <?php else: ?>
        <p class="result-count"><?= $totalProducts ?> piezas encontradas</p>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <a class="product-image product-image-<?= e($product['category']) ?>" href="producto.php?id=<?= (int) $product['id'] ?>">
                        <span class="category-tag"><?= e(ucfirst($product['category'])) ?></span>
                        <?php if (($imageUrl = product_image_url($product['image_url'])) !== null): ?>
                            <img class="product-photo" src="<?= e($imageUrl) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                        <?php else: ?><span class="product-placeholder" aria-hidden="true">✳</span><?php endif; ?>
                        <?php if ((int) $product['stock'] < 1): ?><span class="stock-tag">Agotado</span><?php endif; ?>
                    </a>
                    <div class="product-info">
                        <div>
                            <h3><a href="producto.php?id=<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3>
                            <?php if ($product['category'] === 'artesanal' && $product['artisan_name']): ?><p class="artisan-name">Por <?= e($product['artisan_name']) ?></p><?php endif; ?>
                            <p class="artisan-name"><?= e($product['piece_type']) ?> · <?= e($product['material']) ?></p>
                        </div>
                        <span class="product-price">$<?= number_format((float) $product['price'], 2) ?></span>
                        <p class="product-description"><?= e($product['description']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($totalProducts > $perPage): ?>
            <nav class="pagination" aria-label="Páginas del catálogo">
                <?php $pageParameters = $_GET; unset($pageParameters['pagina']); ?>
                <?php if ($page > 1): ?><a href="?<?= e(http_build_query(array_merge($pageParameters, ['pagina' => $page - 1]))) ?>" aria-label="Página anterior">‹</a><?php endif; ?>
                <?php for ($pageNumber = max(1, $page - 2); $pageNumber <= min($totalPages, $page + 2); $pageNumber++): ?>
                    <a href="?<?= e(http_build_query(array_merge($pageParameters, ['pagina' => $pageNumber]))) ?>" <?= $page === $pageNumber ? 'aria-current="page"' : '' ?>><?= $pageNumber ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?><a href="?<?= e(http_build_query(array_merge($pageParameters, ['pagina' => $page + 1]))) ?>" aria-label="Página siguiente">›</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<section class="story" id="historia">
    <div class="story-mark" aria-hidden="true">T.</div>
    <div><p class="eyebrow">BELLEZA CON PROPÓSITO</p><h2>Un espacio para el talento<br>que merece ser <em>visto.</em></h2></div>
    <p class="story-text">Damos visibilidad a las manos artesanas y acercamos sus piezas únicas a nuevas personas. Elegir una joya también puede apoyar una historia.</p>
</section>
