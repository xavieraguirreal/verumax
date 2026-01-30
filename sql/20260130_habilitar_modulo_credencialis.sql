-- ============================================================================
-- MIGRACIÓN: Habilitar módulo Credencialis en el admin
-- Fecha: 2026-01-30
-- Descripción: Agrega columna modulo_credencialis y habilita para SAJuR
-- ============================================================================

-- ============================================================================
-- 1. AGREGAR COLUMNA modulo_credencialis A INSTANCES
-- ============================================================================

-- Agregar columna para habilitar/deshabilitar el módulo Credencialis
ALTER TABLE verumax_general.instances
ADD COLUMN modulo_credencialis TINYINT(1) DEFAULT 0
COMMENT 'Habilita módulo Credencialis (credenciales de membresía)';

-- ============================================================================
-- 2. HABILITAR CREDENCIALIS PARA SAJUR
-- ============================================================================

UPDATE verumax_general.instances
SET modulo_credencialis = 1
WHERE slug = 'sajur';

-- Configuración inicial de credenciales para SAJuR
UPDATE verumax_general.instances
SET credencial_config = JSON_OBJECT(
    'texto_superior', 'CREDENCIAL DE SOCIO',
    'texto_inferior', '',
    'mostrar_foto', false,
    'template_url', null
)
WHERE slug = 'sajur'
AND (credencial_config IS NULL OR credencial_config = '{}');

-- ============================================================================
-- 3. VERIFICACIÓN
-- ============================================================================

-- Verificar que la columna se agregó
SELECT
    slug,
    nombre,
    modulo_credencialis,
    credencial_config
FROM verumax_general.instances
WHERE slug = 'sajur';

-- Ver estructura de la tabla
DESCRIBE verumax_general.instances;
