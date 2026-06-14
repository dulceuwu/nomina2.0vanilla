<?php
require_once __DIR__ . '/../public/includes/db.php';
require_once __DIR__ . '/../public/includes/helpers.php';
require_once __DIR__ . '/../public/includes/calculos_nomina.php';

echo "=== PRUEBAS UNITARIAS - MOTOR DE CÁLCULO ===\n\n";

$total_pruebas = 0;
$pasaron = 0;

function probar($nombre, $obtenido, $esperado) {
    global $total_pruebas, $pasaron;
    $total_pruebas++;
    $ok = abs($obtenido - $esperado) < 0.01;
    echo ($ok ? "[OK]" : "[FAIL]") . " $nombre: esperado=$esperado obtenido=$obtenido\n";
    if ($ok) $pasaron++;
}

$sd = calcularSalarioDiario(180);
probar("Salario diario(180)", $sd, 6.00);

$ba = calcularBonoAlimentacion(22, 9.00);
probar("Cesta Ticket diario(UT=9)", $ba['diario'], 2.25);
probar("Cesta Ticket total(22 dias)", $ba['total'], 49.50);

$sso = calcularSSO(180, 4, 'empleado');
probar("SSO empleado(180,4%)", $sso['monto'], 7.20);

$lrpe = calcularLRPE(180, 0.5, 'empleado');
probar("LRPE empleado(180,0.5%)", $lrpe['monto'], 0.90);

$faov = calcularFAOV(180, 2);
probar("FAOV(180,2%)", $faov['monto'], 3.60);

$sso_tope = calcularSSO(1000, 4, 'empleado');
probar("SSO con tope(1000,4%)", $sso_tope['monto'], 26.00);

$islr = calcularISLRConTabla(180, 9.00);
probar("ISLR mensual(180,UT=9)", $islr['impuesto_mensual'], 0);

echo "\n=== PRUEBA INTEGRACION ===\n";

$emp = $mysqli->query("SELECT u.id FROM usuarios u LEFT JOIN cargos c ON u.cargo_id = c.id WHERE u.rol='empleado' AND u.activo=1 AND COALESCE(u.salario_personalizado, c.salario_base, 0) > 0 LIMIT 1")->fetch_assoc();
$per = $mysqli->query("SELECT id FROM periodos_nomina WHERE estado='abierto' LIMIT 1")->fetch_assoc();

if ($emp && $per) {
    echo "Probando calcularNominaCompleta(empleado={$emp['id']}, periodo={$per['id']})\n";
    $resultado = calcularNominaCompleta($mysqli, $emp['id'], $per['id']);
    if (isset($resultado['error'])) {
        echo "ERROR: " . $resultado['error'] . "\n";
    } else {
        echo "Salario base mensual: " . $resultado['salario_base_mensual'] . "\n";
        echo "Dias trabajados: " . $resultado['dias_trabajados'] . "\n";
        echo "Asignaciones:\n";
        foreach ($resultado['asignaciones'] as $a) {
            echo "  + {$a['concepto']}: {$a['monto']}\n";
        }
        echo "Deducciones:\n";
        foreach ($resultado['deducciones'] as $d) {
            echo "  - {$d['concepto']}: {$d['monto']}\n";
        }
        echo "Total Asignaciones: {$resultado['total_asignaciones']}\n";
        echo "Total Deducciones: {$resultado['total_deducciones']}\n";
        echo "Salario Neto: {$resultado['salario_neto']}\n";
        echo "Costo Patronal: {$resultado['costo_patronal']}\n";

        $diferencia = abs($resultado['total_asignaciones'] - $resultado['total_deducciones'] - $resultado['salario_neto']);
        echo "Diferencia (asig - ded - neto): $diferencia\n";
        if ($diferencia < 0.01) {
            echo "[OK] Cuadre correcto\n";
        } else {
            echo "[FAIL] Diferencia de cuadre detectada\n";
        }
    }
} else {
    echo "No se encontraron empleados activos con cargo o períodos abiertos.\n";
    echo "Asegúrese de ejecutar primero database/schema.sql y database/seed_data.sql\n";
}

echo "\n=== RESUMEN: $pasaron/$total_pruebas pruebas pasaron ===\n";
