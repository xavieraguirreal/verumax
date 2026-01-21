-- =====================================================
-- SCRIPT: Agregar tabla log_validaciones
-- Base de datos: verumax_certifi (Certificatum)
-- Fecha: 2026-01-01
-- Descripcion: Tabla para logging detallado de consultas QR
-- SEGURO: Solo agrega, no modifica tablas existentes
-- =====================================================

-- Crear tabla de logs detallados
CREATE TABLE IF NOT EXISTS `log_validaciones` (
  `id_log` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo_validacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Codigo QR consultado',
  `institucion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Institucion del certificado',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP del visitante (IPv4 o IPv6)',
  `user_agent` text COLLATE utf8mb4_unicode_ci COMMENT 'Navegador/dispositivo del visitante',
  `referer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL de origen',
  `fecha_consulta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora exacta',
  `pais` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pais (para geolocalizacion futura)',
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ciudad (para geolocalizacion futura)',
  `tipo_documento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de documento validado',
  `exitoso` tinyint(1) DEFAULT '1' COMMENT '1=codigo valido, 0=codigo no encontrado',
  PRIMARY KEY (`id_log`),
  KEY `idx_codigo` (`codigo_validacion`),
  KEY `idx_fecha` (`fecha_consulta`),
  KEY `idx_institucion` (`institucion`),
  KEY `idx_exitoso` (`exitoso`),
  KEY `idx_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial detallado de consultas de validacion QR';

-- Crear vista para estadisticas rapidas (opcional)
CREATE OR REPLACE VIEW `vista_estadisticas_validaciones` AS
SELECT
    institucion,
    DATE(fecha_consulta) as fecha,
    COUNT(*) as total_consultas,
    SUM(CASE WHEN exitoso = 1 THEN 1 ELSE 0 END) as consultas_exitosas,
    SUM(CASE WHEN exitoso = 0 THEN 1 ELSE 0 END) as consultas_fallidas,
    COUNT(DISTINCT ip_address) as visitantes_unicos
FROM log_validaciones
GROUP BY institucion, DATE(fecha_consulta)
ORDER BY fecha DESC;
