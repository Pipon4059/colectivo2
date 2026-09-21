<?php
require 'db.php';

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$calle = trim($_POST['calle'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$depto = trim($_POST['depto'] ?? '');

if ($nombre == '' || $email == '' || $contrasena == '') {
    header('Location: ../signup.html?error=faltan-datos');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../signup.html?error=correo-invalido');
    exit;
}

if (strlen($contrasena) < 6) {
    header('Location: ../signup.html?error=contrasena-corta');
    exit;
}

if ($telefono != '' && !ctype_digit($telefono)) {
    header('Location: ../signup.html?error=telefono-invalido');
    exit;
}

$pdo = db();

$buscar = $pdo->prepare("SELECT id_cop FROM usuarios WHERE email = ?");
$buscar->execute([$email]);

if ($buscar->fetch()) {
    header('Location: ../signup.html?error=correo-repetido');
    exit;
}

$contrasenaSegura = password_hash($contrasena, PASSWORD_DEFAULT);

$guardar = $pdo->prepare("INSERT INTO usuarios
    (nombre, email, telefono, contrasena, calle, numero, depto)
    VALUES (?, ?, ?, ?, ?, ?, ?)");

$guardar->execute([
    $nombre,
    $email,
    $telefono,
    $contrasenaSegura,
    $calle,
    $numero,
    $depto
]);

$idUsuario = $pdo->lastInsertId();

$guardarCliente = $pdo->prepare("INSERT INTO clientes (id_cop, fecha_ingreso) VALUES (?, CURDATE())");
$guardarCliente->execute([$idUsuario]);

header('Location: ../login.html?registro=correcto');
exit;
