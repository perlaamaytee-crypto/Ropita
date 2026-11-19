<?php
// Registro de cliente. Las cuentas nuevas nunca reciben permisos de administración.
require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/plantilla.php';

if (isset($_SESSION['uid'])) {
    ir('/');
}

$errores = [];
$v = ['nombre' => '', 'correo' => '', 'embutido_favorito' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_requerir_post();
    $v['nombre'] = trim((string) ($_POST['nombre'] ?? ''));
    $v['correo'] = trim((string) ($_POST['correo'] ?? ''));
    $v['embutido_favorito'] = (string) ($_POST['embutido_favorito'] ?? '');
    $clave = (string) ($_POST['clave'] ?? '');
    $confirma = (string) ($_POST['confirma'] ?? '');

    if (mb_strlen($v['nombre']) < 2 || mb_strlen($v['nombre']) > 60) $errores[] = 'El nombre debe tener entre 2 y 60 caracteres.';
    if (!filter_var($v['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($v['correo']) > 150) $errores[] = 'Escribe un correo válido.';
    if (!in_array($v['embutido_favorito'], EMBUTIDOS, true)) $errores[] = 'Elige una categoría de la lista.';
    if (mb_strlen($clave) < 8) $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    if ($clave !== $confirma) $errores[] = 'Las contraseñas no coinciden.';

    if (!$errores) {
        try {
            $st = $pdo->prepare("INSERT INTO usuarios (nombre, correo, embutido_favorito, password_hash, rol) VALUES (?, ?, ?, ?, 'cliente')");
            $st->execute([$v['nombre'], $v['correo'], $v['embutido_favorito'], password_hash($clave, PASSWORD_DEFAULT)]);
            iniciar_sesion(['id' => (int) $pdo->lastInsertId(), 'nombre' => $v['nombre'], 'rol' => 'cliente']);
            flash('ok', '¡Tu cuenta Ropita ya está lista!');
            ir('/');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') $errores[] = 'Ese correo ya está registrado. Prueba iniciar sesión.';
            else { error_log('Registro falló: ' . $ex->getMessage()); $errores[] = 'No pudimos crear la cuenta. Inténtalo de nuevo.'; }
        }
    }
}

cabecera('Crear cuenta | Ropita', ['clase' => 'pagina-cuenta']);
?>
<main id="contenido" class="cuenta"><div class="caja angosta">
    <h1>Únete a Ropita</h1><p>Crea tu cuenta para compartir tu opinión y ayudarnos a elegir las próximas novedades.</p>
    <?php foreach ($errores as $m): ?><p class="flash flash-error" role="alert"><?= e($m) ?></p><?php endforeach; ?>
    <form class="formulario" method="post" action="/auth/registro.php" id="form-registro">
        <?= csrf_campo() ?>
        <label for="nombre">Nombre</label><input type="text" id="nombre" name="nombre" value="<?= e($v['nombre']) ?>" required maxlength="60" autocomplete="name">
        <label for="correo">Correo</label><input type="email" id="correo" name="correo" value="<?= e($v['correo']) ?>" required maxlength="150" autocomplete="email">
        <label for="embutido_favorito">Categoría favorita</label><select id="embutido_favorito" name="embutido_favorito" required><option value="">Elige una opción</option><?php foreach (EMBUTIDOS as $op): ?><option <?= $v['embutido_favorito'] === $op ? 'selected' : '' ?>><?= e($op) ?></option><?php endforeach; ?></select>
        <label for="clave">Contraseña (mínimo 8 caracteres)</label><input type="password" id="clave" name="clave" required minlength="8" autocomplete="new-password">
        <label for="confirma">Repite la contraseña</label><input type="password" id="confirma" name="confirma" required minlength="8" autocomplete="new-password">
        <p class="mensaje error" id="msg-clave" hidden>Las contraseñas no coinciden.</p><button class="boton" type="submit">Crear mi cuenta</button>
    </form><p>¿Ya tienes cuenta? <a href="/auth/login.php">Inicia sesión</a>.</p>
</div></main>
<?php pie(); ?>
