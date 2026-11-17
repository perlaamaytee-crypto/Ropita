<?php
// admin/encuesta.php: resultados de la encuesta (conteos por pregunta y todas las respuestas).

require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/admin.php';
requerir_admin($pdo);

$total = (int) $pdo->query('SELECT COUNT(*) FROM respuestas')->fetchColumn();

// Los nombres de columna salen de config.php (no del usuario), por eso es seguro armarlos así.
$resumen = [];
foreach (ENCUESTA as $campo => $q) {
    $conteos = array_fill_keys($q['opciones'], 0);
    foreach ($pdo->query("SELECT $campo AS valor, COUNT(*) AS total FROM respuestas GROUP BY $campo") as $f) {
        if (isset($conteos[$f['valor']])) {
            $conteos[$f['valor']] = (int) $f['total'];
        }
    }
    $resumen[$campo] = $conteos;
}
$respuestas = $pdo->query('SELECT * FROM respuestas ORDER BY id DESC LIMIT 100')->fetchAll();

admin_cabecera('Encuesta', 'encuesta');
?>
<h1>Encuesta de satisfacción</h1>
<p><?= $total ?> <?= $total === 1 ? 'respuesta' : 'respuestas' ?> en total.</p>

<?php if ($total > 0): ?>
    <div class="admin-grid">
    <?php foreach (ENCUESTA as $campo => $q): ?>
        <section class="panel">
            <h2><?= e($q['pregunta']) ?></h2>
            <ul class="barras">
                <?php foreach ($resumen[$campo] as $op => $n): ?>
                    <?php $pct = $total ? round($n * 100 / $total) : 0; ?>
                    <li>
                        <span class="barra-texto"><?= e($op) ?></span>
                        <span class="barra-pista"><span class="barra-valor" style="width: <?= (int) $pct ?>%"></span></span>
                        <span class="barra-num"><?= $n ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
    </div>

    <h2>Últimas respuestas</h2>
    <div class="tabla"><table>
        <thead><tr><th>Fecha</th><th>Cliente</th><th>Favorito</th><th>Atención</th><th>Limpieza</th><th>Precios</th><th>Frescura</th><th>Recomienda</th></tr></thead>
        <tbody>
        <?php foreach ($respuestas as $r): ?>
            <tr>
                <td><?= e(fecha_mx($r['fecha'])) ?></td>
                <td><?= e($r['nombre']) ?></td>
                <td><?= e($r['embutido_favorito']) ?></td>
                <td><?= e($r['atencion']) ?></td>
                <td><?= e($r['limpieza']) ?></td>
                <td><?= e($r['precios']) ?></td>
                <td><?= e($r['frescura']) ?></td>
                <td><?= e($r['recomendacion']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
<?php else: ?>
    <p>Todavía nadie contesta la encuesta. Cuando un cliente la envíe, los resultados aparecen aquí.</p>
<?php endif; ?>
<?php admin_pie(); ?>
