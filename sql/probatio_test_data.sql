-- =====================================================
-- PROBATIO - Datos de Prueba
-- Evaluación de prueba para SAJuR
-- =====================================================

USE verumax_academi;

-- Crear evaluación de prueba
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
    8,  -- Curso de JR (SJ-CR-0111)
    'EVAL-SAJUR-CORR-2025',
    'Evaluación Final - Diplomatura JR Corrientes 2025',
    'Evaluación de conocimientos sobre Justicia Restaurativa',
    'examen',
    'tradicional',  -- Siempre avanza, muestra feedback
    TRUE,
    'Compartí una reflexión sobre lo aprendido en el curso:',
    50,
    '<p><strong>Bienvenido/a a la evaluación final.</strong></p><p>Esta evaluación consta de 3 preguntas de prueba. Leé cada situación con atención y seleccioná las respuestas que consideres correctas.</p>',
    '<p><strong>¡Felicitaciones!</strong></p><p>Has completado la evaluación. Tu participación ha sido registrada.</p>',
    'activa'
);

-- Obtener el ID de la evaluación recién creada
SET @eval_id = LAST_INSERT_ID();

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

-- Verificar datos creados
SELECT 'Evaluación creada:' as info;
SELECT id_evaluatio, codigo, nombre, estado FROM evaluationes WHERE codigo = 'EVAL-SAJUR-CORR-2025';

SELECT 'Preguntas creadas:' as info;
SELECT id_quaestio, orden, tipo, LEFT(enunciado, 60) as enunciado_preview FROM quaestiones WHERE id_evaluatio = @eval_id ORDER BY orden;
