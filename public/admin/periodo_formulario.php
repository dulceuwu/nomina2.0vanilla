<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editando = $id > 0;
$periodo = ['nombre' => '', 'tipo' => 'mensual', 'fecha_inicio' => '', 'fecha_fin' => '', 'dias_habiles' => 0];

if ($editando) {
    $stmt = $mysqli->prepare("SELECT * FROM periodos_nomina WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) { $periodo = $row; }
    else { redirigir('periodos.php'); }
    $stmt->close();
}

$token = generarTokenCSRF();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<h1><?php echo $editando ? 'Editar Periodo' : 'Nuevo Periodo'; ?></h1>

<?php echo mostrarMensaje(); ?>

<div class="tarjeta">
    <form action="../includes/procesar/periodo_guardar.php" method="POST" data-validar>
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <div class="form-row">
            <div class="form-grupo">
                <label for="nombre">Nombre del Periodo *</label>
                <input type="text" name="nombre" id="nombre" required
                       value="<?php echo htmlspecialchars($periodo['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Ej: 1ra Quincena Julio 2025">
            </div>
            <div class="form-grupo">
                <label for="tipo">Tipo *</label>
                <select name="tipo" id="tipo" required>
                    <option value="semanal" <?php echo $periodo['tipo'] === 'semanal' ? 'selected' : ''; ?>>Semanal</option>
                    <option value="quincenal" <?php echo $periodo['tipo'] === 'quincenal' ? 'selected' : ''; ?>>Quincenal</option>
                    <option value="mensual" <?php echo $periodo['tipo'] === 'mensual' ? 'selected' : ''; ?>>Mensual</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-grupo">
                <label for="fecha_inicio">Fecha Inicio *</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" required
                       value="<?php echo htmlspecialchars($periodo['fecha_inicio'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-grupo">
                <label for="fecha_fin">Fecha Fin *</label>
                <input type="date" name="fecha_fin" id="fecha_fin" required
                       value="<?php echo htmlspecialchars($periodo['fecha_fin'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-grupo">
                <label for="dias_habiles">Dias Habiles</label>
                <input type="number" name="dias_habiles" id="dias_habiles" min="0" max="31"
                       value="<?php echo (int)$periodo['dias_habiles']; ?>">
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primario"><?php echo $editando ? 'Actualizar Periodo' : 'Crear Periodo'; ?></button>
            <a href="periodos.php" class="btn btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
