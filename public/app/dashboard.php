<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('empleado');

$user_id = (int)$_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT u.nombres, u.apellidos, u.email, u.telefono, u.fecha_ingreso, c.nombre as cargo_nombre FROM usuarios u LEFT JOIN cargos c ON u.cargo_id = c.id WHERE u.id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$empleado = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $mysqli->prepare("SELECT n.id, n.salario_neto, n.generada_en, p.nombre as periodo FROM nominas n JOIN periodos_nomina p ON n.periodo_id = p.id WHERE n.usuario_id = ? ORDER BY n.generada_en DESC LIMIT 5");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="tarjeta">
    <h2>Bienvenido, <?php echo htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <p>Panel de empleado - Sistema de Nominas</p>
</div>

<div class="tarjeta">
    <h3>Mis Datos</h3>
    <div class="form-row">
        <div><strong>Email:</strong> <?php echo htmlspecialchars($empleado['email'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Telefono:</strong> <?php echo htmlspecialchars($empleado['telefono'] ?? 'No registrado', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Cargo:</strong> <?php echo htmlspecialchars($empleado['cargo_nombre'] ?? 'No asignado', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Fecha Ingreso:</strong> <?php echo $empleado['fecha_ingreso'] ? date('d/m/Y', strtotime($empleado['fecha_ingreso'])) : 'No registrada'; ?></div>
    </div>
</div>

<?php if ($result && $result->num_rows > 0): ?>
<div class="tarjeta">
    <h3>Ultimas Nominas</h3>
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Salario Neto</th>
                    <th>Generada</th>
                    <th>Accion</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['periodo'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo formatoMoneda($row['salario_neto']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['generada_en'])); ?></td>
                    <td><a href="ver_bauche.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primario">Ver Bauche</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="mensaje mensaje-info">No tiene nominas registradas aun.</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
