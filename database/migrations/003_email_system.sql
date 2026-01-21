-- =====================================================
-- MIGRACIÓN: Sistema de Emails (Communica Base)
-- Fecha: 2025-12-14
-- Descripción: Tablas base para envío de emails transaccionales
--              Compatible con futuro módulo Communica completo
-- =====================================================

-- Usar la base de datos general de VERUMax
USE verumax_general;

-- -----------------------------------------------------
-- Tabla: email_config
-- Configuración de email por instancia (SendGrid, remitente, etc.)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS email_config (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,

    -- Configuración SendGrid
    sendgrid_api_key VARCHAR(255) DEFAULT NULL COMMENT 'API Key de SendGrid (encriptada)',
    usar_sendgrid_global TINYINT(1) DEFAULT 1 COMMENT 'Usar API key global de VERUMax',

    -- Remitente
    email_remitente VARCHAR(255) DEFAULT NULL COMMENT 'Email verificado en SendGrid',
    nombre_remitente VARCHAR(100) DEFAULT NULL COMMENT 'Nombre que aparece como remitente',
    email_respuesta VARCHAR(255) DEFAULT NULL COMMENT 'Reply-to email',

    -- Dominio verificado
    dominio_verificado VARCHAR(100) DEFAULT NULL COMMENT 'Dominio verificado en SendGrid (ej: sajur.org.ar)',
    dominio_verificado_at TIMESTAMP NULL COMMENT 'Fecha de verificación',

    -- Estado
    activo TINYINT(1) DEFAULT 1,
    emails_enviados_mes INT DEFAULT 0 COMMENT 'Contador mensual',
    ultimo_envio_at TIMESTAMP NULL,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_instancia (id_instancia),
    FOREIGN KEY (id_instancia) REFERENCES instances(id_instancia) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Configuración de email por instancia';

-- -----------------------------------------------------
-- Tabla: email_logs
-- Registro de todos los emails enviados
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS email_logs (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,

    -- Tipo de email
    tipo_email ENUM(
        'certificado_disponible',
        'constancia_disponible',
        'bienvenida_curso',
        'recordatorio',
        'notificacion_general',
        'campana_marketing'
    ) NOT NULL,

    -- Destinatario
    email_destino VARCHAR(255) NOT NULL,
    nombre_destino VARCHAR(255) DEFAULT NULL,

    -- Contenido
    asunto VARCHAR(255) NOT NULL,
    template_usado VARCHAR(50) DEFAULT NULL COMMENT 'Código del template',

    -- Estado del envío
    estado ENUM('pendiente', 'enviado', 'error', 'rebotado', 'abierto', 'click') DEFAULT 'pendiente',

    -- SendGrid tracking
    sendgrid_message_id VARCHAR(100) DEFAULT NULL,
    sendgrid_batch_id VARCHAR(100) DEFAULT NULL COMMENT 'ID del batch si fue envío masivo',

    -- Error info
    error_codigo VARCHAR(50) DEFAULT NULL,
    error_mensaje TEXT DEFAULT NULL,
    intentos INT DEFAULT 0,

    -- Datos contextuales (para debugging y reportes)
    datos_json JSON DEFAULT NULL COMMENT 'Datos adicionales: id_inscripcion, id_curso, etc.',

    -- Timestamps
    programado_para TIMESTAMP NULL COMMENT 'Para envíos programados',
    enviado_at TIMESTAMP NULL,
    abierto_at TIMESTAMP NULL,
    click_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_instancia_tipo (id_instancia, tipo_email),
    INDEX idx_estado (estado),
    INDEX idx_email (email_destino),
    INDEX idx_fecha (created_at),
    INDEX idx_sendgrid_msg (sendgrid_message_id),
    INDEX idx_batch (sendgrid_batch_id),

    FOREIGN KEY (id_instancia) REFERENCES instances(id_instancia) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log de emails enviados';

-- -----------------------------------------------------
-- Tabla: email_templates
-- Templates de email reutilizables
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS email_templates (
    id_template INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT DEFAULT NULL COMMENT 'NULL = template del sistema',

    -- Identificación
    codigo VARCHAR(50) NOT NULL COMMENT 'Código único: certificado_disponible',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre descriptivo',
    descripcion TEXT DEFAULT NULL,

    -- Contenido
    asunto_default VARCHAR(255) NOT NULL,
    contenido_html MEDIUMTEXT NOT NULL,
    contenido_texto TEXT DEFAULT NULL COMMENT 'Versión texto plano',

    -- Variables disponibles para personalización
    variables_disponibles JSON DEFAULT NULL COMMENT '["nombre", "curso", "url_portal"]',

    -- SendGrid
    sendgrid_template_id VARCHAR(100) DEFAULT NULL COMMENT 'ID si está sincronizado con SendGrid',

    -- Estado
    es_sistema TINYINT(1) DEFAULT 0 COMMENT 'Template del sistema, no editable',
    activo TINYINT(1) DEFAULT 1,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_codigo_instancia (id_instancia, codigo),
    INDEX idx_codigo (codigo),
    INDEX idx_sistema (es_sistema)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Templates de email reutilizables';

-- -----------------------------------------------------
-- Insertar templates del sistema
-- -----------------------------------------------------
INSERT INTO email_templates (id_instancia, codigo, nombre, asunto_default, contenido_html, contenido_texto, variables_disponibles, es_sistema) VALUES
(NULL, 'certificado_disponible', 'Certificado Disponible para Descarga',
'Tu certificado de {{nombre_curso}} está listo',
'<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:{{color_primario}};padding:30px;text-align:center;">
                            <img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;max-width:200px;">
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 30px;">
                            <h1 style="color:#333;margin:0 0 20px;font-size:24px;">¡Felicitaciones, {{nombre_estudiante}}!</h1>
                            <p style="color:#666;font-size:16px;line-height:1.6;margin:0 0 20px;">
                                Tu certificado del curso <strong>{{nombre_curso}}</strong> ya está disponible para descargar.
                            </p>
                            <p style="color:#666;font-size:16px;line-height:1.6;margin:0 0 30px;">
                                Podés acceder a tu certificado ingresando a nuestro portal con tu número de documento.
                            </p>
                            <!-- Button -->
                            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                <tr>
                                    <td style="background-color:{{color_primario}};border-radius:6px;">
                                        <a href="{{url_portal}}" style="display:inline-block;padding:15px 30px;color:#ffffff;text-decoration:none;font-weight:bold;font-size:16px;">
                                            Descargar Certificado
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8f9fa;padding:20px 30px;text-align:center;border-top:1px solid #eee;">
                            <p style="color:#999;font-size:12px;margin:0;">
                                {{nombre_institucion}}<br>
                                Este es un mensaje automático, por favor no respondas a este email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
'¡Felicitaciones, {{nombre_estudiante}}!

Tu certificado del curso "{{nombre_curso}}" ya está disponible para descargar.

Podés acceder a tu certificado ingresando a nuestro portal con tu número de documento:
{{url_portal}}

{{nombre_institucion}}
Este es un mensaje automático, por favor no respondas a este email.',
'["nombre_estudiante", "nombre_curso", "url_portal", "nombre_institucion", "logo_url", "color_primario"]',
1),

(NULL, 'constancia_disponible', 'Constancia Disponible para Descarga',
'Tu constancia de {{nombre_curso}} está lista',
'<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:{{color_primario}};padding:30px;text-align:center;">
                            <img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;max-width:200px;">
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 30px;">
                            <h1 style="color:#333;margin:0 0 20px;font-size:24px;">Hola, {{nombre_estudiante}}</h1>
                            <p style="color:#666;font-size:16px;line-height:1.6;margin:0 0 20px;">
                                Tu constancia del curso <strong>{{nombre_curso}}</strong> ya está disponible para descargar.
                            </p>
                            <p style="color:#666;font-size:16px;line-height:1.6;margin:0 0 30px;">
                                Podés acceder ingresando a nuestro portal con tu número de documento.
                            </p>
                            <!-- Button -->
                            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                <tr>
                                    <td style="background-color:{{color_primario}};border-radius:6px;">
                                        <a href="{{url_portal}}" style="display:inline-block;padding:15px 30px;color:#ffffff;text-decoration:none;font-weight:bold;font-size:16px;">
                                            Ver Constancia
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8f9fa;padding:20px 30px;text-align:center;border-top:1px solid #eee;">
                            <p style="color:#999;font-size:12px;margin:0;">
                                {{nombre_institucion}}<br>
                                Este es un mensaje automático, por favor no respondas a este email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
'Hola, {{nombre_estudiante}}

Tu constancia del curso "{{nombre_curso}}" ya está disponible para descargar.

Podés acceder ingresando a nuestro portal con tu número de documento:
{{url_portal}}

{{nombre_institucion}}
Este es un mensaje automático, por favor no respondas a este email.',
'["nombre_estudiante", "nombre_curso", "url_portal", "nombre_institucion", "logo_url", "color_primario"]',
1);

-- -----------------------------------------------------
-- Insertar configuración por defecto para instancias existentes
-- -----------------------------------------------------
INSERT IGNORE INTO email_config (id_instancia, usar_sendgrid_global, nombre_remitente, activo)
SELECT id_instancia, 1, nombre, 1 FROM instances WHERE activo = 1;
