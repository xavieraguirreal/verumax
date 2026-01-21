
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
CREATE TABLE `configuracion_nexus` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(11) NOT NULL,
  `etiqueta_miembro` varchar(50) DEFAULT 'Miembro' COMMENT 'Estudiante, Socio, Cliente, etc.',
  `etiqueta_identificador` varchar(50) DEFAULT 'DNI',
  `campos_activos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Lista de campos habilitados para esta organización' CHECK (json_valid(`campos_activos`)),
  `campos_requeridos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Lista de campos obligatorios' CHECK (json_valid(`campos_requeridos`)),
  `etiquetas_personalizadas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Etiquetas custom para campos' CHECK (json_valid(`etiquetas_personalizadas`)),
  `color_primario` varchar(7) DEFAULT '#0F52BA',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_config`),
  UNIQUE KEY `id_instancia` (`id_instancia`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `miembros` (
  `id_miembro` int(11) NOT NULL AUTO_INCREMENT,
  `id_instancia` int(11) NOT NULL COMMENT 'FK a verumax_identitas.instancias',
  `identificador_principal` varchar(50) NOT NULL COMMENT 'DNI, CUIT, Pasaporte, etc.',
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla central de miembros - Gestor Nexus';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

