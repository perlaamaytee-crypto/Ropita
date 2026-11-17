<?php
// admin/ubicaciones.php: ubicaciones que compartieron los clientes y su distancia a la sucursal.

require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/admin.php';
requerir_admin($pdo);

$filas = $pdo->query('SELECT nombre, latitud, longitud, precision_m, distancia_km, creado_en FROM ubicaciones ORDER BY id DESC LIMIT 100')->fetchAll();

admin_cabecera('Ubicaciones', 'ubicaciones');
?>
<h1>Ubicaciones de clientes</h1>
<p>Cada fila es una vez que un cliente pulsó «Calcular mi distancia». La distancia es en línea recta hasta la sucursal.</p>
<div class="tabla"><table>
    <thead><tr><th>Cliente</th><th>Distancia</th><th>Coordenadas</th><th>Precisión</th><th>Fecha</th><th>Mapa</th></tr></thead>
    <tbody>
    <?php if (!$filas): ?><tr><td colspan="6">Todavía nadie ha compartido su ubicación.</td></tr><?php endif; ?>
    <?php foreach ($filas as $f): ?>
        <tr>
            <td><?= e($f['nombre']) ?></td>
            <td><?= e(number_format((float) $f['distancia_km'], 2)) ?> km</td>
            <td><?= e(number_format((float) $f['latitud'], 5)) ?>, <?= e(number_format((float) $f['longitud'], 5)) ?></td>
            <td>±<?= (int) $f['precision_m'] ?> m</td>
            <td><?= e(fecha_mx($f['creado_en'])) ?></td>
            <td><a href="https://www.google.com/maps?q=<?= e((string) $f['latitud']) ?>,<?= e((string) $f['longitud']) ?>" target="_blank" rel="noopener">Ver</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php admin_pie(); ?>
