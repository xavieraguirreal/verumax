-- ============================================================================
-- IDENTITAS - Sistema de Presencia Digital Profesional
-- Base de Datos - VERUMax
-- ============================================================================
--
-- Base de datos: verumax_identi
-- Usuario: verumax_identi
-- Password: /hPfiYd6xH
-- ============================================================================

USE verumax_identi;

-- ============================================================================
-- TABLA: identitas_instances
-- Almacena las instancias de clientes (ej: sajur, liberte, etc.)
-- ============================================================================
CREATE TABLE IF NOT EXISTS identitas_instances (
    id_instancia INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE COMMENT 'Identificador único (ej: sajur, liberte)',
    nombre VARCHAR(255) NOT NULL COMMENT 'Nombre de la institución/profesional',
    nombre_completo TEXT COMMENT 'Nombre completo o descripción',
    dominio VARCHAR(255) COMMENT 'Dominio personalizado (ej: sajur.verumax.com)',

    -- Branding y personalización
    logo_url VARCHAR(500) COMMENT 'URL del logo',
    color_primario VARCHAR(7) DEFAULT '#D4AF37' COMMENT 'Color principal (hex)',
    color_secundario VARCHAR(7) COMMENT 'Color secundario (hex)',

    -- Configuración JSON
    configuracion TEXT COMMENT 'JSON con config adicional (páginas, módulos, etc.)',

    -- Módulos activos
    modulo_certificatum BOOLEAN DEFAULT 0 COMMENT 'Módulo Certificatum activo',
    modulo_scripta BOOLEAN DEFAULT 0 COMMENT 'Módulo Scripta (blog) activo',
    modulo_nexus BOOLEAN DEFAULT 0 COMMENT 'Módulo Nexus (CRM) activo',
    modulo_vitae BOOLEAN DEFAULT 0 COMMENT 'Módulo Vitae (CV) activo',
    modulo_lumen BOOLEAN DEFAULT 0 COMMENT 'Módulo Lumen (portfolio) activo',
    modulo_opera BOOLEAN DEFAULT 0 COMMENT 'Módulo Opera (proyectos) activo',

    -- Plan contratado
    plan VARCHAR(50) DEFAULT 'basicum' COMMENT 'Plan: basicum, premium, excellens, supremus',

    -- Metadatos
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT 1,

    INDEX idx_slug (slug),
    INDEX idx_dominio (dominio),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: identitas_paginas
-- Páginas personalizadas de cada instancia
-- ============================================================================
CREATE TABLE IF NOT EXISTS identitas_paginas (
    id_pagina INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    slug VARCHAR(100) NOT NULL COMMENT 'URL de la página (ej: sobre-nosotros, servicios)',
    titulo VARCHAR(255) NOT NULL,
    contenido LONGTEXT COMMENT 'Contenido HTML de la página',
    orden INT DEFAULT 0 COMMENT 'Orden de aparición en menú',
    visible_menu BOOLEAN DEFAULT 1 COMMENT 'Mostrar en menú de navegación',
    activo BOOLEAN DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_instancia) REFERENCES identitas_instances(id_instancia) ON DELETE CASCADE,
    UNIQUE KEY unique_instance_slug (id_instancia, slug),
    INDEX idx_instancia (id_instancia),
    INDEX idx_activo (activo),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: identitas_tarjetas
-- Tarjetas digitales generadas para cada instancia
-- ============================================================================
CREATE TABLE IF NOT EXISTS identitas_tarjetas (
    id_tarjeta INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    nombre_persona VARCHAR(255) NOT NULL COMMENT 'Nombre en la tarjeta',
    cargo VARCHAR(255) COMMENT 'Cargo/Título en la tarjeta',
    telefono VARCHAR(50),
    email VARCHAR(255),
    direccion TEXT,
    redes_sociales TEXT COMMENT 'JSON con enlaces a redes sociales',

    -- QR Code
    qr_code_url VARCHAR(500) COMMENT 'URL del QR generado',
    qr_destino VARCHAR(500) COMMENT 'URL a donde apunta el QR',

    -- Imagen de la tarjeta
    imagen_tarjeta_url VARCHAR(500) COMMENT 'URL de la tarjeta JPG generada',

    -- Metadatos
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT 1,

    FOREIGN KEY (id_instancia) REFERENCES identitas_instances(id_instancia) ON DELETE CASCADE,
    INDEX idx_instancia (id_instancia),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: identitas_contactos
-- Mensajes del formulario de contacto
-- ============================================================================
CREATE TABLE IF NOT EXISTS identitas_contactos (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    asunto VARCHAR(500),
    mensaje TEXT NOT NULL,
    ip_origen VARCHAR(45),
    user_agent TEXT,
    leido BOOLEAN DEFAULT 0,
    fecha_contacto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_instancia) REFERENCES identitas_instances(id_instancia) ON DELETE CASCADE,
    INDEX idx_instancia (id_instancia),
    INDEX idx_leido (leido),
    INDEX idx_fecha (fecha_contacto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DATOS INICIALES: Instancia SAJuR
-- ============================================================================
INSERT INTO identitas_instances (
    slug,
    nombre,
    nombre_completo,
    dominio,
    color_primario,
    configuracion,
    modulo_certificatum,
    plan
) VALUES (
    'sajur',
    'SAJuR',
    'Sociedad Argentina de Justicia Restaurativa',
    'sajur.verumax.com',
    '#006837',
    JSON_OBJECT(
        'sitio_web_oficial', 'https://sajur.org/es/',
        'email_contacto', 'info@sajur.org',
        'mision', 'La Sociedad Argentina de Justicia Restaurativa (SAJuR) es una asociación civil sin fines de lucro, fundada en 2017, con el objetivo de promover, difundir e investigar la Justicia Restaurativa como un nuevo paradigma de respuesta al conflicto y al delito.'
    ),
    1,  -- modulo_certificatum activo
    'basicum'
);

-- Obtener ID de SAJuR para las páginas
SET @sajur_id = LAST_INSERT_ID();

-- Páginas predefinidas para SAJuR
INSERT INTO identitas_paginas (id_instancia, slug, titulo, contenido, orden, visible_menu) VALUES
(@sajur_id, 'inicio', 'Inicio', '<h1>Bienvenido a SAJuR</h1>', 0, 1),
(@sajur_id, 'sobre-nosotros', 'Sobre Nosotros', '<h2>Nuestra Misión</h2><p>La Sociedad Argentina de Justicia Restaurativa (SAJuR) es una asociación civil sin fines de lucro, fundada en 2017...</p>', 1, 1),
(@sajur_id, 'servicios', 'Servicios', '<h2>Nuestros Servicios</h2>', 2, 1),
(@sajur_id, 'contacto', 'Contacto', '<h2>Contacto</h2>', 3, 1);

-- ============================================================================
-- NOTAS DE IMPLEMENTACIÓN
-- ============================================================================
--
-- Esta estructura permite:
-- 1. Múltiples instancias (clientes) en una sola base de datos
-- 2. Personalización completa de branding por instancia
-- 3. Activación modular de soluciones VERUMax
-- 4. Páginas personalizables por instancia
-- 5. Tarjetas digitales con QR por instancia
-- 6. Gestión de contactos por instancia
--
-- Para agregar un nuevo cliente:
-- INSERT INTO identitas_instances (slug, nombre, ...) VALUES (...);
--
-- ============================================================================
