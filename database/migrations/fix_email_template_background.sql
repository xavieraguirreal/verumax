-- =====================================================
-- FIX: Template de email con fondo correcto
-- Fecha: 2025-12-15
-- Problema: El contenido del email se veía con fondo verde
-- Solución: Agregar background-color explícito al área de contenido
-- =====================================================

USE verumax_general;

-- Primero verificar el template actual
SELECT id_template, codigo, nombre,
       LEFT(contenido_html, 500) as preview_html
FROM email_templates
WHERE codigo = 'constancia_disponible';

-- Actualizar el template con fondo blanco explícito en el contenido
UPDATE email_templates
SET contenido_html = '<!DOCTYPE html>
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
                        <td style="background-color:#ffffff;padding:40px 30px;">
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
updated_at = NOW()
WHERE codigo = 'constancia_disponible' AND id_instancia IS NULL;

-- También actualizar el template de certificado_disponible con la misma corrección
UPDATE email_templates
SET contenido_html = '<!DOCTYPE html>
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
                        <td style="background-color:#ffffff;padding:40px 30px;">
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
updated_at = NOW()
WHERE codigo = 'certificado_disponible' AND id_instancia IS NULL;

-- Verificar los cambios
SELECT id_template, codigo, updated_at FROM email_templates WHERE codigo IN ('constancia_disponible', 'certificado_disponible');
