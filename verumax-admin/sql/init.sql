-- ============================================================================
-- VERUMAX SUPER ADMIN - Inicialización de Base de Datos
--
-- Ejecutar en: verumax_general
-- Fecha: 2026-01-13
-- ============================================================================

-- Usar base de datos
USE verumax_general;

-- ============================================================================
-- TABLA: super_admins
-- Usuarios del Super Admin Panel
-- ============================================================================
CREATE TABLE IF NOT EXISTS super_admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    rol ENUM('superadmin', 'admin', 'viewer') DEFAULT 'admin',
    totp_secret VARCHAR(32) DEFAULT NULL,
    totp_habilitado TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME DEFAULT NULL,
    ultimo_acceso DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: planes_suscripcion
-- Planes disponibles para clientes
-- ============================================================================
CREATE TABLE IF NOT EXISTS planes_suscripcion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    precio_mensual DECIMAL(10,2) NOT NULL,
    precio_anual DECIMAL(10,2) DEFAULT NULL,
    -- Límites de Certificatum
    limite_pdfs_mes INT DEFAULT NULL COMMENT 'NULL = ilimitado',
    limite_emails_mes INT DEFAULT NULL,
    limite_ai_calls_mes INT DEFAULT NULL,
    limite_dalle_imagenes_mes INT DEFAULT NULL,
    limite_import_registros_mes INT DEFAULT NULL,
    limite_usuarios_admin INT DEFAULT 1,
    -- Idiomas disponibles (JSON array)
    idiomas_disponibles JSON DEFAULT NULL COMMENT '["es_AR", "pt_BR", ...]',
    -- Retención
    dias_retencion_logs INT DEFAULT 30,
    -- Características
    tiene_branding_custom TINYINT(1) DEFAULT 0,
    tiene_dominio_custom TINYINT(1) DEFAULT 0,
    tiene_api_access TINYINT(1) DEFAULT 0,
    tiene_soporte_prioritario TINYINT(1) DEFAULT 0,
    -- Estado
    activo TINYINT(1) DEFAULT 1,
    orden_display INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: instancias
