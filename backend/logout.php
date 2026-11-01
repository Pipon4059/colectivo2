<?php
require_once 'iniciar-sesion.php';
session_destroy();

header('Location: ../index.html');
exit;
