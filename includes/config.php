<?php
// Configuración central de Ropita: boutique urbana de ropa y accesorios.

const SUCURSAL = [
    'nombre'    => 'Ropita',
    'corto'     => 'Ropita',
    'lema'      => 'Tu estilo, a tu manera.',
    'sucursal'  => 'Tienda en línea',
    'direccion' => 'Atención y pedidos en línea',
    'ciudad'    => 'Moda para todos los días',
    'telefono'  => 'Atención por correo',
    'correo'    => 'hola@ropita.example',
    'lat'       => 19.4326,
    'lng'       => -99.1332,
    'video_id'  => '',
];

const HORARIO = [1 => [9, 18], 2 => [9, 18], 3 => [9, 18], 4 => [9, 18], 5 => [9, 18], 6 => [10, 16], 7 => [10, 14]];
const HORARIO_TEXTO = ['Lunes a viernes' => '9:00 a 18:00', 'Sábado' => '10:00 a 16:00', 'Domingo' => '10:00 a 14:00'];
const CATEGORIAS = ['Novedades', 'Ropa', 'Accesorios'];
const UNIDADES = ['pieza', 'par', 'conjunto'];
// Se conserva el nombre de la constante por compatibilidad con el registro existente.
const EMBUTIDOS = ['Playeras', 'Sudaderas', 'Pantalones', 'Vestidos', 'Chamarras', 'Bolsas', 'Gorras', 'Accesorios', 'Me gusta descubrir'];

const ENCUESTA = [
    'embutido_favorito' => ['pregunta' => '¿Qué categoría te gustaría ver más?', 'opciones' => EMBUTIDOS],
    'atencion' => ['pregunta' => '¿Qué te pareció la experiencia de compra?', 'opciones' => ['Excelente', 'Muy buena', 'Buena', 'Podemos mejorar']],
    'limpieza' => ['pregunta' => '¿Qué tan fácil fue encontrar lo que buscabas?', 'opciones' => ['Muy fácil', 'Fácil', 'Un poco difícil', 'No encontré lo que buscaba']],
    'precios' => ['pregunta' => '¿Cómo te parecen nuestros precios?', 'opciones' => ['Muy buenos', 'Justos', 'Un poco altos', 'Me gustaría ver más ofertas']],
    'frescura' => ['pregunta' => '¿Cómo calificarías la variedad del catálogo?', 'opciones' => ['Excelente', 'Muy buena', 'Buena', 'Quiero más variedad']],
    'recomendacion' => ['pregunta' => '¿Recomendarías Ropita?', 'opciones' => ['¡Claro que sí!', 'Probablemente', 'Aún no lo sé', 'No por ahora']],
];

const REGALOS = [
    ['✨', 'Tu estilo merece brillar. Gracias por ayudarnos a mejorar.'],
    ['🧢', '¡Buen gusto! Que nunca falte un accesorio que te represente.'],
    ['👕', 'Un básico para combinar con todo: gracias por ser parte de Ropita.'],
    ['💜', '¡Tu opinión nos inspira a crear una tienda más bonita para todos!'],
];
