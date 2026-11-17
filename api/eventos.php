<?php
// api/eventos.php: RECIBE eventos de analítica (vista, clic, tiempo). POST con cuerpo JSON.
// Es público a propósito: también se mide a los invitados. Por eso se valida todo.

require __DIR__ . '/../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// sendBeacon manda el JSON como texto plano, así que se lee el cuerpo crudo.
$datos = json_decode(file_get_contents('php://input'), true);

$tipo      = is_array($datos) ? ($datos['tipo'] ?? '') : '';
$visitante = is_array($datos) ? ($datos['visitante_id'] ?? '') : '';
$sesion    = is_array($datos) ? ($datos['sesion_id'] ?? '') : '';
$uuid      = '/^[0-9a-f-]{36}$/';

if (!in_array($tipo, ['vista', 'clic', 'tiempo'], true) || !is_string($visitante) || !is_string($sesion)
    || !preg_match($uuid, $visitante) || !preg_match($uuid, $sesion)) {
    http_response_code(400);
    exit;
}

$pagina   = mb_substr((string) ($datos['pagina'] ?? ''), 0, 200);
$elemento = $tipo === 'clic' ? mb_substr((string) ($datos['elemento'] ?? ''), 0, 100) : null;
$segundos = $tipo === 'tiempo' ? max(0, min(3600, (int) ($datos['segundos'] ?? 0))) : null;

// ¿Quién es? Lo decide el SERVIDOR con la sesión, no lo que diga el navegador.
$usuarioId = $_SESSION['uid'] ?? null;

$pdo->prepare('INSERT INTO eventos (visitante_id, sesion_id, usuario_id, tipo, pagina, elemento, segundos) VALUES (?, ?, ?, ?, ?, ?, ?)')
    ->execute([$visitante, $sesion, $usuarioId, $tipo, $pagina, $elemento, $segundos]);

http_response_code(204);
