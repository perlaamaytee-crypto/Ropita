-- Base de datos de Embutidos El Gran Salchichón.
-- En DBeaver (conexión railway): abre una hoja SQL, pega TODO esto y ejecútalo con ALT + X (Ejecutar script).
-- Crea una base NUEVA llamada gran_salchichon. No toca ninguna otra base de tu Railway.

CREATE DATABASE IF NOT EXISTS gran_salchichon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gran_salchichon;

-- Cuentas. rol: 'cliente' (se crea desde el registro) o 'admin' (se asigna con el UPDATE del README).
CREATE TABLE IF NOT EXISTS usuarios (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    nombre            VARCHAR(100) NOT NULL,
    correo            VARCHAR(150) NOT NULL UNIQUE,
    embutido_favorito VARCHAR(60)  NOT NULL,
    password_hash     VARCHAR(255) NOT NULL,
    rol               ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
    creado_en         TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Inventario (el CRUD del administrador)
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100)  NOT NULL,
    descripcion VARCHAR(160)  NOT NULL DEFAULT '',
    precio      DECIMAL(10,2) NOT NULL,
    unidad      VARCHAR(20)   NOT NULL DEFAULT 'kg',
    stock       INT           NOT NULL DEFAULT 0,
    categoria   VARCHAR(40)   NOT NULL,
    emoji       VARCHAR(16)   NOT NULL DEFAULT '🍖'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Encuesta de satisfacción
CREATE TABLE IF NOT EXISTS respuestas (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id        INT NULL,
    nombre            VARCHAR(100) NOT NULL,
    embutido_favorito VARCHAR(60)  NOT NULL,
    atencion          VARCHAR(120) NOT NULL,
    limpieza          VARCHAR(120) NOT NULL,
    precios           VARCHAR(120) NOT NULL,
    frescura          VARCHAR(120) NOT NULL,
    recomendacion     VARCHAR(120) NOT NULL,
    fecha             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Métricas del sitio (vistas, clics y tiempo)
CREATE TABLE IF NOT EXISTS eventos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    visitante_id CHAR(36) NOT NULL,
    sesion_id    CHAR(36) NOT NULL,
    usuario_id   INT NULL,
    tipo         ENUM('vista', 'clic', 'tiempo') NOT NULL,
    pagina       VARCHAR(200) NOT NULL,
    elemento     VARCHAR(100) NULL,
    segundos     SMALLINT UNSIGNED NULL,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX (visitante_id),
    INDEX (sesion_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Ubicaciones que comparten los clientes
CREATE TABLE IF NOT EXISTS ubicaciones (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id   INT NULL,
    nombre       VARCHAR(100)  NOT NULL,
    latitud      DECIMAL(9, 6) NOT NULL,
    longitud     DECIMAL(9, 6) NOT NULL,
    precision_m  INT UNSIGNED  NOT NULL,
    distancia_km DECIMAL(8, 2) NOT NULL DEFAULT 0,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Muro de clientes (fotos)
CREATE TABLE IF NOT EXISTS fotos (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    nombre     VARCHAR(100) NOT NULL,
    archivo    VARCHAR(150) NOT NULL UNIQUE,
    tipo       VARCHAR(20)  NOT NULL,
    bytes      INT UNSIGNED NOT NULL,
    creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Productos de ejemplo para que el pizarrón no salga vacío
INSERT INTO productos (nombre, descripcion, precio, unidad, stock, categoria, emoji) VALUES
    ('Jamón de pierna',            'Rebanado al momento, el favorito de la casa',  189.00, 'kg',      25, 'Jamones', '🍖'),
    ('Jamón de pavo',              'Bajo en grasa, alto en sabor',                 168.00, 'kg',      18, 'Jamones', '🦃'),
    ('Jamón serrano',              'Curado, para presumir en la tabla de quesos',  420.00, 'kg',       6, 'Jamones', '🐷'),
    ('Jamón Virginia',             'Suave y ligeramente dulce',                    215.00, 'kg',      14, 'Jamones', '🍖'),
    ('Salchicha de pavo',          'Paquete de 10 piezas',                          62.50, 'paquete', 40, 'Salchichas', '🌭'),
    ('Salchicha viena',            'Paquete de 12 piezas',                          48.00, 'paquete',  3, 'Salchichas', '🌭'),
    ('Salchicha cocktail',         'Para botanas y fiestas, paquete de 20',         59.00, 'paquete', 22, 'Salchichas', '🌭'),
    ('Chorizo del Gran Salchichón','El de la casa, con receta que no se cuenta',   145.00, 'kg',      18, 'Chorizos y longanizas', '🌶️'),
    ('Longaniza de Toluca',        'Rojita, sabrosa y sin pena',                   138.00, 'kg',      12, 'Chorizos y longanizas', '🌶️'),
    ('Chorizo argentino',          'Para el asado del domingo',                    175.00, 'kg',       0, 'Chorizos y longanizas', '🥩'),
    ('Queso de puerco',            'Ni queso ni cerdito: una experiencia',         120.00, 'kg',      12, 'Quesos y más', '🐷'),
    ('Mortadela elegante',         'Se pone corbata los domingos',                  98.00, 'kg',      15, 'Quesos y más', '🥪'),
    ('Salami de autor',            'Cada rebanada trae su propia opinión',         210.00, 'kg',       9, 'Quesos y más', '🥓'),
    ('Queso panela',               'Fresco, de la tarde',                          110.00, 'kg',      20, 'Quesos y más', '🧀');
