<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../calculos_nomina.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensaje' => 'Metodo no permitido']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de seguridad']);
    exit;
}

$periodo_id = isset($_POST['periodo_id']) ? (int)$_POST['periodo_id'] : 0;
$empleados_ids = isset($_POST['empleados_seleccionados']) ? $_POST['empleados_seleccionados'] : [];

if ($periodo_id <= 0 || empty($empleados_ids)) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
    exit;
}

$empleados_ids = array_map('intval', $empleados_ids);
$empleados_ids = array_filter($empleados_ids, function($id) { return $id > 0; });

if (empty($empleados_ids)) {
    echo json_encode(['success' => false, 'mensaje' => 'IDs de empleados invalidos']);
    exit;
}

$stmt = $mysqli->prepare("SELECT id FROM periodos_nomina WHERE id = ? AND estado = 'abierto'");
$stmt->bind_param('i', $periodo_id);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    echo json_encode(['success' => false, 'mensaje' => 'Periodo no encontrado o esta cerrado']);
    exit;
}
$stmt->close();

$admin_id = (int)$_SESSION['user_id'];
$generadas = 0;
$errores = 0;
$errores_detalle = [];

$mysqli->begin_transaction();

try {
    foreach ($empleados_ids as $emp_id) {
        $stmt = $mysqli->prepare("SELECT id FROM nominas WHERE usuario_id = ? AND periodo_id = ?");
        $stmt->bind_param('ii', $emp_id, $periodo_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores_detalle[] = "Empleado ID $emp_id: ya tiene nomina en este periodo";
            $errores++;
            $stmt->close();
            continue;
        }
        $stmt->close();

        $resultado = calcularNominaCompleta($mysqli, $emp_id, $periodo_id);

        if (isset($resultado['error'])) {
            $errores_detalle[] = "Empleado ID $emp_id: " . $resultado['error'];
            $errores++;
            continue;
        }

        $stmt = $mysqli->prepare("INSERT INTO nominas (usuario_id, periodo_id, dias_trabajados, dias_ausencia, salario_base_mensual, total_asignaciones, total_deducciones, salario_neto, costo_patronal, generada_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiidddddii',
            $resultado['empleado_id'],
            $resultado['periodo_id'],
            $resultado['dias_trabajados'],
            $resultado['dias_ausencia'],
            $resultado['salario_base_mensual'],
            $resultado['total_asignaciones'],
            $resultado['total_deducciones'],
            $resultado['salario_neto'],
            $resultado['costo_patronal'],
            $admin_id
        );

        if (!$stmt->execute()) {
            $errores_detalle[] = "Empleado ID $emp_id: error al insertar nomina";
            $errores++;
            $stmt->close();
            continue;
        }
        $nomina_id = $stmt->insert_id;
        $stmt->close();

        $orden = 1;
        foreach ($resultado['asignaciones'] as $asig) {
            $stmt = $mysqli->prepare("INSERT INTO detalle_nomina (nomina_id, concepto, tipo, monto, orden) VALUES (?, ?, 'asignacion', ?, ?)");
            $stmt->bind_param('isdi', $nomina_id, $asig['concepto'], $asig['monto'], $orden);
            $stmt->execute();
            $stmt->close();
            $orden++;
        }

        foreach ($resultado['deducciones'] as $ded) {
            $stmt = $mysqli->prepare("INSERT INTO detalle_nomina (nomina_id, concepto, tipo, monto, orden) VALUES (?, ?, 'deduccion', ?, ?)");
            $stmt->bind_param('isdi', $nomina_id, $ded['concepto'], $ded['monto'], $orden);
            $stmt->execute();
            $stmt->close();
            $orden++;
        }

        $generadas++;
    }

    $mysqli->commit();

    $mensaje = "Nominas generadas exitosamente.";
    if (!empty($errores_detalle)) {
        $mensaje .= " Errores: " . implode("; ", array_slice($errores_detalle, 0, 5));
        if (count($errores_detalle) > 5) {
            $mensaje .= " (y " . (count($errores_detalle) - 5) . " mas)";
        }
    }

    echo json_encode([
        'success' => true,
        'generadas' => $generadas,
        'errores' => $errores,
        'mensaje' => $mensaje
    ]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode([
        'success' => false,
        'generadas' => $generadas,
        'errores' => $errores + 1,
        'mensaje' => 'Error general: ' . $e->getMessage()
    ]);
}
