# Diccionario de Datos - NominaApp

Base de datos: `nomina_vanilla_db` (MySQL 8, InnoDB, utf8mb4_unicode_ci)

## Tablas

### cargos
Catalogo de cargos laborales con salario base.

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT UNSIGNED PK | ID unico |
| codigo | VARCHAR(20) UNIQUE | Codigo del cargo (ADM-001) |
| nombre | VARCHAR(100) | Nombre del cargo |
| descripcion | TEXT | Funciones y responsabilidades |
| salario_base | DECIMAL(12,2) | Salario base mensual |
| incluye_cestaticket | TINYINT(1) | Aplica cesta ticket |
| incluye_transporte | TINYINT(1) | Aplica bono transporte |
| activo | TINYINT(1) | Estado del registro |

### deducciones
Conceptos de deduccion configurables (SSO, LRPE, FAOV, ISLR, etc.).

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT UNSIGNED PK | ID unico |
| codigo | VARCHAR(20) UNIQUE | Codigo (DED-SSO-EMP) |
| nombre | VARCHAR(100) | Nombre del concepto |
| tipo | ENUM('fijo','porcentaje','legal') | Tipo de deduccion |
| monto_fijo | DECIMAL(12,2) | Monto fijo |
| porcentaje | DECIMAL(6,2) | Porcentaje |
| aplica_a | ENUM('todos','cargo','empleado') | Ambito de aplicacion |
| es_legal | TINYINT(1) | Es deduccion de ley |
| es_patronal | TINYINT(1) | Es contribucion patronal |
| activo | TINYINT(1) | Estado |

### asignaciones
Conceptos de asignacion configurables (salario, cesta ticket, bono transporte, etc.).

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT UNSIGNED PK | ID unico |
| codigo | VARCHAR(20) UNIQUE | Codigo (ASIG-SALARIO) |
| nombre | VARCHAR(100) | Nombre del concepto |
| tipo | ENUM('fijo','porcentaje','diario','legal') | Tipo de asignacion |
| monto_fijo | DECIMAL(12,2) | Monto fijo |
| porcentaje | DECIMAL(6,2) | Porcentaje |
| monto_diario | DECIMAL(12,2) | Monto por dia |
| aplica_a | ENUM('todos','cargo','empleado') | Ambito de aplicacion |
| es_legal | TINYINT(1) | Es asignacion de ley |
| activo | TINYINT(1) | Estado |

### prestaciones
Conceptos de prestaciones sociales (antiguedad, vacaciones, utilidades, etc.).

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT UNSIGNED PK | ID unico |
| codigo | VARCHAR(20) UNIQUE | Codigo (PRE-ANTIGUEDAD) |
| nombre | VARCHAR(100) | Nombre del concepto |
| tipo_calculo | ENUM('dias_ano','porcentaje','fijo') | Tipo de calculo |
| dias_ano | INT UNSIGNED | Dias por ano |
| porcentaje | DECIMAL(6,2) | Porcentaje |
| monto_fijo | DECIMAL(12,2) | Monto fijo |
| aplica_a | ENUM('todos','cargo','empleado') | Ambito de aplicacion |
| es_legal | TINYINT(1) | Es prestacion de ley |
| activo | TINYINT(1) | Estado |

### usuarios
Usuarios del sistema (admin y empleados).

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT UNSIGNED PK | ID unico |
| cedula | VARCHAR(12) UNIQUE | Cedula (V12345678) |
| nombres | VARCHAR(100) | Nombres |
| apellidos | VARCHAR(100) | Apellidos |
| email | VARCHAR(150) UNIQUE | Correo electronico |
| password_hash | VARCHAR(255) | Hash BCRYPT |
| telefono | VARCHAR(15) | Telefono |
| direccion | TEXT | Direccion |
| fecha_nacimiento | DATE | Fecha de nacimiento |
| fecha_ingreso | DATE | Fecha de ingreso |
| cargo_id | INT UNSIGNED FK | FK a cargos.id |
| rol | ENUM('admin','empleado') | Rol del usuario |
| salario_personalizado | DECIMAL(12,2) | Salario personalizado (opcional) |
| activo | TINYINT(1) | Estado del usuario |

