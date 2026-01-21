-- ================================================================================
-- MIGRAR DATOS INSTITUCIONALES A verumax_general.instances
-- Fecha: 04/12/2025 - 20:20
-- ================================================================================
--
-- PROBLEMA:
-- Los campos mision, email_contacto, sitio_web_oficial, redes_sociales están en
-- verumax_identi.identitas_config pero son datos GENERALES del cliente, no
-- específicos de Identitas.
--
-- SOLUCIÓN:
-- 1. Agregar estos campos a verumax_general.instances
-- 2. Migrar los datos desde identitas_config
-- 3. Mantener campos en identitas_config por retrocompatibilidad (opcional)
--
-- ================================================================================

USE verumax_general;

-- ================================================================================
-- PASO 1: AGREGAR CAMPOS A verumax_general.instances
-- ================================================================================

ALTER TABLE instances
ADD COLUMN mision TEXT DEFAULT NULL COMMENT 'Misión o descripción de la institución' AFTER nombre_completo,
ADD COLUMN email_contacto VARCHAR(255) DEFAULT NULL COMMENT 'Email de contacto institucional' AFTER mision,
ADD COLUMN sitio_web_oficial VARCHAR(500) DEFAULT NULL COMMENT 'URL del sitio web oficial' AFTER email_contacto,
ADD COLUMN redes_sociales JSON DEFAULT NULL COMMENT 'URLs de redes sociales (Instagram, Facebook, LinkedIn, WhatsApp, Twitter, YouTube)' AFTER sitio_web_oficial,
ADD INDEX idx_email_contacto (email_contacto);

-- ================================================================================
-- PASO 2: MIGRAR DATOS DESDE verumax_identi.identitas_config
-- ================================================================================

-- SAJuR (id_instancia = 1)
UPDATE verumax_general.instances i
INNER JOIN verumax_identi.identitas_config ic ON i.id_instancia = ic.id_instancia
SET
    i.mision = ic.mision,
    i.email_contacto = ic.email_contacto,
    i.sitio_web_oficial = ic.sitio_web_oficial,
    i.redes_sociales = ic.redes_sociales
WHERE i.id_instancia = 1;

-- Si hay más instituciones, agregar aquí:
-- UPDATE verumax_general.instances i
-- INNER JOIN verumax_identi.identitas_config ic ON i.id_instancia = ic.id_instancia
-- SET ...
-- WHERE i.id_instancia = 2;

-- ================================================================================
-- PASO 3: VERIFICAR MIGRACIÓN
-- ================================================================================

SELECT
    id_instancia,
    slug,
    nombre,
    mision,
    email_contacto,
    sitio_web_oficial,
    JSON_EXTRACT(redes_sociales, '$.instagram') as instagram,
    JSON_EXTRACT(redes_sociales, '$.facebook') as facebook
FROM verumax_general.instances
WHERE id_instancia = 1;

-- ================================================================================
-- PASO 4 (OPCIONAL): ELIMINAR CAMPOS DE identitas_config
-- ================================================================================
--
-- NOTA: Mantener estos campos por ahora para retrocompatibilidad
-- El InstitutionService ya hace array_merge(), así que si los datos están en
-- ambas tablas, prevalecen los de identitas_config (último merge)
--
-- Cuando todo esté migrado y probado, ejecutar:
--
-- USE verumax_identi;
-- ALTER TABLE identitas_config
-- DROP COLUMN mision,
-- DROP COLUMN email_contacto,
-- DROP COLUMN sitio_web_oficial,
-- DROP COLUMN redes_sociales;
--
-- ================================================================================

-- ================================================================================
-- NOTAS IMPORTANTES
-- ================================================================================
--
-- 1. El footer compartido ya está buscando estos campos en $instance, que viene
--    de InstitutionService::getConfig()
--
-- 2. Después de ejecutar este SQL, los datos estarán en ambas tablas
--
-- 3. El admin panel (general.php) debería actualizarse para editar estos campos
--    en verumax_general.instances en lugar de identitas_config
--
-- 4. Futuro: Deprecar estos campos en identitas_config
--
-- ================================================================================
