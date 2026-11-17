<?php
// admin/producto.php: formulario para AGREGAR (sin id) o EDITAR (con ?id=) un producto.

require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/admin.php';
requerir_admin($pdo);

$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$p = ['nombre' => '', 'descripcion' => '', 'precio' => '', 'unidad' => 'kg', 'stock' => '0', 'categoria' => CATEGORIAS[0], 'emoji' => '🍖'];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM productos WHERE id_producto = ?');
    $st->execute([$id]);
    $existente = $st->fetch();
    if (!$existente) {
        flash('error', 'Ese producto no existe.');
        ir('/admin/productos.php');
    }
    $p = array_merge($p, $existente);
}

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_requerir_post();
    $p['nombre']      = trim((string) ($_POST['nombre'] ?? ''));
    $p['descripcion'] = trim((string) ($_POST['descripcion'] ?? ''));
    $p['precio']      = str_replace(',', '.', trim((string) ($_POST['precio'] ?? '')));
    $p['unidad']      = (string) ($_POST['unidad'] ?? '');
    $p['stock']       = trim((string) ($_POST['stock'] ?? ''));
    $p['categoria']   = (string) ($_POST['categoria'] ?? '');
    $p['emoji']       = trim((string) ($_POST['emoji'] ?? ''));

    if ($p['nombre'] === '' || mb_strlen($p['nombre']) > 100) {
        $errores[] = 'El nombre es obligatorio y puede tener hasta 100 caracteres.';
    }
    if (mb_strlen($p['descripcion']) > 160) {
        $errores[] = 'La descripción puede tener hasta 160 caracteres.';
    }
    if (!is_numeric($p['precio']) || (float) $p['precio'] < 0 || (float) $p['precio'] > 99999.99) {
        $errores[] = 'El precio debe ser un número entre 0 y 99,999.99.';
    }
    if (!in_array($p['unidad'], UNIDADES, true)) {
        $errores[] = 'Elige una unidad de la lista.';
    }
    if (filter_var($p['stock'], FILTER_VALIDATE_INT) === false || (int) $p['stock'] < 0 || (int) $p['stock'] > 9999) {
        $errores[] = 'El stock debe ser un número entero entre 0 y 9999.';
    }
    if (!in_array($p['categoria'], CATEGORIAS, true)) {
        $errores[] = 'Elige una categoría de la lista.';
    }
    if ($p['emoji'] === '' || mb_strlen($p['emoji']) > 8) {
        $errores[] = 'Pon un emoji (por ejemplo 🥓) para que el producto se vea en el pizarrón.';
    }

    if (!$errores) {
        $valores = [$p['nombre'], $p['descripcion'], (float) $p['precio'], $p['unidad'], (int) $p['stock'], $p['categoria'], $p['emoji']];
        if ($id) {
            $pdo->prepare('UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, unidad = ?, stock = ?, categoria = ?, emoji = ? WHERE id_producto = ?')
                ->execute([...$valores, $id]);
            flash('ok', 'Producto actualizado.');
        } else {
            $pdo->prepare('INSERT INTO productos (nombre, descripcion, precio, unidad, stock, categoria, emoji) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute($valores);
            flash('ok', 'Producto agregado al pizarrón.');
        }
        ir('/admin/productos.php');
    }
}

admin_cabecera($id ? 'Editar producto' : 'Agregar producto', 'productos');
?>
<h1><?= $id ? 'Editar producto' : 'Agregar producto' ?></h1>
<?php foreach ($errores as $m): ?><p class="flash flash-error" role="alert"><?= e($m) ?></p><?php endforeach; ?>
<form class="formulario ancho-medio" method="post" action="/admin/producto.php<?= $id ? '?id=' . $id : '' ?>">
    <?= csrf_campo() ?>
    <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" value="<?= e($p['nombre']) ?>" required maxlength="100">

    <label for="descripcion">Descripción corta</label>
    <input type="text" id="descripcion" name="descripcion" value="<?= e($p['descripcion']) ?>" maxlength="160">

    <label for="categoria">Categoría</label>
    <select id="categoria" name="categoria">
        <?php foreach (CATEGORIAS as $c): ?><option <?= $p['categoria'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
    </select>

    <div class="fila-campos">
        <div>
            <label for="precio">Precio (MXN)</label>
            <input type="text" inputmode="decimal" id="precio" name="precio" value="<?= e((string) $p['precio']) ?>" required>
        </div>
        <div>
            <label for="unidad">Se vende por</label>
            <select id="unidad" name="unidad">
                <?php foreach (UNIDADES as $un): ?><option <?= $p['unidad'] === $un ? 'selected' : '' ?>><?= e($un) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" value="<?= e((string) $p['stock']) ?>" min="0" max="9999" required>
        </div>
        <div>
            <label for="emoji">Emoji</label>
            <input type="text" id="emoji" name="emoji" value="<?= e($p['emoji']) ?>" maxlength="8" required>
        </div>
    </div>

    <p>
        <button class="boton" type="submit"><?= $id ? 'Guardar cambios' : 'Agregar producto' ?></button>
        <a class="boton contorno-osc" href="/admin/productos.php">Cancelar</a>
    </p>
</form>
<?php admin_pie(); ?>
