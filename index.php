<?php
// Catálogo principal de Ropita. El inventario se administra desde /admin/.
require __DIR__ . '/includes/conexion.php';
require __DIR__ . '/includes/plantilla.php';

$u = usuario();
$productos = [];
$por_categoria = array_fill_keys(CATEGORIAS, []);
$marcas = implode(',', array_fill(0, count(CATEGORIAS), '?'));
$consulta = $pdo->prepare("SELECT * FROM productos WHERE categoria IN ($marcas) ORDER BY categoria, nombre");
$consulta->execute(CATEGORIAS);
foreach ($consulta->fetchAll() as $producto) {
    $por_categoria[$producto['categoria']][] = $producto;
    $productos[] = $producto;
}

cabecera('Ropita | Tu estilo, a tu manera', ['clase' => 'inicio']);
?>
<main id="contenido">
    <section class="ropita-hero" id="inicio">
        <div class="caja ropita-hero-in">
            <div class="hero-copy">
                <p class="eyebrow">VÍSTETE COMO TE SIENTES</p>
                <h1>Tu estilo.<br><span>Tu historia.</span></h1>
                <p>Prendas cómodas, detalles que hablan de ti y novedades para todos los días. Encuentra tu próxima favorita en Ropita.</p>
                <a class="boton ropita-cta" href="#catalogo">Explorar colección <span aria-hidden="true">↗</span></a>
                <p class="hero-note">Estilo cotidiano, elegido con cariño.</p>
            </div>
            <div class="hero-art" aria-label="Ilustración de prendas y accesorios" role="img">
                <span class="art-sparkle sparkle-one">✳</span><span class="art-sparkle sparkle-two">✦</span>
                <div class="art-card card-back"><span>ROPITA</span><span>NEW SEASON</span></div>
                <div class="art-card card-front"><span class="art-shirt">👚</span><span class="art-label">hecho para ti</span></div>
                <span class="art-sticker">VISTE<br>A TU<br>MANERA</span>
            </div>
        </div>
        <div class="hero-ribbon"><span>PIEZAS PARA TODOS LOS DÍAS</span><span>✳</span><span>ENCUENTRA TU ESTILO</span><span>✳</span><span>PIEZAS PARA TODOS LOS DÍAS</span></div>
    </section>

    <section class="seccion catalogo" id="catalogo">
        <div class="caja">
            <div class="section-heading">
                <div><p class="eyebrow">HECHO PARA COMBINAR</p><h2>Descubre Ropita</h2></div>
                <p>Una selección para acompañarte en cada plan. Explora lo que tenemos disponible.</p>
            </div>
            <nav class="category-links" aria-label="Categorías del catálogo">
                <?php foreach (CATEGORIAS as $categoria): ?><a href="#cat-<?= e(strtolower(str_replace(' ', '-', $categoria))) ?>"><?= e($categoria) ?> <span>↘</span></a><?php endforeach; ?>
            </nav>
            <?php if (!$productos): ?>
                <div class="catalogo-vacio"><span aria-hidden="true">🧵</span><h3>Estamos preparando la colección</h3><p>Muy pronto encontrarás aquí nuestras prendas y accesorios. Vuelve a visitarnos pronto.</p>
                    <?php if (es_admin()): ?><a class="boton" href="/admin/productos.php">Agregar productos al catálogo</a><?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($por_categoria as $categoria => $lista): if (!$lista) continue; $ancla = strtolower(str_replace(' ', '-', $categoria)); ?>
                    <section class="product-section" id="cat-<?= e($ancla) ?>">
                        <div class="category-title"><h3><?= e($categoria) ?></h3><span><?= count($lista) ?> <?= count($lista) === 1 ? 'pieza' : 'piezas' ?></span></div>
                        <div class="product-grid">
                            <?php foreach ($lista as $p): $agotado = (int) $p['stock'] <= 0; ?>
                                <article class="product-card <?= $agotado ? 'product-soldout' : '' ?>">
                                    <div class="product-image"><span class="product-emoji" aria-hidden="true"><?= e($p['emoji']) ?></span>
                                        <?php if ($agotado): ?><span class="product-badge soldout-badge">Agotado</span><?php else: ?><span class="product-badge">Disponible</span><?php endif; ?>
                                    </div>
                                    <div class="product-info"><p class="product-type"><?= e($categoria) ?></p><h4><?= e($p['nombre']) ?></h4>
                                        <?php if ($p['descripcion'] !== ''): ?><p class="product-desc"><?= e($p['descripcion']) ?></p><?php endif; ?>
                                        <div class="product-bottom"><strong><?= e(dinero((float) $p['precio'])) ?></strong><span>/ <?= e($p['unidad']) ?></span></div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="style-note" id="nosotros"><div class="caja style-note-in"><span class="note-icon" aria-hidden="true">✳</span><div><p class="eyebrow">UN CLÓSET CON PERSONALIDAD</p><h2>La ropa no tiene que complicarse.</h2><p>En Ropita creemos que vestirte bien empieza por sentirte bien. Por eso reunimos básicos cómodos y accesorios con personalidad para que armes looks que se sientan tuyos.</p></div><a href="#catalogo" class="text-link">Encuentra tu próxima favorita <span aria-hidden="true">→</span></a></div></section>

    <section class="community-section" id="comunidad"><div class="caja community-in"><div><p class="eyebrow">CRECEMOS CONTIGO</p><h2>Tu opinión también marca tendencia.</h2><p>Ayúdanos a elegir qué prendas y novedades quieres ver en Ropita. Tu opinión nos ayuda a mejorar el catálogo.</p></div>
        <?php if ($u): ?><form class="formulario community-form" method="post" action="/acciones/encuesta.php"><?= csrf_campo() ?><?php foreach (ENCUESTA as $campo => $q): ?><label for="q-<?= e($campo) ?>"><?= e($q['pregunta']) ?></label><select id="q-<?= e($campo) ?>" name="<?= e($campo) ?>" required><option value="">Elige una opción</option><?php foreach ($q['opciones'] as $op): ?><option><?= e($op) ?></option><?php endforeach; ?></select><?php endforeach; ?><button class="boton" type="submit">Enviar mi opinión</button></form>
        <?php else: ?><div class="community-card"><span aria-hidden="true">💌</span><h3>Únete a la comunidad Ropita</h3><p>Crea una cuenta para compartir tu opinión y ayudarnos a mejorar.</p><a class="boton" href="/auth/registro.php">Crear cuenta gratis</a><p class="login-note">¿Ya tienes cuenta? <a href="/auth/login.php?volver=%2F%23comunidad">Inicia sesión</a></p></div><?php endif; ?>
    </div></section>
</main>
<?php pie(); ?>
