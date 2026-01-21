-- =====================================================
-- MIGRACIÓN: Sistema de Templates de Certificados
-- Fecha: 2025-12-26
-- Versión: 1.0
-- =====================================================
-- IMPORTANTE: Ejecutar en orden. El sistema actual sigue
-- funcionando porque id_template es nullable (fallback).
-- =====================================================

-- =====================================================
-- PASO 1: Crear tabla certificatum_templates
-- Base de datos: verumax_certifi
-- =====================================================
USE verumax_certifi;

CREATE TABLE IF NOT EXISTS `certificatum_templates` (
  `id_template` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `tipo_generador` ENUM('tcpdf', 'mpdf') DEFAULT 'mpdf',
  `orientacion` ENUM('landscape', 'portrait') DEFAULT 'landscape',
  `preview_url` VARCHAR(255),
  `config` JSON COMMENT 'Posicionamiento, fuentes, estilos',
  `tiene_imagen_fondo` TINYINT(1) DEFAULT 0,
  `imagen_fondo_path` VARCHAR(255),
  `activo` TINYINT(1) DEFAULT 1,
  `orden` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PASO 2: Insertar templates iniciales
-- =====================================================
INSERT INTO `certificatum_templates`
  (`slug`, `nombre`, `descripcion`, `tipo_generador`, `orientacion`, `preview_url`, `config`, `tiene_imagen_fondo`, `orden`)
VALUES
  ('moderno', 'Moderno', 'Diseño contemporáneo con gradientes y tipografía sans-serif', 'mpdf', 'landscape', '/assets/templates/certificados/moderno/preview.jpg', '{"fuente_titulo": "Montserrat", "fuente_nombre": "Playfair Display", "fuente_cuerpo": "Open Sans", "estilo": "gradiente"}', 0, 1),
  ('minimalista', 'Minimalista', 'Diseño limpio con espacios en blanco y líneas simples', 'mpdf', 'landscape', '/assets/templates/certificados/minimalista/preview.jpg', '{"fuente_titulo": "Josefin Sans", "fuente_nombre": "Lora", "fuente_cuerpo": "Source Sans 3", "estilo": "minimal"}', 0, 2),
  ('formal', 'Formal Académico', 'Estilo tradicional universitario con bordes ornamentales', 'mpdf', 'landscape', '/assets/templates/certificados/formal/preview.jpg', '{"fuente_titulo": "Cormorant Garamond", "fuente_nombre": "Great Vibes", "fuente_cuerpo": "Crimson Text", "estilo": "academico"}', 0, 3),
  ('corporativo', 'Corporativo', 'Diseño profesional para empresas y capacitaciones', 'mpdf', 'landscape', '/assets/templates/certificados/corporativo/preview.jpg', '{"fuente_titulo": "Poppins", "fuente_nombre": "Raleway", "fuente_cuerpo": "Roboto", "estilo": "empresarial"}', 0, 4);

-- =====================================================
-- PASO 3: Agregar campo id_template a cursos
-- Base de datos: verumax_academi
-- =====================================================
USE verumax_academi;

ALTER TABLE `cursos`
ADD COLUMN `id_template` INT DEFAULT NULL
COMMENT 'Template de certificado (NULL=usar sistema actual/fallback)';

-- =====================================================
-- VERIFICACIÓN (opcional)
-- =====================================================
-- SELECT * FROM verumax_certifi.certificatum_templates;
-- DESCRIBE verumax_academi.cursos;
