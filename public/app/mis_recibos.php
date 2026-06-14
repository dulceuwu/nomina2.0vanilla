<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('empleado');

$user_id = (int)$_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT n.id, n.salario_neto, n.total_asignaciones, n.total_deducciones, n.generada_en, p.nombre as periodo, p.fecha_inicio, p.fecha_fin
    FROM nominas n
    JOIN periodos_nomina p ON n.periodo_id = p.id
    WHERE n.usuario_id = ?
    ORDER BY n.generada_en DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$nominas = $stmt->get_result();
$stmt->close();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<h1>Mis Bauches de Pago</h1>

<?php if ($nominas->num_rows === 0): ?>
<div class="mensaje mensaje-info">No tiene nominas registradas aun.</div>
<?php else: ?>
<div class="tarjeta">
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Fecha Generacion</th>
                    <th>Total Asignaciones</th>
                    <th>Total Deducciones</th>
                    <th>Salario Neto</th>
                    <th>Accion</th>
                    <th>PDF</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($nom = $nominas->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($nom['periodo'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($nom['generada_en'])); ?></td>
                    <td><?php echo formatoDual($nom['total_asignaciones'], $mysqli); ?></td>
                    <td><?php echo formatoDual($nom['total_deducciones'], $mysqli); ?></td>
                    <td><strong><?php echo formatoDual($nom['salario_neto'], $mysqli); ?></strong></td>
                    <td><a href="ver_bauche.php?id=<?php echo $nom['id']; ?>" class="btn btn-sm btn-primario">Ver Bauche</a></td>
                    <td><a href="descargar_bauche.php?id=<?php echo $nom['id']; ?>" class="btn btn-sm btn-primario">Imprimir</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
