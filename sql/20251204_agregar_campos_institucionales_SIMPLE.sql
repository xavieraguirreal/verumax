-- ================================================================================
-- AGREGAR CAMPOS INSTITUCIONALES A verumax_general.instances (SIMPLE)
-- Fecha: 04/12/2025 - 20:40
-- ================================================================================

USE verumax_general;



-- Agregar índice
ALTER TABLE instances
ADD INDEX idx_email_contacto (email_contacto);

-- ================================================================================
-- PASO 2: MIGRAR DATOS DESDE identitas_config
-- ================================================================================

UPDATE verumax_general.instances i
INNER JOIN verumax_identi.identitas_config ic ON i.id_instancia = ic.id_instancia
SET
    i.mision = ic.mision,
    i.email_contacto = ic.email_contacto,
    i.redes_sociales = ic.redes_sociales
WHERE i.id_instancia = 1;

-- ================================================================================
-- PASO 3: VERIFICAR RESULTADO
-- ================================================================================

SELECT
    id_instancia,
    slug,
    nombre,
    SUBSTRING(mision, 1, 80) as mision_preview,
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
-- Si una columna ya existe, saltará error en ese ALTER TABLE específico.
-- Simplemente continuá con las siguientes líneas.
--
-- sitio_web_oficial ya existe, por eso NO la agregamos aquí.
--
-- ================================================================================
