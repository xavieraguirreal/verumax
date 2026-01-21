-- ============================================================================
-- CONFIGURACION IA PARA VERUMAX
-- Ejecutar en phpMyAdmin sobre la base de datos 'verumax_general'
-- Fecha: 2025-11-23
--
-- NOTA: La API key de IA se almacena centralmente en config.php
--       Solo se necesita una columna para habilitar/deshabilitar por institucion
-- ============================================================================

-- Agregar columna para habilitar/deshabilitar IA por institucion
-- La API key es de VERUMax, no del cliente (se ofrece como servicio)
ALTER TABLE `instances`
ADD COLUMN `ia_habilitada` TINYINT(1) DEFAULT 0
    COMMENT '1 = IA habilitada para esta institucion, 0 = deshabilitada';

-- Indice para consultas rapidas
CREATE INDEX `idx_ia_habilitada` ON `instances` (`ia_habilitada`);
