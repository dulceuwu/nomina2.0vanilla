<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editando = $id > 0;
$prestacion = ['codigo' => '', 'nombre' => '', 'descripcion' => '', 'tipo_calculo' => 'dias_ano', 'dias_ano' => null, 'porcentaje' => null, 'monto_fijo' => null, 'monto_fijo_usd' => null, 'aplica_a' => 'todos', 'es_legal' => 0, 'activo' => 1];

if ($editando) {
    $stmt = $mysqli->prepare("SELECT * FROM prestaciones WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) { $prestacion = $row; }
    else { redirigir('prestaciones.php'); }
    $stmt->close();
}

$tasa_dolar = obtenerTasaDolar($mysqli);
$token = generarTokenCSRF();
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<h1><?php echo $editando ? 'Editar Prestacion' : 'Nueva Prestacion'; ?></h1>

<?php echo mostrarMensaje(); ?>

<div class="tarjeta">
    <form action="../includes/procesar/prestacion_guardar.php" method="POST" data-validar>
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <div class="form-row">
            <div class="form-grupo">
                <label for="codigo">Codigo *</label>
                <input type="text" name="codigo" id="codigo" required
                       value="<?php echo htmlspecialchars($prestacion['codigo'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Ej: PRE-VACACIONES">
            </div>
            <div class="form-grupo">
                <label for="nombre">Nombre *</label>
                <input type="text" name="nombre" id="nombre" required
                       value="<?php echo htmlspecialchars($prestacion['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Ej: Vacaciones">
            </div>
            <div class="form-grupo">
                <label for="tipo_calculo">Tipo de Calculo *</label>
                <select name="tipo_calculo" id="tipo_calculo" required onchange="mostrarCampoCalculo()">
                    <option value="dias_ano" <?php echo $prestacion['tipo_calculo'] === 'dias_ano' ? 'selected' : ''; ?>>Dias por Año</option>
                    <option value="porcentaje" <?php echo $prestacion['tipo_calculo'] === 'porcentaje' ? 'selected' : ''; ?>>Porcentaje</option>
                    <option value="fijo" <?php echo $prestacion['tipo_calculo'] === 'fijo' ? 'selected' : ''; ?>>Monto Fijo</option>
                </select>
            </div>
        </div>

        <div class="form-row" id="campo-calculo">
            <div class="form-grupo" id="grupo-dias">
                <label for="dias_ano">Dias por Año</label>
                <input type="number" name="dias_ano" id="dias_ano" min="0" max="365"
                       value="<?php echo htmlspecialchars($prestacion['dias_ano'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="15">
            </div>
            <div class="form-grupo" id="grupo-porcentaje" style="display:none;">
                <label for="porcentaje">Porcentaje (%)</label>
                <input type="number" name="porcentaje" id="porcentaje" step="0.01" min="0" max="100"
                       value="<?php echo htmlspecialchars($prestacion['porcentaje'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="0.00">
            </div>
            <div class="form-grupo" id="grupo-fijo" style="display:none;">
                <div class="subgrupo">
                    <label for="monto_fijo">Monto Fijo (Bs.)</label>
                    <input type="number" name="monto_fijo" id="monto_fijo" step="0.01" min="0"
                           value="<?php echo htmlspecialchars($prestacion['monto_fijo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="0.00">
                </div>
                <div class="subgrupo">
                    <label for="monto_fijo_usd">Monto Fijo (USD)</label>
                    <input type="number" name="monto_fijo_usd" id="monto_fijo_usd" step="0.01" min="0"
                           value="<?php echo htmlspecialchars($prestacion['monto_fijo_usd'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="0.00 (1 USD = <?php echo number_format($tasa_dolar, 2, ',', '.'); ?> Bs.)">
                </div>
            </div>
            <div class="form-grupo">
                <label for="aplica_a">Aplica a *</label>
                <select name="aplica_a" id="aplica_a">
                    <option value="todos" <?php echo $prestacion['aplica_a'] === 'todos' ? 'selected' : ''; ?>>Todos los empleados</option>
                    <option value="cargo" <?php echo $prestacion['aplica_a'] === 'cargo' ? 'selected' : ''; ?>>Por cargo</option>
                    <option value="empleado" <?php echo $prestacion['aplica_a'] === 'empleado' ? 'selected' : ''; ?>>Por empleado</option>
                </select>
            </div>
        </div>

        <div class="form-grupo">
            <label for="descripcion">Descripcion</label>
            <textarea name="descripcion" id="descripcion" rows="2" placeholder="Descripcion..."><?php echo htmlspecialchars($prestacion['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-grupo">
                <label class="checkbox-label">
                    <input type="checkbox" name="es_legal" value="1" <?php echo $prestacion['es_legal'] ? 'checked' : ''; ?>>
                    Es prestacion legal (Ley)
                </label>
            </div>
            <?php if ($editando): ?>
            <div class="form-grupo">
                <label class="checkbox-label">
                    <input type="checkbox" name="activo" value="1" <?php echo $prestacion['activo'] ? 'checked' : ''; ?>>
                    Activo
                </label>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primario"><?php echo $editando ? 'Actualizar Prestacion' : 'Crear Prestacion'; ?></button>
            <a href="prestaciones.php" class="btn btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

<script>
var tasaDolar = <?php echo $tasa_dolar; ?>;

function mostrarCampoCalculo() {
    var tipo = document.getElementById('tipo_calculo').value;
    document.getElementById('grupo-dias').style.display = tipo === 'dias_ano' ? 'block' : 'none';
    document.getElementById('grupo-porcentaje').style.display = tipo === 'porcentaje' ? 'block' : 'none';
    document.getElementById('grupo-fijo').style.display = tipo === 'fijo' ? 'block' : 'none';
}

document.getElementById('monto_fijo').addEventListener('input', function() {
    var bs = parseFloat(this.value) || 0;
    document.getElementById('monto_fijo_usd').value = tasaDolar > 0 ? (bs / tasaDolar).toFixed(2) : '';
});
document.getElementById('monto_fijo_usd').addEventListener('input', function() {
    var usd = parseFloat(this.value) || 0;
    document.getElementById('monto_fijo').value = (usd * tasaDolar).toFixed(2);
});

mostrarCampoCalculo();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
