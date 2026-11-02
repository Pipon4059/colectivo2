const paginaPublicar = document.getElementById('publicar-pagina');
const mensaje = document.getElementById('mensaje-publicar');
const campoImagen = document.getElementById('imagen');
const vistaImagen = document.getElementById('vista-imagen');

fetch('backend/sesion.php')
    .then(function (respuesta) { return respuesta.json(); })
    .then(function (datos) {
        if (datos.sesion == false) {
            window.location.href = 'login.html';
            return;
        }
        paginaPublicar.style.display = 'grid';
    })
    .catch(function () {
        window.location.href = 'login.html';
    });

const parametros = new URLSearchParams(window.location.search);

if (parametros.get('error') == 'faltan-datos') {
    mensaje.textContent = 'Completa todos los campos.';
}

if (parametros.get('error') == 'precio-invalido') {
    mensaje.textContent = 'El precio debe ser un numero mayor que cero.';
}

if (parametros.get('error') == 'imagen-invalida') {
    mensaje.textContent = 'Selecciona una imagen JPG, PNG o WEBP.';
}

if (parametros.get('error') == 'imagen-grande') {
    mensaje.textContent = 'Cada imagen puede pesar hasta 5 MB.';
}

if (parametros.get('error') == 'sin-permiso') {
    mensaje.textContent = 'Tu cuenta es solo compradora. Cambia el tipo de cuenta para publicar.';
}

campoImagen.addEventListener('change', function () {
    const archivo = campoImagen.files[0];
    if (!archivo) { return; }

    const lector = new FileReader();
    lector.addEventListener('load', function () {
        vistaImagen.src = lector.result;
    });
    lector.readAsDataURL(archivo);
});
