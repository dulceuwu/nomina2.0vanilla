<?php
function calcularSalarioDiario($salario_mensual) {
    return round($salario_mensual / 30, 2);
}

function calcularSalarioIntegral($salario_mensual, $dias_laborados = 360) {
    $salario_diario = calcularSalarioDiario($salario_mensual);
    $alicuota_utilidades = round(($salario_diario * 30) / 360, 2);
    $alicuota_prestaciones = round(($salario_diario * 15) / 360, 2);
    return round($salario_diario + $alicuota_utilidades + $alicuota_prestaciones, 2);
}

function calcularBonoAlimentacion($dias_habiles, $ut) {
    $diario = round($ut * 0.25, 2);
    $total = round($diario * $dias_habiles, 2);
    $tope = round($ut * 0.25 * 30, 2);
    if ($total > $tope) { $total = $tope; }
    return ['diario' => $diario, 'total' => $total, 'tope_aplicado' => ($total >= $tope && $dias_habiles > 30)];
}

function calcularBonoTransporte($salario_mensual, $dias_laborados) {
    $salario_diario = calcularSalarioDiario($salario_mensual);
    $transporte_diario = round($salario_diario * 0.08, 2);
    $total = round($transporte_diario * $dias_laborados, 2);
    return ['diario' => $transporte_diario, 'total' => $total];
}

function calcularSSO($salario_bruto, $porcentaje, $tipo = 'empleado') {
    $tope = 0;
    $salario_minimo = $GLOBALS['salario_minimo'] ?? 130;
    $tope_salarios = $GLOBALS['tope_sso_salarios'] ?? 5;
    $tope_cotizacion = round($salario_minimo * $tope_salarios, 2);
    $base_calculo = $salario_bruto;
    if ($tipo === 'empleado' && $base_calculo > $tope_cotizacion) {
        $base_calculo = $tope_cotizacion;
        $tope = $tope_cotizacion;
    }
    $monto = round($base_calculo * $porcentaje / 100, 2);
    return ['base_calculo' => $base_calculo, 'porcentaje' => $porcentaje, 'monto' => $monto, 'tope_aplicado' => ($tope > 0)];
}

function calcularLRPE($salario_bruto, $porcentaje, $tipo = 'empleado') {
    $tope = 0;
    $salario_minimo = $GLOBALS['salario_minimo'] ?? 130;
    $tope_salarios = 10;
    $tope_cotizacion = round($salario_minimo * $tope_salarios, 2);
    $base_calculo = $salario_bruto;
    if ($tipo === 'empleado' && $base_calculo > $tope_cotizacion) {
        $base_calculo = $tope_cotizacion;
        $tope = $tope_cotizacion;
    }
    $monto = round($base_calculo * $porcentaje / 100, 2);
    return ['base_calculo' => $base_calculo, 'porcentaje' => $porcentaje, 'monto' => $monto, 'tope_aplicado' => ($tope > 0)];
}

function calcularFAOV($salario_bruto, $porcentaje) {
    $monto = round($salario_bruto * $porcentaje / 100, 2);
    return ['porcentaje' => $porcentaje, 'monto' => $monto];
}

