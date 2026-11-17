<?php
// includes/plantilla.php
// Cabecera (menú) y pie de página compartidos por la página principal y las páginas de cuenta.

function cabecera(string $titulo, array $o = []): void
{
    $desc   = $o['desc'] ?? SUCURSAL['nombre'] . ', ' . SUCURSAL['lema'] . '. Jamones, salchichas y chorizos en ' . SUCURSAL['ciudad'] . '.';
    $clase  = $o['clase'] ?? '';
    $u      = usuario();
    $avisos = flash_leer();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?></title>
    <meta name="description" content="<?= e($desc) ?>">
    <meta name="csrf" content="<?= e(csrf_token()) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=Source+Sans+3:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="/assets/estilos.css">
</head>
<body class="<?= e($clase) ?>">
<a class="saltar" href="#contenido">Saltar al contenido</a>
<header class="barra">
    <div class="caja barra-in">
        <a class="marca" href="/#inicio">
            <svg viewBox="0 0 40 40" width="38" height="38" aria-hidden="true">
                <rect width="40" height="40" rx="9" fill="#a3231a"/>
                <g transform="rotate(-22 20 20)">
                    <rect x="5" y="14" width="30" height="12" rx="6" fill="#f0b323"/>
                    <rect x="12" y="14" width="2.5" height="12" fill="#c98f0a"/>
                    <rect x="25.5" y="14" width="2.5" height="12" fill="#c98f0a"/>
                </g>
            </svg>
            <span><?= e(SUCURSAL['corto']) ?></span>
        </a>
        <button class="menu-btn" type="button" aria-expanded="false" aria-controls="menu-principal">Menú</button>
        <nav id="menu-principal" class="menu" aria-label="Principal">
            <a href="/#nosotros">Nosotros</a>
            <a href="/#productos">Productos</a>
            <a href="/#sucursal">La sucursal</a>
            <a href="/#opinion">Tu opinión</a>
            <a href="/#video">Video</a>
            <a href="/#chat">Chat</a>
            <a href="/#clientes">Clientes</a>
            <span class="sesion">
                <?php if ($u): ?>
                    <span class="quien">Hola, <?= e($u['nombre']) ?> <span class="rol"><?= $u['rol'] === 'admin' ? 'Administrador' : 'Cliente' ?></span></span>
                    <?php if ($u['rol'] === 'admin'): ?><a class="boton chico" href="/admin/">Panel</a><?php endif; ?>
                    <form method="post" action="/auth/logout.php">
                        <?= csrf_campo() ?>
                        <button class="enlace" type="submit">Salir</button>
                    </form>
                <?php else: ?>
                    <a href="/auth/login.php">Entrar</a>
                    <a class="boton chico" href="/auth/registro.php">Crear cuenta</a>
                <?php endif; ?>
            </span>
        </nav>
    </div>
</header>
<?php foreach ($avisos as [$tipo, $texto]): ?>
    <div class="caja"><p class="flash flash-<?= e($tipo) ?>" role="status"><?= e($texto) ?></p></div>
<?php endforeach; ?>
<?php
}

function pie(array $o = []): void
{
    $tracker = $o['tracker'] ?? true;
    ?>
<footer class="pie">
    <div class="caja pie-in">
        <div>
            <p class="pie-nombre"><?= e(SUCURSAL['nombre']) ?></p>
            <p><?= e(SUCURSAL['lema']) ?></p>
        </div>
        <div>
            <p><strong><?= e(SUCURSAL['sucursal']) ?></strong></p>
            <p><?= e(SUCURSAL['direccion']) ?><br><?= e(SUCURSAL['ciudad']) ?></p>
        </div>
        <div>
            <p><strong>Contacto</strong></p>
            <p><?= e(SUCURSAL['telefono']) ?><br><?= e(SUCURSAL['correo']) ?></p>
        </div>
    </div>
    <div class="caja pie-fin">
        <p>Sucursal ficticia creada con fines escolares. Los datos de contacto y la dirección son inventados.</p>
        <p><a href="#" id="cambiar-privacidad">Cambiar preferencia de cookies</a></p>
    </div>
</footer>
<script src="/assets/app.js"></script>
<?php if ($tracker): ?><script src="/assets/tracker.js"></script><?php endif; ?>
</body>
</html>
<?php
}
