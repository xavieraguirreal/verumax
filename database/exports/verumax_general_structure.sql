
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_config` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(11) NOT NULL,
  `sendgrid_api_key` varchar(255) DEFAULT NULL COMMENT 'API Key de SendGrid (encriptada)',
  `usar_sendgrid_global` tinyint(1) DEFAULT 1 COMMENT 'Usar API key global de VERUMax',
  `email_remitente` varchar(255) DEFAULT NULL COMMENT 'Email verificado en SendGrid',
  `nombre_remitente` varchar(100) DEFAULT NULL COMMENT 'Nombre que aparece como remitente',
  `email_respuesta` varchar(255) DEFAULT NULL COMMENT 'Reply-to email',
  `dominio_verificado` varchar(100) DEFAULT NULL COMMENT 'Dominio verificado en SendGrid (ej: sajur.org.ar)',
  `dominio_verificado_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha de verificaci├│n',
  `activo` tinyint(1) DEFAULT 1,
  `emails_enviados_mes` int(11) DEFAULT 0 COMMENT 'Contador mensual',
  `ultimo_envio_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_config`),
  UNIQUE KEY `unique_instancia` (`id_instancia`),
  CONSTRAINT `email_config_ibfk_1` FOREIGN KEY (`id_instancia`) REFERENCES `instances` (`id_instancia`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuraci├│n de email por instancia';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_logs` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(11) NOT NULL,
  `tipo_email` enum('certificado_disponible','constancia_disponible','bienvenida_curso','recordatorio','notificacion_general','campana_marketing') NOT NULL,
  `email_destino` varchar(255) NOT NULL,
  `nombre_destino` varchar(255) DEFAULT NULL,
  `asunto` varchar(255) NOT NULL,
  `template_usado` varchar(50) DEFAULT NULL COMMENT 'C├│digo del template',
  `estado` enum('pendiente','enviado','error','rebotado','abierto','click') DEFAULT 'pendiente',
  `sendgrid_message_id` varchar(100) DEFAULT NULL,
  `sendgrid_batch_id` varchar(100) DEFAULT NULL COMMENT 'ID del batch si fue env├¡o masivo',
  `error_codigo` varchar(50) DEFAULT NULL,
  `error_mensaje` text DEFAULT NULL,
  `intentos` int(11) DEFAULT 0,
  `datos_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Datos adicionales: id_inscripcion, id_curso, etc.' CHECK (json_valid(`datos_json`)),
  `programado_para` timestamp NULL DEFAULT NULL COMMENT 'Para env├¡os programados',
  `enviado_at` timestamp NULL DEFAULT NULL,
  `abierto_at` timestamp NULL DEFAULT NULL,
  `click_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`),
  KEY `idx_instancia_tipo` (`id_instancia`,`tipo_email`),
  KEY `idx_estado` (`estado`),
  KEY `idx_email` (`email_destino`),
  KEY `idx_fecha` (`created_at`),
  KEY `idx_sendgrid_msg` (`sendgrid_message_id`),
  KEY `idx_batch` (`sendgrid_batch_id`),
  CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`id_instancia`) REFERENCES `instances` (`id_instancia`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de emails enviados';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_templates` (
  `id_template` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(11) DEFAULT NULL COMMENT 'NULL = template del sistema',
  `codigo` varchar(50) NOT NULL COMMENT 'C├│digo ├║nico: certificado_disponible',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre descriptivo',
  `descripcion` text DEFAULT NULL,
  `asunto_default` varchar(255) NOT NULL,
  `contenido_html` mediumtext NOT NULL,
  `contenido_texto` text DEFAULT NULL COMMENT 'Versi├│n texto plano',
  `variables_disponibles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '["nombre", "curso", "url_portal"]' CHECK (json_valid(`variables_disponibles`)),
  `sendgrid_template_id` varchar(100) DEFAULT NULL COMMENT 'ID si est├í sincronizado con SendGrid',
  `es_sistema` tinyint(1) DEFAULT 0 COMMENT 'Template del sistema, no editable',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_template`),
  UNIQUE KEY `unique_codigo_instancia` (`id_instancia`,`codigo`),
  KEY `idx_codigo` (`codigo`),
  KEY `idx_sistema` (`es_sistema`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Templates de email reutilizables';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instance_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(11) NOT NULL,
  `campo` varchar(100) NOT NULL COMMENT 'mision, vision, descripcion, etc.',
  `idioma` varchar(5) NOT NULL COMMENT 'es_AR, pt_BR, en_US',
  `contenido` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_translation` (`id_instancia`,`campo`,`idioma`),
  KEY `idx_instancia` (`id_instancia`),
  KEY `idx_idioma` (`idioma`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instances` (
  `id_instancia` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL COMMENT 'Identificador único de la instancia (ej: sajur)',
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre de la institución',
  `nombre_completo` text DEFAULT NULL COMMENT 'Nombre completo de la institución',
  `mision` text DEFAULT NULL COMMENT 'Misión o descripción de la institución',
  `sitio_web_oficial` varchar(255) DEFAULT NULL COMMENT 'URL del sitio web oficial de la institución',
  `email_contacto` varchar(255) DEFAULT NULL COMMENT 'Email de contacto de la institución',
  `redes_sociales` text DEFAULT NULL COMMENT 'JSON con URLs de redes sociales (instagram, facebook, linkedin, whatsapp, twitter, youtube)',
  `dominio` varchar(255) DEFAULT NULL COMMENT 'Dominio personalizado',
  `logo_url` varchar(500) DEFAULT NULL COMMENT 'URL del logo institucional',
  `color_primario` varchar(7) DEFAULT '#D4AF37' COMMENT 'Color primario global',
  `color_secundario` varchar(7) DEFAULT NULL COMMENT 'Color secundario global',
  `color_acento` varchar(7) DEFAULT '#1976D2' COMMENT 'Color de acento global',
  `paleta_colores` varchar(30) DEFAULT 'verde-elegante' COMMENT 'Nombre de la paleta predefinida',
  `identitas_activo` tinyint(1) DEFAULT 1 COMMENT 'Si Identitas está activo (1) o en construcción (0)',
  `modulo_certificatum` tinyint(1) DEFAULT 0 COMMENT 'Si Certificatum está activo',
  `modulo_scripta` tinyint(1) DEFAULT 0 COMMENT 'Si Scripta está activo',
  `modulo_nexus` tinyint(1) DEFAULT 0 COMMENT 'Si Nexus está activo',
  `modulo_vitae` tinyint(1) DEFAULT 0 COMMENT 'Si Vitae está activo',
  `modulo_lumen` tinyint(1) DEFAULT 0 COMMENT 'Si Lumen está activo',
  `modulo_opera` tinyint(1) DEFAULT 0 COMMENT 'Si Opera está activo',
  `plan` varchar(50) DEFAULT 'basicum' COMMENT 'Plan contratado (basicum, premium, excellens, supremus)',
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Si la instancia está activa globalmente',
  `admin_usuario` varchar(100) DEFAULT NULL COMMENT 'Usuario del administrador',
  `admin_password` varchar(255) DEFAULT NULL COMMENT 'Contraseña hasheada del administrador',
  `admin_email` varchar(255) DEFAULT NULL COMMENT 'Email del administrador',
  `seo_title` varchar(70) DEFAULT NULL COMMENT 'Título SEO global',
  `seo_description` varchar(160) DEFAULT NULL COMMENT 'Descripción SEO global',
  `seo_keywords` varchar(255) DEFAULT NULL COMMENT 'Keywords SEO globales',
  `favicon_url` varchar(500) DEFAULT NULL COMMENT 'URL del favicon generado desde el logo',
  `favicon_generated_at` datetime DEFAULT NULL COMMENT 'Fecha de generación del favicon desde el logo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sitio_en_construccion` tinyint(1) DEFAULT 0 COMMENT 'Si el sitio está en modo construcción (0=público, 1=en construcción)',
  `mensaje_construccion` text DEFAULT NULL COMMENT 'Mensaje personalizado para página en construcción',
  `robots_noindex` tinyint(1) DEFAULT 1 COMMENT 'Si se debe evitar indexación de buscadores (1=noindex, 0=index)',
  `logo_estilo` varchar(50) DEFAULT 'rectangular',
  `logo_mostrar_texto` tinyint(1) DEFAULT 1 COMMENT 'Mostrar nombre de la institución al lado del logo (1=sí, 0=no)',
  `favicon_generado` tinyint(1) DEFAULT 0 COMMENT 'Indica si se generó el favicon (1=sí, 0=no)',
  `openai_api_key` varchar(255) DEFAULT NULL COMMENT 'API Key de OpenAI para autocompletado con IA',
  `openai_habilitado` tinyint(1) DEFAULT 0 COMMENT '1 = IA habilitada, 0 = deshabilitada',
  `ia_habilitada` tinyint(1) DEFAULT 0 COMMENT '1 = IA habilitada para esta institucion, 0 = deshabilitada',
  `idioma_default` varchar(5) DEFAULT 'es_AR',
  `idiomas_habilitados` varchar(100) DEFAULT 'es_AR',
  PRIMARY KEY (`id_instancia`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_dominio` (`dominio`),
  KEY `idx_identitas_activo` (`identitas_activo`),
  KEY `idx_activo` (`activo`),
  KEY `idx_admin_usuario` (`admin_usuario`),
  KEY `idx_openai_habilitado` (`openai_habilitado`),
  KEY `idx_ia_habilitada` (`ia_habilitada`),
  KEY `idx_email_contacto` (`email_contacto`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración general de instancias - Paleta global y estado de módulos';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

