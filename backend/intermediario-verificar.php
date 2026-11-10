<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['intermediario_id'])) {
    echo json_encode(['correcto' => false, 'mensaje' => 'Acceso no permitido.']);
    exit;
}

$idOrden = $_POST['id_orden'] ?? '';
$resultado = $_POST['resultado'] ?? '';
$observacion = trim($_POST['observacion'] ?? '');
$seguimientoComprador = trim($_POST['seguimiento_comprador'] ?? '');

if ($resultado != 'aprobado' && $resultado != 'rechazado') {
    echo json_encode(['correcto' => false, 'mensaje' => 'Selecciona un resultado valido.']);
    exit;
}

if ($resultado == 'aprobado' && $seguimientoComprador == '') {
    echo json_encode(['correcto' => false, 'mensaje' => 'Ingresa el seguimiento del envio al comprador.']);
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
    WHERE ordenes.id_orden = ? AND ordenes.estado IN ('enviado', 'en_verificacion')");
$buscar->execute([$idOrden]);
$orden = $buscar->fetch();

if (!$orden) {
    echo json_encode(['correcto' => false, 'mensaje' => 'La orden ya no esta disponible para verificar.']);
    exit;
}

$guardar = $pdo->prepare("INSERT INTO verificaciones
    (id_orden, id_intermediario, resultado, observacion) VALUES (?, ?, ?, ?)");
$guardar->execute([$idOrden, $_SESSION['intermediario_id'], $resultado, $observacion]);

if ($resultado == 'aprobado') {
    $nuevoEstado = 'verificado';
    $mensaje = 'La orden #' . $idOrden . ' fue verificada y enviada al comprador. Seguimiento: ' . $seguimientoComprador;
} else {
    $nuevoEstado = 'en_disputa';
    $mensaje = 'La orden #' . $idOrden . ' fue rechazada y paso a disputa.';
    $motivo = $observacion == '' ? 'Producto rechazado por el intermediario.' : $observacion;
    $crearDisputa = $pdo->prepare("INSERT INTO disputas (id_orden, motivo) VALUES (?, ?)");
    $crearDisputa->execute([$idOrden, $motivo]);
}

$actualizar = $pdo->prepare("UPDATE ordenes SET estado = ?, codigo_seguimiento_comprador = ? WHERE id_orden = ?");
$actualizar->execute([$nuevoEstado, $resultado == 'aprobado' ? $seguimientoComprador : null, $idOrden]);
notificar($pdo, $orden['comprador_id'], $mensaje);
notificar($pdo, $orden['vendedor_id'], $mensaje);
auditar($pdo, $_SESSION['intermediario_id'], 'Verificacion ' . $resultado . '. Orden #' . $idOrden);

echo json_encode(['correcto' => true, 'mensaje' => 'Verificacion guardada correctamente.']);
