<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['correcto' => false, 'mensaje' => 'Debes iniciar sesion.']);
    exit;
}

$idOrden = $_POST['id_orden'] ?? '';
require 'db.php';
require 'funciones.php';
$pdo = db();

$buscar = $pdo->prepare("SELECT publicaciones.id_cop AS vendedor_id FROM ordenes
    JOIN detalle_orden ON ordenes.id_orden = detalle_orden.id_orden
    JOIN productos ON detalle_orden.id_producto = productos.id_producto
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    WHERE ordenes.id_orden = ? AND ordenes.id_cop = ? AND ordenes.estado = 'verificado'");
$buscar->execute([$idOrden, $_SESSION['usuario_id']]);
$orden = $buscar->fetch();

if (!$orden) {
    echo json_encode(['correcto' => false, 'mensaje' => 'La orden no se puede confirmar.']);
    exit;
}

$actualizar = $pdo->prepare("UPDATE ordenes SET estado = 'liberado' WHERE id_orden = ?");
$actualizar->execute([$idOrden]);
notificar($pdo, $orden['vendedor_id'], 'El comprador confirmo la orden #' . $idOrden . '. Pago liberado.');
auditar($pdo, $_SESSION['usuario_id'], 'Recepcion confirmada. Orden #' . $idOrden);

echo json_encode(['correcto' => true, 'mensaje' => 'Recepcion confirmada y pago liberado.']);
