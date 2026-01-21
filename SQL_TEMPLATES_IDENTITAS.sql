-- ============================================================================
-- SISTEMA DE TEMPLATES + BLOQUES PARA IDENTITAS
-- ============================================================================
-- Fecha: 2025-11-22
-- Base de datos: verumax_identi
-- Descripción: Permite a cada institución elegir templates y editar contenido
--              de forma estructurada sin romper el diseño
-- ============================================================================

USE verumax_identi;

-- ============================================================================
-- PASO 1: CREAR TABLAS
-- ============================================================================

-- Tabla de templates disponibles
CREATE TABLE IF NOT EXISTS identitas_templates (
    id_template INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    thumbnail_url VARCHAR(255),
    pagina VARCHAR(50) NOT NULL COMMENT 'sobre-nosotros, servicios, contacto, home',
    activo TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_template_pagina (slug, pagina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de bloques que componen cada template
CREATE TABLE IF NOT EXISTS identitas_template_bloques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_template INT NOT NULL,
    tipo_bloque VARCHAR(50) NOT NULL COMMENT 'hero, mision, stats_2x2, servicios_grid, etc',
    orden INT DEFAULT 0,
    config JSON COMMENT 'Configuración específica del bloque',
    FOREIGN KEY (id_template) REFERENCES identitas_templates(id_template) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de qué template eligió cada instancia para cada página
CREATE TABLE IF NOT EXISTS identitas_instancia_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    pagina VARCHAR(50) NOT NULL,
    id_template INT NOT NULL,
    fecha_seleccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_instancia_pagina (id_instancia, pagina),
    FOREIGN KEY (id_instancia) REFERENCES identitas_instances(id_instancia) ON DELETE CASCADE,
    FOREIGN KEY (id_template) REFERENCES identitas_templates(id_template)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de contenido editado por cada cliente para cada bloque
CREATE TABLE IF NOT EXISTS identitas_contenido_bloques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    pagina VARCHAR(50) NOT NULL,
    tipo_bloque VARCHAR(50) NOT NULL,
    contenido JSON NOT NULL COMMENT 'Contenido estructurado del bloque',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_contenido (id_instancia, pagina, tipo_bloque),
    FOREIGN KEY (id_instancia) REFERENCES identitas_instances(id_instancia) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PASO 2: INSERTAR TEMPLATES DISPONIBLES
-- ============================================================================

-- Templates para "Sobre Nosotros"
INSERT INTO identitas_templates (slug, nombre, descripcion, pagina, orden) VALUES
('clasico', 'Clásico', 'Misión a la izquierda, estadísticas en grid 2x2 a la derecha', 'sobre-nosotros', 1),
('moderno', 'Moderno', 'Estadísticas en 4 columnas arriba, misión centrada abajo', 'sobre-nosotros', 2),
('minimal', 'Minimal', 'Solo texto de misión centrado, sin estadísticas', 'sobre-nosotros', 3);

-- Templates para "Servicios"
INSERT INTO identitas_templates (slug, nombre, descripcion, pagina, orden) VALUES
('grid-3col', 'Grid 3 Columnas', 'Servicios en grid de 3 columnas con iconos', 'servicios', 1),
('grid-2col', 'Grid 2 Columnas', 'Servicios en grid de 2 columnas más grandes', 'servicios', 2),
('lista', 'Lista Vertical', 'Servicios en lista vertical con descripciones largas', 'servicios', 3);

-- Templates para "Contacto"
INSERT INTO identitas_templates (slug, nombre, descripcion, pagina, orden) VALUES
('formulario-info', 'Formulario + Info', 'Formulario de contacto con información al lado', 'contacto', 1),
('solo-formulario', 'Solo Formulario', 'Formulario centrado sin información adicional', 'contacto', 2);

-- ============================================================================
-- PASO 3: DEFINIR BLOQUES DE CADA TEMPLATE
-- ============================================================================

-- Bloques del template "Clásico" para Sobre Nosotros
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config) VALUES
((SELECT id_template FROM identitas_templates WHERE slug = 'clasico' AND pagina = 'sobre-nosotros'),
 'mision_con_stats', 1, '{"layout": "left-right", "stats_cols": 2, "stats_rows": 2}');

-- Bloques del template "Moderno" para Sobre Nosotros
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config) VALUES
((SELECT id_template FROM identitas_templates WHERE slug = 'moderno' AND pagina = 'sobre-nosotros'),
 'stats_horizontal', 1, '{"cols": 4}'),
