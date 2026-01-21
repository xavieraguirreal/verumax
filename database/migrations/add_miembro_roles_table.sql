-- =====================================================
-- MIGRACIÓN: Sistema de roles para miembros
-- Fecha: 2025-12-16
-- Descripción: Reemplaza el campo tipo_miembro por una
--              tabla de roles que permite múltiples roles
--              por miembro (Estudiante+Docente+Socio, etc.)
-- =====================================================

USE verumax_nexus;

-- 1. Crear tabla de roles de miembros
CREATE TABLE IF NOT EXISTS miembro_roles (
    id_miembro_rol INT PRIMARY KEY AUTO_INCREMENT,
    id_miembro INT NOT NULL,
    id_instancia INT NOT NULL,
    rol ENUM('Estudiante','Docente','Socio','Cliente','Empleado','Paciente','Otro') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_desde DATE DEFAULT (CURRENT_DATE),
    fecha_hasta DATE NULL,
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_miembro) REFERENCES miembros(id_miembro) ON DELETE CASCADE,
    UNIQUE KEY uk_miembro_rol_instancia (id_miembro, rol, id_instancia),
    INDEX idx_instancia_rol (id_instancia, rol, activo),
    INDEX idx_miembro (id_miembro, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrar datos existentes del campo tipo_miembro a la nueva tabla
-- Para registros con tipo_miembro = 'Estudiante'
INSERT INTO miembro_roles (id_miembro, id_instancia, rol, activo, fecha_desde)
SELECT id_miembro, id_instancia, 'Estudiante', TRUE, DATE(fecha_alta)
FROM miembros
WHERE tipo_miembro IN ('Estudiante', 'ambos')
ON DUPLICATE KEY UPDATE activo = TRUE;

-- Para registros con tipo_miembro = 'Docente'
INSERT INTO miembro_roles (id_miembro, id_instancia, rol, activo, fecha_desde)
SELECT id_miembro, id_instancia, 'Docente', TRUE, DATE(fecha_alta)
FROM miembros
WHERE tipo_miembro IN ('Docente', 'ambos')
ON DUPLICATE KEY UPDATE activo = TRUE;

-- Para registros con tipo_miembro = 'Socio'
INSERT INTO miembro_roles (id_miembro, id_instancia, rol, activo, fecha_desde)
SELECT id_miembro, id_instancia, 'Socio', TRUE, DATE(fecha_alta)
FROM miembros
WHERE tipo_miembro = 'Socio'
ON DUPLICATE KEY UPDATE activo = TRUE;

-- Para registros con tipo_miembro = 'Cliente'
INSERT INTO miembro_roles (id_miembro, id_instancia, rol, activo, fecha_desde)
SELECT id_miembro, id_instancia, 'Cliente', TRUE, DATE(fecha_alta)
FROM miembros
WHERE tipo_miembro = 'Cliente'
ON DUPLICATE KEY UPDATE activo = TRUE;

-- Para registros con tipo_miembro = 'Otro'
INSERT INTO miembro_roles (id_miembro, id_instancia, rol, activo, fecha_desde)
SELECT id_miembro, id_instancia, 'Otro', TRUE, DATE(fecha_alta)
FROM miembros
WHERE tipo_miembro = 'Otro'
ON DUPLICATE KEY UPDATE activo = TRUE;

-- 3. Verificar migración
SELECT 'Resumen de migración:' as info;
SELECT rol, COUNT(*) as cantidad FROM miembro_roles GROUP BY rol;

-- NOTA: El campo tipo_miembro en la tabla miembros queda deprecated
-- pero no lo eliminamos para mantener compatibilidad temporal.
-- En una futura migración se puede eliminar con:
-- ALTER TABLE miembros DROP COLUMN tipo_miembro;
