const parametros = new URLSearchParams(window.location.search);
const mensajeLogin = document.getElementById('mensaje-login');

if (parametros.get('registro') == 'correcto') {
    mensajeLogin.textContent = 'Cuenta creada correctamente. Ya puedes iniciar sesion.';
    mensajeLogin.classList.add('mensaje-exito');
}

if (parametros.get('error') == 'faltan-datos') {
    mensajeLogin.textContent = 'Completa el correo y la contraseña.';
    mensajeLogin.classList.add('mensaje-error');
}

if (parametros.get('error') == 'datos-incorrectos') {
    mensajeLogin.textContent = 'El correo o la contraseña son incorrectos.';
    mensajeLogin.classList.add('mensaje-error');
}
