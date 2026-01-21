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
-- Table structure for table `instance_translations`
--

DROP TABLE IF EXISTS `instance_translations`;
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

--
-- Dumping data for table `instance_translations`
--

LOCK TABLES `instance_translations` WRITE;
/*!40000 ALTER TABLE `instance_translations` DISABLE KEYS */;
INSERT INTO `instance_translations` VALUES (3,1,'mision','pt_BR','A Sociedade Argentina de Justiça Restaurativa (SAJuR) é uma associação civil sem fins lucrativos, fundada em 2024, com o objetivo de promover, difundir e pesquisar a Justiça Restaurativa como um novo paradigma de resposta ao conflito e ao delito.','2025-12-09 21:48:13','2025-12-09 21:48:13'),(12,1,'intro_historia_titulo','pt_BR','Nossa História','2025-12-11 22:23:41','2025-12-11 22:23:41'),(13,1,'timeline_titulo','pt_BR','Evolução e Conquistas em Justiça Restaurativa','2025-12-11 22:23:41','2025-12-11 22:23:41'),(14,1,'intro_historia_texto','pt_BR','<p>A Sociedade Argentina de Justiça Restaurativa foi fundada com o propósito de promover e fortalecer as práticas restaurativas no sistema de justiça do país. Desde seus inícios, tem trabalhado para integrar enfoques inovadores que buscam a reparação do dano e a reconciliação entre as partes envolvidas. Ao longo dos anos, a instituição consolidou sua liderança na implementação de programas e metodologias que fomentam a participação ativa da comunidade e dos atores judiciais, contribuindo para uma justiça mais humana e efetiva.</p>','2025-12-11 22:23:41','2025-12-11 22:23:41'),(15,1,'certificatum_cta_texto','pt_BR','Entrar com meu documento','2025-12-11 22:23:41','2025-12-11 22:23:41'),(16,1,'certificatum_descripcion','pt_BR','Acesse seus certificados, declarações e histórico acadêmico completo. Baixe seus documentos em formato PDF com código QR de validação.','2025-12-11 22:23:41','2025-12-11 22:23:41');
/*!40000 ALTER TABLE `instance_translations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-15 17:07:52
