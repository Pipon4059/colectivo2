const panelIntermediario = document.getElementById('panel-intermediario');
const mensajeIntermediario = document.getElementById('mensaje-intermediario-panel');

cargarOrdenes();

function cargarOrdenes() {
    fetch('backend/intermediario-datos.php')
        .then(function (respuesta) {
            if (respuesta.status == 403) {
                window.location.href = 'intermediario-login.html';
                return null;
            }
            return respuesta.json();
        })
        .then(function (datos) {
            if (!datos) { return; }
            panelIntermediario.style.display = 'block';
            document.getElementById('nombre-intermediario').textContent = datos.nombre;
            mostrarOrdenes(datos.ordenes);
        });
}

function mostrarOrdenes(ordenes) {
    const lista = document.getElementById('ordenes-verificar');
    lista.innerHTML = '';

    if (ordenes.length == 0) {
        lista.innerHTML = '<p>No hay productos pendientes de verificacion.</p>';
        return;
    }

    ordenes.forEach(function (orden) {
        const tarjeta = document.createElement('article');
        tarjeta.className = 'intermediario-item';

        const titulo = document.createElement('h3');
        titulo.textContent = 'Orden #' + orden.id_orden + ' - ' + orden.producto;
        tarjeta.appendChild(titulo);

        const vendedor = document.createElement('p');
        vendedor.textContent = 'Vendedor: ' + orden.vendedor;
        tarjeta.appendChild(vendedor);

        const comprador = document.createElement('p');
        comprador.textContent = 'Comprador: ' + orden.comprador;
        tarjeta.appendChild(comprador);

        const seguimiento = document.createElement('p');
        seguimiento.textContent = 'Seguimiento: ' + (orden.codigo_seguimiento || '-');
        tarjeta.appendChild(seguimiento);

        const observacion = document.createElement('textarea');
        observacion.placeholder = 'Observacion de la verificacion';
        const aprobar = crearBotonIntermediario('Aprobar', 'admin-btn-primario');
        const rechazar = crearBotonIntermediario('Rechazar', 'admin-btn-eliminar');
        aprobar.addEventListener('click', function () { verificar(orden.id_orden, 'aprobado', observacion.value); });
        rechazar.addEventListener('click', function () { verificar(orden.id_orden, 'rechazado', observacion.value); });
        tarjeta.appendChild(observacion);
        tarjeta.appendChild(aprobar);
        tarjeta.appendChild(rechazar);
        lista.appendChild(tarjeta);
    });
}

function crearBotonIntermediario(texto, clase) {
    const boton = document.createElement('button');
    boton.type = 'button';
    boton.textContent = texto;
    boton.className = clase;
    return boton;
}

function verificar(idOrden, resultado, observacion) {
    const datos = new FormData();
    datos.append('id_orden', idOrden);
    datos.append('resultado', resultado);
    datos.append('observacion', observacion);

    fetch('backend/intermediario-verificar.php', { method: 'POST', body: datos })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (resultadoPeticion) {
            mensajeIntermediario.textContent = resultadoPeticion.mensaje;
            mensajeIntermediario.className = 'admin-mensaje ' + (resultadoPeticion.correcto ? 'exito' : 'error');
            if (resultadoPeticion.correcto) { cargarOrdenes(); }
        });
}