function calcularISLRConTabla($salario_bruto_mensual, $ut, $mysqli = null) {
    $sueldo_anual = $salario_bruto_mensual * 12;
    $sueldo_anual_ut = round($sueldo_anual / $ut, 2);

    if ($mysqli) {
        $tramos = [];
        for ($i = 1; $i <= 8; $i++) {
            $hasta = $i < 8 ? (float)(obtenerConfigLegal($mysqli, "UT_ISLR_TRAMO$i") ?: 0) : 999999;
            $porc = (float)(obtenerConfigLegal($mysqli, "UT_ISLR_PORC$i") ?: 0);
            $sust = (float)(obtenerConfigLegal($mysqli, "UT_ISLR_SUST$i") ?: 0);
            $desde = $i === 1 ? 0 : (float)(obtenerConfigLegal($mysqli, "UT_ISLR_TRAMO" . ($i-1)) ?: 0);
            $tramos[] = ['desde' => $desde, 'hasta' => $hasta, 'sustraendo' => $sust, 'porcentaje' => $porc];
        }
    } else {
        $tramos = [
            ['desde' => 0, 'hasta' => 1000, 'sustraendo' => 0, 'porcentaje' => 0],
            ['desde' => 1000, 'hasta' => 1500, 'sustraendo' => 60, 'porcentaje' => 6],
            ['desde' => 1500, 'hasta' => 2000, 'sustraendo' => 105, 'porcentaje' => 9],
            ['desde' => 2000, 'hasta' => 2500, 'sustraendo' => 165, 'porcentaje' => 12],
            ['desde' => 2500, 'hasta' => 3000, 'sustraendo' => 265, 'porcentaje' => 16],
            ['desde' => 3000, 'hasta' => 4000, 'sustraendo' => 385, 'porcentaje' => 20],
            ['desde' => 4000, 'hasta' => 6000, 'sustraendo' => 545, 'porcentaje' => 24],
            ['desde' => 6000, 'hasta' => 999999, 'sustraendo' => 1145, 'porcentaje' => 34],
        ];
    }

    $impuesto_anual_ut = 0;
    foreach ($tramos as $tramo) {
        if ($sueldo_anual_ut > $tramo['desde'] && $sueldo_anual_ut <= $tramo['hasta']) {
            $excedente = $sueldo_anual_ut - $tramo['desde'];
            $impuesto_anual_ut = round(($excedente * $tramo['porcentaje'] / 100) + $tramo['sustraendo'], 2);
            break;
        }
    }
    $impuesto_anual_bs = round($impuesto_anual_ut * $ut, 2);
    $impuesto_mensual = round($impuesto_anual_bs / 12, 2);
    return [
        'sueldo_anual_ut' => $sueldo_anual_ut,
        'impuesto_anual_ut' => $impuesto_anual_ut,
        'impuesto_anual_bs' => $impuesto_anual_bs,
        'impuesto_mensual' => $impuesto_mensual
    ];
}

function cuadrarRedondeo($asignaciones, $deducciones, $salario_neto) {
    $total_asignaciones = array_sum(array_column($asignaciones, 'monto'));
    $total_deducciones = array_sum(array_column($deducciones, 'monto'));
    $diferencia = round($total_asignaciones - $total_deducciones - $salario_neto, 2);
    if (abs($diferencia) > 0.001) {
        for ($i = count($asignaciones) - 1; $i >= 0; $i--) {
            if ($asignaciones[$i]['tipo'] === 'asignacion') {
                $asignaciones[$i]['monto'] = round($asignaciones[$i]['monto'] + $diferencia, 2);
                break;
            }
        }
    }
    return ['asignaciones' => $asignaciones, 'deducciones' => $deducciones];
}

