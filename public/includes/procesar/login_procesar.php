<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../../index.php');
}

$datos = sanitizarArray($_POST);

if (empty($datos['email']) || empty($datos['password'])) {
    $_SESSION['mensaje_flash'] = 'Todos los campos son obligatorios.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('../../index.php');
}

if (!validarTokenCSRF($datos['csrf_token'] ?? '')) {
    $_SESSION['mensaje_flash'] = 'Error de seguridad. Intente nuevamente.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('../../index.php');
}

$email = $datos['email'];
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

$stmt = $mysqli->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip_address = ? AND exitoso = 0 AND intento_en > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
$stmt->bind_param('ss', $email, $ip);
$stmt->execute();
$stmt->bind_result($intentos);
$stmt->fetch();
$stmt->close();

if ($intentos >= 5) {
    $_SESSION['mensaje_flash'] = 'Demasiados intentos fallidos. Espere 15 minutos.';
    $_SESSION['mensaje_tipo'] = 'error';
    $stmt = $mysqli->prepare("INSERT INTO login_attempts (email, ip_address, exitoso) VALUES (?, ?, 0)");
    $stmt->bind_param('ss', $email, $ip);
    $stmt->execute();
    $stmt->close();
    redirigir('../../index.php');
}

$stmt = $mysqli->prepare("SELECT id, password_hash, nombres, apellidos, rol, activo FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $stmt = $mysqli->prepare("INSERT INTO login_attempts (email, ip_address, exitoso) VALUES (?, ?, 0)");
    $stmt->bind_param('ss', $email, $ip);
    $stmt->execute();
    $stmt->close();

    $_SESSION['mensaje_flash'] = 'Credenciales invalidas.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('../../index.php');
}

if ((int)$usuario['activo'] !== 1) {
    $stmt = $mysqli->prepare("INSERT INTO login_attempts (email, ip_address, exitoso) VALUES (?, ?, 0)");
    $stmt->bind_param('ss', $email, $ip);
    $stmt->execute();
    $stmt->close();

    $_SESSION['mensaje_flash'] = 'Su cuenta esta desactivada. Contacte al administrador.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('../../index.php');
}

if (!password_verify($datos['password'], $usuario['password_hash'])) {
    $stmt = $mysqli->prepare("INSERT INTO login_attempts (email, ip_address, exitoso) VALUES (?, ?, 0)");
    $stmt->bind_param('ss', $email, $ip);
    $stmt->execute();
    $stmt->close();

    $_SESSION['mensaje_flash'] = 'Credenciales invalidas.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('../../index.php');
}

$stmt = $mysqli->prepare("INSERT INTO login_attempts (email, ip_address, exitoso) VALUES (?, ?, 1)");
$stmt->bind_param('ss', $email, $ip);
$stmt->execute();
$stmt->close();

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$usuario['id'];
$_SESSION['user_nombre'] = $usuario['nombres'] . ' ' . $usuario['apellidos'];
$_SESSION['rol'] = $usuario['rol'];

if ($usuario['rol'] === 'admin') {
    redirigir('../../admin/dashboard.php');
} else {
    redirigir('../../app/dashboard.php');
}
