-- ================================================================================
-- MIGRAR DATOS INSTITUCIONALES A verumax_general.instances (VERSIÓN SEGURA)
-- Fecha: 04/12/2025 - 20:30
-- ================================================================================
--
-- Esta versión verifica qué columnas faltan antes de agregarlas
--
-- ================================================================================

USE verumax_general;

-- ================================================================================
-- PASO 1: VERIFICAR QUÉ COLUMNAS EXISTEN
-- ================================================================================

SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'verumax_general'
  AND TABLE_NAME = 'instances'
  AND COLUMN_NAME IN ('mision', 'email_contacto', 'sitio_web_oficial', 'redes_sociales')
ORDER BY ORDINAL_POSITION;

-- ================================================================================
-- PASO 2: AGREGAR SOLO LAS COLUMNAS QUE FALTAN
-- ================================================================================

-- Agregar 'mision' si no existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'verumax_general'
      AND TABLE_NAME = 'instances'
      AND COLUMN_NAME = 'mision'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE instances ADD COLUMN mision TEXT DEFAULT NULL COMMENT ''Misión o descripción de la institución'' AFTER nombre_completo',
    'SELECT ''La columna mision ya existe'' AS mensaje'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar 'email_contacto' si no existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'verumax_general'
      AND TABLE_NAME = 'instances'
      AND COLUMN_NAME = 'email_contacto'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE instances ADD COLUMN email_contacto VARCHAR(255) DEFAULT NULL COMMENT ''Email de contacto institucional'' AFTER mision',
    'SELECT ''La columna email_contacto ya existe'' AS mensaje'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar 'sitio_web_oficial' si no existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'verumax_general'
      AND TABLE_NAME = 'instances'
      AND COLUMN_NAME = 'sitio_web_oficial'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE instances ADD COLUMN sitio_web_oficial VARCHAR(500) DEFAULT NULL COMMENT ''URL del sitio web oficial'' AFTER email_contacto',
    'SELECT ''La columna sitio_web_oficial ya existe'' AS mensaje'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar 'redes_sociales' si no existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'verumax_general'
      AND TABLE_NAME = 'instances'
      AND COLUMN_NAME = 'redes_sociales'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE instances ADD COLUMN redes_sociales JSON DEFAULT NULL COMMENT ''URLs de redes sociales (Instagram, Facebook, LinkedIn, WhatsApp, Twitter, YouTube)'' AFTER sitio_web_oficial',
    'SELECT ''La columna redes_sociales ya existe'' AS mensaje'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar índice si no existe
SET @index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'verumax_general'
      AND TABLE_NAME = 'instances'
      AND INDEX_NAME = 'idx_email_contacto'
);

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE instances ADD INDEX idx_email_contacto (email_contacto)',
    'SELECT ''El índice idx_email_contacto ya existe'' AS mensaje'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================================================
-- PASO 3: MIGRAR DATOS DESDE verumax_identi.identitas_config
-- ================================================================================

-- Actualizar solo los campos que tienen valor en identitas_config
UPDATE verumax_general.instances i
INNER JOIN verumax_identi.identitas_config ic ON i.id_instancia = ic.id_instancia
SET
    i.mision = COALESCE(i.mision, ic.mision),
    i.email_contacto = COALESCE(i.email_contacto, ic.email_contacto),
    i.sitio_web_oficial = COALESCE(i.sitio_web_oficial, ic.sitio_web_oficial),
    i.redes_sociales = COALESCE(i.redes_sociales, ic.redes_sociales)
WHERE i.id_instancia = 1
  AND (i.mision IS NULL OR i.email_contacto IS NULL OR i.sitio_web_oficial IS NULL OR i.redes_sociales IS NULL);

-- ================================================================================
-- PASO 4: VERIFICAR RESULTADO
-- ================================================================================

SELECT
    id_instancia,
    slug,
    nombre,
    LEFT(mision, 50) as mision_preview,
    email_contacto,
    sitio_web_oficial,
    JSON_EXTRACT(redes_sociales, '$.instagram') as instagram,
    JSON_EXTRACT(redes_sociales, '$.facebook') as facebook
FROM verumax_general.instances
WHERE id_instancia = 1;

-- ================================================================================
-- NOTAS
-- ================================================================================
--
-- Este script es seguro para ejecutar múltiples veces porque:
-- 1. Verifica si cada columna existe antes de agregarla
-- 2. Usa COALESCE para no sobrescribir datos existentes
-- 3. Muestra mensajes informativos
--
-- ================================================================================
