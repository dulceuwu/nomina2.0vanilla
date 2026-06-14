<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';
requiereRol('admin');

$total_empleados = 0;
$nomina_pendiente = 0;
$ultima_nomina = 'Ninguna';

$result = $mysqli->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='empleado' AND activo=1");
if ($result) { $total_empleados = $result->fetch_assoc()['total']; }

$result = $mysqli->query("SELECT COUNT(*) as total FROM periodos_nomina WHERE estado='abierto'");
if ($result) { $nomina_pendiente = $result->fetch_assoc()['total']; }

$result = $mysqli->query("SELECT CONCAT(p.nombre, ' - ', DATE_FORMAT(n.generada_en, '%d/%m/%Y')) as info
    FROM nominas n JOIN periodos_nomina p ON n.periodo_id = p.id
    ORDER BY n.generada_en DESC LIMIT 1");
if ($result && $row = $result->fetch_assoc()) { $ultima_nomina = $row['info']; }

$r1 = $mysqli->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='empleado'"); $total_admin = $r1 ? $r1->fetch_assoc()['total'] : 0;
$r2 = $mysqli->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='empleado' AND activo=1"); $total_activos = $r2 ? $r2->fetch_assoc()['total'] : 0;
$r3 = $mysqli->query("SELECT COUNT(*) as total FROM cargos WHERE activo=1"); $total_cargos = $r3 ? $r3->fetch_assoc()['total'] : 0;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="tarjetas-resumen">
    <div class="tarjeta-resumen">
        <div class="numero"><?php echo $total_empleados; ?></div>
        <div class="etiqueta">Empleados Activos</div>
    </div>
    <div class="tarjeta-resumen" style="border-left-color:var(--color-advertencia);">
        <div class="numero"><?php echo $nomina_pendiente; ?></div>
        <div class="etiqueta">Periodos Abiertos</div>
    </div>
    <div class="tarjeta-resumen" style="border-left-color:var(--color-exito);">
        <div class="numero" style="font-size:1.1rem;"><?php echo htmlspecialchars($ultima_nomina, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="etiqueta">Ultima Nomina Generada</div>
    </div>
    <div class="tarjeta-resumen" style="border-left-color:var(--color-info);">
        <div class="numero"><?php echo $total_admin; ?> / <?php echo $total_activos; ?></div>
        <div class="etiqueta">Total Empleados / Activos</div>
    </div>
    <div class="tarjeta-resumen" style="border-left-color:var(--color-secundario);">
        <div class="numero"><?php echo $total_cargos; ?></div>
        <div class="etiqueta">Cargos Activos</div>
    </div>
</div>

<div class="tarjeta">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8'); ?></h2>
    <p>Seleccione una opcion del menu para gestionar el sistema de nominas.</p>
    <div class="flex gap-2 mt-3 flex-wrap">
        <a href="empleados.php" class="btn btn-primario">Empleados</a>
        <a href="cargos.php" class="btn btn-primario">Cargos</a>
        <a href="generar_nomina.php" class="btn btn-exito">Generar Nomina</a>
        <a href="periodos.php" class="btn btn-secundario">Periodos</a>
        <a href="configuracion.php" class="btn btn-advertencia">Config. Legal</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
