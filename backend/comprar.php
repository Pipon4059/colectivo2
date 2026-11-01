<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Debes iniciar sesion para comprar.']);
    exit;
}

$idProducto = $_POST['id_producto'] ?? '';
$direccion = trim($_POST['direccion'] ?? '');
$pago = $_POST['pago'] ?? '';

if ($idProducto == '' || $direccion == '' || $pago == '') {
    echo json_encode(['error' => 'Debes completar todos los campos.']);
    exit;
}

if ($pago != 'efectivo' && $pago != 'transferencia') {
    echo json_encode(['error' => 'La forma de pago no es valida.']);
    exit;
}

require 'db.php';
require 'funciones.php';
$pdo = db();

$buscarProducto = $pdo->prepare("SELECT productos.id_producto, productos.nombre, productos.precio,
    productos.disponible, publicaciones.id_cop AS vendedor_id
    FROM productos
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    WHERE productos.id_producto = ?");
$buscarProducto->execute([$idProducto]);
$producto = $buscarProducto->fetch();

if (!$producto) {
    echo json_encode(['error' => 'El producto no existe.']);
    exit;
}

if ($producto['disponible'] == 0) {
    echo json_encode(['error' => 'El producto ya no esta disponible.']);
    exit;
}

if ($producto['vendedor_id'] == $_SESSION['usuario_id']) {
    echo json_encode(['error' => 'No puedes comprar tu propio producto.']);
    exit;
}

$buscarCliente = $pdo->prepare("SELECT id_cop, tipo FROM clientes WHERE id_cop = ?");
$buscarCliente->execute([$_SESSION['usuario_id']]);
$cliente = $buscarCliente->fetch();

if (!$cliente || $cliente['tipo'] == 'vendedor') {
    echo json_encode(['error' => 'Esta cuenta no tiene permiso de comprador.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $guardarOrden = $pdo->prepare("INSERT INTO ordenes
        (estado, id_cop, direccion, medio_pago) VALUES ('pagado', ?, ?, ?)");
    $guardarOrden->execute([$_SESSION['usuario_id'], $direccion, $pago]);
    $idOrden = $pdo->lastInsertId();

    $guardarDetalle = $pdo->prepare("INSERT INTO detalle_orden
        (precio_unitario, cantidad, id_orden, id_producto)
        VALUES (?, 1, ?, ?)");
    $guardarDetalle->execute([$producto['precio'], $idOrden, $producto['id_producto']]);

    $marcarVendido = $pdo->prepare("UPDATE productos SET disponible = 0 WHERE id_producto = ?");
    $marcarVendido->execute([$producto['id_producto']]);

    notificar($pdo, $_SESSION['usuario_id'], 'Tu pago simulado quedo retenido. Orden #' . $idOrden);
    notificar($pdo, $producto['vendedor_id'], 'Vendiste ' . $producto['nombre'] . '. Debes marcar el envio.');
    auditar($pdo, $_SESSION['usuario_id'], 'Compra simulada creada. Orden #' . $idOrden);

    $pdo->commit();
} catch (Exception $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => 'No se pudo guardar la compra.']);
    exit;
}

echo json_encode([
    'correcto' => true,
    'orden' => $idOrden,
    'mensaje' => 'El pago simulado quedo retenido.'
]);
