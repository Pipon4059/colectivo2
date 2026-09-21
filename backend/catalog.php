<?php
require 'db.php';

$pdo = db();

$stmt = $pdo->query("SELECT * FROM productos");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
