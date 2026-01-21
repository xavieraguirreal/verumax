-- ================================================================================
-- DIAGNÓSTICO POST-MIGRACIÓN - SAJUR
-- Fecha: 04/12/2025
-- ================================================================================
--
-- Ejecuta este SQL para ver el estado actual de la configuración de SAJuR
--
-- ================================================================================

-- PASO 1: Ver configuración en verumax_general.instances
SELECT '=== verumax_general.instances ===' as tabla;
SELECT
    id_instancia,
    slug,
    nombre,
    nombre_completo,
    logo_url,
    color_primario,
    color_secundario,
    color_acento,
    paleta_colores,
    identitas_activo,
    modulo_certificatum,
    activo
FROM verumax_general.instances
WHERE slug = 'sajur';

-- PASO 2: Ver configuración en verumax_identi.identitas_config
SELECT '=== verumax_identi.identitas_config ===' as tabla;
SELECT
    id_config,
    id_instancia,
    identitas_usar_paleta_general,
    sitio_web_oficial,
    email_contacto,
    redes_sociales,
    favicon_generado,
    created_at,
    updated_at
FROM verumax_identi.identitas_config
WHERE id_instancia = (SELECT id_instancia FROM verumax_general.instances WHERE slug = 'sajur');

-- PASO 3: Ver configuración en verumax_certifi.certificatum_config
SELECT '=== verumax_certifi.certificatum_config ===' as tabla;
SELECT
    id_config,
    id_instancia,
    certificatum_modo,
    certificatum_titulo,
    certificatum_descripcion,
    certificatum_features,
    certificatum_mostrar_stats,
    certificatum_usar_paleta_general,
    created_at,
    updated_at
FROM verumax_certifi.certificatum_config
WHERE id_instancia = (SELECT id_instancia FROM verumax_general.instances WHERE slug = 'sajur');

-- ================================================================================
-- COPIA Y PEGA LOS RESULTADOS
-- ================================================================================
