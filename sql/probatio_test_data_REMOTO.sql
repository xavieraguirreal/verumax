-- =====================================================
-- PROBATIO - Datos de Prueba para REMOTO
-- Ejecutar en servidor de producción
-- =====================================================

USE verumax_academi;

-- 1. Verificar/Crear inscripciones de estudiantes al curso
-- Inscribir a DIANA MÁRQUEZ (id_miembro=16) al curso 1
INSERT IGNORE INTO inscripciones (id_miembro, id_curso, estado, fecha_inscripcion)
VALUES (16, 1, 'Inscrito', CURDATE());

-- Inscribir a Javier Villarreal (id_miembro=15) al curso 1
INSERT IGNORE INTO inscripciones (id_miembro, id_curso, estado, fecha_inscripcion)
VALUES (15, 1, 'Inscrito', CURDATE());

-- Inscribir a JORGE AGUILAR (id_miembro=14) al curso 1
INSERT IGNORE INTO inscripciones (id_miembro, id_curso, estado, fecha_inscripcion)
VALUES (14, 1, 'Inscrito', CURDATE());

-- 2. Crear evaluación de prueba (vinculada al curso id=1 "Curso de JR")
INSERT INTO evaluationes (
    id_instancia,
    id_curso,
    codigo,
    nombre,
    descripcion,
    tipo,
    metodologia,
    requiere_cierre_cualitativo,
    texto_cierre_cualitativo,
    minimo_caracteres_cierre,
    mensaje_bienvenida,
    mensaje_finalizacion,
    estado
) VALUES (
    1,  -- SAJuR
    1,  -- Curso de JR (SA-CUR-2025-001)
    'EVAL-SAJUR-CORR-2025',
    'Evaluación Final - Diplomatura JR Corrientes 2025',
    'Evaluación de conocimientos sobre Justicia Restaurativa',
    'examen',
    'tradicional',
    TRUE,
    'Compartí una reflexión sobre lo aprendido en el curso:',
    50,
    '<p><strong>Bienvenido/a a la evaluación final.</strong></p><p>Esta evaluación consta de 3 preguntas de prueba. Leé cada situación con atención y seleccioná las respuestas que consideres correctas.</p>',
    '<p><strong>¡Felicitaciones!</strong></p><p>Has completado la evaluación. Tu participación ha sido registrada.</p>',
    'activa'
);

-- Obtener el ID de la evaluación recién creada
SET @eval_id = LAST_INSERT_ID();

-- 3. Crear las 3 preguntas de prueba

-- Pregunta 1: Multiple choice (una sola correcta)
INSERT INTO quaestiones (
    id_evaluatio,
    orden,
    tipo,
    enunciado,
    opciones,
    explicacion_correcta,
    explicacion_incorrecta
) VALUES (
    @eval_id,
    1,
    'multiple_choice',
    'En un proceso de Justicia Restaurativa, ¿cuál es el objetivo principal?',
    '[
        {"letra": "A", "texto": "Castigar al ofensor con la máxima pena posible", "es_correcta": false},
        {"letra": "B", "texto": "Reparar el daño causado y restaurar las relaciones", "es_correcta": true},
        {"letra": "C", "texto": "Evitar que el caso llegue a juicio para ahorrar recursos", "es_correcta": false},
        {"letra": "D", "texto": "Determinar quién tiene la razón en el conflicto", "es_correcta": false}
    ]',
    '¡Exacto! La Justicia Restaurativa se centra en reparar el daño y restaurar las relaciones entre las partes involucradas, no en el castigo.',
    'La Justicia Restaurativa no se enfoca en el castigo ni en determinar ganadores, sino en reparar el daño causado y restaurar las relaciones entre víctima, ofensor y comunidad.'
);

-- Pregunta 2: Multiple answer (varias correctas)
INSERT INTO quaestiones (
    id_evaluatio,
    orden,
    tipo,
    enunciado,
    opciones,
    explicacion_correcta,
    explicacion_incorrecta
) VALUES (
    @eval_id,
    2,
    'multiple_answer',
    '¿Cuáles de los siguientes son principios fundamentales de la Justicia Restaurativa? (Seleccioná todas las correctas)',
    '[
        {"letra": "A", "texto": "Participación voluntaria de las partes", "es_correcta": true},
        {"letra": "B", "texto": "Imposición de sanciones por un juez", "es_correcta": false},
        {"letra": "C", "texto": "Reparación del daño", "es_correcta": true},
        {"letra": "D", "texto": "Responsabilización activa del ofensor", "es_correcta": true},
        {"letra": "E", "texto": "Confidencialidad del proceso", "es_correcta": true}
    ]',
    '¡Muy bien! La voluntariedad, reparación, responsabilización y confidencialidad son pilares de la JR.',
    'Los principios fundamentales de la Justicia Restaurativa incluyen: participación voluntaria, reparación del daño, responsabilización activa del ofensor y confidencialidad. La imposición de sanciones por un juez corresponde al sistema penal tradicional.'
);

-- Pregunta 3: Multiple choice
INSERT INTO quaestiones (
    id_evaluatio,
    orden,
    tipo,
    enunciado,
    opciones,
    explicacion_correcta,
    explicacion_incorrecta
) VALUES (
    @eval_id,
    3,
    'multiple_choice',
    'Una familia sufre un robo en su casa. El joven responsable es identificado. ¿Cuál sería el enfoque restaurativo más apropiado?',
    '[
        {"letra": "A", "texto": "Enviar al joven directamente a prisión como escarmiento", "es_correcta": false},
        {"letra": "B", "texto": "Organizar un encuentro facilitado donde el joven escuche el impacto de sus acciones y acuerde una forma de reparación", "es_correcta": true},
        {"letra": "C", "texto": "Hacer que los padres del joven paguen todos los daños sin que él participe", "es_correcta": false},
        {"letra": "D", "texto": "Ignorar el incidente para no traumatizar al joven", "es_correcta": false}
    ]',
    '¡Correcto! El encuentro facilitado permite que el ofensor comprenda el impacto de sus acciones y participe activamente en la reparación.',
    'El enfoque restaurativo busca que el ofensor comprenda el daño causado y participe activamente en su reparación. Un encuentro facilitado permite esto, mientras que las otras opciones no promueven la responsabilización ni la reparación.'
);

-- 4. Verificar datos creados
SELECT '=== EVALUACIÓN CREADA ===' as info;
SELECT id_evaluatio, codigo, nombre, estado FROM evaluationes WHERE codigo = 'EVAL-SAJUR-CORR-2025';

SELECT '=== PREGUNTAS CREADAS ===' as info;
SELECT id_quaestio, orden, tipo, LEFT(enunciado, 50) as enunciado_preview
FROM quaestiones WHERE id_evaluatio = @eval_id ORDER BY orden;

SELECT '=== ESTUDIANTES INSCRIPTOS AL CURSO ===' as info;
SELECT m.identificador_principal as dni, m.nombre, m.apellido, i.estado
FROM inscripciones i
JOIN verumax_nexus.miembros m ON i.id_miembro = m.id_miembro
WHERE i.id_curso = 1;

SELECT '=== LISTO! Probá con estos DNIs ===' as info;
SELECT '18037645 (Diana Márquez)' as dni_prueba
UNION SELECT '21090770 (Javier Villarreal)'
UNION SELECT '21090771 (Jorge Aguilar)';
