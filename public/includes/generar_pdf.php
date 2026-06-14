<?php
function generarBauchePDF($mysqli, $nomina_id) {
    $stmt = $mysqli->prepare("SELECT n.*, u.nombres, u.apellidos, u.cedula, u.email,
        p.nombre as periodo_nombre, p.fecha_inicio, p.fecha_fin, p.tipo
        FROM nominas n
        JOIN usuarios u ON n.usuario_id = u.id
        JOIN periodos_nomina p ON n.periodo_id = p.id
        WHERE n.id = ?");
    $stmt->bind_param('i', $nomina_id);
    $stmt->execute();
    $nomina = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$nomina) return false;

    $stmt = $mysqli->prepare("SELECT concepto, tipo, monto FROM detalle_nomina WHERE nomina_id = ? ORDER BY tipo, orden, id");
    $stmt->bind_param('i', $nomina_id);
    $stmt->execute();
    $detalles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Bauche - <?php echo htmlspecialchars($nomina['nombres'] . ' ' . $nomina['apellidos'], ENT_QUOTES, 'UTF-8'); ?></title>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12pt; color: #333; margin: 0; padding: 0; }
            .recibo { max-width: 700px; margin: 0 auto; padding: 30px; }
            .recibo-encabezado { text-align: center; border-bottom: 2px solid #ccc; padding-bottom: 15px; margin-bottom: 20px; }
            .recibo-encabezado h2 { margin: 0 0 5px; font-size: 18pt; }
            .recibo-encabezado p { color: #666; font-size: 10pt; margin: 2px 0; }
            .recibo-datos { display: flex; flex-wrap: wrap; gap: 5px 20px; margin-bottom: 20px; font-size: 10pt; }
            .recibo-datos > div { flex: 1 1 45%; }
            .recibo-datos strong { font-weight: 600; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10pt; }
            th { background: #f5f5f5; padding: 8px 10px; text-align: left; font-weight: 600; border-bottom: 2px solid #ddd; }
            td { padding: 6px 10px; border-bottom: 1px solid #eee; }
            .total-row td { font-weight: 700; border-top: 2px solid #333; padding-top: 8px; }
            .asignacion { color: #2e7d32; }
            .deduccion { color: #c62828; }
            .recibo-neto { text-align: center; font-size: 16pt; font-weight: 700; color: #2e7d32; padding: 15px; background: #f1f8e9; border-radius: 4px; margin-bottom: 15px; }
            .recibo-patronal { font-size: 9pt; color: #666; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
            .recibo-acciones { text-align: center; margin-top: 20px; }
            @media print {
                .recibo-acciones { display: none; }
                body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            }
        </style>
    </head>
    <body>
        <div class="recibo">
            <div class="recibo-encabezado">
                <h2>Bauche de Pago</h2>
                <p>NominaApp - Sistema de Gestión de Nominas</p>
                <p>Periodo: <?php echo htmlspecialchars($nomina['periodo_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                   (<?php echo $nomina['fecha_inicio']; ?> al <?php echo $nomina['fecha_fin']; ?>)</p>
            </div>

            <div class="recibo-datos">
                <div><strong>Empleado:</strong> <?php echo htmlspecialchars($nomina['nombres'] . ' ' . $nomina['apellidos'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div><strong>Cedula:</strong> <?php echo htmlspecialchars($nomina['cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div><strong>Email:</strong> <?php echo htmlspecialchars($nomina['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div><strong>Salario Base:</strong> Bs. <?php echo number_format($nomina['salario_base_mensual'], 2, ',', '.'); ?></div>
                <div><strong>Dias Trabajados:</strong> <?php echo $nomina['dias_trabajados']; ?></div>
                <div><strong>Tipo:</strong> <?php echo ucfirst($nomina['tipo']); ?></div>
            </div>

            <table>
                <thead>
                    <tr><th>Concepto</th><th>Tipo</th><th style="text-align:right">Monto</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $d): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($d['concepto'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="<?php echo $d['tipo']; ?>"><?php echo $d['tipo'] === 'asignacion' ? 'Asignacion' : 'Deduccion'; ?></td>
                        <td style="text-align:right" class="<?php echo $d['tipo']; ?>">Bs. <?php echo number_format($d['monto'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="2">Total <?php echo $nomina['total_deducciones'] > $nomina['total_asignaciones'] ? 'Deducciones' : 'Asignaciones'; ?></td>
                        <td style="text-align:right">Bs. <?php echo number_format(max($nomina['total_asignaciones'], $nomina['total_deducciones']), 2, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="recibo-neto">
                Neto a Pagar: Bs. <?php echo number_format($nomina['salario_neto'], 2, ',', '.'); ?>
            </div>

            <div class="recibo-patronal">
                Costo Patronal: Bs. <?php echo number_format($nomina['costo_patronal'], 2, ',', '.'); ?> |
                Generado el: <?php echo $nomina['generada_en']; ?>
            </div>

            <div class="recibo-acciones">
                <button onclick="window.print()" style="padding:8px 20px;font-size:11pt;cursor:pointer">Imprimir / Guardar PDF</button>
                <button onclick="window.close()" style="padding:8px 20px;font-size:11pt;cursor:pointer">Cerrar</button>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
