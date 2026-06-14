CREATE DATABASE IF NOT EXISTS nomina_vanilla_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE nomina_vanilla_db;

CREATE TABLE cargos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT DEFAULT NULL,
  salario_base DECIMAL(12,2) NOT NULL,
  incluye_cestaticket TINYINT(1) DEFAULT 1,
  incluye_transporte TINYINT(1) DEFAULT 0,
  activo TINYINT(1) DEFAULT 1,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE deducciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  tipo ENUM('fijo','porcentaje','legal') NOT NULL,
  monto_fijo DECIMAL(12,2) DEFAULT NULL,
  monto_fijo_usd DECIMAL(12,2) DEFAULT NULL,
  porcentaje DECIMAL(6,2) DEFAULT NULL,
  aplica_a ENUM('todos','cargo','empleado') DEFAULT 'todos',
  descripcion TEXT DEFAULT NULL,
  es_legal TINYINT(1) DEFAULT 0,
  es_patronal TINYINT(1) DEFAULT 0,
  activo TINYINT(1) DEFAULT 1,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE asignaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  tipo ENUM('fijo','porcentaje','diario','legal') NOT NULL,
  monto_fijo DECIMAL(12,2) DEFAULT NULL,
  monto_fijo_usd DECIMAL(12,2) DEFAULT NULL,
  porcentaje DECIMAL(6,2) DEFAULT NULL,
  monto_diario DECIMAL(12,2) DEFAULT NULL,
  monto_diario_usd DECIMAL(12,2) DEFAULT NULL,
  aplica_a ENUM('todos','cargo','empleado') DEFAULT 'todos',
  descripcion TEXT DEFAULT NULL,
  es_legal TINYINT(1) DEFAULT 0,
  activo TINYINT(1) DEFAULT 1,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE prestaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT DEFAULT NULL,
  tipo_calculo ENUM('dias_ano','porcentaje','fijo') NOT NULL,
  dias_ano INT UNSIGNED DEFAULT NULL,
  porcentaje DECIMAL(6,2) DEFAULT NULL,
  monto_fijo DECIMAL(12,2) DEFAULT NULL,
  monto_fijo_usd DECIMAL(12,2) DEFAULT NULL,
  aplica_a ENUM('todos','cargo','empleado') DEFAULT 'todos',
  es_legal TINYINT(1) DEFAULT 0,
  activo TINYINT(1) DEFAULT 1,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE configuracion_legal (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parametro VARCHAR(50) NOT NULL UNIQUE,
  valor DECIMAL(14,4) NOT NULL,
  descripcion VARCHAR(255) DEFAULT NULL,
  actualizado_en DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO configuracion_legal (parametro, valor, descripcion) VALUES
('UT', 9.0000, 'Unidad Tributaria vigente en Bs.'),
('SALARIO_MINIMO', 130.0000, 'Salario minimo mensual en Bs.'),
('SSO_EMPLEADO_PORC', 4.0000, 'Porcentaje SSO empleado (4%)'),
('SSO_PATRONAL_PORC', 11.0000, 'Porcentaje SSO patronal (11%)'),
('LRPE_EMPLEADO_PORC', 0.5000, 'Porcentaje LRPE empleado (0.5%)'),
('LRPE_PATRONAL_PORC', 2.0000, 'Porcentaje LRPE patronal (2%)'),
('FAOV_PORC', 2.0000, 'Porcentaje FAOV patronal (2%)'),
('TOPE_SSO_SALARIOS', 5, 'Tope SSO en cantidad de salarios minimos'),
('TOPE_LRPE_SALARIOS', 10, 'Tope LRPE en cantidad de salarios minimos'),
('TOPE_FAOV_SALARIOS', 10, 'Tope FAOV patronal en cantidad de salarios minimos'),
('DIAS_HABILES_MES', 22, 'Dias habiles promedio por mes'),
('CESTA_TICKET_PORC_UT', 0.2500, 'Porcentaje de UT para cesta ticket diario'),
('TASA_DOLAR', 60.0000, 'Tasa de cambio Bs./USD vigente');

INSERT INTO configuracion_legal (parametro, valor, descripcion) VALUES
('UT_ISLR_TRAMO1', 1000, 'ISLR Tramo 1: hasta U.T.'),
('UT_ISLR_PORC1', 0, 'ISLR Tramo 1: porcentaje'),
('UT_ISLR_SUST1', 0, 'ISLR Tramo 1: sustraendo'),
('UT_ISLR_TRAMO2', 1500, 'ISLR Tramo 2: hasta U.T.'),
('UT_ISLR_PORC2', 6, 'ISLR Tramo 2: porcentaje'),
('UT_ISLR_SUST2', 60, 'ISLR Tramo 2: sustraendo'),
('UT_ISLR_TRAMO3', 2000, 'ISLR Tramo 3: hasta U.T.'),
('UT_ISLR_PORC3', 9, 'ISLR Tramo 3: porcentaje'),
('UT_ISLR_SUST3', 105, 'ISLR Tramo 3: sustraendo'),
('UT_ISLR_TRAMO4', 2500, 'ISLR Tramo 4: hasta U.T.'),
('UT_ISLR_PORC4', 12, 'ISLR Tramo 4: porcentaje'),
('UT_ISLR_SUST4', 165, 'ISLR Tramo 4: sustraendo'),
('UT_ISLR_TRAMO5', 3000, 'ISLR Tramo 5: hasta U.T.'),
('UT_ISLR_PORC5', 16, 'ISLR Tramo 5: porcentaje'),
('UT_ISLR_SUST5', 265, 'ISLR Tramo 5: sustraendo'),
('UT_ISLR_TRAMO6', 4000, 'ISLR Tramo 6: hasta U.T.'),
('UT_ISLR_PORC6', 20, 'ISLR Tramo 6: porcentaje'),
('UT_ISLR_SUST6', 385, 'ISLR Tramo 6: sustraendo'),
('UT_ISLR_TRAMO7', 6000, 'ISLR Tramo 7: hasta U.T.'),
('UT_ISLR_PORC7', 24, 'ISLR Tramo 7: porcentaje'),
('UT_ISLR_SUST7', 545, 'ISLR Tramo 7: sustraendo'),
('UT_ISLR_PORC8', 34, 'ISLR Tramo 8: porcentaje (mas de 6000 U.T.)'),
('UT_ISLR_SUST8', 1145, 'ISLR Tramo 8: sustraendo');

CREATE TABLE periodos_nomina (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  tipo ENUM('semanal','quincenal','mensual') NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  dias_habiles TINYINT UNSIGNED DEFAULT 0,
  estado ENUM('abierto','cerrado') NOT NULL DEFAULT 'abierto',
  creado_por INT UNSIGNED NOT NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  cerrado_en DATETIME DEFAULT NULL,
  INDEX idx_estado (estado),
  INDEX idx_fechas (fecha_inicio, fecha_fin),
  FOREIGN KEY fk_creado_por (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE nominas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  periodo_id INT UNSIGNED NOT NULL,
  dias_trabajados TINYINT UNSIGNED NOT NULL DEFAULT 0,
  dias_ausencia TINYINT UNSIGNED DEFAULT 0,
  salario_base_mensual DECIMAL(12,2) NOT NULL,
  total_asignaciones DECIMAL(12,2) NOT NULL,
  total_deducciones DECIMAL(12,2) NOT NULL,
  salario_neto DECIMAL(12,2) NOT NULL,
  costo_patronal DECIMAL(12,2) NOT NULL,
  generada_por INT UNSIGNED NOT NULL,
  generada_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_usuario_periodo (usuario_id, periodo_id),
  INDEX idx_periodo (periodo_id),
  INDEX idx_usuario (usuario_id),
  FOREIGN KEY fk_nomina_usuario (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY fk_nomina_periodo (periodo_id) REFERENCES periodos_nomina(id),
  FOREIGN KEY fk_generada_por (generada_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE detalle_nomina (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomina_id INT UNSIGNED NOT NULL,
  concepto VARCHAR(100) NOT NULL,
  tipo ENUM('asignacion','deduccion') NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  orden TINYINT UNSIGNED DEFAULT 0,
  INDEX idx_nomina (nomina_id),
  INDEX idx_tipo (nomina_id, tipo),
  FOREIGN KEY fk_detalle_nomina (nomina_id) REFERENCES nominas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  exitoso TINYINT(1) DEFAULT 0,
  intento_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email_ip_tiempo (email, ip_address, intento_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
