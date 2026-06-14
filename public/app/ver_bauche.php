<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

$nomina_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($nomina_id <= 0) {
    redirigir($_SESSION['rol'] === 'admin' ? '../admin/ver_nominas.php' : 'mis_recibos.php');
}

$user_id = (int)$_SESSION['user_id'];
$es_admin = $_SESSION['rol'] === 'admin';

if ($es_admin) {
    $stmt = $mysqli->prepare("SELECT n.*, u.nombres, u.apellidos, u.cedula, u.email,
        p.nombre as periodo_nombre, p.fecha_inicio, p.fecha_fin, p.tipo
        FROM nominas n
        JOIN usuarios u ON n.usuario_id = u.id
        JOIN periodos_nomina p ON n.periodo_id = p.id
        WHERE n.id = ?");
    $stmt->bind_param('i', $nomina_id);
} else {
    $stmt = $mysqli->prepare("SELECT n.*, u.nombres, u.apellidos, u.cedula, u.email,
        p.nombre as periodo_nombre, p.fecha_inicio, p.fecha_fin, p.tipo
        FROM nominas n
        JOIN usuarios u ON n.usuario_id = u.id
        JOIN periodos_nomina p ON n.periodo_id = p.id
        WHERE n.id = ? AND n.usuario_id = ?");
    $stmt->bind_param('ii', $nomina_id, $user_id);
}

$stmt->execute();
$nomina = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$nomina) {
    $_SESSION['mensaje_flash'] = 'Bauche no encontrado o no tiene permiso para verlo.';
    $_SESSION['mensaje_tipo'] = 'error';
    redirigir($es_admin ? '../admin/ver_nominas.php' : 'mis_recibos.php');
}

$detalles = $mysqli->query("SELECT concepto, tipo, monto, orden FROM detalle_nomina WHERE nomina_id = $nomina_id ORDER BY orden, tipo");

$asignaciones = [];
$deducciones = [];
while ($det = $detalles->fetch_assoc()) {
    if ($det['tipo'] === 'asignacion') { $asignaciones[] = $det; }
    else { $deducciones[] = $det; }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="recibo">
    <div class="recibo-encabezado">
        <h2>Bauche de Pago</h2>
        <p>NominaApp - Sistema de Gestion de Nominas</p>
        <p>Periodo: <?php echo htmlspecialchars($nomina['periodo_nombre'], ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <div class="recibo-datos">
        <div><strong>Empleado:</strong> <?php echo htmlspecialchars($nomina['nombres'] . ' ' . $nomina['apellidos'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Cedula:</strong> <?php echo htmlspecialchars($nomina['cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Email:</strong> <?php echo htmlspecialchars($nomina['email'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Fecha Generacion:</strong> <?php echo date('d/m/Y H:i', strtotime($nomina['generada_en'])); ?></div>
        <div><strong>Periodo:</strong> <?php echo date('d/m/Y', strtotime($nomina['fecha_inicio'])) . ' al ' . date('d/m/Y', strtotime($nomina['fecha_fin'])); ?></div>
        <div><strong>Dias Trabajados:</strong> <?php echo $nomina['dias_trabajados']; ?></div>
        <?php if ($nomina['dias_ausencia'] > 0): ?>
        <div><strong>Ausencias:</strong> <?php echo $nomina['dias_ausencia']; ?> dia(s)</div>
        <?php endif; ?>
    </div>

    <h3>Asignaciones</h3>
    <table class="recibo-tabla">
        <thead><tr><th>Concepto</th><th style="text-align:right">Monto</th></tr></thead>
        <tbody>
            <?php foreach ($asignaciones as $a): ?>
            <tr>
                <td><?php echo htmlspecialchars($a['concepto'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="asignacion" style="text-align:right"><?php echo formatoMoneda($a['monto']); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total">
                <td>Total Asignaciones</td>
                <td style="text-align:right"><?php echo formatoMoneda($nomina['total_asignaciones']); ?></td>
            </tr>
        </tbody>
    </table>

    <h3>Deducciones</h3>
    <table class="recibo-tabla">
        <thead><tr><th>Concepto</th><th style="text-align:right">Monto</th></tr></thead>
        <tbody>
            <?php foreach ($deducciones as $d): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['concepto'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="deduccion" style="text-align:right">- <?php echo formatoMoneda($d['monto']); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total">
                <td>Total Deducciones</td>
                <td style="text-align:right">- <?php echo formatoMoneda($nomina['total_deducciones']); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="recibo-neto">
        Total Neto a Cobrar: <?php echo formatoMoneda($nomina['salario_neto']); ?>
        <br><span style="font-size:0.9rem;"><?php echo formatoUSD($nomina['salario_neto'], $mysqli); ?></span>
    </div>

    <div class="recibo-patronal">
        <p><strong>Costo Total para el Empleador:</strong> <?php echo formatoMoneda($nomina['costo_patronal']); ?>
        (<?php echo formatoUSD($nomina['costo_patronal'], $mysqli); ?>)</p>
        <p style="font-size:0.8rem; margin-top:4px;">Incluye: Salario Neto + Aportes Patronales (SSO, LRPE, FAOV)</p>
    </div>

    <div class="recibo-acciones no-print">
        <button class="btn btn-primario" onclick="window.print()">Imprimir / PDF</button>
        <a href="<?php echo $es_admin ? '../admin/ver_nominas.php' : 'mis_recibos.php'; ?>" class="btn btn-secundario">Volver</a>
    </div>
</div>

<script><?php if (isset($_GET['print'])): ?>window.onload=function(){setTimeout(function(){window.print()},500)};<?php endif; ?></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
