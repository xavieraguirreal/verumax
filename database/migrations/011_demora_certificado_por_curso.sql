-- ============================================================================
-- MIGRACIÓN 011: Agregar configuración de demora de certificado por curso
-- Fecha: 2025-12-29
-- Descripción: Permite configurar una demora específica por curso o usar la
--              configuración global de la institución
-- ============================================================================

-- IMPORTANTE: Ejecutar en este orden exacto

-- 1. Agregar campo para indicar si usa demora global o propia
ALTER TABLE verumax_academi.cursos
ADD COLUMN usar_demora_global TINYINT(1) DEFAULT 1
COMMENT 'Si es 1, usa la demora de la institución. Si es 0, usa demora_certificado_horas del curso'
AFTER firmante_2_firma_url;

-- 2. Agregar campo para demora propia del curso
ALTER TABLE verumax_academi.cursos
ADD COLUMN demora_certificado_horas INT UNSIGNED DEFAULT NULL
COMMENT 'Horas de demora propia del curso (solo si usar_demora_global=0)'
AFTER usar_demora_global;

-- 3. Verificar migración
SELECT
    id_curso,
    codigo_curso,
    nombre_curso,
    usar_demora_global,
    demora_certificado_horas
FROM verumax_academi.cursos
LIMIT 5;

-- NOTAS:
-- - Por defecto todos los cursos usan la configuración global (usar_demora_global = 1)
-- - Para usar demora propia: UPDATE cursos SET usar_demora_global = 0, demora_certificado_horas = 0 WHERE id_curso = X;
-- - demora_certificado_horas = 0 significa certificado disponible inmediatamente
-- - demora_certificado_horas = NULL cuando usar_demora_global = 1
