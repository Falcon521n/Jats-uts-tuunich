<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">PIEZAS QUE CUENTAN HISTORIAS</p>
        <h1>Encuentra algo<br>hecho para <em>perdurar.</em></h1>
        <p class="hero-text">Joyería artesanal e industrial elegida para acompañarte todos los días. Cada pieza tiene algo que decir.</p>
        <a class="button button-dark" href="#catalogo">Explorar colección <span aria-hidden="true">↓</span></a>
        <div class="hero-note"><span class="note-line"></span> Hecho con intención. Elegido para ti.</div>
    </div>
    <?php if ($featuredProducts !== []): ?>
        <div class="hero-carousel" data-product-carousel role="region" aria-label="Productos disponibles">
            <div class="hero-carousel-slides">
                <?php foreach ($featuredProducts as $index => $product): ?>
                    <a class="hero-carousel-slide" data-carousel-slide href="product.php?id=<?= (int) $product['id'] ?>" <?= $index > 0 ? 'hidden' : '' ?>>
                        <img src="<?= e(product_image_url($product['image_url'])) ?>" alt="<?= e($product['name']) ?>">
                        <span class="hero-carousel-caption"><?= e($product['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (count($featuredProducts) > 1): ?>
                <div class="hero-carousel-controls">
                    <button type="button" data-carousel-previous aria-label="Foto anterior">‹</button>
                    <span data-carousel-status aria-hidden="true">1 / <?= count($featuredProducts) ?></span>
                    <button type="button" data-carousel-next aria-label="Foto siguiente">›</button>
                    <button type="button" data-carousel-toggle aria-pressed="false">Pausar</button>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="hero-carousel hero-carousel-empty">
            <p>Aquí encontrarás las fotos de nuestras piezas disponibles.</p>
        </div>
    <?php endif; ?>
</section>

<?php if (current_user() === null): ?>
    <section class="home-access" id="acceso">
        <div class="home-access-copy">
            <p class="eyebrow">TUUNICH</p>
            <h2>Inicia sesión para <em>elegir.</em></h2>
            <p>Entra para guardar piezas en tu carrito y finalizar una compra de demostración.</p>
            <a class="text-link" href="register.php">¿Primera vez? Crea una cuenta de cliente</a>
        </div>
        <form action="login.php" method="post" class="home-access-form">
            <?= csrf_field() ?>
            <label>Usuario o correo
                <input type="text" name="identifier" required maxlength="190" autocomplete="username">
            </label>
            <label>Contraseña
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="button button-dark" type="submit">Iniciar sesión</button>
        </form>
    </section>
<?php endif; ?>

<section class="catalog section-wrap" id="catalogo">
    <div class="section-heading">
        <div><p class="eyebrow">HECHAS PARA ENCONTRARTE</p><h2>Piezas con <em>carácter.</em></h2></div>
        <p class="section-intro">De manos artesanas a diseños contemporáneos, descubre la pieza que habla de ti.</p>
    </div>
    <form class="catalog-controls catalog-filter-form" method="get" action="index.php" data-catalog-filter>
        <div class="filter-grid">
            <label>Buscar
                <input type="search" name="search" placeholder="Nombre o descripción" value="<?= e($search) ?>">
            </label>
            <label>Categoría
                <select name="category">
                    <option value="">Todas</option>
                    <?php foreach ($categories as $option): ?>
                        <option value="<?= e($option['name']) ?>" <?= $category === $option['name'] ? 'selected' : '' ?>><?= e(ucfirst($option['name'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Tipo de pieza
                <select name="type">
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
                        <input type="checkbox" name="materials[]" value="<?= e($material) ?>" <?= in_array($material, $selectedMaterials, true) ? 'checked' : '' ?>>
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
                    <a class="product-image product-image-<?= e($product['category']) ?>" href="product.php?id=<?= (int) $product['id'] ?>">
                        <span class="category-tag"><?= e(ucfirst($product['category'])) ?></span>
                        <?php if (($imageUrl = product_image_url($product['image_url'])) !== null): ?>
                            <img class="product-photo" src="<?= e($imageUrl) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                        <?php else: ?><span class="product-placeholder" aria-hidden="true">✳</span><?php endif; ?>
                        <?php if ((int) $product['stock'] < 1): ?><span class="stock-tag">Agotado</span><?php endif; ?>
                    </a>
                    <div class="product-info">
                        <div>
                            <h3><a href="product.php?id=<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3>
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
                <?php $pageParameters = $_GET; unset($pageParameters['page']); ?>
                <?php if ($page > 1): ?><a href="?<?= e(http_build_query(array_merge($pageParameters, ['page' => $page - 1]))) ?>" aria-label="Página anterior">‹</a><?php endif; ?>
                <?php for ($pageNumber = max(1, $page - 2); $pageNumber <= min($totalPages, $page + 2); $pageNumber++): ?>
                    <a href="?<?= e(http_build_query(array_merge($pageParameters, ['page' => $pageNumber]))) ?>" <?= $page === $pageNumber ? 'aria-current="page"' : '' ?>><?= $pageNumber ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?><a href="?<?= e(http_build_query(array_merge($pageParameters, ['page' => $page + 1]))) ?>" aria-label="Página siguiente">›</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<section class="story" id="historia">
    <div class="story-mark" aria-hidden="true">T.</div>
    <div><p class="eyebrow">BELLEZA CON PROPÓSITO</p><h2>Un espacio para el talento<br>que merece ser <em>visto.</em></h2></div>
    <p class="story-text">Damos visibilidad a las manos artesanas y acercamos sus piezas únicas a nuevas personas. Elegir una joya también puede apoyar una historia.</p>
</section>
