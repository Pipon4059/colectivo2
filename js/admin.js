const panelAdmin = document.getElementById('panel-admin');
const mensajePanel = document.getElementById('mensaje-admin-panel');
const formularioCliente = document.getElementById('form-cliente');
const botonCancelar = document.getElementById('cancelar-edicion');

let clientesGuardados = [];

fetch('backend/verificar-admin.php')
    .then(function (respuesta) { return respuesta.json(); })
    .then(function (datos) {
        if (datos.esAdmin == false) {
            window.location.href = 'admin-login.html';
            return;
        }

        document.getElementById('nombre-admin').textContent = datos.nombre;
        panelAdmin.style.display = 'block';
        cargarDatos();
    })
    .catch(function () {
        window.location.href = 'admin-login.html';
    });

function cargarDatos() {
    fetch('backend/admin-datos.php')
        .then(function (respuesta) {
            if (respuesta.ok == false) {
                throw new Error();
            }
            return respuesta.json();
        })
        .then(function (datos) {
            clientesGuardados = datos.clientes;
            document.getElementById('total-clientes').textContent = datos.estadisticas.clientes;
            document.getElementById('total-productos').textContent = datos.estadisticas.productos;
            document.getElementById('total-publicaciones').textContent = datos.estadisticas.publicaciones;
            document.getElementById('total-ordenes').textContent = datos.estadisticas.ordenes;
            mostrarClientes(datos.clientes);
            mostrarProductos(datos.productos);
            mostrarOrdenes(datos.ordenes);
            mostrarDisputas(datos.disputas);
            mostrarIntermediarios(datos.intermediarios);
            mostrarAuditoria(datos.auditoria);
        })
        .catch(function () {
            mostrarMensaje('No se pudieron cargar los datos.', 'error');
        });
}

function mostrarClientes(clientes) {
    const lista = document.getElementById('lista-clientes');
    lista.innerHTML = '';

    if (clientes.length == 0) {
        lista.innerHTML = '<tr><td colspan="6">No hay clientes registrados.</td></tr>';
        return;
    }

    clientes.forEach(function (cliente) {
        const fila = document.createElement('tr');
        agregarCelda(fila, cliente.nombre);
        agregarCelda(fila, cliente.email);
        agregarCelda(fila, cliente.tipo);
        agregarCelda(fila, cliente.activo == 1 ? 'Activo' : 'Inactivo');
        agregarCelda(fila, cliente.publicaciones);

        const acciones = document.createElement('td');
        acciones.className = 'admin-acciones';
        const editar = crearBoton('Editar', 'admin-btn-editar');
        editar.addEventListener('click', function () { editarCliente(cliente.id_cop); });
        const eliminar = crearBoton('Desactivar', 'admin-btn-eliminar');
        eliminar.addEventListener('click', function () { eliminarCliente(cliente.id_cop, cliente.nombre); });
        acciones.appendChild(editar);
        acciones.appendChild(eliminar);
        fila.appendChild(acciones);
        lista.appendChild(fila);
    });
}

function mostrarProductos(productos) {
    const lista = document.getElementById('lista-productos');
    lista.innerHTML = '';

    if (productos.length == 0) {
        lista.innerHTML = '<tr><td colspan="5">No hay productos publicados.</td></tr>';
        return;
    }

    productos.forEach(function (producto) {
        const fila = document.createElement('tr');
        agregarCelda(fila, producto.nombre);
        agregarCelda(fila, producto.vendedor);
        agregarCelda(fila, '$' + Number(producto.precio).toLocaleString('es-UY'));
        agregarCelda(fila, producto.fecha_publicacion);

        const acciones = document.createElement('td');
        acciones.className = 'admin-acciones';
        const eliminar = crearBoton(producto.disponible == 1 ? 'Retirar' : 'Retirado', 'admin-btn-eliminar');
        eliminar.disabled = producto.disponible == 0;
        eliminar.addEventListener('click', function () { eliminarProducto(producto.id_producto, producto.nombre); });
        acciones.appendChild(eliminar);
        fila.appendChild(acciones);
        lista.appendChild(fila);
    });
}

function mostrarOrdenes(ordenes) {
    const lista = document.getElementById('lista-ordenes');
    lista.innerHTML = '';
    if (ordenes.length == 0) {
        lista.innerHTML = '<tr><td colspan="6">No hay ordenes.</td></tr>';
        return;
    }
    ordenes.forEach(function (orden) {
        const fila = document.createElement('tr');
        agregarCelda(fila, '#' + orden.id_orden);
        agregarCelda(fila, orden.producto);
        agregarCelda(fila, orden.comprador);
        agregarCelda(fila, orden.vendedor);
        agregarCelda(fila, orden.estado.replaceAll('_', ' '));
        agregarCelda(fila, '$' + Number(orden.precio_unitario).toLocaleString('es-UY'));
        lista.appendChild(fila);
    });
}

function mostrarDisputas(disputas) {
    const lista = document.getElementById('lista-disputas');
    lista.innerHTML = '';
    if (disputas.length == 0) {
        lista.innerHTML = '<p>No hay disputas.</p>';
        return;
    }
    disputas.forEach(function (disputa) {
        const item = document.createElement('article');
        item.className = 'admin-item-simple';
        item.innerHTML = '<strong>Orden #' + disputa.id_orden + '</strong><p>' + disputa.motivo + '</p><p>Estado: ' + disputa.estado + '</p>';
        if (disputa.estado == 'abierta') {
            const reembolsar = crearBoton('Reembolsar', 'admin-btn-eliminar');
            const liberar = crearBoton('Liberar pago', 'admin-btn-editar');
            reembolsar.addEventListener('click', function () { resolverDisputa(disputa.id_disputa, 'reembolsar'); });
            liberar.addEventListener('click', function () { resolverDisputa(disputa.id_disputa, 'liberar'); });
            item.appendChild(reembolsar);
            item.appendChild(liberar);
        }
        lista.appendChild(item);
    });
}

