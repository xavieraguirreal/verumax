-- =====================================================
-- MIGRACIÓN: Agregar campos laborales a miembros
-- Fecha: 2025-12-23
-- Descripción: Agrega lugar_trabajo, cargo y profesion
--              a la tabla de miembros para tener datos
--              más completos de estudiantes y docentes
-- =====================================================

USE verumax_nexus;

-- -----------------------------------------------------
-- Agregar campos laborales después de domicilio_pais
-- (Ignorar errores si las columnas ya existen)
-- -----------------------------------------------------

-- Lugar de trabajo (empresa, institución, etc.)
ALTER TABLE miembros
ADD COLUMN lugar_trabajo VARCHAR(200) NULL DEFAULT NULL
AFTER domicilio_pais;

-- Cargo o posición en el trabajo
ALTER TABLE miembros
ADD COLUMN cargo VARCHAR(150) NULL DEFAULT NULL
AFTER lugar_trabajo;

-- Profesión u oficio
ALTER TABLE miembros
ADD COLUMN profesion VARCHAR(150) NULL DEFAULT NULL
AFTER cargo;
