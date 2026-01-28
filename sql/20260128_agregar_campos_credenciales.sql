-- ============================================================================
-- MIGRACIÓN: Agregar campos para credenciales de socios/miembros
-- Fecha: 2026-01-28
-- Descripción: Permite emitir credenciales desde Certificatum (nuevo genus)
-- ============================================================================

-- ============================================================================
-- 1. CAMPOS EN MIEMBROS (verumax_nexus)
-- ============================================================================

-- Campos para socios de mutuales, clubes, cooperativas, etc.
ALTER TABLE verumax_nexus.miembros
ADD COLUMN numero_asociado VARCHAR(50) NULL COMMENT 'Número de socio/asociado (propio o de entidad)',
ADD COLUMN tipo_asociado VARCHAR(50) NULL COMMENT 'Tipo: TITULAR, ADHERENTE, INST., EMPRESA, etc.',
ADD COLUMN nombre_entidad VARCHAR(200) NULL COMMENT 'Nombre de entidad si el socio pertenece a una (ej: Coop Liberté)',
ADD COLUMN categoria_servicio VARCHAR(100) NULL COMMENT 'Categoría de servicio: BÁSICO, PREMIUM, etc.',
ADD COLUMN fecha_ingreso DATE NULL COMMENT 'Fecha de ingreso a la mutual/club/cooperativa',
ADD COLUMN foto_url VARCHAR(500) NULL COMMENT 'URL de foto del socio (opcional)';

-- Índice para búsqueda por número de asociado
ALTER TABLE verumax_nexus.miembros
ADD INDEX idx_numero_asociado (id_instancia, numero_asociado);

-- ============================================================================
-- 2. TABLA DE CREDENCIALES EMITIDAS (tracking y validación)
-- ============================================================================

CREATE TABLE IF NOT EXISTS verumax_nexus.credenciales (
    id_credencial INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    id_miembro INT NOT NULL,

    -- Código de validación único (para QR)
    codigo_validacion VARCHAR(50) NOT NULL UNIQUE COMMENT 'Ej: CRED-ABCD-1234',

    -- Fechas
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NULL COMMENT 'NULL = sin vencimiento',

    -- Estado
    activa TINYINT(1) DEFAULT 1,
    motivo_baja VARCHAR(200) NULL COMMENT 'Si se da de baja: perdida, renovación, etc.',

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_instancia (id_instancia),
    INDEX idx_miembro (id_miembro),
    INDEX idx_codigo (codigo_validacion),

    FOREIGN KEY (id_miembro) REFERENCES miembros(id_miembro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. CONFIGURACIÓN DE CREDENCIALES POR INSTANCIA (verumax_general)
-- ============================================================================

-- Agregar campo para tipos de documento habilitados
ALTER TABLE verumax_general.instances
ADD COLUMN tipos_documento_habilitados JSON DEFAULT NULL
COMMENT 'Tipos de documento que puede emitir: ["certificatum_approbationis", "analyticum", "credentialis", ...]';

-- Agregar configuración específica de credenciales
ALTER TABLE verumax_general.instances
ADD COLUMN credencial_config JSON DEFAULT NULL
COMMENT 'Config de credencial: {template_url, campos_posiciones, mostrar_foto, texto_inferior, ...}';

-- ============================================================================
-- 4. DATOS INICIALES
-- ============================================================================

-- Actualizar SAJuR con sus tipos habilitados (certificados y constancias)
UPDATE verumax_general.instances
SET tipos_documento_habilitados = '["certificatum_approbationis", "analyticum", "testimonium_regulare", "testimonium_inscriptionis", "testimonium_completionis", "certificatum_doctoris"]'
WHERE slug = 'sajur';

-- ============================================================================
-- 5. VERIFICACIÓN
-- ============================================================================

-- Verificar campos agregados en miembros
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'verumax_nexus'
AND TABLE_NAME = 'miembros'
AND COLUMN_NAME IN ('numero_asociado', 'tipo_asociado', 'nombre_entidad', 'categoria_servicio', 'fecha_ingreso', 'foto_url');

-- Verificar tabla credenciales
SHOW CREATE TABLE verumax_nexus.credenciales;

-- Verificar campos en instances
SELECT
    COLUMN_NAME,
    DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'verumax_general'
AND TABLE_NAME = 'instances'
AND COLUMN_NAME IN ('tipos_documento_habilitados', 'credencial_config');