function obtenerSalarioEmpleado($mysqli, $id_empleado) {
    $stmt = $mysqli->prepare("SELECT COALESCE(u.salario_personalizado, c.salario_base, 0) as salario, u.cargo_id, COALESCE(c.incluye_cestaticket, 0) as incluye_cestaticket, COALESCE(c.incluye_transporte, 0) as incluye_transporte FROM usuarios u LEFT JOIN cargos c ON u.cargo_id = c.id WHERE u.id = ? AND u.activo = 1");
    $stmt->bind_param('i', $id_empleado);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

function calcularNominaCompleta($mysqli, $id_empleado, $id_periodo) {
    $salario_minimo = obtenerConfigLegal($mysqli, 'SALARIO_MINIMO');
    $ut = obtenerConfigLegal($mysqli, 'UT');
    $sso_emp_porc = obtenerConfigLegal($mysqli, 'SSO_EMPLEADO_PORC');
    $sso_pat_porc = obtenerConfigLegal($mysqli, 'SSO_PATRONAL_PORC');
    $lrpe_emp_porc = obtenerConfigLegal($mysqli, 'LRPE_EMPLEADO_PORC');
    $lrpe_pat_porc = obtenerConfigLegal($mysqli, 'LRPE_PATRONAL_PORC');
    $faov_porc = obtenerConfigLegal($mysqli, 'FAOV_PORC');
    $tope_sso_salarios = (int)obtenerConfigLegal($mysqli, 'TOPE_SSO_SALARIOS');
    $dias_habiles_mes = (int)obtenerConfigLegal($mysqli, 'DIAS_HABILES_MES');

    $GLOBALS['salario_minimo'] = $salario_minimo;
    $GLOBALS['tope_sso_salarios'] = $tope_sso_salarios;

    $emp_data = obtenerSalarioEmpleado($mysqli, $id_empleado);
    if (!$emp_data || !$emp_data['salario']) {
        return ['error' => 'Empleado no encontrado, inactivo o sin cargo asignado'];
    }

    $salario_base = (float)$emp_data['salario'];
    $incluye_cestaticket = (int)$emp_data['incluye_cestaticket'];
    $incluye_transporte = (int)$emp_data['incluye_transporte'];

    $stmt = $mysqli->prepare("SELECT fecha_inicio, fecha_fin, dias_habiles FROM periodos_nomina WHERE id = ? AND estado = 'abierto'");
    $stmt->bind_param('i', $id_periodo);
    $stmt->execute();
    $periodo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$periodo) {
        return ['error' => 'Periodo no encontrado o cerrado'];
    }

    $fecha_inicio = $periodo['fecha_inicio'];
    $fecha_fin = $periodo['fecha_fin'];
    $dias_habiles_periodo = (int)$periodo['dias_habiles'];
    if ($dias_habiles_periodo <= 0) { $dias_habiles_periodo = $dias_habiles_mes; }

    $fecha_inicio_dt = new DateTime($fecha_inicio);
    $fecha_fin_dt = new DateTime($fecha_fin);
    $dias_totales_periodo = $fecha_inicio_dt->diff($fecha_fin_dt)->days + 1;
    $dias_trabajados = $dias_totales_periodo;

    $salario_diario = calcularSalarioDiario($salario_base);
    $proporcion_periodo = round($dias_totales_periodo / 30, 4);
    $salario_periodo = round($salario_base * $proporcion_periodo, 2);

    $asignaciones = [];
    $deducciones = [];

    $asignaciones[] = ['concepto' => 'Salario Base', 'tipo' => 'asignacion', 'monto' => $salario_periodo, 'orden' => 1];

    if ($incluye_cestaticket) {
        $bono_alimentacion = calcularBonoAlimentacion($dias_habiles_periodo, $ut);
        if ($bono_alimentacion['total'] > 0) {
            $asignaciones[] = ['concepto' => 'Bono de Alimentacion (Cesta Ticket)', 'tipo' => 'asignacion', 'monto' => $bono_alimentacion['total'], 'orden' => 2];
        }
    }

    if ($incluye_transporte) {
        $bono_transporte = calcularBonoTransporte($salario_base, $dias_trabajados);
        if ($bono_transporte['total'] > 0) {
            $asignaciones[] = ['concepto' => 'Bono de Transporte', 'tipo' => 'asignacion', 'monto' => $bono_transporte['total'], 'orden' => 3];
        }
    }

    $stmt = $mysqli->prepare("SELECT a.codigo, a.nombre, a.tipo, a.monto_fijo, a.porcentaje, a.monto_diario FROM empleado_asignaciones ea JOIN asignaciones a ON ea.asignacion_id = a.id WHERE ea.usuario_id = ? AND a.activo = 1 AND a.codigo NOT IN ('ASIG-SALARIO','ASIG-CESTA-TICKET','ASIG-TRANSPORTE')");
    $stmt->bind_param('i', $id_empleado);
    $stmt->execute();
    $asig_extra = $stmt->get_result();
    $stmt->close();
    $orden = 4;

    while ($a = $asig_extra->fetch_assoc()) {
        $monto = 0;
        if ($a['tipo'] === 'fijo') {
            $monto = round($a['monto_fijo'] * $proporcion_periodo, 2);
        } elseif ($a['tipo'] === 'porcentaje') {
            $monto = round($salario_periodo * $a['porcentaje'] / 100, 2);
        } elseif ($a['tipo'] === 'diario' && $a['monto_diario']) {
            $monto = round($a['monto_diario'] * $dias_trabajados, 2);
        }
        if ($monto > 0) {
            $asignaciones[] = ['concepto' => $a['nombre'], 'tipo' => 'asignacion', 'monto' => $monto, 'orden' => $orden++];
        }
    }

    $total_asignaciones = array_sum(array_column($asignaciones, 'monto'));

    $stmt = $mysqli->prepare("SELECT d.codigo, d.nombre, d.tipo, d.monto_fijo, d.porcentaje, d.es_patronal FROM empleado_deducciones ed JOIN deducciones d ON ed.deduccion_id = d.id WHERE ed.usuario_id = ? AND d.activo = 1 AND d.es_patronal = 0");
    $stmt->bind_param('i', $id_empleado);
    $stmt->execute();
    $ded_extra = $stmt->get_result();
    $stmt->close();
    $orden_ded = 1;

    while ($d = $ded_extra->fetch_assoc()) {
        $monto = 0;
        $concepto = $d['nombre'];
        if ($d['codigo'] === 'DED-SSO-EMP') {
            $result = calcularSSO($salario_periodo, (float)$d['porcentaje'], 'empleado');
            $monto = $result['monto'];
            $concepto .= ' (' . $d['porcentaje'] . '%)';
        } elseif ($d['codigo'] === 'DED-LRPE-EMP') {
            $result = calcularLRPE($salario_periodo, (float)$d['porcentaje'], 'empleado');
            $monto = $result['monto'];
            $concepto .= ' (' . $d['porcentaje'] . '%)';
        } elseif ($d['codigo'] === 'DED-FAOV-EMP') {
            $result = calcularFAOV($salario_periodo, (float)$d['porcentaje']);
            $monto = $result['monto'];
            $concepto .= ' (' . $d['porcentaje'] . '%)';
        } elseif ($d['codigo'] === 'DED-ISLR') {
            $result = calcularISLRConTabla($salario_periodo, $ut, $mysqli);
            $monto = $result['impuesto_mensual'];
        } elseif ($d['tipo'] === 'porcentaje' && $d['porcentaje']) {
            $monto = round($salario_periodo * $d['porcentaje'] / 100, 2);
        } elseif ($d['tipo'] === 'fijo' && $d['monto_fijo']) {
            $monto = round($d['monto_fijo'] * $proporcion_periodo, 2);
        }
        if ($monto > 0) {
            $deducciones[] = ['concepto' => $concepto, 'tipo' => 'deduccion', 'monto' => $monto, 'orden' => $orden_ded++];
        }
    }

    $total_deducciones = array_sum(array_column($deducciones, 'monto'));
    $salario_neto = round($total_asignaciones - $total_deducciones, 2);
    if ($salario_neto < 0) { $salario_neto = 0; }

    $result_sso_patronal = calcularSSO($salario_periodo, (float)$sso_pat_porc, 'patronal');
    $result_lrpe_patronal = calcularLRPE($salario_periodo, (float)$lrpe_pat_porc, 'patronal');
    $result_faov = calcularFAOV($salario_periodo, (float)$faov_porc);

    $costo_patronal = round($salario_neto + $result_sso_patronal['monto'] + $result_lrpe_patronal['monto'] + $result_faov['monto'], 2);

    $ajuste = cuadrarRedondeo($asignaciones, $deducciones, $salario_neto);
    $asignaciones = $ajuste['asignaciones'];
    $deducciones = $ajuste['deducciones'];

    return [
        'empleado_id' => $id_empleado,
        'periodo_id' => $id_periodo,
        'salario_base_mensual' => $salario_base,
        'dias_totales_periodo' => $dias_totales_periodo,
        'dias_trabajados' => $dias_trabajados,
        'dias_ausencia' => 0,
        'asignaciones' => $asignaciones,
        'deducciones' => $deducciones,
        'total_asignaciones' => round(array_sum(array_column($asignaciones, 'monto')), 2),
        'total_deducciones' => round(array_sum(array_column($deducciones, 'monto')), 2),
        'salario_neto' => $salario_neto,
        'sso_empleado' => $result_sso_patronal ?? [],
        'lrpe_empleado' => $result_lrpe_patronal ?? [],
        'islr' => $result ?? [],
        'sso_patronal' => $result_sso_patronal,
        'lrpe_patronal' => $result_lrpe_patronal,
        'faov' => $result_faov,
        'costo_patronal' => $costo_patronal
    ];
}
