<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['correcto' => false, 'mensaje' => 'Debes iniciar sesion.']);
    exit;
}

$idOrden = $_POST['id_orden'] ?? '';
$puntaje = $_POST['puntaje'] ?? '';
$comentario = trim($_POST['comentario'] ?? '');

if (!is_numeric($puntaje) || $puntaje < 1 || $puntaje > 5) {
    echo json_encode(['correcto' => false, 'mensaje' => 'La calificacion debe ser de 1 a 5.']);
    exit;
}

require 'db.php';
require 'funciones.php';
$pdo = db();

$buscar = $pdo->prepare("SELECT ordenes.id_cop AS comprador_id, publicaciones.id_cop AS vendedor_id
    FROM ordenes
    JOIN detalle_orden ON ordenes.id_orden = detalle_orden.id_orden
    JOIN productos ON detalle_orden.id_producto = productos.id_producto
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    WHERE ordenes.id_orden = ? AND ordenes.estado = 'liberado'");
$buscar->execute([$idOrden]);
$orden = $buscar->fetch();

if (!$orden || ($_SESSION['usuario_id'] != $orden['comprador_id'] && $_SESSION['usuario_id'] != $orden['vendedor_id'])) {
    echo json_encode(['correcto' => false, 'mensaje' => 'No puedes calificar esta orden.']);
    exit;
}

$calificado = $_SESSION['usuario_id'] == $orden['comprador_id'] ? $orden['vendedor_id'] : $orden['comprador_id'];

try {
    $guardar = $pdo->prepare("INSERT INTO calificaciones
        (id_orden, id_autor, id_calificado, puntaje, comentario) VALUES (?, ?, ?, ?, ?)");
    $guardar->execute([$idOrden, $_SESSION['usuario_id'], $calificado, $puntaje, $comentario]);
} catch (Exception $error) {
    echo json_encode(['correcto' => false, 'mensaje' => 'Ya calificaste esta compra.']);
    exit;
}

notificar($pdo, $calificado, 'Recibiste una calificacion de ' . $puntaje . ' estrellas.');
echo json_encode(['correcto' => true, 'mensaje' => 'Calificacion guardada.']);
