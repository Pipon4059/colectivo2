<?php
session_start();
require 'db.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email == '' || $password == '') {
    header('Location: ../admin-login.html?error=faltan-datos');
    exit;
}

$pdo = db();

// Se busca el usuario y se comprueba que tambien sea administrador.
$buscar = $pdo->prepare("SELECT usuarios.* FROM usuarios
    JOIN administradores ON usuarios.id_cop = administradores.id_cop
    WHERE usuarios.email = ?");
$buscar->execute([$email]);
$admin = $buscar->fetch();

if ($admin && password_verify($password, $admin['contrasena'])) {
    $_SESSION['admin_id'] = $admin['id_cop'];
    $_SESSION['admin_nombre'] = $admin['nombre'];

    // Tambien se guarda como usuario para que el inicio reconozca la sesion.
    $_SESSION['usuario_id'] = $admin['id_cop'];
    $_SESSION['usuario_nombre'] = $admin['nombre'];

    header('Location: ../admin.html');
    exit;
}

header('Location: ../admin-login.html?error=datos-incorrectos');
exit;
