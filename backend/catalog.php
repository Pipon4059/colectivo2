<?php
require 'db.php';

$pdo = db();

$consulta = $pdo->query("SELECT productos.id_producto, productos.nombre, productos.descripcion, productos.precio,
    productos.id_categoria, productos.estado_conservacion, productos.certificado,
    usuarios.nombre AS vendedor,
    COALESCE((SELECT AVG(puntaje) FROM calificaciones WHERE id_calificado = usuarios.id_cop), 0) AS reputacion,
    (SELECT imagen FROM imagenes WHERE imagenes.id_producto = productos.id_producto LIMIT 1) AS imagen
    FROM productos
    JOIN publicaciones ON productos.id_publi = publicaciones.id_publi
    JOIN usuarios ON publicaciones.id_cop = usuarios.id_cop
    WHERE productos.disponible = 1 AND usuarios.activo = 1
    ORDER BY productos.id_producto DESC");

$productos = $consulta->fetchAll();

header('Content-Type: application/json');
echo json_encode($productos);
