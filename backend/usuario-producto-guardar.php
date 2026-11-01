<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['correcto' => false, 'mensaje' => 'Debes iniciar sesion.']);
    exit;
}

require 'db.php';

$id = $_POST['id'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = $_POST['precio'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$estadoConservacion = trim($_POST['estado_conservacion'] ?? '');
$certificado = trim($_POST['certificado'] ?? '');
$imagen = $_FILES['imagen'] ?? null;

if ($id == '' || $nombre == '' || $descripcion == '' || $precio == '' || $categoria == '' || $estadoConservacion == '') {
    echo json_encode(['correcto' => false, 'mensaje' => 'Completa los datos del producto.']);
    exit;
}

if (!is_numeric($precio) || $precio <= 0) {
    echo json_encode(['correcto' => false, 'mensaje' => 'El precio debe ser mayor que cero.']);
    exit;
}

$pdo = db();
$buscar = $pdo->prepare("SELECT productos.id_producto, productos.id_publi
    FROM productos
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    WHERE productos.id_producto = ? AND publicaciones.id_cop = ?");
$buscar->execute([$id, $_SESSION['usuario_id']]);
$producto = $buscar->fetch();

if (!$producto) {
    echo json_encode(['correcto' => false, 'mensaje' => 'El producto no existe o no te pertenece.']);
    exit;
}

$buscarCategoria = $pdo->prepare("SELECT id_categoria FROM categorias WHERE id_categoria = ?");
$buscarCategoria->execute([$categoria]);
if (!$buscarCategoria->fetch()) {
    echo json_encode(['correcto' => false, 'mensaje' => 'La categoria no es valida.']);
    exit;
}

$hayImagenNueva = $imagen != null && $imagen['error'] != UPLOAD_ERR_NO_FILE;
$nombreImagen = '';

if ($hayImagenNueva) {
    if ($imagen['error'] != UPLOAD_ERR_OK) {
        echo json_encode(['correcto' => false, 'mensaje' => 'No se pudo subir la imagen.']);
        exit;
    }

    $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $permitidas) || getimagesize($imagen['tmp_name']) == false) {
        echo json_encode(['correcto' => false, 'mensaje' => 'La imagen debe ser JPG, PNG o WEBP.']);
        exit;
    }

    $nombreImagen = time() . '_' . rand(1000, 9999) . '.' . $extension;
    if (!move_uploaded_file($imagen['tmp_name'], '../img/productos/' . $nombreImagen)) {
        echo json_encode(['correcto' => false, 'mensaje' => 'No se pudo guardar la imagen.']);
        exit;
    }
}

$actualizarProducto = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?,
    precio = ?, id_categoria = ?, estado_conservacion = ?, certificado = ? WHERE id_producto = ?");
$actualizarProducto->execute([$nombre, $descripcion, $precio, $categoria, $estadoConservacion, $certificado, $id]);

$actualizarPublicacion = $pdo->prepare("UPDATE publicaciones SET descripcion = ? WHERE id_publi = ?");
$actualizarPublicacion->execute([$descripcion, $producto['id_publi']]);

if ($hayImagenNueva) {
    $buscarImagenes = $pdo->prepare("SELECT imagen FROM imagenes WHERE id_producto = ?");
    $buscarImagenes->execute([$id]);
    $imagenesAnteriores = $buscarImagenes->fetchAll();

    $eliminarImagenes = $pdo->prepare("DELETE FROM imagenes WHERE id_producto = ?");
    $eliminarImagenes->execute([$id]);

    $guardarImagen = $pdo->prepare("INSERT INTO imagenes (id_producto, imagen) VALUES (?, ?)");
    $guardarImagen->execute([$id, 'productos/' . $nombreImagen]);

    foreach ($imagenesAnteriores as $imagenAnterior) {
        $archivo = '../img/' . $imagenAnterior['imagen'];
        if (file_exists($archivo)) {
            unlink($archivo);
        }
    }
}

echo json_encode(['correcto' => true, 'mensaje' => 'Producto actualizado correctamente.']);
