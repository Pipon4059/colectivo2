<?php
session_start();

require 'backend/db.php';

$pdo = db();

$consulta = $pdo->query("SELECT productos.nombre, productos.descripcion, productos.precio,
    productos.id_categoria,
    usuarios.nombre AS vendedor,
    (SELECT imagen FROM imagenes WHERE imagenes.id_producto = productos.id_producto LIMIT 1) AS imagen
    FROM productos
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    JOIN usuarios ON publicaciones.id_cop = usuarios.id_cop
    ORDER BY productos.id_producto DESC");

$productos = $consulta->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El colectivo | Login</title>
    <link rel="stylesheet" href="css/styles.css?v=11">
    <script src="https://kit.fontawesome.com/f263054d87.js" crossorigin="anonymous"></script>
</head>
<body>
    <main>
        <header class="bg-primary flex header">
            <div class="width header-in">

                <div>
                    <a href="index.php"> <img width="100px" src="img/elcolectivo-logo.png" alt=""></a>
                </div>
                <div>
                    <?php if (isset($_SESSION['usuario_id'])) { ?>
                        <div class="usuario-menu">
                            <span class="usuario-nav">Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                            <?php if (isset($_SESSION['admin_id'])) { ?>
                                <a class="publicar-nav" href="admin.html">Panel admin</a>
                            <?php } ?>
                            <a class="publicar-nav" href="publicar.html">Publicar producto</a>
                            <a class="logout-nav" href="backend/logout.php">Cerrar sesion</a>
                        </div>
                    <?php } else { ?>
                        <button class="login-nav">Ingresar</button>
                        <button class="signup-nav">Crear cuenta</button>
                    <?php } ?>
                </div>
            </div>
        </header>
        <?php if (isset($_GET['publicado']) && $_GET['publicado'] == 'si') { ?>
            <div class="mensaje-publicado width">Producto publicado correctamente.</div>
        <?php } ?>
        <section class="market-inicio">
            <div class="main-card width">
                <p></p>
                <h1>Tu pasion tambien se colecciona</h1>
                <div style="width: 100%;">
                    <p>Encontrá camisetas, pelotas, figuritas y recuerdos deportivos publicados por otros coleccionistas.</p>
                    <button class="catalog-btn">Explorar catalogo</button>
                </div>
            </div>
        </section>
        <section class="categorias-container">
            <div class="categorias width">
                <button class="activo" data-categoria="todos">Todos</button>
                <button data-categoria="1">Camisetas</button>
                <button data-categoria="2">Pelotas</button>
                <button data-categoria="3">Figuritas</button>
                <button data-categoria="4">Botines</button>
                <button data-categoria="5">Otros</button>
            </div>
        </section>
        <section class="productos-container">
            <div id="catalogo" class="productos width">
                <?php foreach ($productos as $producto) { ?>
                    <div class="productos-card" data-categoria="<?php echo $producto['id_categoria']; ?>">
                        <?php if ($producto['imagen']) { ?>
                            <img src="img/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Producto deportivo">
                        <?php } else { ?>
                            <img src="img/test-producto.jpg" alt="Producto deportivo">
                        <?php } ?>
                        <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                        <p class="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        <div class="vendedor">
                            <span>Vendedor: </span>
                            <span><?php echo htmlspecialchars($producto['vendedor']); ?></span>
                        </div>
                        <div class="precio">
                            $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
    </main>
    <script>
        const loginBtn = document.querySelector('.login-nav');
        const signupBtn = document.querySelector('.signup-nav');
        if (loginBtn) {
            loginBtn.addEventListener('click', () => {
                window.location.href = 'login.html';
            });
        }

        if (signupBtn) {
            signupBtn.addEventListener('click', () => {
                window.location.href = 'signup.html';
            });
        }

        const botonesCategorias = document.querySelectorAll('.categorias button');
        const tarjetasProductos = document.querySelectorAll('.productos-card');

        botonesCategorias.forEach(function (boton) {
            boton.addEventListener('click', function () {
                const categoriaElegida = boton.dataset.categoria;

                botonesCategorias.forEach(function (otroBoton) {
                    otroBoton.classList.remove('activo');
                });
                boton.classList.add('activo');

                tarjetasProductos.forEach(function (tarjeta) {
                    if (categoriaElegida == 'todos' || tarjeta.dataset.categoria == categoriaElegida) {
                        tarjeta.style.display = 'flex';
                    } else {
                        tarjeta.style.display = 'none';
                    }
                });
            });
        });

        const explorarBtn = document.querySelector('.catalog-btn');

        explorarBtn.addEventListener('click', function () {
            document.getElementById('catalogo').scrollIntoView();
        });
    </script>
</body>
</html>
