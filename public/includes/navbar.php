<nav class="navbar">
    <div class="nav-contenedor">
        <a href="dashboard.php" class="nav-logo">Nomina Integral</a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Menu">&#9776;</button>
        <ul class="nav-menu" id="nav-menu">
            <li><a href="dashboard.php">Inicio</a></li>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
            <li><a href="empleados.php">Empleados</a></li>
            <li><a href="cargos.php">Cargos</a></li>
            <li><a href="deducciones.php">Deducciones</a></li>
            <li><a href="asignaciones.php">Asignaciones</a></li>
            <li><a href="prestaciones.php">Prestaciones</a></li>
            <li><a href="periodos.php">Periodos</a></li>
            <li><a href="configuracion.php">Configuración Legal</a></li>
            <li><a href="generar_nomina.php">Generar Nómina</a></li>
            <li><a href="ver_nominas.php">Ver Nóminas</a></li>
            <?php else: ?>
            <li><a href="mi_perfil.php">Mi Perfil</a></li>
            <li><a href="mis_recibos.php">Mis Recibos</a></li>
            <?php endif; ?>
            <li><a href="../logout.php" class="nav-cerrar-sesion">Cerrar Sesión</a></li>
        </ul>
    </div>
</nav>
