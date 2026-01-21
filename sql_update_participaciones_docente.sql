-- ============================================================
-- SQL para actualizar servidor remoto
-- Ejecutar en phpMyAdmin
-- Fecha: 2025-12-17
-- ============================================================

-- ============================================================
-- PARTE 1: ACTUALIZAR TABLA participaciones_docente
-- ============================================================

-- 1. Agregar campo estado a participaciones_docente
ALTER TABLE verumax_certifi.participaciones_docente
ADD COLUMN estado ENUM('Asignado', 'En curso', 'Completado') NOT NULL DEFAULT 'Asignado'
AFTER rol;

-- 2. Actualizar registros existentes según lógica de fechas
-- Si fecha_fin es pasada y certificado_emitido = 1 → Completado
-- Si fecha_inicio es pasada pero fecha_fin es futura o NULL → En curso
-- Resto → Asignado (ya es el default)

UPDATE verumax_certifi.participaciones_docente
SET estado = 'Completado'
WHERE certificado_emitido = 1
   OR (fecha_fin IS NOT NULL AND fecha_fin < CURDATE());

UPDATE verumax_certifi.participaciones_docente
SET estado = 'En curso'
WHERE estado = 'Asignado'
  AND fecha_inicio IS NOT NULL
  AND fecha_inicio <= CURDATE()
  AND (fecha_fin IS NULL OR fecha_fin >= CURDATE());

-- 3. Verificar resultado
SELECT estado, COUNT(*) as cantidad
FROM verumax_certifi.participaciones_docente
GROUP BY estado;

-- ============================================================
-- PARTE 2: AGREGAR TEMPLATES DE EMAIL PARA DOCENTES
-- ============================================================

-- Template: Docente asignado a curso
INSERT INTO verumax_general.email_templates
(codigo, nombre, asunto_default, contenido_html, variables_disponibles, activo)
VALUES (
    'docente_asignado',
    'Docente Asignado a Curso',
    'Has sido asignado como formador en {{nombre_curso}}',
    '<html><body style="font-family:Arial;background:#f4f4f4;padding:20px;"><div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;"><div style="background:#166534;padding:30px;text-align:center;"><img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;"></div><div style="padding:30px;"><h1 style="color:#333;">Hola, {{nombre_docente}}</h1><p style="color:#666;font-size:16px;">Te informamos que has sido asignado/a como <strong>{{rol}}</strong> en el curso:</p><div style="background:#f8f9fa;padding:20px;border-radius:6px;margin:20px 0;"><p style="margin:5px 0;color:#333;"><strong>Curso:</strong> {{nombre_curso}}</p><p style="margin:5px 0;color:#333;"><strong>Fecha inicio:</strong> {{fecha_inicio}}</p><p style="margin:5px 0;color:#333;"><strong>Fecha fin:</strong> {{fecha_fin}}</p></div><p style="color:#666;">Podrás acceder a tu certificado de participación una vez finalizado el curso desde nuestro portal.</p><p style="text-align:center;margin:30px 0;"><a href="{{url_portal}}" style="background:#166534;color:#fff;padding:15px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">Ir al Portal</a></p></div><div style="text-align:center;padding:20px;color:#999;font-size:12px;border-top:1px solid #eee;">{{nombre_institucion}}<br>Este es un mensaje automático.</div></div></body></html>',
    '["nombre_docente","nombre_curso","rol","fecha_inicio","fecha_fin","url_portal","logo_url","nombre_institucion"]',
    1
);

-- Template: Certificado docente disponible
INSERT INTO verumax_general.email_templates
(codigo, nombre, asunto_default, contenido_html, variables_disponibles, activo)
VALUES (
    'certificado_docente_disponible',
    'Certificado Docente Disponible',
    'Tu certificado de participación en {{nombre_curso}} está listo',
    '<html><body style="font-family:Arial;background:#f4f4f4;padding:20px;"><div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;"><div style="background:#166534;padding:30px;text-align:center;"><img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;"></div><div style="padding:30px;"><h1 style="color:#333;">¡Felicitaciones, {{nombre_docente}}!</h1><p style="color:#666;font-size:16px;">Tu certificado de participación como <strong>{{rol}}</strong> en el curso <strong>{{nombre_curso}}</strong> ya está disponible para descargar.</p><p style="color:#666;">Podés acceder a tu certificado ingresando a nuestro portal con tu número de documento.</p><p style="text-align:center;margin:30px 0;"><a href="{{url_portal}}" style="background:#166534;color:#fff;padding:15px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">Descargar Certificado</a></p></div><div style="text-align:center;padding:20px;color:#999;font-size:12px;border-top:1px solid #eee;">{{nombre_institucion}}<br>Este es un mensaje automático.</div></div></body></html>',
    '["nombre_docente","nombre_curso","rol","url_portal","logo_url","nombre_institucion"]',
    1
);

-- Verificar templates creados
SELECT codigo, nombre FROM verumax_general.email_templates WHERE codigo LIKE 'docente%' OR codigo LIKE 'certificado_docente%';