### empleado_deducciones
Relacion muchos a muchos entre empleados y deducciones.

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| usuario_id | INT UNSIGNED FK | FK a usuarios.id |
| deduccion_id | INT UNSIGNED FK | FK a deducciones.id |

### empleado_asignaciones
Relacion muchos a muchos entre empleados y asignaciones.

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| usuario_id | INT UNSIGNED FK | FK a usuarios.id |
| asignacion_id | INT UNSIGNED FK | FK a asignaciones.id |

### empleado_prestaciones
Relacion muchos a muchos entre empleados y prestaciones.

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| usuario_id | INT UNSIGNED FK | FK a usuarios.id |
| prestacion_id | INT UNSIGNED FK | FK a prestaciones.id |

### configuracion_legal
Parametros legales venezolanos (UT, salario minimo, porcentajes SSO/LRPE/FAOV/ISLR).

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| parametro | VARCHAR(50) UNIQUE | Nombre del parametro |
| valor | DECIMAL(14,4) | Valor numerico |
| descripcion | VARCHAR(255) | Descripcion |

### periodos_nomina
Periodos de pago (semanal, quincenal, mensual).

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT UNSIGNED PK | ID unico |
| nombre | VARCHAR(100) | Nombre del periodo |
| tipo | ENUM('semanal','quincenal','mensual') | Tipo |
| fecha_inicio | DATE | Fecha inicio |
| fecha_fin | DATE | Fecha fin |
| dias_habiles | TINYINT UNSIGNED | Dias habiles |
| estado | ENUM('abierto','cerrado') | Estado |
| creado_por | INT UNSIGNED FK | FK a usuarios.id |

### nominas
Cabecera de cada bauche generado.

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT UNSIGNED PK | ID unico |
| usuario_id | INT UNSIGNED FK | FK a usuarios.id |
| periodo_id | INT UNSIGNED FK | FK a periodos_nomina.id |
| dias_trabajados | TINYINT UNSIGNED | Dias trabajados |
| dias_ausencia | TINYINT UNSIGNED | Dias de ausencia |
| salario_base_mensual | DECIMAL(12,2) | Salario base |
| total_asignaciones | DECIMAL(12,2) | Total asignaciones |
| total_deducciones | DECIMAL(12,2) | Total deducciones |
| salario_neto | DECIMAL(12,2) | Salario neto a cobrar |
| costo_patronal | DECIMAL(12,2) | Costo total empleador |
| generada_por | INT UNSIGNED FK | FK a usuarios.id |

### detalle_nomina
Desglose de conceptos del bauche.

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT UNSIGNED PK | ID unico |
| nomina_id | INT UNSIGNED FK | FK a nominas.id (CASCADE) |
| concepto | VARCHAR(100) | Nombre del concepto |
| tipo | ENUM('asignacion','deduccion') | Tipo |
| monto | DECIMAL(12,2) | Monto |

### login_attempts
Auditoria de intentos de login.

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT UNSIGNED PK | ID unico |
| email | VARCHAR(150) | Email intentado |
| ip_address | VARCHAR(45) | Direccion IP |
| exitoso | TINYINT(1) | Exito o fallo |
| intento_en | DATETIME | Fecha del intento |

## Relaciones (ERD)

```
cargos 1---* usuarios
usuarios *---* deducciones (via empleado_deducciones)
usuarios *---* asignaciones (via empleado_asignaciones)
usuarios *---* prestaciones (via empleado_prestaciones)
usuarios 1---* nominas
periodos_nomina 1---* nominas
nominas 1---* detalle_nomina
```
