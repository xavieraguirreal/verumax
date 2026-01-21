-- ================================================================================
-- VERIFICAR Y MIGRAR DATOS INSTITUCIONALES (SIN PREPARED STATEMENTS)
-- Fecha: 04/12/2025 - 20:35
-- ================================================================================

USE verumax_general;

-- ================================================================================
-- PASO 1: VERIFICAR QUÉ COLUMNAS EXISTEN
-- ================================================================================
-- Ejecutá esta query primero para ver qué columnas ya existen:

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
-- PASO 2: AGREGAR COLUMNAS FALTANTES (EJECUTAR SOLO LAS QUE FALTAN)
-- ================================================================================
-- Basado en el resultado anterior, ejecutá solo las líneas que correspondan:

-- Si NO existe 'mision':
-- ALTER TABLE instances ADD COLUMN mision TEXT DEFAULT NULL COMMENT 'Misión o descripción de la institución' AFTER nombre_completo;

-- Si NO existe 'email_contacto':
-- ALTER TABLE instances ADD COLUMN email_contacto VARCHAR(255) DEFAULT NULL COMMENT 'Email de contacto institucional' AFTER mision;

-- Si NO existe 'sitio_web_oficial':
-- ALTER TABLE instances ADD COLUMN sitio_web_oficial VARCHAR(500) DEFAULT NULL COMMENT 'URL del sitio web oficial' AFTER email_contacto;

-- Si NO existe 'redes_sociales':
-- ALTER TABLE instances ADD COLUMN redes_sociales JSON DEFAULT NULL COMMENT 'URLs de redes sociales' AFTER sitio_web_oficial;

-- Si NO existe el índice:
-- ALTER TABLE instances ADD INDEX idx_email_contacto (email_contacto);

-- ================================================================================
-- PASO 3: MIGRAR DATOS DESDE identitas_config
-- ================================================================================
-- Esta query actualiza instances con los datos de identitas_config
-- Solo sobrescribe si el campo en instances está vacío (NULL)

UPDATE verumax_general.instances i
INNER JOIN verumax_identi.identitas_config ic ON i.id_instancia = ic.id_instancia
SET
    i.mision = COALESCE(i.mision, ic.mision),
    i.email_contacto = COALESCE(i.email_contacto, ic.email_contacto),
    i.sitio_web_oficial = COALESCE(i.sitio_web_oficial, ic.sitio_web_oficial),
    i.redes_sociales = COALESCE(i.redes_sociales, ic.redes_sociales)
WHERE i.id_instancia = 1;

-- ================================================================================
-- PASO 4: VERIFICAR RESULTADO
-- ================================================================================

SELECT
    id_instancia,
    slug,
    nombre,
    SUBSTRING(mision, 1, 80) as mision_preview,
    email_contacto,
    sitio_web_oficial,
    JSON_EXTRACT(redes_sociales, '$.instagram') as instagram,
    JSON_EXTRACT(redes_sociales, '$.facebook') as facebook,
    JSON_EXTRACT(redes_sociales, '$.linkedin') as linkedin
FROM verumax_general.instances
WHERE id_instancia = 1;

-- ================================================================================
-- PASO 5: COMPARAR CON identitas_config (PARA VERIFICAR)
-- ================================================================================

SELECT
    'INSTANCES' as tabla,
    id_instancia,
    SUBSTRING(mision, 1, 50) as mision,
    email_contacto,
    sitio_web_oficial
FROM verumax_general.instances
WHERE id_instancia = 1

UNION ALL

SELECT
    'IDENTITAS_CONFIG' as tabla,
    id_instancia,
    SUBSTRING(mision, 1, 50) as mision,
    email_contacto,
    sitio_web_oficial
FROM verumax_identi.identitas_config
WHERE id_instancia = 1;

-- ================================================================================
-- INSTRUCCIONES DE USO
-- ================================================================================
--
-- 1. Ejecutá PASO 1 para ver qué columnas ya existen
-- 2. Descomentá y ejecutá las líneas del PASO 2 que correspondan
-- 3. Ejecutá PASO 3 para migrar los datos
-- 4. Ejecutá PASO 4 para verificar que se migraron correctamente
-- 5. Ejecutá PASO 5 para comparar ambas tablas
--
-- ================================================================================
