<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensaje' => 'Metodo no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['accion'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos invalidos']);
    exit;
}

$id = (int)$input['id'];
$accion = $input['accion'];

if ($accion === 'activar') {
    $nuevo_estado = 1;
} elseif ($accion === 'desactivar') {
    $nuevo_estado = 0;
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Accion invalida']);
    exit;
}

$stmt = $mysqli->prepare("UPDATE usuarios SET activo = ? WHERE id = ? AND rol = 'empleado'");
$stmt->bind_param('ii', $nuevo_estado, $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'mensaje' => 'Estado actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'No se pudo actualizar el estado.']);
}

$stmt->close();
