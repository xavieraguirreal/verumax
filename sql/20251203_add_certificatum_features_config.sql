-- ================================================================================
-- AGREGAR CONFIGURACIÓN DE FICHAS/FEATURES DE CERTIFICATUM
-- Fecha: 03/12/2025 - 18:30
-- ================================================================================
--
-- Permite configurar las 3 fichas que se muestran en el portal de certificados
-- (Certificados Verificables, Acceso 24/7, Descarga Inmediata)
--
-- ================================================================================

-- IMPORTANTE: Selecciona la base de datos "verumax_identi" en phpMyAdmin antes de ejecutar
USE verumax_identi;

-- PASO 1: Agregar columna para configuración de features
-- =======================================================
-- NOTA: Si la columna ya existe, este ALTER dará error 1060 "Duplicate column".
--       Es normal, puedes ignorarlo y continuar con los siguientes pasos.

ALTER TABLE verumax_identi.identitas_instances
ADD COLUMN certificatum_features JSON DEFAULT NULL
    COMMENT 'Configuración de las 3 fichas que se muestran: {feature1: {titulo, descripcion, icono}, feature2, feature3}';

-- PASO 2: Configurar features para SAJuR
-- =======================================

UPDATE verumax_identi.identitas_instances
SET
    certificatum_features = JSON_OBJECT(
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
WHERE slug = 'sajur';

-- PASO 3: Asegurar que certificatum_descripcion esté configurado
-- ===============================================================

UPDATE verumax_identi.identitas_instances
SET
    certificatum_descripcion = 'Accede a tus certificados, constancias y registro académico completo ingresando tu DNI'
WHERE slug = 'sajur'
  AND (certificatum_descripcion IS NULL OR certificatum_descripcion = '');

-- PASO 4: Verificar configuración
-- ================================

SELECT
    slug,
    nombre,
    certificatum_descripcion,
    certificatum_features
FROM verumax_identi.identitas_instances
WHERE slug = 'sajur';

-- ================================================================================
-- ESTRUCTURA DEL JSON DE FEATURES
-- ================================================================================
--
-- {
--     "feature1": {
--         "titulo": "Certificados Verificables",
--         "descripcion": "Con código QR de validación única y segura",
--         "icono": "shield-check"
--     },
--     "feature2": {
--         "titulo": "Acceso 24/7",
--         "descripcion": "Disponible en cualquier momento y desde cualquier lugar",
--         "icono": "clock"
--     },
--     "feature3": {
--         "titulo": "Descarga Inmediata",
--         "descripcion": "PDF de alta calidad listo para imprimir",
--         "icono": "download"
--     }
-- }
--
-- ICONOS DISPONIBLES (Lucide Icons):
-- - shield-check, lock, award, verified
-- - clock, calendar, timer
-- - download, file-down, save
-- - file-text, file-check, file-badge
-- - user-check, users, graduation-cap
-- - check-circle, check-square
-- - search, eye, scan
--
-- ================================================================================
-- EJEMPLOS DE OTRAS CONFIGURACIONES
-- ================================================================================

-- Ejemplo para institución de idiomas:
-- UPDATE identitas_instances SET certificatum_features = JSON_OBJECT(
--     'feature1', JSON_OBJECT('titulo', 'Certificados Internacionales', 'descripcion', 'Reconocidos en 50+ países', 'icono', 'globe'),
--     'feature2', JSON_OBJECT('titulo', 'Niveles Verificados', 'descripcion', 'Según Marco Común Europeo', 'icono', 'award'),
--     'feature3', JSON_OBJECT('titulo', 'Digital y Físico', 'descripcion', 'Recibe ambos formatos', 'icono', 'file-badge')
-- ) WHERE slug = 'idiomas';

-- Ejemplo para institución técnica:
-- UPDATE identitas_instances SET certificatum_features = JSON_OBJECT(
--     'feature1', JSON_OBJECT('titulo', 'Certificación Técnica', 'descripcion', 'Avalada por colegios profesionales', 'icono', 'tool'),
--     'feature2', JSON_OBJECT('titulo', 'Portfolio Digital', 'descripcion', 'Comparte tus logros en LinkedIn', 'icono', 'briefcase'),
--     'feature3', JSON_OBJECT('titulo', 'Trayectoria Completa', 'descripcion', 'Historial de todos tus cursos', 'icono', 'timeline')
-- ) WHERE slug = 'tecnica';

-- ================================================================================
