<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'sesion' => true,
        'nombre' => $_SESSION['usuario_nombre'],
        'esAdmin' => isset($_SESSION['admin_id'])
    ]);
    exit;
}

echo json_encode(['sesion' => false]);
