-- ============================================================
--  Migración: compensación de horas + configuración SMTP
--  Aplicar sobre una base de datos FichaPro ya existente:
--    mysql -u root fichapro < app/sql/migracion_compensaciones.sql
--  (o importar este archivo desde phpMyAdmin)
-- ============================================================

USE `fichapro`;

-- Tabla de compensaciones de horas (extra pagadas o recuperadas)
CREATE TABLE IF NOT EXISTS `compensaciones` (
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

-- Claves de configuración SMTP (no sobrescribe valores ya guardados)
INSERT IGNORE INTO `ajustes` (`clave`,`valor`,`descripcion`) VALUES
('smtp_host','smtp.gmail.com','Servidor SMTP para el envío de correos'),
('smtp_port','587','Puerto SMTP (587 = TLS, 465 = SSL)'),
('smtp_secure','tls','Cifrado SMTP: tls o ssl'),
('smtp_user','','Usuario / correo remitente SMTP'),
('smtp_pass','','Contraseña o contraseña de aplicación SMTP'),
('smtp_from_name','FichaPro','Nombre mostrado como remitente');
