<?php
// auth/login.php: iniciar sesión (clientes y administrador usan el mismo formulario).

require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/plantilla.php';

$volver = ruta_segura($_GET['volver'] ?? $_POST['volver'] ?? '/');
if (isset($_SESSION['uid'])) {
    ir($volver);
}

$error = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_requerir_post();
    $correo = trim((string) ($_POST['correo'] ?? ''));
    $clave  = (string) ($_POST['clave'] ?? '');

    // Freno sencillo contra adivinar contraseñas: 5 intentos fallidos = 5 minutos de espera.
    if (($_SESSION['bloqueo_hasta'] ?? 0) > time()) {
        $error = 'Demasiados intentos. Espera unos minutos e inténtalo de nuevo.';
    } else {
        $st = $pdo->prepare('SELECT id, nombre, rol, password_hash FROM usuarios WHERE correo = ?');
        $st->execute([$correo]);
        $fila = $st->fetch();

        // Siempre se compara contra un hash (aunque el correo no exista) para tardar lo mismo.
        $hash = $fila['password_hash'] ?? password_hash('relleno-para-igualar-tiempo', PASSWORD_DEFAULT);
        if ($fila && password_verify($clave, $hash)) {
            unset($_SESSION['intentos'], $_SESSION['bloqueo_hasta']);
            iniciar_sesion($fila);
            flash('ok', 'Bienvenido, ' . $fila['nombre'] . '.');
            ir($volver);
        }
        $_SESSION['intentos'] = ($_SESSION['intentos'] ?? 0) + 1;
        if ($_SESSION['intentos'] >= 5) {
            $_SESSION['bloqueo_hasta'] = time() + 300;
            $_SESSION['intentos'] = 0;
        }
        $error = 'Correo o contraseña incorrectos.';
    }
}

cabecera('Entrar | ' . SUCURSAL['corto'], ['clase' => 'pagina-cuenta']);
?>
<main id="contenido" class="cuenta">
    <div class="caja angosta">
        <h1>Entrar</h1>
        <p>Con tu cuenta puedes opinar, usar el chat y subir tu foto.</p>
        <?php if ($error): ?><p class="flash flash-error" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form class="formulario" method="post" action="/auth/login.php">
            <?= csrf_campo() ?>
            <input type="hidden" name="volver" value="<?= e($volver) ?>">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" value="<?= e($correo) ?>" required autocomplete="email">
            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" required autocomplete="current-password">
            <button class="boton" type="submit">Entrar</button>
        </form>
        <p>¿Todavía no tienes cuenta? <a href="/auth/registro.php">Crea una gratis</a>.</p>
    </div>
</main>
<?php pie(); ?>
