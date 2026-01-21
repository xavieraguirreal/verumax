-- =====================================================
-- SCRIPT: Agregar tabla log_accesos_certificados
-- Base de datos: verumax_certifi (Certificatum)
-- Fecha: 2026-01-04
-- Descripcion: Tabla para tracking de vistas y descargas de certificados
-- SEGURO: Solo agrega, no modifica tablas existentes
-- =====================================================

-- Crear tabla de logs de accesos a certificados
CREATE TABLE IF NOT EXISTS `log_accesos_certificados` (
  `id_log` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `institucion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Slug de la institucion',
  `dni` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'DNI del titular del certificado',
  `nombre_persona` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre completo de la persona',
  `tipo_accion` enum('vista_pantalla', 'descarga_pdf') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de acceso',
  `tipo_documento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de documento (genus)',
  `codigo_curso` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codigo del curso',
  `nombre_curso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre del curso (para referencia)',
  `tipo_usuario` enum('estudiante', 'docente') COLLATE utf8mb4_unicode_ci DEFAULT 'estudiante' COMMENT 'Si es estudiante o docente',
  `id_participacion` int DEFAULT NULL COMMENT 'ID de participacion docente (si aplica)',
  `idioma` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Idioma seleccionado (es_AR, pt_BR, etc)',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP del visitante (IPv4 o IPv6)',
  `user_agent` text COLLATE utf8mb4_unicode_ci COMMENT 'Navegador/dispositivo del visitante',
  `dispositivo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de dispositivo detectado',
  `referer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL de origen',
  `fecha_acceso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora exacta',
  PRIMARY KEY (`id_log`),
  KEY `idx_institucion` (`institucion`),
  KEY `idx_dni` (`dni`),
  KEY `idx_tipo_accion` (`tipo_accion`),
  KEY `idx_tipo_documento` (`tipo_documento`),
  KEY `idx_fecha` (`fecha_acceso`),
  KEY `idx_tipo_usuario` (`tipo_usuario`),
  KEY `idx_inst_fecha` (`institucion`, `fecha_acceso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de vistas en pantalla y descargas PDF de certificados';

-- Si la tabla ya existe, agregar las columnas nuevas
ALTER TABLE `log_accesos_certificados`
ADD COLUMN IF NOT EXISTS `nombre_persona` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre completo de la persona' AFTER `dni`,
ADD COLUMN IF NOT EXISTS `idioma` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Idioma seleccionado' AFTER `id_participacion`;

-- Vista para estadisticas rapidas de accesos
CREATE OR REPLACE VIEW `vista_estadisticas_accesos` AS
SELECT
    institucion,
    DATE(fecha_acceso) as fecha,
    tipo_accion,
    tipo_usuario,
    COUNT(*) as total_accesos,
    COUNT(DISTINCT dni) as usuarios_unicos,
    COUNT(DISTINCT ip_address) as ips_unicas
FROM log_accesos_certificados
GROUP BY institucion, DATE(fecha_acceso), tipo_accion, tipo_usuario
ORDER BY fecha DESC;

-- Vista para resumen diario por institucion
CREATE OR REPLACE VIEW `vista_resumen_accesos_diario` AS
SELECT
    institucion,
    DATE(fecha_acceso) as fecha,
    SUM(CASE WHEN tipo_accion = 'vista_pantalla' THEN 1 ELSE 0 END) as vistas_pantalla,
    SUM(CASE WHEN tipo_accion = 'descarga_pdf' THEN 1 ELSE 0 END) as descargas_pdf,
    COUNT(DISTINCT dni) as usuarios_unicos,
    COUNT(DISTINCT CASE WHEN tipo_usuario = 'estudiante' THEN dni END) as estudiantes_unicos,
    COUNT(DISTINCT CASE WHEN tipo_usuario = 'docente' THEN dni END) as docentes_unicos
FROM log_accesos_certificados
GROUP BY institucion, DATE(fecha_acceso)
ORDER BY fecha DESC;
