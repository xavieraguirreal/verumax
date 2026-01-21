-- =====================================================
-- MIGRACIÓN: Agregar tipo_documento a templates de email
-- Fecha: 2025-12-22
-- Descripción: Actualiza templates para mostrar el tipo específico
--              de documento (Constancia de Inscripción, de Finalización, etc.)
-- =====================================================

USE verumax_general;

-- -----------------------------------------------------
-- Actualizar template de CONSTANCIA
-- -----------------------------------------------------
UPDATE email_templates
SET
    asunto_default = 'Tu {{tipo_documento}} del curso {{nombre_curso}} está lista',
    contenido_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,{{color_primario}},#1a365d);padding:30px;text-align:center;">
                            <img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;margin-bottom:15px;">
                            <h1 style="color:#ffffff;margin:0;font-size:24px;">{{nombre_institucion}}</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 30px;">
                            <p style="font-size:18px;color:#333;margin:0 0 20px;">Hola, <strong>{{nombre_estudiante}}</strong></p>
                            <p style="font-size:16px;color:#555;line-height:1.6;margin:0 0 25px;">
                                Tu <strong>{{tipo_documento}}</strong> del curso <strong>{{nombre_curso}}</strong> ya está disponible para descargar.
                            </p>
                            <p style="font-size:16px;color:#555;line-height:1.6;margin:0 0 30px;">
                                Podés acceder ingresando a nuestro portal con tu número de documento.
                            </p>
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{url_portal}}" style="display:inline-block;background:linear-gradient(135deg,{{color_primario}},#1a365d);color:#ffffff;text-decoration:none;padding:15px 40px;border-radius:8px;font-weight:bold;font-size:16px;">
                                            Acceder al Portal
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8f9fa;padding:20px 30px;text-align:center;border-top:1px solid #eee;">
                            <p style="font-size:12px;color:#888;margin:0;">
                                Este es un mensaje automático de {{nombre_institucion}}.<br>
                                Por favor no responda a este correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
    contenido_texto = 'Hola, {{nombre_estudiante}}. Tu {{tipo_documento}} del curso {{nombre_curso}} está disponible. Accedé: {{url_portal}}',
    variables_disponibles = '["nombre_estudiante","nombre_curso","url_portal","nombre_institucion","logo_url","color_primario","tipo_documento"]'
WHERE codigo = 'constancia_disponible' AND id_instancia IS NULL;

-- -----------------------------------------------------
-- Actualizar template de CERTIFICADO
-- -----------------------------------------------------
UPDATE email_templates
SET
    asunto_default = 'Tu {{tipo_documento}} del curso {{nombre_curso}} está listo',
    contenido_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header con badge de felicitaciones -->
                    <tr>
                        <td style="background:linear-gradient(135deg,{{color_primario}},#1a365d);padding:30px;text-align:center;">
                            <img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;margin-bottom:15px;">
                            <h1 style="color:#ffffff;margin:0;font-size:24px;">{{nombre_institucion}}</h1>
                        </td>
                    </tr>
                    <!-- Felicitaciones -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#fbbf24,#f59e0b);padding:15px;text-align:center;">
                            <p style="color:#ffffff;margin:0;font-size:18px;font-weight:bold;">🎉 ¡Felicitaciones! 🎉</p>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 30px;">
                            <p style="font-size:18px;color:#333;margin:0 0 20px;">Hola, <strong>{{nombre_estudiante}}</strong></p>
                            <p style="font-size:16px;color:#555;line-height:1.6;margin:0 0 25px;">
                                Tu <strong>{{tipo_documento}}</strong> del curso <strong>{{nombre_curso}}</strong> ya está disponible para descargar.
                            </p>
                            <p style="font-size:16px;color:#555;line-height:1.6;margin:0 0 30px;">
                                Podés acceder ingresando a nuestro portal con tu número de documento.
                            </p>
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{url_portal}}" style="display:inline-block;background:linear-gradient(135deg,{{color_primario}},#1a365d);color:#ffffff;text-decoration:none;padding:15px 40px;border-radius:8px;font-weight:bold;font-size:16px;">
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
                            <p style="font-size:12px;color:#888;margin:0;">
                                Este es un mensaje automático de {{nombre_institucion}}.<br>
                                Por favor no responda a este correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
    contenido_texto = '¡Felicitaciones, {{nombre_estudiante}}! Tu {{tipo_documento}} del curso {{nombre_curso}} está disponible. Accedé: {{url_portal}}',
    variables_disponibles = '["nombre_estudiante","nombre_curso","url_portal","nombre_institucion","logo_url","color_primario","tipo_documento"]'
WHERE codigo = 'certificado_disponible' AND id_instancia IS NULL;

-- -----------------------------------------------------
-- Verificar cambios
-- -----------------------------------------------------
SELECT id_template, codigo, asunto_default, updated_at
FROM email_templates
WHERE codigo IN ('constancia_disponible', 'certificado_disponible');
