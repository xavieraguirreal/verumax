-- ================================================================================
-- DIAGNÓSTICO: ¿Dónde están los campos de SEO?
-- Fecha: 04/12/2025 - 22:00
-- ================================================================================

-- ================================================================================
-- OPCIÓN 1: Ver estructura de identitas_config
-- ================================================================================
USE verumax_identi;
SHOW COLUMNS FROM identitas_config;

-- ================================================================================
-- OPCIÓN 2: Ver estructura de verumax_general.instances
-- ================================================================================
USE verumax_general;
SHOW COLUMNS FROM instances WHERE Field LIKE '%seo%';

-- ================================================================================
-- OPCIÓN 3: Buscar en todas las tablas de identitas
-- ================================================================================
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'verumax_identi'
  AND (COLUMN_NAME LIKE '%seo%' OR COLUMN_NAME LIKE '%title%' OR COLUMN_NAME LIKE '%description%' OR COLUMN_NAME LIKE '%keywords%')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

-- ================================================================================
-- OPCIÓN 4: Ver datos actuales en instances
-- ================================================================================
SELECT
    id_instancia,
    slug,
    seo_title,
    seo_description,
    seo_keywords
FROM verumax_general.instances
WHERE id_instancia = 1;

-- ================================================================================
-- NOTAS
-- ================================================================================
--
-- Este script ayuda a identificar dónde están los campos de SEO.
-- Ejecutá cada query por separado y copiame los resultados.
--
-- ================================================================================
