-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 05-12-2025 a las 08:34:47
-- Versión del servidor: 8.0.44
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `verumax_identi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identitas_config`
--

CREATE TABLE `identitas_config` (
  `id_config` int NOT NULL,
  `id_instancia` int NOT NULL COMMENT 'FK a verumax_general.instances.id_instancia',
  `identitas_usar_paleta_general` tinyint(1) DEFAULT '1' COMMENT 'Si usa paleta de verumax_general.instances (1) o propia (0)',
  `identitas_paleta_colores_propia` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Paleta predefinida propia (verde-elegante, azul-profesional, etc)',
  `identitas_color_primario_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color primario propio',
  `identitas_color_secundario_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color secundario propio',
  `identitas_color_acento_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color de acento propio',
  `favicon_generado` tinyint(1) DEFAULT '0' COMMENT 'Si se generó el favicon automáticamente',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración específica del módulo Identitas';

--
-- Volcado de datos para la tabla `identitas_config`
--

INSERT INTO `identitas_config` (`id_config`, `id_instancia`, `identitas_usar_paleta_general`, `identitas_paleta_colores_propia`, `identitas_color_primario_propio`, `identitas_color_secundario_propio`, `identitas_color_acento_propio`, `favicon_generado`, `created_at`, `updated_at`) VALUES
(2, 1, 1, NULL, NULL, NULL, NULL, 1, '2025-11-18 15:07:02', '2025-12-04 20:18:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identitas_contactos`
--

CREATE TABLE `identitas_contactos` (
  `id_contacto` int NOT NULL,
  `id_instancia` int NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asunto` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_origen` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `leido` tinyint(1) DEFAULT '0',
  `fecha_contacto` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identitas_contenido_bloques`
--

CREATE TABLE `identitas_contenido_bloques` (
  `id` int NOT NULL,
  `id_instancia` int NOT NULL,
  `pagina` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_bloque` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` json NOT NULL COMMENT 'Contenido estructurado del bloque',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `identitas_contenido_bloques`
--

INSERT INTO `identitas_contenido_bloques` (`id`, `id_instancia`, `pagina`, `tipo_bloque`, `contenido`, `fecha_actualizacion`) VALUES
(1, 1, 'sobre-nosotros', 'mision', '{\"texto\": \"<p>La <strong>Sociedad Argentina de Justicia Restaurativa (SAJuR)</strong> es una asociación civil sin fines de lucro, fundada en 2017, con el objetivo de promover, difundir e investigar la Justicia Restaurativa como un nuevo paradigma de respuesta al conflicto y al delito.</p><p>Buscamos generar espacios de diálogo y formación para la construcción de una sociedad más justa, pacífica e inclusiva, fortaleciendo los lazos comunitarios y reparando el daño a través de la participación de todos los involucrados.</p>\", \"titulo\": \"Nuestra Misiónnn\", \"link_url\": \"https://sajur.org/es/quienes-somos\", \"link_texto\": \"Conoce más sobre nosotros\"}', '2025-11-23 13:25:18'),
(2, 1, 'sobre-nosotros', 'stats', '{\"items\": [{\"texto\": \"Casos resueltos\", \"titulo\": \"1500+\"}, {\"texto\": \"Acuerdos duraderos\", \"titulo\": \"85%\"}, {\"texto\": \"Procesos restaurativos\", \"titulo\": \"2500+\"}, {\"texto\": \"Cursos de formación\", \"titulo\": \"1200+\"}, {\"texto\": \"Participantes activos\", \"titulo\": \"3000+\"}]}', '2025-11-24 16:40:41'),
(3, 1, 'servicios', 'servicios_grid', '{\"items\": [{\"icono\": \"award\", \"texto\": \"Accede a tus certificados académicos con validación QR infalsificable. Sistema de verificación en línea para todos nuestros cursos y programas.\", \"titulo\": \"Certificados Verificados\"}, {\"icono\": \"users\", \"texto\": \"Programas de capacitación para profesionales del derecho, trabajo social, educación y la comunidad en general.\", \"titulo\": \"Formación Continua\"}, {\"icono\": \"book-open\", \"texto\": \"Material de estudio, investigaciones y recursos bibliográficos para tu desarrollo profesional en Justicia Restaurativa.\", \"titulo\": \"Recursos Educativos\"}, {\"icono\": \"lightbulb\", \"texto\": \"Consultoría y acompañamiento en la implementación de prácticas restaurativas en organizaciones e instituciones.\", \"titulo\": \"Asesoramiento\"}, {\"icono\": \"search\", \"texto\": \"Desarrollo de investigaciones académicas y estudios de campo sobre Justicia Restaurativa en Argentina y América Latina.\", \"titulo\": \"Investigación\"}, {\"icono\": \"calendar\", \"texto\": \"Organización de jornadas, seminarios y conferencias nacionales e internacionales sobre Justicia Restaurativa.\", \"titulo\": \"Eventos y Conferenciass\"}], \"subtitulo\": \"Ofrecemos una amplia gama de servicios orientados a la formación, capacitación y promoción de la Justicia Restaurativa.\", \"titulo_seccion\": \"Nuestros Serviciosss\"}', '2025-11-23 13:46:08'),
(4, 1, 'contacto', 'contacto_info', '{\"web\": \"https://sajur.org\", \"email\": \"info@sajur.org\", \"texto\": \"<p>Si tienes problemas para acceder a tus certificados o necesitas contactar con nuestra administración, no dudes en escribirnos.</p>\", \"titulo\": \"Contacto Administrativoo\", \"telefono\": \"\"}', '2025-11-22 20:24:55'),
(23, 1, 'sobre-nosotros', 'mision_vision', '{\"valores\": [{\"icono\": \"star\", \"nombre\": \"Empatía\", \"descripcion\": \"Valoramos la capacidad de entender y compartir los sentimientos de los demás para promover diálogos constructivos y soluciones restaurativas.\"}, {\"icono\": \"lightbulb\", \"nombre\": \"Innovación\", \"descripcion\": \"Fomentamos ideas y enfoques creativos para transformar la justicia y promover soluciones sostenibles y efectivas en la resolución de conflictos.\"}, {\"icono\": \"shield\", \"nombre\": \"Integridad\", \"descripcion\": \"Nos comprometemos a actuar con honestidad y transparencia en todas nuestras acciones para fortalecer la confianza en la justicia restaurativa.\"}], \"mision_texto\": \"<p>La misión de la Sociedad Argentina de Justicia Restaurativa es promover y difundir los principios de la justicia restaurativa en el ámbito educativo, brindando herramientas y conocimientos que favorezcan la resolución pacífica de conflictos. Nos comprometemos a formar profesionales y ciudadanos conscientes de la importancia de la reparación y la inclusión, fomentando un enfoque que prioriza el diálogo y la empatía en la construcción de una sociedad más justa y equitativa. A través de programas académicos, talleres y actividades formativas, buscamos generar un impacto positivo en la comunidad y contribuir al desarrollo de un sistema de justicia más humano y accesible para todos.</p>\", \"vision_texto\": \"<p>Ser una organización líder en Argentina que promueve y fortalece la Justicia Restaurativa como un enfoque efectivo para la resolución de conflictos, fomentando la cultura del diálogo, la reparación y la reintegración social. Aspiramos a transformar la justicia en un proceso más humano, equitativo y restaurador para toda la comunidad.</p>\", \"mision_titulo\": \"Promover la justicia a través de la educación restaurativa\", \"vision_titulo\": \"Nuestra Vision\", \"valores_titulo\": \"Compromiso con la justicia y la paz social.\"}', '2025-11-24 16:13:10'),
(26, 1, 'sobre-nosotros', 'equipo', '{\"titulo\": \"Transformando la Justicia\", \"miembros\": [{\"bio\": \"Especializada en mediación y resolución de conflictos, con más de 10 años de experiencia en justicia restaurativa en Argentina.\", \"foto\": \"\", \"cargo\": \"Coordinadora de Programas\", \"nombre\": \"María Eugenia López\", \"linkedin\": \"\"}, {\"bio\": \"Profesional con amplia experiencia en diseño e implementación de programas de justicia restaurativa en comunidades vulnerables, comprometido con la promoción de soluciones restaurativas en el sistema judicial.\", \"foto\": \"\", \"cargo\": \"Asesor en Políticas de Justicia\", \"nombre\": \"Facundo Ramírez\", \"linkedin\": \"\"}, {\"bio\": \"Abogada especializada en resolución de conflictos y facilitadora de procesos restaurativos con más de 8 años de experiencia en la implementación de programas comunitarios en Argentina.\", \"foto\": \"\", \"cargo\": \"Consultora en Justicia Restaurativa\", \"nombre\": \"Lucía Fernández Gómez\", \"linkedin\": \"\"}], \"subtitulo\": \"Promovemos prácticas restaurativas para fortalecer el tejido social y promover la reparación y el diálogo.\"}', '2025-11-24 16:40:13'),
(30, 1, 'servicios', 'servicios_tabs', '{\"categorias\": [{\"icono\": \"award\", \"nombre\": \"SAJuR\", \"servicios\": \"Uno\\r\\nDos\\r\\nTres\"}, {\"icono\": \"award\", \"nombre\": \"SAJuR\", \"servicios\": \"Uno\\r\\nDos\\r\\nTres\"}]}', '2025-11-23 17:44:36'),
(31, 1, 'servicios', 'testimonios_servicios', '{\"items\": [{\"foto\": \"\", \"cargo\": \"\", \"texto\": \"Somos una institucion comprometida con la excelencia y la innovacion. Trabajamos dia a dia para brindar el mejor servicio a nuestra comunidad.\", \"nombre\": \"SAJuR\"}, {\"foto\": \"\", \"cargo\": \"\", \"texto\": \"\", \"nombre\": \"\"}], \"titulo\": \"\"}', '2025-11-23 17:47:04'),
(32, 1, 'servicios', 'servicios_header', '{\"imagen\": \"https://verumax.com/uploads/ia-images/ia-20251124-174625-6924c4210b6cc.png\", \"titulo\": \"Nuestros Servicios\", \"subtitulo\": \"Soluciones integrales en justicia restaurativa para comunidades y organizaciones.\"}', '2025-11-26 12:23:22'),
(33, 1, 'servicios', 'servicios_accordion', '{\"items\": [{\"icono\": \"handshake\", \"titulo\": \"Mediación y Conciliación\", \"contenido\": \"<p>Nuestra Sociedad Argentina de Justicia Restaurativa ofrece servicios de mediación y conciliación para resolver conflictos de manera pacífica y colaborativa. Promovemos la comunicación y el entendimiento entre las partes, buscando soluciones justas y duraderas que restituyan relaciones y fomenten la convivencia.</p>\", \"descripcion_corta\": \"Facilitamos acuerdos a través de mediaciones efectivas.\"}, {\"icono\": \"shield\", \"titulo\": \"Programas de Justicia Restaurativa\", \"contenido\": \"<p>Nuestros programas están diseñados para facilitar procesos que fomentan la reparación del daño y la reconstrucción de relaciones a través del diálogo respetuoso. Trabajamos con comunidades, instituciones y particulares para implementar prácticas restaurativas que fortalecen el tejido social y generan soluciones sostenibles.</p>\", \"descripcion_corta\": \"Promovemos procesos de reparación y diálogo.\"}, {\"icono\": \"star\", \"titulo\": \"Capacitación en Justicia Restaurativa\", \"contenido\": \"Ofrecemos programas de capacitación diseñados para enseñar técnicas y principios de la justicia restaurativa. Nuestros cursos brindan herramientas prácticas para facilitar procesos reparadores en diferentes contextos sociales y judiciales, promoviendo una cultura de diálogo y reparación.\", \"descripcion_corta\": \"Formamos a profesionales en prácticas restaurativas efectivas.\"}, {\"icono\": \"heart\", \"titulo\": \"Programas de Resolución Comunitaria\", \"contenido\": \"Nuestros programas están diseñados para fortalecer la resolución de conflictos en comunidades mediante prácticas restaurativas. Facilitamos espacios de diálogo y reparación que promueven la cohesión social y la convivencia pacífica, adaptados a las necesidades específicas de cada grupo.\", \"descripcion_corta\": \"Fomentamos soluciones pacíficas en comunidades.\"}]}', '2025-11-26 12:23:56'),
(34, 1, 'servicios', 'servicios_cards', '{\"items\": [{\"link\": \"\", \"icono\": \"handshake\", \"titulo\": \"Mediación Restaurativa\", \"descripcion\": \"Facilitamos diálogos para resolver conflictos y promover la armonía comunitaria.\"}, {\"link\": \"\", \"icono\": \"heart\", \"titulo\": \"Resolución de Conflictos\", \"descripcion\": \"Acompañamos procesos para solucionar disputas mediante diálogo y empatía.\"}, {\"link\": \"\", \"icono\": \"shield\", \"titulo\": \"Restauración Comunitaria\", \"descripcion\": \"Fomentamos la reparación de relaciones y la armonía social mediante procesos restaurativos efectivos.\"}]}', '2025-11-24 22:51:12'),
(37, 1, 'sobre-nosotros', 'hero_institucional', '{\"titulo\": \"Promoviendo la Justicia Restaurativa en la Educación\", \"subtitulo\": \"Promovemos una educación integral en justicia restaurativa, fomentando el diálogo y la reparación del daño en la comunidad. Nuestra misión es formar profesionales comprometidos con la construcción de una sociedad más justa y solidaria. A través de programas académicos y actividades formativas, buscamos transformar el enfoque tradicional del sistema judicial.\", \"imagen_fondo\": \"\", \"mostrar_logo\": \"1\"}', '2025-11-23 20:31:50'),
(47, 1, 'sobre-nosotros', 'areas_investigacion', '{\"areas\": [{\"link\": \"\", \"icono\": \"heart\", \"nombre\": \"Medios de Resolución de Conflictos\", \"descripcion\": \"Estudio y promoción de métodos restaurativos para la resolución de conflictos en la sociedad argentina.\"}, {\"link\": \"\", \"icono\": \"heart\", \"nombre\": \"Mediación Comunitaria\", \"descripcion\": \"Estudia y promueve técnicas de mediación para resolver conflictos en comunidades, fomentando la justicia restaurativa y la convivencia pacífica.\"}], \"titulo\": \"Líneas de Investigación en Justicia Restaurativa\"}', '2025-11-24 12:56:41'),
(48, 1, 'sobre-nosotros', 'intro_academica', '{\"cita\": \"La justicia restaurativa busca sanar las heridas, no solo castigar al culpable, porque solo a través de la reparación podemos construir una sociedad más humana.\", \"texto\": \"<p>La Sociedad Argentina de Justicia Restaurativa es una institución dedicada a promover y fortalecer los principios de la justicia restaurativa en el ámbito académico, social y legal en Argentina. A través de programas de formación, investigación y articulación con actores clave del sistema judicial, busca fomentar una cultura de reparación, diálogo y responsabilidad compartida. Nuestro compromiso es contribuir al desarrollo de prácticas que favorezcan la resolución de conflictos de manera efectiva y humanizada, promoviendo la transformación social y el bienestar comunitario. Con un enfoque interdisciplinario, la institución ofrece recursos y espacios de formación para profesionales, estudiantes y actores sociales interesados en la justicia restaurativa. En nuestra comunidad académica, valoramos la innovación, el respeto por los derechos humanos y la participación activa en la construcción de una sociedad más justa y equitativa.</p>\", \"imagen\": \"\", \"titulo\": \"Compromiso con la Justicia Restaurativa\", \"autor_cita\": \"Howard Zehr\"}', '2025-11-24 11:18:41'),
(49, 1, 'sobre-nosotros', 'publicaciones', '{\"items\": [{\"link\": \"\", \"fecha\": \"2025-11-05\", \"titulo\": \"Innovación y Prácticas en Justicia Restaurativa\", \"autores\": \"García, L., Martínez, P., Sánchez, R.\", \"revista\": \"Revista Internacional de Ciencias Humanas\"}, {\"link\": \"\", \"fecha\": \"2025-11-01\", \"titulo\": \"Publicaciones y Recursos\", \"autores\": \"García, L., Martínez, P., Sánchez, R.\", \"revista\": \"Revista Internacional de Ciencias Sociales\"}, {\"link\": \"\", \"fecha\": \"2025-11-01\", \"titulo\": \"Publicaciones y Recursos Socraticos\", \"autores\": \"García, L., Martínez, P., Sánchez, R.\", \"revista\": \"Revista Internacional de Ciencias Sociales\"}], \"titulo\": \"Publicaciones y Recursos de Justicia\"}', '2025-11-24 12:58:08'),
(50, 1, 'sobre-nosotros', 'reconocimientos', '{\"items\": [{\"anio\": \"2011\", \"imagen\": \"\", \"nombre\": \"Lucía Fernández Morales\", \"otorgante\": \"Instituto Argentino de Reconocimientos y Méritos\"}, {\"anio\": \"2011\", \"imagen\": \"\", \"nombre\": \"Galardón a la Promoción de la Justicia Restaurativa\", \"otorgante\": \"Instituto Argentino de Excelencia Académica\"}], \"titulo\": \"Honores y Reconocimientos Sociedad Argentina\"}', '2025-11-24 13:07:03'),
(72, 1, 'sobre-nosotros', 'intro_historia', '{\"texto\": \"<p>La Sociedad Argentina de Justicia Restaurativa fue fundada con el propósito de promover y fortalecer las prácticas restaurativas en el sistema de justicia del país. Desde sus inicios, ha trabajado para integrar enfoques innovadores que buscan la reparación del daño y la reconciliación entre las partes involucradas. A lo largo de los años, la institución ha consolidado su liderazgo en la implementación de programas y metodologías que fomentan la participación activa de la comunidad y los actores judiciales, contribuyendo a una justicia más humana y efectiva.</p>\", \"titulo\": \"Nuestra Historia\", \"anio_fundacion\": \"2024\"}', '2025-11-24 16:58:20'),
(73, 1, 'sobre-nosotros', 'timeline_vertical', '{\"titulo\": \"Evolución y Logros en Justicia Restaurativa\", \"eventos\": [{\"anio\": \"2018\", \"imagen\": \"https://verumax.com/uploads/ia-images/ia-20251124-152517-6924a30ddb8f1.png\", \"titulo\": \"Primera Conferencia Nacional de Justicia Restaurativa\", \"descripcion\": \"Se realizó la primera conferencia nacional en Argentina, promoviendo el intercambio de experiencias y fortaleciendo la comunidad de practicantes.\"}, {\"anio\": \"2015\", \"imagen\": \"https://verumax.com/uploads/ia-images/ia-20251124-152553-6924a331c024b.png\", \"titulo\": \"Implementación de programas piloto en provincias\", \"descripcion\": \"La Sociedad Argentina de Justicia Restaurativa lanzó programas piloto en varias provincias, consolidando su presencia y promoviendo la integración de prácticas restaurativas en el sistema judicial.\"}, {\"anio\": \"2022\", \"imagen\": \"https://verumax.com/uploads/ia-images/ia-20251124-152627-6924a35384bdd.png\", \"titulo\": \"Creación del Comité de Capacitación Nacional\", \"descripcion\": \"Se estableció un comité dedicado a la formación y certificación de practicantes en justicia restaurativa, fortaleciendo la profesionalización y expansión de la disciplina en Argentina.\"}]}', '2025-11-24 18:26:29'),
(86, 1, 'servicios', 'cta_servicios', '{\"texto\": \"Únete a la Sociedad Argentina de Justicia Restaurativa y construye soluciones pacíficas y duraderas.\", \"estilo\": \"card\", \"titulo\": \"Transforma Conflictos Hoy\", \"boton_url\": \"\", \"boton_texto\": \"¡Participa Ahora!\"}', '2025-11-25 19:31:33'),
(101, 1, 'servicios', 'faq_servicios', '{\"titulo\": \"Innovación y Compromiso en Justicia Restaurativa\", \"preguntas\": [{\"pregunta\": \"¿Qué servicios ofrece la Sociedad Argentina de Justicia Restaurativa?\", \"respuesta\": \"<p>Ofrecemos capacitación, mediación y asesoramiento en procesos de justicia restaurativa para promover la reparación y la convivencia pacífica.</p>\"}, {\"pregunta\": \"¿Cómo puedo acceder a los programas de justicia restaurativa de la Sociedad Argentina de Justicia Restaurativa?\", \"respuesta\": \"<p>Puedes contactarnos a través de nuestra página web o vía telefónica para obtener información sobre los próximos talleres y procesos disponibles.</p>\"}, {\"pregunta\": \"¿Cómo puedo solicitar asesoramiento en justicia restaurativa con la Sociedad Argentina?\", \"respuesta\": \"<p>Puedes comunicarte con nosotros mediante nuestro formulario en línea o por teléfono para recibir orientación personalizada.</p>\"}, {\"pregunta\": \"¿Cuál es el objetivo principal de la Sociedad Argentina de Justicia Restaurativa?\", \"respuesta\": \"Su objetivo es promover la resolución de conflictos a través de métodos restaurativos que fomenten la reparación y la reconciliación.\"}]}', '2025-11-26 12:24:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identitas_instances`
--

CREATE TABLE `identitas_instances` (
  `id_instancia` int NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único (ej: sajur, liberte)',
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de la institución/profesional',
  `nombre_completo` text COLLATE utf8mb4_unicode_ci COMMENT 'Nombre completo o descripción',
  `dominio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dominio personalizado (ej: sajur.verumax.com)',
  `logo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL del logo',
  `color_primario` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#D4AF37' COMMENT 'Color principal (hex)',
  `color_secundario` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color secundario (hex)',
  `configuracion` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON con config adicional (páginas, módulos, etc.)',
  `modulo_certificatum` tinyint(1) DEFAULT '0' COMMENT 'Módulo Certificatum activo',
  `modulo_scripta` tinyint(1) DEFAULT '0' COMMENT 'Módulo Scripta (blog) activo',
  `modulo_nexus` tinyint(1) DEFAULT '0' COMMENT 'Módulo Nexus (CRM) activo',
  `modulo_vitae` tinyint(1) DEFAULT '0' COMMENT 'Módulo Vitae (CV) activo',
  `modulo_lumen` tinyint(1) DEFAULT '0' COMMENT 'Módulo Lumen (portfolio) activo',
  `modulo_opera` tinyint(1) DEFAULT '0' COMMENT 'Módulo Opera (proyectos) activo',
  `plan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'basicum' COMMENT 'Plan: basicum, premium, excellens, supremus',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1',
  `admin_usuario` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario para login del administrador',
  `admin_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Password hasheado (bcrypt)',
  `admin_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email del administrador',
  `certificatum_modo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'seccion' COMMENT 'Modo de integración: seccion (sección en home) o pagina (página independiente)',
  `certificatum_titulo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Certificados' COMMENT 'Título del enlace/sección',
  `certificatum_posicion` int DEFAULT '99' COMMENT 'Posición en el menú (orden)',
  `certificatum_icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'award' COMMENT 'Icono Lucide para el enlace',
  `seo_title` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Título SEO (max 70 caracteres)',
  `seo_description` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción SEO (max 160 caracteres)',
  `seo_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Palabras clave SEO (separadas por comas)',
  `favicon_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL del favicon generado',
  `favicon_generated_at` datetime DEFAULT NULL COMMENT 'Fecha de última generación del favicon',
  `identitas_activo` tinyint(1) DEFAULT '1' COMMENT 'Controla si el módulo Identitas está activo (1) o en construcción (0)',
  `certificatum_descripcion` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción que aparece en la página de certificados',
  `certificatum_cta_texto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Accede a tus certificados' COMMENT 'Texto del botón principal',
  `redes_sociales` json DEFAULT NULL COMMENT 'URLs de redes sociales: instagram, facebook, linkedin, whatsapp, twitter, youtube',
  `certificatum_estadisticas` json DEFAULT NULL COMMENT 'Estadísticas a mostrar: {certificados_emitidos, estudiantes, cursos}',
  `certificatum_mostrar_stats` tinyint(1) DEFAULT '1' COMMENT 'Mostrar sección de estadísticas en página de certificados',
  `color_acento` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#1976D2' COMMENT 'Color de acento para elementos destacados',
  `paleta_colores` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'verde-elegante' COMMENT 'Paleta predefinida: verde-elegante, azul-profesional, morado-creativo, etc',
  `identitas_usar_paleta_general` tinyint(1) DEFAULT '1' COMMENT 'Si es 1 usa paleta de GENERAL, si es 0 usa paleta propia de Identitas',
  `identitas_paleta_colores_propia` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Paleta predefinida propia de Identitas (verde-elegante, azul-profesional, etc)',
  `identitas_color_primario_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color primario propio de Identitas (solo si usar_paleta_general = 0)',
  `identitas_color_secundario_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color secundario propio de Identitas (solo si usar_paleta_general = 0)',
  `identitas_color_acento_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color de acento propio de Identitas (solo si usar_paleta_general = 0)',
  `certificatum_usar_paleta_general` tinyint(1) DEFAULT '1' COMMENT 'Si es 1 usa paleta de GENERAL, si es 0 usa paleta propia de Certificatum',
  `certificatum_paleta_colores_propia` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Paleta predefinida propia de Certificatum (verde-elegante, azul-profesional, etc)',
  `certificatum_color_primario_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color primario propio de Certificatum (solo si usar_paleta_general = 0)',
  `certificatum_color_secundario_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color secundario propio de Certificatum (solo si usar_paleta_general = 0)',
  `certificatum_color_acento_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color de acento propio de Certificatum (solo si usar_paleta_general = 0)',
  `certificatum_features` json DEFAULT NULL COMMENT 'Configuración de las 3 fichas que se muestran: {feature1: {titulo, descripcion, icono}, feature2, feature3}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `identitas_instances`
--

INSERT INTO `identitas_instances` (`id_instancia`, `slug`, `nombre`, `nombre_completo`, `dominio`, `logo_url`, `color_primario`, `color_secundario`, `configuracion`, `modulo_certificatum`, `modulo_scripta`, `modulo_nexus`, `modulo_vitae`, `modulo_lumen`, `modulo_opera`, `plan`, `fecha_creacion`, `fecha_actualizacion`, `activo`, `admin_usuario`, `admin_password`, `admin_email`, `certificatum_modo`, `certificatum_titulo`, `certificatum_posicion`, `certificatum_icono`, `seo_title`, `seo_description`, `seo_keywords`, `favicon_url`, `favicon_generated_at`, `identitas_activo`, `certificatum_descripcion`, `certificatum_cta_texto`, `redes_sociales`, `certificatum_estadisticas`, `certificatum_mostrar_stats`, `color_acento`, `paleta_colores`, `identitas_usar_paleta_general`, `identitas_paleta_colores_propia`, `identitas_color_primario_propio`, `identitas_color_secundario_propio`, `identitas_color_acento_propio`, `certificatum_usar_paleta_general`, `certificatum_paleta_colores_propia`, `certificatum_color_primario_propio`, `certificatum_color_secundario_propio`, `certificatum_color_acento_propio`, `certificatum_features`) VALUES
(1, 'sajur', 'SAJuR', 'Sociedad Argentina de Justicia Restaurativa', 'sajur.verumax.com', '', '#692f7f', '#ad5425', '{\"sitio_web_oficial\":\"https:\\/\\/sajur.org\\/es\\/\",\"email_contacto\":\"info@sajur.org\",\"mision\":\"La Sociedad Argentina de Justicia Restaurativa (SAJuR) es una asociaci\\u00f3n civil sin fines de lucro, fundada en 2024, con el objetivo de promover, difundir e investigar la Justicia Restaurativa como un nuevo paradigma de respuesta al conflicto y al delito.\"}', 1, 0, 0, 0, 0, 0, 'basicum', '2025-11-16 20:20:01', '2025-12-04 11:29:13', 1, 'admin@sajur', '$2y$12$4RSQyKzNic3dwDY240.uA.icnDv7ED47ojPKNWKO24Zr/bLtMOcmu', 'info@sajur.org', 'pagina', 'Certificados', 99, 'award', 'SAJuR - Sociedad Argentina de Justicia Restaurativa', 'Portal oficial de la Sociedad Argentina de Justicia Restaurativa. Accede a certificados, cursos y recursos educativos.', 'SAJuR, justicia restaurativa, educación, certificados, Argentina, cursos', '/identitas/favicons/sajur-favicon-32x32.png', '2025-11-19 17:00:19', 1, 'Accede a tus certificados, constancias y registro académico completo. Descarga tus documentos en formato PDF con\r\n  código QR de validación.', 'Ingresar con mi DNI', '{\"twitter\": \"\", \"youtube\": \"\", \"facebook\": \"https://facebook.com/sajur\", \"linkedin\": \"https://linkedin.com/company/sajur\", \"whatsapp\": \"https://wa.me/5491112345678\", \"instagram\": \"https://instagram.com/sajurargentina\"}', '{\"cursos\": \"15+\", \"estudiantes\": \"300+\", \"certificados_emitidos\": \"500+\"}', 1, '#fdff99', 'verde-elegante', 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, '{\"feature1\": {\"icono\": \"shield-check\", \"titulo\": \"Certificados Verificables\", \"descripcion\": \"Con código QR de validación única y segura\"}, \"feature2\": {\"icono\": \"clock\", \"titulo\": \"Acceso 24/7\", \"descripcion\": \"Disponible en cualquier momento y desde cualquier lugar\"}, \"feature3\": {\"icono\": \"download\", \"titulo\": \"Descarga Inmediata\", \"descripcion\": \"PDF de alta calidad listo para imprimir\"}}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identitas_instancia_templates`
--

CREATE TABLE `identitas_instancia_templates` (
  `id` int NOT NULL,
  `id_instancia` int NOT NULL,
  `pagina` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_template` int NOT NULL,
  `fecha_seleccion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `identitas_instancia_templates`
--

INSERT INTO `identitas_instancia_templates` (`id`, `id_instancia`, `pagina`, `id_template`, `fecha_seleccion`) VALUES
(1, 1, 'sobre-nosotros', 17, '2025-11-24 16:57:59'),
(2, 1, 'servicios', 19, '2025-11-25 19:31:41'),
(3, 1, 'contacto', 7, '2025-11-22 18:12:40'),
(46, 1, 'home', 15, '2025-11-30 16:34:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identitas_paginas`
--

CREATE TABLE `identitas_paginas` (
  `id_pagina` int NOT NULL,
  `id_instancia` int NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL de la página (ej: sobre-nosotros, servicios)',
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Contenido HTML de la página',
  `orden` int DEFAULT '0' COMMENT 'Orden de aparición en menú',
  `visible_menu` tinyint(1) DEFAULT '1' COMMENT 'Mostrar en menú de navegación',
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `identitas_paginas`
--

INSERT INTO `identitas_paginas` (`id_pagina`, `id_instancia`, `slug`, `titulo`, `contenido`, `orden`, `visible_menu`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 'inicio', 'Inicio', '<div class=\"text-center\">\r\n<h1 class=\"text-4xl md:text-5xl font-extrabold leading-tight text-white\">Portal de Certificaci&oacute;n Acad&eacute;mica</h1>\r\n<p class=\"mt-4 text-lg text-gray-300 max-w-2xl mx-auto\">Accede a tu historial, constancias y certificados emitidos por la Sociedad Argentina de Justicia Restaurativa.</p>\r\n</div>', 0, 1, 1, '2025-11-16 20:20:01', '2025-11-19 22:23:21'),
(2, 1, 'sobre-nosotros', 'Sobre Nosotros', '<h2>Nuestra Misión</h2><p>La <strong>Sociedad Argentina de Justicia Restaurativa (SAJuR)</strong> es una asociación civil sin fines de lucro, fundada en 2024, con el objetivo de promover, difundir e investigar la Justicia Restaurativa como un nuevo paradigma de respuesta al conflicto y al delito.</p><p>Buscamos generar espacios de diálogo y formación para la construcción de una sociedad más justa, pacífica e inclusiva, fortaleciendo los lazos comunitarios y reparando el daño a través de la participación de todos los involucrados.</p><p><a href=\"https://sajur.org/es/quienes-somos\">Conoce más sobre nosotros&nbsp;</a></p><p>Visión</p><p>Ser un referente en la promoción de prácticas restaurativas.</p><p>Valores</p><p>Diálogo, Respeto, Inclusión y Reparación.</p><p>Impacto</p><p>Fortalecimiento de la comunidad y la paz social.</p><p>Formación</p><p>Capacitación continua para profesionales y la comunidad.</p>', 1, 1, 1, '2025-11-16 20:20:01', '2025-11-22 17:49:35'),
(3, 1, 'servicios', 'Servicios', '\r\n<div class=\"text-center mb-12\">\r\n    <h2 class=\"text-3xl font-bold text-gray-900 dark:text-white\">Nuestros Servicios</h2>\r\n    <p class=\"mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto\">\r\n        Ofrecemos una amplia gama de servicios orientados a la formación, capacitación y promoción de la Justicia Restaurativa.\r\n    </p>\r\n</div>\r\n\r\n<div class=\"grid md:grid-cols-3 gap-8\">\r\n    <!-- Certificados Verificados -->\r\n    <div class=\"bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700\">\r\n        <div class=\"w-12 h-12 rounded-lg flex items-center justify-center mb-4\" style=\"background-color: var(--color-primario); opacity: 0.1;\">\r\n            <i data-lucide=\"award\" class=\"w-6 h-6\" style=\"color: var(--color-primario);\"></i>\r\n        </div>\r\n        <h3 class=\"text-xl font-bold text-gray-900 dark:text-white mb-2\">Certificados Verificados</h3>\r\n        <p class=\"text-gray-600 dark:text-gray-400\">\r\n            Accede a tus certificados académicos con validación QR infalsificable. Sistema de verificación en línea para todos nuestros cursos y programas.\r\n        </p>\r\n    </div>\r\n\r\n    <!-- Formación Continua -->\r\n    <div class=\"bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700\">\r\n        <div class=\"w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4\">\r\n            <i data-lucide=\"users\" class=\"w-6 h-6 text-blue-600 dark:text-blue-400\"></i>\r\n        </div>\r\n        <h3 class=\"text-xl font-bold text-gray-900 dark:text-white mb-2\">Formación Continua</h3>\r\n        <p class=\"text-gray-600 dark:text-gray-400\">\r\n            Programas de capacitación para profesionales del derecho, trabajo social, educación y la comunidad en general.\r\n        </p>\r\n    </div>\r\n\r\n    <!-- Recursos Educativos -->\r\n    <div class=\"bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700\">\r\n        <div class=\"w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4\">\r\n            <i data-lucide=\"book-open\" class=\"w-6 h-6 text-purple-600 dark:text-purple-400\"></i>\r\n        </div>\r\n        <h3 class=\"text-xl font-bold text-gray-900 dark:text-white mb-2\">Recursos Educativos</h3>\r\n        <p class=\"text-gray-600 dark:text-gray-400\">\r\n            Material de estudio, investigaciones y recursos bibliográficos para tu desarrollo profesional en Justicia Restaurativa.\r\n        </p>\r\n    </div>\r\n\r\n    <!-- Asesoramiento -->\r\n    <div class=\"bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700\">\r\n        <div class=\"w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mb-4\">\r\n            <i data-lucide=\"lightbulb\" class=\"w-6 h-6 text-orange-600 dark:text-orange-400\"></i>\r\n        </div>\r\n        <h3 class=\"text-xl font-bold text-gray-900 dark:text-white mb-2\">Asesoramiento</h3>\r\n        <p class=\"text-gray-600 dark:text-gray-400\">\r\n            Consultoría y acompañamiento en la implementación de prácticas restaurativas en organizaciones e instituciones.\r\n        </p>\r\n    </div>\r\n\r\n    <!-- Investigación -->\r\n    <div class=\"bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700\">\r\n        <div class=\"w-12 h-12 bg-pink-100 dark:bg-pink-900 rounded-lg flex items-center justify-center mb-4\">\r\n            <i data-lucide=\"search\" class=\"w-6 h-6 text-pink-600 dark:text-pink-400\"></i>\r\n        </div>\r\n        <h3 class=\"text-xl font-bold text-gray-900 dark:text-white mb-2\">Investigación</h3>\r\n        <p class=\"text-gray-600 dark:text-gray-400\">\r\n            Desarrollo de investigaciones académicas y estudios de campo sobre Justicia Restaurativa en Argentina y América Latina.\r\n        </p>\r\n    </div>\r\n\r\n    <!-- Eventos y Conferencias -->\r\n    <div class=\"bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700\">\r\n        <div class=\"w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mb-4\">\r\n            <i data-lucide=\"calendar\" class=\"w-6 h-6 text-indigo-600 dark:text-indigo-400\"></i>\r\n        </div>\r\n        <h3 class=\"text-xl font-bold text-gray-900 dark:text-white mb-2\">Eventos y Conferencias</h3>\r\n        <p class=\"text-gray-600 dark:text-gray-400\">\r\n            Organización de jornadas, seminarios y conferencias nacionales e internacionales sobre Justicia Restaurativa.\r\n        </p>\r\n    </div>\r\n</div>\r\n    ', 2, 1, 1, '2025-11-16 20:20:01', '2025-11-22 15:23:13'),
(4, 1, 'contacto', 'Contacto', '\r\n<div class=\"text-center mb-12\">\r\n    <i data-lucide=\"mail\" class=\"w-12 h-12 mx-auto\" style=\"color: var(--color-primario);\"></i>\r\n    <h2 class=\"text-3xl font-bold text-gray-900 dark:text-white mt-4\">Contacto Administrativo</h2>\r\n    <p class=\"mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto\">\r\n        Si tienes problemas para acceder a tus certificados o necesitas contactar con nuestra administración, no dudes en escribirnos.\r\n    </p>\r\n</div>\r\n\r\n<div class=\"max-w-2xl mx-auto\">\r\n    <div class=\"bg-gray-50 dark:bg-gray-800 rounded-lg p-8 mb-8\">\r\n        <h3 class=\"text-xl font-bold text-gray-900 dark:text-white mb-4\">Información de Contacto</h3>\r\n        <div class=\"space-y-3\">\r\n            <div class=\"flex items-center gap-3 text-gray-700 dark:text-gray-300\">\r\n                <i data-lucide=\"mail\" class=\"w-5 h-5\" style=\"color: var(--color-primario);\"></i>\r\n                <a href=\"mailto:info@sajur.org\" class=\"hover:underline transition\" style=\"hover:color: var(--color-primario);\">\r\n                    info@sajur.org\r\n                </a>\r\n            </div>\r\n            <div class=\"flex items-center gap-3 text-gray-700 dark:text-gray-300\">\r\n                <i data-lucide=\"globe\" class=\"w-5 h-5\" style=\"color: var(--color-primario);\"></i>\r\n                <a href=\"https://sajur.org/es/\" target=\"_blank\" class=\"hover:underline transition\" style=\"hover:color: var(--color-primario);\">\r\n                    www.sajur.org\r\n                </a>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>\r\n    ', 3, 1, 1, '2025-11-16 20:20:01', '2025-11-22 15:23:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identitas_tarjetas`
--

CREATE TABLE `identitas_tarjetas` (
  `id_tarjeta` int NOT NULL,
  `id_instancia` int NOT NULL,
  `nombre_persona` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre en la tarjeta',
  `cargo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cargo/Título en la tarjeta',
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `redes_sociales` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON con enlaces a redes sociales',
  `qr_code_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL del QR generado',
  `qr_destino` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL a donde apunta el QR',
  `imagen_tarjeta_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL de la tarjeta JPG generada',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identitas_templates`
--

CREATE TABLE `identitas_templates` (
  `id_template` int NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `thumbnail_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pagina` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'sobre-nosotros, servicios, contacto, home',
  `activo` tinyint(1) DEFAULT '1',
  `orden` int DEFAULT '0',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `identitas_templates`
--

INSERT INTO `identitas_templates` (`id_template`, `slug`, `nombre`, `descripcion`, `thumbnail_url`, `pagina`, `activo`, `orden`, `fecha_creacion`) VALUES
(1, 'clasico', 'Clásico', 'Misión a la izquierda, estadísticas en grid 2x2 a la derecha', NULL, 'sobre-nosotros', 1, 1, '2025-11-22 18:12:40'),
(2, 'moderno', 'Moderno', 'Estadísticas en 4 columnas arriba, misión centrada abajo', NULL, 'sobre-nosotros', 1, 2, '2025-11-22 18:12:40'),
(3, 'minimal', 'Minimal', 'Solo texto de misión centrado, sin estadísticas', NULL, 'sobre-nosotros', 1, 3, '2025-11-22 18:12:40'),
(4, 'grid-3col', 'Grid 3 Columnas', 'Servicios en grid de 3 columnas con iconos', NULL, 'servicios', 1, 1, '2025-11-22 18:12:40'),
(5, 'grid-2col', 'Grid 2 Columnas', 'Servicios en grid de 2 columnas más grandes', NULL, 'servicios', 1, 2, '2025-11-22 18:12:40'),
(6, 'lista', 'Lista Vertical', 'Servicios en lista vertical con descripciones largas', NULL, 'servicios', 1, 3, '2025-11-22 18:12:40'),
(7, 'formulario-info', 'Formulario + Info', 'Formulario de contacto con información al lado', NULL, 'contacto', 1, 1, '2025-11-22 18:12:40'),
(8, 'solo-formulario', 'Solo Formulario', 'Formulario centrado sin información adicional', NULL, 'contacto', 1, 2, '2025-11-22 18:12:40'),
(15, 'corporativo', 'Corporativo', 'Diseño formal e institucional con énfasis en credenciales, trayectoria y profesionalismo. Ideal para instituciones establecidas.', '/assets/templates/sobre-nosotros-corporativo.png', 'sobre-nosotros', 1, 4, '2025-11-23 16:21:01'),
(16, 'academico', 'Académico', 'Enfocado en educación, investigación y logros académicos. Incluye secciones para publicaciones y reconocimientos.', '/assets/templates/sobre-nosotros-academico.png', 'sobre-nosotros', 1, 5, '2025-11-23 16:21:01'),
(17, 'timeline', 'Línea de Tiempo', 'Presenta la historia y evolución de la institución en formato cronológico. Perfecto para mostrar hitos y crecimiento.', '/assets/templates/sobre-nosotros-timeline.png', 'sobre-nosotros', 1, 6, '2025-11-23 16:21:01'),
(18, 'cards-iconos', 'Cards con Iconos', 'Tarjetas elegantes con iconos destacados y efectos hover. Diseño limpio y profesional.', '/assets/templates/servicios-cards-iconos.png', 'servicios', 1, 4, '2025-11-23 16:21:01'),
(19, 'lista-detallada', 'Lista Detallada', 'Formato de lista expandible con descripciones completas. Ideal para servicios que requieren explicación detallada.', '/assets/templates/servicios-lista-detallada.png', 'servicios', 1, 5, '2025-11-23 16:21:01'),
(20, 'tabs', 'Con Pestañas', 'Organiza servicios en pestañas interactivas. Útil cuando hay muchos servicios o categorías diferentes.', '/assets/templates/servicios-tabs.png', 'servicios', 1, 6, '2025-11-23 16:21:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identitas_template_bloques`
--

CREATE TABLE `identitas_template_bloques` (
  `id` int NOT NULL,
  `id_template` int NOT NULL,
  `tipo_bloque` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'hero, mision, stats_2x2, servicios_grid, etc',
  `orden` int DEFAULT '0',
  `config` json DEFAULT NULL COMMENT 'Configuración específica del bloque'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `identitas_template_bloques`
--

INSERT INTO `identitas_template_bloques` (`id`, `id_template`, `tipo_bloque`, `orden`, `config`) VALUES
(1, 1, 'mision_con_stats', 1, '{\"layout\": \"left-right\", \"stats_cols\": 2, \"stats_rows\": 2}'),
(2, 2, 'stats_horizontal', 1, '{\"cols\": 4}'),
(3, 2, 'mision_centrada', 2, '{\"ancho\": \"full\"}'),
(4, 3, 'mision_centrada', 1, '{\"ancho\": \"narrow\"}'),
(5, 4, 'servicios_grid', 1, '{\"cols\": 3, \"con_iconos\": true}'),
(6, 5, 'servicios_grid', 1, '{\"cols\": 2, \"con_iconos\": true}'),
(7, 6, 'servicios_lista', 1, '{\"con_iconos\": true}'),
(8, 7, 'contacto_info', 1, '{\"mostrar_info\": true}'),
(9, 7, 'contacto_formulario', 2, '{}'),
(10, 8, 'contacto_formulario', 1, '{\"centrado\": true}'),
(11, 15, 'hero-institucional', 1, '{\"style\": \"formal\", \"show_logo\": true}'),
(12, 15, 'mision-vision', 2, '{\"icons\": true, \"columns\": 2}'),
(13, 15, 'equipo', 3, '{\"layout\": \"grid\", \"show_roles\": true}'),
(14, 15, 'stats', 4, '{\"style\": \"boxes\", \"animated\": true}'),
(15, 16, 'intro_academica', 1, '{\"style\": \"scholar\"}'),
(16, 16, 'areas_investigacion', 2, '{\"layout\": \"cards\"}'),
(17, 16, 'publicaciones', 3, '{\"limit\": 6, \"show_dates\": true}'),
(18, 16, 'reconocimientos', 4, '{\"style\": \"badges\"}'),
(19, 17, 'intro_historia', 1, '{\"style\": \"centered\"}'),
(20, 17, 'timeline_vertical', 2, '{\"animated\": true, \"show_images\": true}'),
(21, 17, 'stats', 3, '{\"style\": \"inline\", \"prefix\": \"Hoy:\"}'),
(22, 18, 'servicios_header', 1, '{\"style\": \"centered\"}'),
(23, 18, 'servicios_cards', 2, '{\"columns\": 3, \"icon_size\": \"large\", \"hover_effect\": \"lift\"}'),
(24, 18, 'cta_servicios', 3, '{\"style\": \"banner\"}'),
(25, 19, 'servicios_header', 1, '{\"style\": \"left-aligned\"}'),
(26, 19, 'servicios_accordion', 2, '{\"expandable\": true, \"show_pricing\": false}'),
(27, 19, 'faq_servicios', 3, '{\"limit\": 5}'),
(28, 20, 'servicios_header', 1, '{\"style\": \"minimal\"}'),
(29, 20, 'servicios_tabs', 2, '{\"tab_style\": \"pills\", \"content_style\": \"cards\"}'),
(30, 20, 'testimonios_servicios', 3, '{\"layout\": \"carousel\"}');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `identitas_config`
--
ALTER TABLE `identitas_config`
  ADD PRIMARY KEY (`id_config`),
  ADD UNIQUE KEY `unique_instance` (`id_instancia`),
  ADD KEY `idx_usar_paleta_general` (`identitas_usar_paleta_general`);

--
-- Indices de la tabla `identitas_contactos`
--
ALTER TABLE `identitas_contactos`
  ADD PRIMARY KEY (`id_contacto`),
  ADD KEY `idx_instancia` (`id_instancia`),
  ADD KEY `idx_leido` (`leido`),
  ADD KEY `idx_fecha` (`fecha_contacto`);

--
-- Indices de la tabla `identitas_contenido_bloques`
--
ALTER TABLE `identitas_contenido_bloques`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_contenido` (`id_instancia`,`pagina`,`tipo_bloque`);

--
-- Indices de la tabla `identitas_instances`
--
ALTER TABLE `identitas_instances`
  ADD PRIMARY KEY (`id_instancia`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_dominio` (`dominio`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_admin_usuario` (`admin_usuario`);

--
-- Indices de la tabla `identitas_instancia_templates`
--
ALTER TABLE `identitas_instancia_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_instancia_pagina` (`id_instancia`,`pagina`),
  ADD KEY `id_template` (`id_template`);

--
-- Indices de la tabla `identitas_paginas`
--
ALTER TABLE `identitas_paginas`
  ADD PRIMARY KEY (`id_pagina`),
  ADD UNIQUE KEY `unique_instance_slug` (`id_instancia`,`slug`),
  ADD KEY `idx_instancia` (`id_instancia`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_orden` (`orden`);

--
-- Indices de la tabla `identitas_tarjetas`
--
ALTER TABLE `identitas_tarjetas`
  ADD PRIMARY KEY (`id_tarjeta`),
  ADD KEY `idx_instancia` (`id_instancia`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `identitas_templates`
--
ALTER TABLE `identitas_templates`
  ADD PRIMARY KEY (`id_template`),
  ADD UNIQUE KEY `unique_template_pagina` (`slug`,`pagina`);

--
-- Indices de la tabla `identitas_template_bloques`
--
ALTER TABLE `identitas_template_bloques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_template` (`id_template`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `identitas_config`
--
ALTER TABLE `identitas_config`
  MODIFY `id_config` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `identitas_contactos`
--
ALTER TABLE `identitas_contactos`
  MODIFY `id_contacto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `identitas_contenido_bloques`
--
ALTER TABLE `identitas_contenido_bloques`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `identitas_instances`
--
ALTER TABLE `identitas_instances`
  MODIFY `id_instancia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `identitas_instancia_templates`
--
ALTER TABLE `identitas_instancia_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `identitas_paginas`
--
ALTER TABLE `identitas_paginas`
  MODIFY `id_pagina` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `identitas_tarjetas`
--
ALTER TABLE `identitas_tarjetas`
  MODIFY `id_tarjeta` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `identitas_templates`
--
ALTER TABLE `identitas_templates`
  MODIFY `id_template` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `identitas_template_bloques`
--
ALTER TABLE `identitas_template_bloques`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `identitas_contactos`
--
ALTER TABLE `identitas_contactos`
  ADD CONSTRAINT `identitas_contactos_ibfk_1` FOREIGN KEY (`id_instancia`) REFERENCES `identitas_instances` (`id_instancia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `identitas_contenido_bloques`
--
ALTER TABLE `identitas_contenido_bloques`
  ADD CONSTRAINT `identitas_contenido_bloques_ibfk_1` FOREIGN KEY (`id_instancia`) REFERENCES `identitas_instances` (`id_instancia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `identitas_instancia_templates`
--
ALTER TABLE `identitas_instancia_templates`
  ADD CONSTRAINT `identitas_instancia_templates_ibfk_1` FOREIGN KEY (`id_instancia`) REFERENCES `identitas_instances` (`id_instancia`) ON DELETE CASCADE,
  ADD CONSTRAINT `identitas_instancia_templates_ibfk_2` FOREIGN KEY (`id_template`) REFERENCES `identitas_templates` (`id_template`);

--
-- Filtros para la tabla `identitas_paginas`
--
ALTER TABLE `identitas_paginas`
  ADD CONSTRAINT `identitas_paginas_ibfk_1` FOREIGN KEY (`id_instancia`) REFERENCES `identitas_instances` (`id_instancia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `identitas_tarjetas`
--
ALTER TABLE `identitas_tarjetas`
  ADD CONSTRAINT `identitas_tarjetas_ibfk_1` FOREIGN KEY (`id_instancia`) REFERENCES `identitas_instances` (`id_instancia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `identitas_template_bloques`
--
ALTER TABLE `identitas_template_bloques`
  ADD CONSTRAINT `identitas_template_bloques_ibfk_1` FOREIGN KEY (`id_template`) REFERENCES `identitas_templates` (`id_template`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
