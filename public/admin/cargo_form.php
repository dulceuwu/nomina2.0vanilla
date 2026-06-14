<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editando = $id > 0;
$cargo = ['codigo' => '', 'nombre' => '', 'descripcion' => '', 'salario_base' => '0.00', 'incluye_cestaticket' => 1, 'incluye_transporte' => 0, 'activo' => 1];

if ($editando) {
    $stmt = $mysqli->prepare("SELECT * FROM cargos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) { $cargo = $row; }
    else { redirigir('cargos.php'); }
    $stmt->close();
}

$token = generarTokenCSRF();
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<h1><?php echo $editando ? 'Editar Cargo' : 'Nuevo Cargo'; ?></h1>

<?php echo mostrarMensaje(); ?>

<div class="tarjeta">
    <form action="../includes/procesar/cargo_guardar.php" method="POST" data-validar>
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <div class="form-row">
            <div class="form-grupo">
                <label for="codigo">Codigo *</label>
                <input type="text" name="codigo" id="codigo" required
                       value="<?php echo htmlspecialchars($cargo['codigo'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Ej: ADM-001">
            </div>
            <div class="form-grupo">
                <label for="nombre">Nombre del Cargo *</label>
                <input type="text" name="nombre" id="nombre" required
                       value="<?php echo htmlspecialchars($cargo['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Ej: Administrador General">
            </div>
            <div class="form-grupo">
                <label for="salario_base">Salario Base (Bs.) *</label>
                <input type="number" name="salario_base" id="salario_base" required step="0.01" min="0"
                       value="<?php echo htmlspecialchars($cargo['salario_base'], ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="0.00">
            </div>
        </div>

        <div class="form-grupo">
            <label for="descripcion">Descripcion</label>
            <textarea name="descripcion" id="descripcion" rows="3" placeholder="Funciones y responsabilidades del cargo..."><?php echo htmlspecialchars($cargo['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-grupo">
                <label class="checkbox-label">
                    <input type="checkbox" name="incluye_cestaticket" value="1" <?php echo $cargo['incluye_cestaticket'] ? 'checked' : ''; ?>>
                    Incluye Cesta Ticket
                </label>
            </div>
            <div class="form-grupo">
                <label class="checkbox-label">
                    <input type="checkbox" name="incluye_transporte" value="1" <?php echo $cargo['incluye_transporte'] ? 'checked' : ''; ?>>
                    Incluye Bono Transporte
                </label>
            </div>
            <?php if ($editando): ?>
            <div class="form-grupo">
                <label class="checkbox-label">
                    <input type="checkbox" name="activo" value="1" <?php echo $cargo['activo'] ? 'checked' : ''; ?>>
                    Activo
                </label>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primario"><?php echo $editando ? 'Actualizar Cargo' : 'Crear Cargo'; ?></button>
            <a href="cargos.php" class="btn btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
