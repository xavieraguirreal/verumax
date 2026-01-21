-- ================================================================================
-- VERIFICAR ESTRUCTURA ACTUAL DE LAS BASES DE DATOS
-- Fecha: 04/12/2025
-- ================================================================================
--
-- Este script verifica qué tablas existen y qué estructura tienen
-- para planificar la migración correctamente
--
-- ================================================================================

-- PASO 1: Verificar tablas en verumax_general
-- =============================================
SELECT 'BASE DE DATOS: verumax_general' as info;
SELECT TABLE_NAME, TABLE_ROWS, CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'verumax_general'
ORDER BY TABLE_NAME;

-- PASO 2: Verificar tablas en verumax_identi
-- ===========================================
SELECT 'BASE DE DATOS: verumax_identi' as info;
SELECT TABLE_NAME, TABLE_ROWS, CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'verumax_identi'
ORDER BY TABLE_NAME;

-- PASO 3: Verificar tablas en verumax_certifi
-- ============================================
SELECT 'BASE DE DATOS: verumax_certifi' as info;
SELECT TABLE_NAME, TABLE_ROWS, CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'verumax_certifi'
ORDER BY TABLE_NAME;

-- PASO 4: Ver estructura de identitas_instances (si existe)
-- ==========================================================
SELECT 'ESTRUCTURA DE identitas_instances' as info;
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'verumax_identi'
  AND TABLE_NAME = 'identitas_instances'
ORDER BY ORDINAL_POSITION;

-- PASO 5: Ver datos actuales de SAJuR
-- ====================================
SELECT 'DATOS ACTUALES DE SAJUR' as info;
SELECT *
FROM verumax_identi.identitas_instances
WHERE slug = 'sajur';

-- ================================================================================
-- EJECUTA ESTE SCRIPT Y COPIA TODO EL RESULTADO
-- Lo necesitamos para crear el plan de migración correcto
-- ================================================================================
