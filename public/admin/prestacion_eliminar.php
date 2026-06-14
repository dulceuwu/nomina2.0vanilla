<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'mensaje' => 'ID invalido']);
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM prestaciones WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'mensaje' => 'Prestacion eliminada correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'No se pudo eliminar la prestacion.']);
}
$stmt->close();
