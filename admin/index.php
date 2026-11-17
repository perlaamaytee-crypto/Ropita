<?php
// admin/index.php: resumen del negocio para el administrador.

require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/admin.php';
requerir_admin($pdo);

$conteo = fn(string $tabla): int => (int) $pdo->query("SELECT COUNT(*) FROM $tabla")->fetchColumn();
$tarjetas = [
    ['Productos en el pizarrón', $conteo('productos'), '/admin/productos.php'],
    ['Clientes registrados', (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'")->fetchColumn(), '/admin/clientes.php'],
    ['Opiniones recibidas', $conteo('respuestas'), '/admin/encuesta.php'],
    ['Ubicaciones compartidas', $conteo('ubicaciones'), '/admin/ubicaciones.php'],
    ['Fotos subidas', $conteo('fotos'), '/admin/fotos.php'],
    ['Eventos medidos', $conteo('eventos'), '/admin/metricas.php'],
];
$sin_stock = $pdo->query('SELECT nombre, stock FROM productos WHERE stock <= 5 ORDER BY stock, nombre')->fetchAll();

admin_cabecera('Resumen', 'index');
?>
<h1>Resumen</h1>
<ul class="kpis">
    <?php foreach ($tarjetas as [$titulo, $n, $url]): ?>
        <li class="kpi"><a href="<?= e($url) ?>"><strong><?= $n ?></strong><span><?= e($titulo) ?></span></a></li>
    <?php endforeach; ?>
</ul>

<h2>Por reponer</h2>
<?php if ($sin_stock): ?>
    <div class="tabla"><table>
        <thead><tr><th>Producto</th><th>Piezas o kilos en stock</th></tr></thead>
        <tbody>
        <?php foreach ($sin_stock as $p): ?>
            <tr><td><?= e($p['nombre']) ?></td><td><?= (int) $p['stock'] === 0 ? 'Agotado' : (int) $p['stock'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
<?php else: ?>
    <p>Todo el inventario está por encima de 5. No hay nada urgente por reponer.</p>
<?php endif; ?>
<?php admin_pie(); ?>
