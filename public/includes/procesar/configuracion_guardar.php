<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../../admin/configuracion.php');
}

if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
    $_SESSION['mensaje_flash'] = 'Error de seguridad.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('../../admin/configuracion.php');
}

if (isset($_POST['restaurar'])) {
    $valores_defecto = [
        'UT' => 9.0000, 'SALARIO_MINIMO' => 130.0000, 'SSO_EMPLEADO_PORC' => 4.0000,
        'SSO_PATRONAL_PORC' => 11.0000, 'LRPE_EMPLEADO_PORC' => 0.5000,
        'LRPE_PATRONAL_PORC' => 2.0000, 'FAOV_PORC' => 2.0000,
        'TOPE_SSO_SALARIOS' => 5, 'TOPE_LRPE_SALARIOS' => 10, 'TOPE_FAOV_SALARIOS' => 10,
        'DIAS_HABILES_MES' => 22, 'CESTA_TICKET_PORC_UT' => 0.2500
    ];
    $stmt = $mysqli->prepare("UPDATE configuracion_legal SET valor = ? WHERE parametro = ?");
    foreach ($valores_defecto as $param => $val) {
        $stmt->bind_param('ds', $val, $param);
        $stmt->execute();
    }
    $stmt->close();
    $_SESSION['mensaje_flash'] = 'Valores restaurados a los valores por defecto.';
    $_SESSION['mensaje_tipo'] = 'exito';
    redirigir('../../admin/configuracion.php');
}

$nombres = $_POST['nombre_config'] ?? [];
$valores = $_POST['valor_config'] ?? [];

if (count($nombres) !== count($valores)) {
    $_SESSION['mensaje_flash'] = 'Error en los datos enviados.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('../../admin/configuracion.php');
}

$stmt = $mysqli->prepare("UPDATE configuracion_legal SET valor = ? WHERE parametro = ?");
$errores = 0;

foreach ($nombres as $i => $parametro) {
    $valor = (float)str_replace(',', '.', $valores[$i]);
    if (!is_numeric($valor) || $valor < 0) {
        $errores++;
        continue;
    }
    $stmt->bind_param('ds', $valor, $parametro);
    if (!$stmt->execute()) {
        $errores++;
    }
}
$stmt->close();

if ($errores === 0) {
    $_SESSION['mensaje_flash'] = 'Configuracion actualizada correctamente.';
    $_SESSION['mensaje_tipo'] = 'exito';
} else {
    $_SESSION['mensaje_flash'] = "Configuracion actualizada con $errores errores.";
    $_SESSION['mensaje_tipo'] = 'advertencia';
}

redirigir('../../admin/configuracion.php');
