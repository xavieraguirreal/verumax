-- ============================================================================
-- MIGRACIÓN 010: Mover demora_certificado_horas a certificatum_config
-- Fecha: 2025-12-29
-- Descripción: Traslada la configuración de demora de certificados desde
--              verumax_general.instances a verumax_certifi.certificatum_config
-- ============================================================================

-- IMPORTANTE: Ejecutar en este orden exacto

-- 1. Agregar columna a certificatum_config
ALTER TABLE verumax_certifi.certificatum_config
ADD COLUMN demora_certificado_horas INT UNSIGNED DEFAULT 24
COMMENT 'Horas de espera entre aprobación y disponibilidad del certificado (0=inmediato)'
AFTER certificatum_features;

-- 2. Migrar datos existentes desde instances
UPDATE verumax_certifi.certificatum_config cc
INNER JOIN verumax_general.instances i ON cc.id_instancia = i.id_instancia
SET cc.demora_certificado_horas = COALESCE(i.demora_certificado_horas, 24);

-- 3. Verificar migración (ejecutar para confirmar)
SELECT
    cc.id_instancia,
    i.slug,
    i.demora_certificado_horas AS demora_en_instances,
    cc.demora_certificado_horas AS demora_en_certifi,
    CASE
        WHEN i.demora_certificado_horas = cc.demora_certificado_horas THEN 'OK'
        ELSE 'REVISAR'
    END AS estado_migracion
FROM verumax_certifi.certificatum_config cc
INNER JOIN verumax_general.instances i ON cc.id_instancia = i.id_instancia;

-- NOTA: No eliminamos la columna de instances por seguridad.
-- Una vez confirmado que todo funciona en producción, se puede eliminar manualmente:
-- ALTER TABLE verumax_general.instances DROP COLUMN demora_certificado_horas;