-- Clientes/Instituciones registradas
-- ============================================================================
CREATE TABLE IF NOT EXISTS instancias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(30) NOT NULL UNIQUE COMMENT 'ej: sajur, liberte',
    nombre VARCHAR(100) NOT NULL,
    nombre_completo VARCHAR(255) DEFAULT NULL,
    -- Plan y facturación
    id_plan INT DEFAULT NULL,
    modo ENUM('test', 'beta', 'produccion') DEFAULT 'test',
    fecha_inicio DATE DEFAULT NULL,
    fecha_vencimiento DATE DEFAULT NULL,
    -- Configuración básica
    email_contacto VARCHAR(100) DEFAULT NULL,
    telefono VARCHAR(30) DEFAULT NULL,
    pais VARCHAR(50) DEFAULT 'Argentina',
    timezone VARCHAR(50) DEFAULT 'America/Argentina/Buenos_Aires',
    idioma_default VARCHAR(10) DEFAULT 'es_AR',
    -- Branding (el cliente configura desde su admin)
    logo_url VARCHAR(255) DEFAULT NULL,
    color_primario VARCHAR(7) DEFAULT '#2E7D32',
    color_secundario VARCHAR(7) DEFAULT '#1B5E20',
    -- Dominio
    dominio_custom VARCHAR(100) DEFAULT NULL,
    subdominio VARCHAR(50) DEFAULT NULL,
    -- Estado
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_plan) REFERENCES planes_suscripcion(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: instancia_soluciones
-- Qué soluciones tiene habilitadas cada instancia
-- ============================================================================
CREATE TABLE IF NOT EXISTS instancia_soluciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_instancia INT NOT NULL,
    solucion ENUM('certificatum', 'identitas', 'liberatum', 'coadmin') NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_activacion DATE DEFAULT NULL,
    configuracion JSON DEFAULT NULL COMMENT 'Configuración específica de la solución',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_instancia_solucion (id_instancia, solucion),
    FOREIGN KEY (id_instancia) REFERENCES instancias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: instancia_uso_mensual
-- Tracking de uso mensual por instancia
-- ============================================================================
CREATE TABLE IF NOT EXISTS instancia_uso_mensual (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_instancia INT NOT NULL,
    anio INT NOT NULL,
    mes INT NOT NULL,
    -- Contadores Certificatum
    pdfs_generados INT DEFAULT 0,
    emails_enviados INT DEFAULT 0,
    ai_calls INT DEFAULT 0,
    dalle_imagenes INT DEFAULT 0,
    import_registros INT DEFAULT 0,
    -- Contadores Identitas
    tarjetas_generadas INT DEFAULT 0,
    qr_escaneados INT DEFAULT 0,
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_instancia_periodo (id_instancia, anio, mes),
    FOREIGN KEY (id_instancia) REFERENCES instancias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: audit_log
-- Log de acciones del Super Admin
-- ============================================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_superadmin INT DEFAULT NULL,
    accion VARCHAR(50) NOT NULL,
    entidad VARCHAR(50) DEFAULT NULL COMMENT 'instancia, plan, etc',
    id_entidad INT DEFAULT NULL,
    datos_anteriores JSON DEFAULT NULL,
    datos_nuevos JSON DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_superadmin) REFERENCES super_admins(id) ON DELETE SET NULL,
    INDEX idx_accion (accion),
    INDEX idx_entidad (entidad, id_entidad),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DATOS INICIALES
-- ============================================================================

-- Insertar planes de suscripción
INSERT INTO planes_suscripcion (codigo, nombre, precio_mensual, precio_anual,
    limite_pdfs_mes, limite_emails_mes, limite_ai_calls_mes, limite_dalle_imagenes_mes,
    limite_import_registros_mes, limite_usuarios_admin, idiomas_disponibles,
    dias_retencion_logs, tiene_branding_custom, tiene_dominio_custom, orden_display)
VALUES
    ('test', 'Test', 0.00, NULL,
     10, 5, 5, 2, 50, 1,
     '["es_AR"]', 7, 0, 0, 0),

    ('essentialis', 'Essentialis', 12.00, 120.00,
     50, 20, 10, 5, 100, 1,
     '["es_AR", "pt_BR"]', 30, 0, 0, 1),

    ('premium', 'Premium', 24.00, 240.00,
     200, 100, 50, 20, 500, 3,
     '["es_AR", "pt_BR", "en_US", "es_ES"]', 90, 1, 0, 2),

    ('excellens', 'Excellens', 40.00, 400.00,
     1000, 500, 200, 50, 2000, 5,
     '["es_AR", "pt_BR", "en_US", "es_ES", "ca_ES", "eu_ES", "pt_PT"]', 180, 1, 1, 3),

    ('supremus', 'Supremus', 80.00, 800.00,
     NULL, NULL, NULL, NULL, NULL, 10,
     '["es_AR", "pt_BR", "en_US", "es_ES", "ca_ES", "eu_ES", "pt_PT", "el_GR"]', 365, 1, 1, 4);

-- ============================================================================
-- CREAR PRIMER SUPERADMIN
-- Password: VERUMax2026! (cambiar después del primer login)
-- ============================================================================
INSERT INTO super_admins (username, password_hash, nombre, email, rol, activo)
VALUES (
    'admin',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4gqp7OKYL0qT9Qkm', -- VERUMax2026!
    'Administrador',
    'admin@verumax.com',
    'superadmin',
    1
);

-- ============================================================================
-- REGISTRAR SAJUR COMO CLIENTE LEGACY (sin límites)
-- ============================================================================
INSERT INTO instancias (codigo, nombre, nombre_completo, modo, activo,
    email_contacto, pais, idioma_default, color_primario, color_secundario)
VALUES (
    'sajur',
    'SAJuR',
    'Sociedad Argentina de Justicia Restaurativa',
    'produccion',
    1,
    'info@sajur.org',
    'Argentina',
    'es_AR',
    '#2E7D32',
    '#1B5E20'
);

-- Activar Certificatum para SAJuR
INSERT INTO instancia_soluciones (id_instancia, solucion, activo, fecha_activacion)
SELECT id, 'certificatum', 1, CURDATE()
FROM instancias WHERE codigo = 'sajur';

-- ============================================================================
-- FIN
-- ============================================================================
SELECT 'Inicialización completada correctamente' AS mensaje;
