<?php

if (session_status() == PHP_SESSION_NONE) {
    $usaHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';

    session_set_cookie_params([
        'httponly' => true,
        'secure' => $usaHttps,
        'samesite' => 'Lax'
    ]);

    session_start();

    if (isset($_SESSION['ultima_actividad']) && time() - $_SESSION['ultima_actividad'] > 1800) {
        session_unset();
        session_destroy();
        session_start();
    }

    $_SESSION['ultima_actividad'] = time();
}
