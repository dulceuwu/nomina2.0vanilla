<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
    $_SESSION['mensaje_flash'] = 'Debe iniciar sesion para acceder a esta pagina.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('../index.php');
    exit;
}

function requiereRol($rol_requerido) {
    if ($_SESSION['rol'] !== $rol_requerido) {
        $_SESSION['mensaje_flash'] = 'No tiene permisos para acceder a esta pagina.';
        $_SESSION['mensaje_tipo'] = 'error';
        if ($_SESSION['rol'] === 'admin') {
            redirigir('../admin/dashboard.php');
        } else {
            redirigir('../app/dashboard.php');
        }
        exit;
    }
}
