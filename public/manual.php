<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual de Usuario - NominaApp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11pt; color: #333; background: #fff; line-height: 1.6; padding: 40px; max-width: 900px; margin: 0 auto; }
        h1 { font-size: 22pt; color: #1a73e8; margin-bottom: 5px; }
        h2 { font-size: 16pt; color: #1a73e8; margin-top: 30px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        h3 { font-size: 13pt; color: #444; margin-top: 20px; margin-bottom: 8px; }
        p { margin-bottom: 10px; }
        ul, ol { margin-bottom: 10px; padding-left: 25px; }
        li { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10pt; }
        th { background: #f0f4ff; padding: 8px 10px; text-align: left; font-weight: 600; border: 1px solid #ddd; }
        td { padding: 6px 10px; border: 1px solid #ddd; }
        .subtitle { color: #666; font-size: 11pt; margin-bottom: 25px; }
        .toc { margin: 20px 0; padding: 15px 20px; background: #f8f9fa; border-radius: 4px; }
        .toc a { color: #1a73e8; text-decoration: none; }
        .toc a:hover { text-decoration: underline; }
        code { background: #f4f4f4; padding: 1px 5px; border-radius: 3px; font-size: 10pt; }
        strong { color: #222; }
        .btn-print { display: block; margin: 30px auto; padding: 10px 30px; background: #1a73e8; color: #fff; border: none; border-radius: 4px; font-size: 12pt; cursor: pointer; }
        .btn-print:hover { background: #1557b0; }
        @media print {
            body { padding: 20px; }
            .btn-print { display: none; }
            h2 { page-break-before: always; }
            h2:first-of-type { page-break-before: auto; }
        }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">Imprimir / Guardar PDF</button>

<h1>Manual de Usuario — NominaApp</h1>
<p class="subtitle">Sistema de Gestion de Nominas (PHP vanilla + MySQL)</p>

<div class="toc">
<strong>Contenido:</strong>
<ol>
<li><a href="#requisitos">Requisitos Tecnicos</a></li>
<li><a href="#instalacion">Instalacion</a></li>
<li><a href="#acceso">Acceso al Sistema</a></li>
<li><a href="#roles">Roles de Usuario</a></li>
<li><a href="#dashboard">Dashboard</a></li>
<li><a href="#cargos">Cargos</a></li>
<li><a href="#deducciones">Deducciones</a></li>
<li><a href="#asignaciones">Asignaciones</a></li>
<li><a href="#prestaciones">Prestaciones</a></li>
<li><a href="#empleados">Empleados</a></li>
<li><a href="#periodos">Periodos</a></li>
<li><a href="#configlegal">Configuracion Legal</a></li>
<li><a href="#generarnomina">Generar Nomina</a></li>
<li><a href="#vernominas">Ver Nominas</a></li>
<li><a href="#miperfil">Mi Perfil (Empleado)</a></li>
<li><a href="#misrecibos">Mis Recibos (Empleado)</a></li>
<li><a href="#bauche">Bauche de Pago</a></li>
<li><a href="#exportar">Exportacion de Datos</a></li>
</ol>
</div>

<h2 id="requisitos">1. Requisitos Tecnicos</h2>
<ul>
<li>Servidor web: Apache, Nginx o PHP built-in server</li>
<li>PHP version 8.2 o superior</li>
<li>Base de datos MySQL 8.0+</li>
<li>Navegador: Chrome, Firefox, Edge o Safari actualizado</li>
</ul>

<h2 id="instalacion">2. Instalacion</h2>
<ol>
<li>Colocar los archivos en la carpeta del servidor web</li>
<li>Importar <code>database/schema.sql</code> en MySQL para crear la base de datos y las tablas</li>
<li>Importar <code>database/seed_data.sql</code> para poblar con datos de prueba</li>
<li>Configurar credenciales de base de datos en <code>includes/db.php</code></li>
<li>Acceder via navegador a la URL del sistema</li>
</ol>

<h2 id="acceso">3. Acceso al Sistema</h2>
<p>Al abrir la URL del sistema se muestra la pantalla de inicio de sesion.</p>
<p><strong>Para iniciar sesion:</strong></p>
<ol>
<li>Ingresar el correo electronico</li>
<li>Ingresar la contrasena</li>
<li>Hacer click en <strong>Iniciar Sesion</strong></li>
</ol>

<p><strong>Credenciales por defecto (demo):</strong></p>
<table>
<tr><th>Rol</th><th>Email</th><th>Contrasena</th></tr>
<tr><td>Administrador</td><td>admin@nomina.com</td><td>password</td></tr>
<tr><td>Empleado</td><td>juan@email.com</td><td>password</td></tr>
<tr><td>Empleado</td><td>maria@email.com</td><td>password</td></tr>
<tr><td>Empleado</td><td>carlos@email.com</td><td>password</td></tr>
<tr><td>Empleado</td><td>pedro@email.com</td><td>password</td></tr>
</table>

<h2 id="roles">4. Roles de Usuario</h2>

<h3>Administrador</h3>
<p>Acceso completo a todos los modulos: CRUD de Cargos, Deducciones, Asignaciones, Prestaciones, Empleados; gestion de periodos; configuracion legal; generacion de nominas; exportacion CSV.</p>

<h3>Empleado</h3>
<p>Acceso limitado a su propia informacion: perfil personal, recibos de pago (bauches), descarga/impresion de PDF.</p>

<h2 id="dashboard">5. Dashboard</h2>
<p>Muestra un resumen con tarjetas de: empleados activos, periodos abiertos, ultima nomina generada, total empleados/activos, cargos activos. Incluye enlaces rapidos a los modulos principales.</p>

<h2 id="cargos">6. Cargos</h2>
<p>Gestion de cargos de la empresa. Cada cargo tiene: codigo, nombre, descripcion, salario base mensual, y flags de cesta ticket y transporte.</p>
<p><strong>Acciones:</strong> Nuevo, Editar, Eliminar.</p>

<h2 id="deducciones">7. Deducciones</h2>
<p>Conceptos que se descuentan del salario. Tipos: Porcentaje, Fijo o Legal (ISLR). Se pueden marcar como legales (de ley) o patronales (las paga el empleador).</p>

<h3>Deducciones pre-cargadas:</h3>
<ul>
<li>SSO Empleado (4%) — Legal</li>
<li>LRPE Empleado (0.5%) — Legal</li>
<li>FAOV Empleado (1%) — Legal</li>
<li>ISLR — Legal (tabla progresiva)</li>
<li>SSO Patronal (11%) — Legal, Patronal</li>
<li>LRPE Patronal (2%) — Legal, Patronal</li>
<li>FAOV Patronal (2%) — Legal, Patronal</li>
<li>Caja de Ahorro, Sindicato, Prestamo, Pension — Opcionales</li>
</ul>

<h2 id="asignaciones">8. Asignaciones</h2>
<p>Conceptos que se suman al salario. Tipos: Porcentaje, Fijo, Diario o Legal.</p>

<h3>Asignaciones pre-cargadas:</h3>
<ul>
<li>Salario Base — Legal</li>
<li>Cesta Ticket — Legal (UT × 0.25 por dia)</li>
<li>Bono de Transporte — Diario (8% salario diario)</li>
<li>Bono de Productividad — Opcional</li>
<li>Bonificacion Especial — Opcional</li>
<li>Horas Extraordinarias — Opcional</li>
</ul>

<h2 id="prestaciones">9. Prestaciones</h2>
<p>Prestaciones sociales: Antiguedad (15 dias/ano), Cesantia (15), Vacaciones (15 + 1 adicional), Utilidades (30), Bono Vacacional (7), Intereses sobre Prestaciones.</p>

<h2 id="empleados">10. Empleados</h2>
<p>Gestion de empleados y administradores. Datos personales (cedula, nombre, email, telefono, direccion, fecha nacimiento) y laborales (rol, fecha ingreso, cargo, salario personalizado). Se asignan deducciones, asignaciones y prestaciones especificas.</p>

<h2 id="periodos">11. Periodos</h2>
<p>Periodos de pago: semanal, quincenal o mensual. Estados: abierto (se puede generar nomina) o cerrado.</p>

<h2 id="configlegal">12. Configuracion Legal</h2>
<p>Parametros para calculos de nomina: UT, salario minimo, porcentajes SSO/LRPE/FAOV, topes, dias habiles, cesta ticket. Tabla ISLR de 8 tramos.</p>

<h2 id="generarnomina">13. Generar Nomina</h2>
<p>Seleccionar periodo abierto, elegir empleados activos, y generar. El sistema calcula automaticamente:</p>
<ul>
<li>Salario proporcional (mensual / 30 × dias trabajados)</li>
<li>Asignaciones: Salario Base, Cesta Ticket, Transporte, bonos</li>
<li>Deducciones: SSO (4% tope 5 SM), LRPE (0.5% tope 10 SM), FAOV (1%), ISLR (tabla progresiva)</li>
<li>Costo patronal: SSO (11%), LRPE (2%), FAOV (2%)</li>
</ul>

<h2 id="vernominas">14. Ver Nominas</h2>
<p>Listado de nominas generadas con totales. Acciones: Ver detalle, Imprimir/PDF, Exportar CSV.</p>

<h2 id="miperfil">15. Mi Perfil (Empleado)</h2>
<p>Muestra los datos del empleado: cedula, nombre, email, telefono, direccion, cargo, salario base, fecha de ingreso.</p>

<h2 id="misrecibos">16. Mis Recibos (Empleado)</h2>
<p>Listado de nominas generadas para el empleado actual. Acciones: Ver detalle, Imprimir/PDF.</p>

<h2 id="bauche">17. Bauche de Pago</h2>
<p>Documento detallado con: datos del empleado, asignaciones, deducciones, neto a pagar y costo patronal. Para descargar como PDF, hacer click en <strong>Imprimir / PDF</strong> y en el dialogo del navegador seleccionar <strong>Guardar como PDF</strong>.</p>

<h2 id="exportar">18. Exportacion de Datos</h2>
<p>Desde <strong>Ver Nominas</strong>, click en <strong>Exportar CSV</strong>. El archivo incluye: empleado, cedula, cargo, periodo, salario base, dias, asignaciones, deducciones, neto y costo patronal. Compatible con Excel y LibreOffice Calc.</p>

<button class="btn-print" onclick="window.print()" style="margin-top:40px;">Imprimir / Guardar PDF</button>

<script><?php if (isset($_GET['print'])): ?>window.onload=function(){setTimeout(function(){window.print()},500)};<?php endif; ?></script>

</body>
</html>
