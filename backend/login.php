<?php
require 'db.php';
require_once 'iniciar-sesion.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email == '' || $password == '') {
    header('Location: ../login.html?error=faltan-datos');
    exit;
}

$pdo = db();

$buscar = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
$buscar->execute([$email]);
$usuario = $buscar->fetch();

if ($usuario && password_verify($password, $usuario['contrasena'])) {
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id_cop'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];

    header('Location: ../index.html');
    exit;
}

header('Location: ../login.html?error=datos-incorrectos');
exit;
