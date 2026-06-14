USE nomina_vanilla_db;

INSERT INTO cargos (codigo, nombre, descripcion, salario_base, incluye_cestaticket, incluye_transporte, activo) VALUES
('ADM-001', 'Administrador General', 'Gestion administrativa general de la empresa', 500.00, 1, 1, 1),
('CON-001', 'Contador Senior', 'Contabilidad y finanzas de la empresa', 400.00, 1, 1, 1),
('CON-002', 'Contador Junior', 'Apoyo en contabilidad', 250.00, 1, 0, 1),
('VEN-001', 'Vendedor Senior', 'Ventas y atencion al cliente', 300.00, 1, 1, 1),
('VEN-002', 'Vendedor Junior', 'Apoyo en ventas', 180.00, 1, 0, 1),
('OPE-001', 'Operador de Produccion', 'Operacion de maquinaria y equipos', 200.00, 1, 1, 1),
('OPE-002', 'Asistente de Produccion', 'Apoyo en linea de produccion', 130.00, 1, 0, 1);

INSERT INTO deducciones (codigo, nombre, tipo, porcentaje, aplica_a, es_legal, es_patronal, descripcion) VALUES
('DED-SSO-EMP', 'Seguro Social Obligatorio (Empleado)', 'porcentaje', 4.00, 'todos', 1, 0, '4% sobre salario base con tope 5 S.M.'),
('DED-LRPE-EMP', 'Regimen Prestacional de Empleo (Empleado)', 'porcentaje', 0.50, 'todos', 1, 0, '0.5% sobre salario base con tope 10 S.M.'),
('DED-FAOV-EMP', 'FAOV - Vivienda (Empleado)', 'porcentaje', 1.00, 'todos', 1, 0, '1% sobre salario normal sin tope'),
('DED-ISLR', 'Impuesto Sobre la Renta (ISLR)', 'legal', NULL, 'todos', 1, 0, 'Retencion segun tabla SENIAT en U.T.'),
('DED-SSO-PAT', 'Seguro Social Obligatorio (Patronal)', 'porcentaje', 11.00, 'todos', 1, 1, '11% patronal con tope 5 S.M.'),
('DED-LRPE-PAT', 'Regimen Prestacional de Empleo (Patronal)', 'porcentaje', 2.00, 'todos', 1, 1, '2% patronal con tope 10 S.M.'),
('DED-FAOV-PAT', 'FAOV - Vivienda (Patronal)', 'porcentaje', 2.00, 'todos', 1, 1, '2% patronal con tope 10 S.M.'),
('DED-CAJA-AHORRO', 'Caja de Ahorro', 'porcentaje', 5.00, 'empleado', 0, 0, 'Aporte voluntario a caja de ahorro'),
('DED-SINDICATO', 'Aporte Sindical', 'fijo', NULL, 'empleado', 0, 0, 'Aporte sindical mensual'),
('DED-PRESTAMO', 'Prestamo Personal', 'fijo', NULL, 'empleado', 0, 0, 'Descuento por prestamo personal'),
('DED-PENSION', 'Pension Alimenticia', 'porcentaje', 30.00, 'empleado', 0, 0, 'Pension alimenticia ordenada por tribunal');

INSERT INTO asignaciones (codigo, nombre, tipo, monto_diario, porcentaje, aplica_a, es_legal, descripcion) VALUES
('ASIG-SALARIO', 'Salario Base', 'legal', NULL, NULL, 'todos', 1, 'Salario base mensual segun cargo'),
('ASIG-CESTA-TICKET', 'Bono de Alimentacion (Cesta Ticket)', 'diario', NULL, NULL, 'todos', 1, 'Cesta ticket: UT * 0.25 por dia habil'),
('ASIG-TRANSPORTE', 'Bono de Transporte', 'diario', NULL, NULL, 'todos', 0, '8% del salario diario por dia trabajado'),
('ASIG-BONO-PRODUCTIVIDAD', 'Bono de Productividad', 'porcentaje', NULL, 10.00, 'empleado', 0, '10% sobre salario base por productividad'),
('ASIG-BONO-ESPECIAL', 'Bonificacion Especial', 'fijo', NULL, NULL, 'empleado', 0, 'Bonificacion especial asignada al empleado'),
('ASIG-HORAS-EXTRA', 'Horas Extraordinarias', 'fijo', NULL, NULL, 'empleado', 0, 'Pago de horas extras laboradas');

