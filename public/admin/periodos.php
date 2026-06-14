<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$filtro_tipo = isset($_GET['tipo']) ? sanitizarInput($_GET['tipo']) : '';

$sql = "SELECT p.*, u.nombres, u.apellidos FROM periodos_nomina p JOIN usuarios u ON p.creado_por = u.id WHERE 1=1";
$params = [];
$tipos = '';
if ($filtro_tipo !== '') {
    $sql .= " AND p.tipo = ?";
    $params[] = $filtro_tipo;
    $tipos .= 's';
}
$sql .= " ORDER BY p.fecha_inicio DESC";
$stmt = $mysqli->prepare($sql);
if ($params) { $stmt->bind_param($tipos, ...$params); }
$stmt->execute();
$periodos = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <h1>Gestion de Periodos de Nomina</h1>
    <a href="periodo_formulario.php" class="btn btn-primario">+ Nuevo Periodo</a>
</div>

<?php echo mostrarMensaje(); ?>

<form method="GET" class="filtros">
    <label for="tipo">Filtrar por tipo:</label>
    <select name="tipo" id="tipo" onchange="this.form.submit()">
        <option value="">Todos</option>
        <option value="semanal" <?php echo $filtro_tipo === 'semanal' ? 'selected' : ''; ?>>Semanal</option>
        <option value="quincenal" <?php echo $filtro_tipo === 'quincenal' ? 'selected' : ''; ?>>Quincenal</option>
        <option value="mensual" <?php echo $filtro_tipo === 'mensual' ? 'selected' : ''; ?>>Mensual</option>
    </select>
    <?php if ($filtro_tipo !== ''): ?>
    <a href="periodos.php" class="btn btn-sm btn-secundario">Limpiar</a>
    <?php endif; ?>
</form>

<div class="tarjeta">
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Dias Habiles</th>
                    <th>Estado</th>
                    <th>Creado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($periodos->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center">No hay periodos registrados.</td></tr>
                <?php endif; ?>
                <?php while ($per = $periodos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($per['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge badge-info"><?php echo ucfirst($per['tipo']); ?></span></td>
                    <td><?php echo date('d/m/Y', strtotime($per['fecha_inicio'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($per['fecha_fin'])); ?></td>
                    <td><?php echo $per['dias_habiles']; ?></td>
                    <td>
                        <span class="badge <?php echo $per['estado'] === 'abierto' ? 'badge-abierto' : 'badge-cerrado'; ?>">
                            <?php echo strtoupper($per['estado']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($per['nombres'] . ' ' . $per['apellidos'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if ($per['estado'] === 'abierto'): ?>
                        <a href="periodo_formulario.php?id=<?php echo $per['id']; ?>" class="btn btn-sm btn-primario">Editar</a>
                        <button class="btn btn-sm btn-advertencia" onclick="cerrarPeriodo(<?php echo $per['id']; ?>)">Cerrar</button>
                        <?php else: ?>
                        <span class="badge badge-cerrado">Cerrado</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="spinner-overlay" id="spinner-overlay">
    <div class="spinner"></div>
</div>
<div class="modal-overlay" id="modal-confirm">
    <div class="modal">
        <div class="modal-body">
            <p id="modal-texto"></p>
            <div class="modal-acciones">
                <button class="btn btn-exito" id="modal-btn-si">Si</button>
                <button class="btn btn-secundario" id="modal-btn-no">No</button>
            </div>
        </div>
    </div>
</div>

<script>
function cerrarPeriodo(id) {
    confirmarModal('¿Cerrar este periodo? No se podran generar nominas en el.', function(ok) {
        if (!ok) return;
        mostrarSpinner();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../includes/procesar/periodo_guardar.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            ocultarSpinner();
            if (xhr.status === 200) { location.reload(); }
            else { mostrarToast('Error al cerrar periodo', 'error'); }
        };
        xhr.send('accion=cerrar&id=' + id + '&csrf_token=<?php echo generarTokenCSRF(); ?>');
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
