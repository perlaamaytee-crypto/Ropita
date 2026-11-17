# Embutidos El Gran Salchichón (sucursal Izcalli Norte)

Sitio de la sucursal: PHP 8.3 + Apache (Docker) + MySQL. Chat aparte en Flask.

## Tipos de cuenta
- **Invitado** (sin sesión): ve información, productos, mapa y video.
- **Cliente** (registro gratis): además opina en la encuesta, usa el chat, calcula su distancia y sube su foto.
- **Administrador**: todo lo anterior + panel `/admin/` (inventario, encuesta, clientes, ubicaciones, fotos, métricas).

## Variables de entorno (servicio del sitio en Render)
`DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, `DB_NAME` (= `gran_salchichon`), `CHAT_URL`.
El servicio del chat no lleva variables.

## Volver administrador a una cuenta
1. Regístrate normalmente en el sitio.
2. En DBeaver ejecuta (con tu correo): `UPDATE usuarios SET rol = 'admin' WHERE correo = 'tu_correo';`
3. Cierra sesión y vuelve a entrar. Aparece el botón «Panel».

## Nota
Las fotos se guardan en `uploads/`. En Render Free el disco se borra en cada despliegue o reinicio:
el registro queda en la base de datos, pero el archivo desaparece (el sitio simplemente no lo muestra).
