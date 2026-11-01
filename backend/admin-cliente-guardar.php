<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['correcto' => false, 'mensaje' => 'Acceso no permitido.']);
    exit;
}

require 'db.php';

$id = $_POST['id'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$tipo = $_POST['tipo'] ?? 'ambos';

if ($nombre == '' || $email == '') {
    echo json_encode(['correcto' => false, 'mensaje' => 'Completa el nombre y el correo.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['correcto' => false, 'mensaje' => 'El correo no es valido.']);
    exit;
}

if ($telefono != '' && !ctype_digit($telefono)) {
    echo json_encode(['correcto' => false, 'mensaje' => 'El telefono debe tener solo numeros.']);
    exit;
}

if (!in_array($tipo, ['comprador', 'vendedor', 'ambos', 'intermediario'])) {
    $tipo = 'ambos';
}

$pdo = db();
$buscar = $pdo->prepare("SELECT id_cop FROM usuarios WHERE email = ? AND id_cop != ?");
$buscar->execute([$email, $id == '' ? 0 : $id]);

if ($buscar->fetch()) {
    echo json_encode(['correcto' => false, 'mensaje' => 'Ese correo ya esta registrado.']);
    exit;
}

if ($id == '') {
    if (strlen($contrasena) < 6) {
        echo json_encode(['correcto' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.']);
        exit;
    }

    $contrasenaSegura = password_hash($contrasena, PASSWORD_DEFAULT);
    $pdo->beginTransaction();

    $guardarUsuario = $pdo->prepare(
        "INSERT INTO usuarios (nombre, email, telefono, contrasena) VALUES (?, ?, ?, ?)"
    );
    $guardarUsuario->execute([$nombre, $email, $telefono, $contrasenaSegura]);

    $idUsuario = $pdo->lastInsertId();
    if ($tipo == 'intermediario') {
        $guardarIntermediario = $pdo->prepare("INSERT INTO intermediarios (id_cop) VALUES (?)");
        $guardarIntermediario->execute([$idUsuario]);
    } else {
        $guardarCliente = $pdo->prepare(
            "INSERT INTO clientes (id_cop, fecha_ingreso, tipo) VALUES (?, CURDATE(), ?)"
        );
        $guardarCliente->execute([$idUsuario, $tipo]);
    }
    $pdo->commit();

    echo json_encode(['correcto' => true, 'mensaje' => 'Usuario agregado correctamente.']);
    exit;
}

$buscarCliente = $pdo->prepare("SELECT id_cop FROM clientes WHERE id_cop = ?");
$buscarCliente->execute([$id]);

if (!$buscarCliente->fetch()) {
    echo json_encode(['correcto' => false, 'mensaje' => 'El cliente no existe.']);
    exit;
}

if ($contrasena != '' && strlen($contrasena) < 6) {
    echo json_encode(['correcto' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

$actualizar = $pdo->prepare(
    "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, activo = 1 WHERE id_cop = ?"
);
$actualizar->execute([$nombre, $email, $telefono, $id]);

$actualizarTipo = $pdo->prepare("UPDATE clientes SET tipo = ? WHERE id_cop = ?");
$actualizarTipo->execute([$tipo == 'intermediario' ? 'ambos' : $tipo, $id]);

if ($contrasena != '') {
    $contrasenaSegura = password_hash($contrasena, PASSWORD_DEFAULT);
    $actualizarClave = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id_cop = ?");
    $actualizarClave->execute([$contrasenaSegura, $id]);
}

echo json_encode(['correcto' => true, 'mensaje' => 'Cliente actualizado correctamente.']);
