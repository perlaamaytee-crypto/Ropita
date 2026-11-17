<?php
// index.php: la página principal de la sucursal (una sola página larga con secciones).
// Invitado: solo ve. Cliente: además opina, usa el chat, sube fotos y calcula su distancia.
// Administrador: todo lo anterior y, además, el panel /admin/.

require __DIR__ . '/includes/conexion.php';
require __DIR__ . '/includes/plantilla.php';

[$abierto, $estado_texto] = estado_tienda();
$u = usuario();

// ---- Productos, agrupados por categoría ----
$por_categoria = [];
foreach (CATEGORIAS as $c) {
    $por_categoria[$c] = [];
}
foreach ($pdo->query('SELECT * FROM productos ORDER BY nombre') as $p) {
    $por_categoria[$p['categoria']][] = $p;
}

// ---- Fotos de clientes (si el archivo ya no existe en el servidor, no se muestra) ----
$fotos = [];
foreach ($pdo->query('SELECT nombre, archivo FROM fotos ORDER BY id DESC LIMIT 24') as $f) {
    if (is_file(__DIR__ . '/uploads/' . $f['archivo'])) {
        $fotos[] = $f;
    }
}

// ---- Resultados públicos de la encuesta (solo totales, sin datos personales) ----
$total_opiniones = (int) $pdo->query('SELECT COUNT(*) FROM respuestas')->fetchColumn();
$recomendacion = [];
foreach (ENCUESTA['recomendacion']['opciones'] as $op) {
    $recomendacion[$op] = 0;
}
foreach ($pdo->query('SELECT recomendacion, COUNT(*) AS total FROM respuestas GROUP BY recomendacion') as $r) {
    if (isset($recomendacion[$r['recomendacion']])) {
        $recomendacion[$r['recomendacion']] = (int) $r['total'];
    }
}

$chat_url = getenv('CHAT_URL') ?: '';
$mapa_src = 'https://www.google.com/maps?q=' . SUCURSAL['lat'] . ',' . SUCURSAL['lng'] . '&z=16&output=embed';
$ruta_url = 'https://www.google.com/maps/dir/?api=1&destination=' . SUCURSAL['lat'] . ',' . SUCURSAL['lng'];

cabecera(SUCURSAL['nombre'] . ': ' . SUCURSAL['lema'], ['clase' => 'inicio']);
?>
<div class="toldo" aria-hidden="true"></div>

<main id="contenido">

<section class="hero" id="inicio">
    <div class="caja hero-in">
        <div class="hero-letrero">
            <p class="hero-sucursal"><?= e(SUCURSAL['sucursal']) ?></p>
            <h1>Embutidos<br>El Gran Salchichón</h1>
            <p class="hero-lema"><?= e(SUCURSAL['lema']) ?></p>
            <p class="hero-texto">Jamón rebanado al momento, salchichas de pavo, chorizo de la casa y queso de puerco. Entra, pide tu probadita y llévate lo que se te antoje.</p>
            <p class="hero-botones">
                <a class="boton mostaza" href="#productos">Ver productos</a>
                <a class="boton contorno" href="#sucursal">Cómo llegar</a>
            </p>
        </div>
        <div class="hero-estado <?= $abierto ? 'abierto' : 'cerrado' ?>">
            <p class="estado-titulo"><?= $abierto ? 'Abierto' : 'Cerrado' ?></p>
            <p><?= e($estado_texto) ?></p>
            <p class="estado-tel">Pedidos al <?= e(SUCURSAL['telefono']) ?></p>
        </div>
    </div>
</section>

<section class="seccion fondo-hueso" id="nosotros">
    <div class="caja dos-col">
        <div>
            <h2>Una salchichonería de barrio, con orgullo</h2>
            <p>Abrimos la sucursal <?= e(SUCURSAL['sucursal']) ?> para que en Cuautitlán Izcalli nadie se quede sin su rebanada de jamón. Aquí el embutido llega fresco cada mañana, se corta frente a ti y se pesa sin trampa.</p>
            <p>Somos de esas tiendas donde el que atiende se sabe tu pedido de memoria y todavía te regala una probadita. Si es tu primera vez, pregunta por el chorizo de la casa.</p>
            <ul class="promesas">
                <li><strong>Frescura diaria.</strong> El refrigerador se surte cada mañana y lo que no se vende ese día se rebaja.</li>
                <li><strong>Rebanado al momento.</strong> Tú dices el grosor: delgadito, normal o torta de tres pisos.</li>
                <li><strong>Precios de barrio.</strong> Lo que ves en el pizarrón es lo que pagas.</li>
            </ul>
        </div>
        <aside class="ticket" aria-label="Datos de la sucursal">
            <p class="ticket-titulo"><?= e(SUCURSAL['corto']) ?></p>
            <p class="ticket-sub"><?= e(SUCURSAL['sucursal']) ?></p>
            <hr>
            <dl>
                <?php foreach (HORARIO_TEXTO as $dias => $horas): ?>
                    <div><dt><?= e($dias) ?></dt><dd><?= e($horas) ?></dd></div>
                <?php endforeach; ?>
                <div><dt>Teléfono</dt><dd><?= e(SUCURSAL['telefono']) ?></dd></div>
                <div><dt>Dirección</dt><dd><?= e(SUCURSAL['direccion']) ?></dd></div>
                <div><dt>Pago</dt><dd>Efectivo y tarjeta</dd></div>
            </dl>
            <hr>
            <p class="ticket-fin">Gracias por su compra</p>
        </aside>
    </div>
