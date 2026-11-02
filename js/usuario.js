const panelUsuario = document.getElementById('panel-usuario');
const mensajeUsuario = document.getElementById('mensaje-usuario');
const formularioPerfil = document.getElementById('form-perfil');
const formularioProducto = document.getElementById('form-producto');
const seccionEditarProducto = document.getElementById('editar-producto');

let productosGuardados = [];

cargarDatos();

function cargarDatos() {
    fetch('backend/usuario-datos.php')
        .then(function (respuesta) {
            if (respuesta.status == 403) {
                window.location.href = 'login.html';
                return null;
            }
            return respuesta.json();
        })
        .then(function (datos) {
            if (datos == null) { return; }

            panelUsuario.style.display = 'block';
            productosGuardados = datos.productos;
            mostrarPerfil(datos.usuario);
            mostrarProductos(datos.productos);
            mostrarOrdenes(datos.compras, 'usuario-compras', 'compra');
            mostrarOrdenes(datos.ventas, 'usuario-ventas', 'venta');
            mostrarNotificaciones(datos.notificaciones);
            document.getElementById('cantidad-productos').textContent = datos.productos.length;
            document.getElementById('usuario-reputacion').textContent = datos.reputacion;
        })
        .catch(function () {
            mostrarMensaje('No se pudieron cargar tus datos.', 'error');
        });
}

function mostrarPerfil(usuario) {
    document.getElementById('usuario-nombre-titulo').textContent = usuario.nombre;
    document.getElementById('perfil-nombre').value = usuario.nombre;
    document.getElementById('perfil-email').value = usuario.email;
    document.getElementById('perfil-telefono').value = usuario.telefono || '';
    document.getElementById('perfil-calle').value = usuario.calle || '';
    document.getElementById('perfil-numero').value = usuario.numero || '';
    document.getElementById('perfil-depto').value = usuario.depto || '';
    document.getElementById('perfil-tipo').value = usuario.tipo || 'ambos';
}

function mostrarProductos(productos) {
    const contenedor = document.getElementById('mis-productos');
    contenedor.innerHTML = '';

    if (productos.length == 0) {
        contenedor.innerHTML = '<p class="usuario-sin-productos">Todavia no publicaste productos.</p>';
        return;
    }

    productos.forEach(function (producto) {
        const tarjeta = document.createElement('article');
        tarjeta.className = 'usuario-producto-card';

        const imagen = document.createElement('img');
        imagen.src = producto.imagen ? 'img/' + producto.imagen : 'img/test-producto.jpg';
        imagen.alt = producto.nombre;

        const contenido = document.createElement('div');
        contenido.className = 'usuario-producto-info';
        const categoria = document.createElement('span');
        categoria.className = 'usuario-producto-categoria';
        categoria.textContent = producto.categoria;
        const nombre = document.createElement('h3');
        nombre.textContent = producto.nombre;
        const descripcion = document.createElement('p');
        descripcion.textContent = producto.descripcion;
        const precio = document.createElement('strong');
        precio.textContent = '$' + Number(producto.precio).toLocaleString('es-UY');
        const estado = document.createElement('small');
        estado.textContent = producto.disponible == 1 ? 'Disponible - ' + producto.estado_conservacion : 'No disponible';

        const acciones = document.createElement('div');
        acciones.className = 'usuario-producto-acciones';
        const editar = crearBoton('Editar', 'usuario-btn-editar');
        editar.addEventListener('click', function () { editarProducto(producto.id_producto); });
        const eliminar = crearBoton('Retirar', 'usuario-btn-eliminar');
        eliminar.addEventListener('click', function () { eliminarProducto(producto.id_producto, producto.nombre); });
        acciones.appendChild(editar);
        acciones.appendChild(eliminar);

        contenido.appendChild(categoria);
        contenido.appendChild(nombre);
        contenido.appendChild(descripcion);
        contenido.appendChild(precio);
        contenido.appendChild(estado);
        contenido.appendChild(acciones);
        tarjeta.appendChild(imagen);
        tarjeta.appendChild(contenido);
        contenedor.appendChild(tarjeta);
    });
}

function crearBoton(texto, clase) {
    const boton = document.createElement('button');
    boton.type = 'button';
    boton.textContent = texto;
    boton.className = clase;
    return boton;
}

