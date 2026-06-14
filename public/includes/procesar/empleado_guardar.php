<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../../admin/empleados.php');
}

$datos = sanitizarArray($_POST);
$id = isset($datos['id']) ? (int)$datos['id'] : 0;
$editando = $id > 0;

if (!validarTokenCSRF($datos['csrf_token'] ?? '')) {
    $_SESSION['mensaje_flash'] = 'Error de seguridad. Intente nuevamente.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/empleado_formulario.php?id=$id" : '../../admin/empleado_formulario.php');
}

$campos_requeridos = ['cedula', 'nombres', 'apellidos', 'email'];
if (!$editando) {
    $campos_requeridos[] = 'password';
}

foreach ($campos_requeridos as $campo) {
    if (empty($datos[$campo])) {
        $_SESSION['mensaje_flash'] = 'Todos los campos obligatorios deben estar llenos.';
        $_SESSION['mensaje_tipo'] = 'error';
        redirigir($editando ? "../../admin/empleado_formulario.php?id=$id" : '../../admin/empleado_formulario.php');
    }
}

$cedula = $datos['cedula'];
$nombres = $datos['nombres'];
$apellidos = $datos['apellidos'];
$email = $datos['email'];
$telefono = $datos['telefono'] ?? null;
$direccion = $datos['direccion'] ?? null;
$fecha_nacimiento = $datos['fecha_nacimiento'] ?? null;
$fecha_ingreso = $datos['fecha_ingreso'] ?? date('Y-m-d');
$cargo_id = !empty($datos['cargo_id']) ? (int)$datos['cargo_id'] : null;
$salario_personalizado = !empty($datos['salario_personalizado']) ? (float)str_replace(',', '.', $datos['salario_personalizado']) : null;

$deducciones_ids = isset($datos['deducciones']) ? array_map('intval', (array)$datos['deducciones']) : [];
$asignaciones_ids = isset($datos['asignaciones']) ? array_map('intval', (array)$datos['asignaciones']) : [];
$prestaciones_ids = isset($datos['prestaciones']) ? array_map('intval', (array)$datos['prestaciones']) : [];

if ($fecha_nacimiento === '') { $fecha_nacimiento = null; }
if ($fecha_ingreso === '') { $fecha_ingreso = date('Y-m-d'); }

$stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
$stmt->bind_param('si', $email, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['mensaje_flash'] = 'El email ya esta registrado por otro usuario.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/empleado_formulario.php?id=$id" : '../../admin/empleado_formulario.php');
}
$stmt->close();

$stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE cedula = ? AND id != ?");
$stmt->bind_param('si', $cedula, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['mensaje_flash'] = 'La cedula ya esta registrada.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/empleado_formulario.php?id=$id" : '../../admin/empleado_formulario.php');
}
$stmt->close();

$mysqli->begin_transaction();

try {
    if ($editando) {
        $sql = "UPDATE usuarios SET cedula=?, nombres=?, apellidos=?, email=?, telefono=?, direccion=?, fecha_nacimiento=?, fecha_ingreso=?, cargo_id=?, salario_personalizado=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ssssssssidi', $cedula, $nombres, $apellidos, $email, $telefono, $direccion, $fecha_nacimiento, $fecha_ingreso, $cargo_id, $salario_personalizado, $id);
    } else {
        $password_hash = password_hash($datos['password'], PASSWORD_BCRYPT);
        $rol = $datos['rol'] ?? 'empleado';
        $sql = "INSERT INTO usuarios (cedula, nombres, apellidos, email, password_hash, telefono, direccion, fecha_nacimiento, fecha_ingreso, cargo_id, salario_personalizado, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('sssssssssids', $cedula, $nombres, $apellidos, $email, $password_hash, $telefono, $direccion, $fecha_nacimiento, $fecha_ingreso, $cargo_id, $salario_personalizado, $rol);
    }

    if (!$stmt->execute()) {
        throw new Exception($mysqli->error);
    }

    if (!$editando) {
        $id = $stmt->insert_id;
    }
    $stmt->close();

    if ($editando) {
        $mysqli->query("DELETE FROM empleado_deducciones WHERE usuario_id = $id");
        $mysqli->query("DELETE FROM empleado_asignaciones WHERE usuario_id = $id");
        $mysqli->query("DELETE FROM empleado_prestaciones WHERE usuario_id = $id");
    }

    foreach ($deducciones_ids as $did) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO empleado_deducciones (usuario_id, deduccion_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $id, $did);
        $stmt->execute();
        $stmt->close();
    }

    foreach ($asignaciones_ids as $aid) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO empleado_asignaciones (usuario_id, asignacion_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $id, $aid);
        $stmt->execute();
        $stmt->close();
    }

    foreach ($prestaciones_ids as $pid) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO empleado_prestaciones (usuario_id, prestacion_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $id, $pid);
        $stmt->execute();
        $stmt->close();
    }

    $mysqli->commit();

    $_SESSION['mensaje_flash'] = $editando ? 'Empleado actualizado correctamente.' : 'Empleado creado correctamente.';
    $_SESSION['mensaje_tipo'] = 'exito';
    redirigir('../../admin/empleados.php');

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['mensaje_flash'] = 'Error al guardar: ' . $e->getMessage();
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/empleado_formulario.php?id=$id" : '../../admin/empleado_formulario.php');
}
