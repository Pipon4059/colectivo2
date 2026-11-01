<?php
require_once 'iniciar-sesion.php';
unset($_SESSION['intermediario_id']);
unset($_SESSION['intermediario_nombre']);
header('Location: ../intermediario-login.html');
exit;
