-- =====================================================
-- MIGRACIÓN: SendGrid Webhook Tracking System
-- Fecha: 2026-01-01
-- Descripción: Sistema de tracking de eventos de SendGrid vía webhooks
--              Almacena eventos raw y configura notificaciones admin
-- =====================================================

-- Usar la base de datos general de VERUMax
USE verumax_general;

-- -----------------------------------------------------
-- Tabla: sendgrid_webhook_events
-- Almacena TODOS los eventos raw recibidos de SendGrid
-- Usado para debugging, auditoría y estadísticas avanzadas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS sendgrid_webhook_events (
    id_event BIGINT AUTO_INCREMENT PRIMARY KEY,

    -- Identificación del evento
    sg_event_id VARCHAR(255) DEFAULT NULL COMMENT 'ID único del evento de SendGrid',
    sg_message_id VARCHAR(255) NOT NULL COMMENT 'ID del mensaje de SendGrid',
    event_type VARCHAR(50) NOT NULL COMMENT 'Tipo: delivered, open, click, bounce, etc.',

    -- Información del destinatario
    email VARCHAR(255) NOT NULL,

    -- Timestamp del evento (según SendGrid)
    event_timestamp INT NOT NULL COMMENT 'Unix timestamp del evento',
    event_datetime DATETIME GENERATED ALWAYS AS (FROM_UNIXTIME(event_timestamp)) STORED,

    -- Datos adicionales del evento
    ip VARCHAR(45) DEFAULT NULL COMMENT 'IP del destinatario',
    user_agent TEXT DEFAULT NULL COMMENT 'User agent del navegador',
    url VARCHAR(500) DEFAULT NULL COMMENT 'URL clickeada (si event_type=click)',
    reason TEXT DEFAULT NULL COMMENT 'Razón del bounce/drop',
    response TEXT DEFAULT NULL COMMENT 'Respuesta del servidor (bounces)',

    -- Custom args (contexto del email)
    batch_id VARCHAR(100) DEFAULT NULL,
    instancia VARCHAR(50) DEFAULT NULL,
    tipo_email VARCHAR(50) DEFAULT NULL,

    -- Payload completo (para debugging)
    payload_json TEXT NOT NULL COMMENT 'JSON completo del evento',

    -- Procesamiento
    procesado TINYINT(1) DEFAULT 0 COMMENT '1 si se actualizó email_logs',
    procesado_at TIMESTAMP NULL,
    error_procesamiento TEXT DEFAULT NULL,

    -- Auditoría
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Índices para consultas rápidas
    INDEX idx_sg_message_id (sg_message_id),
    INDEX idx_email (email),
    INDEX idx_event_type (event_type),
    INDEX idx_event_timestamp (event_timestamp),
    INDEX idx_batch_id (batch_id),
    INDEX idx_instancia (instancia),
    INDEX idx_procesado (procesado),
    INDEX idx_event_datetime (event_datetime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Eventos raw de SendGrid webhooks para debugging y auditoría';

-- -----------------------------------------------------
-- Actualizar tabla email_config
-- Agregar configuración de notificaciones de estadísticas
-- (Ejecutar cada ALTER por separado, ignorar error si columna ya existe)
-- -----------------------------------------------------

-- Procedimiento para agregar columnas de forma segura
DELIMITER //

DROP PROCEDURE IF EXISTS add_email_config_columns//

CREATE PROCEDURE add_email_config_columns()
BEGIN
    -- Agregar notificar_estadisticas si no existe
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'verumax_general'
        AND TABLE_NAME = 'email_config'
        AND COLUMN_NAME = 'notificar_estadisticas'
    ) THEN
        ALTER TABLE email_config
        ADD COLUMN notificar_estadisticas TINYINT(1) DEFAULT 0
        COMMENT 'Enviar notificaciones de estadísticas de emails' AFTER ultimo_envio_at;
    END IF;

    -- Agregar notificar_email si no existe
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'verumax_general'
        AND TABLE_NAME = 'email_config'
        AND COLUMN_NAME = 'notificar_email'
    ) THEN
        ALTER TABLE email_config
        ADD COLUMN notificar_email VARCHAR(255) DEFAULT NULL
        COMMENT 'Email del admin que recibirá notificaciones' AFTER notificar_estadisticas;
    END IF;

    -- Agregar notificar_frecuencia si no existe
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'verumax_general'
        AND TABLE_NAME = 'email_config'
        AND COLUMN_NAME = 'notificar_frecuencia'
    ) THEN
        ALTER TABLE email_config
        ADD COLUMN notificar_frecuencia ENUM('diario', 'semanal', 'mensual', 'nunca') DEFAULT 'nunca'
        COMMENT 'Frecuencia de envío de reportes' AFTER notificar_email;
    END IF;

    -- Agregar notificar_rebotes_alta si no existe
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'verumax_general'
        AND TABLE_NAME = 'email_config'
        AND COLUMN_NAME = 'notificar_rebotes_alta'
    ) THEN
        ALTER TABLE email_config
        ADD COLUMN notificar_rebotes_alta TINYINT(1) DEFAULT 1
        COMMENT 'Notificar si tasa de rebote > 5%' AFTER notificar_frecuencia;
    END IF;
