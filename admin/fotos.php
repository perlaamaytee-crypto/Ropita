<?php
// admin/fotos.php: moderación del muro de clientes (el administrador puede borrar fotos).

require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/admin.php';
requerir_admin($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_requerir_post();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        $st = $pdo->prepare('SELECT archivo FROM fotos WHERE id = ?');
        $st->execute([$id]);
        $archivo = $st->fetchColumn();
        if ($archivo) {
            // basename: aunque el registro tuviera algo raro, nunca se sale de la carpeta uploads/.
            $ruta = __DIR__ . '/../uploads/' . basename($archivo);
            if (is_file($ruta)) {
                unlink($ruta);
            }
            $pdo->prepare('DELETE FROM fotos WHERE id = ?')->execute([$id]);
            flash('ok', 'Foto eliminada.');
        }
    }
    ir('/admin/fotos.php');
}

$fotos = $pdo->query('SELECT id, nombre, archivo, bytes, creado_en FROM fotos ORDER BY id DESC LIMIT 100')->fetchAll();

admin_cabecera('Fotos', 'fotos');
?>
<h1>Fotos del muro de clientes</h1>
<?php if (!$fotos): ?>
    <p>Todavía no hay fotos.</p>
<?php else: ?>
    <ul class="galeria admin-galeria">
        <?php foreach ($fotos as $f): ?>
            <?php $existe = is_file(__DIR__ . '/../uploads/' . basename($f['archivo'])); ?>
            <li>
                <?php if ($existe): ?>
                    <img src="/uploads/<?= e($f['archivo']) ?>" alt="Foto de <?= e($f['nombre']) ?>" loading="lazy">
                <?php else: ?>
                    <span class="sin-archivo">El archivo ya no está en el servidor</span>
                <?php endif; ?>
                <span><?= e($f['nombre']) ?><br><small><?= e(fecha_mx($f['creado_en'])) ?></small></span>
                <form method="post" onsubmit="return confirm('¿Eliminar esta foto?');">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                    <button class="boton chico peligro" type="submit">Eliminar</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php admin_pie(); ?>
