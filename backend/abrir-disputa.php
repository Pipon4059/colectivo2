<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['correcto' => false, 'mensaje' => 'Debes iniciar sesion.']);
    exit;
}

$idOrden = $_POST['id_orden'] ?? '';
$motivo = trim($_POST['motivo'] ?? '');

if ($motivo == '') {
    echo json_encode(['correcto' => false, 'mensaje' => 'Escribe el motivo de la disputa.']);
    exit;
}

require 'db.php';
require 'funciones.php';
$pdo = db();

$buscar = $pdo->prepare("SELECT publicaciones.id_cop AS vendedor_id FROM ordenes
    JOIN detalle_orden ON ordenes.id_orden = detalle_orden.id_orden
    JOIN productos ON detalle_orden.id_producto = productos.id_producto
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    WHERE ordenes.id_orden = ? AND ordenes.id_cop = ?
    AND ordenes.estado NOT IN ('liberado', 'reembolsado', 'cancelado', 'en_disputa')");
$buscar->execute([$idOrden, $_SESSION['usuario_id']]);
$orden = $buscar->fetch();

if (!$orden) {
    echo json_encode(['correcto' => false, 'mensaje' => 'No puedes abrir una disputa para esta orden.']);
    exit;
}

$guardar = $pdo->prepare("INSERT INTO disputas (id_orden, motivo) VALUES (?, ?)");
$guardar->execute([$idOrden, $motivo]);
$actualizar = $pdo->prepare("UPDATE ordenes SET estado = 'en_disputa' WHERE id_orden = ?");
$actualizar->execute([$idOrden]);
notificar($pdo, $orden['vendedor_id'], 'Se abrio una disputa para la orden #' . $idOrden);
auditar($pdo, $_SESSION['usuario_id'], 'Disputa abierta. Orden #' . $idOrden);

echo json_encode(['correcto' => true, 'mensaje' => 'Disputa abierta correctamente.']);