END//

DELIMITER ;

-- Ejecutar procedimiento
CALL add_email_config_columns();

-- Limpiar procedimiento
DROP PROCEDURE IF EXISTS add_email_config_columns;

-- -----------------------------------------------------
-- Tabla: email_notification_history
-- Historial de notificaciones enviadas a admins
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS email_notification_history (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,

    -- Tipo de notificación
    tipo ENUM('reporte_estadisticas', 'alerta_rebotes', 'alerta_clicks_bajos') NOT NULL,

    -- Período del reporte
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,

    -- Estadísticas incluidas
    total_enviados INT DEFAULT 0,
    total_abiertos INT DEFAULT 0,
    total_clicks INT DEFAULT 0,
    total_rebotes INT DEFAULT 0,
    tasa_apertura DECIMAL(5,2) DEFAULT 0.00,
    tasa_clicks DECIMAL(5,2) DEFAULT 0.00,
    tasa_rebotes DECIMAL(5,2) DEFAULT 0.00,

    -- Email enviado
    email_destino VARCHAR(255) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    enviado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Estado
    estado ENUM('enviado', 'error') DEFAULT 'enviado',
    error_mensaje TEXT DEFAULT NULL,

    INDEX idx_instancia (id_instancia),
    INDEX idx_tipo (tipo),
    INDEX idx_enviado_at (enviado_at),
    FOREIGN KEY (id_instancia) REFERENCES instances(id_instancia) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de notificaciones de estadísticas a administradores';

-- -----------------------------------------------------
-- Configuración inicial para instancias existentes
-- -----------------------------------------------------
-- Configurar notificaciones deshabilitadas por defecto (seguridad)
UPDATE email_config
SET notificar_estadisticas = 0,
    notificar_frecuencia = 'nunca',
    notificar_rebotes_alta = 0
WHERE notificar_estadisticas IS NULL;

-- -----------------------------------------------------
-- Crear vista para estadísticas rápidas
-- -----------------------------------------------------
CREATE OR REPLACE VIEW v_email_stats_daily AS
SELECT
    id_instancia,
    DATE(enviado_at) as fecha,
    COUNT(*) as total_enviados,
    SUM(CASE WHEN estado IN ('abierto', 'click') THEN 1 ELSE 0 END) as abiertos,
    SUM(CASE WHEN estado = 'click' THEN 1 ELSE 0 END) as clicks,
    SUM(CASE WHEN estado = 'rebotado' THEN 1 ELSE 0 END) as rebotes,
    ROUND(SUM(CASE WHEN estado IN ('abierto', 'click') THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as tasa_apertura,
    ROUND(SUM(CASE WHEN estado = 'click' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as tasa_clicks,
    ROUND(SUM(CASE WHEN estado = 'rebotado' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as tasa_rebotes
FROM email_logs
WHERE enviado_at IS NOT NULL
GROUP BY id_instancia, DATE(enviado_at);

-- =====================================================
-- NOTAS DE IMPLEMENTACIÓN
-- =====================================================
--
-- 1. WEBHOOK ENDPOINT: Crear archivo api/sendgrid/webhook.php
-- 2. CONFIGURAR EN SENDGRID:
--    - URL: https://verumax.com/api/sendgrid/webhook.php
--    - Eventos: delivered, open, click, bounce, dropped, spam_report
--    - Habilitar OAuth Signature
-- 3. MONITOREO: Revisar tabla sendgrid_webhook_events para debugging
-- 4. LIMPIEZA: Programar limpieza de eventos > 90 días
--
-- =====================================================
