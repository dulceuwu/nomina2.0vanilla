<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editando = $id > 0;
$empleado = [
    'cedula' => '', 'nombres' => '', 'apellidos' => '', 'email' => '',
    'telefono' => '', 'direccion' => '', 'fecha_nacimiento' => '',
    'fecha_ingreso' => '', 'cargo_id' => '', 'salario_personalizado' => '',
    'rol' => 'empleado', 'activo' => 1
];
$emp_deducciones = [];
$emp_asignaciones = [];
$emp_prestaciones = [];

if ($editando) {
    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $empleado = $row;
    } else {
        $_SESSION['mensaje_flash'] = 'Empleado no encontrado.';
        $_SESSION['mensaje_tipo'] = 'error';
        redirigir('empleados.php');
    }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT deduccion_id FROM empleado_deducciones WHERE usuario_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) { $emp_deducciones[] = $row['deduccion_id']; }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT asignacion_id FROM empleado_asignaciones WHERE usuario_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) { $emp_asignaciones[] = $row['asignacion_id']; }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT prestacion_id FROM empleado_prestaciones WHERE usuario_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) { $emp_prestaciones[] = $row['prestacion_id']; }
    $stmt->close();
}

$cargos = $mysqli->query("SELECT id, codigo, nombre, salario_base, incluye_cestaticket, incluye_transporte FROM cargos WHERE activo = 1 ORDER BY nombre");
$deducciones = $mysqli->query("SELECT id, codigo, nombre, tipo, porcentaje, monto_fijo, es_patronal FROM deducciones WHERE activo = 1 ORDER BY es_legal DESC, nombre");
$asignaciones = $mysqli->query("SELECT id, codigo, nombre, tipo, porcentaje, monto_fijo, monto_diario FROM asignaciones WHERE activo = 1 ORDER BY es_legal DESC, nombre");
$prestaciones = $mysqli->query("SELECT id, codigo, nombre, tipo_calculo, dias_ano FROM prestaciones WHERE activo = 1 ORDER BY es_legal DESC, nombre");

$token = generarTokenCSRF();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<h1><?php echo $editando ? 'Editar Empleado' : 'Nuevo Empleado'; ?></h1>

<?php echo mostrarMensaje(); ?>

