-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 05-12-2025 a las 08:34:41
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
-- Base de datos: `verumax_general`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instances`
--

CREATE TABLE `instances` (
  `id_instancia` int NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la instancia (ej: sajur)',
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de la institución',
  `nombre_completo` text COLLATE utf8mb4_unicode_ci COMMENT 'Nombre completo de la institución',
  `mision` text COLLATE utf8mb4_unicode_ci COMMENT 'Misión o descripción de la institución',
  `sitio_web_oficial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL del sitio web oficial de la institución',
  `email_contacto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email de contacto de la institución',
  `redes_sociales` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON con URLs de redes sociales (instagram, facebook, linkedin, whatsapp, twitter, youtube)',
  `dominio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dominio personalizado',
  `logo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL del logo institucional',
  `color_primario` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#D4AF37' COMMENT 'Color primario global',
  `color_secundario` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color secundario global',
  `color_acento` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#1976D2' COMMENT 'Color de acento global',
  `paleta_colores` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'verde-elegante' COMMENT 'Nombre de la paleta predefinida',
  `identitas_activo` tinyint(1) DEFAULT '1' COMMENT 'Si Identitas está activo (1) o en construcción (0)',
  `modulo_certificatum` tinyint(1) DEFAULT '0' COMMENT 'Si Certificatum está activo',
  `modulo_scripta` tinyint(1) DEFAULT '0' COMMENT 'Si Scripta está activo',
  `modulo_nexus` tinyint(1) DEFAULT '0' COMMENT 'Si Nexus está activo',
  `modulo_vitae` tinyint(1) DEFAULT '0' COMMENT 'Si Vitae está activo',
  `modulo_lumen` tinyint(1) DEFAULT '0' COMMENT 'Si Lumen está activo',
  `modulo_opera` tinyint(1) DEFAULT '0' COMMENT 'Si Opera está activo',
  `plan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'basicum' COMMENT 'Plan contratado (basicum, premium, excellens, supremus)',
  `activo` tinyint(1) DEFAULT '1' COMMENT 'Si la instancia está activa globalmente',
  `admin_usuario` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario del administrador',
  `admin_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contraseña hasheada del administrador',
  `admin_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email del administrador',
  `seo_title` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Título SEO global',
  `seo_description` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción SEO global',
  `seo_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Keywords SEO globales',
  `favicon_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL del favicon generado desde el logo',
  `favicon_generated_at` datetime DEFAULT NULL COMMENT 'Fecha de generación del favicon desde el logo',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sitio_en_construccion` tinyint(1) DEFAULT '0' COMMENT 'Si el sitio está en modo construcción (0=público, 1=en construcción)',
  `mensaje_construccion` text COLLATE utf8mb4_unicode_ci COMMENT 'Mensaje personalizado para página en construcción',
  `robots_noindex` tinyint(1) DEFAULT '1' COMMENT 'Si se debe evitar indexación de buscadores (1=noindex, 0=index)',
  `logo_estilo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'rectangular',
  `logo_mostrar_texto` tinyint(1) DEFAULT '1' COMMENT 'Mostrar nombre de la institución al lado del logo (1=sí, 0=no)',
  `favicon_generado` tinyint(1) DEFAULT '0' COMMENT 'Indica si se generó el favicon (1=sí, 0=no)',
  `openai_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'API Key de OpenAI para autocompletado con IA',
  `openai_habilitado` tinyint(1) DEFAULT '0' COMMENT '1 = IA habilitada, 0 = deshabilitada',
  `ia_habilitada` tinyint(1) DEFAULT '0' COMMENT '1 = IA habilitada para esta institucion, 0 = deshabilitada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración general de instancias - Paleta global y estado de módulos';

--
-- Volcado de datos para la tabla `instances`
--

INSERT INTO `instances` (`id_instancia`, `slug`, `nombre`, `nombre_completo`, `mision`, `sitio_web_oficial`, `email_contacto`, `redes_sociales`, `dominio`, `logo_url`, `color_primario`, `color_secundario`, `color_acento`, `paleta_colores`, `identitas_activo`, `modulo_certificatum`, `modulo_scripta`, `modulo_nexus`, `modulo_vitae`, `modulo_lumen`, `modulo_opera`, `plan`, `activo`, `admin_usuario`, `admin_password`, `admin_email`, `seo_title`, `seo_description`, `seo_keywords`, `favicon_url`, `favicon_generated_at`, `fecha_creacion`, `fecha_actualizacion`, `sitio_en_construccion`, `mensaje_construccion`, `robots_noindex`, `logo_estilo`, `logo_mostrar_texto`, `favicon_generado`, `openai_api_key`, `openai_habilitado`, `ia_habilitada`) VALUES
(1, 'sajur', 'SAJuR', 'Sociedad Argentina de Justicia Restaurativa', 'La Sociedad Argentina de Justicia Restaurativa (SAJuR) es una asociación civil sin fines de lucro, fundada en 2024, con el objetivo de promover, difundir e investigar la Justicia Restaurativa como un nuevo paradigma de respuesta al conflicto y al delito.', '', 'info@sajur.org', '{\"instagram\":\"https:\\/\\/instagram.com\\/sajurargentina\",\"facebook\":\"https:\\/\\/facebook.com\\/sajur\",\"linkedin\":\"https:\\/\\/linkedin.com\\/company\\/sajur\",\"whatsapp\":\"https:\\/\\/wa.me\\/5491112345678\",\"twitter\":\"\",\"youtube\":\"\"}', 'sajur.verumax.com', 'https://verumax.com/uploads/logos/sajur-logo-1764853099.png', '#2e7d32', '#1b5e20', '#66bb6a', 'verde-elegante', 1, 1, 0, 0, 0, 0, 0, 'basicum', 1, 'admin@sajur', '$2y$12$4RSQyKzNic3dwDY240.uA.icnDv7ED47ojPKNWKO24Zr/bLtMOcmu', 'info@sajur.org', 'SAJuR - Sociedad Argentina de Justicia Restaurativ', 'Portal de Certificados de la Sociedad Argentina de Justicia Restaurativa.', 'SAJuR, justicia restaurativa, educación, certificados, Argentina, cursos, formaciones', 'https://verumax.com/identitas/favicons/sajur-favicon-32x32.png', '2025-11-21 16:42:10', '2025-11-16 20:20:01', '2025-12-05 11:18:38', 0, '', 1, 'rectangular-rounded', 0, 1, NULL, 0, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `instances`
--
ALTER TABLE `instances`
  ADD PRIMARY KEY (`id_instancia`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_dominio` (`dominio`),
  ADD KEY `idx_identitas_activo` (`identitas_activo`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_admin_usuario` (`admin_usuario`),
  ADD KEY `idx_openai_habilitado` (`openai_habilitado`),
  ADD KEY `idx_ia_habilitada` (`ia_habilitada`),
  ADD KEY `idx_email_contacto` (`email_contacto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `instances`
--
ALTER TABLE `instances`
  MODIFY `id_instancia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
