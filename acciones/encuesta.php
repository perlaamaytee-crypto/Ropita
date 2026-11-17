<?php
// acciones/encuesta.php: guarda las respuestas de la encuesta (solo clientes y administrador).

require __DIR__ . '/../includes/conexion.php';

requerir_login();
csrf_requerir_post();

$datos = [];
foreach (ENCUESTA as $campo => $q) {
    $valor = (string) ($_POST[$campo] ?? '');
    // Nunca confiar en el navegador: solo se aceptan las opciones que existen en config.php.
    if (!in_array($valor, $q['opciones'], true)) {
        flash('error', 'Hay una respuesta que no es válida. Revisa el formulario e inténtalo de nuevo.');
        ir('/#opinion');
    }
    $datos[$campo] = $valor;
}

$u = usuario();
$st = $pdo->prepare('INSERT INTO respuestas (usuario_id, nombre, embutido_favorito, atencion, limpieza, precios, frescura, recomendacion)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$st->execute([$u['id'], $u['nombre'], $datos['embutido_favorito'], $datos['atencion'], $datos['limpieza'],
              $datos['precios'], $datos['frescura'], $datos['recomendacion']]);

ir('/?gracias=1#opinion');
