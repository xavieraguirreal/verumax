-- =====================================================
-- MIGRACIÓN: Template de Email para Evaluaciones
-- Fecha: 2025-12-22
-- Descripción: Agrega template de notificación de evaluación disponible
-- =====================================================

-- -----------------------------------------------------
-- Paso 1: Modificar ENUM de tipo_email en email_logs
-- -----------------------------------------------------
ALTER TABLE verumax_general.email_logs
MODIFY COLUMN tipo_email ENUM(
    'certificado_disponible',
    'constancia_disponible',
    'bienvenida_curso',
    'recordatorio',
    'notificacion_general',
    'campana_marketing',
    'evaluacion_disponible'
) NOT NULL;

-- -----------------------------------------------------
-- Paso 2: Insertar template de evaluación disponible
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
    'evaluacion_disponible',
    'Evaluación Disponible',
    'Nueva evaluación disponible: {{nombre_evaluacion}}',
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
                                Tenés una nueva evaluación disponible para completar:
                            </p>

                            <!-- Evaluación Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f4ff;border-radius:8px;border-left:4px solid #7c3aed;margin:0 0 25px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="color:#7c3aed;font-size:12px;text-transform:uppercase;letter-spacing:1px;margin:0 0 8px;font-weight:bold;">
                                            {{tipo_evaluacion}}
                                        </p>
                                        <h2 style="color:#333;margin:0 0 10px;font-size:20px;">{{nombre_evaluacion}}</h2>
                                        <p style="color:#666;font-size:14px;margin:0;">
                                            Curso: <strong>{{nombre_curso}}</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Fecha límite (si existe) -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 25px;">
                                <tr>
                                    <td style="background-color:#fef3c7;border-radius:6px;padding:15px;text-align:center;">
                                        <p style="color:#92400e;font-size:14px;margin:0;">
                                            <strong>Fecha límite:</strong> {{fecha_limite}}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#666;font-size:16px;line-height:1.6;margin:0 0 30px;">
                                Ingresá al portal con tu número de documento para acceder a la evaluación.
                            </p>

                            <!-- Button -->
                            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                <tr>
                                    <td style="background-color:#7c3aed;border-radius:6px;">
                                        <a href="{{url_evaluacion}}" style="display:inline-block;padding:15px 30px;color:#ffffff;text-decoration:none;font-weight:bold;font-size:16px;">
                                            Realizar Evaluación
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

Tenés una nueva evaluación disponible para completar:

{{tipo_evaluacion}}: {{nombre_evaluacion}}
Curso: {{nombre_curso}}
Fecha límite: {{fecha_limite}}

Ingresá al portal con tu número de documento para acceder a la evaluación:
{{url_evaluacion}}

{{nombre_institucion}}
Este es un mensaje automático, por favor no respondas a este email.',
    '["nombre_estudiante", "nombre_evaluacion", "tipo_evaluacion", "nombre_curso", "fecha_limite", "url_evaluacion", "url_portal", "nombre_institucion", "logo_url", "color_primario"]',
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
-- Verificación
-- -----------------------------------------------------
SELECT codigo, nombre, asunto_default FROM verumax_general.email_templates WHERE codigo = 'evaluacion_disponible';
