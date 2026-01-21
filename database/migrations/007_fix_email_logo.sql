-- =====================================================
-- MIGRACIÓN: Arreglar logo en templates de email
-- Fecha: 2025-12-22
-- Descripción: Usa fondo sólido en el header para que el logo
--              con transparencia se vea correctamente
-- =====================================================

USE verumax_general;

-- -----------------------------------------------------
-- Actualizar template de CONSTANCIA - Header con fondo sólido
-- -----------------------------------------------------
UPDATE email_templates
SET
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
                    <!-- Header con fondo sólido -->
                    <tr>
                        <td style="background-color:{{color_primario}};padding:30px;text-align:center;">
                            <img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:80px;max-width:200px;">
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
                                        <a href="{{url_portal}}" style="display:inline-block;background-color:{{color_primario}};color:#ffffff;text-decoration:none;padding:15px 40px;border-radius:8px;font-weight:bold;font-size:16px;">
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
</html>'
WHERE codigo = 'constancia_disponible' AND id_instancia IS NULL;

-- -----------------------------------------------------
-- Actualizar template de CERTIFICADO - Header con fondo sólido
-- -----------------------------------------------------
UPDATE email_templates
SET
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
                    <!-- Header con fondo sólido -->
                    <tr>
                        <td style="background-color:{{color_primario}};padding:30px;text-align:center;">
                            <img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:80px;max-width:200px;">
                        </td>
                    </tr>
                    <!-- Felicitaciones -->
                    <tr>
                        <td style="background-color:#fbbf24;padding:12px;text-align:center;">
                            <p style="color:#78350f;margin:0;font-size:16px;font-weight:bold;">🎉 ¡Felicitaciones! 🎉</p>
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
                                        <a href="{{url_portal}}" style="display:inline-block;background-color:{{color_primario}};color:#ffffff;text-decoration:none;padding:15px 40px;border-radius:8px;font-weight:bold;font-size:16px;">
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
</html>'
WHERE codigo = 'certificado_disponible' AND id_instancia IS NULL;

-- Verificar
SELECT id_template, codigo, updated_at FROM email_templates
WHERE codigo IN ('constancia_disponible', 'certificado_disponible');
