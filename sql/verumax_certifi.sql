-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 05-12-2025 a las 08:34:27
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
-- Base de datos: `verumax_certifi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificatum_config`
--

CREATE TABLE `certificatum_config` (
  `id_config` int NOT NULL,
  `id_instancia` int NOT NULL COMMENT 'FK a verumax_general.instances.id_instancia',
  `certificatum_usar_paleta_general` tinyint(1) DEFAULT '1' COMMENT 'Si usa paleta de verumax_general.instances (1) o propia (0)',
  `certificatum_paleta_colores_propia` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Paleta predefinida propia (verde-elegante, azul-profesional, etc)',
  `certificatum_color_primario_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color primario propio',
  `certificatum_color_secundario_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color secundario propio',
  `certificatum_color_acento_propio` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color de acento propio',
  `certificatum_modo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'seccion' COMMENT 'Modo de integración: seccion o pagina',
  `certificatum_titulo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Certificados' COMMENT 'Título que aparece en el menú',
  `certificatum_icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'award' COMMENT 'Nombre del icono Lucide',
  `certificatum_posicion` int DEFAULT '99' COMMENT 'Posición en el menú de navegación',
  `certificatum_descripcion` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción que aparece en la página de certificados',
  `certificatum_cta_texto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Ver mis certificados' COMMENT 'Texto del botón principal',
  `certificatum_estadisticas` json DEFAULT NULL COMMENT 'Estadísticas a mostrar (certificados_emitidos, estudiantes, cursos)',
  `certificatum_mostrar_stats` tinyint(1) DEFAULT '1' COMMENT 'Si se muestran las estadísticas en el portal',
  `certificatum_features` json DEFAULT NULL COMMENT 'Configuración de las 3 fichas que se muestran: {feature1: {titulo, descripcion, icono}, feature2, feature3}',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración específica del módulo Certificatum';

--
-- Volcado de datos para la tabla `certificatum_config`
--

INSERT INTO `certificatum_config` (`id_config`, `id_instancia`, `certificatum_usar_paleta_general`, `certificatum_paleta_colores_propia`, `certificatum_color_primario_propio`, `certificatum_color_secundario_propio`, `certificatum_color_acento_propio`, `certificatum_modo`, `certificatum_titulo`, `certificatum_icono`, `certificatum_posicion`, `certificatum_descripcion`, `certificatum_cta_texto`, `certificatum_estadisticas`, `certificatum_mostrar_stats`, `certificatum_features`, `created_at`, `updated_at`) VALUES
(2, 1, 1, NULL, NULL, NULL, NULL, 'pagina', 'Certificados', 'award', 99, 'Accede a tus certificados, constancias y registro académico completo. Descarga tus documentos en formato PDF con\r\n  código QR de validación.', 'Ingresar con mi DNI', '{\"cursos\": \"15+\", \"estudiantes\": \"300+\", \"certificados_emitidos\": \"500+\"}', 1, '{\"feature1\": {\"icono\": \"shield-check\", \"titulo\": \"Certificados Verificables\", \"descripcion\": \"Con código QR de validación única y segura\"}, \"feature2\": {\"icono\": \"clock\", \"titulo\": \"Acceso 24/7\", \"descripcion\": \"Disponible en cualquier momento y desde cualquier lugar\"}, \"feature3\": {\"icono\": \"download\", \"titulo\": \"Descarga Inmediata\", \"descripcion\": \"PDF de alta calidad listo para imprimir\"}}', '2025-11-18 15:07:02', '2025-12-04 12:21:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos_validacion`
--

CREATE TABLE `codigos_validacion` (
  `id_validacion` int NOT NULL,
  `institucion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_curso` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_validacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: VALID-xxxxxxxxxxxxx',
  `fecha_generacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo_documento` enum('analitico','certificado_aprobacion','constancia_regular','constancia_finalizacion','constancia_inscripcion') COLLATE utf8mb4_unicode_ci DEFAULT 'certificado_aprobacion',
  `veces_consultado` int DEFAULT '0',
  `ultima_consulta` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Códigos de validación para certificados y documentos';

--
-- Volcado de datos para la tabla `codigos_validacion`
--

INSERT INTO `codigos_validacion` (`id_validacion`, `institucion`, `dni`, `codigo_curso`, `codigo_validacion`, `fecha_generacion`, `tipo_documento`, `veces_consultado`, `ultima_consulta`) VALUES
(1, 'sajur', '25123456', 'SJ-DPA-2024', 'VALID-2FEC50EE4FA1', '2025-11-16 13:46:47', 'analitico', 2, '2025-11-21 19:36:09'),
(6, 'sajur', '25123456', 'SJ-AJ-2023', 'VALID-D8271C5B19EB', '2025-11-16 15:21:21', 'certificado_aprobacion', 1, '2025-11-17 17:43:07'),
(7, 'sajur', '30987654', 'SJ-CM-2024', 'VALID-7F1C16F88065', '2025-11-16 15:21:51', 'analitico', 0, NULL),
(8, 'sajur', '30987654', 'SJ-IDP-2023', 'VALID-511CE1E73323', '2025-11-16 15:22:14', 'constancia_finalizacion', 0, NULL),
(9, 'sajur', '42555888', 'SJ-OPA-2025', 'VALID-EB172680DCEC', '2025-11-16 15:22:35', 'analitico', 1, '2025-11-16 16:08:15'),
(14, 'sajur', '33456789', 'SJ-DPA-2024', 'VALID-35A49570EBE0', '2025-11-17 18:38:48', 'analitico', 0, NULL),
(16, 'sajur', '30987654', 'SJ-MED-2024', 'VALID-B349C3521FB3', '2025-11-21 19:49:50', 'analitico', 7, '2025-11-27 14:45:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `competencias_curso`
--

CREATE TABLE `competencias_curso` (
  `id_competencia` int NOT NULL,
  `id_inscripcion` int NOT NULL,
  `competencia` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Competencias adquiridas por estudiante en cada curso';

--
-- Volcado de datos para la tabla `competencias_curso`
--

INSERT INTO `competencias_curso` (`id_competencia`, `id_inscripcion`, `competencia`, `orden`) VALUES
(3, 2, 'Retórica', 0),
(4, 2, 'Lógica Jurídica', 1),
(5, 3, 'Contratos Digitales', 0),
(6, 3, 'Fideicomisos', 1),
(7, 4, 'Teoría del Delito', 0),
(8, 5, 'Comunicación No Verbal', 0),
(9, 5, 'Storytelling', 1),
(16, 1, 'Mediación', 1),
(17, 1, 'Facilitación de Círculos', 2),
(18, 1, 'Prácticas Restaurativas', 3),
(19, 6, 'Mediación', 1),
(20, 6, 'Comunicación No Violenta', 2),
(21, 6, 'Resolución de Conflictos', 3),
(22, 7, 'Círculos Restaurativos', 1),
(23, 7, 'Facilitación de Grupos', 2),
(24, 7, 'Escucha Activa', 3),
(25, 8, 'Mediación', 1),
(26, 8, 'Facilitación', 2),
(27, 8, 'Prácticas Restaurativas', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id_curso` int NOT NULL,
  `institucion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_curso` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: SJ-DPA-2024',
  `nombre_curso` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `carga_horaria` int DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cursos/formaciones por institución';

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_curso`, `institucion`, `codigo_curso`, `nombre_curso`, `carga_horaria`, `descripcion`, `fecha_creacion`, `activo`) VALUES
(1, 'sajur', 'SJ-DPA-2024', 'Diplomatura en Prácticas de Justicia Restaurativa', 90, NULL, '2025-11-15 00:03:46', 1),
(2, 'sajur', 'SJ-AJ-2023', 'Argumentación Jurídica', 60, NULL, '2025-11-15 00:03:46', 1),
(3, 'sajur', 'SJ-CM-2024', 'Contratos Modernos', 80, NULL, '2025-11-15 00:03:46', 1),
(4, 'sajur', 'SJ-IDP-2023', 'Introducción al Derecho Penal', 70, NULL, '2025-11-15 00:03:46', 1),
(5, 'sajur', 'SJ-OPA-2025', 'Oratoria y Persuasión para Abogados', 40, NULL, '2025-11-15 00:03:46', 1),
(6, 'sajur', 'SJ-MED-2024', 'Curso de Mediación Comunitaria', 60, NULL, '2025-11-16 18:45:34', 1),
(7, 'sajur', 'SJ-CIR-2025', 'Facilitación de Círculos Restaurativos', 40, NULL, '2025-11-16 19:04:41', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id_estudiante` int NOT NULL,
  `institucion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'sajur, liberte, etc.',
  `dni` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_completo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Estudiantes registrados por institución';

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id_estudiante`, `institucion`, `dni`, `nombre_completo`, `email`, `fecha_registro`) VALUES
(1, 'sajur', '25123456', 'Alejandro Rodriguez', NULL, '2025-11-15 00:03:46'),
(2, 'sajur', '30987654', 'Sofía Gómez', NULL, '2025-11-15 00:03:46'),
(3, 'sajur', '42555888', 'Martín Lopez', NULL, '2025-11-15 00:03:46'),
(4, 'sajur', '33456789', 'María Fernández', NULL, '2025-11-16 19:04:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id_inscripcion` int NOT NULL,
  `id_estudiante` int NOT NULL,
  `id_curso` int NOT NULL,
  `estado` enum('Por Iniciar','En Curso','Finalizado','Aprobado','Desaprobado') COLLATE utf8mb4_unicode_ci DEFAULT 'Por Iniciar',
  `fecha_inscripcion` date DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `nota_final` decimal(4,2) DEFAULT NULL COMMENT 'Nota de 0 a 10',
  `asistencia` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ej: 98%'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Inscripciones de estudiantes a cursos';

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id_inscripcion`, `id_estudiante`, `id_curso`, `estado`, `fecha_inscripcion`, `fecha_inicio`, `fecha_finalizacion`, `nota_final`, `asistencia`) VALUES
(1, 1, 1, 'Aprobado', NULL, '2024-03-01', '2024-09-15', 9.50, '98%'),
(2, 1, 2, 'Aprobado', NULL, NULL, '2023-12-15', 8.75, '100%'),
(3, 2, 3, 'En Curso', NULL, NULL, NULL, NULL, '95%'),
(4, 2, 4, 'Finalizado', NULL, NULL, '2023-11-30', 6.50, '85%'),
(5, 3, 5, 'Por Iniciar', NULL, '2025-02-15', NULL, NULL, 'N/A'),
(6, 2, 6, 'En Curso', '2025-11-16', '2024-05-15', NULL, 8.75, '95%'),
(7, 3, 7, 'Por Iniciar', '2025-11-16', '2025-01-15', NULL, NULL, ''),
(8, 4, 1, 'Finalizado', '2025-11-16', '2024-03-01', '2024-09-15', 8.80, '92%');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_26_000001_create_instances_table', 1),
(5, '2025_11_26_000002_create_estudiantes_table', 1),
(6, '2025_11_26_000003_create_cursos_table', 1),
(7, '2025_11_26_000004_create_inscripciones_table', 1),
(8, '2025_11_26_000005_create_codigos_validacion_table', 1),
(9, '2025_11_26_000006_create_competencias_table', 1),
(10, '2025_11_26_000007_create_linea_tiempo_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trayectoria`
--

CREATE TABLE `trayectoria` (
  `id_evento` int NOT NULL,
  `id_inscripcion` int NOT NULL,
  `fecha` date NOT NULL,
  `evento` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: Inscripción, Examen Parcial',
  `detalle` text COLLATE utf8mb4_unicode_ci COMMENT 'Información adicional del evento',
  `orden` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Eventos de la trayectoria académica del estudiante';

--
-- Volcado de datos para la tabla `trayectoria`
--

INSERT INTO `trayectoria` (`id_evento`, `id_inscripcion`, `fecha`, `evento`, `detalle`, `orden`) VALUES
(6, 2, '2023-09-01', 'Inscripción', NULL, 0),
(7, 2, '2023-09-15', 'Inicio de cursada', NULL, 1),
(8, 2, '2023-12-15', 'Finalización', NULL, 2),
(9, 3, '2024-08-01', 'Inscripción', NULL, 0),
(10, 3, '2024-08-15', 'Inicio de cursada', NULL, 1),
(11, 4, '2023-07-01', 'Inscripción', NULL, 0),
(12, 4, '2023-07-15', 'Inicio de cursada', NULL, 1),
(13, 4, '2023-11-30', 'Finalización', NULL, 2),
(14, 5, '2024-12-10', 'Inscripción', NULL, 0),
(23, 1, '2024-03-01', 'Inicio del curso', '', 1),
(24, 1, '2024-04-15', 'Módulo 1 completado', 'Evaluación: 9/10', 2),
(25, 1, '2024-05-30', 'Módulo 2 completado', 'Evaluación: 10/10', 3),
(26, 1, '2024-09-15', 'Finalización del curso', 'Aprobado con mención', 4),
(27, 6, '2024-05-15', 'Inicio del curso', '', 1),
(28, 6, '2024-06-30', 'Primer módulo completado', 'Muy buen desempeño', 2),
(29, 6, '2024-08-15', 'Segundo módulo en progreso', '', 3),
(30, 8, '2024-03-01', 'Inicio del curso', '', 1),
(31, 8, '2024-04-15', 'Módulo 1 completado', '', 2),
(32, 8, '2024-05-30', 'Módulo 2 completado', '', 3),
(33, 8, '2024-09-15', 'Curso finalizado', '', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin VERUMax', 'admin@verumax.com', '2025-11-28 18:53:10', '$2y$12$oPrRSZPdmb1KJWhvZo5V/ew930QZArdqkibdzEDdxPzCFRhYrXxfC', '0S9CfU5snpfTrGRevTT5OsJwufkBF7v7QypSKw6AWOdGVS8BLqabk2Kk0tV1', '2025-11-28 18:40:33', '2025-11-28 18:40:33');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_estadisticas_institucion`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_estadisticas_institucion` (
`institucion` varchar(50)
,`total_estudiantes` bigint
,`total_cursos` bigint
,`total_inscripciones` bigint
,`cursos_aprobados` decimal(23,0)
,`cursos_en_curso` decimal(23,0)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_estudiantes_cursos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_estudiantes_cursos` (
`id_estudiante` int
,`institucion` varchar(50)
,`dni` varchar(50)
,`nombre_completo` varchar(200)
,`email` varchar(150)
,`id_curso` int
,`codigo_curso` varchar(100)
,`nombre_curso` varchar(300)
,`carga_horaria` int
,`id_inscripcion` int
,`estado` enum('Por Iniciar','En Curso','Finalizado','Aprobado','Desaprobado')
,`fecha_inscripcion` date
,`fecha_inicio` date
,`fecha_finalizacion` date
,`nota_final` decimal(4,2)
,`asistencia` varchar(10)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_estadisticas_institucion`
--
DROP TABLE IF EXISTS `vista_estadisticas_institucion`;

CREATE ALGORITHM=UNDEFINED DEFINER=`verumax`@`localhost` SQL SECURITY DEFINER VIEW `vista_estadisticas_institucion`  AS SELECT `e`.`institucion` AS `institucion`, count(distinct `e`.`id_estudiante`) AS `total_estudiantes`, count(distinct `c`.`id_curso`) AS `total_cursos`, count(distinct `i`.`id_inscripcion`) AS `total_inscripciones`, sum((case when (`i`.`estado` = 'Aprobado') then 1 else 0 end)) AS `cursos_aprobados`, sum((case when (`i`.`estado` = 'En Curso') then 1 else 0 end)) AS `cursos_en_curso` FROM ((`estudiantes` `e` left join `inscripciones` `i` on((`e`.`id_estudiante` = `i`.`id_estudiante`))) left join `cursos` `c` on((`i`.`id_curso` = `c`.`id_curso`))) GROUP BY `e`.`institucion` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_estudiantes_cursos`
--
DROP TABLE IF EXISTS `vista_estudiantes_cursos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`verumax`@`localhost` SQL SECURITY DEFINER VIEW `vista_estudiantes_cursos`  AS SELECT `e`.`id_estudiante` AS `id_estudiante`, `e`.`institucion` AS `institucion`, `e`.`dni` AS `dni`, `e`.`nombre_completo` AS `nombre_completo`, `e`.`email` AS `email`, `c`.`id_curso` AS `id_curso`, `c`.`codigo_curso` AS `codigo_curso`, `c`.`nombre_curso` AS `nombre_curso`, `c`.`carga_horaria` AS `carga_horaria`, `i`.`id_inscripcion` AS `id_inscripcion`, `i`.`estado` AS `estado`, `i`.`fecha_inscripcion` AS `fecha_inscripcion`, `i`.`fecha_inicio` AS `fecha_inicio`, `i`.`fecha_finalizacion` AS `fecha_finalizacion`, `i`.`nota_final` AS `nota_final`, `i`.`asistencia` AS `asistencia` FROM ((`estudiantes` `e` join `inscripciones` `i` on((`e`.`id_estudiante` = `i`.`id_estudiante`))) join `cursos` `c` on((`i`.`id_curso` = `c`.`id_curso`))) WHERE (`c`.`activo` = 1) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `certificatum_config`
--
ALTER TABLE `certificatum_config`
  ADD PRIMARY KEY (`id_config`),
  ADD UNIQUE KEY `unique_instance` (`id_instancia`),
  ADD KEY `idx_usar_paleta_general` (`certificatum_usar_paleta_general`),
  ADD KEY `idx_modo` (`certificatum_modo`);

--
-- Indices de la tabla `codigos_validacion`
--
ALTER TABLE `codigos_validacion`
  ADD PRIMARY KEY (`id_validacion`),
  ADD UNIQUE KEY `unique_codigo` (`codigo_validacion`),
  ADD KEY `idx_institucion_dni` (`institucion`,`dni`),
  ADD KEY `idx_codigo_curso` (`codigo_curso`),
  ADD KEY `idx_fecha` (`fecha_generacion`);

--
-- Indices de la tabla `competencias_curso`
--
ALTER TABLE `competencias_curso`
  ADD PRIMARY KEY (`id_competencia`),
  ADD KEY `idx_inscripcion` (`id_inscripcion`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`),
  ADD UNIQUE KEY `unique_codigo_curso` (`institucion`,`codigo_curso`),
  ADD KEY `idx_institucion` (`institucion`),
  ADD KEY `idx_codigo` (`codigo_curso`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id_estudiante`),
  ADD UNIQUE KEY `unique_estudiante_dni` (`institucion`,`dni`),
  ADD KEY `idx_dni` (`dni`),
  ADD KEY `idx_institucion` (`institucion`),
  ADD KEY `idx_nombre` (`nombre_completo`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id_inscripcion`),
  ADD UNIQUE KEY `unique_inscripcion` (`id_estudiante`,`id_curso`),
  ADD KEY `idx_estudiante` (`id_estudiante`),
  ADD KEY `idx_curso` (`id_curso`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_estudiante_estado` (`id_estudiante`,`estado`),
  ADD KEY `idx_curso_estado` (`id_curso`,`estado`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `trayectoria`
--
ALTER TABLE `trayectoria`
  ADD PRIMARY KEY (`id_evento`),
  ADD KEY `idx_inscripcion` (`id_inscripcion`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `certificatum_config`
--
ALTER TABLE `certificatum_config`
  MODIFY `id_config` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `codigos_validacion`
--
ALTER TABLE `codigos_validacion`
  MODIFY `id_validacion` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `competencias_curso`
--
ALTER TABLE `competencias_curso`
  MODIFY `id_competencia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id_estudiante` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id_inscripcion` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `trayectoria`
--
ALTER TABLE `trayectoria`
  MODIFY `id_evento` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `competencias_curso`
--
ALTER TABLE `competencias_curso`
  ADD CONSTRAINT `competencias_curso_ibfk_1` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripciones` (`id_inscripcion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE;

--
-- Filtros para la tabla `trayectoria`
--
ALTER TABLE `trayectoria`
  ADD CONSTRAINT `trayectoria_ibfk_1` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripciones` (`id_inscripcion`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
