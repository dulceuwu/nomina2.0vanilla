<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$periodos = $mysqli->query("SELECT id, nombre, tipo, fecha_inicio, fecha_fin, dias_habiles FROM periodos_nomina WHERE estado='abierto' ORDER BY fecha_inicio DESC");
$empleados = $mysqli->query("SELECT u.id, u.cedula, u.nombres, u.apellidos, COALESCE(u.salario_personalizado, c.salario_base, 0) as salario_base, u.fecha_ingreso, c.nombre as cargo_nombre FROM usuarios u LEFT JOIN cargos c ON u.cargo_id = c.id WHERE u.rol='empleado' AND u.activo=1 ORDER BY u.apellidos, u.nombres");

$token = generarTokenCSRF();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<h1>Generar Nominas</h1>

<?php echo mostrarMensaje(); ?>

<?php if ($periodos->num_rows === 0): ?>
<div class="mensaje mensaje-advertencia">No hay periodos abiertos. <a href="periodo_formulario.php">Cree un periodo</a> primero.</div>
<?php else: ?>

<form id="form-generar-nomina" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

    <div class="filtros">
        <label for="periodo_id">Periodo a generar:</label>
        <select name="periodo_id" id="periodo_id" required>
            <option value="">Seleccione un periodo...</option>
            <?php while ($per = $periodos->fetch_assoc()): ?>
            <option value="<?php echo $per['id']; ?>">
                <?php echo htmlspecialchars($per['nombre'] . ' (' . ucfirst($per['tipo']) . ' - ' . date('d/m/Y', strtotime($per['fecha_inicio'])) . ' al ' . date('d/m/Y', strtotime($per['fecha_fin'])) . ')', ENT_QUOTES, 'UTF-8'); ?>
            </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="tarjeta">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <h3>Seleccionar Empleados</h3>
            <label class="checkbox-label">
                <input type="checkbox" id="seleccionar-todos">
                Seleccionar Todos
            </label>
        </div>

        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th class="seleccion"></th>
                        <th>Cedula</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Salario Base</th>
                        <th>Cargo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($empleados->num_rows === 0): ?>
                    <tr><td colspan="6" class="text-center">No hay empleados activos.</td></tr>
                    <?php endif; ?>
                    <?php while ($emp = $empleados->fetch_assoc()): ?>
                    <tr>
                        <td class="seleccion">
                            <input type="checkbox" name="empleados_seleccionados[]" value="<?php echo $emp['id']; ?>" class="empleado-checkbox">
                        </td>
                        <td><?php echo htmlspecialchars($emp['cedula'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($emp['nombres'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($emp['apellidos'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo formatoDual($emp['salario_base'], $mysqli); ?></td>
                        <td><?php echo htmlspecialchars($emp['cargo_nombre'] ?? 'Sin cargo', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="form-acciones">
        <button type="submit" class="btn btn-exito" id="btn-generar">Generar Nominas Seleccionadas</button>
    </div>
</form>

<?php endif; ?>

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
document.getElementById('form-generar-nomina')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var periodo = document.getElementById('periodo_id').value;
    if (!periodo) {
        mostrarToast('Seleccione un periodo.', 'error');
        return;
    }
    var checkboxes = document.querySelectorAll('.empleado-checkbox:checked');
    if (checkboxes.length === 0) {
        mostrarToast('Seleccione al menos un empleado.', 'error');
        return;
    }
    confirmarModal('¿Generar nominas para ' + checkboxes.length + ' empleado(s) en el periodo seleccionado?', function(ok) {
        if (!ok) return;
        mostrarSpinner();
        document.getElementById('btn-generar').disabled = true;
        var formData = new FormData(e.target);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../includes/procesar/generar_nomina_procesar.php', true);
        xhr.onload = function() {
            ocultarSpinner();
            document.getElementById('btn-generar').disabled = false;
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    mostrarToast(resp.mensaje + ' (' + resp.generadas + ' nominas generadas, ' + resp.errores + ' errores)', 'exito');
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    mostrarToast(resp.mensaje || 'Error al generar nominas', 'error');
                }
            } catch(e) {
                mostrarToast('Error en la respuesta del servidor', 'error');
            }
        };
        xhr.onerror = function() {
            ocultarSpinner();
            document.getElementById('btn-generar').disabled = false;
            mostrarToast('Error de comunicacion', 'error');
        };
        xhr.send(formData);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