((SELECT id_template FROM identitas_templates WHERE slug = 'moderno' AND pagina = 'sobre-nosotros'),
 'mision_centrada', 2, '{"ancho": "full"}');

-- Bloques del template "Minimal" para Sobre Nosotros
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config) VALUES
((SELECT id_template FROM identitas_templates WHERE slug = 'minimal' AND pagina = 'sobre-nosotros'),
 'mision_centrada', 1, '{"ancho": "narrow"}');

-- Bloques del template "Grid 3 Columnas" para Servicios
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config) VALUES
((SELECT id_template FROM identitas_templates WHERE slug = 'grid-3col' AND pagina = 'servicios'),
 'servicios_grid', 1, '{"cols": 3, "con_iconos": true}');

-- Bloques del template "Grid 2 Columnas" para Servicios
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config) VALUES
((SELECT id_template FROM identitas_templates WHERE slug = 'grid-2col' AND pagina = 'servicios'),
 'servicios_grid', 1, '{"cols": 2, "con_iconos": true}');

-- Bloques del template "Lista" para Servicios
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config) VALUES
((SELECT id_template FROM identitas_templates WHERE slug = 'lista' AND pagina = 'servicios'),
 'servicios_lista', 1, '{"con_iconos": true}');

-- Bloques del template "Formulario + Info" para Contacto
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config) VALUES
((SELECT id_template FROM identitas_templates WHERE slug = 'formulario-info' AND pagina = 'contacto'),
 'contacto_info', 1, '{"mostrar_info": true}'),
((SELECT id_template FROM identitas_templates WHERE slug = 'formulario-info' AND pagina = 'contacto'),
 'contacto_formulario', 2, '{}');

-- Bloques del template "Solo Formulario" para Contacto
INSERT INTO identitas_template_bloques (id_template, tipo_bloque, orden, config) VALUES
((SELECT id_template FROM identitas_templates WHERE slug = 'solo-formulario' AND pagina = 'contacto'),
 'contacto_formulario', 1, '{"centrado": true}');

-- ============================================================================
-- PASO 4: ASIGNAR TEMPLATES INICIALES A SAJUR
-- ============================================================================

