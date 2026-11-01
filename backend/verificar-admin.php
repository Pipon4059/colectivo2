<?php
require_once 'iniciar-sesion.php';
header('Content-Type: application/json');

if (isset($_SESSION['admin_id'])) {
    echo json_encode([
        'esAdmin' => true,
        'nombre' => $_SESSION['admin_nombre']
    ]);
    exit;
}

echo json_encode(['esAdmin' => false]);
