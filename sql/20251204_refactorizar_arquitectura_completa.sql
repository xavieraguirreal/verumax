-- ================================================================================
-- REFACTORIZACIÓN COMPLETA DE ARQUITECTURA MULTI-TENANT
-- Fecha: 04/12/2025
-- ================================================================================
--
-- OBJETIVO: Separar correctamente la configuración en 3 bases de datos:
-- - verumax_general: Configuración general del cliente
-- - verumax_identi: Configuración específica de Identitas
-- - verumax_certifi: Configuración específica de Certificatum
--
-- ================================================================================

-- ================================================================================
-- PASO 1: AGREGAR COLUMNA certificatum_features A certificatum_config
-- ================================================================================

ALTER TABLE verumax_certifi.certificatum_config
ADD COLUMN certificatum_features JSON DEFAULT NULL
    COMMENT 'Configuración de las 3 fichas que se muestran: {feature1: {titulo, descripcion, icono}, feature2, feature3}'
AFTER certificatum_mostrar_stats;

-- ================================================================================
-- PASO 2: ACTUALIZAR verumax_general.instances CON DATOS DE SAJUR
-- ================================================================================

-- Verificar si SAJuR ya existe
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
    certificatum_activo = 1,
    updated_at = CURRENT_TIMESTAMP
WHERE slug = 'sajur';

-- Si no existe, insertarlo
INSERT INTO verumax_general.instances (
    slug,
    nombre,
    nombre_completo,
    logo_url,
    color_primario,
    color_secundario,
    color_acento,
    paleta_colores,
    identitas_activo,
    certificatum_activo
)
SELECT 'sajur', 'SAJuR', 'Sociedad Argentina de Justicia Restaurativa',
       '/identitas/assets/logo-sajur.png', '#692f7f', '#ad5425', '#fdff99',
       'verde-elegante', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM verumax_general.instances WHERE slug = 'sajur');

-- ================================================================================
-- PASO 3: ACTUALIZAR verumax_identi.identitas_config CON DATOS DE SAJUR
-- ================================================================================

-- Obtener el id_instancia de SAJuR
SET @id_sajur = (SELECT id_instancia FROM verumax_general.instances WHERE slug = 'sajur');

-- Actualizar o insertar configuración de Identitas
INSERT INTO verumax_identi.identitas_config (
    id_instancia,
    identitas_usar_paleta_general,
    identitas_paleta_colores_propia,
    identitas_color_primario_propio,
    identitas_color_secundario_propio,
    identitas_color_acento_propio,
    redes_sociales,
    sitio_web_oficial,
    email_contacto,
    mision,
    favicon_generado
)
SELECT
    @id_sajur,
    COALESCE(identitas_usar_paleta_general, 1),
    identitas_paleta_colores_propia,
    identitas_color_primario_propio,
    identitas_color_secundario_propio,
    identitas_color_acento_propio,
    redes_sociales,
    JSON_UNQUOTE(JSON_EXTRACT(configuracion, '$.sitio_web_oficial')),
    admin_email,
    JSON_UNQUOTE(JSON_EXTRACT(configuracion, '$.mision')),
    CASE WHEN favicon_generated_at IS NOT NULL THEN 1 ELSE 0 END
FROM verumax_identi.identitas_instances
WHERE slug = 'sajur'
ON DUPLICATE KEY UPDATE
    identitas_usar_paleta_general = VALUES(identitas_usar_paleta_general),
    identitas_paleta_colores_propia = VALUES(identitas_paleta_colores_propia),
    identitas_color_primario_propio = VALUES(identitas_color_primario_propio),
    identitas_color_secundario_propio = VALUES(identitas_color_secundario_propio),
    identitas_color_acento_propio = VALUES(identitas_color_acento_propio),
    redes_sociales = VALUES(redes_sociales),
    sitio_web_oficial = VALUES(sitio_web_oficial),
    email_contacto = VALUES(email_contacto),
    mision = VALUES(mision),
    favicon_generado = VALUES(favicon_generado),
    updated_at = CURRENT_TIMESTAMP;

-- ================================================================================
-- PASO 4: ACTUALIZAR verumax_certifi.certificatum_config CON DATOS DE SAJUR
-- ================================================================================

-- Actualizar o insertar configuración de Certificatum
INSERT INTO verumax_certifi.certificatum_config (
    id_instancia,
    certificatum_usar_paleta_general,
    certificatum_paleta_colores_propia,
    certificatum_color_primario_propio,
    certificatum_color_secundario_propio,
    certificatum_color_acento_propio,
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
    COALESCE(certificatum_usar_paleta_general, 1),
    certificatum_paleta_colores_propia,
    certificatum_color_primario_propio,
    certificatum_color_secundario_propio,
    certificatum_color_acento_propio,
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
    certificatum_paleta_colores_propia = VALUES(certificatum_paleta_colores_propia),
    certificatum_color_primario_propio = VALUES(certificatum_color_primario_propio),
    certificatum_color_secundario_propio = VALUES(certificatum_color_secundario_propio),
    certificatum_color_acento_propio = VALUES(certificatum_color_acento_propio),
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

-- ================================================================================
-- PASO 5: VERIFICAR QUE TODO ESTÉ CORRECTO
-- ================================================================================

-- Verificar verumax_general.instances
SELECT 'VERIFICACION: verumax_general.instances' as tabla;
SELECT id_instancia, slug, nombre, color_primario, identitas_activo, certificatum_activo
FROM verumax_general.instances
WHERE slug = 'sajur';

-- Verificar verumax_identi.identitas_config
SELECT 'VERIFICACION: verumax_identi.identitas_config' as tabla;
SELECT id_config, id_instancia, identitas_usar_paleta_general, sitio_web_oficial, email_contacto
FROM verumax_identi.identitas_config
WHERE id_instancia = @id_sajur;

-- Verificar verumax_certifi.certificatum_config
SELECT 'VERIFICACION: verumax_certifi.certificatum_config' as tabla;
SELECT id_config, id_instancia, certificatum_modo, certificatum_titulo, certificatum_descripcion,
       certificatum_features
FROM verumax_certifi.certificatum_config
WHERE id_instancia = @id_sajur;

-- ================================================================================
-- FIN DEL SCRIPT
-- ================================================================================
-- IMPORTANTE: Después de ejecutar esto, hay que actualizar el código PHP
-- para que lea de las 3 tablas en lugar de identitas_instances
-- ================================================================================
