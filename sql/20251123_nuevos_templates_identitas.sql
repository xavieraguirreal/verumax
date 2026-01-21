-- =====================================================
-- NUEVOS TEMPLATES PARA IDENTITAS
-- Fecha: 2025-11-23
-- Ejecutar en: verumax_identi (phpMyAdmin)
-- =====================================================

-- =====================================================
-- TEMPLATES PARA "SOBRE NOSOTROS" (3 nuevos)
-- =====================================================

-- Template 4: Corporativo
INSERT INTO identitas_templates (pagina, slug, nombre, descripcion, thumbnail_url, activo, orden)
VALUES (
    'sobre-nosotros',
    'corporativo',
    'Corporativo',
    'Diseño formal e institucional con énfasis en credenciales, trayectoria y profesionalismo. Ideal para instituciones establecidas.',
    '/assets/templates/sobre-nosotros-corporativo.png',
    1,
    4
);

-- Template 5: Académico
INSERT INTO identitas_templates (pagina, slug, nombre, descripcion, thumbnail_url, activo, orden)
VALUES (
    'sobre-nosotros',
    'academico',
    'Académico',
    'Enfocado en educación, investigación y logros académicos. Incluye secciones para publicaciones y reconocimientos.',
    '/assets/templates/sobre-nosotros-academico.png',
    1,
    5
);

-- Template 6: Timeline
INSERT INTO identitas_templates (pagina, slug, nombre, descripcion, thumbnail_url, activo, orden)
VALUES (
    'sobre-nosotros',
    'timeline',
    'Línea de Tiempo',
    'Presenta la historia y evolución de la institución en formato cronológico. Perfecto para mostrar hitos y crecimiento.',
    '/assets/templates/sobre-nosotros-timeline.png',
    1,
    6
);

-- =====================================================
-- TEMPLATES PARA "SERVICIOS" (3 nuevos)
-- =====================================================

-- Template 4: Cards con Iconos
INSERT INTO identitas_templates (pagina, slug, nombre, descripcion, thumbnail_url, activo, orden)
VALUES (
    'servicios',
    'cards-iconos',
    'Cards con Iconos',
    'Tarjetas elegantes con iconos destacados y efectos hover. Diseño limpio y profesional.',
    '/assets/templates/servicios-cards-iconos.png',
    1,
    4
);

-- Template 5: Lista Detallada
INSERT INTO identitas_templates (pagina, slug, nombre, descripcion, thumbnail_url, activo, orden)
VALUES (
    'servicios',
    'lista-detallada',
    'Lista Detallada',
    'Formato de lista expandible con descripciones completas. Ideal para servicios que requieren explicación detallada.',
    '/assets/templates/servicios-lista-detallada.png',
    1,
    5
);

-- Template 6: Tabs/Pestañas
INSERT INTO identitas_templates (pagina, slug, nombre, descripcion, thumbnail_url, activo, orden)
VALUES (
    'servicios',
    'tabs',
    'Con Pestañas',
    'Organiza servicios en pestañas interactivas. Útil cuando hay muchos servicios o categorías diferentes.',
    '/assets/templates/servicios-tabs.png',
    1,
    6
);

-- =====================================================
-- BLOQUES PARA LOS NUEVOS TEMPLATES
-- =====================================================

-- Obtener IDs de los nuevos templates y crear sus bloques
-- NOTA: Ajustar los id_template según los valores generados

-- Bloques para Corporativo (sobre-nosotros)
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'hero_institucional', 1, '{"style": "formal", "show_logo": true}'
FROM identitas_templates WHERE slug = 'corporativo' AND pagina = 'sobre-nosotros';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'mision_vision', 2, '{"columns": 2, "icons": true}'
FROM identitas_templates WHERE slug = 'corporativo' AND pagina = 'sobre-nosotros';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'equipo', 3, '{"layout": "grid", "show_roles": true}'
FROM identitas_templates WHERE slug = 'corporativo' AND pagina = 'sobre-nosotros';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'stats', 4, '{"style": "boxes", "animated": true}'
FROM identitas_templates WHERE slug = 'corporativo' AND pagina = 'sobre-nosotros';

-- Bloques para Académico (sobre-nosotros)
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'intro_academica', 1, '{"style": "scholar"}'
FROM identitas_templates WHERE slug = 'academico' AND pagina = 'sobre-nosotros';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'areas_investigacion', 2, '{"layout": "cards"}'
FROM identitas_templates WHERE slug = 'academico' AND pagina = 'sobre-nosotros';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'publicaciones', 3, '{"limit": 6, "show_dates": true}'
FROM identitas_templates WHERE slug = 'academico' AND pagina = 'sobre-nosotros';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'reconocimientos', 4, '{"style": "badges"}'
FROM identitas_templates WHERE slug = 'academico' AND pagina = 'sobre-nosotros';

-- Bloques para Timeline (sobre-nosotros)
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'intro_historia', 1, '{"style": "centered"}'
FROM identitas_templates WHERE slug = 'timeline' AND pagina = 'sobre-nosotros';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'timeline_vertical', 2, '{"animated": true, "show_images": true}'
FROM identitas_templates WHERE slug = 'timeline' AND pagina = 'sobre-nosotros';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'stats', 3, '{"style": "inline", "prefix": "Hoy:"}'
FROM identitas_templates WHERE slug = 'timeline' AND pagina = 'sobre-nosotros';

-- Bloques para Cards con Iconos (servicios)
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'servicios_header', 1, '{"style": "centered"}'
FROM identitas_templates WHERE slug = 'cards-iconos' AND pagina = 'servicios';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'servicios_cards', 2, '{"columns": 3, "hover_effect": "lift", "icon_size": "large"}'
FROM identitas_templates WHERE slug = 'cards-iconos' AND pagina = 'servicios';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'cta_servicios', 3, '{"style": "banner"}'
FROM identitas_templates WHERE slug = 'cards-iconos' AND pagina = 'servicios';

-- Bloques para Lista Detallada (servicios)
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'servicios_header', 1, '{"style": "left-aligned"}'
FROM identitas_templates WHERE slug = 'lista-detallada' AND pagina = 'servicios';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'servicios_accordion', 2, '{"expandable": true, "show_pricing": false}'
FROM identitas_templates WHERE slug = 'lista-detallada' AND pagina = 'servicios';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'faq_servicios', 3, '{"limit": 5}'
FROM identitas_templates WHERE slug = 'lista-detallada' AND pagina = 'servicios';

-- Bloques para Tabs (servicios)
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'servicios_header', 1, '{"style": "minimal"}'
FROM identitas_templates WHERE slug = 'tabs' AND pagina = 'servicios';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'servicios_tabs', 2, '{"tab_style": "pills", "content_style": "cards"}'
FROM identitas_templates WHERE slug = 'tabs' AND pagina = 'servicios';

INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config)
SELECT id_template, 'testimonios_servicios', 3, '{"layout": "carousel"}'
FROM identitas_templates WHERE slug = 'tabs' AND pagina = 'servicios';

-- =====================================================
-- VERIFICACIÓN
-- =====================================================
-- Ejecutar después para verificar:
-- SELECT pagina, slug, nombre FROM identitas_templates ORDER BY pagina, orden;
