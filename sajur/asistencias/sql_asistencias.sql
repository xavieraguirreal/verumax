-- =====================================================
-- Script SQL - Sistema de Asistencias SAJUR
-- Base de datos: sajurorg_formac
-- Fecha: 14/11/2025
-- =====================================================

-- Tabla de asistencias a formaciones
CREATE TABLE IF NOT EXISTS asistencias_formaciones (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    id_formacion INT NOT NULL,
    nombres VARCHAR(100) NOT NULL COMMENT 'Nombres en MAYÚSCULAS',
    apellidos VARCHAR(100) NOT NULL COMMENT 'Apellidos en MAYÚSCULAS',
    dni VARCHAR(50) NOT NULL COMMENT 'DNI o documento sin puntos',
    correo_electronico VARCHAR(150) NOT NULL,
    ip_registro VARCHAR(50) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Índices para búsquedas rápidas
    INDEX idx_formacion (id_formacion),
    INDEX idx_dni_formacion (dni, id_formacion),
    INDEX idx_fecha_registro (fecha_registro),

    -- Relación con formaciones
    FOREIGN KEY (id_formacion) REFERENCES formaciones(id_formacion) ON DELETE CASCADE,

    -- Constraint único: un DNI solo puede registrar asistencia UNA vez por formación
    UNIQUE KEY unique_asistencia (dni, id_formacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Datos de Ejemplo / Testing (OPCIONAL - comentar en producción)
-- =====================================================
/*
-- Ejemplo: Insertar asistencia de prueba
INSERT INTO asistencias_formaciones
    (id_formacion, nombres, apellidos, dni, correo_electronico, fecha_registro)
VALUES
    (1, 'JUAN CARLOS', 'PÉREZ GÓMEZ', '12345678', 'juan.perez@example.com', NOW());
*/

-- =====================================================
-- Consultas Útiles para Testing
-- =====================================================

-- Ver todas las asistencias con datos de la formación
/*
SELECT
    a.id_asistencia,
    a.nombres,
    a.apellidos,
    a.dni,
    a.correo_electronico,
    a.fecha_registro,
    f.nombre_formacion,
    f.codigo_formacion,
    f.fecha_inicio
FROM asistencias_formaciones a
INNER JOIN formaciones f ON a.id_formacion = f.id_formacion
ORDER BY a.fecha_registro DESC;
*/

-- Contar asistencias por formación
/*
SELECT
    f.nombre_formacion,
    f.codigo_formacion,
    COUNT(a.id_asistencia) as total_asistencias
FROM formaciones f
LEFT JOIN asistencias_formaciones a ON f.id_formacion = a.id_formacion
GROUP BY f.id_formacion
ORDER BY total_asistencias DESC;
*/

-- Verificar si un DNI ya registró asistencia para una formación específica
/*
SELECT * FROM asistencias_formaciones
WHERE dni = '12345678' AND id_formacion = 1;
*/

-- =====================================================
-- Notas Importantes
-- =====================================================
-- 1. La tabla usa UNIQUE KEY (dni, id_formacion) para evitar duplicados
-- 2. Los nombres y apellidos se guardan en MAYÚSCULAS para certificados
-- 3. El DNI se guarda sin puntos ni espacios
-- 4. La relación con formaciones tiene ON DELETE CASCADE
-- 5. Los índices optimizan las búsquedas frecuentes
