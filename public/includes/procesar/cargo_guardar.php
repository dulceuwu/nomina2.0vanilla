<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../../admin/cargos.php');
}

$datos = sanitizarArray($_POST);
$id = isset($datos['id']) ? (int)$datos['id'] : 0;
$editando = $id > 0;

if (!validarTokenCSRF($datos['csrf_token'] ?? '')) {
    $_SESSION['mensaje_flash'] = 'Error de seguridad.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/cargo_form.php?id=$id" : '../../admin/cargo_form.php');
}

$codigo = $datos['codigo'];
$nombre = $datos['nombre'];
$descripcion = $datos['descripcion'] ?? null;
$salario_base = (float)str_replace(',', '.', $datos['salario_base']);
$incluye_cestaticket = isset($datos['incluye_cestaticket']) ? 1 : 0;
$incluye_transporte = isset($datos['incluye_transporte']) ? 1 : 0;
$activo = isset($datos['activo']) ? 1 : 0;

if ($editando && $activo === 0 && !isset($datos['activo'])) {
    $activo = 1;
}

if (empty($codigo) || empty($nombre) || $salario_base <= 0) {
    $_SESSION['mensaje_flash'] = 'Todos los campos obligatorios deben estar llenos.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/cargo_form.php?id=$id" : '../../admin/cargo_form.php');
}

$stmt = $mysqli->prepare("SELECT id FROM cargos WHERE codigo = ? AND id != ?");
$stmt->bind_param('si', $codigo, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['mensaje_flash'] = 'El codigo ya existe.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/cargo_form.php?id=$id" : '../../admin/cargo_form.php');
}
$stmt->close();

if ($editando) {
    $stmt = $mysqli->prepare("UPDATE cargos SET codigo=?, nombre=?, descripcion=?, salario_base=?, incluye_cestaticket=?, incluye_transporte=?, activo=? WHERE id=?");
    $stmt->bind_param('sssdiiii', $codigo, $nombre, $descripcion, $salario_base, $incluye_cestaticket, $incluye_transporte, $activo, $id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO cargos (codigo, nombre, descripcion, salario_base, incluye_cestaticket, incluye_transporte, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param('sssdii', $codigo, $nombre, $descripcion, $salario_base, $incluye_cestaticket, $incluye_transporte);
}

if ($stmt->execute()) {
    $_SESSION['mensaje_flash'] = $editando ? 'Cargo actualizado correctamente.' : 'Cargo creado correctamente.';
    $_SESSION['mensaje_tipo'] = 'exito';
    redirigir('../../admin/cargos.php');
} else {
    $_SESSION['mensaje_flash'] = 'Error al guardar: ' . $mysqli->error;
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($editando ? "../../admin/cargo_form.php?id=$id" : '../../admin/cargo_form.php');
}
