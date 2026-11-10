<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['intermediario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no permitido.']);
    exit;
}

require 'db.php';
$pdo = db();

$consulta = $pdo->query("SELECT ordenes.id_orden, ordenes.estado, ordenes.fecha,
    ordenes.codigo_seguimiento, ordenes.direccion, productos.nombre AS producto,
    comprador.nombre AS comprador, vendedor.nombre AS vendedor
    FROM ordenes
    JOIN detalle_orden ON ordenes.id_orden = detalle_orden.id_orden
    JOIN productos ON detalle_orden.id_producto = productos.id_producto
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    JOIN usuarios comprador ON ordenes.id_cop = comprador.id_cop
    JOIN usuarios vendedor ON publicaciones.id_cop = vendedor.id_cop
    WHERE ordenes.estado IN ('enviado', 'en_verificacion')
    ORDER BY ordenes.fecha ASC");

echo json_encode([
    'nombre' => $_SESSION['intermediario_nombre'],
    'ordenes' => $consulta->fetchAll()
]);
