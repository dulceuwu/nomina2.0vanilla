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

$sql = "SELECT * FROM prestaciones $where ORDER BY es_legal DESC, nombre";
$stmt = $mysqli->prepare($sql);
if ($params) { $stmt->bind_param($tipos, ...$params); }
$stmt->execute();
$prestaciones = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <h1>Gestion de Prestaciones</h1>
    <a href="prestacion_form.php" class="btn btn-primario">+ Nueva Prestacion</a>
</div>

<?php echo mostrarMensaje(); ?>

<form method="GET" class="filtros">
    <label for="busqueda">Buscar:</label>
    <input type="text" name="busqueda" id="busqueda" placeholder="Nombre o codigo..." value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" class="btn btn-sm btn-primario">Buscar</button>
    <?php if ($busqueda !== ''): ?>
    <a href="prestaciones.php" class="btn btn-sm btn-secundario">Limpiar</a>
    <?php endif; ?>
</form>

<div class="tarjeta">
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>Tipo Calculo</th>
                    <th>Valor</th>
                    <th>Aplica</th>
                    <th>Legal</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($prestaciones->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center">No se encontraron prestaciones.</td></tr>
                <?php endif; ?>
                <?php while ($p = $prestaciones->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><span class="badge badge-info"><?php echo $p['tipo_calculo'] === 'dias_ano' ? 'Dias año' : str_replace('_', ' ', ucfirst($p['tipo_calculo'])); ?></span></td>
                    <td>
                        <?php if ($p['dias_ano']): ?>
                            <?php echo $p['dias_ano']; ?> dias/año
                        <?php elseif ($p['porcentaje']): ?>
                            <?php echo number_format($p['porcentaje'] ?? 0, 2); ?>%
                        <?php elseif ($p['monto_fijo']): ?>
                            <?php echo formatoDual($p['monto_fijo'], $mysqli); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($p['aplica_a'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $p['es_legal'] ? '<span class="badge badge-legal">Si</span>' : '<span class="badge badge-opcional">No</span>'; ?></td>
                    <td>
                        <span class="badge <?php echo $p['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>">
                            <?php echo $p['activo'] ? 'ACTIVO' : 'INACTIVO'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="prestacion_form.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primario">Editar</a>
                        <button class="btn btn-sm btn-peligro" onclick="eliminarPrestacion(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>')">Eliminar</button>
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
function eliminarPrestacion(id, nombre) {
    confirmarModal('Eliminar la prestacion "' + nombre + '"?', function(ok) {
        if (!ok) return;
        mostrarSpinner();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'prestacion_eliminar.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function() {
            ocultarSpinner();
            try { var resp = JSON.parse(xhr.responseText); if (resp.success) { location.reload(); } else { mostrarToast(resp.mensaje || 'Error', 'error'); } }
            catch(e) { mostrarToast('Error de comunicacion', 'error'); }
        };
        xhr.onerror = function() { ocultarSpinner(); mostrarToast('Error de comunicacion', 'error'); };
        xhr.send(JSON.stringify({id: id}));
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
