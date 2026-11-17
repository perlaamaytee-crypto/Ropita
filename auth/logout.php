<?php
// auth/logout.php: cerrar sesión. Solo por POST con token (así otra página no puede cerrarte la sesión).

require __DIR__ . '/../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok($_POST['csrf'] ?? null)) {
    ir('/');
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
session_start();
flash('ok', 'Cerraste sesión. ¡Vuelve pronto!');
ir('/');
