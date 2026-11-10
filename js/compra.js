const parametrosCompra = new URLSearchParams(window.location.search);
const idProducto = parametrosCompra.get('id');
const paginaCompra = document.getElementById('compra-pagina');
const formularioCompra = document.getElementById('form-compra');
const mensajeCompra = document.getElementById('compra-mensaje');

if (!idProducto) {
    mostrarError('No se eligio ningun producto.');
} else {
    verificarSesion();
}

function verificarSesion() {
    fetch('backend/sesion.php')
        .then(function (respuesta) {
            return respuesta.json();
        })
        .then(function (datos) {
            if (datos.sesion == false) {
                window.location.href = 'login.html';
                return;
            }

            cargarProducto();
        });
}

function cargarProducto() {
    fetch('backend/catalog.php')
        .then(function (respuesta) {
            return respuesta.json();
        })
        .then(function (productos) {
            const producto = productos.find(function (item) {
                return item.id_producto == idProducto;
            });

            if (!producto) {
                mostrarError('El producto no existe.');
                return;
            }

            document.getElementById('compra-id').value = producto.id_producto;
            document.getElementById('compra-nombre').textContent = producto.nombre;
            document.getElementById('compra-descripcion').textContent = producto.descripcion;
            document.getElementById('compra-vendedor').textContent = 'Vendedor: ' + producto.vendedor;
            document.getElementById('compra-precio').textContent = '$' + Number(producto.precio).toLocaleString('es-UY');

            if (producto.imagen) {
                document.getElementById('compra-imagen').src = 'img/' + producto.imagen;
            }

            paginaCompra.style.display = 'grid';
        });
}

formularioCompra.addEventListener('submit', function (evento) {
    evento.preventDefault();

    const datosFormulario = new FormData(formularioCompra);

    fetch('backend/comprar.php', {
        method: 'POST',
        body: datosFormulario
    })
        .then(function (respuesta) {
            return respuesta.json();
        })
        .then(function (datos) {
            if (datos.error) {
                mostrarError(datos.error);
                return;
            }

            mensajeCompra.className = 'compra-mensaje exito';
            mensajeCompra.textContent = datos.mensaje + ' Numero de orden: ' + datos.orden;
            formularioCompra.style.display = 'none';
        })
        .catch(function () {
            mostrarError('No se pudo realizar la compra.');
        });
});

function mostrarError(texto) {
    paginaCompra.style.display = 'grid';
    mensajeCompra.className = 'compra-mensaje error';
    mensajeCompra.textContent = texto;
}
