-- ============================================================
--  Migración: ausencias remuneradas con límite anual de horas
--  Aplicar sobre una base de datos FichaPro ya existente:
--    mysql -u root fichapro < app/sql/migracion_ausencias_limite.sql
-- ============================================================

USE `fichapro`;

-- Límite anual de horas remuneradas por tipo (0 = sin límite)
ALTER TABLE `tipos_ausencia`
  ADD COLUMN IF NOT EXISTS `limite_horas_anual` INT NOT NULL DEFAULT 0 AFTER `dias_estimados`;

-- Horas realmente remuneradas de cada ausencia (lo que cuenta dentro del límite)
ALTER TABLE `ausencias`
  ADD COLUMN IF NOT EXISTS `horas_remuneradas` DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER `horas`;

-- Límites de ejemplo para los tipos remunerados ya existentes (sólo si están a 0)
UPDATE `tipos_ausencia` SET `limite_horas_anual` = 24 WHERE `nombre` = 'Permiso retribuido' AND `limite_horas_anual` = 0;
UPDATE `tipos_ausencia` SET `limite_horas_anual` = 40 WHERE `nombre` = 'Asuntos propios'    AND `limite_horas_anual` = 0;
UPDATE `tipos_ausencia` SET `limite_horas_anual` = 16 WHERE `nombre` = 'Visita médica'      AND `limite_horas_anual` = 0;
UPDATE `tipos_ausencia` SET `limite_horas_anual` =  8 WHERE `nombre` = 'Mudanza'            AND `limite_horas_anual` = 0;
UPDATE `tipos_ausencia` SET `limite_horas_anual` = 16 WHERE `nombre` = 'Asuntos familiares' AND `limite_horas_anual` = 0;
