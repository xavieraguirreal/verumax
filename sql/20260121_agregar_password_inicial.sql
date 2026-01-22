-- ============================================================================
-- MIGRACIÓN: Agregar campos para tracking de cambio de contraseña
-- Fecha: 2026-01-21
-- Ejecutar en: verumax_general
-- ============================================================================
-- NOTA: El campo admin_password_plain YA EXISTE y guarda la contraseña inicial.
--       Solo agregamos campos para saber si el cliente la cambió.
-- ============================================================================

USE verumax_general;

-- Agregar campo para indicar si el cliente cambió su contraseña
ALTER TABLE instances
ADD COLUMN admin_password_cambiada TINYINT(1) DEFAULT 0
COMMENT '1 si el cliente cambió su contraseña desde el admin'
AFTER admin_password_plain;

-- Agregar campo para fecha de cambio de contraseña
ALTER TABLE instances
ADD COLUMN admin_password_fecha_cambio DATETIME DEFAULT NULL
COMMENT 'Fecha en que el cliente cambió su contraseña'
AFTER admin_password_cambiada;

-- Verificar que se agregaron correctamente
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'verumax_general'
AND TABLE_NAME = 'instances'
AND COLUMN_NAME LIKE 'admin_password%';
