<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit;
}

require 'db.php';

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = $_POST['precio'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$imagen = $_FILES['imagen'] ?? null;

if ($nombre == '' || $descripcion == '' || $precio == '' || $categoria == '') {
    header('Location: ../publicar.html?error=faltan-datos');
    exit;
}

if (!is_numeric($precio) || $precio <= 0) {
    header('Location: ../publicar.html?error=precio-invalido');
    exit;
}

if ($imagen == null || $imagen['error'] != 0) {
    header('Location: ../publicar.html?error=imagen-invalida');
    exit;
}

$extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
$extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($extension, $extensionesPermitidas) || getimagesize($imagen['tmp_name']) == false) {
    header('Location: ../publicar.html?error=imagen-invalida');
    exit;
}

$nombreImagen = time() . '_' . rand(1000, 9999) . '.' . $extension;
$rutaCarpeta = '../img/productos/';
$rutaCompleta = $rutaCarpeta . $nombreImagen;

if (!move_uploaded_file($imagen['tmp_name'], $rutaCompleta)) {
    header('Location: ../publicar.html?error=imagen-invalida');
    exit;
}

$pdo = db();

$guardarPublicacion = $pdo->prepare(
    "INSERT INTO publicaciones (descripcion, id_cop) VALUES (?, ?)"
);
$guardarPublicacion->execute([
    $descripcion,
    $_SESSION['usuario_id']
]);

$idPublicacion = $pdo->lastInsertId();


$guardarProducto = $pdo->prepare(
    "INSERT INTO productos (nombre, descripcion, precio, id_publi, id_categoria)
    VALUES (?, ?, ?, ?, ?)"
);
$guardarProducto->execute([
    $nombre,
    $descripcion,
    $precio,
    $idPublicacion,
    $categoria
]);

$idProducto = $pdo->lastInsertId();

$guardarImagen = $pdo->prepare(
    "INSERT INTO imagenes (id_producto, imagen) VALUES (?, ?)"
);
$guardarImagen->execute([
    $idProducto,
    'productos/' . $nombreImagen
]);

header('Location: ../index.php?publicado=si');
exit;
