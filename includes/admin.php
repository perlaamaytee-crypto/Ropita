<?php
// includes/admin.php: marco común de las páginas del panel de administración.

function admin_cabecera(string $titulo, string $activa): void
{
    $u = usuario();
    $pestanas = [
        'index'       => ['/admin/', 'Resumen'],
        'productos'   => ['/admin/productos.php', 'Inventario'],
        'encuesta'    => ['/admin/encuesta.php', 'Encuesta'],
        'clientes'    => ['/admin/clientes.php', 'Clientes'],
        'ubicaciones' => ['/admin/ubicaciones.php', 'Ubicaciones'],
        'fotos'       => ['/admin/fotos.php', 'Fotos'],
        'metricas'    => ['/admin/metricas.php', 'Métricas'],
    ];
    $avisos = flash_leer();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="csrf" content="<?= e(csrf_token()) ?>">
    <title><?= e($titulo) ?> | Panel</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=Source+Sans+3:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="/assets/estilos.css">
</head>
<body class="admin">
<header class="admin-barra">
    <div class="caja admin-barra-in">
        <a class="admin-marca" href="/admin/">Panel de <?= e(SUCURSAL['corto']) ?></a>
        <span class="admin-quien"><?= e($u['nombre'] ?? '') ?> (Administrador)
            <a href="/">Ver el sitio</a>
        </span>
    </div>
    <nav class="caja admin-tabs" aria-label="Secciones del panel">
        <?php foreach ($pestanas as $clave => [$url, $texto]): ?>
            <a href="<?= e($url) ?>" <?= $clave === $activa ? 'aria-current="page"' : '' ?>><?= e($texto) ?></a>
        <?php endforeach; ?>
    </nav>
</header>
<main id="contenido" class="caja admin-main">
<?php foreach ($avisos as [$tipo, $texto]): ?>
    <p class="flash flash-<?= e($tipo) ?>" role="status"><?= e($texto) ?></p>
<?php endforeach; ?>
<?php
}

function admin_pie(array $scripts = []): void
{
    echo "</main>\n";
    foreach ($scripts as $s) {
        echo '<script src="' . e($s) . '"></script>' . "\n";
    }
    echo "</body>\n</html>\n";
}
