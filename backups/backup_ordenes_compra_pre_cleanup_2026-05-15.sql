mysqldump: [Warning] Using a password on the command line interface can be insecure.
mysqldump: Error: 'Access denied; you need (at least one of) the PROCESS privilege(s) for this operation' when trying to dump tablespaces
-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: gestorpincadb
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ordenes_compra`
--

DROP TABLE IF EXISTS `ordenes_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_compra` (
  `id_orden` int unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `proveedor_id` int NOT NULL,
  `bodegas_id` int NOT NULL,
  `fecha` date NOT NULL,
  `fecha_esperada` date DEFAULT NULL,
  `estado` enum('Borrador','Enviada','Recibida','Cancelada') DEFAULT 'Borrador',
  `total` decimal(12,2) DEFAULT '0.00',
  `observaciones` text,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_orden`),
  UNIQUE KEY `numero` (`numero`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `bodegas_id` (`bodegas_id`),
  KEY `idx_ordenes_compra_deleted_at` (`deleted_at`),
  CONSTRAINT `ordenes_compra_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id_proveedor`),
  CONSTRAINT `ordenes_compra_ibfk_2` FOREIGN KEY (`bodegas_id`) REFERENCES `bodegas` (`id_bodegas`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_compra`
--

LOCK TABLES `ordenes_compra` WRITE;
/*!40000 ALTER TABLE `ordenes_compra` DISABLE KEYS */;
INSERT INTO `ordenes_compra` VALUES (16,'OC-001',23,1,'2026-05-12','2026-05-20','Recibida',268900.00,'OC de prueba Phase 6','2026-05-12 00:28:33',NULL),(17,'OC-002',23,1,'2026-05-13','2026-05-18','Borrador',50000.00,'Test integración OC','2026-05-13 19:26:38',NULL),(18,'OC-003',23,1,'2026-05-13',NULL,'Borrador',5000.00,NULL,'2026-05-13 19:26:40',NULL),(19,'OC-004',23,1,'2026-05-13','2026-05-18','Borrador',50000.00,'Test integración OC','2026-05-13 19:27:29',NULL),(20,'OC-005',23,1,'2026-05-13',NULL,'Borrador',5000.00,NULL,'2026-05-13 19:27:30',NULL),(21,'OC-006',23,1,'2026-05-13','2026-05-18','Borrador',50000.00,'Test integración OC','2026-05-13 19:28:18',NULL),(22,'OC-007',23,1,'2026-05-13','2026-05-18','Recibida',50000.00,'Test integración OC','2026-05-13 19:29:56',NULL),(23,'OC-008',23,1,'2026-05-13',NULL,'Borrador',5000.00,NULL,'2026-05-13 19:29:58',NULL),(24,'OC-009',23,1,'2026-05-13','2026-05-18','Recibida',50000.00,'Test integración OC','2026-05-13 19:33:51',NULL),(25,'OC-010',23,1,'2026-05-13',NULL,'Borrador',5000.00,NULL,'2026-05-13 19:33:52',NULL),(26,'OC-011',23,1,'2026-05-13','2026-05-18','Recibida',50000.00,'Test integración OC','2026-05-13 19:39:44',NULL),(27,'OC-012',23,1,'2026-05-13',NULL,'Borrador',5000.00,NULL,'2026-05-13 19:39:46',NULL),(28,'OC-013',23,1,'2026-05-13','2026-05-18','Recibida',50000.00,'Test integración OC','2026-05-13 19:44:17',NULL),(29,'OC-014',23,1,'2026-05-13',NULL,'Borrador',5000.00,NULL,'2026-05-13 19:44:18',NULL),(30,'OC-015',23,1,'2026-05-13','2026-05-18','Recibida',50000.00,'Test integración OC','2026-05-13 19:47:20',NULL),(31,'OC-016',23,1,'2026-05-13',NULL,'Borrador',5000.00,NULL,'2026-05-13 19:47:21',NULL),(32,'OC-017',23,1,'2026-05-13','2026-05-18','Recibida',50000.00,'Test integración OC','2026-05-13 19:56:21',NULL),(33,'OC-018',23,1,'2026-05-13',NULL,'Borrador',5000.00,NULL,'2026-05-13 19:56:22',NULL),(34,'OC-019',23,1,'2026-05-13','2026-05-18','Recibida',50000.00,'Test integración OC','2026-05-13 20:03:30',NULL),(35,'OC-020',23,1,'2026-05-13',NULL,'Borrador',5000.00,NULL,'2026-05-13 20:03:32',NULL);
/*!40000 ALTER TABLE `ordenes_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_compra_detalle`
--

DROP TABLE IF EXISTS `ordenes_compra_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_compra_detalle` (
  `id_detalle` int unsigned NOT NULL AUTO_INCREMENT,
  `ordenes_compra_id` int unsigned NOT NULL,
  `item_proveedor_id` int NOT NULL,
  `item_general_id` int DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unit` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `cantidad_recibida` decimal(10,2) DEFAULT '0.00',
  `recibido_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `ordenes_compra_id` (`ordenes_compra_id`),
  KEY `item_proveedor_id` (`item_proveedor_id`),
  CONSTRAINT `ordenes_compra_detalle_ibfk_1` FOREIGN KEY (`ordenes_compra_id`) REFERENCES `ordenes_compra` (`id_orden`) ON DELETE CASCADE,
  CONSTRAINT `ordenes_compra_detalle_ibfk_2` FOREIGN KEY (`item_proveedor_id`) REFERENCES `item_proveedor` (`id_item_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_compra_detalle`
--

LOCK TABLES `ordenes_compra_detalle` WRITE;
/*!40000 ALTER TABLE `ordenes_compra_detalle` DISABLE KEYS */;
INSERT INTO `ordenes_compra_detalle` VALUES (27,16,46,230,NULL,50.00,5378.00,268900.00,50.00,'2026-05-12 00:29:03'),(28,17,46,230,'Test',10.00,5000.00,50000.00,0.00,NULL),(29,18,46,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL),(30,19,46,230,'Test',10.00,5000.00,50000.00,0.00,NULL),(31,20,46,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL),(32,21,46,230,'Test',10.00,5000.00,50000.00,0.00,NULL),(33,22,46,230,'Test',10.00,5000.00,50000.00,10.00,'2026-05-13 19:29:57'),(34,23,46,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL),(35,24,46,230,'Test',10.00,5000.00,50000.00,10.00,'2026-05-13 19:33:51'),(36,25,46,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL),(37,26,46,230,'Test',10.00,5000.00,50000.00,10.00,'2026-05-13 19:39:45'),(38,27,46,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL),(39,28,46,230,'Test',10.00,5000.00,50000.00,10.00,'2026-05-13 19:44:18'),(40,29,46,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL),(41,30,46,230,'Test',10.00,5000.00,50000.00,10.00,'2026-05-13 19:47:20'),(42,31,46,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL),(43,32,46,230,'Test',10.00,5000.00,50000.00,10.00,'2026-05-13 19:56:21'),(44,33,46,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL),(45,34,46,230,'Test',10.00,5000.00,50000.00,10.00,'2026-05-13 20:03:31'),(46,35,46,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL);
/*!40000 ALTER TABLE `ordenes_compra_detalle` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-15 20:04:06
