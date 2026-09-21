const parametros = new URLSearchParams(window.location.search);
const mensajeRegistro = document.getElementById('mensaje-registro');
const formularioRegistro = document.getElementById('form-registro');
const error = parametros.get('error');

if (error == 'faltan-datos') {
    mensajeRegistro.textContent = 'Completa nombre, correo y contraseña.';
    mensajeRegistro.classList.add('mensaje-error');
}

if (error == 'correo-repetido') {
    mensajeRegistro.textContent = 'Ese correo ya esta registrado.';
    mensajeRegistro.classList.add('mensaje-error');
}

if (error == 'correo-invalido') {
    mensajeRegistro.textContent = 'Ingresa un correo valido.';
    mensajeRegistro.classList.add('mensaje-error');
}

if (error == 'contrasena-corta') {
    mensajeRegistro.textContent = 'La contraseña debe tener al menos 6 caracteres.';
    mensajeRegistro.classList.add('mensaje-error');
}

if (error == 'telefono-invalido') {
    mensajeRegistro.textContent = 'El telefono debe contener solo numeros.';
    mensajeRegistro.classList.add('mensaje-error');
}

formularioRegistro.addEventListener('submit', function (evento) {
    const nombre = document.getElementById('nombre').value.trim();
    const email = document.getElementById('email');
    const contrasena = document.getElementById('contrasena').value;

    mensajeRegistro.className = 'mensaje';

    if (nombre == '' || email.value.trim() == '' || contrasena == '') {
        evento.preventDefault();
        mensajeRegistro.textContent = 'Completa nombre, correo y contraseña.';
        mensajeRegistro.classList.add('mensaje-error');
        return;
    }

    if (!email.checkValidity()) {
        evento.preventDefault();
        mensajeRegistro.textContent = 'Ingresa un correo valido.';
        mensajeRegistro.classList.add('mensaje-error');
        return;
    }

    if (contrasena.length < 6) {
        evento.preventDefault();
        mensajeRegistro.textContent = 'La contraseña debe tener al menos 6 caracteres.';
        mensajeRegistro.classList.add('mensaje-error');
    }
});
