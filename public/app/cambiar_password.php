<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('empleado');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('mi_perfil.php');
}

$datos = sanitizarArray($_POST);

if (!validarTokenCSRF($datos['csrf_token'] ?? '')) {
    $_SESSION['mensaje_flash'] = 'Error de seguridad.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('mi_perfil.php');
}

$password_actual = $datos['password_actual'];
$password_nueva = $datos['password_nueva'];
$password_confirmar = $datos['password_confirmar'];

if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
    $_SESSION['mensaje_flash'] = 'Todos los campos son obligatorios.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('mi_perfil.php');
}

if ($password_nueva !== $password_confirmar) {
    $_SESSION['mensaje_flash'] = 'Las nuevas contrasenas no coinciden.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('mi_perfil.php');
}

if (strlen($password_nueva) < 6) {
    $_SESSION['mensaje_flash'] = 'La contrasena debe tener al menos 6 caracteres.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('mi_perfil.php');
}

$user_id = (int)$_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT password_hash FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($hash_actual);
$stmt->fetch();
$stmt->close();

if (!password_verify($password_actual, $hash_actual)) {
    $_SESSION['mensaje_flash'] = 'La contrasena actual es incorrecta.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('mi_perfil.php');
}

$nuevo_hash = password_hash($password_nueva, PASSWORD_BCRYPT);
$stmt = $mysqli->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
$stmt->bind_param('si', $nuevo_hash, $user_id);

if ($stmt->execute()) {
    $_SESSION['mensaje_flash'] = 'Contrasena actualizada correctamente.';
    $_SESSION['mensaje_tipo'] = 'exito';
} else {
    $_SESSION['mensaje_flash'] = 'Error al actualizar la contrasena.';
    $_SESSION['mensaje_tipo'] = 'error';
}
$stmt->close();
redirigir('mi_perfil.php');
