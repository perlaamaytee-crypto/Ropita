// assets/app.js
// Comportamiento de la página: menú móvil, regalo, distancia a la sucursal y subida de fotos.

const csrf = document.querySelector('meta[name="csrf"]')?.content ?? '';

function mostrarMensaje(el, texto, clase) {
    el.textContent = texto;
    el.className = 'mensaje ' + clase;
    el.hidden = false;
}

// Lee la respuesta como JSON; si el servidor devolvió una página de error, no se rompe.
async function leerJson(respuesta) {
    try {
        return await respuesta.json();
    } catch {
        return { error: 'El servidor respondió algo inesperado. Inténtalo de nuevo.' };
    }
}

// ---------- Menú en pantallas chicas ----------
const botonMenu = document.querySelector('.menu-btn');
const menu = document.getElementById('menu-principal');
if (botonMenu && menu) {
    botonMenu.addEventListener('click', () => {
        const abierto = menu.classList.toggle('abierto');
        botonMenu.setAttribute('aria-expanded', String(abierto));
    });
    menu.addEventListener('click', (e) => {
        if (e.target.closest('a')) {
            menu.classList.remove('abierto');
            botonMenu.setAttribute('aria-expanded', 'false');
        }
    });
}

// ---------- Registro: aviso si las contraseñas no coinciden ----------
const formRegistro = document.getElementById('form-registro');
if (formRegistro) {
    const clave = document.getElementById('clave');
    const confirma = document.getElementById('confirma');
    const aviso = document.getElementById('msg-clave');
    const revisar = () => {
        aviso.hidden = confirma.value === '' || clave.value === confirma.value;
    };
    clave.addEventListener('input', revisar);
    confirma.addEventListener('input', revisar);
    formRegistro.addEventListener('submit', (e) => {
        if (clave.value !== confirma.value) {
            e.preventDefault();
            aviso.hidden = false;
            confirma.focus();
        }
    });
}

// ---------- Regalo después de la encuesta ----------
const botonRegalo = document.getElementById('btn-regalo');
if (botonRegalo) {
    const regalos = JSON.parse(document.getElementById('regalos-datos').textContent);
    botonRegalo.addEventListener('click', () => {
        const [emoji, texto] = regalos[Math.floor(Math.random() * regalos.length)];
        document.getElementById('regalo-emoji').textContent = emoji;
        document.getElementById('regalo-texto').textContent = texto;
        document.getElementById('regalo-premio').hidden = false;
        botonRegalo.hidden = true;
    });
}

// ---------- ¿Qué tan lejos estás? (geolocalización) ----------
const botonUbicacion = document.getElementById('btn-ubicacion');
if (botonUbicacion) {
    const caja = document.getElementById('distancia');
    const mensaje = document.getElementById('msg-ubicacion');
    const ruta = document.getElementById('ruta-desde');
    const destino = `${caja.dataset.lat},${caja.dataset.lng}`;

    const errores = {
        1: 'Negaste el permiso de ubicación. Puedes activarlo desde el candado de la barra de direcciones.',
        2: 'No se pudo determinar tu ubicación.',
        3: 'Se agotó el tiempo de espera. Inténtalo otra vez.',
    };

    botonUbicacion.addEventListener('click', () => {
        // La ubicación, como la cámara, solo funciona en https:// o en localhost.
        if (!window.isSecureContext || !navigator.geolocation) {
            mostrarMensaje(mensaje, 'Tu navegador solo comparte la ubicación en sitios https.', 'error');
            return;
        }
        botonUbicacion.disabled = true;
        mostrarMensaje(mensaje, 'Buscando tu ubicación…', 'info');
        navigator.geolocation.getCurrentPosition(async (pos) => {
            const { latitude, longitude, accuracy } = pos.coords;
            try {
                const r = await fetch('/api/ubicacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                    body: JSON.stringify({ latitud: latitude, longitud: longitude, precision: accuracy }),
                });
                const json = await leerJson(r);
                if (!r.ok) {
                    mostrarMensaje(mensaje, json.error, 'error');
                } else {
                    mostrarMensaje(mensaje, `Estás a ${json.distancia_km.toFixed(1)} km de la sucursal (en línea recta).`, 'exito');
                    ruta.querySelector('a').href =
                        `https://www.google.com/maps/dir/?api=1&origin=${latitude},${longitude}&destination=${destino}`;
                    ruta.hidden = false;
                }
            } catch {
                mostrarMensaje(mensaje, 'No se pudo conectar con el servidor.', 'error');
            }
            botonUbicacion.disabled = false;
        }, (error) => {
            botonUbicacion.disabled = false;
            mostrarMensaje(mensaje, errores[error.code] ?? error.message, 'error');
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
    });
}

// ---------- Muro de clientes: cámara o archivo, vista previa y subida ----------
const zonaFoto = document.getElementById('subir-foto');
if (zonaFoto) {
    const video = document.getElementById('video-camara');
    const btnCamara = document.getElementById('btn-camara');
    const btnTomar = document.getElementById('btn-tomar');
    const archivo = document.getElementById('archivo');
    const vistaPrevia = document.getElementById('vista-previa');
    const btnSubir = document.getElementById('btn-subir');
    const mensaje = document.getElementById('msg-foto');
    let foto = null; // la imagen lista para subir (venga de la cámara o del archivo)

    // Las dos opciones terminan aquí. createObjectURL da una dirección temporal para ver la imagen antes de subirla.
    function usarFoto(imagen) {
        foto = imagen;
        vistaPrevia.src = URL.createObjectURL(imagen);
        vistaPrevia.hidden = false;
        btnSubir.disabled = false;
    }

    btnCamara.addEventListener('click', async () => {
        if (!navigator.mediaDevices) {
            mostrarMensaje(mensaje, 'La cámara solo funciona en sitios https. Usa la opción de elegir archivo.', 'error');
            return;
        }
        try {
            video.srcObject = await navigator.mediaDevices.getUserMedia({ video: true });
            video.hidden = false;
            btnTomar.disabled = false;
        } catch (error) {
            mostrarMensaje(mensaje, 'No se pudo abrir la cámara: ' + error.message, 'error');
        }
    });

    btnTomar.addEventListener('click', () => {
        const lienzo = document.createElement('canvas');
        lienzo.width = video.videoWidth;
        lienzo.height = video.videoHeight;
        lienzo.getContext('2d').drawImage(video, 0, 0);
        lienzo.toBlob(usarFoto, 'image/jpeg', 0.9);
        video.srcObject.getTracks().forEach((t) => t.stop()); // apaga la cámara
        video.hidden = true;
        btnTomar.disabled = true;
    });

    archivo.addEventListener('change', () => {
        if (archivo.files[0]) usarFoto(archivo.files[0]);
    });

    btnSubir.addEventListener('click', async () => {
        const datos = new FormData();
        datos.append('foto', foto, 'foto.jpg'); // el servidor ignora este nombre y pone el suyo
        btnSubir.disabled = true;
        try {
            const r = await fetch('/api/foto_subir.php', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf },
                body: datos,
            });
            const json = await leerJson(r);
            if (!r.ok) {
                mostrarMensaje(mensaje, json.error, 'error');
                btnSubir.disabled = false;
                return;
            }
            mostrarMensaje(mensaje, 'Foto guardada. Actualizando el muro…', 'exito');
            setTimeout(() => location.reload(), 900);
        } catch {
            mostrarMensaje(mensaje, 'No se pudo conectar con el servidor.', 'error');
            btnSubir.disabled = false;
        }
    });
}
