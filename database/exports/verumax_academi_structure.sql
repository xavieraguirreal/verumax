
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
CREATE TABLE `cohortes` (
  `id_cohorte` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(10) unsigned NOT NULL,
  `id_curso` int(11) NOT NULL,
  `codigo_cohorte` varchar(50) NOT NULL COMMENT 'Ej: 2024-C1, MAR-2024',
  `nombre_cohorte` varchar(255) DEFAULT NULL COMMENT 'Ej: Comisi¾n Marzo 2024',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `cupo_maximo` int(10) unsigned DEFAULT NULL,
  `modalidad` enum('Presencial','Virtual','HÝbrido') DEFAULT 'Virtual',
  `horario` varchar(255) DEFAULT NULL COMMENT 'Ej: Lunes y MiÚrcoles 18:00-20:00',
  `ubicacion` varchar(255) DEFAULT NULL,
  `link_virtual` varchar(500) DEFAULT NULL COMMENT 'Link Zoom/Meet/etc',
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
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `competencias` (
  `id_competencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `competencia` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL COMMENT 'Ej: TÚcnica, Transversal, EspecÝfica',
  `orden` tinyint(3) unsigned DEFAULT 1,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_competencia`),
  KEY `idx_curso` (`id_curso`),
  CONSTRAINT `fk_comp_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `competencias_inscripcion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_inscripcion` int(11) NOT NULL,
  `id_competencia` int(11) DEFAULT NULL COMMENT 'FK a competencias (opcional)',
  `competencia` varchar(255) NOT NULL COMMENT 'Texto de la competencia',
  `adquirida` tinyint(1) DEFAULT 1,
  `fecha_adquisicion` date DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_inscripcion` (`id_inscripcion`),
  CONSTRAINT `fk_compinsc_inscripcion` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripciones` (`id_inscripcion`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cursos` (
  `id_curso` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(10) unsigned NOT NULL COMMENT 'FK a verumax_nexus.instancias',
  `codigo_curso` varchar(50) NOT NULL COMMENT 'C¾digo ·nico por instancia (ej: SJ-DPA-2024)',
  `nombre_curso` varchar(300) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL COMMENT '┴rea temßtica (ej: Derecho, TecnologÝa)',
  `tipo_curso` enum('Curso','Diplomatura','Taller','Seminario','Capacitaci¾n','Certificaci¾n') DEFAULT 'Curso',
  `nivel` enum('Inicial','Intermedio','Avanzado','Todos los niveles') DEFAULT 'Todos los niveles',
  `modalidad` enum('Presencial','Virtual','HÝbrido') DEFAULT 'Virtual',
  `carga_horaria` int(11) DEFAULT NULL COMMENT 'Horas totales',
  `duracion_semanas` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `cupo_maximo` int(11) DEFAULT NULL COMMENT 'NULL = sin lÝmite',
  `requisitos_previos` text DEFAULT NULL COMMENT 'Cursos o conocimientos previos',
  `emite_certificado` tinyint(1) DEFAULT 1,
  `tipo_certificado` varchar(100) DEFAULT 'Certificado de Aprobaci¾n',
  `activo` tinyint(1) DEFAULT 1,
  `visible_catalogo` tinyint(1) DEFAULT 1 COMMENT 'Mostrar en catßlogo p·blico',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_curso`),
  UNIQUE KEY `unique_curso_instancia` (`id_instancia`,`codigo_curso`),
  KEY `idx_instancia` (`id_instancia`),
  KEY `idx_activo` (`activo`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_tipo` (`tipo_curso`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inscripciones` (
  `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(10) unsigned NOT NULL COMMENT 'FK a instancias',
  `id_miembro` int(11) NOT NULL COMMENT 'FK a verumax_nexus.miembros',
  `id_curso` int(11) NOT NULL COMMENT 'FK a cursos',
  `id_cohorte` int(11) DEFAULT NULL COMMENT 'FK a cohortes (opcional)',
  `estado` enum('Preinscrito','Inscrito','En Curso','Finalizado','Aprobado','Desaprobado','Abandonado','Suspendido') DEFAULT 'Inscrito',
  `fecha_preinscripcion` date DEFAULT NULL,
  `fecha_inscripcion` date DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `nota_final` decimal(5,2) DEFAULT NULL COMMENT 'Nota con 2 decimales (ej: 8.50)',
  `nota_minima_aprobacion` decimal(5,2) DEFAULT 6.00,
  `asistencia_porcentaje` decimal(5,2) DEFAULT NULL COMMENT 'Porcentaje 0-100',
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trayectoria` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `id_inscripcion` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_evento` enum('Inscripci¾n','Inicio','M¾dulo','Evaluaci¾n','Asistencia','Certificaci¾n','Otro') DEFAULT 'Otro',
  `evento` varchar(200) NOT NULL,
  `detalle` text DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_evento`),
  KEY `idx_inscripcion` (`id_inscripcion`),
  KEY `idx_fecha` (`fecha`),
  CONSTRAINT `fk_tray_inscripcion` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripciones` (`id_inscripcion`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

