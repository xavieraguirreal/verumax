-- =====================================================
-- PROBATIO - Sistema de Evaluaciones (Módulo de Academicus)
-- Tablas para verumax_academi
-- Fecha: 2025-12-20
-- =====================================================

USE verumax_academi;

-- =====================================================
-- TABLA: evaluationes (Evaluaciones/Exámenes)
-- =====================================================
CREATE TABLE IF NOT EXISTS evaluationes (
    id_evaluatio INT PRIMARY KEY AUTO_INCREMENT,
    id_instancia INT NOT NULL,                  -- FK a instancia (SAJuR=1, etc.)
    id_curso INT DEFAULT NULL,                  -- FK a cursos (opcional)
    id_cohorte INT DEFAULT NULL,                -- FK a cohortes (opcional)
    codigo VARCHAR(50) UNIQUE,                  -- Ej: 'EVAL-SAJUR-CORR-2025'
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,

    -- Tipo y metodología
    tipo ENUM('examen', 'quiz', 'encuesta', 'autoevaluacion') DEFAULT 'examen',
    metodologia ENUM('afirmacion', 'tradicional', 'adaptive') DEFAULT 'tradicional',
    -- afirmacion: no avanza hasta acertar
    -- tradicional: siempre avanza, muestra feedback
    -- adaptive: ajusta dificultad según respuestas

    -- Configuración
    requiere_aprobacion_previa BOOLEAN DEFAULT FALSE,
    permite_multiples_intentos BOOLEAN DEFAULT TRUE,
    muestra_respuestas_correctas BOOLEAN DEFAULT FALSE,
    requiere_cierre_cualitativo BOOLEAN DEFAULT FALSE,
    texto_cierre_cualitativo TEXT,
    minimo_caracteres_cierre INT DEFAULT 50,

    -- Mensajes personalizados
    mensaje_bienvenida TEXT,
    mensaje_finalizacion TEXT,
    mensaje_error_no_inscripto TEXT,

    -- Estado y disponibilidad
    estado ENUM('borrador', 'activa', 'cerrada', 'archivada') DEFAULT 'borrador',
    fecha_inicio DATETIME DEFAULT NULL,
    fecha_fin DATETIME DEFAULT NULL,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,

    -- Índices
    INDEX idx_instancia (id_instancia),
    INDEX idx_curso (id_curso),
    INDEX idx_cohorte (id_cohorte),
    INDEX idx_estado (estado),
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: quaestiones (Preguntas)
-- =====================================================
CREATE TABLE IF NOT EXISTS quaestiones (
    id_quaestio INT PRIMARY KEY AUTO_INCREMENT,
    id_evaluatio INT NOT NULL,                  -- FK a evaluationes
    orden INT NOT NULL DEFAULT 1,

    -- Tipo de pregunta
    tipo ENUM('multiple_choice', 'multiple_answer', 'verdadero_falso', 'abierta') DEFAULT 'multiple_answer',
    -- multiple_choice: una sola respuesta correcta (radio)
    -- multiple_answer: múltiples respuestas correctas (checkbox)
    -- verdadero_falso: solo V/F
    -- abierta: texto libre

    -- Contenido
    enunciado TEXT NOT NULL,
    opciones JSON DEFAULT NULL,                 -- [{"letra":"A","texto":"...","es_correcta":true}, ...]

    -- Feedback pedagógico
    explicacion_correcta TEXT,                  -- Se muestra cuando acierta (ampliación)
    explicacion_incorrecta TEXT,                -- Se muestra cuando falla (refuerzo)

    -- Configuración
    puntos INT DEFAULT 1,
    es_obligatoria BOOLEAN DEFAULT TRUE,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices y FK
    INDEX idx_evaluatio (id_evaluatio),
    INDEX idx_orden (id_evaluatio, orden),
    CONSTRAINT fk_quaestio_evaluatio
        FOREIGN KEY (id_evaluatio) REFERENCES evaluationes(id_evaluatio) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: sessiones_evaluatio (Sesiones de estudiantes)
-- =====================================================
CREATE TABLE IF NOT EXISTS sessiones_evaluatio (
    id_sessio INT PRIMARY KEY AUTO_INCREMENT,
    id_evaluatio INT NOT NULL,
    id_miembro INT NOT NULL,                    -- FK a verumax_nexus.miembros
    id_inscripcion INT DEFAULT NULL,            -- FK a inscripciones (si aplica)

    -- Progreso
    pregunta_actual INT DEFAULT 1,
    preguntas_completadas INT DEFAULT 0,
    total_preguntas INT DEFAULT NULL,
    progreso_json JSON DEFAULT NULL,            -- Estado detallado por pregunta

    -- Resultado
    estado ENUM('iniciada', 'en_progreso', 'completada', 'abandonada') DEFAULT 'iniciada',
    puntaje_obtenido DECIMAL(5,2) DEFAULT NULL,
    puntaje_maximo DECIMAL(5,2) DEFAULT NULL,
    porcentaje DECIMAL(5,2) DEFAULT NULL,
    aprobado BOOLEAN DEFAULT NULL,
    reflexion_final TEXT,                       -- Cierre cualitativo

    -- Auditoría
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_finalizacion TIMESTAMP NULL DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,

    -- Índices y constraints
    UNIQUE KEY unique_estudiante_evaluacion (id_evaluatio, id_miembro),
    INDEX idx_evaluatio (id_evaluatio),
    INDEX idx_miembro (id_miembro),
    INDEX idx_estado (estado),
    INDEX idx_inscripcion (id_inscripcion),
    CONSTRAINT fk_sessio_evaluatio
        FOREIGN KEY (id_evaluatio) REFERENCES evaluationes(id_evaluatio) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: responsa (Respuestas/Intentos)
-- =====================================================
CREATE TABLE IF NOT EXISTS responsa (
    id_responsum INT PRIMARY KEY AUTO_INCREMENT,
    id_sessio INT NOT NULL,
    id_quaestio INT NOT NULL,
    intento_numero INT DEFAULT 1,

    -- Respuesta
    respuestas_seleccionadas JSON DEFAULT NULL, -- ["A", "C"] para múltiple respuesta
    respuesta_texto TEXT DEFAULT NULL,          -- Para preguntas abiertas

    -- Resultado
    es_correcta BOOLEAN DEFAULT NULL,
    puntos_obtenidos DECIMAL(5,2) DEFAULT 0,

    -- Timing
    tiempo_respuesta_segundos INT DEFAULT NULL,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Índices y FK
    INDEX idx_sessio (id_sessio),
    INDEX idx_quaestio (id_quaestio),
    INDEX idx_sessio_quaestio (id_sessio, id_quaestio),
    CONSTRAINT fk_responsum_sessio
        FOREIGN KEY (id_sessio) REFERENCES sessiones_evaluatio(id_sessio) ON DELETE CASCADE,
    CONSTRAINT fk_responsum_quaestio
        FOREIGN KEY (id_quaestio) REFERENCES quaestiones(id_quaestio) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VISTA: v_estadisticas_evaluationes
-- =====================================================
CREATE OR REPLACE VIEW v_estadisticas_evaluationes AS
SELECT
    e.id_evaluatio,
    e.id_instancia,
    e.codigo,
    e.nombre,
    e.estado,
    e.id_curso,
    (SELECT COUNT(*) FROM quaestiones q WHERE q.id_evaluatio = e.id_evaluatio) as total_preguntas,
    COUNT(DISTINCT s.id_sessio) as total_sesiones,
    COUNT(DISTINCT CASE WHEN s.estado = 'completada' THEN s.id_sessio END) as sesiones_completadas,
    COUNT(DISTINCT CASE WHEN s.aprobado = 1 THEN s.id_sessio END) as sesiones_aprobadas,
    ROUND(AVG(CASE WHEN s.estado = 'completada' THEN s.porcentaje END), 2) as promedio_porcentaje,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, s.fecha_inicio, s.fecha_finalizacion)), 1) as promedio_minutos
FROM evaluationes e
LEFT JOIN sessiones_evaluatio s ON e.id_evaluatio = s.id_evaluatio
GROUP BY e.id_evaluatio;

-- =====================================================
-- VISTA: v_estadisticas_quaestiones (por pregunta)
-- =====================================================
CREATE OR REPLACE VIEW v_estadisticas_quaestiones AS
SELECT
    q.id_quaestio,
    q.id_evaluatio,
    q.orden,
    LEFT(q.enunciado, 100) as enunciado_preview,
    COUNT(DISTINCT r.id_sessio) as total_intentos_estudiantes,
    COUNT(r.id_responsum) as total_respuestas,
    ROUND(AVG(r.intento_numero), 2) as promedio_intentos,
    SUM(CASE WHEN r.es_correcta = 1 THEN 1 ELSE 0 END) as respuestas_correctas,
    ROUND(
        SUM(CASE WHEN r.es_correcta = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(r.id_responsum), 0),
        1
    ) as tasa_acierto_porcentaje
FROM quaestiones q
LEFT JOIN responsa r ON q.id_quaestio = r.id_quaestio
GROUP BY q.id_quaestio;

-- =====================================================
-- DATOS DE EJEMPLO (comentado - descomentar si se necesita)
-- =====================================================
/*
-- Ejemplo: Crear evaluación SAJuR Corrientes 2025
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
    mensaje_bienvenida,
    mensaje_finalizacion,
    estado
) VALUES (
    1,
    NULL,  -- Actualizar con id_curso real
    'EVAL-SAJUR-CORR-2025',
    'Evaluación Final - Diplomatura JR Corrientes 2025',
    'Evaluación de conocimientos para certificación de la Diplomatura en Justicia Restaurativa',
    'examen',
    'tradicional',
    TRUE,
    'Comparte una reflexión, consulta o comentario sobre el curso:',
    '<p>Bienvenido/a a la evaluación final de la Diplomatura en Justicia Restaurativa.</p><p>Esta evaluación consta de 10 situaciones problemáticas. Lee cada una con atención y selecciona la o las respuestas que consideres correctas.</p>',
    '<p>¡Felicitaciones! Has completado la evaluación.</p><p>Tu participación ha sido registrada exitosamente.</p>',
    'borrador'
);
*/

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
SELECT 'Tablas de Probatio creadas exitosamente en verumax_academi' as resultado;
