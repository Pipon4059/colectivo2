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
$buscar = $pdo->prepare("SELECT id_cop FROM clientes WHERE id_cop = ?");
$buscar->execute([$id]);

if (!$buscar->fetch()) {
    echo json_encode(['correcto' => false, 'mensaje' => 'El cliente no existe.']);
    exit;
}

$desactivar = $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id_cop = ?");
$desactivar->execute([$id]);
$retirarProductos = $pdo->prepare("UPDATE productos
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    SET productos.disponible = 0 WHERE publicaciones.id_cop = ?");
$retirarProductos->execute([$id]);

echo json_encode(['correcto' => true, 'mensaje' => 'Cliente desactivado correctamente.']);
