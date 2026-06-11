-- ============================================================
--  FichaPro — esquema de base de datos (diseño original)
--  Importar en phpMyAdmin o:  mysql -u root < fichapro.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `fichapro` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fichapro`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `auditoria`, `password_resets`, `vehiculos`, `incidencias`,
    `tareas`, `tipos_tarea`, `descansos`, `compensaciones`, `fichajes`, `ausencias`,
    `vacaciones`, `tipos_ausencia`, `jornadas`, `ajustes`, `empleados`;
SET FOREIGN_KEY_CHECKS = 1;

-- ── Empleados ────────────────────────────────────────────────────────
CREATE TABLE `empleados` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `nombre`           VARCHAR(100) NOT NULL,
  `apellidos`        VARCHAR(150) NOT NULL DEFAULT '',
  `usuario`          VARCHAR(60)  NOT NULL,
  `password`         VARCHAR(255) NOT NULL,
  `dni`              VARCHAR(20)  NOT NULL,
  `telefono`         VARCHAR(20)  DEFAULT NULL,
  `email`            VARCHAR(150) NOT NULL,
  `fecha_nacimiento` DATE         DEFAULT NULL,
  `foto`             VARCHAR(255) DEFAULT NULL,
  `rol`              ENUM('admin','trabajador') NOT NULL DEFAULT 'trabajador',
  `activo`           TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_usuario` (`usuario`),
  UNIQUE KEY `uk_email`   (`email`),
  UNIQUE KEY `uk_dni`     (`dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Ajustes (configuración global clave/valor) ──────────────────────
CREATE TABLE `ajustes` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `clave`       VARCHAR(100) NOT NULL,
  `valor`       TEXT         DEFAULT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY `uk_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Jornadas (horario contratado por empleado) ──────────────────────
CREATE TABLE `jornadas` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `id_empleado`  INT NOT NULL,
  `horas_dia`    DECIMAL(4,2) NOT NULL,
  `horas_semana` DECIMAL(5,2) NOT NULL,
  `fecha_inicio` DATE DEFAULT NULL,
  `fecha_fin`    DATE DEFAULT NULL,
  KEY `idx_jornada_emp` (`id_empleado`),
  CONSTRAINT `fk_jornada_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Fichajes (entrada/salida diaria) ────────────────────────────────
CREATE TABLE `fichajes` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `id_empleado`      INT NOT NULL,
  `fecha`            DATE NOT NULL,
  `hora_entrada`     TIME NOT NULL,
  `hora_salida`      TIME DEFAULT NULL,
  `total_horas`      DECIMAL(5,2) DEFAULT NULL,
  `horas_ordinarias` DECIMAL(5,2) DEFAULT NULL,
  `horas_extra`      DECIMAL(5,2) DEFAULT NULL,
  `estado`           ENUM('abierto','cerrado','incidencia','validado') NOT NULL DEFAULT 'abierto',
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_fichaje_emp`   (`id_empleado`),
  KEY `idx_fichaje_fecha` (`fecha`),
  CONSTRAINT `fk_fichaje_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Compensación de horas (extra pagadas o recuperadas) ─────────────
CREATE TABLE `compensaciones` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `id_empleado` INT NOT NULL,
  `fecha`       DATE NOT NULL,
  `horas`       DECIMAL(5,2) NOT NULL,
  `tipo`        ENUM('pagada','recuperada') NOT NULL DEFAULT 'pagada',
  `concepto`    VARCHAR(255) DEFAULT NULL,
  `id_creador`  INT DEFAULT NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_comp_emp`   (`id_empleado`),
  KEY `idx_comp_fecha` (`fecha`),
  CONSTRAINT `fk_comp_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Descansos (pausas dentro de un fichaje) ─────────────────────────
CREATE TABLE `descansos` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `id_fichaje`  INT NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fin`    TIME DEFAULT NULL,
  `motivo`      ENUM('Descanso','Comida','Pausa personal','Fumar','Gestión laboral','Otro') NOT NULL DEFAULT 'Descanso',
  KEY `idx_descanso_fichaje` (`id_fichaje`),
  CONSTRAINT `fk_descanso_fichaje` FOREIGN KEY (`id_fichaje`) REFERENCES `fichajes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tipos de tarea ──────────────────────────────────────────────────
CREATE TABLE `tipos_tarea` (
  `id`     INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tareas (registradas durante un fichaje) ─────────────────────────
CREATE TABLE `tareas` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `id_fichaje`  INT NOT NULL,
  `id_empleado` INT NOT NULL,
  `id_tipo`     INT DEFAULT NULL,
  `titulo`      VARCHAR(150) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `hora_inicio` TIME DEFAULT NULL,
  `hora_fin`    TIME DEFAULT NULL,
  `total_horas` DECIMAL(5,2) DEFAULT NULL,
  `estado`      ENUM('pendiente','en_progreso','finalizada') NOT NULL DEFAULT 'pendiente',
  `fecha`       DATE NOT NULL,
  KEY `idx_tarea_fichaje` (`id_fichaje`),
  KEY `idx_tarea_emp`     (`id_empleado`),
  CONSTRAINT `fk_tarea_fichaje` FOREIGN KEY (`id_fichaje`) REFERENCES `fichajes`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tarea_emp`     FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tarea_tipo`    FOREIGN KEY (`id_tipo`) REFERENCES `tipos_tarea`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Incidencias (reclamación sobre un fichaje) ──────────────────────
CREATE TABLE `incidencias` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `id_fichaje`  INT NOT NULL,
  `mensaje`     TEXT NOT NULL,
  `respuesta`   TEXT DEFAULT NULL,
  `estado`      ENUM('pendiente','resuelta') NOT NULL DEFAULT 'pendiente',
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_incidencia_fichaje` (`id_fichaje`),
  CONSTRAINT `fk_incidencia_fichaje` FOREIGN KEY (`id_fichaje`) REFERENCES `fichajes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tipos de ausencia (catálogo) ────────────────────────────────────
CREATE TABLE `tipos_ausencia` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `nombre`         VARCHAR(120) NOT NULL,
  `descripcion`    VARCHAR(255) DEFAULT NULL,
  `tipo`           ENUM('baja','permiso','personal','otro') NOT NULL DEFAULT 'permiso',
  `remunerada`     TINYINT(1) NOT NULL DEFAULT 1,
  `dias_estimados` INT DEFAULT 0,
  `limite_horas_anual` INT NOT NULL DEFAULT 0,   -- 0 = sin límite; horas remuneradas/año
  `activo`         TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Vacaciones (solicitudes) ────────────────────────────────────────
CREATE TABLE `vacaciones` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `id_empleado`  INT NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin`    DATE NOT NULL,
  `dias`         INT NOT NULL,
  `estado`       ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `comentario`   TEXT DEFAULT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_vac_emp` (`id_empleado`),
  CONSTRAINT `fk_vac_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Ausencias / bajas (solicitudes) ─────────────────────────────────
CREATE TABLE `ausencias` (
  `id`                  INT AUTO_INCREMENT PRIMARY KEY,
  `id_empleado`         INT NOT NULL,
  `id_tipo`             INT DEFAULT NULL,
  `motivo_personalizado` VARCHAR(255) DEFAULT NULL,
  `fecha_inicio`        DATE NOT NULL,
  `fecha_fin`           DATE DEFAULT NULL,
  `horas`               DECIMAL(5,2) DEFAULT NULL,
  `horas_remuneradas`   DECIMAL(5,2) NOT NULL DEFAULT 0,
  `remunerada`          TINYINT(1) NOT NULL DEFAULT 1,
  `estado`              ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `observaciones`       TEXT DEFAULT NULL,
  `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_aus_emp` (`id_empleado`),
  CONSTRAINT `fk_aus_emp`  FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aus_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_ausencia`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Vehículos (matrículas por empleado) ─────────────────────────────
CREATE TABLE `vehiculos` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `id_empleado` INT NOT NULL,
  `matricula`   VARCHAR(20) NOT NULL,
  `marca`       VARCHAR(50) DEFAULT NULL,
  `modelo`      VARCHAR(50) DEFAULT NULL,
  `color`       VARCHAR(30) DEFAULT NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_veh_emp` (`id_empleado`),
  CONSTRAINT `fk_veh_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Auditoría (registro de cambios) ─────────────────────────────────
CREATE TABLE `auditoria` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `id_empleado`    INT DEFAULT NULL,
  `tabla`          VARCHAR(50) NOT NULL,
  `id_registro`    INT DEFAULT NULL,
  `accion`         VARCHAR(50) NOT NULL,
  `detalle`        TEXT DEFAULT NULL,
  `fecha`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_audit_emp` (`id_empleado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Recuperación de contraseña ──────────────────────────────────────
CREATE TABLE `password_resets` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `id_empleado` INT NOT NULL,
  `token`       VARCHAR(255) NOT NULL,
  `expira`      DATETIME NOT NULL,
  `usado`       TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_reset_token` (`token`),
  CONSTRAINT `fk_reset_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Datos iniciales (ejemplo — sin datos reales)
-- ============================================================

-- Admin por defecto · usuario: admin · contraseña: admin123
INSERT INTO `empleados` (`nombre`,`apellidos`,`usuario`,`password`,`dni`,`telefono`,`email`,`rol`,`activo`) VALUES
('Admin','Principal','admin','$2y$10$SDn3Kkg9iv5r09wlqZAnb.R0Mp1BaPkaayD2StaRvHJqWb20RgQCK','00000000A','600000000','admin@fichapro.local','admin',1),
('Lucía','García Ruiz','lucia','$2y$10$SDn3Kkg9iv5r09wlqZAnb.R0Mp1BaPkaayD2StaRvHJqWb20RgQCK','11111111B','611111111','lucia@empresa.com','trabajador',1),
('Carlos','Martín Soto','carlos','$2y$10$SDn3Kkg9iv5r09wlqZAnb.R0Mp1BaPkaayD2StaRvHJqWb20RgQCK','22222222C','622222222','carlos@empresa.com','trabajador',1);

INSERT INTO `ajustes` (`clave`,`valor`,`descripcion`) VALUES
('nombre_empresa','Mi Empresa','Nombre de la empresa'),
('hora_inicio_jornada','08:00','Hora oficial de inicio de la jornada'),
('umbral_retraso_minutos','30','Margen en minutos antes de marcar un fichaje como retraso'),
('horas_jornada_defecto','7.5','Horas diarias por defecto'),
('horas_semana_defecto','37.5','Horas semanales por defecto'),
('smtp_host','smtp.gmail.com','Servidor SMTP para el envío de correos'),
('smtp_port','587','Puerto SMTP (587 = TLS, 465 = SSL)'),
('smtp_secure','tls','Cifrado SMTP: tls o ssl'),
('smtp_user','','Usuario / correo remitente SMTP'),
('smtp_pass','','Contraseña o contraseña de aplicación SMTP'),
('smtp_from_name','FichaPro','Nombre mostrado como remitente');

INSERT INTO `tipos_tarea` (`nombre`) VALUES
('Oficina'),('Teletrabajo'),('Visita a cliente'),('Formación');

INSERT INTO `tipos_ausencia` (`nombre`,`descripcion`,`tipo`,`remunerada`,`dias_estimados`,`limite_horas_anual`,`activo`) VALUES
('Baja médica','Incapacidad temporal por enfermedad o accidente','baja',1,0,0,1),
('Permiso retribuido','Permiso justificado con derecho a remuneración','permiso',1,1,24,1),
('Asuntos propios','Días de libre disposición del trabajador','personal',1,1,40,1),
('Permiso sin sueldo','Ausencia autorizada sin remuneración','otro',0,1,0,1),
('Visita médica','Tiempo para acudir a consulta médica','permiso',1,0,16,1),
('Mudanza','Cambio de domicilio habitual','permiso',1,1,8,1),
('Asuntos familiares','Atención de un familiar','permiso',1,1,16,1),
('Formación externa','Asistencia a formación fuera de la empresa','otro',0,1,0,1);

INSERT INTO `jornadas` (`id_empleado`,`horas_dia`,`horas_semana`,`fecha_inicio`) VALUES
(1,7.50,37.50,'2026-01-01'),
(2,7.50,37.50,'2026-01-01'),
(3,8.00,40.00,'2026-01-01');
