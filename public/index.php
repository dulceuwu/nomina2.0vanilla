<?php
session_start();
require_once __DIR__ . '/includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['rol'] === 'admin') {
        redirigir('admin/dashboard.php');
    } else {
        redirigir('app/dashboard.php');
    }
}

$token = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NominaApp - Iniciar Sesion</title>
    <link rel="stylesheet" href="assets/css/estilos.css?v=<?php echo filemtime(__DIR__ . '/assets/css/estilos.css'); ?>">
</head>
<body>
    <div class="login-contenedor">
        <h1>NominaApp</h1>
        <p class="subtitulo">Sistema de Gestion de Nominas Integral</p>
        <?php if (isset($_SESSION['mensaje_flash'])): ?>
            <div class="mensaje mensaje-<?php echo $_SESSION['mensaje_tipo'] ?? 'info'; ?>">
                <?php echo htmlspecialchars($_SESSION['mensaje_flash'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php unset($_SESSION['mensaje_flash'], $_SESSION['mensaje_tipo']); ?>
        <?php endif; ?>
        <form action="includes/procesar/login_procesar.php" method="POST" data-validar>
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
            <div class="form-grupo">
                <label for="email">Correo Electronico</label>
                <input type="email" name="email" id="email" required autofocus placeholder="correo@ejemplo.com">
            </div>
            <div class="form-grupo">
                <label for="password">Contrasena</label>
                <input type="password" name="password" id="password" required placeholder="Ingrese su contraseña">
            </div>
            <button type="submit" class="btn btn-primario" style="width:100%">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>
