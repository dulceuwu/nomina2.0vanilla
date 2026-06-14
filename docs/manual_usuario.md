# Manual de Usuario — NominaApp

Sistema de Gestion de Nominas desarrollado en PHP vanilla.

---

## Indice

1. [Requisitos Tecnicos](#1-requisitos-tecnicos)
2. [Instalacion](#2-instalacion)
3. [Acceso al Sistema](#3-acceso-al-sistema)
4. [Roles de Usuario](#4-roles-de-usuario)
5. [Modulo: Dashboard](#5-modulo-dashboard)
6. [Modulo: Cargos](#6-modulo-cargos)
7. [Modulo: Deducciones](#7-modulo-deducciones)
8. [Modulo: Asignaciones](#8-modulo-asignaciones)
9. [Modulo: Prestaciones](#9-modulo-prestaciones)
10. [Modulo: Empleados](#10-modulo-empleados)
11. [Modulo: Periodos](#11-modulo-periodos)
12. [Modulo: Configuracion Legal](#12-modulo-configuracion-legal)
13. [Modulo: Generar Nomina](#13-modulo-generar-nomina)
14. [Modulo: Ver Nominas](#14-modulo-ver-nominas)
15. [Modulo Empleado: Mi Perfil](#15-modulo-empleado-mi-perfil)
16. [Modulo Empleado: Mis Recibos](#16-modulo-empleado-mis-recibos)
17. [Bauche de Pago (Recibo)](#17-bauche-de-pago-recibo)
18. [Exportacion de Datos](#18-exportacion-de-datos)

---

## 1. Requisitos Tecnicos

- **Servidor web**: Apache, Nginx o PHP built-in server
- **PHP**: version 8.2 o superior
- **Base de datos**: MySQL 8.0+
- **Navegador**: Chrome, Firefox, Edge o Safari actualizado

---

## 2. Instalacion

1. Colocar los archivos en la carpeta del servidor web (`htdocs`, `www`, o cualquier directorio publico)
2. Importar `database/schema.sql` en MySQL para crear la base de datos y las tablas
3. Importar `database/seed_data.sql` para poblar con datos de prueba
4. Configurar credenciales de base de datos en `includes/db.php`
5. Acceder via navegador a la URL del sistema

---

## 3. Acceso al Sistema

### Pagina de inicio

Al abrir la URL del sistema se muestra la pantalla de inicio de sesion.

**Para iniciar sesion:**
1. Ingresar el correo electronico
2. Ingresar la contrasena
3. Hacer click en **Iniciar Sesion**

### Credenciales por defecto (demo)

| Rol | Email | Contrasena |
|---|---|---|
| Administrador | admin@nomina.com | password |
| Empleado | juan@email.com | password |
| Empleado | maria@email.com | password |
| Empleado | carlos@email.com | password |
| Empleado | pedro@email.com | password |

---

## 4. Roles de Usuario

El sistema tiene dos roles con distintos niveles de acceso:

### Administrador
Acceso completo a todos los modulos:
- CRUD completo de Cargos, Deducciones, Asignaciones, Prestaciones, Empleados
- Gestion de periodos de nomina
- Configuracion de parametros legales (SSO, ISLR, etc.)
- Generacion de nominas
- Visualizacion de todas las nominas generadas
- Exportacion de datos a CSV

### Empleado
Acceso limitado a su propia informacion:
- Visualizar su perfil (datos personales y laborales)
- Visualizar sus recibos de pago (bauches)
- Descargar/Imprimir sus bauches en PDF

---

## 5. Modulo: Dashboard

### Administrador
Muestra un resumen con tarjetas de:
- Total de empleados activos
- Periodos abiertos
- Ultima nomina generada
- Total empleados / empleados activos
- Cargos activos

Enlaces rapidos a: Empleados, Cargos, Generar Nomina, Periodos, Config. Legal

### Empleado
Muestra sus datos basicos y enlaces a Mi Perfil y Mis Recibos.

---

## 6. Modulo: Cargos

**Ruta:** Menu → Cargos

Gestion de cargos de la empresa. Cada cargo tiene:
- **Codigo**: identificador unico (ej: ADM-001)
- **Nombre**: nombre del cargo
- **Descripcion**: descripcion opcional
- **Salario Base**: salario mensual en Bs.
- **Incluye Cesta Ticket**: si aplica beneficio de alimentacion
- **Incluye Transporte**: si aplica bono de transporte

### Acciones
- **Nuevo Cargo**: formulario para crear un cargo
- **Editar**: modificar datos del cargo
- **Eliminar**: eliminar cargo (no se puede eliminar si tiene empleados asociados)

---

## 7. Modulo: Deducciones

**Ruta:** Menu → Deducciones

Gestion de conceptos que se descuentan del salario del empleado.

### Campos
- **Codigo**: identificador unico
- **Nombre**: nombre del concepto
- **Tipo**: Porcentaje, Fijo o Legal (ISLR)
- **Valor**: porcentaje o monto fijo segun el tipo
- **Aplica a**: Todos los empleados, por cargo o por empleado
- **Es Legal**: si es una deduccion de ley (SSO, LRPE, FAOV, ISLR)
- **Es Patronal**: si es un aporte que paga el empleador (no descuenta al empleado)

### Deducciones pre-cargadas
- SSO Empleado (4%) — Legal
- LRPE Empleado (0.5%) — Legal
- FAOV Empleado (1%) — Legal
- ISLR — Legal (tabla progresiva)
- SSO Patronal (11%) — Legal, Patronal
- LRPE Patronal (2%) — Legal, Patronal
- FAOV Patronal (2%) — Legal, Patronal
- Caja de Ahorro — Opcional
- Aporte Sindical — Opcional
- Prestamo Personal — Opcional
- Pension Alimenticia — Opcional

### Acciones
- **Nuevo**: crear nueva deduccion
- **Editar**: modificar deduccion
- **Eliminar**: eliminar deduccion

> Las deducciones marcadas como "Patronal" se asignan automaticamente a todos los empleados y no pueden desmarcarse en el formulario de empleado.

---

## 8. Modulo: Asignaciones

**Ruta:** Menu → Asignaciones

Gestion de conceptos que se suman al salario del empleado.

### Campos
- **Codigo**: identificador unico
- **Nombre**: nombre del concepto
- **Tipo**: Porcentaje, Fijo, Diario o Legal
- **Valor**: segun el tipo (porcentaje, monto fijo, monto diario)
- **Aplica a**: Todos, por cargo o por empleado
- **Es Legal**: si es obligatorio por ley

### Asignaciones pre-cargadas
- Salario Base — Legal (se calcula automaticamente)
- Cesta Ticket — Legal (diario: UT * 0.25)
- Bono de Transporte — Diario (8% del salario diario)
- Bono de Productividad — Opcional (porcentaje)
- Bonificacion Especial — Opcional (fijo)
- Horas Extraordinarias — Opcional (fijo)

---

## 9. Modulo: Prestaciones

**Ruta:** Menu → Prestaciones

Gestion de prestaciones sociales y beneficios laborales.

### Campos
- **Codigo**: identificador unico
- **Nombre**: nombre de la prestacion
- **Tipo de Calculo**: Dias por ano, Porcentaje o Fijo
- **Valor**: segun el tipo de calculo
- **Aplica a**: Todos, por cargo o por empleado
- **Es Legal**: si es obligatorio por ley

### Prestaciones pre-cargadas
- Antiguedad (15 dias/ano) — Legal
- Cesantia (15 dias/ano) — Legal
- Vacaciones (15 dias/ano) — Legal
- Utilidades (30 dias/ano) — Legal
- Bono Vacacional (7 dias/ano) — Legal
- Intereses sobre Prestaciones — Legal

---

## 10. Modulo: Empleados

**Ruta:** Menu → Empleados

Gestion de empleados y administradores del sistema.

### Datos Personales
- Cedula, Nombres, Apellidos
- Email (usado para iniciar sesion)
- Telefono, Direccion
- Fecha de Nacimiento

### Datos Laborales
- **Rol**: Empleado o Administrador (solo al crear)
- **Fecha de Ingreso**
- **Cargo**: seleccion de la lista de cargos activos
- **Salario Personalizado**: opcional, si es diferente al salario base del cargo

### Conceptos Asignados
- **Deducciones**: seleccion multiple de deducciones que aplican al empleado
- **Asignaciones**: seleccion multiple de asignaciones que aplican al empleado
- **Prestaciones**: seleccion multiple de prestaciones que aplican al empleado

Las deducciones patronales aparecen marcadas y deshabilitadas (se asignan automaticamente).

### Acciones
- **Nuevo Empleado**: formulario completo con todos los campos
- **Editar**: modificar datos del empleado
- **Activar/Desactivar**: cambiar estado activo/inactivo
- **Eliminar**: eliminar empleado

> Los empleados inactivos no aparecen en la generacion de nominas.

---

## 11. Modulo: Periodos

**Ruta:** Menu → Periodos

Gestion de periodos de pago (semanales, quincenales o mensuales).

### Campos
- **Nombre**: nombre del periodo (ej: "Enero 2025 - Mensual")
- **Tipo**: Semanal, Quincenal o Mensual
- **Fecha Inicio**: inicio del periodo
- **Fecha Fin**: cierre del periodo
- **Dias Habiles**: cantidad de dias laborales en el periodo

### Estados
- **Abierto**: se puede generar nomina para este periodo
- **Cerrado**: no se puede generar nomina (periodo ya procesado)

### Acciones
- **Nuevo Periodo**: crear un nuevo periodo
- **Cerrar Periodo**: marcar como cerrado (no se pueden hacer cambios)
- **Eliminar**: eliminar periodo (solo si no tiene nominas asociadas)

---

## 12. Modulo: Configuracion Legal

**Ruta:** Menu → Config. Legal

Parametros legales utilizados en los calculos de nomina.

### Parametros Generales
- **UT (Unidad Tributaria)**: valor de la UT vigente en Bs.
- **Salario Minimo**: salario minimo mensual en Bs.
- **SSO Empleado**: porcentaje de SSO del empleado
- **SSO Patronal**: porcentaje de SSO del empleador
- **LRPE Empleado**: porcentaje de LRPE del empleado
- **LRPE Patronal**: porcentaje de LRPE del empleador
- **FAOV**: porcentaje de FAOV
- **Topes**: maximos en cantidad de salarios minimos para SSO, LRPE y FAOV
- **Dias Habiles**: promedio de dias habiles por mes
- **Cesta Ticket**: porcentaje de UT para calculo de cesta ticket

### ISLR (Impuesto Sobre la Renta)
Tabla progresiva de 8 tramos con:
- Limite superior en Unidades Tributarias
- Porcentaje de retencion
- Sustraendo (rebaja)

---

## 13. Modulo: Generar Nomina

**Ruta:** Menu → Generar Nomina

Proceso de calculo y generacion de la nomina para un periodo.

### Pasos
1. **Seleccionar periodo**: elegir un periodo abierto
2. **Seleccionar empleados**: se muestran los empleados activos con sus cargos y salarios
3. **Hacer click en "Generar Nomina"**: el sistema calcula automaticamente:
   - Salario proporcional segun dias trabajados
   - Asignaciones (Salario Base, Cesta Ticket, Transporte, bonos, etc.)
   - Deducciones (SSO, LRPE, FAOV, ISLR, Caja de Ahorro, etc.)
   - Costo patronal (SSO patronal, LRPE patronal, FAOV patronal)

### Reglas de Calculo
- **Salario diario**: salario mensual / 30
- **Salario del periodo**: salario diario * dias trabajados
- **SSO**: 4% del empleado, tope 5 salarios minimos
- **LRPE**: 0.5% del empleado, tope 10 salarios minimos
- **FAOV**: 1% empleado + 2% patronal
- **ISLR**: tabla progresiva de 8 tramos en U.T.
- **Cesta Ticket**: UT * 0.25 por dia habil
- **Bono Transporte**: 8% del salario diario por dia trabajado

---

## 14. Modulo: Ver Nominas

**Ruta:** Menu → Ver Nominas

Listado de todas las nominas generadas, ordenadas por fecha.

### Columnas
- Empleado, Cedula, Periodo, Salario Base, Total Asignaciones, Total Deducciones, Neto a Pagar, Fecha, Acciones

### Acciones por nomina
- **Ver**: muestra el bauche de pago detallado
- **Imprimir**: abre el bauche con dialogo de impresion/PDF
- **Exportar CSV**: descarga todas las nominas visibles en formato CSV

---

## 15. Modulo Empleado: Mi Perfil

**Ruta:** Menu → Mi Perfil (empleado)

Muestra los datos del empleado que ha iniciado sesion:
- Datos personales (cedula, nombre, email, telefono, direccion)
- Datos laborales (cargo, salario base, fecha de ingreso)

---

## 16. Modulo Empleado: Mis Recibos

**Ruta:** Menu → Mis Recibos (empleado)

Listado de las nominas generadas para el empleado actual.

Cada fila muestra: Periodo, Salario Base, Asignaciones, Deducciones, Neto, Fecha.

**Acciones:**
- **Ver**: detalle completo del bauche
- **Imprimir**: descarga/imprime el bauche como PDF

---

## 17. Bauche de Pago (Recibo)

Documento detallado de pago que incluye:

### Encabezado
- Nombre de la empresa (NominaApp)
- Periodo de pago

### Datos del Empleado
- Nombre completo, cedula, email
- Fecha de generacion, periodo, dias trabajados

### Asignaciones
Lista de ingresos con montos individuales y total.

### Deducciones
Lista de descuentos con montos individuales y total.

### Resumen
- **Neto a Pagar**: total a recibir (Asignaciones - Deducciones)
- **Costo Patronal**: costo total para el empleador

### Acciones
- **Imprimir / PDF**: abre el dialogo de impresion del navegador. Seleccionar "Guardar como PDF" para obtener el archivo digital.

---

## 18. Exportacion de Datos

### Exportar Nominas a CSV
Desde el modulo **Ver Nominas**, hacer click en **Exportar CSV**.
El archivo incluye:
- Empleado, Cedula, Cargo, Periodo
- Salario Base, Dias Trabajados, Ausencias
- Asignaciones, Deducciones, Neto, Costo Patronal

El archivo se descarga con formato compatible con Excel y LibreOffice Calc.