<div class="tarjeta">
    <form action="../includes/procesar/empleado_guardar.php" method="POST" data-validar>
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <h2>Datos Personales</h2>
        <div class="form-row">
            <div class="form-grupo">
                <label for="cedula">Cedula *</label>
                <input type="text" name="cedula" id="cedula" required data-tipo="cedula"
                       value="<?php echo htmlspecialchars($empleado['cedula'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="V12345678">
            </div>
            <div class="form-grupo">
                <label for="nombres">Nombres *</label>
                <input type="text" name="nombres" id="nombres" required
                       value="<?php echo htmlspecialchars($empleado['nombres'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Nombres completos">
            </div>
            <div class="form-grupo">
                <label for="apellidos">Apellidos *</label>
                <input type="text" name="apellidos" id="apellidos" required
                       value="<?php echo htmlspecialchars($empleado['apellidos'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Apellidos completos">
            </div>
        </div>

        <div class="form-row">
            <div class="form-grupo">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" required
                       value="<?php echo htmlspecialchars($empleado['email'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="correo@ejemplo.com">
            </div>
            <div class="form-grupo">
                <label for="telefono">Telefono</label>
                <input type="text" name="telefono" id="telefono" data-tipo="numero"
                       value="<?php echo htmlspecialchars($empleado['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="04125551234">
            </div>
            <div class="form-grupo">
                <label for="fecha_nacimiento">Fecha Nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                       value="<?php echo htmlspecialchars($empleado['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div class="form-grupo">
            <label for="direccion">Direccion</label>
            <textarea name="direccion" id="direccion" rows="2" placeholder="Direccion de habitacion..."><?php echo htmlspecialchars($empleado['direccion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <hr class="mb-4">

        <h2>Datos Laborales</h2>
        <div class="form-row">
            <?php if (!$editando): ?>
            <div class="form-grupo">
                <label for="rol">Rol *</label>
                <select name="rol" id="rol" onchange="toggleCargoRequerido()">
                    <option value="empleado" <?php echo ($empleado['rol'] ?? 'empleado') === 'empleado' ? 'selected' : ''; ?>>Empleado</option>
                    <option value="admin" <?php echo ($empleado['rol'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-grupo">
                <label for="fecha_ingreso">Fecha Ingreso *</label>
                <input type="date" name="fecha_ingreso" id="fecha_ingreso" required
                       value="<?php echo htmlspecialchars($empleado['fecha_ingreso'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-grupo">
                <label for="cargo_id">Cargo</label>
                <select name="cargo_id" id="cargo_id" onchange="actualizarSalarioBase()">
                    <option value="">Seleccione un cargo...</option>
                    <?php while ($c = $cargos->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>"
                            data-salario="<?php echo $c['salario_base']; ?>"
                            <?php echo $empleado['cargo_id'] == $c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['codigo'] . ' - ' . $c['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                        (<?php echo formatoMoneda($c['salario_base']); ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
                <span class="salario-label">El salario base lo hereda del cargo.</span>
            </div>
            <div class="form-grupo">
                <label for="salario_personalizado">Salario Personalizado (Bs.)</label>
                <input type="number" name="salario_personalizado" id="salario_personalizado" step="0.01" min="0"
                       value="<?php echo htmlspecialchars($empleado['salario_personalizado'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Dejar vacio para usar el del cargo">
                <span class="salario-label">Si se deja vacio, se usara el salario del cargo seleccionado.</span>
            </div>
        </div>

        <?php if (!$editando): ?>
        <div class="form-row">
            <div class="form-grupo">
                <label for="password">Contrasena Inicial *</label>
                <input type="password" name="password" id="password" required placeholder="Contrasena temporal">
            </div>
        </div>
        <?php endif; ?>

        <hr class="mb-4">

        <h2>Deducciones Aplicables</h2>
        <p class="salario-label mb-3">Seleccione las deducciones que aplican a este empleado.</p>
        <div class="conceptos-grid">
            <?php while ($d = $deducciones->fetch_assoc()): ?>
            <label class="concepto-item">
                <input type="checkbox" name="deducciones[]" value="<?php echo $d['id']; ?>"
                       <?php echo in_array($d['id'], $emp_deducciones) ? 'checked' : ''; ?>
                       <?php echo $d['es_patronal'] ? 'disabled' : ''; ?>>
                <div class="concepto-info">
                    <div class="concepto-nombre"><?php echo htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="concepto-detalle">
                        <?php echo $d['tipo'] === 'porcentaje' ? number_format($d['porcentaje'] ?? 0, 2) . '%' : ($d['tipo'] === 'fijo' ? formatoDual($d['monto_fijo'] ?? 0, $mysqli) : 'Tabla legal'); ?>
                        <?php echo $d['es_patronal'] ? ' - (Patronal - automatico)' : ''; ?>
                    </div>
                </div>
            </label>
            <?php endwhile; ?>
        </div>

        <hr class="mb-4">

        <h2>Asignaciones Aplicables</h2>
        <p class="salario-label mb-3">Seleccione las asignaciones que aplican a este empleado.</p>
        <div class="conceptos-grid">
            <?php while ($a = $asignaciones->fetch_assoc()): ?>
            <label class="concepto-item">
                <input type="checkbox" name="asignaciones[]" value="<?php echo $a['id']; ?>"
                       <?php echo in_array($a['id'], $emp_asignaciones) ? 'checked' : ''; ?>>
                <div class="concepto-info">
                    <div class="concepto-nombre"><?php echo htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="concepto-detalle">
                        <?php echo $a['tipo'] === 'porcentaje' ? number_format($a['porcentaje'] ?? 0, 2) . '%' : ($a['tipo'] === 'fijo' ? formatoDual($a['monto_fijo'] ?? 0, $mysqli) : ($a['tipo'] === 'diario' ? ($a['monto_diario'] ? formatoDual($a['monto_diario'], $mysqli) . '/dia' : 'Formula') : 'Salario base')); ?>
                    </div>
                </div>
            </label>
            <?php endwhile; ?>
        </div>

        <hr class="mb-4">

        <h2>Prestaciones Aplicables</h2>
        <p class="salario-label mb-3">Seleccione las prestaciones que aplican a este empleado.</p>
        <div class="conceptos-grid">
            <?php while ($p = $prestaciones->fetch_assoc()): ?>
            <label class="concepto-item">
                <input type="checkbox" name="prestaciones[]" value="<?php echo $p['id']; ?>"
                       <?php echo in_array($p['id'], $emp_prestaciones) ? 'checked' : ''; ?>>
                <div class="concepto-info">
                    <div class="concepto-nombre"><?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="concepto-detalle">
                        <?php echo $p['tipo_calculo'] === 'dias_ano' ? ($p['dias_ano'] ?? '') . ' dias/ano' : 'Formula'; ?>
                    </div>
                </div>
            </label>
            <?php endwhile; ?>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primario"><?php echo $editando ? 'Actualizar Empleado' : 'Crear Empleado'; ?></button>
            <a href="empleados.php" class="btn btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

<script>
function actualizarSalarioBase() {
    var select = document.getElementById('cargo_id');
    var selected = select.options[select.selectedIndex];
    if (selected && selected.dataset.salario) {
        document.getElementById('salario_personalizado').placeholder = 'Cargo: ' + selected.dataset.salario;
    }
}
function toggleCargoRequerido() {
    var rol = document.getElementById('rol').value;
    var cargo = document.getElementById('cargo_id');
    if (rol === 'admin') {
        cargo.removeAttribute('required');
    } else {
        cargo.setAttribute('required', 'required');
    }
}
<?php if (!$editando): ?>toggleCargoRequerido();<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
