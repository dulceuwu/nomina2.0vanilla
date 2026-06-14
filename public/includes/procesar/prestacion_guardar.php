<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../../admin/prestaciones.php');
}

$datos = sanitizarArray($_POST);
$id = isset($datos['id']) ? (int)$datos['id'] : 0;
$editando = $id > 0;

if (!validarTokenCSRF($datos['csrf_token'] ?? '')) {
    $_SESSION['mensaje_flash'] = 'Error de seguridad.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/prestacion_form.php?id=$id" : '../../admin/prestacion_form.php');
}

$codigo = $datos['codigo'];
$nombre = $datos['nombre'];
$descripcion = $datos['descripcion'] ?? null;
$tipo_calculo = $datos['tipo_calculo'];
$dias_ano = $tipo_calculo === 'dias_ano' ? (int)($datos['dias_ano'] ?? 0) : null;
$porcentaje = $tipo_calculo === 'porcentaje' ? (float)str_replace(',', '.', $datos['porcentaje'] ?? '0') : null;
$monto_fijo = $tipo_calculo === 'fijo' ? (float)str_replace(',', '.', $datos['monto_fijo'] ?? '0') : null;
$monto_fijo_usd = $tipo_calculo === 'fijo' ? (float)str_replace(',', '.', $datos['monto_fijo_usd'] ?? '0') : null;
$aplica_a = $datos['aplica_a'] ?? 'todos';
$es_legal = isset($datos['es_legal']) ? 1 : 0;
$activo = isset($datos['activo']) ? 1 : 0;
if (!$editando) { $activo = 1; }

if (empty($codigo) || empty($nombre) || empty($tipo_calculo)) {
    $_SESSION['mensaje_flash'] = 'Complete los campos obligatorios.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/prestacion_form.php?id=$id" : '../../admin/prestacion_form.php');
}

$stmt = $mysqli->prepare("SELECT id FROM prestaciones WHERE codigo = ? AND id != ?");
$stmt->bind_param('si', $codigo, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['mensaje_flash'] = 'El codigo ya existe.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/prestacion_form.php?id=$id" : '../../admin/prestacion_form.php');
}
$stmt->close();

if ($editando) {
    $stmt = $mysqli->prepare("UPDATE prestaciones SET codigo=?, nombre=?, descripcion=?, tipo_calculo=?, dias_ano=?, porcentaje=?, monto_fijo=?, monto_fijo_usd=?, aplica_a=?, es_legal=?, activo=? WHERE id=?");
    $stmt->bind_param('ssssidddsiii', $codigo, $nombre, $descripcion, $tipo_calculo, $dias_ano, $porcentaje, $monto_fijo, $monto_fijo_usd, $aplica_a, $es_legal, $activo, $id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO prestaciones (codigo, nombre, descripcion, tipo_calculo, dias_ano, porcentaje, monto_fijo, monto_fijo_usd, aplica_a, es_legal, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param('ssssidddsi', $codigo, $nombre, $descripcion, $tipo_calculo, $dias_ano, $porcentaje, $monto_fijo, $monto_fijo_usd, $aplica_a, $es_legal);
}

if ($stmt->execute()) {
    $_SESSION['mensaje_flash'] = $editando ? 'Prestacion actualizada correctamente.' : 'Prestacion creada correctamente.';
    $_SESSION['mensaje_tipo'] = 'exito';
    redirigir('../../admin/prestaciones.php');
} else {
    $_SESSION['mensaje_flash'] = 'Error al guardar: ' . $mysqli->error;
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/prestacion_form.php?id=$id" : '../../admin/prestacion_form.php');
}