INSERT INTO prestaciones (codigo, nombre, descripcion, tipo_calculo, dias_ano, es_legal, aplica_a) VALUES
('PRE-ANTIGUEDAD', 'Antiguedad', 'Prestacion de antiguedad: 15 dias por ano', 'dias_ano', 15, 1, 'todos'),
('PRE-CESANTIA', 'Cesantia', 'Indemnizacion por despido', 'dias_ano', 15, 1, 'todos'),
('PRE-VACACIONES', 'Vacaciones', 'Dias de vacaciones: 15 dias + 1 adicional por ano', 'dias_ano', 15, 1, 'todos'),
('PRE-UTILIDADES', 'Utilidades', 'Utilidades legales: 30 dias por ano', 'dias_ano', 30, 1, 'todos'),
('PRE-BONO-VACACIONAL', 'Bono Vacacional', 'Bono vacacional segun LOTTT', 'dias_ano', 7, 1, 'todos'),
('PRE-INTERESES-PREST', 'Intereses sobre Prestaciones', 'Intereses generados por las prestaciones sociales', 'porcentaje', NULL, 1, 'todos');

INSERT INTO usuarios (cedula, nombres, apellidos, email, password_hash, telefono, rol, activo, cargo_id, fecha_ingreso)
VALUES ('V12345678', 'Admin', 'Principal', 'admin@nomina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04121234567', 'admin', 1, NULL, '2024-01-01');

INSERT INTO usuarios (cedula, nombres, apellidos, email, password_hash, telefono, rol, activo, cargo_id, salario_personalizado, fecha_ingreso)
VALUES
('V20000001', 'Juan Carlos', 'Martinez Lopez', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04141234501', 'empleado', 1, 1, NULL, '2024-03-01'),
('V20000002', 'Maria Elena', 'Rodriguez Perez', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04141234502', 'empleado', 1, 3, NULL, '2024-06-15'),
('V20000003', 'Carlos Jose', 'Gonzalez Hernandez', 'carlos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04141234503', 'empleado', 1, 1, 500.00, '2023-01-10'),
('E20000004', 'Ana Sofia', 'Diaz Castillo', 'ana@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04141234504', 'empleado', 0, 7, NULL, '2025-01-01'),
('V20000005', 'Pedro Luis', 'Ramirez Contreras', 'pedro@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04141234505', 'empleado', 1, 5, NULL, '2024-11-01');

INSERT INTO empleado_deducciones (usuario_id, deduccion_id)
SELECT u.id, d.id FROM usuarios u, deducciones d
WHERE u.rol = 'empleado' AND u.activo = 1 AND d.codigo IN ('DED-SSO-EMP', 'DED-LRPE-EMP', 'DED-FAOV-EMP', 'DED-ISLR');

INSERT INTO empleado_asignaciones (usuario_id, asignacion_id)
SELECT u.id, a.id FROM usuarios u, asignaciones a
WHERE u.rol = 'empleado' AND u.activo = 1 AND a.codigo IN ('ASIG-SALARIO', 'ASIG-CESTA-TICKET');

INSERT INTO empleado_asignaciones (usuario_id, asignacion_id)
SELECT u.id, a.id FROM usuarios u, asignaciones a
WHERE u.rol = 'empleado' AND u.activo = 1 AND u.id IN (1,3,5) AND a.codigo = 'ASIG-TRANSPORTE';

INSERT INTO empleado_prestaciones (usuario_id, prestacion_id)
SELECT u.id, p.id FROM usuarios u, prestaciones p
WHERE u.rol = 'empleado' AND u.activo = 1;

INSERT INTO periodos_nomina (nombre, tipo, fecha_inicio, fecha_fin, dias_habiles, estado, creado_por)
VALUES
('Enero 2025 - Mensual', 'mensual', '2025-01-01', '2025-01-31', 22, 'cerrado', 1),
('Febrero 2025 - Mensual', 'mensual', '2025-02-01', '2025-02-28', 20, 'cerrado', 1),
('Marzo 2025 - Mensual', 'mensual', '2025-03-01', '2025-03-31', 21, 'cerrado', 1),
('1ra Quincena Junio 2025', 'quincenal', '2025-06-01', '2025-06-15', 10, 'abierto', 1),
('Junio 2025 - Mensual', 'mensual', '2025-06-01', '2025-06-30', 22, 'abierto', 1);