function mostrarIntermediarios(intermediarios) {
    const lista = document.getElementById('lista-intermediarios');
    lista.innerHTML = '';
    intermediarios.forEach(function (usuario) {
        const texto = document.createElement('p');
        texto.textContent = usuario.nombre + ' - ' + usuario.email + ' - ' + (usuario.activo == 1 ? 'Activo' : 'Inactivo');
        lista.appendChild(texto);
    });
}

function mostrarAuditoria(registros) {
    const lista = document.getElementById('lista-auditoria');
    lista.innerHTML = '';
    registros.forEach(function (registro) {
        const texto = document.createElement('p');
        texto.textContent = registro.fecha + ' - ' + registro.usuario + ': ' + registro.accion;
        lista.appendChild(texto);
    });
}

async function resolverDisputa(idDisputa, decision) {
    const resultado = await Swal.fire({
        title: decision == 'reembolsar' ? 'Confirmar reembolso' : 'Liberar pago',
        input: 'textarea',
        inputLabel: 'Explica la resolucion',
        showCancelButton: true,
        confirmButtonText: 'Guardar resolucion',
        cancelButtonText: 'Cancelar',
        inputValidator: function (valor) {
            if (!valor) { return 'Escribe una explicacion.'; }
        }
    });

    if (!resultado.isConfirmed) { return; }

    const datos = new FormData();
    datos.append('id_disputa', idDisputa);
    datos.append('decision', decision);
    datos.append('resolucion', resultado.value);
    fetch('backend/admin-disputa-resolver.php', { method: 'POST', body: datos })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (resultado) {
            mostrarMensaje(resultado.mensaje, resultado.correcto ? 'exito' : 'error');
            if (resultado.correcto) { cargarDatos(); }
        });
}

function agregarCelda(fila, texto) {
    const celda = document.createElement('td');
    celda.textContent = texto;
    fila.appendChild(celda);
}

function crearBoton(texto, clase) {
    const boton = document.createElement('button');
    boton.textContent = texto;
    boton.className = clase;
    return boton;
}

formularioCliente.addEventListener('submit', function (evento) {
    evento.preventDefault();

    fetch('backend/admin-cliente-guardar.php', {
        method: 'POST',
        body: new FormData(formularioCliente)
    })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (datos) {
            if (datos.correcto == false) {
                mostrarMensaje(datos.mensaje, 'error');
                return;
            }
            mostrarMensaje(datos.mensaje, 'exito');
            limpiarFormulario();
            cargarDatos();
        })
        .catch(function () { mostrarMensaje('No se pudo guardar el cliente.', 'error'); });
});

function editarCliente(id) {
    const cliente = clientesGuardados.find(function (item) { return item.id_cop == id; });
    if (!cliente) { return; }

    document.getElementById('cliente-id').value = cliente.id_cop;
    document.getElementById('cliente-nombre').value = cliente.nombre;
    document.getElementById('cliente-email').value = cliente.email;
    document.getElementById('cliente-telefono').value = cliente.telefono || '';
    document.getElementById('cliente-tipo').value = cliente.tipo;
    document.getElementById('cliente-contrasena').value = '';
    document.getElementById('titulo-form-cliente').textContent = 'Editar cliente';
    document.getElementById('ayuda-contrasena').textContent = 'Dejala vacia para mantener la contraseña actual.';
    botonCancelar.style.display = 'inline-block';
    formularioCliente.scrollIntoView({ behavior: 'smooth' });
}

botonCancelar.addEventListener('click', limpiarFormulario);

function limpiarFormulario() {
    formularioCliente.reset();
    document.getElementById('cliente-id').value = '';
    document.getElementById('titulo-form-cliente').textContent = 'Agregar cliente';
    document.getElementById('ayuda-contrasena').textContent = 'Minimo 6 caracteres.';
    botonCancelar.style.display = 'none';
}

async function eliminarCliente(id, nombre) {
    const confirmacion = await Swal.fire({
        title: '¿Desactivar cliente?',
        text: nombre + ' no podra iniciar sesion.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#a00000'
    });

    if (confirmacion.isConfirmed) {
        enviarEliminacion('backend/admin-cliente-eliminar.php', id, 'Cliente desactivado correctamente.');
    }
}

async function eliminarProducto(id, nombre) {
    const confirmacion = await Swal.fire({
        title: '¿Retirar producto?',
        text: nombre + ' dejara de aparecer en el catalogo.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, retirar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#a00000'
    });

    if (confirmacion.isConfirmed) {
        enviarEliminacion('backend/admin-producto-eliminar.php', id, 'Producto retirado correctamente.');
    }
}

function enviarEliminacion(url, id, mensajeCorrecto) {
    const datos = new FormData();
    datos.append('id', id);

    fetch(url, { method: 'POST', body: datos })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (resultado) {
            if (resultado.correcto == false) {
                mostrarMensaje(resultado.mensaje, 'error');
                return;
            }
            mostrarMensaje(mensajeCorrecto, 'exito');
            cargarDatos();
        })
        .catch(function () { mostrarMensaje('No se pudo completar la operacion.', 'error'); });
}

function mostrarMensaje(texto, tipo) {
    mensajePanel.textContent = texto;
    mensajePanel.className = 'admin-mensaje ' + tipo;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
