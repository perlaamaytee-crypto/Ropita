<?php
// Cabecera y pie compartidos.
function cabecera(string $titulo, array $o = []): void
{
    $desc = $o['desc'] ?? 'Ropita: prendas cómodas y accesorios con personalidad para todos los días.';
    $clase = $o['clase'] ?? '';
    $u = usuario();
    $avisos = flash_leer();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f1e9">
    <title><?= e($titulo) ?></title>
    <meta name="description" content="<?= e($desc) ?>">
    <meta name="csrf" content="<?= e(csrf_token()) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap">
    <link rel="stylesheet" href="/assets/estilos.css">
</head>
<body class="<?= e($clase) ?>">
<a class="saltar" href="#contenido">Saltar al contenido</a>
<header class="barra ropita-barra"><div class="caja barra-in">
    <a class="marca ropita-marca" href="/#inicio" aria-label="Ropita, inicio"><span class="marca-icon" aria-hidden="true">r.</span><span>ropita</span></a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="menu-principal">Menú</button>
    <nav id="menu-principal" class="menu ropita-menu" aria-label="Principal">
        <a href="/#catalogo">Colección</a><a href="/#nosotros">Nosotros</a><a href="/#comunidad">Comunidad</a>
        <span class="sesion">
            <?php if ($u): ?><span class="quien">Hola, <?= e($u['nombre']) ?></span><?php if ($u['rol'] === 'admin'): ?><a class="boton chico" href="/admin/">Panel</a><?php endif; ?><form method="post" action="/auth/logout.php"><?= csrf_campo() ?><button class="enlace" type="submit">Salir</button></form>
            <?php else: ?><a href="/auth/login.php">Iniciar sesión</a><a class="boton chico" href="/auth/registro.php">Crear cuenta</a><?php endif; ?>
        </span>
    </nav>
</div></header>
<?php foreach ($avisos as [$tipo, $texto]): ?><div class="caja"><p class="flash flash-<?= e($tipo) ?>" role="status"><?= e($texto) ?></p></div><?php endforeach; ?>
<?php
}

function pie(array $o = []): void
{
    $tracker = $o['tracker'] ?? true;
    ?>
<footer class="pie ropita-footer"><div class="caja footer-main">
    <div><a class="marca ropita-marca footer-brand" href="/#inicio"><span class="marca-icon" aria-hidden="true">r.</span><span>ropita</span></a><p>Tu estilo, a tu manera.</p></div>
    <div><h2>Explora</h2><a href="/#catalogo">Colección</a><a href="/#nosotros">Nosotros</a><a href="/#comunidad">Comunidad</a></div>
    <div><h2>Tu cuenta</h2><a href="/auth/login.php">Iniciar sesión</a><a href="/auth/registro.php">Crear cuenta</a><?php if (es_admin()): ?><a href="/admin/">Administrar catálogo</a><?php endif; ?></div>
</div><div class="caja footer-bottom"><span>© <?= date('Y') ?> Ropita. Hecho para expresarte.</span><a href="#" id="cambiar-privacidad">Preferencias de cookies</a></div></footer>
<script src="/assets/app.js"></script>
<?php if ($tracker): ?><script src="/assets/tracker.js"></script><?php endif; ?>
</body></html>
<?php
}
