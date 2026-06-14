<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('empleado');

$user_id = (int)$_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT u.cedula, u.nombres, u.apellidos, u.email, u.telefono, u.direccion, u.fecha_nacimiento, u.fecha_ingreso, COALESCE(u.salario_personalizado, c.salario_base, 0) as salario_base, c.nombre as cargo_nombre, u.creado_en FROM usuarios u LEFT JOIN cargos c ON u.cargo_id = c.id WHERE u.id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<h1>Mi Perfil</h1>

<?php echo mostrarMensaje(); ?>

<div class="tarjeta">
    <div class="form-row">
        <div class="form-grupo"><strong>Cedula:</strong> <?php echo htmlspecialchars($emp['cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="form-grupo"><strong>Nombres:</strong> <?php echo htmlspecialchars($emp['nombres'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="form-grupo"><strong>Apellidos:</strong> <?php echo htmlspecialchars($emp['apellidos'], ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <div class="form-row">
        <div class="form-grupo"><strong>Email:</strong> <?php echo htmlspecialchars($emp['email'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="form-grupo"><strong>Telefono:</strong> <?php echo htmlspecialchars($emp['telefono'] ?? 'No registrado', ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="form-grupo"><strong>Cargo:</strong> <?php echo htmlspecialchars($emp['cargo_nombre'] ?? 'No asignado', ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <div class="form-row">
        <div class="form-grupo"><strong>Salario Base:</strong> <?php echo formatoDual($emp['salario_base'], $mysqli); ?></div>
        <div class="form-grupo"><strong>Fecha Ingreso:</strong> <?php echo $emp['fecha_ingreso'] ? date('d/m/Y', strtotime($emp['fecha_ingreso'])) : 'N/A'; ?></div>
        <div class="form-grupo"><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($emp['creado_en'])); ?></div>
    </div>
</div>

<div class="tarjeta">
    <h3>Cambiar Contrasena</h3>
    <form action="cambiar_password.php" method="POST" data-validar>
        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
        <div class="form-row">
            <div class="form-grupo">
                <label for="password_actual">Contrasena Actual *</label>
                <input type="password" name="password_actual" id="password_actual" required>
            </div>
            <div class="form-grupo">
                <label for="password_nueva">Nueva Contrasena *</label>
                <input type="password" name="password_nueva" id="password_nueva" required minlength="6">
            </div>
            <div class="form-grupo">
                <label for="password_confirmar">Confirmar Contrasena *</label>
                <input type="password" name="password_confirmar" id="password_confirmar" required minlength="6">
            </div>
        </div>
        <button type="submit" class="btn btn-primario">Cambiar Contrasena</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
