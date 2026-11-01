<?php

function notificar($pdo, $idUsuario, $mensaje)
{
    $guardar = $pdo->prepare("INSERT INTO notificaciones (id_cop, mensaje) VALUES (?, ?)");
    $guardar->execute([$idUsuario, $mensaje]);
}

function auditar($pdo, $idUsuario, $accion)
{
    $guardar = $pdo->prepare("INSERT INTO auditoria (id_cop, accion) VALUES (?, ?)");
    $guardar->execute([$idUsuario, $accion]);
}
