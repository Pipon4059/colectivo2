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
$pdo = db();
$buscar = $pdo->prepare("SELECT productos.id_publi FROM productos
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    WHERE productos.id_producto = ? AND publicaciones.id_cop = ?");
$buscar->execute([$id, $_SESSION['usuario_id']]);
$producto = $buscar->fetch();

if (!$producto) {
    echo json_encode(['correcto' => false, 'mensaje' => 'El producto no existe o no te pertenece.']);
    exit;
}

$retirar = $pdo->prepare("UPDATE productos SET disponible = 0 WHERE id_producto = ?");
$retirar->execute([$id]);

echo json_encode(['correcto' => true, 'mensaje' => 'Producto retirado del catalogo.']);
