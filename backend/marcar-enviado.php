<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['correcto' => false, 'mensaje' => 'Debes iniciar sesion.']);
    exit;
}

$idOrden = $_POST['id_orden'] ?? '';
$seguimiento = trim($_POST['seguimiento'] ?? '');

if ($idOrden == '' || $seguimiento == '') {
    echo json_encode(['correcto' => false, 'mensaje' => 'Ingresa el codigo de seguimiento.']);
    exit;
}

require 'db.php';
require 'funciones.php';
$pdo = db();

$buscar = $pdo->prepare("SELECT ordenes.id_cop AS comprador_id FROM ordenes
    JOIN detalle_orden ON ordenes.id_orden = detalle_orden.id_orden
    JOIN productos ON detalle_orden.id_producto = productos.id_producto
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    WHERE ordenes.id_orden = ? AND publicaciones.id_cop = ? AND ordenes.estado = 'pagado'");
$buscar->execute([$idOrden, $_SESSION['usuario_id']]);
$orden = $buscar->fetch();

if (!$orden) {
    echo json_encode(['correcto' => false, 'mensaje' => 'La venta no se puede marcar como enviada.']);
    exit;
}

$actualizar = $pdo->prepare("UPDATE ordenes SET estado = 'enviado', codigo_seguimiento = ? WHERE id_orden = ?");
$actualizar->execute([$seguimiento, $idOrden]);
notificar($pdo, $orden['comprador_id'], 'La orden #' . $idOrden . ' fue enviada al intermediario.');
auditar($pdo, $_SESSION['usuario_id'], 'Orden #' . $idOrden . ' marcada como enviada.');

echo json_encode(['correcto' => true, 'mensaje' => 'Envio registrado correctamente.']);
