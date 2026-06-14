<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$filtro_periodo = isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : 0;
$filtro_empleado = isset($_GET['empleado']) ? sanitizarInput($_GET['empleado']) : '';

$sql = "SELECT n.*, u.nombres, u.apellidos, u.cedula, p.nombre as periodo_nombre, p.tipo as periodo_tipo,
        p.fecha_inicio, p.fecha_fin, a.nombres as g_nombres, a.apellidos as g_apellidos
        FROM nominas n
        JOIN usuarios u ON n.usuario_id = u.id
        JOIN periodos_nomina p ON n.periodo_id = p.id
        JOIN usuarios a ON n.generada_por = a.id
        WHERE 1=1";
$params = [];
$tipos = '';

if ($filtro_periodo > 0) {
    $sql .= " AND n.periodo_id = ?";
    $params[] = $filtro_periodo;
    $tipos .= 'i';
}
if ($filtro_empleado !== '') {
    $sql .= " AND (u.nombres LIKE ? OR u.apellidos LIKE ? OR u.cedula LIKE ?)";
    $busq = "%$filtro_empleado%";
    $params = array_merge($params, [$busq, $busq, $busq]);
    $tipos .= 'sss';
}
$sql .= " ORDER BY n.generada_en DESC";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$nominas = $stmt->get_result();

$periodos = $mysqli->query("SELECT id, nombre FROM periodos_nomina ORDER BY fecha_inicio DESC");

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<h1>Nominas Generadas</h1>

<?php echo mostrarMensaje(); ?>

<form method="GET" class="filtros">
    <label for="periodo_id">Periodo:</label>
    <select name="periodo_id" id="periodo_id">
        <option value="">Todos</option>
        <?php while ($per = $periodos->fetch_assoc()): ?>
        <option value="<?php echo $per['id']; ?>" <?php echo $filtro_periodo === (int)$per['id'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($per['nombre'], ENT_QUOTES, 'UTF-8'); ?>
        </option>
        <?php endwhile; ?>
    </select>
    <label for="empleado">Empleado:</label>
    <input type="text" name="empleado" id="empleado" placeholder="Nombre o cedula..."
           value="<?php echo htmlspecialchars($filtro_empleado, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" class="btn btn-sm btn-primario">Filtrar</button>
    <?php if ($filtro_periodo > 0 || $filtro_empleado !== ''): ?>
    <a href="ver_nominas.php" class="btn btn-sm btn-secundario">Limpiar</a>
    <?php endif; ?>
    <a href="exportar_nomina.php?periodo_id=<?php echo $filtro_periodo; ?>" class="btn btn-sm btn-exito">Exportar CSV</a>
</form>

<div class="tarjeta">
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Cedula</th>
                    <th>Periodo</th>
                    <th>Salario Neto</th>
                    <th>Costo Total</th>
                    <th>Generada</th>
                    <th>Acciones</th>
                    <th>PDF</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($nominas->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center">No hay nominas generadas.</td></tr>
                <?php endif; ?>
                <?php while ($nom = $nominas->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($nom['nombres'] . ' ' . $nom['apellidos'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($nom['cedula'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($nom['periodo_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?php echo formatoDual($nom['salario_neto'], $mysqli); ?></strong></td>
                    <td><?php echo formatoDual($nom['costo_patronal'], $mysqli); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($nom['generada_en'])); ?></td>
                    <td><a href="ver_bauche_admin.php?id=<?php echo $nom['id']; ?>" class="btn btn-sm btn-primario">Ver Bauche</a></td>
                    <td><a href="descargar_bauche.php?id=<?php echo $nom['id']; ?>" class="btn btn-sm btn-primario">Imprimir</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
