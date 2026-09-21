const parametros = new URLSearchParams(window.location.search);
const mensaje = document.getElementById('mensaje-admin');

if (parametros.get('error') == 'faltan-datos') {
    mensaje.textContent = 'Completa el correo y la contraseña.';
    mensaje.classList.add('mensaje-error');
}

if (parametros.get('error') == 'datos-incorrectos') {
    mensaje.textContent = 'Los datos de administrador son incorrectos.';
    mensaje.classList.add('mensaje-error');
}
