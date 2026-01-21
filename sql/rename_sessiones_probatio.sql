-- =====================================================
-- Renombrar tabla sessiones_evaluatio a sessiones_probatio
-- Base de datos: verumax_academi
-- =====================================================

RENAME TABLE sessiones_evaluatio TO sessiones_probatio;

-- Verificar
SHOW TABLES LIKE 'sessiones%';
