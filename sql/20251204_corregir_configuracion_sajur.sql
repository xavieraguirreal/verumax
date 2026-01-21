-- ================================================================================
-- CORRECCIÓN DE CONFIGURACIÓN - SAJUR
-- Fecha: 04/12/2025
-- ================================================================================
--
-- IMPORTANTE: Este SQL asume que SAJuR no tiene logo propio todavía.
-- Puedes modificarlo después cuando tengas el logo real.
--
-- ================================================================================

-- OPCIÓN A: Sin logo (usa iniciales)
-- ================================================================================
UPDATE verumax_general.instances
SET
    logo_url = NULL,  -- NULL hace que se muestren las iniciales en un círculo
    nombre = 'SAJuR',
    nombre_completo = 'Sociedad Argentina de Justicia Restaurativa',
    color_primario = '#692f7f',
    color_secundario = '#ad5425',
    color_acento = '#fdff99',
    paleta_colores = 'verde-elegante',
    identitas_activo = 1,
    modulo_certificatum = 1,
    activo = 1,
    fecha_actualizacion = CURRENT_TIMESTAMP
WHERE slug = 'sajur';

-- OPCIÓN B: Usar el logo de VERUMax temporalmente (descomenta si quieres usar esto)
-- ================================================================================
-- UPDATE verumax_general.instances
-- SET
--     logo_url = '/assets/images/logo-verumax-escudo.png',
--     nombre = 'SAJuR',
--     nombre_completo = 'Sociedad Argentina de Justicia Restaurativa',
--     color_primario = '#692f7f',
--     color_secundario = '#ad5425',
--     color_acento = '#fdff99',
--     paleta_colores = 'verde-elegante',
--     identitas_activo = 1,
--     modulo_certificatum = 1,
--     activo = 1,
--     fecha_actualizacion = CURRENT_TIMESTAMP
-- WHERE slug = 'sajur';

-- ================================================================================
-- VERIFICACIÓN
-- ================================================================================
SELECT
    '✓ Configuración actualizada' as resultado,
    slug,
    nombre,
    logo_url,
    identitas_activo,
    modulo_certificatum
FROM verumax_general.instances
WHERE slug = 'sajur';

-- ================================================================================
-- NOTA: Si tienes un logo de SAJuR:
-- ================================================================================
-- 1. Sube el logo a: E:\appVerumax\identitas\assets\logo-sajur.png
-- 2. Luego ejecuta:
--
-- UPDATE verumax_general.instances
-- SET logo_url = '/identitas/assets/logo-sajur.png'
-- WHERE slug = 'sajur';
--
-- ================================================================================
