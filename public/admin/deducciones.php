<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$busqueda = isset($_GET['busqueda']) ? sanitizarInput($_GET['busqueda']) : '';
$filtro_tipo = isset($_GET['tipo']) ? sanitizarInput($_GET['tipo']) : '';

$where = "WHERE 1=1";
$params = [];
$tipos = '';

if ($busqueda !== '') {
    $where .= " AND (nombre LIKE ? OR codigo LIKE ?)";
    $param_busqueda = "%$busqueda%";
    $params = [$param_busqueda, $param_busqueda];
    $tipos = 'ss';
}
if ($filtro_tipo !== '') {
    $where .= " AND tipo = ?";
    $params[] = $filtro_tipo;
    $tipos .= 's';
}

$sql = "SELECT * FROM deducciones $where ORDER BY es_legal DESC, nombre";
$stmt = $mysqli->prepare($sql);
if ($params) { $stmt->bind_param($tipos, ...$params); }
$stmt->execute();
$deducciones = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <h1>Gestion de Deducciones</h1>
    <a href="deduccion_form.php" class="btn btn-primario">+ Nueva Deduccion</a>
</div>

<?php echo mostrarMensaje(); ?>

<form method="GET" class="filtros">
    <label for="busqueda">Buscar:</label>
    <input type="text" name="busqueda" id="busqueda" placeholder="Nombre o codigo..." value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
    <label for="tipo">Tipo:</label>
    <select name="tipo" id="tipo">
        <option value="">Todos</option>
        <option value="fijo" <?php echo $filtro_tipo === 'fijo' ? 'selected' : ''; ?>>Fijo</option>
        <option value="porcentaje" <?php echo $filtro_tipo === 'porcentaje' ? 'selected' : ''; ?>>Porcentaje</option>
        <option value="legal" <?php echo $filtro_tipo === 'legal' ? 'selected' : ''; ?>>Legal</option>
    </select>
    <button type="submit" class="btn btn-sm btn-primario">Filtrar</button>
    <?php if ($busqueda !== '' || $filtro_tipo !== ''): ?>
    <a href="deducciones.php" class="btn btn-sm btn-secundario">Limpiar</a>
    <?php endif; ?>
</form>

<div class="tarjeta">
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Aplica</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($deducciones->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center">No se encontraron deducciones.</td></tr>
                <?php endif; ?>
                <?php while ($d = $deducciones->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?php echo htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><span class="badge badge-info"><?php echo ucfirst($d['tipo']); ?></span></td>
                    <td>
                        <?php if ($d['tipo'] === 'porcentaje'): ?>
                            <?php echo number_format($d['porcentaje'] ?? 0, 2); ?>%
                        <?php elseif ($d['tipo'] === 'fijo'): ?>
                            <?php echo formatoDual($d['monto_fijo'] ?? 0, $mysqli); ?>
                        <?php else: ?>
                            <span class="badge badge-legal">Tabla ISLR</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($d['aplica_a'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if ($d['es_legal']): ?>
                            <span class="badge badge-legal">Legal</span>
                        <?php elseif ($d['es_patronal']): ?>
                            <span class="badge badge-patronal">Patronal</span>
                        <?php else: ?>
                            <span class="badge badge-opcional">Opcional</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $d['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>">
                            <?php echo $d['activo'] ? 'ACTIVO' : 'INACTIVO'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="deduccion_form.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-primario">Editar</a>
                        <button class="btn btn-sm btn-peligro" onclick="eliminarDeduccion(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8'); ?>')">Eliminar</button>
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
function eliminarDeduccion(id, nombre) {
    confirmarModal('Eliminar la deduccion "' + nombre + '"?', function(ok) {
        if (!ok) return;
        mostrarSpinner();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'deduccion_eliminar.php', true);
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
