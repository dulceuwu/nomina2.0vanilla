<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../../admin/deducciones.php');
}

$datos = sanitizarArray($_POST);
$id = isset($datos['id']) ? (int)$datos['id'] : 0;
$editando = $id > 0;

if (!validarTokenCSRF($datos['csrf_token'] ?? '')) {
    $_SESSION['mensaje_flash'] = 'Error de seguridad.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/deduccion_form.php?id=$id" : '../../admin/deduccion_form.php');
}

$codigo = $datos['codigo'];
$nombre = $datos['nombre'];
$tipo = $datos['tipo'];
$monto_fijo = $tipo === 'fijo' ? (float)str_replace(',', '.', $datos['monto_fijo'] ?? '0') : null;
$monto_fijo_usd = $tipo === 'fijo' ? (float)str_replace(',', '.', $datos['monto_fijo_usd'] ?? '0') : null;
$porcentaje = $tipo === 'porcentaje' ? (float)str_replace(',', '.', $datos['porcentaje'] ?? '0') : null;
$aplica_a = $datos['aplica_a'] ?? 'todos';
$descripcion = $datos['descripcion'] ?? null;
$es_legal = isset($datos['es_legal']) ? 1 : 0;
$es_patronal = isset($datos['es_patronal']) ? 1 : 0;
$activo = isset($datos['activo']) ? 1 : 0;
if (!$editando) { $activo = 1; }

if (empty($codigo) || empty($nombre) || empty($tipo)) {
    $_SESSION['mensaje_flash'] = 'Complete los campos obligatorios.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/deduccion_form.php?id=$id" : '../../admin/deduccion_form.php');
}

$stmt = $mysqli->prepare("SELECT id FROM deducciones WHERE codigo = ? AND id != ?");
$stmt->bind_param('si', $codigo, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['mensaje_flash'] = 'El codigo ya existe.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/deduccion_form.php?id=$id" : '../../admin/deduccion_form.php');
}
$stmt->close();

if ($editando) {
    $stmt = $mysqli->prepare("UPDATE deducciones SET codigo=?, nombre=?, tipo=?, monto_fijo=?, monto_fijo_usd=?, porcentaje=?, aplica_a=?, descripcion=?, es_legal=?, es_patronal=?, activo=? WHERE id=?");
    $stmt->bind_param('sssdddsiiiii', $codigo, $nombre, $tipo, $monto_fijo, $monto_fijo_usd, $porcentaje, $aplica_a, $descripcion, $es_legal, $es_patronal, $activo, $id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO deducciones (codigo, nombre, tipo, monto_fijo, monto_fijo_usd, porcentaje, aplica_a, descripcion, es_legal, es_patronal, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param('sssdddsii', $codigo, $nombre, $tipo, $monto_fijo, $monto_fijo_usd, $porcentaje, $aplica_a, $descripcion, $es_legal, $es_patronal);
}

if ($stmt->execute()) {
    $_SESSION['mensaje_flash'] = $editando ? 'Deduccion actualizada correctamente.' : 'Deduccion creada correctamente.';
    $_SESSION['mensaje_tipo'] = 'exito';
    redirigir('../../admin/deducciones.php');
} else {
    $_SESSION['mensaje_flash'] = 'Error al guardar: ' . $mysqli->error;
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/deduccion_form.php?id=$id" : '../../admin/deduccion_form.php');
}
