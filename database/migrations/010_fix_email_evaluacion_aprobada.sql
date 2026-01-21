-- =====================================================
-- MIGRACION: Corregir template de evaluacion aprobada
-- Fecha: 2025-12-30
-- Descripcion: Elimina mensaje de "te enviaremos un email"
--              ya que no se envia segundo email automatico
-- =====================================================

UPDATE verumax_general.email_templates
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
                                            Tu certificado estara disponible a partir del<br>
                                            <strong style="font-size:18px;">{{fecha_disponibilidad}}</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#666;font-size:16px;line-height:1.6;margin:0 0 30px;">
                                Una vez cumplido el plazo, podras acceder a tu certificado desde el portal.
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
contenido_texto = 'Felicitaciones, {{nombre_estudiante}}

Has completado exitosamente la evaluacion del curso:
{{nombre_curso}}

Nuestro equipo docente realizara una revision pedagogica de tus respuestas como parte del proceso de certificacion academica.

Tu certificado estara disponible a partir del {{fecha_disponibilidad}}.

Una vez cumplido el plazo, podras acceder a tu certificado desde el portal:
{{url_portal}}

{{nombre_institucion}}
Este es un mensaje automatico, por favor no respondas a este email.',
updated_at = CURRENT_TIMESTAMP
WHERE codigo = 'evaluacion_aprobada';

-- Verificacion
SELECT codigo, nombre, updated_at FROM verumax_general.email_templates WHERE codigo = 'evaluacion_aprobada';
