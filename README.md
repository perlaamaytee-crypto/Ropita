# Ropita

Ropita es una boutique urbana en línea: un catálogo sencillo de prendas y accesorios, con inventario editable y cuentas para la comunidad. La interfaz se diseñó para que la ropa y el estilo personal sean el centro de la experiencia.

## Requisitos
- PHP 8.3 con extensiones `pdo_mysql` y `mbstring`.
- MySQL 8 o compatible.
- Docker (opcional).

## Ejecutar localmente
1. Crea la base y las tablas ejecutando `base_de_datos.sql` en MySQL.
2. Configura `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS` y `DB_NAME` en tu entorno. Si no defines `DB_NAME`, la aplicación usa `ropita`.
3. Sirve la carpeta con Apache/PHP o ejecuta `php -S localhost:8000` desde la raíz.
4. Abre `http://localhost:8000`.

Con Docker, construye la imagen (`docker build -t ropita .`) y ejecútala pasando las variables de conexión de MySQL.

## Catálogo y administración
La página principal muestra productos de las categorías `Novedades`, `Ropa` y `Accesorios`. Para añadir o editar prendas, inicia sesión con una cuenta cuyo rol sea `admin` y abre `/admin/productos.php`. El registro público siempre crea cuentas de cliente; el rol de administrador se asigna de forma manual en MySQL:

```sql
UPDATE usuarios SET rol = 'admin' WHERE correo = 'tu_correo';
```

La tienda muestra precio, descripción y disponibilidad. No se simula un carrito ni una compra: el checkout y los pagos no forman parte de esta aplicación.

## Funciones incluidas
- Registro e inicio de sesión con contraseñas hash y protección CSRF.
- Catálogo responsivo y gestión de inventario para administradores.
- Encuesta de comunidad y métricas con consentimiento de cookies.
- Endpoints de ubicación y muro de fotos de clientes heredados del proyecto; necesitan una sesión y un entorno HTTPS para las funciones del navegador.

## Despliegue
El `Dockerfile` ejecuta PHP 8.3 con Apache. Configura las variables de base de datos en el servicio de despliegue. Las imágenes subidas se guardan en `uploads/`; usa almacenamiento persistente si necesitas conservarlas entre despliegues.
