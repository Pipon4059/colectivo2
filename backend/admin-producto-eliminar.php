<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['correcto' => false, 'mensaje' => 'Acceso no permitido.']);
    exit;
}

require 'db.php';

$id = $_POST['id'] ?? '';
$pdo = db();
$buscar = $pdo->prepare("SELECT id_publi FROM productos WHERE id_producto = ?");
$buscar->execute([$id]);
$producto = $buscar->fetch();

if (!$producto) {
    echo json_encode(['correcto' => false, 'mensaje' => 'El producto no existe.']);
    exit;
}

$retirar = $pdo->prepare("UPDATE productos SET disponible = 0 WHERE id_producto = ?");
$retirar->execute([$id]);

echo json_encode(['correcto' => true, 'mensaje' => 'Producto retirado del catalogo.']);