formularioPerfil.addEventListener('submit', function (evento) {
    evento.preventDefault();

    fetch('backend/usuario-guardar.php', {
        method: 'POST',
        body: new FormData(formularioPerfil)
    })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (datos) {
            if (datos.correcto == false) {
                mostrarMensaje(datos.mensaje, 'error');
                return;
            }
            document.getElementById('perfil-contrasena').value = '';
            mostrarMensaje(datos.mensaje, 'exito');
            cargarDatos();
        })
        .catch(function () { mostrarMensaje('No se pudo actualizar el perfil.', 'error'); });
});

function editarProducto(id) {
    const producto = productosGuardados.find(function (item) { return item.id_producto == id; });
    if (!producto) { return; }

    document.getElementById('producto-id').value = producto.id_producto;
    document.getElementById('producto-nombre').value = producto.nombre;
    document.getElementById('producto-precio').value = producto.precio;
    document.getElementById('producto-descripcion').value = producto.descripcion;
    document.getElementById('producto-categoria').value = producto.id_categoria;
    document.getElementById('producto-estado').value = producto.estado_conservacion;
    document.getElementById('producto-certificado').value = producto.certificado || '';
    document.getElementById('producto-imagen').value = '';
    seccionEditarProducto.style.display = 'block';
    seccionEditarProducto.scrollIntoView({ behavior: 'smooth' });
}

document.getElementById('cancelar-producto').addEventListener('click', function () {
    formularioProducto.reset();
    seccionEditarProducto.style.display = 'none';
});

formularioProducto.addEventListener('submit', function (evento) {
    evento.preventDefault();

    fetch('backend/usuario-producto-guardar.php', {
        method: 'POST',
        body: new FormData(formularioProducto)
    })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (datos) {
            if (datos.correcto == false) {
                mostrarMensaje(datos.mensaje, 'error');
                return;
            }
            formularioProducto.reset();
            seccionEditarProducto.style.display = 'none';
            mostrarMensaje(datos.mensaje, 'exito');
            cargarDatos();
        })
        .catch(function () { mostrarMensaje('No se pudo actualizar el producto.', 'error'); });
});

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

    if (!confirmacion.isConfirmed) { return; }

    const datos = new FormData();
    datos.append('id', id);

    fetch('backend/usuario-producto-eliminar.php', { method: 'POST', body: datos })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (resultado) {
            if (resultado.correcto == false) {
                mostrarMensaje(resultado.mensaje, 'error');
                return;
            }
            mostrarMensaje(resultado.mensaje || 'Producto retirado correctamente.', 'exito');
            cargarDatos();
        })
        .catch(function () { mostrarMensaje('No se pudo eliminar el producto.', 'error'); });
}

function mostrarNotificaciones(notificaciones) {
    const contenedor = document.getElementById('usuario-notificaciones');
    contenedor.innerHTML = '';

    if (notificaciones.length == 0) {
        contenedor.innerHTML = '<p>No tienes notificaciones.</p>';
        return;
    }

    notificaciones.forEach(function (notificacion) {
        const texto = document.createElement('p');
        texto.textContent = notificacion.mensaje + ' - ' + notificacion.fecha;
        contenedor.appendChild(texto);
    });
}

function mostrarOrdenes(ordenes, idContenedor, tipo) {
    const contenedor = document.getElementById(idContenedor);
    contenedor.innerHTML = '';

    if (ordenes.length == 0) {
        contenedor.innerHTML = '<p>No hay movimientos para mostrar.</p>';
        return;
    }

    ordenes.forEach(function (orden) {
        const tarjeta = document.createElement('article');
        tarjeta.className = 'usuario-orden-item';

        const titulo = document.createElement('h3');
        titulo.textContent = 'Orden #' + orden.id_orden + ' - ' + orden.producto;
        tarjeta.appendChild(titulo);

        const persona = document.createElement('p');
        persona.textContent = (tipo == 'compra' ? 'Vendedor: ' : 'Comprador: ') + orden.otra_persona;
        tarjeta.appendChild(persona);

        const precio = document.createElement('p');
        precio.textContent = 'Precio: $' + Number(orden.precio_unitario).toLocaleString('es-UY');
        tarjeta.appendChild(precio);

        const estado = document.createElement('p');
        estado.textContent = 'Estado: ' + nombreEstado(orden.estado);
        tarjeta.appendChild(estado);

        if (orden.codigo_seguimiento) {
            const seguimiento = document.createElement('p');
            seguimiento.textContent = 'Seguimiento: ' + orden.codigo_seguimiento;
            tarjeta.appendChild(seguimiento);
        }

        if (tipo == 'venta' && orden.estado == 'pagado') {
            const enviar = crearBoton('Marcar enviado', 'usuario-btn-editar');
            enviar.addEventListener('click', function () { marcarEnviado(orden.id_orden); });
            tarjeta.appendChild(enviar);
        }

        if (tipo == 'compra' && orden.estado == 'verificado') {
            const recibir = crearBoton('Confirmar recepcion', 'usuario-btn-editar');
            recibir.addEventListener('click', function () { accionOrden('backend/confirmar-recepcion.php', orden.id_orden); });
            tarjeta.appendChild(recibir);
        }

        if (tipo == 'compra' && ['pagado', 'enviado', 'verificado'].includes(orden.estado)) {
            const disputar = crearBoton('Abrir disputa', 'usuario-btn-eliminar');
            disputar.addEventListener('click', function () { abrirDisputa(orden.id_orden); });
            tarjeta.appendChild(disputar);
        }

        if (orden.estado == 'liberado' && orden.calificada == 0) {
            const calificar = crearBoton('Calificar', 'usuario-btn-editar');
            calificar.addEventListener('click', function () { calificarOrden(orden.id_orden); });
            tarjeta.appendChild(calificar);
        }

        contenedor.appendChild(tarjeta);
    });
}

