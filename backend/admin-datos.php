<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no permitido.']);
    exit;
}

require 'db.php';
$pdo = db();

$clientes = $pdo->query("SELECT usuarios.id_cop, usuarios.nombre, usuarios.email,
    usuarios.telefono, usuarios.activo, clientes.fecha_ingreso, clientes.tipo,
    (SELECT COUNT(*) FROM publicaciones WHERE publicaciones.id_cop = usuarios.id_cop) AS publicaciones
    FROM clientes
    JOIN usuarios ON clientes.id_cop = usuarios.id_cop
    ORDER BY usuarios.id_cop DESC")->fetchAll();

$productos = $pdo->query("SELECT productos.id_producto, productos.nombre, productos.precio,
    productos.disponible, publicaciones.fecha_publicacion, usuarios.nombre AS vendedor
    FROM productos
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    JOIN usuarios ON publicaciones.id_cop = usuarios.id_cop
    ORDER BY productos.id_producto DESC")->fetchAll();

$ordenes = $pdo->query("SELECT ordenes.id_orden, ordenes.estado, ordenes.fecha,
    productos.nombre AS producto, comprador.nombre AS comprador, vendedor.nombre AS vendedor,
    detalle_orden.precio_unitario
    FROM ordenes
    JOIN detalle_orden ON ordenes.id_orden = detalle_orden.id_orden
    JOIN productos ON detalle_orden.id_producto = productos.id_producto
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    JOIN usuarios comprador ON ordenes.id_cop = comprador.id_cop
    JOIN usuarios vendedor ON publicaciones.id_cop = vendedor.id_cop
    ORDER BY ordenes.fecha DESC")->fetchAll();

$disputas = $pdo->query("SELECT disputas.id_disputa, disputas.id_orden, disputas.motivo,
    disputas.estado, disputas.resolucion, disputas.fecha
    FROM disputas ORDER BY disputas.fecha DESC")->fetchAll();

$intermediarios = $pdo->query("SELECT usuarios.nombre, usuarios.email, usuarios.activo
    FROM intermediarios JOIN usuarios ON intermediarios.id_cop = usuarios.id_cop
    ORDER BY usuarios.nombre")->fetchAll();

$auditoria = $pdo->query("SELECT auditoria.accion, auditoria.fecha,
    COALESCE(usuarios.nombre, 'Sistema') AS usuario
    FROM auditoria LEFT JOIN usuarios ON auditoria.id_cop = usuarios.id_cop
    ORDER BY auditoria.fecha DESC LIMIT 20")->fetchAll();

$estadisticas = [
    'clientes' => $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn(),
    'productos' => $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn(),
    'publicaciones' => $pdo->query("SELECT COUNT(*) FROM publicaciones")->fetchColumn(),
    'ordenes' => $pdo->query("SELECT COUNT(*) FROM ordenes")->fetchColumn()
];

echo json_encode([
    'clientes' => $clientes,
    'productos' => $productos,
    'ordenes' => $ordenes,
    'disputas' => $disputas,
    'intermediarios' => $intermediarios,
    'auditoria' => $auditoria,
    'estadisticas' => $estadisticas
]);
