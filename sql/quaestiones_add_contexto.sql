-- =====================================================
-- Agregar columna 'contexto' a quaestiones
-- Base de datos: verumax_academi
--
-- Cada pregunta ahora tiene:
--   - contexto: Situación o escenario (opcional)
--   - enunciado: La consigna/pregunta propiamente dicha
--
-- Cada opción en el JSON ahora incluye:
--   - feedback: Explicación de por qué es correcta/incorrecta
-- =====================================================

-- Agregar columna contexto después de enunciado
ALTER TABLE quaestiones
ADD COLUMN contexto TEXT NULL AFTER enunciado;

-- Verificar estructura
DESCRIBE quaestiones;
