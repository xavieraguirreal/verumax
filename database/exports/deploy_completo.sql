-- =====================================================
-- SCRIPT DE DEPLOY COMPLETO - VERUMax
-- Ejecutar en phpMyAdmin del servidor remoto
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- =====================================================
-- 1. CREAR BASES DE DATOS SI NO EXISTEN
-- =====================================================
CREATE DATABASE IF NOT EXISTS verumax_nexus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS verumax_academi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =====================================================
-- 2. TABLAS EN verumax_general (nuevas tablas de email)
-- =====================================================
USE verumax_general;

CREATE TABLE IF NOT EXISTS `email_config` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(10) unsigned NOT NULL,
  `usar_sendgrid_global` tinyint(1) DEFAULT 1,
  `sendgrid_api_key` varchar(255) DEFAULT NULL,
  `email_remitente` varchar(150) DEFAULT NULL,
  `nombre_remitente` varchar(150) DEFAULT NULL,
  `emails_enviados_mes` int(11) DEFAULT 0,
  `limite_mensual` int(11) DEFAULT 1000,
  `ultimo_envio_at` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_config`),
  UNIQUE KEY `unique_instancia` (`id_instancia`),
  CONSTRAINT `email_config_ibfk_1` FOREIGN KEY (`id_instancia`) REFERENCES `instances` (`id_instancia`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_templates` (
  `id_template` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(10) unsigned DEFAULT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `asunto_default` varchar(255) NOT NULL,
  `contenido_html` text NOT NULL,
  `contenido_texto` text DEFAULT NULL,
  `variables_disponibles` text DEFAULT NULL,
  `es_sistema` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_template`),
  UNIQUE KEY `unique_template` (`id_instancia`,`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_logs` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(10) unsigned DEFAULT NULL,
  `tipo_email` varchar(50) DEFAULT NULL,
  `email_destino` varchar(150) NOT NULL,
  `nombre_destino` varchar(150) DEFAULT NULL,
  `asunto` varchar(255) DEFAULT NULL,
  `template_usado` varchar(50) DEFAULT NULL,
  `estado` enum('pendiente','enviado','error','abierto','click','rebotado') DEFAULT 'pendiente',
  `sendgrid_message_id` varchar(100) DEFAULT NULL,
  `sendgrid_batch_id` varchar(100) DEFAULT NULL,
  `error_mensaje` text DEFAULT NULL,
  `enviado_at` datetime DEFAULT NULL,
  `abierto_at` datetime DEFAULT NULL,
  `datos_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`),
  KEY `idx_instancia` (`id_instancia`),
  KEY `idx_estado` (`estado`),
  KEY `idx_email` (`email_destino`),
  KEY `idx_batch` (`sendgrid_batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar templates por defecto
INSERT IGNORE INTO email_templates (id_instancia, codigo, nombre, asunto_default, contenido_html, contenido_texto, variables_disponibles, es_sistema) VALUES
(NULL, 'certificado_disponible', 'Certificado Disponible',
'Tu certificado de {{nombre_curso}} esta listo',
'<html><body style="font-family:Arial;background:#f4f4f4;padding:20px;"><div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;"><div style="background:#166534;padding:30px;text-align:center;"><img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;"></div><div style="padding:30px;"><h1 style="color:#333;">Felicitaciones, {{nombre_estudiante}}!</h1><p style="color:#666;font-size:16px;">Tu certificado del curso <strong>{{nombre_curso}}</strong> ya esta disponible para descargar.</p><p style="color:#666;">Podes acceder a tu certificado ingresando a nuestro portal con tu numero de documento.</p><p style="text-align:center;margin:30px 0;"><a href="{{url_portal}}" style="background:#166534;color:#fff;padding:15px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">Descargar Certificado</a></p></div><div style="text-align:center;padding:20px;color:#999;font-size:12px;border-top:1px solid #eee;">{{nombre_institucion}}<br>Este es un mensaje automatico.</div></div></body></html>',
'Felicitaciones, {{nombre_estudiante}}! Tu certificado de {{nombre_curso}} esta disponible. Accede: {{url_portal}}',
'["nombre_estudiante","nombre_curso","url_portal","nombre_institucion","logo_url"]', 1),

(NULL, 'constancia_disponible', 'Constancia Disponible',
'Tu constancia de {{nombre_curso}} esta lista',
'<html><body style="font-family:Arial;background:#f4f4f4;padding:20px;"><div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;"><div style="background:#166534;padding:30px;text-align:center;"><img src="{{logo_url}}" alt="{{nombre_institucion}}" style="max-height:60px;"></div><div style="padding:30px;"><h1 style="color:#333;">Hola, {{nombre_estudiante}}</h1><p style="color:#666;font-size:16px;">Tu constancia del curso <strong>{{nombre_curso}}</strong> ya esta disponible para descargar.</p><p style="color:#666;">Podes acceder ingresando a nuestro portal con tu numero de documento.</p><p style="text-align:center;margin:30px 0;"><a href="{{url_portal}}" style="background:#166534;color:#fff;padding:15px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">Ver Constancia</a></p></div><div style="text-align:center;padding:20px;color:#999;font-size:12px;border-top:1px solid #eee;">{{nombre_institucion}}<br>Este es un mensaje automatico.</div></div></body></html>',
'Hola, {{nombre_estudiante}}. Tu constancia de {{nombre_curso}} esta disponible. Accede: {{url_portal}}',
'["nombre_estudiante","nombre_curso","url_portal","nombre_institucion","logo_url"]', 1);

-- =====================================================
-- 3. TABLAS EN verumax_nexus
-- =====================================================
USE verumax_nexus;

CREATE TABLE IF NOT EXISTS `configuracion_nexus` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(11) NOT NULL,
  `etiqueta_miembro` varchar(50) DEFAULT 'Miembro',
  `etiqueta_identificador` varchar(50) DEFAULT 'DNI',
  `campos_activos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `campos_requeridos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `etiquetas_personalizadas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `color_primario` varchar(7) DEFAULT '#0F52BA',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_config`),
  UNIQUE KEY `id_instancia` (`id_instancia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `miembros` (
  `id_miembro` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(11) NOT NULL,
  `identificador_principal` varchar(50) NOT NULL,
  `tipo_identificador` enum('DNI','CUIT','CUIL','Pasaporte','Otro') DEFAULT 'DNI',
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `nombre_completo` varchar(200) GENERATED ALWAYS AS (concat(`nombre`,' ',`apellido`)) STORED,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','Otro','No especifica') DEFAULT 'No especifica',
  `domicilio_calle` varchar(200) DEFAULT NULL,
  `domicilio_numero` varchar(20) DEFAULT NULL,
  `domicilio_piso` varchar(10) DEFAULT NULL,
  `domicilio_depto` varchar(10) DEFAULT NULL,
  `domicilio_ciudad` varchar(100) DEFAULT NULL,
  `domicilio_provincia` varchar(100) DEFAULT NULL,
  `domicilio_codigo_postal` varchar(20) DEFAULT NULL,
  `domicilio_pais` varchar(100) DEFAULT 'Argentina',
  `estado` enum('Activo','Inactivo','Suspendido','Pendiente') DEFAULT 'Activo',
  `tipo_miembro` enum('Estudiante','Docente','Socio','Cliente','Otro','ambos') DEFAULT 'Estudiante',
  `campo_texto_1` varchar(255) DEFAULT NULL,
  `campo_texto_2` varchar(255) DEFAULT NULL,
  `campo_texto_3` varchar(255) DEFAULT NULL,
  `campo_numero_1` decimal(10,2) DEFAULT NULL,
  `campo_numero_2` decimal(10,2) DEFAULT NULL,
  `campo_fecha_1` date DEFAULT NULL,
  `campo_fecha_2` date DEFAULT NULL,
  `campo_booleano_1` tinyint(1) DEFAULT 0,
  `fecha_alta` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notas` text DEFAULT NULL,
  PRIMARY KEY (`id_miembro`),
  UNIQUE KEY `uk_instancia_identificador` (`id_instancia`,`identificador_principal`),
  KEY `idx_instancia` (`id_instancia`),
  KEY `idx_estado` (`estado`),
  KEY `idx_nombre` (`apellido`,`nombre`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABLAS EN verumax_academi (en orden de dependencias)
-- =====================================================
USE verumax_academi;

-- Primero: cursos (sin dependencias internas)
CREATE TABLE IF NOT EXISTS `cursos` (
  `id_curso` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(10) unsigned NOT NULL,
  `codigo_curso` varchar(50) NOT NULL,
  `nombre_curso` varchar(300) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `tipo_curso` enum('Curso','Diplomatura','Taller','Seminario','Capacitacion','Certificacion') DEFAULT 'Curso',
  `nivel` enum('Inicial','Intermedio','Avanzado','Todos los niveles') DEFAULT 'Todos los niveles',
  `modalidad` enum('Presencial','Virtual','Hibrido') DEFAULT 'Virtual',
  `carga_horaria` int(11) DEFAULT NULL,
  `duracion_semanas` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `cupo_maximo` int(11) DEFAULT NULL,
  `requisitos_previos` text DEFAULT NULL,
  `emite_certificado` tinyint(1) DEFAULT 1,
  `tipo_certificado` varchar(100) DEFAULT 'Certificado de Aprobacion',
  `activo` tinyint(1) DEFAULT 1,
  `visible_catalogo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_curso`),
  UNIQUE KEY `unique_curso_instancia` (`id_instancia`,`codigo_curso`),
  KEY `idx_instancia` (`id_instancia`),
  KEY `idx_activo` (`activo`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_tipo` (`tipo_curso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Segundo: cohortes (depende de cursos)
CREATE TABLE IF NOT EXISTS `cohortes` (
  `id_cohorte` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(10) unsigned NOT NULL,
  `id_curso` int(11) NOT NULL,
  `codigo_cohorte` varchar(50) NOT NULL,
  `nombre_cohorte` varchar(255) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `cupo_maximo` int(10) unsigned DEFAULT NULL,
  `modalidad` enum('Presencial','Virtual','Hibrido') DEFAULT 'Virtual',
  `horario` varchar(255) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `link_virtual` varchar(500) DEFAULT NULL,
  `estado` enum('Programada','Inscripciones Abiertas','En Curso','Finalizada','Cancelada') DEFAULT 'Programada',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_cohorte`),
  UNIQUE KEY `unique_cohorte` (`id_instancia`,`id_curso`,`codigo_cohorte`),
  KEY `idx_curso` (`id_curso`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`),
  CONSTRAINT `fk_cohorte_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tercero: competencias (depende de cursos)
CREATE TABLE IF NOT EXISTS `competencias` (
  `id_competencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `competencia` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `orden` tinyint(3) unsigned DEFAULT 1,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_competencia`),
  KEY `idx_curso` (`id_curso`),
  CONSTRAINT `fk_comp_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cuarto: inscripciones (depende de cursos y nexus.miembros)
CREATE TABLE IF NOT EXISTS `inscripciones` (
  `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(10) unsigned NOT NULL,
  `id_miembro` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_cohorte` int(11) DEFAULT NULL,
  `estado` enum('Preinscrito','Inscrito','En Curso','Finalizado','Aprobado','Desaprobado','Abandonado','Suspendido') DEFAULT 'Inscrito',
  `fecha_preinscripcion` date DEFAULT NULL,
  `fecha_inscripcion` date DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `nota_final` decimal(5,2) DEFAULT NULL,
  `nota_minima_aprobacion` decimal(5,2) DEFAULT 6.00,
  `asistencia_porcentaje` decimal(5,2) DEFAULT NULL,
  `asistencia_minima` decimal(5,2) DEFAULT 75.00,
  `certificado_emitido` tinyint(1) DEFAULT 0,
  `fecha_emision_certificado` date DEFAULT NULL,
  `codigo_certificado` varchar(50) DEFAULT NULL,
  `monto_pagado` decimal(10,2) DEFAULT NULL,
  `estado_pago` enum('Pendiente','Parcial','Completo','Exento','Becado') DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_inscripcion`),
  UNIQUE KEY `unique_inscripcion` (`id_miembro`,`id_curso`,`id_cohorte`),
  KEY `idx_instancia` (`id_instancia`),
  KEY `idx_miembro` (`id_miembro`),
  KEY `idx_curso` (`id_curso`),
  KEY `idx_cohorte` (`id_cohorte`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fechas` (`fecha_inicio`,`fecha_finalizacion`),
  CONSTRAINT `fk_insc_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`),
  CONSTRAINT `fk_insc_miembro` FOREIGN KEY (`id_miembro`) REFERENCES `verumax_nexus`.`miembros` (`id_miembro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quinto: competencias_inscripcion (depende de inscripciones)
CREATE TABLE IF NOT EXISTS `competencias_inscripcion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_inscripcion` int(11) NOT NULL,
  `id_competencia` int(11) DEFAULT NULL,
  `competencia` varchar(255) NOT NULL,
  `adquirida` tinyint(1) DEFAULT 1,
  `fecha_adquisicion` date DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_inscripcion` (`id_inscripcion`),
  CONSTRAINT `fk_compinsc_inscripcion` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripciones` (`id_inscripcion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sexto: trayectoria (depende de inscripciones)
CREATE TABLE IF NOT EXISTS `trayectoria` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `id_inscripcion` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_evento` enum('Inscripcion','Inicio','Modulo','Evaluacion','Asistencia','Certificacion','Otro') DEFAULT 'Otro',
  `evento` varchar(200) NOT NULL,
  `detalle` text DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_evento`),
  KEY `idx_inscripcion` (`id_inscripcion`),
  KEY `idx_fecha` (`fecha`),
  CONSTRAINT `fk_tray_inscripcion` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripciones` (`id_inscripcion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. REACTIVAR FOREIGN KEY CHECKS
-- =====================================================
SET FOREIGN_KEY_CHECKS = 1;

-- FIN DEL SCRIPT
SELECT 'Deploy completado exitosamente!' AS resultado;
