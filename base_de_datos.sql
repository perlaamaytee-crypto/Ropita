<?php
// Catálogo Ropita: crear la base, tablas y productos de ejemplo.
CREATE DATABASE IF NOT EXISTS ropita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ropita;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    embutido_favorito VARCHAR(60) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(160) NOT NULL DEFAULT '',
    precio DECIMAL(10,2) NOT NULL,
    unidad VARCHAR(20) NOT NULL DEFAULT 'pieza',
    stock INT NOT NULL DEFAULT 0,
    categoria VARCHAR(40) NOT NULL,
    emoji VARCHAR(16) NOT NULL DEFAULT '👕'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    embutido_favorito VARCHAR(60) NOT NULL,
    atencion VARCHAR(120) NOT NULL,
    limpieza VARCHAR(120) NOT NULL,
    precios VARCHAR(120) NOT NULL,
    frescura VARCHAR(120) NOT NULL,
    recomendacion VARCHAR(120) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitante_id CHAR(36) NOT NULL,
    sesion_id CHAR(36) NOT NULL,
    usuario_id INT NULL,
    tipo ENUM('vista', 'clic', 'tiempo') NOT NULL,
    pagina VARCHAR(200) NOT NULL,
    elemento VARCHAR(100) NULL,
    segundos SMALLINT UNSIGNED NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX (visitante_id), INDEX (sesion_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ubicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    latitud DECIMAL(9,6) NOT NULL,
    longitud DECIMAL(9,6) NOT NULL,
    precision_m INT UNSIGNED NOT NULL,
    distancia_km DECIMAL(8,2) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    archivo VARCHAR(150) NOT NULL UNIQUE,
    tipo VARCHAR(20) NOT NULL,
    bytes INT UNSIGNED NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Cargar estos ejemplos una sola vez en una base de datos nueva.
INSERT INTO productos (nombre, descripcion, precio, unidad, stock, categoria, emoji)
SELECT ejemplos.nombre, ejemplos.descripcion, ejemplos.precio, ejemplos.unidad, ejemplos.stock, ejemplos.categoria, ejemplos.emoji
FROM (
    SELECT 'Playera esencial' nombre, 'Algodón suave para combinar todos los días' descripcion, 249.00 precio, 'pieza' unidad, 18 stock, 'Novedades' categoria, '👕' emoji
    UNION ALL SELECT 'Sudadera nube', 'Cómoda, suave y lista para salir', 549.00, 'pieza', 12, 'Ropa', '🧥'
    UNION ALL SELECT 'Pantalón relajado', 'Corte cómodo para tus planes de diario', 499.00, 'pieza', 9, 'Ropa', '👖'
    UNION ALL SELECT 'Bolsa mini', 'Un detalle práctico para llevar lo esencial', 299.00, 'pieza', 15, 'Accesorios', '👜'
    UNION ALL SELECT 'Gorra clásica', 'Un toque final para tu look', 189.00, 'pieza', 20, 'Accesorios', '🧢'
    UNION ALL SELECT 'Conjunto favorito', 'Una combinación fácil que ya viene lista', 699.00, 'conjunto', 7, 'Novedades', '👚'
) AS ejemplos
WHERE NOT EXISTS (SELECT 1 FROM productos);
