-- =====================================================
-- MIGRACION: Sistema de Demora Configurable para Certificados
-- Fecha: 2025-12-24
-- Descripcion: Agrega periodo de espera configurable entre
--              aprobacion de evaluacion y disponibilidad del certificado
-- =====================================================

-- -----------------------------------------------------
-- Paso 1: Agregar configuracion de demora por institucion
-- -----------------------------------------------------
ALTER TABLE verumax_general.instances
ADD COLUMN demora_certificado_horas INT UNSIGNED DEFAULT 24
COMMENT 'Horas de espera entre aprobacion y disponibilidad del certificado (0 = inmediato)';

-- -----------------------------------------------------
-- Paso 2: Agregar campo de fecha de disponibilidad en inscripciones
-- -----------------------------------------------------
ALTER TABLE verumax_academi.inscripciones
ADD COLUMN certificado_disponible_desde DATETIME NULL
COMMENT 'Fecha/hora desde la cual el certificado esta disponible para descargar'
AFTER fecha_emision_certificado;

-- Agregar indice para consultas eficientes
CREATE INDEX idx_cert_disponible ON verumax_academi.inscripciones(certificado_disponible_desde);

-- -----------------------------------------------------
-- Paso 3: Modificar ENUM de tipo_email para incluir nuevo tipo
-- -----------------------------------------------------
ALTER TABLE verumax_general.email_logs
MODIFY COLUMN tipo_email ENUM(
    'certificado_disponible',
    'constancia_disponible',
    'bienvenida_curso',
    'recordatorio',
    'notificacion_general',
    'campana_marketing',
    'evaluacion_disponible',
    'evaluacion_aprobada'
) NOT NULL;

-- -----------------------------------------------------
-- Paso 4: Insertar template de evaluacion aprobada (certificado en proceso)
-- -----------------------------------------------------
INSERT INTO verumax_general.email_templates (
    id_instancia,
    codigo,
    nombre,
    asunto_default,
    contenido_html,
    contenido_texto,
    variables_disponibles,
    es_sistema
) VALUES (
    NULL,
    'evaluacion_aprobada',
    'Evaluacion Aprobada - Certificado en Proceso',
    'Has completado tu evaluacion - {{nombre_curso}}',
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
                            <h1 style="color:#333;margin:0 0 20px;font-size:24px;">Felicitaciones, {{nombre_estudiante}}</h1>

                            <p style="color:#666;font-size:16px;line-height:1.6;margin:0 0 20px;">
                                Has completado exitosamente la evaluacion del curso:
                            </p>

                            <!-- Curso Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border-radius:8px;border-left:4px solid #22c55e;margin:0 0 25px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="color:#22c55e;font-size:12px;text-transform:uppercase;letter-spacing:1px;margin:0 0 8px;font-weight:bold;">
                                            EVALUACION APROBADA
                                        </p>
                                        <h2 style="color:#333;margin:0;font-size:20px;">{{nombre_curso}}</h2>
                                    </td>
                                </tr>
                            </table>

                            <!-- Mensaje de revision -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#eff6ff;border-radius:8px;margin:0 0 25px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="color:#1e40af;font-size:14px;margin:0 0 10px;">
                                            <strong>Proceso de certificacion en curso</strong>
                                        </p>
                                        <p style="color:#3b82f6;font-size:14px;margin:0;line-height:1.5;">
                                            Nuestro equipo docente realizara una revision pedagogica de tus respuestas como parte del proceso de certificacion academica.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Fecha disponibilidad -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 25px;">
                                <tr>
                                    <td style="background-color:#fef3c7;border-radius:6px;padding:15px;text-align:center;">
                                        <p style="color:#92400e;font-size:16px;margin:0;">
                                            Tu certificado estara disponible el<br>
                                            <strong style="font-size:18px;">{{fecha_disponibilidad}}</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#666;font-size:16px;line-height:1.6;margin:0 0 30px;">
                                Te enviaremos un correo electronico cuando tu certificado este listo para descargar.
                            </p>

                            <!-- Button -->
                            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                <tr>
                                    <td style="background-color:{{color_primario}};border-radius:6px;">
                                        <a href="{{url_portal}}" style="display:inline-block;padding:15px 30px;color:#ffffff;text-decoration:none;font-weight:bold;font-size:16px;">
                                            Ver mi trayectoria academica
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
                                Este es un mensaje automatico, por favor no respondas a este email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
    'Felicitaciones, {{nombre_estudiante}}

Has completado exitosamente la evaluacion del curso:
{{nombre_curso}}

Nuestro equipo docente realizara una revision pedagogica de tus respuestas como parte del proceso de certificacion academica.

Tu certificado estara disponible el {{fecha_disponibilidad}}.

Te enviaremos un correo electronico cuando este listo para descargar.

Mientras tanto, podes acceder a tu trayectoria academica en:
{{url_portal}}

{{nombre_institucion}}
Este es un mensaje automatico, por favor no respondas a este email.',
    '["nombre_estudiante", "nombre_curso", "fecha_disponibilidad", "url_portal", "nombre_institucion", "logo_url", "color_primario"]',
    1
)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    asunto_default = VALUES(asunto_default),
    contenido_html = VALUES(contenido_html),
    contenido_texto = VALUES(contenido_texto),
    variables_disponibles = VALUES(variables_disponibles),
    updated_at = CURRENT_TIMESTAMP;

-- -----------------------------------------------------
-- Verificacion
-- -----------------------------------------------------
SELECT 'Verificando cambios...' as status;

-- Verificar campo en instances
SELECT COLUMN_NAME, COLUMN_DEFAULT, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'verumax_general'
AND TABLE_NAME = 'instances'
AND COLUMN_NAME = 'demora_certificado_horas';

-- Verificar campo en inscripciones
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'verumax_academi'
AND TABLE_NAME = 'inscripciones'
AND COLUMN_NAME = 'certificado_disponible_desde';

-- Verificar template insertado
SELECT codigo, nombre, asunto_default
FROM verumax_general.email_templates
WHERE codigo = 'evaluacion_aprobada';

SELECT 'Migracion 009 completada exitosamente!' as resultado;
