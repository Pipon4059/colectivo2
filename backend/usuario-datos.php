<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Debes iniciar sesion.']);
    exit;
}

require 'db.php';
$pdo = db();
$idUsuario = $_SESSION['usuario_id'];

$buscarUsuario = $pdo->prepare("SELECT usuarios.nombre, usuarios.email, usuarios.telefono,
    usuarios.calle, usuarios.numero, usuarios.depto, clientes.tipo
    FROM usuarios LEFT JOIN clientes ON usuarios.id_cop = clientes.id_cop
    WHERE usuarios.id_cop = ?");
$buscarUsuario->execute([$idUsuario]);
$usuario = $buscarUsuario->fetch();

$buscarProductos = $pdo->prepare("SELECT productos.id_producto, productos.nombre,
    productos.descripcion, productos.precio, productos.id_categoria,
    productos.estado_conservacion, productos.certificado, productos.disponible,
    categorias.nombre AS categoria, publicaciones.fecha_publicacion,
    (SELECT imagen FROM imagenes WHERE imagenes.id_producto = productos.id_producto LIMIT 1) AS imagen
    FROM productos
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    JOIN categorias ON productos.id_categoria = categorias.id_categoria
    WHERE publicaciones.id_cop = ?
    ORDER BY productos.id_producto DESC");
$buscarProductos->execute([$idUsuario]);
$productos = $buscarProductos->fetchAll();

$buscarCompras = $pdo->prepare("SELECT ordenes.id_orden, ordenes.estado, ordenes.fecha,
    ordenes.codigo_seguimiento, productos.nombre AS producto, detalle_orden.precio_unitario,
    vendedor.nombre AS otra_persona,
    (SELECT COUNT(*) FROM calificaciones WHERE id_orden = ordenes.id_orden AND id_autor = ?) AS calificada
    FROM ordenes
    JOIN detalle_orden ON ordenes.id_orden = detalle_orden.id_orden
    JOIN productos ON detalle_orden.id_producto = productos.id_producto
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    JOIN usuarios vendedor ON publicaciones.id_cop = vendedor.id_cop
    WHERE ordenes.id_cop = ? ORDER BY ordenes.fecha DESC");
$buscarCompras->execute([$idUsuario, $idUsuario]);

$buscarVentas = $pdo->prepare("SELECT ordenes.id_orden, ordenes.estado, ordenes.fecha,
    ordenes.codigo_seguimiento, productos.nombre AS producto, detalle_orden.precio_unitario,
    comprador.nombre AS otra_persona,
    (SELECT COUNT(*) FROM calificaciones WHERE id_orden = ordenes.id_orden AND id_autor = ?) AS calificada
    FROM ordenes
    JOIN detalle_orden ON ordenes.id_orden = detalle_orden.id_orden
    JOIN productos ON detalle_orden.id_producto = productos.id_producto
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    JOIN usuarios comprador ON ordenes.id_cop = comprador.id_cop
    WHERE publicaciones.id_cop = ? ORDER BY ordenes.fecha DESC");
$buscarVentas->execute([$idUsuario, $idUsuario]);

$buscarNotificaciones = $pdo->prepare("SELECT mensaje, fecha FROM notificaciones
    WHERE id_cop = ? ORDER BY fecha DESC LIMIT 10");
$buscarNotificaciones->execute([$idUsuario]);

$buscarReputacion = $pdo->prepare("SELECT COALESCE(AVG(puntaje), 0) FROM calificaciones WHERE id_calificado = ?");
$buscarReputacion->execute([$idUsuario]);

echo json_encode([
    'usuario' => $usuario,
    'productos' => $productos,
    'compras' => $buscarCompras->fetchAll(),
    'ventas' => $buscarVentas->fetchAll(),
    'notificaciones' => $buscarNotificaciones->fetchAll(),
    'reputacion' => round($buscarReputacion->fetchColumn(), 1)
]);
