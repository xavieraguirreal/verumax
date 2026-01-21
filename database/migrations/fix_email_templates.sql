USE verumax_general;

-- Borrar templates existentes
DELETE FROM email_templates;

-- Insertar templates corregidos con UTF-8
INSERT INTO email_templates (id_instancia, codigo, nombre, asunto_default, contenido_html, contenido_texto, variables_disponibles, es_sistema) VALUES
(NULL, 'certificado_disponible', 'Certificado Disponible',
'Tu certificado de {{nombre_curso}} está listo',
'<html><body style="font-family:Arial;background:#f4f4f4;padding:20px;"><div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;"><div style="background:#166534;padding:30px;text-align:center;"><img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;"></div><div style="padding:30px;"><h1 style="color:#333;">¡Felicitaciones, {{nombre_estudiante}}!</h1><p style="color:#666;font-size:16px;">Tu certificado del curso <strong>{{nombre_curso}}</strong> ya está disponible para descargar.</p><p style="color:#666;">Podés acceder a tu certificado ingresando a nuestro portal con tu número de documento.</p><p style="text-align:center;margin:30px 0;"><a href="{{url_portal}}" style="background:#166534;color:#fff;padding:15px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">Descargar Certificado</a></p></div><div style="text-align:center;padding:20px;color:#999;font-size:12px;border-top:1px solid #eee;">{{nombre_institucion}}<br>Este es un mensaje automático.</div></div></body></html>',
'¡Felicitaciones, {{nombre_estudiante}}! Tu certificado de {{nombre_curso}} está disponible. Accedé: {{url_portal}}',
'["nombre_estudiante","nombre_curso","url_portal","nombre_institucion","logo_url"]', 1),

(NULL, 'constancia_disponible', 'Constancia Disponible',
'Tu constancia de {{nombre_curso}} está lista',
'<html><body style="font-family:Arial;background:#f4f4f4;padding:20px;"><div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;"><div style="background:#166534;padding:30px;text-align:center;"><img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;"></div><div style="padding:30px;"><h1 style="color:#333;">Hola, {{nombre_estudiante}}</h1><p style="color:#666;font-size:16px;">Tu constancia del curso <strong>{{nombre_curso}}</strong> ya está disponible para descargar.</p><p style="color:#666;">Podés acceder ingresando a nuestro portal con tu número de documento.</p><p style="text-align:center;margin:30px 0;"><a href="{{url_portal}}" style="background:#166534;color:#fff;padding:15px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">Ver Constancia</a></p></div><div style="text-align:center;padding:20px;color:#999;font-size:12px;border-top:1px solid #eee;">{{nombre_institucion}}<br>Este es un mensaje automático.</div></div></body></html>',
'Hola, {{nombre_estudiante}}. Tu constancia de {{nombre_curso}} está disponible. Accedé: {{url_portal}}',
'["nombre_estudiante","nombre_curso","url_portal","nombre_institucion","logo_url"]', 1);
