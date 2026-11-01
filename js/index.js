const menuInvitado = document.getElementById('menu-invitado');
const menuUsuario = document.getElementById('menu-usuario');
const nombreUsuario = document.getElementById('nombre-usuario');
const enlaceAdmin = document.getElementById('enlace-admin');
const botonMenuUsuario = document.getElementById('boton-menu-usuario');
const opcionesUsuario = document.getElementById('opciones-usuario');

fetch('backend/sesion.php')
    .then(function (respuesta) {
        return respuesta.json();
    })
    .then(function (datos) {
        if (datos.sesion == true) {
            menuInvitado.style.display = 'none';
            menuUsuario.style.display = 'flex';
            nombreUsuario.textContent = datos.nombre;

            if (datos.esAdmin == true) {
                enlaceAdmin.style.display = 'inline';
            }
        }
    });

botonMenuUsuario.addEventListener('click', function () {
    opcionesUsuario.classList.toggle('abierto');
});

document.addEventListener('click', function (evento) {
    if (!evento.target.closest('.usuario-desplegable')) {
        opcionesUsuario.classList.remove('abierto');
    }
});

document.querySelector('.login-nav').addEventListener('click', function () {
    window.location.href = 'login.html';
});

document.querySelector('.signup-nav').addEventListener('click', function () {
    window.location.href = 'signup.html';
});

const catalogo = document.getElementById('catalogo');

fetch('backend/catalog.php')
    .then(function (respuesta) {
        return respuesta.json();
    })
    .then(function (productos) {
        productos.forEach(function (producto) {
            const tarjeta = document.createElement('div');
            tarjeta.className = 'productos-card';
            tarjeta.dataset.categoria = producto.id_categoria;
            tarjeta.dataset.precio = producto.precio;
            tarjeta.dataset.estado = producto.estado_conservacion;
            tarjeta.dataset.nombre = (producto.nombre + ' ' + producto.descripcion).toLowerCase();

            const imagen = document.createElement('img');
            imagen.alt = 'Producto deportivo';

            if (producto.imagen) {
                imagen.src = 'img/' + producto.imagen;
            } else {
                imagen.src = 'img/test-producto.jpg';
            }

            const nombre = document.createElement('h2');
            nombre.textContent = producto.nombre;

            const descripcion = document.createElement('p');
            descripcion.className = 'descripcion';
            descripcion.textContent = producto.descripcion;

            const vendedor = document.createElement('div');
            vendedor.className = 'vendedor';
            vendedor.textContent = 'Vendedor: ' + producto.vendedor + ' - Reputacion: ' + Number(producto.reputacion).toFixed(1) + '/5';

            const estado = document.createElement('div');
            estado.className = 'producto-estado';
            estado.textContent = 'Estado: ' + producto.estado_conservacion;

            const precio = document.createElement('div');
            precio.className = 'precio';
            precio.textContent = '$' + Number(producto.precio).toLocaleString('es-UY');

            const comprar = document.createElement('a');
            comprar.className = 'producto-comprar';
            comprar.href = 'compra.html?id=' + producto.id_producto;
            comprar.textContent = 'Comprar';

            tarjeta.appendChild(imagen);
            tarjeta.appendChild(nombre);
            tarjeta.appendChild(descripcion);
            tarjeta.appendChild(vendedor);
            tarjeta.appendChild(estado);
            tarjeta.appendChild(precio);
            tarjeta.appendChild(comprar);
            catalogo.appendChild(tarjeta);
        });
    });

const botonesCategorias = document.querySelectorAll('.categorias button');
let categoriaActual = 'todos';

botonesCategorias.forEach(function (boton) {
    boton.addEventListener('click', function () {
        categoriaActual = boton.dataset.categoria;

        botonesCategorias.forEach(function (otroBoton) {
            otroBoton.classList.remove('activo');
        });
        boton.classList.add('activo');

        filtrarProductos();
    });
});

document.getElementById('filtro-texto').addEventListener('input', filtrarProductos);
document.getElementById('filtro-minimo').addEventListener('input', filtrarProductos);
document.getElementById('filtro-maximo').addEventListener('input', filtrarProductos);
document.getElementById('filtro-estado').addEventListener('change', filtrarProductos);

function filtrarProductos() {
    const texto = document.getElementById('filtro-texto').value.toLowerCase();
    const minimo = Number(document.getElementById('filtro-minimo').value || 0);
    const maximo = Number(document.getElementById('filtro-maximo').value || 999999999);
    const estado = document.getElementById('filtro-estado').value;
    const tarjetas = document.querySelectorAll('.productos-card');

    tarjetas.forEach(function (tarjeta) {
        const coincideCategoria = categoriaActual == 'todos' || tarjeta.dataset.categoria == categoriaActual;
        const coincideTexto = tarjeta.dataset.nombre.includes(texto);
        const coincidePrecio = Number(tarjeta.dataset.precio) >= minimo && Number(tarjeta.dataset.precio) <= maximo;
        const coincideEstado = estado == 'todos' || tarjeta.dataset.estado == estado;

        if (coincideCategoria && coincideTexto && coincidePrecio && coincideEstado) {
            tarjeta.style.display = 'flex';
        } else {
            tarjeta.style.display = 'none';
        }
    });
}

document.querySelector('.catalog-btn').addEventListener('click', function () {
    catalogo.scrollIntoView();
});

const parametros = new URLSearchParams(window.location.search);

if (parametros.get('publicado') == 'si') {
    document.getElementById('mensaje-publicado').style.display = 'block';
}
