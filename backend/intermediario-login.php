<?php
require_once 'iniciar-sesion.php';
require 'db.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$pdo = db();

$buscar = $pdo->prepare("SELECT usuarios.* FROM usuarios
    JOIN intermediarios ON usuarios.id_cop = intermediarios.id_cop
    WHERE usuarios.email = ? AND usuarios.activo = 1");
$buscar->execute([$email]);
$usuario = $buscar->fetch();

if ($usuario && password_verify($password, $usuario['contrasena'])) {
    session_regenerate_id(true);
    $_SESSION['intermediario_id'] = $usuario['id_cop'];
    $_SESSION['intermediario_nombre'] = $usuario['nombre'];
    header('Location: ../intermediario.html');
    exit;
}

header('Location: ../intermediario-login.html?error=datos-incorrectos');
exit;
