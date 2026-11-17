<?php
// includes/config.php
// Todos los datos de la sucursal viven aquí: se cambian en un solo lugar.

const SUCURSAL = [
    'nombre'    => 'Embutidos El Gran Salchichón',
    'corto'     => 'El Gran Salchichón',
    'lema'      => 'El Rey del Jamoncito',
    'sucursal'  => 'Sucursal Izcalli Norte',
    'direccion' => 'Av. del Chorizo 69, Col. Los Embutidos',
    'ciudad'    => 'Cuautitlán Izcalli, Estado de México',
    'telefono'  => '55 5555 0069',
    'correo'    => 'hola@gransalchichon.example',
    'lat'       => 19.6048,
    'lng'       => -99.1905,
    'video_id'  => 'o-uov-LlTBI',
];

// Días ISO: 1 = lunes ... 7 = domingo. [hora de apertura, hora de cierre]
const HORARIO = [
    1 => [8, 20], 2 => [8, 20], 3 => [8, 20], 4 => [8, 20], 5 => [8, 20], 6 => [8, 20],
    7 => [9, 15],
];
const HORARIO_TEXTO = [
    'Lunes a sábado' => '8:00 a 20:00',
    'Domingo'        => '9:00 a 15:00',
];

const CATEGORIAS = ['Jamones', 'Salchichas', 'Chorizos y longanizas', 'Quesos y más'];
const UNIDADES   = ['kg', 'pieza', 'paquete'];

const EMBUTIDOS = ['Jamón de pierna', 'Salchicha de pavo', 'Chorizo', 'Mortadela', 'Salami',
                   'Queso de puerco', 'Longaniza', 'Tocino', 'Todos, no me juzgues'];

// La encuesta: pregunta + opciones permitidas. El servidor solo acepta estas opciones.
const ENCUESTA = [
    'embutido_favorito' => ['pregunta' => '¿Cuál es tu embutido favorito?', 'opciones' => EMBUTIDOS],
    'atencion' => ['pregunta' => '¿Cómo te atienden en la sucursal?', 'opciones' => [
        'Como reyes, hasta me dan probadita',
        'Bien, solo que la fila va lenta como lunes',
        'Regular, me dijeron «ahorita lo veo» hace una hora',
        'Mejor no opino, el que atiende tiene cuchillo',
    ]],
    'limpieza' => ['pregunta' => '¿Qué tal la limpieza del local?', 'opciones' => [
        'Impecable, hasta mi mamá lo aprobaría',
        'Bien, el piso brilla más que mi futuro',
        'Regular, vi una mosca pero iba de paso',
        'Mejor no miro el piso',
    ]],
    'precios' => ['pregunta' => '¿Cómo ves los precios?', 'opciones' => [
        'Justos, mi cartera sonrió',
        'Algo caros, pero el jamón lo vale',
        'Regulares, la mortadela cobra por ser elegante',
        'Mi cartera pidió ayuda',
    ]],
    'frescura' => ['pregunta' => '¿Qué tan fresco está todo?', 'opciones' => [
        'Fresquísimo, lo juro por el chorizo',
        'Fresco, nada de misterios en el refri',
        'Regular, algunos llevan rato ahí',
        'Siempre huelo antes de comprar',
    ]],
    'recomendacion' => ['pregunta' => '¿Recomendarías El Gran Salchichón?', 'opciones' => [
        '¡Claro, hasta les hago publicidad!',
        'Solo si me regalan una probadita',
        'Depende de si hay oferta de jamón',
        'Lo pensaré después de mi torta',
    ]],
];

const REGALOS = [
    ['🌭', '¡Un hot dog de pixeles! Calorías: 0. Sabor: 0. Emoción: 100.'],
    ['🥓', '¡Un tocino imaginario! Crujiente solo en tu mente.'],
    ['🧀', '¡Una probadita virtual de queso de puerco! Válida con una sonrisa.'],
    ['🐷', '¡Un cochinito de la suerte! No da dinero, pero da buena vibra.'],
    ['🥪', '¡Una torta de jamón virtual! Cero migajas, cero hambre saciada.'],
    ['🏆', '¡Trofeo al Cliente Más Embutido del Año! Solo existe en tu imaginación.'],
];
