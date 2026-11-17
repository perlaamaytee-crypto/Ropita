<?php
// admin/metricas.php: panel de métricas. Esta página solo trae el HTML vacío;
// los datos los pide assets/panel.js al servicio api/metricas.php cada 5 segundos.

require __DIR__ . '/../includes/conexion.php';
require __DIR__ . '/../includes/admin.php';
requerir_admin($pdo);

admin_cabecera('Métricas', 'metricas');
?>
<h1>Métricas del sitio</h1>
<p class="nota">Se actualiza solo cada 5 segundos. <span id="estado"></span></p>

<section class="kpis" id="kpis"></section>

<h2>Actividad reciente</h2>
<div class="tabla"><table id="tabla-recientes"></table></div>

<h2>Páginas</h2>
<div class="tabla"><table id="tabla-paginas"></table></div>

<h2>¿Dónde hacen clic?</h2>
<div class="tabla"><table id="tabla-clics"></table></div>

<h2>Clientes</h2>
<div class="tabla"><table id="tabla-usuarios"></table></div>

<h2>Por embutido favorito</h2>
<div class="tabla"><table id="tabla-embutidos"></table></div>
<?php admin_pie(['/assets/panel.js']); ?>
