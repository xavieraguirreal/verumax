-- ================================================================================
-- MIGRAR LOGO DESDE identitas_instances A verumax_general.instances
-- Fecha: 04/12/2025
-- ================================================================================
--
-- PROBLEMA: El logo existe en verumax_identi.identitas_instances
-- pero no está en verumax_general.instances donde debería estar
--
-- ================================================================================

-- PASO 1: Ver el logo actual en identitas_instances (tabla vieja)
SELECT
    '=== Logo en identitas_instances (tabla vieja) ===' as info,
    slug,
    logo_url,
    nombre,
    color_primario
FROM verumax_identi.identitas_instances
WHERE slug = 'sajur';

-- PASO 2: Ver el logo actual en instances (tabla correcta)
SELECT
    '=== Logo en instances (tabla correcta) ===' as info,
    slug,
    logo_url,
    nombre,
    color_primario
FROM verumax_general.instances
WHERE slug = 'sajur';

-- PASO 3: Copiar el logo_url de identitas_instances a instances
UPDATE verumax_general.instances gen
INNER JOIN verumax_identi.identitas_instances identi
    ON gen.slug = identi.slug
SET
    gen.logo_url = identi.logo_url,
    gen.nombre = COALESCE(gen.nombre, identi.nombre),
    gen.nombre_completo = COALESCE(gen.nombre_completo, identi.nombre_completo),
    gen.color_primario = COALESCE(gen.color_primario, identi.color_primario),
    gen.color_secundario = COALESCE(gen.color_secundario, identi.color_secundario),
    gen.color_acento = COALESCE(gen.color_acento, identi.color_acento),
    gen.fecha_actualizacion = CURRENT_TIMESTAMP
WHERE gen.slug = 'sajur';

-- PASO 4: Verificar que se copió correctamente
SELECT
    '=== ✓ Migración completada ===' as resultado,
    slug,
    logo_url,
    nombre,
    color_primario,
    identitas_activo,
    modulo_certificatum
FROM verumax_general.instances
WHERE slug = 'sajur';

-- ================================================================================
-- NOTA: Si ves que logo_url ahora tiene una ruta válida, reinicia Apache:
-- sudo systemctl restart httpd
-- ================================================================================
