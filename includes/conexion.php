<?php
// includes/conexion.php
// Base de datos (PDO), sesión de PHP y funciones de ayuda usadas en todo el sitio.
// En Render lee DB_HOST, DB_PORT, DB_USER, DB_PASS y DB_NAME. Ninguna contraseña va en el código.

declare(strict_types=1);
require_once __DIR__ . '/config.php';
date_default_timezone_set('America/Mexico_City');

function pagina_error(int $codigo, string $titulo, string $mensaje): never
{
    http_response_code($codigo);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . htmlspecialchars($titulo) . '</title>'
       . '<link rel="stylesheet" href="/assets/estilos.css"></head><body class="pagina-error"><main class="caja">'
       . '<h1>' . htmlspecialchars($titulo) . '</h1><p>' . htmlspecialchars($mensaje) . '</p>'
       . '<p><a class="boton" href="/">Volver al inicio</a></p></main></body></html>';
    exit;
}

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$name = getenv('DB_NAME') ?: 'gran_salchichon';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Sin detalles técnicos en pantalla: podrían revelar el servidor.
    error_log('Conexión a la base de datos falló: ' . $e->getMessage());
    pagina_error(503, 'Estamos reponiendo el refrigerador', 'No pudimos conectar con la base de datos. Intenta de nuevo en unos minutos.');
}

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
session_start();

// ---------- Ayudas generales ----------
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function ir(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// Solo se permite volver a rutas del mismo sitio (evita redirecciones a páginas ajenas).
function ruta_segura(?string $ruta): string
{
    if ($ruta !== null && preg_match('#^/[A-Za-z0-9_\-./?=&\#%]*$#', $ruta) && !str_starts_with($ruta, '//')) {
        return $ruta;
    }
    return '/';
}

function dinero(float $n): string
{
    return '$' . number_format($n, 2);
}

function fecha_mx(?string $utc, string $formato = 'd/m/Y H:i'): string
{
    if (!$utc) {
        return '';
    }
    $f = new DateTime($utc, new DateTimeZone('UTC'));
    $f->setTimezone(new DateTimeZone('America/Mexico_City'));
    return $f->format($formato);
}

function distancia_km(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $r = 6371.0;
    $a = sin(deg2rad($lat2 - $lat1) / 2) ** 2
       + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin(deg2rad($lng2 - $lng1) / 2) ** 2;
    return 2 * $r * asin(min(1.0, sqrt($a)));
}

function hora_txt(int $h): string
{
    return $h . ':00';
}

// ¿Está abierta la sucursal ahora mismo? Devuelve [abierto, texto].
function estado_tienda(): array
{
    $ahora = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    $dia   = (int) $ahora->format('N');
    $hora  = (int) $ahora->format('G') + ((int) $ahora->format('i')) / 60;
    [$abre, $cierra] = HORARIO[$dia];
    if ($hora >= $abre && $hora < $cierra) {
        return [true, 'Abierto ahora, cerramos a las ' . hora_txt($cierra)];
    }
    if ($hora < $abre) {
        return [false, 'Cerrado ahora, abrimos hoy a las ' . hora_txt($abre)];
    }
    $siguiente = $dia % 7 + 1;
    return [false, 'Cerrado ahora, abrimos mañana a las ' . hora_txt(HORARIO[$siguiente][0])];
}

// ---------- Sesión y roles: invitado / cliente / admin ----------
function usuario(): ?array
{
    if (!isset($_SESSION['uid'])) {
        return null;
    }
    return ['id' => (int) $_SESSION['uid'], 'nombre' => (string) $_SESSION['nombre'], 'rol' => (string) $_SESSION['rol']];
}

function rol_actual(): string
{
    return $_SESSION['rol'] ?? 'invitado';
}

function es_cliente(): bool
{
    return in_array(rol_actual(), ['cliente', 'admin'], true);
}

function es_admin(): bool
{
    return rol_actual() === 'admin';
}

function iniciar_sesion(array $u): void
{
    session_regenerate_id(true);
    $_SESSION['uid']    = (int) $u['id'];
    $_SESSION['nombre'] = $u['nombre'];
    $_SESSION['rol']    = $u['rol'];
}

function requerir_login(): void
{
    if (!isset($_SESSION['uid'])) {
        flash('aviso', 'Inicia sesión para continuar.');
        ir('/auth/login.php?volver=' . urlencode(ruta_segura($_SERVER['REQUEST_URI'] ?? '/')));
    }
}

// El rol se vuelve a leer de la base de datos: si le quitan el permiso a un admin, se nota de inmediato.
function requerir_admin(PDO $pdo): void
{
    requerir_login();
    $st = $pdo->prepare('SELECT rol FROM usuarios WHERE id = ?');
    $st->execute([$_SESSION['uid']]);
    $rol = $st->fetchColumn();
    if ($rol !== 'admin') {
        pagina_error(403, 'Solo para el personal', 'Esta sección es solo para administradores de la sucursal.');
    }
    $_SESSION['rol'] = 'admin';
}

// ---------- Mensajes de una sola vez ----------
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = [$tipo, $mensaje];
}

function flash_leer(): array
{
    $m = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $m;
}

// ---------- CSRF: evita que otra página mande formularios en nombre del usuario ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(?string $token): bool
{
    return is_string($token) && $token !== '' && hash_equals(csrf_token(), $token);
}

function csrf_requerir_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok($_POST['csrf'] ?? null)) {
        pagina_error(400, 'Solicitud no válida', 'El formulario caducó. Regresa, recarga la página e inténtalo otra vez.');
    }
}

// ---------- Ayudas para las APIs (fetch): responden JSON ----------
function api_responder(int $codigo, array $datos = []): never
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos);
    exit;
}

// El token de CSRF viaja en el encabezado X-CSRF-Token.
function api_requerir_cliente(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        api_responder(405, ['error' => 'Usa POST.']);
    }
    if (!isset($_SESSION['uid'])) {
        api_responder(401, ['error' => 'Inicia sesión para usar esta función.']);
    }
    if (!csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        api_responder(403, ['error' => 'Sesión caducada. Recarga la página.']);
    }
}
