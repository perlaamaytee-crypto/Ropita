<?php
// admin/productos.php: inventario (CRUD). Aquí se lista y se elimina; crear y editar está en producto.php.

require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/admin.php';
requerir_admin($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_requerir_post();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        $st = $pdo->prepare('DELETE FROM productos WHERE id_producto = ?');
        $st->execute([$id]);
        flash('ok', $st->rowCount() ? 'Producto eliminado.' : 'Ese producto ya no existe.');
    }
    ir('/admin/productos.php');
}

$productos = $pdo->query('SELECT * FROM productos ORDER BY categoria, nombre')->fetchAll();

admin_cabecera('Inventario', 'productos');
?>
<div class="admin-titulo">
    <h1>Inventario</h1>
    <a class="boton" href="/admin/producto.php">Agregar producto</a>
</div>
<div class="tabla"><table>
    <thead><tr><th></th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php if (!$productos): ?>
        <tr><td colspan="6">Todavía no hay productos. Agrega el primero.</td></tr>
    <?php endif; ?>
    <?php foreach ($productos as $p): ?>
        <tr>
            <td aria-hidden="true"><?= e($p['emoji']) ?></td>
            <td><strong><?= e($p['nombre']) ?></strong><br><small><?= e($p['descripcion']) ?></small></td>
            <td><?= e($p['categoria']) ?></td>
            <td><?= e(dinero((float) $p['precio'])) ?> / <?= e($p['unidad']) ?></td>
            <td><?= (int) $p['stock'] === 0 ? 'Agotado' : (int) $p['stock'] ?></td>
            <td class="acciones">
                <a class="boton chico contorno-osc" href="/admin/producto.php?id=<?= (int) $p['id_producto'] ?>">Editar</a>
                <form method="post" onsubmit="return confirm('¿Eliminar <?= e(addslashes($p['nombre'])) ?>?');">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int) $p['id_producto'] ?>">
                    <button class="boton chico peligro" type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php admin_pie(); ?>
