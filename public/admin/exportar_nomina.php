<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$periodo_id = isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : 0;

$sql = "SELECT n.id, u.cedula, u.nombres, u.apellidos, u.email, COALESCE(c.nombre, 'Sin cargo') as cargo, p.nombre as periodo, p.fecha_inicio, p.fecha_fin,
        n.salario_base_mensual, n.dias_trabajados, n.dias_ausencia, n.total_asignaciones, n.total_deducciones,
        n.salario_neto, n.costo_patronal, n.generada_en
        FROM nominas n
        JOIN usuarios u ON n.usuario_id = u.id
        LEFT JOIN cargos c ON u.cargo_id = c.id
        JOIN periodos_nomina p ON n.periodo_id = p.id";
$params = [];
$tipos = '';

if ($periodo_id > 0) {
    $sql .= " WHERE n.periodo_id = ?";
    $params[] = $periodo_id;
    $tipos .= 'i';
}
$sql .= " ORDER BY u.apellidos, u.nombres";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$nominas = $stmt->get_result();

$nombre_periodo = 'TODOS';
if ($periodo_id > 0) {
    $pstmt = $mysqli->prepare("SELECT nombre FROM periodos_nomina WHERE id = ?");
    $pstmt->bind_param('i', $periodo_id);
    $pstmt->execute();
    $prow = $pstmt->get_result()->fetch_assoc();
    if ($prow) $nombre_periodo = strtoupper(str_replace(' ', '_', $prow['nombre']));
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="nominas_' . $nombre_periodo . '_' . date('Ymd') . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['Cedula', 'Nombres', 'Apellidos', 'Email', 'Cargo', 'Periodo', 'Fecha Inicio', 'Fecha Fin',
    'Salario Base', 'Dias Trabajados', 'Dias Ausencia', 'Total Asignaciones', 'Total Deducciones',
    'Salario Neto', 'Costo Patronal', 'Generada']);

while ($nom = $nominas->fetch_assoc()) {
    fputcsv($output, [
        $nom['cedula'],
        $nom['nombres'],
        $nom['apellidos'],
        $nom['email'],
        $nom['cargo'],
        $nom['periodo'],
        $nom['fecha_inicio'],
        $nom['fecha_fin'],
        number_format($nom['salario_base_mensual'], 2, '.', ''),
        $nom['dias_trabajados'],
        $nom['dias_ausencia'],
        number_format($nom['total_asignaciones'], 2, '.', ''),
        number_format($nom['total_deducciones'], 2, '.', ''),
        number_format($nom['salario_neto'], 2, '.', ''),
        number_format($nom['costo_patronal'], 2, '.', ''),
        $nom['generada_en']
    ]);
}

fclose($output);
exit;
