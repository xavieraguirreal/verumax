-- ================================================================================
-- MIGRACIÓN SIMPLIFICADA - SAJUR
-- Fecha: 04/12/2025
-- ================================================================================
--
-- Este SQL migra los datos de SAJuR a la arquitectura correcta
-- usando solo las columnas que realmente existen
--
-- ================================================================================

-- PASO 1: Obtener id_instancia de SAJuR
SET @id_sajur = (SELECT id_instancia FROM verumax_general.instances WHERE slug = 'sajur');

-- PASO 2: Actualizar verumax_general.instances con datos básicos de SAJuR
UPDATE verumax_general.instances
SET
    nombre = 'SAJuR',
    nombre_completo = 'Sociedad Argentina de Justicia Restaurativa',
    logo_url = '/identitas/assets/logo-sajur.png',
    color_primario = '#692f7f',
    color_secundario = '#ad5425',
    color_acento = '#fdff99',
    paleta_colores = 'verde-elegante',
    identitas_activo = 1,
    modulo_certificatum = 1,
    fecha_actualizacion = CURRENT_TIMESTAMP
WHERE slug = 'sajur';

-- PASO 3: Actualizar verumax_identi.identitas_config con datos de Identitas
INSERT INTO verumax_identi.identitas_config (
    id_instancia,
    identitas_usar_paleta_general,
    redes_sociales,
    sitio_web_oficial,
    email_contacto,
    favicon_generado
)
SELECT
    @id_sajur,
    1, -- Usar paleta general
    redes_sociales,
    JSON_UNQUOTE(JSON_EXTRACT(configuracion, '$.sitio_web_oficial')),
    admin_email,
    CASE WHEN favicon_generated_at IS NOT NULL THEN 1 ELSE 0 END
FROM verumax_identi.identitas_instances
WHERE slug = 'sajur'
ON DUPLICATE KEY UPDATE
    identitas_usar_paleta_general = VALUES(identitas_usar_paleta_general),
    redes_sociales = VALUES(redes_sociales),
    sitio_web_oficial = VALUES(sitio_web_oficial),
    email_contacto = VALUES(email_contacto),
    favicon_generado = VALUES(favicon_generado),
    updated_at = CURRENT_TIMESTAMP;

-- PASO 4: Actualizar verumax_certifi.certificatum_config con datos de Certificatum
INSERT INTO verumax_certifi.certificatum_config (
    id_instancia,
    certificatum_usar_paleta_general,
    certificatum_modo,
    certificatum_titulo,
    certificatum_icono,
    certificatum_posicion,
    certificatum_descripcion,
    certificatum_cta_texto,
    certificatum_estadisticas,
    certificatum_mostrar_stats,
    certificatum_features
)
SELECT
    @id_sajur,
    1, -- Usar paleta general
    COALESCE(certificatum_modo, 'pagina'),
    COALESCE(certificatum_titulo, 'Certificados'),
    COALESCE(certificatum_icono, 'award'),
    COALESCE(certificatum_posicion, 99),
    certificatum_descripcion,
    certificatum_cta_texto,
    certificatum_estadisticas,
    COALESCE(certificatum_mostrar_stats, 1),
    JSON_OBJECT(
        'feature1', JSON_OBJECT(
            'titulo', 'Certificados Verificables',
            'descripcion', 'Con código QR de validación única y segura',
            'icono', 'shield-check'
        ),
        'feature2', JSON_OBJECT(
            'titulo', 'Acceso 24/7',
            'descripcion', 'Disponible en cualquier momento y desde cualquier lugar',
            'icono', 'clock'
        ),
        'feature3', JSON_OBJECT(
            'titulo', 'Descarga Inmediata',
            'descripcion', 'PDF de alta calidad listo para imprimir',
            'icono', 'download'
        )
    )
FROM verumax_identi.identitas_instances
WHERE slug = 'sajur'
ON DUPLICATE KEY UPDATE
    certificatum_usar_paleta_general = VALUES(certificatum_usar_paleta_general),
    certificatum_modo = VALUES(certificatum_modo),
    certificatum_titulo = VALUES(certificatum_titulo),
    certificatum_icono = VALUES(certificatum_icono),
    certificatum_posicion = VALUES(certificatum_posicion),
    certificatum_descripcion = VALUES(certificatum_descripcion),
    certificatum_cta_texto = VALUES(certificatum_cta_texto),
    certificatum_estadisticas = VALUES(certificatum_estadisticas),
    certificatum_mostrar_stats = VALUES(certificatum_mostrar_stats),
    certificatum_features = VALUES(certificatum_features),
    updated_at = CURRENT_TIMESTAMP;

-- PASO 5: Verificar que todo esté correcto
SELECT '=== VERIFICACIÓN: verumax_general.instances ===' as resultado;
SELECT id_instancia, slug, nombre, color_primario, identitas_activo, modulo_certificatum
FROM verumax_general.instances
WHERE slug = 'sajur';

SELECT '=== VERIFICACIÓN: verumax_identi.identitas_config ===' as resultado;
SELECT id_config, id_instancia, sitio_web_oficial, email_contacto
FROM verumax_identi.identitas_config
WHERE id_instancia = @id_sajur;

SELECT '=== VERIFICACIÓN: verumax_certifi.certificatum_config ===' as resultado;
SELECT id_config, id_instancia, certificatum_modo, certificatum_titulo,
       certificatum_descripcion, certificatum_features
FROM verumax_certifi.certificatum_config
WHERE id_instancia = @id_sajur;

-- ================================================================================
-- FIN - La migración está completa
-- ================================================================================
