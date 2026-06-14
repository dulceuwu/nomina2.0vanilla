# NominaApp - Sistema de Nomina Basica Venezuela

Aplicacion web vanilla PHP para gestion de nominas con calculos legales venezolanos.

## Requisitos

- PHP 8.2+
- MySQL 8+
- Apache 2.4 / Nginx

## Instalacion

1. Clonar o copiar los archivos al servidor web
2. Configurar `public/` como DocumentRoot
3. Ejecutar `database/schema.sql` en MySQL
4. Ejecutar `database/seed_data.sql` para datos de prueba
5. Configurar credenciales en `public/includes/db.php`
6. Iniciar sesion con admin@nomina.com / password

## Estructura

| Ruta | Descripcion |
|------|-------------|
| `public/` | DocumentRoot del servidor |
| `public/admin/` | Panel de administrador |
| `public/app/` | Panel de empleado |
| `public/includes/` | Logica compartida (DB, auth, calculos, PDF) |
| `database/` | Esquema SQL y datos iniciales |
| `docs/` | Documentacion tecnica |

## Funcionalidades

- CRUD: Empleados, Cargos, Deducciones, Asignaciones, Prestaciones
- Calculo de nomina con SSO (4%), LRPE (0.5%), FAOV (1%), ISLR
- Generacion de bauche de pago en pantalla y PDF
- Gestion de periodos de nomina
- Configuracion legal parametrizable
