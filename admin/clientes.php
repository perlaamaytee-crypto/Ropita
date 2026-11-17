<?php
// admin/clientes.php: lista de cuentas. Para volver administrador a alguien se usa SQL (ver README).

require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/admin.php';
requerir_admin($pdo);

$usuarios = $pdo->query('SELECT id, nombre, correo, embutido_favorito, rol, creado_en FROM usuarios ORDER BY id DESC')->fetchAll();

admin_cabecera('Clientes', 'clientes');
?>
<h1>Clientes y cuentas</h1>
<div class="tabla"><table>
    <thead><tr><th>Nombre</th><th>Correo</th><th>Embutido favorito</th><th>Tipo de cuenta</th><th>Se registró</th></tr></thead>
    <tbody>
    <?php foreach ($usuarios as $x): ?>
        <tr>
            <td><?= e($x['nombre']) ?></td>
            <td><?= e($x['correo']) ?></td>
            <td><?= e($x['embutido_favorito']) ?></td>
            <td><?= $x['rol'] === 'admin' ? 'Administrador' : 'Cliente' ?></td>
            <td><?= e(fecha_mx($x['creado_en'])) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php admin_pie(); ?>
