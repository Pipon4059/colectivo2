<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['correcto' => false, 'mensaje' => 'Acceso no permitido.']);
    exit;
}

$idDisputa = $_POST['id_disputa'] ?? '';
$decision = $_POST['decision'] ?? '';
$texto = trim($_POST['resolucion'] ?? '');

if (($decision != 'reembolsar' && $decision != 'liberar') || $texto == '') {
    echo json_encode(['correcto' => false, 'mensaje' => 'Completa la decision y la explicacion.']);
    exit;
}

require 'db.php';
require 'funciones.php';
$pdo = db();

$buscar = $pdo->prepare("SELECT disputas.id_orden, ordenes.id_cop AS comprador_id,
    publicaciones.id_cop AS vendedor_id FROM disputas
    JOIN ordenes ON disputas.id_orden = ordenes.id_orden
    JOIN detalle_orden ON ordenes.id_orden = detalle_orden.id_orden
    JOIN productos ON detalle_orden.id_producto = productos.id_producto
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    WHERE disputas.id_disputa = ? AND disputas.estado = 'abierta'");
$buscar->execute([$idDisputa]);
$disputa = $buscar->fetch();

if (!$disputa) {
    echo json_encode(['correcto' => false, 'mensaje' => 'La disputa no existe o ya fue resuelta.']);
    exit;
}

$nuevoEstado = $decision == 'reembolsar' ? 'reembolsado' : 'liberado';
$resolver = $pdo->prepare("UPDATE disputas SET estado = 'resuelta', resolucion = ? WHERE id_disputa = ?");
$resolver->execute([$texto, $idDisputa]);
$actualizar = $pdo->prepare("UPDATE ordenes SET estado = ? WHERE id_orden = ?");
$actualizar->execute([$nuevoEstado, $disputa['id_orden']]);

$mensaje = 'La disputa de la orden #' . $disputa['id_orden'] . ' fue resuelta: ' . $texto;
notificar($pdo, $disputa['comprador_id'], $mensaje);
notificar($pdo, $disputa['vendedor_id'], $mensaje);
auditar($pdo, $_SESSION['admin_id'], 'Disputa resuelta. Orden #' . $disputa['id_orden']);

echo json_encode(['correcto' => true, 'mensaje' => 'Disputa resuelta correctamente.']);
