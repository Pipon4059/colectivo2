fetch('backend/verificar-admin.php')
    .then(function (respuesta) {
        return respuesta.json();
    })
    .then(function (datos) {
        if (datos.esAdmin == false) {
            window.location.href = 'admin-login.html';
            return;
        }

        document.getElementById('nombre-admin').textContent = datos.nombre;
        document.getElementById('panel-admin').style.display = 'flex';
    });
