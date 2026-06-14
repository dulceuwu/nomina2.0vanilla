<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../../admin/periodos.php');
}

if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
    $_SESSION['mensaje_flash'] = 'Error de seguridad.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir('../../admin/periodos.php');
}

if (isset($_POST['accion']) && $_POST['accion'] === 'cerrar') {
    $id = (int)$_POST['id'];
    $stmt = $mysqli->prepare("UPDATE periodos_nomina SET estado = 'cerrado', cerrado_en = NOW() WHERE id = ? AND estado = 'abierto'");
    $stmt->bind_param('i', $id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['mensaje_flash'] = 'Periodo cerrado correctamente.';
        $_SESSION['mensaje_tipo'] = 'exito';
    } else {
        $_SESSION['mensaje_flash'] = 'No se pudo cerrar el periodo.';
        $_SESSION['mensaje_tipo'] = 'error';
    }
    $stmt->close();
    redirigir('../../admin/periodos.php');
}

$datos = sanitizarArray($_POST);
$id = isset($datos['id']) ? (int)$datos['id'] : 0;
$editando = $id > 0;

$nombre = $datos['nombre'];
$tipo = $datos['tipo'];
$fecha_inicio = $datos['fecha_inicio'];
$fecha_fin = $datos['fecha_fin'];
$dias_habiles = (int)($datos['dias_habiles'] ?? 0);

if (empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
    $_SESSION['mensaje_flash'] = 'Todos los campos obligatorios deben estar llenos.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/periodo_formulario.php?id=$id" : '../../admin/periodo_formulario.php');
}

if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
    $_SESSION['mensaje_flash'] = 'La fecha fin debe ser posterior a la fecha inicio.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/periodo_formulario.php?id=$id" : '../../admin/periodo_formulario.php');
}

$stmt = $mysqli->prepare("SELECT id FROM periodos_nomina WHERE tipo = ? AND ((fecha_inicio <= ? AND fecha_fin >= ?) OR (fecha_inicio <= ? AND fecha_fin >= ?)) AND id != ?");
$stmt->bind_param('sssssi', $tipo, $fecha_fin, $fecha_inicio, $fecha_fin, $fecha_inicio, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['mensaje_flash'] = 'Ya existe un periodo con fechas que se solapan.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/periodo_formulario.php?id=$id" : '../../admin/periodo_formulario.php');
}
$stmt->close();

$user_id = (int)$_SESSION['user_id'];

if ($editando) {
    $stmt = $mysqli->prepare("UPDATE periodos_nomina SET nombre=?, tipo=?, fecha_inicio=?, fecha_fin=?, dias_habiles=? WHERE id=? AND estado='abierto'");
    $stmt->bind_param('ssssii', $nombre, $tipo, $fecha_inicio, $fecha_fin, $dias_habiles, $id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO periodos_nomina (nombre, tipo, fecha_inicio, fecha_fin, dias_habiles, creado_por) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssii', $nombre, $tipo, $fecha_inicio, $fecha_fin, $dias_habiles, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['mensaje_flash'] = $editando ? 'Periodo actualizado.' : 'Periodo creado correctamente.';
    $_SESSION['mensaje_tipo'] = 'exito';
    redirigir('../../admin/periodos.php');
} else {
    $_SESSION['mensaje_flash'] = 'Error al guardar: ' . $mysqli->error;
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/periodo_formulario.php?id=$id" : '../../admin/periodo_formulario.php');
}
