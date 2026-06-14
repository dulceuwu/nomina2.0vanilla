<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$por_pagina = 15;
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $por_pagina;
$busqueda = isset($_GET['busqueda']) ? sanitizarInput($_GET['busqueda']) : '';

$where = "WHERE rol='empleado'";
$params = [];
$tipos = '';

if ($busqueda !== '') {
    $where .= " AND (nombres LIKE ? OR apellidos LIKE ? OR cedula LIKE ? OR email LIKE ?)";
    $param_busqueda = "%$busqueda%";
    $params = [$param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda];
    $tipos = 'ssss';
}

$count_sql = "SELECT COUNT(*) as total FROM usuarios $where";
$count_stmt = $mysqli->prepare($count_sql);
if ($params) {
    $count_stmt->bind_param($tipos, ...$params);
}
$count_stmt->execute();
$total_registros = $count_stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);

$sql = "SELECT u.id, u.cedula, u.nombres, u.apellidos, u.email, u.telefono, COALESCE(u.salario_personalizado, c.salario_base) as salario_base, u.activo, c.nombre as cargo_nombre FROM usuarios u LEFT JOIN cargos c ON u.cargo_id = c.id $where ORDER BY u.id DESC LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($tipos . 'ii', ...array_merge($params, [$por_pagina, $offset]));
} else {
    $stmt->bind_param('ii', $por_pagina, $offset);
}
$stmt->execute();
$empleados = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <h1>Gestion de Empleados</h1>
    <a href="empleado_formulario.php" class="btn btn-primario">+ Nuevo Empleado</a>
</div>

<form method="GET" class="filtros">
    <label for="busqueda">Buscar:</label>
    <input type="text" name="busqueda" id="busqueda" placeholder="Cedula, nombre o email..." value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" class="btn btn-sm btn-primario">Buscar</button>
    <?php if ($busqueda !== ''): ?>
    <a href="empleados.php" class="btn btn-sm btn-secundario">Limpiar</a>
    <?php endif; ?>
</form>

<div class="tarjeta">
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Cedula</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Email</th>
                    <th>Salario Base</th>
                    <th>Cargo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($empleados->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center">No se encontraron empleados.</td></tr>
                <?php endif; ?>
                <?php while ($emp = $empleados->fetch_assoc()): ?>
                <tr id="fila-<?php echo $emp['id']; ?>">
                    <td><?php echo htmlspecialchars($emp['cedula'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($emp['nombres'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($emp['apellidos'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($emp['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo formatoDual($emp['salario_base'], $mysqli); ?></td>
                    <td><?php echo htmlspecialchars($emp['cargo_nombre'] ?? 'Sin cargo', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <span class="badge <?php echo $emp['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>"
                              id="badge-<?php echo $emp['id']; ?>">
                            <?php echo $emp['activo'] ? 'ACTIVO' : 'INACTIVO'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="empleado_formulario.php?id=<?php echo $emp['id']; ?>" class="btn btn-sm btn-primario">Editar</a>
                        <button class="btn btn-sm <?php echo $emp['activo'] ? 'btn-advertencia' : 'btn-exito'; ?>"
                                onclick="cambiarEstado(<?php echo $emp['id']; ?>, <?php echo $emp['activo'] ? 0 : 1; ?>)"
                                id="btn-<?php echo $emp['id']; ?>">
                            <?php echo $emp['activo'] ? 'Desactivar' : 'Activar'; ?>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($total_paginas > 1): ?>
<div class="paginacion">
    <?php if ($pagina > 1): ?>
    <a href="?pagina=1<?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>">&laquo;</a>
    <a href="?pagina=<?php echo $pagina - 1; ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>">&lsaquo;</a>
    <?php endif; ?>
    <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
    <a href="?pagina=<?php echo $i; ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>" class="<?php echo $i === $pagina ? 'activo' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    <?php if ($pagina < $total_paginas): ?>
    <a href="?pagina=<?php echo $pagina + 1; ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>">&rsaquo;</a>
    <a href="?pagina=<?php echo $total_paginas; ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>">&raquo;</a>
    <?php endif; ?>
</div>
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
function cambiarEstado(id, nuevoEstado) {
    var accion = nuevoEstado === 1 ? 'activar' : 'desactivar';
    confirmarModal('¿Esta seguro de ' + accion + ' este empleado?', function(confirmado) {
        if (!confirmado) return;
        mostrarSpinner();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'empleado_cambiar_estado.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function() {
            ocultarSpinner();
            if (xhr.status === 200) {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    actualizarFila(id, nuevoEstado);
                    mostrarToast(resp.mensaje, 'exito');
                } else {
                    mostrarToast(resp.mensaje || 'Error al actualizar', 'error');
                }
            } else {
                mostrarToast('Error de comunicacion', 'error');
            }
        };
        xhr.onerror = function() {
            ocultarSpinner();
            mostrarToast('Error de comunicacion', 'error');
        };
        xhr.send(JSON.stringify({id: id, accion: accion}));
    });
}

function actualizarFila(id, nuevoEstado) {
    var badge = document.getElementById('badge-' + id);
    var btn = document.getElementById('btn-' + id);
    if (!badge || !btn) return;
    if (nuevoEstado === 1) {
        badge.className = 'badge badge-activo';
        badge.textContent = 'ACTIVO';
        btn.className = 'btn btn-sm btn-advertencia';
        btn.textContent = 'Desactivar';
        btn.setAttribute('onclick', 'cambiarEstado(' + id + ', 0)');
    } else {
        badge.className = 'badge badge-inactivo';
        badge.textContent = 'INACTIVO';
        btn.className = 'btn btn-sm btn-exito';
        btn.textContent = 'Activar';
        btn.setAttribute('onclick', 'cambiarEstado(' + id + ', 1)');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