function nombreEstado(estado) {
    return estado.replaceAll('_', ' ');
}

async function marcarEnviado(idOrden) {
    const resultado = await Swal.fire({
        title: 'Marcar como enviado',
        input: 'text',
        inputLabel: 'Codigo de seguimiento',
        inputPlaceholder: 'Ejemplo: ENVIO-123',
        showCancelButton: true,
        confirmButtonText: 'Guardar envio',
        cancelButtonText: 'Cancelar',
        inputValidator: function (valor) {
            if (!valor) { return 'Ingresa el codigo de seguimiento.'; }
        }
    });

    if (resultado.isConfirmed) {
        accionOrden('backend/marcar-enviado.php', idOrden, 'seguimiento', resultado.value);
    }
}

async function abrirDisputa(idOrden) {
    const resultado = await Swal.fire({
        title: 'Abrir disputa',
        input: 'textarea',
        inputLabel: 'Explica el problema con la compra',
        showCancelButton: true,
        confirmButtonText: 'Abrir disputa',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#a00000',
        inputValidator: function (valor) {
            if (!valor) { return 'Escribe el motivo de la disputa.'; }
        }
    });

    if (resultado.isConfirmed) {
        accionOrden('backend/abrir-disputa.php', idOrden, 'motivo', resultado.value);
    }
}

async function calificarOrden(idOrden) {
    const calificacion = await Swal.fire({
        title: 'Calificar usuario',
        input: 'select',
        inputOptions: {
            1: '1 estrella',
            2: '2 estrellas',
            3: '3 estrellas',
            4: '4 estrellas',
            5: '5 estrellas'
        },
        inputPlaceholder: 'Selecciona una calificacion',
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Cancelar',
        inputValidator: function (valor) {
            if (!valor) { return 'Selecciona una calificacion.'; }
        }
    });

    if (!calificacion.isConfirmed) { return; }

    const comentarioResultado = await Swal.fire({
        title: 'Comentario',
        input: 'textarea',
        inputLabel: 'Puedes dejar un comentario opcional',
        showCancelButton: true,
        confirmButtonText: 'Guardar calificacion',
        cancelButtonText: 'Cancelar'
    });

    if (!comentarioResultado.isConfirmed) { return; }

    const datos = new FormData();
    datos.append('id_orden', idOrden);
    datos.append('puntaje', calificacion.value);
    datos.append('comentario', comentarioResultado.value || '');
    enviarAccion('backend/calificar.php', datos);
}

function accionOrden(url, idOrden, campoExtra, valorExtra) {
    const datos = new FormData();
    datos.append('id_orden', idOrden);
    if (campoExtra) { datos.append(campoExtra, valorExtra); }
    enviarAccion(url, datos);
}

function enviarAccion(url, datos) {
    fetch(url, { method: 'POST', body: datos })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (resultado) {
            mostrarMensaje(resultado.mensaje, resultado.correcto ? 'exito' : 'error');
            if (resultado.correcto) { cargarDatos(); }
        })
        .catch(function () { mostrarMensaje('No se pudo completar la accion.', 'error'); });
}

function mostrarMensaje(texto, tipo) {
    mensajeUsuario.textContent = texto;
    mensajeUsuario.className = 'usuario-mensaje ' + tipo;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

const parametros = new URLSearchParams(window.location.search);
if (parametros.get('publicado') == 'si') {
    mostrarMensaje('Producto publicado correctamente.', 'exito');
}
