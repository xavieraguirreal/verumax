-- ================================================================================
-- ELIMINAR CAMPOS DUPLICADOS DE identitas_config
-- Fecha: 04/12/2025 - 21:00
-- ================================================================================
--
-- Estos campos ahora están en verumax_general.instances
-- Los eliminamos de identitas_config para evitar duplicación
--
-- ================================================================================

USE verumax_identi;

-- ================================================================================
-- VERIFICAR ANTES DE ELIMINAR (OPCIONAL)
-- ================================================================================

SELECT
    id_instancia,
    SUBSTRING(mision, 1, 50) as mision,
    email_contacto,
    sitio_web_oficial,
    JSON_EXTRACT(redes_sociales, '$.instagram') as instagram
FROM identitas_config
WHERE id_instancia = 1;

-- ================================================================================
-- ELIMINAR COLUMNAS
-- ================================================================================

ALTER TABLE identitas_config
DROP COLUMN mision,
DROP COLUMN email_contacto,
DROP COLUMN sitio_web_oficial,
DROP COLUMN redes_sociales;

-- ================================================================================
-- VERIFICAR ESTRUCTURA RESULTANTE
-- ================================================================================

SHOW COLUMNS FROM identitas_config;

-- ================================================================================
-- NOTAS
-- ================================================================================
--
-- Después de esto, InstitutionService::getConfig() seguirá funcionando
-- correctamente porque hace array_merge() y los datos ahora vienen de instances.
--
-- El admin panel necesitará actualizarse para editar estos campos en instances.
--
-- ================================================================================