</section>

<section class="seccion fondo-pizarron" id="productos">
    <div class="caja">
        <div class="pizarron">
            <div class="pizarron-cabeza">
                <h2>Pizarrón de hoy</h2>
                <p>Precios en pesos mexicanos. Preguntas por tu corte y te lo rebanamos.</p>
                <?php if (es_admin()): ?>
                    <p><a class="boton mostaza chico" href="/admin/productos.php">Editar inventario</a></p>
                <?php endif; ?>
            </div>
            <div class="pizarron-cols">
                <?php foreach ($por_categoria as $categoria => $lista): ?>
                    <?php if (!$lista) { continue; } ?>
                    <section class="pizarron-bloque">
                        <h3><?= e($categoria) ?></h3>
                        <ul>
                            <?php foreach ($lista as $p): ?>
                                <?php $agotado = (int) $p['stock'] <= 0; $pocas = !$agotado && (int) $p['stock'] <= 5; ?>
                                <li class="<?= $agotado ? 'agotado' : '' ?>">
                                    <span class="p-emoji" aria-hidden="true"><?= e($p['emoji']) ?></span>
                                    <span class="p-texto">
                                        <span class="p-nombre"><?= e($p['nombre']) ?></span>
                                        <span class="p-desc"><?= e($p['descripcion']) ?></span>
                                        <?php if ($agotado): ?><span class="p-aviso">Agotado por hoy</span>
                                        <?php elseif ($pocas): ?><span class="p-aviso">Últimas piezas</span><?php endif; ?>
                                    </span>
                                    <span class="p-puntos" aria-hidden="true"></span>
                                    <span class="p-precio"><?= e(dinero((float) $p['precio'])) ?> <small>/ <?= e($p['unidad']) ?></small></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="seccion fondo-jamon" id="sucursal">
    <div class="caja">
        <h2>La sucursal</h2>
        <div class="sucursal-grid">
            <div>
                <p class="direccion"><?= e(SUCURSAL['direccion']) ?><br><?= e(SUCURSAL['ciudad']) ?></p>
                <p class="estado-linea <?= $abierto ? 'abierto' : 'cerrado' ?>"><?= e($estado_texto) ?></p>
                <p><a class="boton" href="<?= e($ruta_url) ?>" target="_blank" rel="noopener">Abrir ruta en Google Maps</a></p>

                <div class="distancia" id="distancia"
                     data-lat="<?= e((string) SUCURSAL['lat']) ?>" data-lng="<?= e((string) SUCURSAL['lng']) ?>">
                    <h3>¿Qué tan lejos estás?</h3>
                    <?php if ($u): ?>
                        <p>Comparte tu ubicación y te decimos cuántos kilómetros hay hasta la sucursal. El navegador te va a pedir permiso.</p>
                        <button class="boton" type="button" id="btn-ubicacion">Calcular mi distancia</button>
                        <p class="mensaje" id="msg-ubicacion" hidden></p>
                        <p id="ruta-desde" hidden><a href="#" target="_blank" rel="noopener">Ruta desde donde estás</a></p>
                    <?php else: ?>
                        <p>Inicia sesión como cliente para calcular tu distancia a la sucursal.</p>
                        <p><a class="boton chico" href="/auth/login.php?volver=%2F%23sucursal">Entrar</a></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="marco-mapa">
                <iframe title="Mapa de la sucursal" src="<?= e($mapa_src) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</section>

