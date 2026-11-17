<?php
// api/ubicacion.php: RECIBE la ubicación de un cliente. POST con cuerpo JSON.
// Solo clientes con sesión. La distancia a la sucursal la calcula el servidor.

require __DIR__ . '/../includes/conexion.php';
api_requerir_cliente();

$datos = json_decode(file_get_contents('php://input'), true);
$lat = is_array($datos) ? ($datos['latitud'] ?? null) : null;
$lng = is_array($datos) ? ($datos['longitud'] ?? null) : null;

// Nunca confiar en el cliente: cualquiera puede mandar un POST con otras coordenadas.
if (!is_numeric($lat) || !is_numeric($lng) || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    api_responder(400, ['error' => 'Coordenadas inválidas.']);
}
$lat = (float) $lat;
$lng = (float) $lng;
$precision = max(0, min(100000, (int) round((float) ($datos['precision'] ?? 0))));
$km = round(distancia_km($lat, $lng, SUCURSAL['lat'], SUCURSAL['lng']), 2);

$u = usuario();
$pdo->prepare('INSERT INTO ubicaciones (usuario_id, nombre, latitud, longitud, precision_m, distancia_km) VALUES (?, ?, ?, ?, ?, ?)')
    ->execute([$u['id'], $u['nombre'], $lat, $lng, $precision, $km]);

api_responder(201, ['distancia_km' => $km]);
