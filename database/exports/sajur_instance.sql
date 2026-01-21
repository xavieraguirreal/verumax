-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: verumax_general
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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

--
-- Table structure for table `instances`
--

DROP TABLE IF EXISTS `instances`;
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

--
-- Dumping data for table `instances`
--
-- WHERE:  slug='sajur'

LOCK TABLES `instances` WRITE;
/*!40000 ALTER TABLE `instances` DISABLE KEYS */;
INSERT INTO `instances` VALUES (1,'sajur','SAJuR','Sociedad Argentina de Justicia Restaurativa','La Sociedad Argentina de Justicia Restaurativa (SAJuR) es una asociación civil sin fines de lucro, fundada en 2024, con el objetivo de promover, difundir e investigar la Justicia Restaurativa como un nuevo paradigma de respuesta al conflicto y al delito.','','info@sajur.org','{\"instagram\":\"https:\\/\\/instagram.com\\/sajurargentina\",\"facebook\":\"https:\\/\\/facebook.com\\/sajur\",\"linkedin\":\"\",\"whatsapp\":\"https:\\/\\/wa.me\\/5491112345678\",\"twitter\":\"\",\"youtube\":\"\"}','sajur.verumax.com','https://verumax.com/uploads/logos/sajur-logo-1764853099.png','#2e7d32','#1b5e20','#66bb6a','verde-elegante',0,1,0,0,0,0,0,'basicum',1,'admin@sajur','$2y$12$4RSQyKzNic3dwDY240.uA.icnDv7ED47ojPKNWKO24Zr/bLtMOcmu','info@sajur.org','SAJuR - Sociedad Argentina de Justicia Restaurativa | Certificados y F','Portal oficial de la Sociedad Argentina de Justicia Restaurativa. Validß certificados, accedÚ a tu analÝtico acadÚmico y conocÚ nuestros programas de formaci¾n ','SAJuR, Sociedad Argentina Justicia Restaurativa, certificados justicia restaurativa, validar certificados Argentina, formaci¾n justicia restaurativa, mediaci¾n, resoluci¾n conflictos, prßcticas restaurativas','https://verumax.com/identitas/favicons/sajur-favicon-32x32.png','2025-11-21 16:42:10','2025-11-16 20:20:01','2025-12-11 23:18:32',0,'',1,'rectangular-rounded',0,1,NULL,0,1,'es_AR','es_AR,pt_BR');
/*!40000 ALTER TABLE `instances` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-15 17:10:05
