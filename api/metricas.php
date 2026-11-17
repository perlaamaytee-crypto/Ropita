<?php
// api/metricas.php: ENTREGA las métricas del sitio en JSON. Solo para el administrador.

require __DIR__ . '/../includes/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['uid'])) {
    api_responder(401, ['error' => 'Inicia sesión para ver las métricas.']);
}
$st = $pdo->prepare('SELECT rol FROM usuarios WHERE id = ?');
$st->execute([$_SESSION['uid']]);
if ($st->fetchColumn() !== 'admin') {
    api_responder(403, ['error' => 'Solo el administrador puede ver las métricas.']);
}

function consultar(PDO $pdo, string $sql): array
{
    return $pdo->query($sql)->fetchAll();
}

$resumen = consultar($pdo, "
    SELECT COUNT(DISTINCT visitante_id) AS visitantes,
           COUNT(DISTINCT sesion_id)    AS sesiones,
           COUNT(DISTINCT usuario_id)   AS usuarios,
           COALESCE(SUM(tipo = 'vista'), 0) AS vistas,
           COALESCE(SUM(tipo = 'clic'), 0)  AS clics
    FROM eventos")[0];

// Tiempo promedio por sesión: primero se suma por sesión y luego se promedia.
$resumen['tiempo_promedio'] = consultar($pdo, "
    SELECT COALESCE(ROUND(AVG(total)), 0) AS promedio
    FROM (SELECT SUM(segundos) AS total FROM eventos WHERE tipo = 'tiempo' GROUP BY sesion_id) AS t")[0]['promedio'];

$paginas = consultar($pdo, "
    SELECT pagina, SUM(tipo = 'vista') AS vistas, SUM(tipo = 'clic') AS clics, COALESCE(SUM(segundos), 0) AS segundos
    FROM eventos GROUP BY pagina ORDER BY vistas DESC LIMIT 10");

$clics = consultar($pdo, "
    SELECT elemento, pagina, COUNT(*) AS total
    FROM eventos WHERE tipo = 'clic'
    GROUP BY elemento, pagina ORDER BY total DESC LIMIT 10");

// Se une la analítica con los datos del registro de clientes.
$usuarios = consultar($pdo, "
    SELECT COALESCE(u.nombre, '(anónimos)') AS nombre,
           COALESCE(u.embutido_favorito, '—') AS embutido_favorito,
           COUNT(DISTINCT e.sesion_id) AS sesiones,
           SUM(e.tipo = 'vista') AS vistas,
           SUM(e.tipo = 'clic')  AS clics,
           COALESCE(SUM(e.segundos), 0) AS segundos,
           MAX(e.creado_en) AS ultima
    FROM eventos e
    LEFT JOIN usuarios u ON u.id = e.usuario_id
    GROUP BY e.usuario_id, u.nombre, u.embutido_favorito
    ORDER BY ultima DESC");
foreach ($usuarios as &$fila) {
    $fila['ultima'] = fecha_mx($fila['ultima'], 'd/m H:i');
}
unset($fila);

$embutidos = consultar($pdo, "
    SELECT u.embutido_favorito, COUNT(DISTINCT u.id) AS usuarios,
           SUM(e.tipo = 'clic') AS clics, COALESCE(SUM(e.segundos), 0) AS segundos
    FROM eventos e JOIN usuarios u ON u.id = e.usuario_id
    GROUP BY u.embutido_favorito ORDER BY segundos DESC");

$recientes = consultar($pdo, "
    SELECT e.creado_en, COALESCE(u.nombre, 'Anónimo') AS nombre, e.tipo, e.pagina, e.elemento, e.segundos
    FROM eventos e LEFT JOIN usuarios u ON u.id = e.usuario_id
    ORDER BY e.id DESC LIMIT 15");
foreach ($recientes as &$fila) {
    $fila['hora'] = fecha_mx($fila['creado_en'], 'H:i:s');
    unset($fila['creado_en']);
}
unset($fila);

echo json_encode(compact('resumen', 'paginas', 'clics', 'usuarios', 'embutidos', 'recientes'));
