-- Migración: Actualizar campo genero en miembros
-- Fecha: 2025-12-16
-- Descripción: Cambia valores cortos (M/F/Otro) a descriptivos e incluye opción "No binario"
-- Valores nuevos: Masculino, Femenino, No binario, Prefiero no especificar

-- Paso 1: Agregar columna temporal
ALTER TABLE verumax_nexus.miembros ADD COLUMN genero_nuevo VARCHAR(50) DEFAULT 'Prefiero no especificar';

-- Paso 2: Migrar datos existentes
UPDATE verumax_nexus.miembros SET genero_nuevo = 'Masculino' WHERE genero = 'M';
UPDATE verumax_nexus.miembros SET genero_nuevo = 'Femenino' WHERE genero = 'F';
UPDATE verumax_nexus.miembros SET genero_nuevo = 'No binario' WHERE genero = 'Otro';
UPDATE verumax_nexus.miembros SET genero_nuevo = 'Prefiero no especificar' WHERE genero = 'No especifica' OR genero IS NULL;

-- Paso 3: Eliminar columna vieja
ALTER TABLE verumax_nexus.miembros DROP COLUMN genero;

-- Paso 4: Renombrar columna nueva
ALTER TABLE verumax_nexus.miembros CHANGE COLUMN genero_nuevo genero
    ENUM('Masculino', 'Femenino', 'No binario', 'Prefiero no especificar')
    DEFAULT 'Prefiero no especificar';

-- Verificar resultado
SELECT genero, COUNT(*) as cantidad FROM verumax_nexus.miembros GROUP BY genero;