<section class="seccion fondo-hueso" id="opinion">
    <div class="caja dos-col">
        <div>
            <h2>Cuéntanos qué tal te fue</h2>
            <p>Son seis preguntas y toman menos de un minuto. Lo que contestes nos ayuda a mejorar el servicio.</p>

            <?php if (isset($_GET['gracias']) && $u): ?>
                <div class="regalo" id="regalo">
                    <p><strong>¡Gracias por tu opinión!</strong> Tienes un regalo esperando.</p>
                    <button class="boton mostaza" type="button" id="btn-regalo">Abrir mi regalo</button>
                    <p class="regalo-premio" id="regalo-premio" hidden>
                        <span class="regalo-emoji" id="regalo-emoji"></span>
                        <span id="regalo-texto"></span>
                    </p>
                </div>
                <script type="application/json" id="regalos-datos"><?= json_encode(REGALOS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
            <?php endif; ?>

            <?php if ($u): ?>
                <form class="formulario" method="post" action="/acciones/encuesta.php">
                    <?= csrf_campo() ?>
                    <?php foreach (ENCUESTA as $campo => $q): ?>
                        <label for="q-<?= e($campo) ?>"><?= e($q['pregunta']) ?></label>
                        <select id="q-<?= e($campo) ?>" name="<?= e($campo) ?>" required>
                            <option value="">Elige una opción</option>
                            <?php foreach ($q['opciones'] as $op): ?>
                                <option><?= e($op) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endforeach; ?>
                    <button class="boton" type="submit">Enviar mi opinión</button>
                </form>
            <?php else: ?>
                <div class="bloqueo">
                    <p>La encuesta es solo para clientes registrados. Crear tu cuenta es gratis y toma un minuto.</p>
                    <p><a class="boton" href="/auth/registro.php">Crear cuenta</a> <a class="boton contorno-osc" href="/auth/login.php?volver=%2F%23opinion">Ya tengo cuenta</a></p>
                </div>
            <?php endif; ?>
        </div>

        <aside class="resultados" aria-label="Resultados de la encuesta">
            <h3>¿Nos recomendarían?</h3>
            <p><?= $total_opiniones ?> <?= $total_opiniones === 1 ? 'cliente ha opinado' : 'clientes han opinado' ?>.</p>
            <?php if ($total_opiniones > 0): ?>
                <ul class="barras">
                    <?php foreach ($recomendacion as $op => $n): ?>
                        <?php $pct = round($n * 100 / $total_opiniones); ?>
                        <li>
                            <span class="barra-texto"><?= e($op) ?></span>
                            <span class="barra-pista"><span class="barra-valor" style="width: <?= (int) $pct ?>%"></span></span>
                            <span class="barra-num"><?= (int) $pct ?>%</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Todavía no hay opiniones. Anímate a ser el primero.</p>
            <?php endif; ?>
        </aside>
    </div>
</section>

<section class="seccion fondo-jamon" id="video">
    <div class="caja dos-col centrado">
        <div>
            <h2>Conoce la tienda en un minuto</h2>
            <p>Un recorrido rápido por el mostrador, el refrigerador y el pizarrón de precios. Si después de verlo te dio hambre, es normal.</p>
            <p><a class="boton" href="#productos">Ver lo que tenemos hoy</a></p>
        </div>
        <div class="video-caja">
            <iframe src="https://www.youtube.com/embed/<?= e(SUCURSAL['video_id']) ?>" title="Video de <?= e(SUCURSAL['nombre']) ?>" allowfullscreen loading="lazy"></iframe>
        </div>
    </div>
</section>

<section class="seccion fondo-hueso" id="chat">
    <div class="caja">
        <h2>Chat de atención a clientes</h2>
        <?php if (!$u): ?>
            <div class="bloqueo">
                <p>El chat es para clientes registrados. Entra con tu cuenta para preguntar por pedidos, apartados o el corte que quieras.</p>
                <p><a class="boton" href="/auth/login.php?volver=%2F%23chat">Entrar</a></p>
            </div>
        <?php elseif ($chat_url === ''): ?>
            <p class="flash flash-aviso">El chat todavía no está conectado a este sitio.</p>
        <?php else: ?>
            <p>Escribe tu mensaje abajo. Si el chat tarda en abrir, espera unos segundos: despierta después de un rato sin uso.</p>
            <div class="chat-caja">
                <iframe title="Chat de la sucursal" src="<?= e($chat_url) ?>?usuario=<?= urlencode($u['nombre']) ?>" loading="lazy"></iframe>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="seccion fondo-jamon" id="clientes">
    <div class="caja">
        <h2>Muro de clientes</h2>
        <p>Las fotos de quienes se tomaron una con su embutido favorito.</p>

        <?php if ($u): ?>
            <div class="subir" id="subir-foto">
                <h3>Sube tu foto</h3>
                <div class="subir-acciones">
                    <button class="boton chico" type="button" id="btn-camara">Activar cámara</button>
                    <button class="boton chico" type="button" id="btn-tomar" disabled>Tomar foto</button>
                    <label class="boton chico contorno-osc" for="archivo">Elegir archivo</label>
                    <input type="file" id="archivo" accept="image/*" hidden>
                </div>
                <video id="video-camara" autoplay playsinline muted hidden></video>
                <img id="vista-previa" alt="Vista previa de tu foto" hidden>
                <p><button class="boton" type="button" id="btn-subir" disabled>Subir foto</button></p>
                <p class="mensaje" id="msg-foto" hidden></p>
            </div>
        <?php else: ?>
            <div class="bloqueo">
                <p>Para subir tu foto necesitas una cuenta de cliente.</p>
                <p><a class="boton" href="/auth/registro.php">Crear cuenta</a> <a class="boton contorno-osc" href="/auth/login.php?volver=%2F%23clientes">Entrar</a></p>
            </div>
        <?php endif; ?>

        <?php if ($fotos): ?>
            <ul class="galeria">
                <?php foreach ($fotos as $f): ?>
                    <li>
                        <img src="/uploads/<?= e($f['archivo']) ?>" alt="Foto de <?= e($f['nombre']) ?>" loading="lazy">
                        <span><?= e($f['nombre']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="vacio">Todavía no hay fotos en el muro.</p>
        <?php endif; ?>
    </div>
</section>

</main>
<?php pie(); ?>
