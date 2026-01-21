-- =====================================================
-- MIGRACIÓN: Limpieza de datos de prueba - SAJuR
-- Fecha: 2025-12-22
-- Descripción: Elimina estudiantes, docentes, cursos (excepto SA-CUR-2025-009),
--              inscripciones y la evaluación EVAL-SAJUR-CORR-2025
-- =====================================================

-- SAJuR tiene id_instancia = 1
SET @id_sajur = 1;

-- Verificar
SELECT @id_sajur AS 'ID Instancia SAJuR';

-- =====================================================
-- 1. ELIMINAR EVALUACIÓN EVAL-SAJUR-CORR-2025
-- =====================================================

-- Obtener el ID de la evaluación
SET @id_eval = (SELECT id_evaluatio FROM verumax_academi.evaluationes WHERE codigo = 'EVAL-SAJUR-CORR-2025');

-- Eliminar respuestas de las sesiones de esa evaluación
DELETE r FROM verumax_academi.responsa r
INNER JOIN verumax_academi.sessiones_probatio s ON r.id_sessio = s.id_sessio
WHERE s.id_evaluatio = @id_eval;

-- Eliminar sesiones de la evaluación
DELETE FROM verumax_academi.sessiones_probatio WHERE id_evaluatio = @id_eval;

-- Eliminar preguntas de la evaluación (las opciones están en JSON dentro de quaestiones)
DELETE FROM verumax_academi.quaestiones WHERE id_evaluatio = @id_eval;

-- Eliminar la evaluación
DELETE FROM verumax_academi.evaluationes WHERE codigo = 'EVAL-SAJUR-CORR-2025';

-- =====================================================
-- 2. ELIMINAR INSCRIPCIONES
-- =====================================================
DELETE FROM verumax_academi.inscripciones WHERE id_instancia = @id_sajur;

-- =====================================================
-- 3. ELIMINAR PARTICIPACIONES DE DOCENTES
-- =====================================================
DELETE FROM verumax_certifi.participaciones_docente WHERE id_instancia = @id_sajur;

-- =====================================================
-- 4. ELIMINAR CURSOS (excepto SA-CUR-2025-009)
-- =====================================================
DELETE FROM verumax_academi.cursos
WHERE id_instancia = @id_sajur
AND codigo_curso != 'SA-CUR-2025-009';

-- =====================================================
-- 5. ELIMINAR ESTUDIANTES
-- =====================================================
DELETE FROM verumax_nexus.miembros
WHERE id_instancia = @id_sajur
AND tipo_miembro = 'Estudiante';

-- =====================================================
-- 6. ELIMINAR DOCENTES
-- =====================================================
DELETE FROM verumax_nexus.miembros
WHERE id_instancia = @id_sajur
AND tipo_miembro = 'Docente';

-- =====================================================
-- VERIFICACIÓN
-- =====================================================
SELECT 'Estudiantes restantes' AS Entidad, COUNT(*) AS Cantidad FROM verumax_nexus.miembros WHERE id_instancia = @id_sajur AND tipo_miembro = 'Estudiante'
UNION ALL
SELECT 'Docentes restantes', COUNT(*) FROM verumax_nexus.miembros WHERE id_instancia = @id_sajur AND tipo_miembro = 'Docente'
UNION ALL
SELECT 'Cursos restantes', COUNT(*) FROM verumax_academi.cursos WHERE id_instancia = @id_sajur
UNION ALL
SELECT 'Inscripciones restantes', COUNT(*) FROM verumax_academi.inscripciones WHERE id_instancia = @id_sajur
UNION ALL
SELECT 'Evaluaciones restantes', COUNT(*) FROM verumax_academi.evaluationes WHERE id_instancia = @id_sajur;
