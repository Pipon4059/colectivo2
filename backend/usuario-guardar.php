<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['correcto' => false, 'mensaje' => 'Debes iniciar sesion.']);
    exit;
}

require 'db.php';

$idUsuario = $_SESSION['usuario_id'];
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$calle = trim($_POST['calle'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$depto = trim($_POST['depto'] ?? '');
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

if ($contrasena != '' && strlen($contrasena) < 6) {
    echo json_encode(['correcto' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

if (!in_array($tipo, ['comprador', 'vendedor', 'ambos'])) {
    $tipo = 'ambos';
}

$pdo = db();
$buscarCorreo = $pdo->prepare("SELECT id_cop FROM usuarios WHERE email = ? AND id_cop != ?");
$buscarCorreo->execute([$email, $idUsuario]);

if ($buscarCorreo->fetch()) {
    echo json_encode(['correcto' => false, 'mensaje' => 'Ese correo ya esta registrado.']);
    exit;
}

$actualizar = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, telefono = ?,
    calle = ?, numero = ?, depto = ? WHERE id_cop = ?");
$actualizar->execute([$nombre, $email, $telefono, $calle, $numero, $depto, $idUsuario]);

$actualizarTipo = $pdo->prepare("UPDATE clientes SET tipo = ? WHERE id_cop = ?");
$actualizarTipo->execute([$tipo, $idUsuario]);

if ($contrasena != '') {
    $contrasenaSegura = password_hash($contrasena, PASSWORD_DEFAULT);
    $actualizarClave = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id_cop = ?");
    $actualizarClave->execute([$contrasenaSegura, $idUsuario]);
}

$_SESSION['usuario_nombre'] = $nombre;
if (isset($_SESSION['admin_id'])) {
    $_SESSION['admin_nombre'] = $nombre;
}

echo json_encode(['correcto' => true, 'mensaje' => 'Perfil actualizado correctamente.']);
