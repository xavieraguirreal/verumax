-- =====================================================
-- Sistema CERTIFICATUM Multi-Tenant - VERUMax
-- Credenciales Verificadas / Certificados Infalsificables
-- Base de datos: verumax_certifi
-- Versión: 1.0.0
-- Fecha: 14/11/2025
-- =====================================================

-- Tabla de estudiantes (multi-tenant)
CREATE TABLE IF NOT EXISTS estudiantes (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    institucion VARCHAR(50) NOT NULL COMMENT 'sajur, liberte, etc.',
    dni VARCHAR(50) NOT NULL,
    nombre_completo VARCHAR(200) NOT NULL,
    email VARCHAR(150) NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_estudiante_dni (institucion, dni),
    INDEX idx_dni (dni),
    INDEX idx_institucion (institucion),
    INDEX idx_nombre (nombre_completo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Estudiantes registrados por institución';

-- Tabla de cursos (multi-tenant)
CREATE TABLE IF NOT EXISTS cursos (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    institucion VARCHAR(50) NOT NULL,
    codigo_curso VARCHAR(100) NOT NULL COMMENT 'Ej: SJ-DPA-2024',
    nombre_curso VARCHAR(300) NOT NULL,
    carga_horaria INT NULL,
    descripcion TEXT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,

    UNIQUE KEY unique_codigo_curso (institucion, codigo_curso),
    INDEX idx_institucion (institucion),
    INDEX idx_codigo (codigo_curso),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cursos/formaciones por institución';

-- Tabla de inscripciones (relación estudiante-curso)
CREATE TABLE IF NOT EXISTS inscripciones (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_curso INT NOT NULL,
    estado ENUM('Por Iniciar', 'En Curso', 'Finalizado', 'Aprobado', 'Desaprobado') DEFAULT 'Por Iniciar',
    fecha_inscripcion DATE NULL,
    fecha_inicio DATE NULL,
    fecha_finalizacion DATE NULL,
    nota_final DECIMAL(4,2) NULL COMMENT 'Nota de 0 a 10',
    asistencia VARCHAR(10) NULL COMMENT 'Ej: 98%',

    UNIQUE KEY unique_inscripcion (id_estudiante, id_curso),
    INDEX idx_estudiante (id_estudiante),
    INDEX idx_curso (id_curso),
    INDEX idx_estado (estado),

    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante) ON DELETE CASCADE,
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Inscripciones de estudiantes a cursos';

-- Tabla de competencias por curso
CREATE TABLE IF NOT EXISTS competencias_curso (
    id_competencia INT AUTO_INCREMENT PRIMARY KEY,
    id_inscripcion INT NOT NULL,
    competencia VARCHAR(200) NOT NULL,
    orden INT DEFAULT 0,

    INDEX idx_inscripcion (id_inscripcion),

    FOREIGN KEY (id_inscripcion) REFERENCES inscripciones(id_inscripcion) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Competencias adquiridas por estudiante en cada curso';

-- Tabla de trayectoria académica (timeline)
CREATE TABLE IF NOT EXISTS trayectoria (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    id_inscripcion INT NOT NULL,
    fecha DATE NOT NULL,
    evento VARCHAR(200) NOT NULL COMMENT 'Ej: Inscripción, Examen Parcial',
    detalle TEXT NULL COMMENT 'Información adicional del evento',
    orden INT DEFAULT 0,

    INDEX idx_inscripcion (id_inscripcion),
    INDEX idx_fecha (fecha),

    FOREIGN KEY (id_inscripcion) REFERENCES inscripciones(id_inscripcion) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Eventos de la trayectoria académica del estudiante';

-- Tabla de códigos de validación (para QR)
CREATE TABLE IF NOT EXISTS codigos_validacion (
    id_validacion INT AUTO_INCREMENT PRIMARY KEY,
    institucion VARCHAR(50) NOT NULL,
    dni VARCHAR(50) NOT NULL,
    codigo_curso VARCHAR(100) NOT NULL,
    codigo_validacion VARCHAR(50) NOT NULL COMMENT 'Ej: VALID-xxxxxxxxxxxxx',
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_documento ENUM('analitico', 'certificado_aprobacion', 'constancia_regular', 'constancia_finalizacion', 'constancia_inscripcion') DEFAULT 'certificado_aprobacion',
    veces_consultado INT DEFAULT 0,
    ultima_consulta TIMESTAMP NULL,

    UNIQUE KEY unique_codigo (codigo_validacion),
    INDEX idx_institucion_dni (institucion, dni),
    INDEX idx_codigo_curso (codigo_curso),
    INDEX idx_fecha (fecha_generacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Códigos de validación para certificados y documentos';

-- =====================================================
-- VISTAS ÚTILES
-- =====================================================

-- Vista completa de estudiantes con sus cursos
CREATE OR REPLACE VIEW vista_estudiantes_cursos AS
SELECT
    e.id_estudiante,
    e.institucion,
    e.dni,
    e.nombre_completo,
    e.email,
    c.id_curso,
    c.codigo_curso,
    c.nombre_curso,
    c.carga_horaria,
    i.id_inscripcion,
    i.estado,
    i.fecha_inscripcion,
    i.fecha_inicio,
    i.fecha_finalizacion,
    i.nota_final,
    i.asistencia
FROM estudiantes e
INNER JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
INNER JOIN cursos c ON i.id_curso = c.id_curso
WHERE c.activo = 1;

-- Vista de estadísticas por institución
CREATE OR REPLACE VIEW vista_estadisticas_institucion AS
SELECT
    e.institucion,
    COUNT(DISTINCT e.id_estudiante) as total_estudiantes,
    COUNT(DISTINCT c.id_curso) as total_cursos,
    COUNT(DISTINCT i.id_inscripcion) as total_inscripciones,
    SUM(CASE WHEN i.estado = 'Aprobado' THEN 1 ELSE 0 END) as cursos_aprobados,
    SUM(CASE WHEN i.estado = 'En Curso' THEN 1 ELSE 0 END) as cursos_en_curso
FROM estudiantes e
LEFT JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
LEFT JOIN cursos c ON i.id_curso = c.id_curso
GROUP BY e.institucion;

-- =====================================================
-- INDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================

-- Índice compuesto para búsquedas frecuentes
CREATE INDEX idx_estudiante_estado ON inscripciones(id_estudiante, estado);
CREATE INDEX idx_curso_estado ON inscripciones(id_curso, estado);

-- =====================================================
-- COMENTARIOS Y DOCUMENTACIÓN
-- =====================================================

-- Esta estructura permite:
-- 1. Multi-tenancy: Cada institución tiene sus datos aislados
-- 2. Escalabilidad: Agregar nuevas instituciones sin cambios estructurales
-- 3. Trazabilidad: Códigos de validación únicos por documento
-- 4. Flexibilidad: Estados y tipos de documento configurables
-- 5. Integridad: Foreign keys y unique constraints
-- 6. Performance: Índices en campos de búsqueda frecuente

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
