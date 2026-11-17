<?php
// api/foto_subir.php: RECIBE una foto (multipart/form-data, campo "foto"). Solo clientes con sesión.

require __DIR__ . '/../includes/conexion.php';
api_requerir_cliente();

$foto = $_FILES['foto'] ?? null;
if (!$foto || $foto['error'] !== UPLOAD_ERR_OK) {
    $demasiado = $foto && in_array($foto['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
    api_responder(400, ['error' => $demasiado ? 'La foto pesa más de 5 MB.' : 'No llegó ninguna foto.']);
}
if ($foto['size'] > 5 * 1024 * 1024) {
    api_responder(400, ['error' => 'La foto pesa más de 5 MB.']);
}

// La extensión y el tipo que manda el navegador se pueden falsear: finfo revisa el CONTENIDO real.
$tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
$tipo  = (new finfo(FILEINFO_MIME_TYPE))->file($foto['tmp_name']);
if (!isset($tipos[$tipo])) {
    api_responder(400, ['error' => 'Eso no es una imagen.']);
}

// El nombre del archivo lo pone el SERVIDOR: id del usuario + fecha + caracteres al azar.
$u = usuario();
$archivo = 'u' . $u['id'] . '_' . date('Ymd-His') . '_' . bin2hex(random_bytes(3)) . '.' . $tipos[$tipo];
$ruta = __DIR__ . '/../uploads/' . $archivo;

if (!move_uploaded_file($foto['tmp_name'], $ruta)) {
    api_responder(500, ['error' => 'No se pudo guardar la foto en el servidor.']);
}

try {
    $pdo->prepare('INSERT INTO fotos (usuario_id, nombre, archivo, tipo, bytes) VALUES (?, ?, ?, ?, ?)')
        ->execute([$u['id'], $u['nombre'], $archivo, $tipo, $foto['size']]);
} catch (PDOException $ex) {
    unlink($ruta); // sin registro no se queda el archivo huérfano
    error_log('Registro de foto falló: ' . $ex->getMessage());
    api_responder(500, ['error' => 'No se pudo registrar la foto.']);
}

api_responder(201, ['archivo' => $archivo]);
