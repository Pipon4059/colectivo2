<?php
require_once 'iniciar-sesion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit;
}

require 'db.php';

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = $_POST['precio'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$estadoConservacion = trim($_POST['estado_conservacion'] ?? '');
$certificado = trim($_POST['certificado'] ?? '');
$imagenes = $_FILES['imagenes'] ?? null;

if ($nombre == '' || $descripcion == '' || $precio == '' || $categoria == '' || $estadoConservacion == '') {
    header('Location: ../publicar.html?error=faltan-datos');
    exit;
}

if (!is_numeric($precio) || $precio <= 0) {
    header('Location: ../publicar.html?error=precio-invalido');
    exit;
}

$pdo = db();

$buscarTipo = $pdo->prepare("SELECT tipo FROM clientes WHERE id_cop = ?");
$buscarTipo->execute([$_SESSION['usuario_id']]);
$cliente = $buscarTipo->fetch();
if (!$cliente || $cliente['tipo'] == 'comprador') {
    header('Location: ../publicar.html?error=sin-permiso');
    exit;
}

$buscarCategoria = $pdo->prepare("SELECT id_categoria FROM categorias WHERE id_categoria = ?");
$buscarCategoria->execute([$categoria]);

if (!$buscarCategoria->fetch()) {
    header('Location: ../publicar.html?error=faltan-datos');
    exit;
}

if ($imagenes == null || $imagenes['error'][0] != 0) {
    header('Location: ../publicar.html?error=imagen-invalida');
    exit;
}

$extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
$rutaCarpeta = '../img/productos/';

for ($i = 0; $i < count($imagenes['name']); $i++) {
    $extension = strtolower(pathinfo($imagenes['name'][$i], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensionesPermitidas) || getimagesize($imagenes['tmp_name'][$i]) == false) {
        header('Location: ../publicar.html?error=imagen-invalida');
        exit;
    }
}

$guardarPublicacion = $pdo->prepare(
    "INSERT INTO publicaciones (descripcion, id_cop) VALUES (?, ?)"
);
$guardarPublicacion->execute([
    $descripcion,
    $_SESSION['usuario_id']
]);

$idPublicacion = $pdo->lastInsertId();


$guardarProducto = $pdo->prepare(
    "INSERT INTO productos (nombre, descripcion, precio, id_publi, id_categoria,
    estado_conservacion, certificado) VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$guardarProducto->execute([
    $nombre,
    $descripcion,
    $precio,
    $idPublicacion,
    $categoria,
    $estadoConservacion,
    $certificado
]);

$idProducto = $pdo->lastInsertId();

for ($i = 0; $i < count($imagenes['name']); $i++) {
    $extension = strtolower(pathinfo($imagenes['name'][$i], PATHINFO_EXTENSION));
    $nombreImagen = time() . '_' . $i . '_' . rand(1000, 9999) . '.' . $extension;
    $rutaCompleta = $rutaCarpeta . $nombreImagen;

    if (move_uploaded_file($imagenes['tmp_name'][$i], $rutaCompleta)) {
        $guardarImagen = $pdo->prepare("INSERT INTO imagenes (id_producto, imagen) VALUES (?, ?)");
        $guardarImagen->execute([$idProducto, 'productos/' . $nombreImagen]);
    }
}

header('Location: ../usuario.html?publicado=si');
exit;
