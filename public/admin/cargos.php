<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$busqueda = isset($_GET['busqueda']) ? sanitizarInput($_GET['busqueda']) : '';

$where = "WHERE 1=1";
$params = [];
$tipos = '';

if ($busqueda !== '') {
    $where .= " AND (nombre LIKE ? OR codigo LIKE ?)";
    $param_busqueda = "%$busqueda%";
    $params = [$param_busqueda, $param_busqueda];
    $tipos = 'ss';
}

$sql = "SELECT c.*, (SELECT COUNT(*) FROM usuarios WHERE cargo_id = c.id AND activo = 1) as empleados_asignados FROM cargos c $where ORDER BY c.nombre";
$stmt = $mysqli->prepare($sql);
if ($params) { $stmt->bind_param($tipos, ...$params); }
$stmt->execute();
$cargos = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <h1>Gestion de Cargos</h1>
    <a href="cargo_form.php" class="btn btn-primario">+ Nuevo Cargo</a>
</div>

<?php echo mostrarMensaje(); ?>

<form method="GET" class="filtros">
    <label for="busqueda">Buscar:</label>
    <input type="text" name="busqueda" id="busqueda" placeholder="Nombre o codigo..." value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" class="btn btn-sm btn-primario">Buscar</button>
    <?php if ($busqueda !== ''): ?>
    <a href="cargos.php" class="btn btn-sm btn-secundario">Limpiar</a>
    <?php endif; ?>
</form>

<div class="tarjeta">
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>Salario Base</th>
                    <th>Cesta Ticket</th>
                    <th>Transporte</th>
                    <th>Empleados</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($cargos->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center">No se encontraron cargos.</td></tr>
                <?php endif; ?>
                <?php while ($c = $cargos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?php echo htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><?php echo formatoDual($c['salario_base'], $mysqli); ?></td>
                    <td><?php echo $c['incluye_cestaticket'] ? 'Si' : 'No'; ?></td>
                    <td><?php echo $c['incluye_transporte'] ? 'Si' : 'No'; ?></td>
                    <td><?php echo (int)$c['empleados_asignados']; ?></td>
                    <td>
                        <span class="badge <?php echo $c['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>">
                            <?php echo $c['activo'] ? 'ACTIVO' : 'INACTIVO'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="cargo_form.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primario">Editar</a>
                        <button class="btn btn-sm btn-peligro" onclick="eliminarCargo(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8'); ?>')">Eliminar</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="spinner-overlay" id="spinner-overlay"><div class="spinner"></div></div>
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
function eliminarCargo(id, nombre) {
    confirmarModal('Eliminar el cargo "' + nombre + '"? Los empleados asignados quedaran sin cargo.', function(ok) {
        if (!ok) return;
        mostrarSpinner();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'cargo_eliminar.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function() {
            ocultarSpinner();
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success) { location.reload(); }
                else { mostrarToast(resp.mensaje || 'Error al eliminar', 'error'); }
            } catch(e) { mostrarToast('Error de comunicacion', 'error'); }
        };
        xhr.onerror = function() { ocultarSpinner(); mostrarToast('Error de comunicacion', 'error'); };
        xhr.send(JSON.stringify({id: id}));
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
