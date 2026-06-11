-- ============================================================
--  Migración: foto de perfil del empleado
--  Aplicar sobre una base de datos FichaPro ya existente:
--    mysql -u root fichapro < app/sql/migracion_foto_perfil.sql
-- ============================================================

USE `fichapro`;

ALTER TABLE `empleados`
  ADD COLUMN IF NOT EXISTS `foto` VARCHAR(255) DEFAULT NULL AFTER `fecha_nacimiento`;
