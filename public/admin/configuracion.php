<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$configs = obtenerTodasConfig($mysqli);
$token = generarTokenCSRF();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<h1>Configuracion Legal</h1>
<p class="mb-4">Parametros legales venezolanos para el calculo de nominas.</p>

<?php echo mostrarMensaje(); ?>

<div class="tarjeta">
    <form action="../includes/procesar/configuracion_guardar.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Parametro</th>
                        <th>Valor Actual</th>
                        <th>Descripcion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($configs as $cfg): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($cfg['parametro'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td>
                            <input type="hidden" name="nombre_config[]" value="<?php echo htmlspecialchars($cfg['parametro'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="number" name="valor_config[]" step="0.0001" min="0"
                                   value="<?php echo htmlspecialchars($cfg['valor'], ENT_QUOTES, 'UTF-8'); ?>"
                                   style="width:150px;" required>
                        </td>
                        <td><?php echo htmlspecialchars($cfg['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primario">Guardar Cambios</button>
            <button type="button" class="btn btn-advertencia" onclick="restaurarValores()">Restaurar Valores por Defecto</button>
        </div>
    </form>
</div>

<script>
function restaurarValores() {
    confirmarModal('¿Restaurar valores legales por defecto?', function(ok) {
        if (!ok) return;
        mostrarSpinner();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../includes/procesar/configuracion_guardar.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            ocultarSpinner();
            if (xhr.status === 200) { location.reload(); }
            else { mostrarToast('Error al restaurar', 'error'); }
        };
        xhr.send('restaurar=1&csrf_token=<?php echo $token; ?>');
    });
}
</script>

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

<?php include __DIR__ . '/../includes/footer.php'; ?>