INSERT INTO identitas_instancia_templates (id_instancia, pagina, id_template)
SELECT
    (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur'),
    'sobre-nosotros',
    (SELECT id_template FROM identitas_templates WHERE slug = 'clasico' AND pagina = 'sobre-nosotros');

INSERT INTO identitas_instancia_templates (id_instancia, pagina, id_template)
SELECT
    (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur'),
    'servicios',
    (SELECT id_template FROM identitas_templates WHERE slug = 'grid-3col' AND pagina = 'servicios');

INSERT INTO identitas_instancia_templates (id_instancia, pagina, id_template)
SELECT
    (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur'),
    'contacto',
    (SELECT id_template FROM identitas_templates WHERE slug = 'formulario-info' AND pagina = 'contacto');

-- ============================================================================
-- PASO 5: MIGRAR CONTENIDO ACTUAL DE SAJUR A NUEVO FORMATO
-- ============================================================================

-- Contenido de "Sobre Nosotros" - Bloque Misión
INSERT INTO identitas_contenido_bloques (id_instancia, pagina, tipo_bloque, contenido)
SELECT
    (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur'),
    'sobre-nosotros',
    'mision',
    JSON_OBJECT(
        'titulo', 'Nuestra Misión',
        'texto', '<p>La <strong>Sociedad Argentina de Justicia Restaurativa (SAJuR)</strong> es una asociación civil sin fines de lucro, fundada en 2017, con el objetivo de promover, difundir e investigar la Justicia Restaurativa como un nuevo paradigma de respuesta al conflicto y al delito.</p><p>Buscamos generar espacios de diálogo y formación para la construcción de una sociedad más justa, pacífica e inclusiva, fortaleciendo los lazos comunitarios y reparando el daño a través de la participación de todos los involucrados.</p>',
        'link_texto', 'Conoce más sobre nosotros',
        'link_url', 'https://sajur.org/es/quienes-somos'
    );

-- Contenido de "Sobre Nosotros" - Bloque Stats
INSERT INTO identitas_contenido_bloques (id_instancia, pagina, tipo_bloque, contenido)
SELECT
    (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur'),
    'sobre-nosotros',
    'stats',
    JSON_OBJECT(
        'items', JSON_ARRAY(
            JSON_OBJECT('titulo', 'Visión', 'texto', 'Ser un referente en la promoción de prácticas restaurativas.'),
            JSON_OBJECT('titulo', 'Valores', 'texto', 'Diálogo, Respeto, Inclusión y Reparación.'),
            JSON_OBJECT('titulo', 'Impacto', 'texto', 'Fortalecimiento de la comunidad y la paz social.'),
            JSON_OBJECT('titulo', 'Formación', 'texto', 'Capacitación continua para profesionales y la comunidad.')
        )
    );

-- Contenido de "Servicios"
INSERT INTO identitas_contenido_bloques (id_instancia, pagina, tipo_bloque, contenido)
SELECT
    (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur'),
    'servicios',
    'servicios_grid',
    JSON_OBJECT(
        'titulo_seccion', 'Nuestros Servicios',
        'subtitulo', 'Ofrecemos una amplia gama de servicios orientados a la formación, capacitación y promoción de la Justicia Restaurativa.',
        'items', JSON_ARRAY(
            JSON_OBJECT('icono', 'award', 'titulo', 'Certificados Verificados', 'texto', 'Accede a tus certificados académicos con validación QR infalsificable. Sistema de verificación en línea para todos nuestros cursos y programas.'),
            JSON_OBJECT('icono', 'users', 'titulo', 'Formación Continua', 'texto', 'Programas de capacitación para profesionales del derecho, trabajo social, educación y la comunidad en general.'),
            JSON_OBJECT('icono', 'book-open', 'titulo', 'Recursos Educativos', 'texto', 'Material de estudio, investigaciones y recursos bibliográficos para tu desarrollo profesional en Justicia Restaurativa.'),
            JSON_OBJECT('icono', 'lightbulb', 'titulo', 'Asesoramiento', 'texto', 'Consultoría y acompañamiento en la implementación de prácticas restaurativas en organizaciones e instituciones.'),
            JSON_OBJECT('icono', 'search', 'titulo', 'Investigación', 'texto', 'Desarrollo de investigaciones académicas y estudios de campo sobre Justicia Restaurativa en Argentina y América Latina.'),
            JSON_OBJECT('icono', 'calendar', 'titulo', 'Eventos y Conferencias', 'texto', 'Organización de jornadas, seminarios y conferencias nacionales e internacionales sobre Justicia Restaurativa.')
        )
    );

-- Contenido de "Contacto"
INSERT INTO identitas_contenido_bloques (id_instancia, pagina, tipo_bloque, contenido)
SELECT
    (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur'),
    'contacto',
    'contacto_info',
    JSON_OBJECT(
        'titulo', 'Contacto Administrativo',
        'texto', 'Si tienes problemas para acceder a tus certificados o necesitas contactar con nuestra administración, no dudes en escribirnos.',
        'email', 'info@sajur.org',
        'web', 'https://sajur.org',
        'telefono', ''
    );

-- ============================================================================
-- VERIFICAR RESULTADOS
-- ============================================================================

-- Ver templates creados
SELECT * FROM identitas_templates ORDER BY pagina, orden;

-- Ver bloques de cada template
SELECT t.nombre as template, t.pagina, tb.tipo_bloque, tb.orden
FROM identitas_templates t
JOIN identitas_template_bloques tb ON t.id_template = tb.id_template
ORDER BY t.pagina, t.orden, tb.orden;

-- Ver templates asignados a SAJuR
SELECT it.pagina, t.nombre as template
FROM identitas_instancia_templates it
JOIN identitas_templates t ON it.id_template = t.id_template
WHERE it.id_instancia = (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur');

-- Ver contenido migrado
SELECT pagina, tipo_bloque, LEFT(contenido, 100) as preview
FROM identitas_contenido_bloques
WHERE id_instancia = (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur');

-- ============================================================================
-- NOTAS
-- ============================================================================
--
-- Después de ejecutar este SQL:
-- 1. Las tablas estarán creadas
-- 2. SAJuR tendrá asignados los templates "Clásico", "Grid 3 Col" y "Formulario + Info"
-- 3. El contenido actual estará migrado al nuevo formato JSON
-- 4. El siguiente paso es crear los archivos PHP de renderizado
--
-- ============================================================================
