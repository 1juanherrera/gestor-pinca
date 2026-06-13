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
-- Table structure for table `bodegas`
--

DROP TABLE IF EXISTS `bodegas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bodegas` (
  `id_bodegas` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` tinyint DEFAULT NULL COMMENT '0 inactiva 1 activa',
  `instalaciones_id` int NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_bodegas`),
  KEY `fk_bodegas_instalaciones1_idx` (`instalaciones_id`),
  KEY `idx_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_bodegas_instalaciones1` FOREIGN KEY (`instalaciones_id`) REFERENCES `instalaciones` (`id_instalaciones`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bodegas`
--

LOCK TABLES `bodegas` WRITE;
/*!40000 ALTER TABLE `bodegas` DISABLE KEYS */;
INSERT INTO `bodegas` VALUES (1,'Bodega principal','BODEGA INSUMOS, MATERIAS PRIMAS Y PRODUCTOS',1,1,NULL),(2,'Bodega 1','Aditivos técnicos, impermeabilizantes de alto desempeño y maquinaria pesada.',1,2,NULL),(3,'Juan Mina','Punto estratégico en la Vía Cordialidad, orientado al manejo de inventarios y distribución regional, con conexiones hacia rutas intermunicipales.',1,3,NULL),(8,'Laboratorio','Área de bodega con acondicionamiento tipo laboratorio',1,1,NULL),(15,'Centro de insumos','Área destinada al almacenamiento y distribución de insumos.',1,1,NULL),(16,'Depósito especializado','Espacio seguro para almacenamiento bajo condiciones controladas.',0,1,NULL),(18,'Bodega 2','Resinas base, solventes y una amplia gama de pinturas para acabados horneables.',1,2,NULL),(19,'Bodega 3','Estación de colorimetría con pastas pigmentadas, anticorrosivos y productos listos para despacho.',1,2,NULL),(21,'Patio','Almacenamiento masivo de solventes industriales, aglutinantes y selladores por volumen.',1,2,NULL);
/*!40000 ALTER TABLE `bodegas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_categoria`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES (1,'ESMALTE',NULL),(2,'PASTA',NULL),(3,'ANTICORROSIVO',NULL),(4,'BARNIZ',NULL),(5,'VINILO',NULL),(6,'EPOXICA',NULL),(7,'LACA',NULL);
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id_clientes` int NOT NULL AUTO_INCREMENT,
  `nombre_encargado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre_empresa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero_documento` bigint DEFAULT NULL,
  `direccion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ciudad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `plazo_pago` int DEFAULT '30' COMMENT 'Días de plazo: 0, 15, 30, 60, 90',
  `telefono` bigint DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` tinyint NOT NULL DEFAULT '2' COMMENT '1 Empresa 2 Particular',
  `estado` tinyint NOT NULL DEFAULT '1' COMMENT '1 activo 2 inactivo',
  `dias_credito` int DEFAULT '30' COMMENT 'Plazo de pago en días',
  `limite_credito` decimal(12,2) DEFAULT '0.00' COMMENT 'Cupo máximo de crédito',
  `credito_usado` decimal(12,2) DEFAULT '0.00' COMMENT 'Suma de saldos pendientes activos',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_clientes`),
  KEY `idx_clientes_deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Carlos Mendoza','Distribuidora Andina S.A.S',900123456,'Calle 45 #32-10, Barranquilla',NULL,30,3014567890,'c.mendoza@andina.com',2,1,30,5000000.00,125000.00,NULL),(2,'Juliana Pérez','Soluciones del Caribe Ltda',801987654,'Carrera 21 #55-22, Cartagena',NULL,30,3157894321,'juliana.perez@caribe.com',1,2,60,10000000.00,0.00,NULL),(3,'Mauricio Torres','Pinturas Torres & Cía',1023456789,'Av. Murillo #12-80, Barranquilla',NULL,30,3001122334,'m.torres@ptorres.com',2,1,30,0.00,0.00,NULL),(21,NULL,'Cliente Soft Delete Test 6a04d3f18d3e0',999591882,NULL,NULL,30,NULL,NULL,2,1,30,0.00,0.00,'2026-05-13 19:41:38'),(23,NULL,'Cliente Soft Delete Test 6a04d42031ae0',999146893,NULL,NULL,30,NULL,NULL,2,1,30,0.00,0.00,'2026-05-13 19:42:25');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion_sistema`
--

DROP TABLE IF EXISTS `configuracion_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion_sistema` (
  `id_configuracion` int unsigned NOT NULL AUTO_INCREMENT,
  `grupo` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `clave` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `valor` json DEFAULT NULL,
  `tipo` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'string',
  `descripcion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_configuracion`),
  UNIQUE KEY `clave` (`clave`),
  KEY `grupo` (`grupo`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion_sistema`
--

LOCK TABLES `configuracion_sistema` WRITE;
/*!40000 ALTER TABLE `configuracion_sistema` DISABLE KEYS */;
INSERT INTO `configuracion_sistema` VALUES (1,'tributaria','iva_default','19','number','Porcentaje IVA general (%) aplicado por defecto en facturas y compras.','2026-05-25 16:05:32','seed'),(2,'tributaria','retencion_fuente_pct','2.5','number','Retención en la fuente por compra (%) — varía por concepto y régimen.','2026-05-25 16:05:32','seed'),(3,'tributaria','retencion_iva_pct','15','number','ReteIVA: porcentaje del IVA pagado que se retiene al proveedor (%).','2026-05-25 16:05:32','seed'),(4,'tributaria','retencion_ica_default','11.04','number','ReteICA por mil — default Barranquilla. Ajustar por ciudad/actividad.','2026-05-25 16:05:32','seed'),(5,'tributaria','aplicar_iva_por_default','true','boolean','Si true, los formularios de compra activan el toggle IVA al abrirse.','2026-05-25 16:05:32','seed'),(6,'umbrales','stock_critico_dias','7','number','Días restantes de stock para considerar una MP \"crítica\" (rojo en dashboard / inventario).','2026-05-25 16:05:32','seed'),(7,'umbrales','stock_warning_dias','30','number','Días restantes para \"advertencia\" (amarillo). Por encima → \"ok\" (verde).','2026-05-25 16:05:32','seed'),(8,'umbrales','mora_warning_dias','30','number','Días de mora desde los cuales una factura entra en alerta amarilla.','2026-05-25 16:05:32','seed'),(9,'umbrales','mora_critica_dias','60','number','Días de mora desde los cuales una factura entra en alerta roja (crítica).','2026-05-25 16:05:32','seed'),(10,'umbrales','margen_minimo_pct','10','number','Margen (%) por debajo del cual el dashboard marca rentabilidad en rojo.','2026-05-25 16:05:32','seed'),(11,'umbrales','margen_objetivo_pct','20','number','Margen (%) objetivo: por encima la rentabilidad se muestra en verde.','2026-05-25 16:05:32','seed'),(12,'seguridad','jwt_expiracion_horas','8','number','Horas de validez del JWT desde su emisión. Tras este tiempo el usuario debe re-loguearse.','2026-05-25 16:05:32','seed'),(13,'seguridad','max_intentos_login','5','number','Cantidad máxima de intentos de login fallidos antes de bloquear la IP temporalmente.','2026-05-25 16:05:32','seed'),(14,'seguridad','ventana_intentos_segundos','900','number','Ventana en segundos durante la cual se cuentan los intentos fallidos (default 900 = 15 min).','2026-05-25 16:05:32','seed'),(15,'seguridad','password_min_caracteres','8','number','Longitud mínima requerida para contraseñas nuevas.','2026-05-25 16:05:32','seed'),(16,'financiero','margen_utilidad_default_pct','50','number','Margen de utilidad por defecto (%) cuando un costo no tiene `porcentaje_utilidad` explícito.','2026-05-25 16:05:32','seed'),(17,'comercial','dias_vencimiento_factura','30','number','Días desde la emisión hasta el vencimiento por default al crear/convertir una factura.','2026-05-25 16:05:32','seed'),(18,'comercial','dias_credito_default','30','number','Plazo de pago por default sugerido al crear un cliente nuevo.','2026-05-25 16:05:32','seed'),(19,'notificaciones','limit_default','30','number','Cantidad de notificaciones a devolver por defecto en la query.','2026-05-25 16:05:32','seed'),(20,'notificaciones','limit_maximo','100','number','Tope superior absoluto para evitar payloads excesivos.','2026-05-25 16:05:32','seed'),(21,'notificaciones','dias_alerta_vencimiento','3','number','Días previos al vencimiento de una factura para empezar a notificar.','2026-05-25 16:05:32','seed'),(22,'paginacion','page_size_default','25','number','Cantidad de filas por página default en tablas listables.','2026-05-25 16:05:32','seed'),(23,'paginacion','max_per_page','200','number','Tope máximo permitido para `?per_page=` en endpoints paginados.','2026-05-25 16:05:32','seed'),(24,'apariencia','avatar_palette','[{\"key\": \"default\", \"grad\": null, \"name\": \"Por rol\", \"preview\": \"from-zinc-400  to-zinc-600\"}, {\"key\": \"violet\", \"grad\": \"from-violet-500  to-purple-600\", \"name\": \"Violeta\", \"preview\": \"from-violet-500 to-purple-600\"}, {\"key\": \"blue\", \"grad\": \"from-blue-500    to-cyan-600\", \"name\": \"Azul\", \"preview\": \"from-blue-500   to-cyan-600\"}, {\"key\": \"emerald\", \"grad\": \"from-emerald-500 to-teal-600\", \"name\": \"Esmeralda\", \"preview\": \"from-emerald-500 to-teal-600\"}, {\"key\": \"amber\", \"grad\": \"from-amber-500   to-orange-600\", \"name\": \"Ámbar\", \"preview\": \"from-amber-500  to-orange-600\"}, {\"key\": \"rose\", \"grad\": \"from-rose-500    to-pink-600\", \"name\": \"Rosa\", \"preview\": \"from-rose-500   to-pink-600\"}, {\"key\": \"slate\", \"grad\": \"from-slate-600   to-zinc-800\", \"name\": \"Pizarra\", \"preview\": \"from-slate-600  to-zinc-800\"}, {\"key\": \"indigo\", \"grad\": \"from-indigo-500  to-fuchsia-600\", \"name\": \"Índigo\", \"preview\": \"from-indigo-500 to-fuchsia-600\"}]','json','Paleta de gradientes que cada usuario puede elegir para su avatar. Array JSON: [{key, name, grad, preview}].','2026-05-25 16:05:32','seed');
/*!40000 ALTER TABLE `configuracion_sistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `costos_indirectos`
--

DROP TABLE IF EXISTS `costos_indirectos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `costos_indirectos` (
  `id_costos_indirectos` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `categoria` enum('servicios','mano_de_obra','instalaciones','otros') NOT NULL,
  `valor_mensual` decimal(15,2) DEFAULT '0.00',
  `activo` tinyint(1) DEFAULT '1',
  `fecha_actualizacion` date DEFAULT NULL,
  PRIMARY KEY (`id_costos_indirectos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `costos_indirectos`
--

LOCK TABLES `costos_indirectos` WRITE;
/*!40000 ALTER TABLE `costos_indirectos` DISABLE KEYS */;
/*!40000 ALTER TABLE `costos_indirectos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `costos_item`
--

DROP TABLE IF EXISTS `costos_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `costos_item` (
  `id_costos_item` int NOT NULL AUTO_INCREMENT,
  `item_general_id` int NOT NULL,
  `costo_unitario` decimal(18,2) DEFAULT NULL,
  `costo_mp_galon` decimal(10,0) DEFAULT NULL,
  `costo_cunete` decimal(10,0) NOT NULL,
  `costo_tambor` decimal(10,0) NOT NULL,
  `periodo` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metodo_calculo` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_calculo` date DEFAULT NULL,
  `costo_mp_kg` decimal(10,0) DEFAULT NULL,
  `envase` decimal(18,2) DEFAULT NULL,
  `etiqueta` decimal(18,2) DEFAULT NULL,
  `bandeja` decimal(10,0) DEFAULT NULL,
  `plastico` decimal(10,0) DEFAULT NULL,
  `volumen` decimal(10,0) DEFAULT NULL,
  `precio_venta` decimal(18,2) DEFAULT NULL,
  `cantidad_total` decimal(10,0) DEFAULT NULL,
  `costo_mod` decimal(10,0) DEFAULT NULL COMMENT '0  inactivo\r\n1 activo',
  `estado` tinyint DEFAULT NULL,
  `porcentaje_utilidad` decimal(10,0) DEFAULT NULL,
  PRIMARY KEY (`id_costos_item`),
  KEY `item_general_id` (`item_general_id`),
  CONSTRAINT `costos_item_ibfk_1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `costos_item`
--

LOCK TABLES `costos_item` WRITE;
/*!40000 ALTER TABLE `costos_item` DISABLE KEYS */;
INSERT INTO `costos_item` VALUES (1,1,0.00,2000,0,0,NULL,'MANUAL','2025-06-07',0,4200.00,350.00,140,153,50,2000.00,0,600,NULL,20),(2,31,7000.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(3,32,11000.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(4,33,34050.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(5,34,27144.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(6,35,12691.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(7,36,4372.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(8,37,5000.00,0,0,0,NULL,'PROMED','2026-06-12',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(9,38,16300.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(10,39,17000.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(11,40,4400.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(12,41,14300.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(13,42,40.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(14,43,1550.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(15,44,4617.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(19,48,5400.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(21,50,12215.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(23,52,14152.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(25,54,12718.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(27,56,11447.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(28,57,1690.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(30,59,722.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(31,60,715.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(32,61,4300.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(34,63,8000.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(35,64,8000.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(36,65,1103.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(37,66,22700.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(38,67,43900.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(39,68,37300.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(40,69,22700.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(42,71,19500.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(43,72,33500.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(44,73,37200.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(45,74,21850.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(46,75,10400.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(47,76,8000.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(48,77,11466.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(49,78,13000.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(51,80,2900.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(52,81,17000.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(54,83,4617.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(55,84,22700.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(56,85,22700.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(57,86,11000.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(58,2,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,212,20000.00,0,600,NULL,75),(59,3,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,398,170000.00,0,600,NULL,NULL),(60,4,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,440,0.00,0,600,NULL,NULL),(61,5,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,50,0.00,0,600,NULL,NULL),(62,6,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,397,0.00,0,600,NULL,NULL),(63,7,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,396,0.00,0,600,NULL,NULL),(64,8,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,712,0.00,0,600,NULL,NULL),(65,9,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,616,0.00,0,600,NULL,NULL),(66,10,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,711,0.00,0,600,NULL,NULL),(67,11,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,50,0.00,0,600,NULL,NULL),(68,12,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,599,0.00,0,600,NULL,NULL),(69,13,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,578,0.00,0,600,NULL,NULL),(70,14,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,100,0.00,0,600,NULL,NULL),(71,15,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,100,0.00,0,600,NULL,NULL),(72,16,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,212,0.00,0,600,NULL,NULL),(73,17,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,213,0.00,0,600,NULL,NULL),(74,18,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,100,0.00,0,600,NULL,NULL),(75,19,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,3600.00,350.00,140,153,50,0.00,0,600,NULL,NULL),(76,20,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,328,0.00,0,150,NULL,NULL),(77,21,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,345,0.00,0,150,NULL,NULL),(78,22,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,488,0.00,0,150,NULL,NULL),(79,23,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,119,0.00,0,150,NULL,NULL),(80,24,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,961,0.00,0,150,NULL,NULL),(81,25,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,1018,0.00,0,150,NULL,NULL),(82,26,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,874,0.00,0,150,NULL,NULL),(83,27,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,851,0.00,0,150,NULL,NULL),(84,28,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,833,0.00,0,150,NULL,NULL),(85,29,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,748,0.00,0,150,NULL,NULL),(86,30,0.00,0,0,0,NULL,'MANUAL','2025-06-07',0,0.00,0.00,0,0,376,0.00,0,150,NULL,NULL),(87,87,4372.00,0,0,0,NULL,'MANUAL','2025-06-10',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(88,88,4400.00,0,0,0,NULL,'MANUAL','2025-06-10',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(89,89,4372.00,0,0,0,NULL,'MANUAL','2025-06-10',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(90,90,4372.00,0,0,0,NULL,'MANUAL','2025-06-10',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(92,92,16300.00,0,0,0,NULL,'MANUAL','2025-06-10',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(93,93,14152.00,0,0,0,NULL,'MANUAL','2025-06-10',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(95,95,17000.00,0,0,0,NULL,'MANUAL','2025-06-10',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(96,96,11447.00,0,0,0,NULL,'MANUAL','2025-06-10',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(97,97,22700.00,0,0,0,NULL,'MANUAL','2025-06-10',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(108,100,8000.00,0,0,0,NULL,'MANUAL','2025-06-15',0,0.00,0.00,0,0,0,0.00,0,0,NULL,NULL),(147,133,0.00,0,0,0,NULL,'Manual','2026-01-16',0,0.00,0.00,0,0,213,0.00,1,0,1,NULL),(164,223,0.00,NULL,0,0,NULL,NULL,NULL,NULL,0.00,0.00,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL),(165,224,0.00,0,0,0,'2026-04','Manual','2026-04-18',0,0.00,0.00,0,0,50,0.00,NULL,0,1,NULL),(167,231,5378.00,0,0,0,'2026-04','Manual','2026-04-22',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL),(168,232,2000.00,0,0,0,'2026-04','Manual','2026-04-22',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL),(169,233,2000.00,0,0,0,'2026-04','Manual','2026-04-22',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL),(171,235,20.00,0,0,0,'2026-04','Manual','2026-04-22',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL),(172,236,1000.00,0,0,0,'2026-04','Manual','2026-04-22',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL),(176,241,2000.00,0,0,0,'2026-04','Manual','2026-04-22',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL),(177,242,2000.00,0,0,0,'2026-04','Manual','2026-04-22',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL),(178,275,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,213,0.00,0,0,1,NULL),(179,276,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,200,0.00,0,0,1,NULL),(180,277,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,100,0.00,0,0,1,NULL),(181,278,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(182,279,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,0,0.00,0,0,1,NULL),(183,280,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(184,281,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(185,282,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,100,0.00,0,0,1,NULL),(186,283,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,200,0.00,0,0,1,NULL),(187,284,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(188,285,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,25,0.00,0,0,1,NULL),(189,286,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,25,0.00,0,0,1,NULL),(190,287,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(191,288,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(192,289,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,100,0.00,0,0,1,NULL),(193,290,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,100,0.00,0,0,1,NULL),(194,291,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(195,292,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,100,0.00,0,0,1,NULL),(196,293,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(197,294,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(198,295,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,15,0.00,0,0,1,NULL),(199,296,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,100,0.00,0,0,1,NULL),(200,297,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,5,0.00,0,0,1,NULL),(201,298,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,4,0.00,0,0,1,NULL),(202,299,0.00,0,0,0,'2026-05','Manual','2026-05-07',0,0.00,0.00,0,0,50,0.00,0,0,1,NULL),(203,230,5157.50,NULL,0,0,NULL,'PROMED','2026-05-25',NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL),(204,300,5040.00,0,0,0,'2026-05','PROMED','2026-05-27',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL),(205,301,7620.00,0,0,0,'2026-05','PROMED','2026-05-17',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL),(206,302,0.00,0,0,0,'2026-05','Catálo','2026-05-16',0,0.00,0.00,0,0,1,0.00,NULL,0,1,NULL);
/*!40000 ALTER TABLE `costos_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `costos_produccion`
--

DROP TABLE IF EXISTS `costos_produccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `costos_produccion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `costo_unitario` mediumint DEFAULT NULL,
  `costo_mp_galon` tinyint DEFAULT NULL,
  `periodo` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metodo_calculo` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_calculo` varchar(0) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `costo_mp_kg` tinyint DEFAULT NULL,
  `envase` smallint DEFAULT NULL,
  `etiqueta` smallint DEFAULT NULL,
  `bandeja` smallint DEFAULT NULL,
  `plastico` smallint DEFAULT NULL,
  `costo_total` tinyint DEFAULT NULL,
  `volumen` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `precio_venta` tinyint DEFAULT NULL,
  `cantidad_total` tinyint DEFAULT NULL,
  `costo_mod` smallint DEFAULT NULL,
  `preparaciones_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_costos_produccion_preparaciones1_idx` (`preparaciones_id`),
  CONSTRAINT `fk_costos_produccion_preparaciones` FOREIGN KEY (`preparaciones_id`) REFERENCES `preparaciones` (`id_preparaciones`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `costos_produccion`
--

LOCK TABLES `costos_produccion` WRITE;
/*!40000 ALTER TABLE `costos_produccion` DISABLE KEYS */;
/*!40000 ALTER TABLE `costos_produccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `costos_snapshot`
--

DROP TABLE IF EXISTS `costos_snapshot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `costos_snapshot` (
  `id_costos_snapshot` int unsigned NOT NULL AUTO_INCREMENT,
  `item_general_id` int NOT NULL COMMENT 'FK a item_general',
  `fecha` date NOT NULL COMMENT 'Fecha del snapshot (YYYY-MM-DD)',
  `estado` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'completo | incompleto al momento del snapshot',
  `volumen_base` decimal(15,4) NOT NULL DEFAULT '1.0000',
  `costo_mp_total` decimal(15,2) DEFAULT NULL COMMENT 'NULL si producto incompleto',
  `costo_mp_por_unidad` decimal(15,2) DEFAULT NULL,
  `costo_empaque_mod` decimal(15,2) NOT NULL DEFAULT '0.00',
  `costo_total` decimal(15,2) DEFAULT NULL,
  `porcentaje_utilidad` decimal(5,2) NOT NULL DEFAULT '0.00',
  `precio_venta_calc` decimal(15,2) DEFAULT NULL,
  `mps_total` int NOT NULL DEFAULT '0',
  `mps_cubiertas` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_costos_snapshot`),
  UNIQUE KEY `uq_snapshot_item_fecha` (`item_general_id`,`fecha`),
  KEY `item_general_id_fecha` (`item_general_id`,`fecha`),
  KEY `fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `costos_snapshot`
--

LOCK TABLES `costos_snapshot` WRITE;
/*!40000 ALTER TABLE `costos_snapshot` DISABLE KEYS */;
/*!40000 ALTER TABLE `costos_snapshot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cotizaciones`
--

DROP TABLE IF EXISTS `cotizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotizaciones` (
  `id_cotizaciones` int unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `cliente_id` int NOT NULL,
  `fecha_cotizacion` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(12,2) NOT NULL DEFAULT '0.00',
  `retencion` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` enum('Borrador','Enviada','Aceptada','Rechazada','Vencida','Convertida') NOT NULL DEFAULT 'Borrador',
  `observaciones` text,
  `facturas_id` int DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cotizaciones`),
  UNIQUE KEY `numero` (`numero`),
  KEY `cliente_id` (`cliente_id`),
  KEY `facturas_id` (`facturas_id`),
  KEY `idx_cotizaciones_deleted_at` (`deleted_at`),
  CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id_clientes`),
  CONSTRAINT `cotizaciones_ibfk_2` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotizaciones`
--

LOCK TABLES `cotizaciones` WRITE;
/*!40000 ALTER TABLE `cotizaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `cotizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cotizaciones_detalle`
--

DROP TABLE IF EXISTS `cotizaciones_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotizaciones_detalle` (
  `id_detalle` int unsigned NOT NULL AUTO_INCREMENT,
  `cotizaciones_id` int unsigned NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `precio_unit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `descuento_pct` decimal(5,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_detalle`),
  KEY `cotizaciones_id` (`cotizaciones_id`),
  CONSTRAINT `cotizaciones_detalle_ibfk_1` FOREIGN KEY (`cotizaciones_id`) REFERENCES `cotizaciones` (`id_cotizaciones`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotizaciones_detalle`
--

LOCK TABLES `cotizaciones_detalle` WRITE;
/*!40000 ALTER TABLE `cotizaciones_detalle` DISABLE KEYS */;
INSERT INTO `cotizaciones_detalle` VALUES (1,1,'Pintura base agua blanca 4L',2.00,85000.00,0.00,170000.00),(2,1,'Sellador multiusos 3.6L',1.00,88000.00,0.00,88000.00),(3,1,'Rodillos premium 9\"',5.00,8400.00,0.00,42000.00),(4,3,'Pintura exterior mate 4L',4.00,92000.00,0.00,368000.00),(5,3,'Lija al agua grano 220',20.00,7600.00,0.00,152000.00),(6,4,'Pintura epóxica 4L',6.00,125000.00,5.00,712500.00),(7,4,'Catalizador epóxico 1L',6.00,45000.00,0.00,270000.00),(8,5,'Thinner acrílico galón',5.00,50000.00,0.00,250000.00);
/*!40000 ALTER TABLE `cotizaciones_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_facturas`
--

DROP TABLE IF EXISTS `detalle_facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_facturas` (
  `id_detalle_facturas` int NOT NULL AUTO_INCREMENT,
  `cantidad` tinyint DEFAULT NULL,
  `precio_unitario` decimal(7,1) DEFAULT NULL,
  `subtotal` decimal(7,1) DEFAULT NULL,
  `facturas_id` int DEFAULT NULL,
  `item_general_id` int DEFAULT NULL,
  PRIMARY KEY (`id_detalle_facturas`),
  UNIQUE KEY `id_detalle_facturas_UNIQUE` (`id_detalle_facturas`),
  KEY `fk_detalle_facturas_facturas1_idx` (`facturas_id`),
  KEY `fk_detalle_facturas_item_general1_idx` (`item_general_id`),
  CONSTRAINT `fk_detalle_facturas_facturas1` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`),
  CONSTRAINT `fk_detalle_facturas_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_facturas`
--

LOCK TABLES `detalle_facturas` WRITE;
/*!40000 ALTER TABLE `detalle_facturas` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresa`
--

DROP TABLE IF EXISTS `empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa` (
  `id_empresa` int NOT NULL AUTO_INCREMENT,
  `nit` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `razon_social` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ciudad` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `celular` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pagina_web` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `locale` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'es-CO',
  `moneda` varchar(5) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'COP',
  `logo_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresa`
--

LOCK TABLES `empresa` WRITE;
/*!40000 ALTER TABLE `empresa` DISABLE KEYS */;
INSERT INTO `empresa` VALUES (1,'901314182','PINTURAS INDUSTRIALES DEL CARIBE S.A.S','Comercio al por mayor de materiales de construcción, artículos de ferretería, pinturas, productos de vidrio, equipo y materiales de fontanería y calefacción. - 4663','Barranquilla','Calle 99 # 6-59','3019794729','+57 3019794729','https://pinca.com.co/','pinca.sas@hotmail.com','es-CO','COP','/uploads/empresa/logo_default.png');
/*!40000 ALTER TABLE `empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturas` (
  `id_facturas` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cliente_id` int DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `saldo_pendiente` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `estado` enum('Pendiente','Parcial','Pagada','Vencida','Anulada') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pendiente',
  `subtotal` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(10,2) DEFAULT NULL,
  `retencion` decimal(10,2) DEFAULT NULL,
  `movimiento_inventario_id` int DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_facturas`),
  UNIQUE KEY `id_facturas_UNIQUE` (`id_facturas`),
  KEY `fk_facturas_movimientos_inventario1_idx` (`movimiento_inventario_id`),
  KEY `idx_facturas_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_facturas_movimientos_inventario1` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimiento_inventario` (`id_movimiento_inventario`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
/*!40000 ALTER TABLE `facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas_detalle`
--

DROP TABLE IF EXISTS `facturas_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturas_detalle` (
  `id_detalle` int unsigned NOT NULL AUTO_INCREMENT,
  `facturas_id` int NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `precio_unit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `descuento_pct` decimal(5,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_detalle`),
  KEY `facturas_id` (`facturas_id`),
  CONSTRAINT `facturas_detalle_ibfk_1` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas_detalle`
--

LOCK TABLES `facturas_detalle` WRITE;
/*!40000 ALTER TABLE `facturas_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `facturas_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formulaciones`
--

DROP TABLE IF EXISTS `formulaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formulaciones` (
  `id_formulaciones` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `estado` tinyint DEFAULT NULL COMMENT '0 inactiva\\n1 activa',
  `defecto` tinyint DEFAULT '0' COMMENT '1 por defecto',
  `version_actual` int NOT NULL DEFAULT '1',
  `item_general_id` int DEFAULT NULL,
  PRIMARY KEY (`id_formulaciones`),
  UNIQUE KEY `id_formulaciones_UNIQUE` (`id_formulaciones`),
  KEY `fk_formulaciones_item_general1_idx` (`item_general_id`),
  CONSTRAINT `fk_formulaciones_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formulaciones`
--

LOCK TABLES `formulaciones` WRITE;
/*!40000 ALTER TABLE `formulaciones` DISABLE KEYS */;
INSERT INTO `formulaciones` VALUES (1,'PREPARACION BARNIZ TRANSPARENTE BRILLANTE',NULL,1,1,2,1),(2,'PREPARACION ESMALTE BLANCO',NULL,1,1,2,2),(3,'PREPARACION ESMALTE CAOBA',NULL,1,1,1,3),(4,'PREPARACION ESMALTE NEGRO MATE',NULL,1,1,1,4),(5,'PREPARACION ESMALTE ROJO FIESTA',NULL,1,1,2,5),(6,'PREPARACION ESMALTE NEGRO BRILLANTE',NULL,1,1,1,6),(7,'PREPARACION ESMALTE VERDE ESMERALDA',NULL,1,1,1,7),(8,'PREPARACION ESMALTE GRIS PLATA',NULL,1,1,1,8),(9,'PREPARACION ESMALTE AZUL ESPAÑOL',NULL,1,1,1,9),(10,'PREPARACION ESMALTE BLANCO MATE',NULL,1,1,1,10),(11,'PREPARACION ESMALTE AMARILLO',NULL,1,1,2,11),(12,'PREPARACION ESMALTE NARANJA',NULL,1,1,1,12),(13,'PREPARACION ESMALTE TABACO',NULL,1,1,1,13),(14,'PREPARACION ANTICORROSIVO GRIS',NULL,1,1,2,14),(15,'PREPARACION ANTICORROSIVO NEGRO',NULL,1,1,2,15),(16,'PREPARACION ANTICORROSIVO AMARILLO',NULL,1,1,1,16),(17,'PREPARACION ANTICORROSIVO ROJO',NULL,1,1,1,17),(18,'PREPARACION ANTICORROSIVO BLANCO',NULL,1,1,2,18),(19,'PREPARACION ANTICORROSIVO VERDE',NULL,1,1,2,19),(20,'PREPARACION PASTA ESMALTE VERDE ENTONADOR',NULL,1,1,1,20),(21,'PREPARACION PASTA ESMALTE AZUL ENTONADOR',NULL,1,1,1,21),(22,'PREPARACION PASTA ESMALTE NEGRO',NULL,1,1,1,22),(23,'PREPARACION PASTA ESMALTE ROJO CARMIN 57:1',NULL,1,1,1,23),(24,'PREPARACION PASTA ESMALTE NARANJA',NULL,1,1,1,24),(25,'PREPARACION PASTA ESMALTE AMARILLO',NULL,1,1,1,25),(26,'PREPARACION PASTA ESMALTE CAOBA',NULL,1,1,1,26),(27,'PREPARACION PASTA ESMALTE AMARILLO OXIDO',NULL,1,1,1,27),(28,'PREPARACION PASTA ESMALTE ROJO OXIDO',NULL,1,1,1,28),(29,'PREPARACION PASTA ESMALTE BLANCO',NULL,1,1,1,29),(30,'PREPARACION PASTA ESMALTE TABACO',NULL,1,1,1,30),(31,'FORMULACION VINILO T1 BLANCO',NULL,1,1,1,133),(32,'FORMULACION EPOXICA TRANSPARENTE',NULL,1,1,1,224),(33,'FORMULACION VINILO BLANCO TIPO 2',NULL,1,1,1,275),(34,'FORMULACION VINILO BLANCO TIPO 3',NULL,1,1,1,276),(35,'FORMULACION ESMALTE AZUL REAL',NULL,1,1,1,277),(36,'FORMULACION LACA CATALIZADA BRILLANTE',NULL,1,1,1,278),(37,'FORMULACION PASTA OCRE PARA VINILO',NULL,1,1,1,279),(38,'FORMULACION VINILO OCRE T1',NULL,1,1,1,280),(39,'FORMULACION ESMALTE AMARILLO CATERPILLAR',NULL,1,1,1,281),(40,'FORMULACION ESMALTE NEGRO',NULL,1,1,1,282),(41,'FORMULACION ESMALTE BLANCO T1',NULL,1,1,1,283),(42,'FORMULACION ESMALTE BLANCO 4X1',NULL,1,1,1,284),(43,'FORMULACION ESMALTE BLANCO ECONOMICO JJ',NULL,1,1,1,285),(44,'FORMULACION ESMALTE ECONOMICO BLANCO JH',NULL,1,1,1,286),(45,'FORMULACION ESMALTE DORADO',NULL,1,1,1,287),(46,'FORMULACION ANTICORROSIVO CROMATO ZN',NULL,1,1,1,288),(47,'FORMULACION ESMALTE DE ALUMINIO',NULL,1,1,1,289),(48,'FORMULACION EPOXICA BLANCO',NULL,1,1,1,290),(49,'FORMULACION EPOXICA NEGRA',NULL,1,1,1,291),(50,'FORMULACION EPOXICA GRIS',NULL,1,1,1,292),(51,'FORMULACION EPOXICA NEGRA RESINA 100%',NULL,1,1,1,293),(52,'FORMULACION EPOXICA POLIAMIDA VERDE',NULL,1,1,1,294),(53,'FORMULACION EPOXICA AZUL',NULL,1,1,1,295),(54,'FORMULACION EPOXICA ROJO OXIDO',NULL,1,1,1,296),(55,'FORMULACION ESM EPOXI SILICATO BLANCO',NULL,1,1,1,297),(56,'FORMULACION ESM EPOXI SILICATO VERDE',NULL,1,1,1,298),(57,'FORMULACION ESMALTE EPOXICO AMARILLO',NULL,1,1,1,299);
/*!40000 ALTER TABLE `formulaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formulaciones_versiones`
--

DROP TABLE IF EXISTS `formulaciones_versiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formulaciones_versiones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formulacion_id` int NOT NULL,
  `version_num` int NOT NULL,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ingredientes` json NOT NULL,
  `notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_formulacion_version` (`formulacion_id`,`version_num`),
  KEY `idx_fv_formulacion` (`formulacion_id`),
  CONSTRAINT `formulaciones_versiones_formulacion_id_foreign` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id_formulaciones`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formulaciones_versiones`
--

LOCK TABLES `formulaciones_versiones` WRITE;
/*!40000 ALTER TABLE `formulaciones_versiones` DISABLE KEYS */;
INSERT INTO `formulaciones_versiones` VALUES (1,1,1,'PREPARACIÓN BARNIZ TRANSPARENTE BIRILLANTE',NULL,'[{\"cantidad\": \"932.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"3.72\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"METIL ETIL CETOXIMA\", \"item_general_id\": \"32\"}, {\"cantidad\": \"6.52\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"10.25\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"9.32\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"301.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 #3\", \"item_general_id\": \"36\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(2,2,1,'PREPARACION ESMALTE BLANCO',NULL,'[{\"cantidad\": \"914.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"425.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"293.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"2.63\", \"porcentaje\": null, \"item_codigo\": \"SOZ016\", \"item_nombre\": \"OCTOATO DE ZINC AL 16%\", \"item_general_id\": \"38\"}, {\"cantidad\": \"16.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"14.20\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"470.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"4.70\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"5.20\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"9.37\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"14.72\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"13.40\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"197.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 #3\", \"item_general_id\": \"36\"}, {\"cantidad\": \"200.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(3,3,1,'PREPARACION ESMALTE CAOBA',NULL,'[{\"cantidad\": \"775.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"103.00\", \"porcentaje\": null, \"item_codigo\": \"PAS026\", \"item_nombre\": \"PASTA ESMALTE CAOBA\", \"item_general_id\": \"26\"}, {\"cantidad\": \"8.70\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"290.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"3.30\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"5.78\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"9.10\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"8.26\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"113.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 #3\", \"item_general_id\": \"36\"}, {\"cantidad\": \"114.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(4,4,1,'PREPARACION ESMALTE NEGRO MATE',NULL,'[{\"cantidad\": \"775.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"224.00\", \"porcentaje\": null, \"item_codigo\": \"CTA011\", \"item_nombre\": \"MICROTALC C 20\", \"item_general_id\": \"47\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"MSI006\", \"item_nombre\": \"CELITE 499\", \"item_general_id\": \"48\"}, {\"cantidad\": \"12.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"ORGANOCLAY BK 884\", \"item_general_id\": \"81\"}, {\"cantidad\": \"6.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"125.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"8.70\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"290.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"2.90\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"3.35\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"5.86\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"9.21\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"8.37\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"227.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(5,5,1,'PREPARACIÓN ESMALTE ROJO FIESTA',NULL,'[{\"cantidad\": \"775.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"36.56\", \"porcentaje\": null, \"item_codigo\": \"PE1033\", \"item_nombre\": \"PASTA ESMALTE ROJO 57:1\", \"item_general_id\": \"50\"}, {\"cantidad\": \"79.40\", \"porcentaje\": null, \"item_codigo\": \"PAS024\", \"item_nombre\": \"PASTA ESMALTE NARANJA\", \"item_general_id\": \"24\"}, {\"cantidad\": \"6.00\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"200.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"3.33\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"5.83\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"9.16\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"8.32\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"227.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 #3\", \"item_general_id\": \"36\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(6,6,1,'PREPARACION ESMALTE NEGRO BRILLANTE',NULL,'[{\"cantidad\": \"775.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"125.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"5.70\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"190.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.90\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"3.35\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"5.86\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"9.21\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"8.37\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"227.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(7,7,1,'PREPARACION ESMALTE VERDE ESMERALDA',NULL,'[{\"cantidad\": \"775.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"62.00\", \"porcentaje\": null, \"item_codigo\": \"PE1010\", \"item_nombre\": \"PASTA AMARILLO CROMO MEDIO\", \"item_general_id\": \"52\"}, {\"cantidad\": \"10.40\", \"porcentaje\": null, \"item_codigo\": \"PE1021\", \"item_nombre\": \"PASTA ESMALTE AZUL FTALO 15:3\", \"item_general_id\": \"56\"}, {\"cantidad\": \"108.00\", \"porcentaje\": null, \"item_codigo\": \"PE1040\", \"item_nombre\": \"PASTA VERDE FTALO\", \"item_general_id\": \"54\"}, {\"cantidad\": \"6.20\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"205.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"2.10\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"3.46\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"6.05\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"9.51\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"8.65\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"113.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 #3\", \"item_general_id\": \"36\"}, {\"cantidad\": \"114.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(8,8,1,'PREPARACION ESMALTE GRIS PLATA',NULL,'[{\"cantidad\": \"425.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"251.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"2.63\", \"porcentaje\": null, \"item_codigo\": \"SOZ016\", \"item_nombre\": \"OCTOATO DE ZINC AL 16%\", \"item_general_id\": \"38\"}, {\"cantidad\": \"16.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"3.30\", \"porcentaje\": null, \"item_codigo\": \"PAS027\", \"item_nombre\": \"PASTA ESMALTE AMARILLO OXIDO\", \"item_general_id\": \"27\"}, {\"cantidad\": \"17.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"14.20\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"470.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"4.70\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"5.20\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"9.37\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"14.72\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"13.40\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"197.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 #3\", \"item_general_id\": \"36\"}, {\"cantidad\": \"200.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(9,9,1,'PREPARACION ESMALTE AZUL ESPAÑOL',NULL,'[{\"cantidad\": \"225.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"56.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"0.70\", \"porcentaje\": null, \"item_codigo\": \"SOZ016\", \"item_nombre\": \"OCTOATO DE ZINC AL 16%\", \"item_general_id\": \"38\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"168.00\", \"porcentaje\": null, \"item_codigo\": \"PE1021\", \"item_nombre\": \"PASTA ESMALTE AZUL FTALO 15:3\", \"item_general_id\": \"56\"}, {\"cantidad\": \"11.20\", \"porcentaje\": null, \"item_codigo\": \"PE1033\", \"item_nombre\": \"PASTA ESMALTE ROJO 57:1\", \"item_general_id\": \"50\"}, {\"cantidad\": \"9.70\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"323.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"3.23\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"5.40\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"9.45\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"14.86\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"13.51\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"197.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 #3\", \"item_general_id\": \"36\"}, {\"cantidad\": \"165.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(10,10,1,'PREPARACION ESMALTE BLANCO MATE',NULL,'[{\"cantidad\": \"1173.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"288.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"435.00\", \"porcentaje\": null, \"item_codigo\": \"CCC002\", \"item_nombre\": \"OMYACARB UF\", \"item_general_id\": \"57\"}, {\"cantidad\": \"84.00\", \"porcentaje\": null, \"item_codigo\": \"MSI006\", \"item_nombre\": \"CELITE 499\", \"item_general_id\": \"48\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ016\", \"item_nombre\": \"OCTOATO DE ZINC AL 16%\", \"item_general_id\": \"38\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"14.30\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"477.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"4.80\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"4.69\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"8.20\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"12.90\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"11.70\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"433.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(11,11,1,'PREPARACION ESMALTE AMARILLO',NULL,'[{\"cantidad\": \"1033.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"294.70\", \"porcentaje\": null, \"item_codigo\": \"PE1010\", \"item_nombre\": \"PASTA AMARILLO CROMO MEDIO\", \"item_general_id\": \"52\"}, {\"cantidad\": \"11.13\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"371.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"3.70\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"4.72\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"8.26\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"13.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"11.81\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"391.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(12,12,1,'PREPARACION ESMALTE NARANJA',NULL,'[{\"cantidad\": \"1033.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"180.00\", \"porcentaje\": null, \"item_codigo\": \"PAS024\", \"item_nombre\": \"PASTA ESMALTE NARANJA\", \"item_general_id\": \"24\"}, {\"cantidad\": \"77.00\", \"porcentaje\": null, \"item_codigo\": \"PE1010\", \"item_nombre\": \"PASTA AMARILLO CROMO MEDIO\", \"item_general_id\": \"52\"}, {\"cantidad\": \"11.00\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"363.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"3.66\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"4.64\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"8.13\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"12.77\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"11.61\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"391.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(13,13,1,'PREPARACION ESMALTE TABACO',NULL,'[{\"cantidad\": \"1033.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"190.00\", \"porcentaje\": null, \"item_codigo\": \"PAS030\", \"item_nombre\": \"PASTA ESMALTE TABACO\", \"item_general_id\": \"30\"}, {\"cantidad\": \"11.00\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"363.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"3.60\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"4.50\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"7.90\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"12.40\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"11.30\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"391.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(14,14,1,'PREPARACION ANTICORROSIVO GRIS',NULL,'[{\"cantidad\": \"1056.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"186.00\", \"porcentaje\": null, \"item_codigo\": \"PED007\", \"item_nombre\": \"DIOXIDO DE TITANIO SULFATO 2196\", \"item_general_id\": \"77\"}, {\"cantidad\": \"848.00\", \"porcentaje\": null, \"item_codigo\": \"CTA025\", \"item_nombre\": \"MICROTALC C 20\", \"item_general_id\": \"59\"}, {\"cantidad\": \"70.00\", \"porcentaje\": null, \"item_codigo\": \"CCC004\", \"item_nombre\": \"CARBONATO DE CALCIO HI WHITE\", \"item_general_id\": \"60\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"17.80\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"593.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"5.93\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"4.30\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"7.40\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"11.60\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"10.60\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"550.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(15,15,1,'PREPARACION ANTICORROSIVO NEGRO',NULL,'[{\"cantidad\": \"256.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"37.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"2.30\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"46.00\", \"porcentaje\": null, \"item_codigo\": \"CCC004\", \"item_nombre\": \"CARBONATO DE CALCIO HI WHITE\", \"item_general_id\": \"60\"}, {\"cantidad\": \"132.00\", \"porcentaje\": null, \"item_codigo\": \"CTA025\", \"item_nombre\": \"MICROTALC C 20\", \"item_general_id\": \"59\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"AAS012\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"79\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"3.70\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"123.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.30\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"1.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"2.80\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"89.60\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(16,16,1,'PREPARACION ANTICORROSIVO AMARILLO',NULL,'[{\"cantidad\": \"274.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"47.00\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"220.00\", \"porcentaje\": null, \"item_codigo\": \"CTA025\", \"item_nombre\": \"MICROTALC C 20\", \"item_general_id\": \"59\"}, {\"cantidad\": \"18.00\", \"porcentaje\": null, \"item_codigo\": \"CCC004\", \"item_nombre\": \"CARBONATO DE CALCIO HI WHITE\", \"item_general_id\": \"60\"}, {\"cantidad\": \"1.30\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"6.50\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"4.80\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"160.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.60\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"1.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"1.92\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"2.74\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"142.60\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(17,17,1,'PREPARACION ANTICORROSIVO ROJO',NULL,'[{\"cantidad\": \"274.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"58.00\", \"porcentaje\": null, \"item_codigo\": \"PER030\", \"item_nombre\": \"OXIDO DE HIERRO ROJO R-5530\", \"item_general_id\": \"64\"}, {\"cantidad\": \"220.00\", \"porcentaje\": null, \"item_codigo\": \"CTA025\", \"item_nombre\": \"MICROTALC C 20\", \"item_general_id\": \"59\"}, {\"cantidad\": \"18.00\", \"porcentaje\": null, \"item_codigo\": \"CCC004\", \"item_nombre\": \"CARBONATO DE CALCIO HI WHITE\", \"item_general_id\": \"60\"}, {\"cantidad\": \"1.30\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"6.50\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"4.70\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"155.60\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.55\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"1.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"1.92\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"2.74\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"142.60\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(18,18,1,'PREPARACION ANTICORROSIVO BLANCO',NULL,'[{\"cantidad\": \"1056.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"165.00\", \"porcentaje\": null, \"item_codigo\": \"PED007\", \"item_nombre\": \"DIOXIDO DE TITANIO SULFATO 2196\", \"item_general_id\": \"77\"}, {\"cantidad\": \"230.00\", \"porcentaje\": null, \"item_codigo\": \"CTA020\", \"item_nombre\": \"MICROTALC 20\", \"item_general_id\": \"65\"}, {\"cantidad\": \"688.00\", \"porcentaje\": null, \"item_codigo\": \"CCC004\", \"item_nombre\": \"CARBONATO DE CALCIO HI WHITE\", \"item_general_id\": \"60\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ016\", \"item_nombre\": \"OCTOATO DE ZINC AL 16%\", \"item_general_id\": \"38\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"17.55\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"585.26\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"5.85\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"4.30\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"7.40\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"11.60\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"10.60\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"550.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(19,19,1,'PREPARACION ANTICORROSIVO VERDE',NULL,'[{\"cantidad\": \"256.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"36.00\", \"porcentaje\": null, \"item_codigo\": \"PED007\", \"item_nombre\": \"DIOXIDO DE TITANIO SULFATO 2196\", \"item_general_id\": \"77\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"PE1021\", \"item_nombre\": \"PASTA ESMALTE AZUL 15:3\", \"item_general_id\": \"96\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"2.30\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"46.00\", \"porcentaje\": null, \"item_codigo\": \"CCC004\", \"item_nombre\": \"CARBONATO DE CALCIO HI WHITE\", \"item_general_id\": \"60\"}, {\"cantidad\": \"132.00\", \"porcentaje\": null, \"item_codigo\": \"CTA025\", \"item_nombre\": \"MICROTALC C 20\", \"item_general_id\": \"59\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"3.90\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"130.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.30\", \"porcentaje\": null, \"item_codigo\": \"AET004\", \"item_nombre\": \"SULFATO DE MAGNESIO\", \"item_general_id\": \"43\"}, {\"cantidad\": \"1.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"2.80\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"89.60\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(20,20,1,'PREPARACION PASTA ESMALTE VERDE ENTONADOR',NULL,'[{\"cantidad\": \"186.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"METIL ETIL CETOXIMA\", \"item_general_id\": \"32\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"ADI002\", \"item_nombre\": \"TROYSPERSE CD1\", \"item_general_id\": \"66\"}, {\"cantidad\": \"50.00\", \"porcentaje\": null, \"item_codigo\": \"PEV053\", \"item_nombre\": \"PIGMENTO VERDE FTALO 7\", \"item_general_id\": \"67\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"76.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(21,21,1,'PREPARACION PASTA ESMALTE AZUL ENTONADOR',NULL,'[{\"cantidad\": \"186.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"METIL ETIL CETOXIMA\", \"item_general_id\": \"32\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"AAS012\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"79\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"SAM023\", \"item_nombre\": \"METANOL\", \"item_general_id\": \"80\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"52.00\", \"porcentaje\": null, \"item_codigo\": \"PEA041\", \"item_nombre\": \"PIGMENTO AZUL FTALO 15;3\", \"item_general_id\": \"68\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"ADI010\", \"item_nombre\": \"EDAPLAN 918\", \"item_general_id\": \"97\"}, {\"cantidad\": \"76.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(22,22,1,'PREPARACION PASTA ESMALTE NEGRO',NULL,'[{\"cantidad\": \"242.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"3.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"9.00\", \"porcentaje\": null, \"item_codigo\": \"ADI010\", \"item_nombre\": \"EDAPLAN 918\", \"item_general_id\": \"97\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"59.00\", \"porcentaje\": null, \"item_codigo\": \"PEN081\", \"item_nombre\": \"POW CARBON BLACK CHEMO\", \"item_general_id\": \"71\"}, {\"cantidad\": \"150.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 / VARSOL\", \"item_general_id\": \"83\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(23,23,1,'PREPARACION PASTA ESMALTE ROJO CARMIN 57:1',NULL,'[{\"cantidad\": \"55.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"SAM023\", \"item_nombre\": \"METANOL\", \"item_general_id\": \"80\"}, {\"cantidad\": \"0.25\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"2.80\", \"porcentaje\": null, \"item_codigo\": \"ADI011\", \"item_nombre\": \"CHEMOSPERSE 77\", \"item_general_id\": \"85\"}, {\"cantidad\": \"1.60\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"24.00\", \"porcentaje\": null, \"item_codigo\": \"PER031\", \"item_nombre\": \"PIGMENTO ROJO CARMIN 57:1\", \"item_general_id\": \"72\"}, {\"cantidad\": \"34.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(24,24,1,'PREPARACION PASTA ESMALTE NARANJA',NULL,'[{\"cantidad\": \"332.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"9.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"SAM023\", \"item_nombre\": \"METANOL\", \"item_general_id\": \"80\"}, {\"cantidad\": \"3.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"ADI011\", \"item_nombre\": \"CHEMOSPERSE 77\", \"item_general_id\": \"85\"}, {\"cantidad\": \"18.90\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"408.00\", \"porcentaje\": null, \"item_codigo\": \"PEN023\", \"item_nombre\": \"PIGMENTO NARANJA MOLIBDENO\", \"item_general_id\": \"73\"}, {\"cantidad\": \"150.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(25,25,1,'PREPARACION PASTA ESMALTE AMARILLO',NULL,'[{\"cantidad\": \"332.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"9.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"SAM023\", \"item_nombre\": \"METANOL\", \"item_general_id\": \"80\"}, {\"cantidad\": \"3.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"18.90\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"465.00\", \"porcentaje\": null, \"item_codigo\": \"PEA011\", \"item_nombre\": \"PIGMENTO MARILLO DE CROMO AL 73\", \"item_general_id\": \"74\"}, {\"cantidad\": \"150.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"ADI010\", \"item_nombre\": \"EDAPLAN 915\", \"item_general_id\": \"84\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(26,26,1,'PREPARACION PASTA ESMALTE CAOBA',NULL,'[{\"cantidad\": \"295.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"6.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"SAM023\", \"item_nombre\": \"METANOL\", \"item_general_id\": \"80\"}, {\"cantidad\": \"3.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"ADI010\", \"item_nombre\": \"EDAPLAN 918\", \"item_general_id\": \"97\"}, {\"cantidad\": \"18.90\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"340.00\", \"porcentaje\": null, \"item_codigo\": \"PEC081\", \"item_nombre\": \"PIGMENTO OXIFERR CAOBA MARRON M 4781\", \"item_general_id\": \"75\"}, {\"cantidad\": \"173.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(27,27,1,'PREPARACION PASTA ESMALTE AMARILLO OXIDO',NULL,'[{\"cantidad\": \"295.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"6.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"SAM023\", \"item_nombre\": \"METANOL\", \"item_general_id\": \"80\"}, {\"cantidad\": \"3.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"18.90\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"340.00\", \"porcentaje\": null, \"item_codigo\": \"PEA013\", \"item_nombre\": \"PIGMENTO OXIFERR AMARILLO Y-4011\", \"item_general_id\": \"76\"}, {\"cantidad\": \"150.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 #3\", \"item_general_id\": \"36\"}, {\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"ADI010\", \"item_nombre\": \"EDAPLAN 915\", \"item_general_id\": \"84\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(28,28,1,'PREPARACION PASTA ESMALTE ROJO OXIDO',NULL,'[{\"cantidad\": \"295.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"6.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"SAM023\", \"item_nombre\": \"METANOL\", \"item_general_id\": \"80\"}, {\"cantidad\": \"3.10\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"17.00\", \"porcentaje\": null, \"item_codigo\": \"ADI010\", \"item_nombre\": \"EDAPLAN 918\", \"item_general_id\": \"97\"}, {\"cantidad\": \"18.90\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"340.00\", \"porcentaje\": null, \"item_codigo\": \"PER030\", \"item_nombre\": \"PIGMENTO OXIFERR ROJO R-5530\", \"item_general_id\": \"100\"}, {\"cantidad\": \"150.00\", \"porcentaje\": null, \"item_codigo\": \"SAA011\", \"item_nombre\": \"DISOLVENTE 2232 #3\", \"item_general_id\": \"36\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(29,29,1,'PREPARACION PASTA ESMALTE BLANCO',NULL,'[{\"cantidad\": \"213.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"22.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"ADI002\", \"item_nombre\": \"TROYSPERSE CD1\", \"item_general_id\": \"66\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}, {\"cantidad\": \"441.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"63.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(30,30,1,'PREPARACION PASTA ESMALTE TABACO',NULL,'[{\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"ADIMON 84\", \"item_general_id\": \"86\"}, {\"cantidad\": \"185.00\", \"porcentaje\": null, \"item_codigo\": \"PET080\", \"item_nombre\": \"OXIFER TABACO R-4370\", \"item_general_id\": \"78\"}, {\"cantidad\": \"134.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"6.00\", \"porcentaje\": null, \"item_codigo\": \"ADI002\", \"item_nombre\": \"TROYSPERSE CD1\", \"item_general_id\": \"66\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"AAS005\", \"item_nombre\": \"BENTOCLAY BP 184\", \"item_general_id\": \"39\"}, {\"cantidad\": \"7.00\", \"porcentaje\": null, \"item_codigo\": \"AHU002\", \"item_nombre\": \"LECITINA DE SOYA\", \"item_general_id\": \"61\"}, {\"cantidad\": \"33.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"SAA022\", \"item_nombre\": \"ETANOL AL 96%\", \"item_general_id\": \"40\"}]','Versión inicial (backfill automático)','sistema','2026-05-13 16:48:39'),(31,1,2,'PREPARACIÓN BARNIZ TRANSPARENTE BIRILLANTE',NULL,'[{\"cantidad\": \"115.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"66.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.30\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"1.30\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"1.60\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','Reemplazo de ingredientes via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(32,2,2,'PREPARACION ESMALTE BLANCO',NULL,'[{\"cantidad\": \"180.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.28\", \"porcentaje\": null, \"item_codigo\": \"018273\", \"item_nombre\": \"AZUL ULTRAMAR\", \"item_general_id\": \"233\"}, {\"cantidad\": \"180.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"556115\", \"item_nombre\": \"CARBONATO UF\", \"item_general_id\": \"235\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"0.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"180.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"0.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}, {\"cantidad\": \"0.35\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"P-400\", \"item_general_id\": \"242\"}]','Reemplazo de ingredientes via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(33,5,2,'PREPARACIÓN ESMALTE ROJO FIESTA',NULL,'[{\"cantidad\": \"100.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"18.00\", \"porcentaje\": null, \"item_codigo\": \"PAS023\", \"item_nombre\": \"PASTA ESMALTE ROJO CARMIN 57:1\", \"item_general_id\": \"23\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"50.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"1.30\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"METIL ETIL CETOXIMA\", \"item_general_id\": \"32\"}]','Reemplazo de ingredientes via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(34,11,2,'PREPARACION ESMALTE AMARILLO',NULL,'[{\"cantidad\": \"100.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"16.00\", \"porcentaje\": null, \"item_codigo\": \"PEA011\", \"item_nombre\": \"PIGMENTO MARILLO DE CROMO AL 73\", \"item_general_id\": \"74\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"0.75\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"556115\", \"item_nombre\": \"CARBONATO UF\", \"item_general_id\": \"235\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','Reemplazo de ingredientes via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(35,14,2,'PREPARACION ANTICORROSIVO GRIS',NULL,'[{\"cantidad\": \"160.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"120.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"65.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','Reemplazo de ingredientes via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(36,15,2,'PREPARACION ANTICORROSIVO NEGRO',NULL,'[{\"cantidad\": \"210.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.60\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"130.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"38.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"45.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','Reemplazo de ingredientes via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(37,18,2,'PREPARACION ANTICORROSIVO BLANCO',NULL,'[{\"cantidad\": \"160.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"30.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"60.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"50.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"55.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"52.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','Reemplazo de ingredientes via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(38,19,2,'PREPARACION ANTICORROSIVO VERDE',NULL,'[{\"cantidad\": \"95.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.30\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"66.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"33.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"PE1021\", \"item_nombre\": \"PASTA ESMALTE AZUL FTALO 15:3\", \"item_general_id\": \"56\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"0.60\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','Reemplazo de ingredientes via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(39,31,1,'FORMULACION VINILO T1 BLANCO',NULL,'[{\"cantidad\": \"180.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.60\", \"porcentaje\": null, \"item_codigo\": \"MP-246\", \"item_nombre\": \"TPF\", \"item_general_id\": \"246\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"2.50\", \"porcentaje\": null, \"item_codigo\": \"MP-247\", \"item_nombre\": \"NONIL TERGITOL\", \"item_general_id\": \"247\"}, {\"cantidad\": \"2.50\", \"porcentaje\": null, \"item_codigo\": \"MP-248\", \"item_nombre\": \"MECELLOSE\", \"item_general_id\": \"248\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-249\", \"item_nombre\": \"ANTIESPUMANTE\", \"item_general_id\": \"249\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"MP-250\", \"item_nombre\": \"DIETILEN GLICOL\", \"item_general_id\": \"250\"}, {\"cantidad\": \"95.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"200.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"60.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"80.00\", \"porcentaje\": null, \"item_codigo\": \"MP-253\", \"item_nombre\": \"CAOLIN\", \"item_general_id\": \"253\"}, {\"cantidad\": \"80.00\", \"porcentaje\": null, \"item_codigo\": \"556115\", \"item_nombre\": \"CARBONATO UF\", \"item_general_id\": \"235\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"MP-254\", \"item_nombre\": \"TEXANOL\", \"item_general_id\": \"254\"}, {\"cantidad\": \"170.00\", \"porcentaje\": null, \"item_codigo\": \"MP-255\", \"item_nombre\": \"ACRONAL\", \"item_general_id\": \"255\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-256\", \"item_nombre\": \"BACTERICIDA\", \"item_general_id\": \"256\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"MP-249\", \"item_nombre\": \"ANTIESPUMANTE\", \"item_general_id\": \"249\"}, {\"cantidad\": \"127.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-257\", \"item_nombre\": \"AMONIACO\", \"item_general_id\": \"257\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"MP-248\", \"item_nombre\": \"MECELLOSE\", \"item_general_id\": \"248\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"MP-258\", \"item_nombre\": \"HISOL ASOCIATIVO\", \"item_general_id\": \"258\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-259\", \"item_nombre\": \"FUNGICIDA\", \"item_general_id\": \"259\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"MP-260\", \"item_nombre\": \"ACEITE DE PINO\", \"item_general_id\": \"260\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(40,32,1,'FORMULACION EPOXICA TRANSPARENTE',NULL,'[{\"cantidad\": \"100.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"30.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"VAR001\", \"item_nombre\": \"UFI PRETHOX\", \"item_general_id\": \"142\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"MP-261\", \"item_nombre\": \"BUTIL CELLOSOLVE\", \"item_general_id\": \"261\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"MP-269\", \"item_nombre\": \"ACETATO N-PROPILO\", \"item_general_id\": \"269\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(41,33,1,'FORMULACION VINILO BLANCO TIPO 2',NULL,'[{\"cantidad\": \"0.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"MP-246\", \"item_nombre\": \"TPF\", \"item_general_id\": \"246\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-248\", \"item_nombre\": \"MECELLOSE\", \"item_general_id\": \"248\"}, {\"cantidad\": \"2.60\", \"porcentaje\": null, \"item_codigo\": \"MP-247\", \"item_nombre\": \"NONIL TERGITOL\", \"item_general_id\": \"247\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"MP-250\", \"item_nombre\": \"DIETILEN GLICOL\", \"item_general_id\": \"250\"}, {\"cantidad\": \"2.50\", \"porcentaje\": null, \"item_codigo\": \"MP-254\", \"item_nombre\": \"TEXANOL\", \"item_general_id\": \"254\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-249\", \"item_nombre\": \"ANTIESPUMANTE\", \"item_general_id\": \"249\"}, {\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"400.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"50.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"50.00\", \"porcentaje\": null, \"item_codigo\": \"MP-253\", \"item_nombre\": \"CAOLIN\", \"item_general_id\": \"253\"}, {\"cantidad\": \"80.00\", \"porcentaje\": null, \"item_codigo\": \"MP-255\", \"item_nombre\": \"ACRONAL\", \"item_general_id\": \"255\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-249\", \"item_nombre\": \"ANTIESPUMANTE\", \"item_general_id\": \"249\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-257\", \"item_nombre\": \"AMONIACO\", \"item_general_id\": \"257\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-248\", \"item_nombre\": \"MECELLOSE\", \"item_general_id\": \"248\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-256\", \"item_nombre\": \"BACTERICIDA\", \"item_general_id\": \"256\"}, {\"cantidad\": \"3.30\", \"porcentaje\": null, \"item_codigo\": \"MP-258\", \"item_nombre\": \"HISOL ASOCIATIVO\", \"item_general_id\": \"258\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"MP-260\", \"item_nombre\": \"ACEITE DE PINO\", \"item_general_id\": \"260\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(42,34,1,'FORMULACION VINILO BLANCO TIPO 3',NULL,'[{\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.40\", \"porcentaje\": null, \"item_codigo\": \"MP-246\", \"item_nombre\": \"TPF\", \"item_general_id\": \"246\"}, {\"cantidad\": \"2.20\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-247\", \"item_nombre\": \"NONIL TERGITOL\", \"item_general_id\": \"247\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"MP-248\", \"item_nombre\": \"MECELLOSE\", \"item_general_id\": \"248\"}, {\"cantidad\": \"60.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"425.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"MP-253\", \"item_nombre\": \"CAOLIN\", \"item_general_id\": \"253\"}, {\"cantidad\": \"36.00\", \"porcentaje\": null, \"item_codigo\": \"MP-250\", \"item_nombre\": \"DIETILEN GLICOL\", \"item_general_id\": \"250\"}, {\"cantidad\": \"2.40\", \"porcentaje\": null, \"item_codigo\": \"MP-255\", \"item_nombre\": \"ACRONAL\", \"item_general_id\": \"255\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-256\", \"item_nombre\": \"BACTERICIDA\", \"item_general_id\": \"256\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-249\", \"item_nombre\": \"ANTIESPUMANTE\", \"item_general_id\": \"249\"}, {\"cantidad\": \"2.50\", \"porcentaje\": null, \"item_codigo\": \"MP-248\", \"item_nombre\": \"MECELLOSE\", \"item_general_id\": \"248\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-257\", \"item_nombre\": \"AMONIACO\", \"item_general_id\": \"257\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-258\", \"item_nombre\": \"HISOL ASOCIATIVO\", \"item_general_id\": \"258\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"MP-260\", \"item_nombre\": \"ACEITE DE PINO\", \"item_general_id\": \"260\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(43,35,1,'FORMULACION ESMALTE AZUL REAL',NULL,'[{\"cantidad\": \"180.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"130.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"556115\", \"item_nombre\": \"CARBONATO UF\", \"item_general_id\": \"235\"}, {\"cantidad\": \"30.00\", \"porcentaje\": null, \"item_codigo\": \"PE1021\", \"item_nombre\": \"PASTA ESMALTE AZUL FTALO 15:3\", \"item_general_id\": \"56\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"METIL ETIL CETOXIMA\", \"item_general_id\": \"32\"}, {\"cantidad\": \"0.30\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"P-400\", \"item_general_id\": \"242\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(44,36,1,'FORMULACION LACA CATALIZADA BRILLANTE',NULL,'[{\"cantidad\": \"60.00\", \"porcentaje\": null, \"item_codigo\": \"RES004\", \"item_nombre\": \"RESINA CORTA R4\", \"item_general_id\": \"162\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"VAR001\", \"item_nombre\": \"UFI PRETHOX\", \"item_general_id\": \"142\"}, {\"cantidad\": \"55.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"2.50\", \"porcentaje\": null, \"item_codigo\": \"MP-261\", \"item_nombre\": \"BUTIL CELLOSOLVE\", \"item_general_id\": \"261\"}, {\"cantidad\": \"12.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"0.30\", \"porcentaje\": null, \"item_codigo\": \"MP-262\", \"item_nombre\": \"TROYSSOL 366\", \"item_general_id\": \"262\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(45,37,1,'FORMULACION PASTA OCRE PARA VINILO',NULL,'[{\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-247\", \"item_nombre\": \"NONIL TERGITOL\", \"item_general_id\": \"247\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"MP-246\", \"item_nombre\": \"TPF\", \"item_general_id\": \"246\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"MP-256\", \"item_nombre\": \"BACTERICIDA\", \"item_general_id\": \"256\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": \"MP-250\", \"item_nombre\": \"DIETILEN GLICOL\", \"item_general_id\": \"250\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-249\", \"item_nombre\": \"ANTIESPUMANTE\", \"item_general_id\": \"249\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"MP-248\", \"item_nombre\": \"MECELLOSE\", \"item_general_id\": \"248\"}, {\"cantidad\": \"0.30\", \"porcentaje\": null, \"item_codigo\": \"MP-258\", \"item_nombre\": \"HISOL ASOCIATIVO\", \"item_general_id\": \"258\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(46,38,1,'FORMULACION VINILO OCRE T1',NULL,'[{\"cantidad\": \"80.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"0.70\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"MP-249\", \"item_nombre\": \"ANTIESPUMANTE\", \"item_general_id\": \"249\"}, {\"cantidad\": \"0.60\", \"porcentaje\": null, \"item_codigo\": \"MP-247\", \"item_nombre\": \"NONIL TERGITOL\", \"item_general_id\": \"247\"}, {\"cantidad\": \"0.60\", \"porcentaje\": null, \"item_codigo\": \"MP-246\", \"item_nombre\": \"TPF\", \"item_general_id\": \"246\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"MP-248\", \"item_nombre\": \"MECELLOSE\", \"item_general_id\": \"248\"}, {\"cantidad\": \"18.00\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"MP-255\", \"item_nombre\": \"ACRONAL\", \"item_general_id\": \"255\"}, {\"cantidad\": \"2.10\", \"porcentaje\": null, \"item_codigo\": \"MP-250\", \"item_nombre\": \"DIETILEN GLICOL\", \"item_general_id\": \"250\"}, {\"cantidad\": \"0.70\", \"porcentaje\": null, \"item_codigo\": \"MP-254\", \"item_nombre\": \"TEXANOL\", \"item_general_id\": \"254\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"MP-256\", \"item_nombre\": \"BACTERICIDA\", \"item_general_id\": \"256\"}, {\"cantidad\": \"0.90\", \"porcentaje\": null, \"item_codigo\": \"MP-257\", \"item_nombre\": \"AMONIACO\", \"item_general_id\": \"257\"}, {\"cantidad\": \"0.60\", \"porcentaje\": null, \"item_codigo\": \"MP-248\", \"item_nombre\": \"MECELLOSE\", \"item_general_id\": \"248\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"MP-260\", \"item_nombre\": \"ACEITE DE PINO\", \"item_general_id\": \"260\"}, {\"cantidad\": \"0.60\", \"porcentaje\": null, \"item_codigo\": \"MP-249\", \"item_nombre\": \"ANTIESPUMANTE\", \"item_general_id\": \"249\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"MP-258\", \"item_nombre\": \"HISOL ASOCIATIVO\", \"item_general_id\": \"258\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(47,39,1,'FORMULACION ESMALTE AMARILLO CATERPILLAR',NULL,'[{\"cantidad\": \"60.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"22.00\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}, {\"cantidad\": \"0.20\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"P-400\", \"item_general_id\": \"242\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(48,40,1,'FORMULACION ESMALTE NEGRO',NULL,'[{\"cantidad\": \"180.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"30.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"30.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"24.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"1.30\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"AAN002\", \"item_nombre\": \"METIL ETIL CETOXIMA\", \"item_general_id\": \"32\"}, {\"cantidad\": \"0.20\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"P-400\", \"item_general_id\": \"242\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(49,41,1,'FORMULACION ESMALTE BLANCO T1',NULL,'[{\"cantidad\": \"180.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.28\", \"porcentaje\": null, \"item_codigo\": \"018273\", \"item_nombre\": \"AZUL ULTRAMAR\", \"item_general_id\": \"233\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"556115\", \"item_nombre\": \"CARBONATO UF\", \"item_general_id\": \"235\"}, {\"cantidad\": \"100.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"30.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"200.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"185.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"2.50\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"3.50\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"1.80\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"P-400\", \"item_general_id\": \"242\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(50,42,1,'FORMULACION ESMALTE BLANCO 4X1',NULL,'[{\"cantidad\": \"80.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"6.00\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"6.00\", \"porcentaje\": null, \"item_codigo\": \"556115\", \"item_nombre\": \"CARBONATO UF\", \"item_general_id\": \"235\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"0.90\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"0.85\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"P-400\", \"item_general_id\": \"242\"}, {\"cantidad\": \"0.10\", \"porcentaje\": null, \"item_codigo\": \"PHI011\", \"item_nombre\": \"PASTA VIOLETA\", \"item_general_id\": \"204\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(51,43,1,'FORMULACION ESMALTE BLANCO ECONOMICO JJ',NULL,'[{\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"0.20\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"0.70\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.09\", \"porcentaje\": null, \"item_codigo\": \"018273\", \"item_nombre\": \"AZUL ULTRAMAR\", \"item_general_id\": \"233\"}, {\"cantidad\": \"9.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"1.90\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"6.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"0.30\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"11.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"17.50\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"0.25\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"0.35\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.25\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(52,44,1,'FORMULACION ESMALTE ECONOMICO BLANCO JH',NULL,'[{\"cantidad\": \"46.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"0.20\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"0.60\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.09\", \"porcentaje\": null, \"item_codigo\": \"018273\", \"item_nombre\": \"AZUL ULTRAMAR\", \"item_general_id\": \"233\"}, {\"cantidad\": \"10.20\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"7.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"AEM005\", \"item_nombre\": \"DISASTAB\", \"item_general_id\": \"41\"}, {\"cantidad\": \"16.00\", \"porcentaje\": null, \"item_codigo\": \"SIA040\", \"item_nombre\": \"AGUA\", \"item_general_id\": \"42\"}, {\"cantidad\": \"7.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"0.32\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.20\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(53,45,1,'FORMULACION ESMALTE DORADO',NULL,'[{\"cantidad\": \"105.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"1.60\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"39.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"1.40\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"1.60\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"6.40\", \"porcentaje\": null, \"item_codigo\": \"MP-263\", \"item_nombre\": \"POLVO PERLADO VERDOSO\", \"item_general_id\": \"263\"}, {\"cantidad\": \"1.60\", \"porcentaje\": null, \"item_codigo\": \"MP-264\", \"item_nombre\": \"POLVO PERLADO RICO EN ORO\", \"item_general_id\": \"264\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(54,46,1,'FORMULACION ANTICORROSIVO CROMATO ZN',NULL,'[{\"cantidad\": \"35.00\", \"porcentaje\": null, \"item_codigo\": \"MP-265\", \"item_nombre\": \"RESINA 000\", \"item_general_id\": \"265\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"6.50\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"MP-267\", \"item_nombre\": \"PIGMENTO CROMATO DE ZINC\", \"item_general_id\": \"267\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"38.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"38.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": \"MP-266\", \"item_nombre\": \"RESINA MALEICA AL 60%\", \"item_general_id\": \"266\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"50.00\", \"porcentaje\": null, \"item_codigo\": \"MP-265\", \"item_nombre\": \"RESINA 000\", \"item_general_id\": \"265\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"0.60\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"0.60\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"PE1021\", \"item_nombre\": \"PASTA ESMALTE AZUL FTALO 15:3\", \"item_general_id\": \"56\"}, {\"cantidad\": \"30.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(55,47,1,'FORMULACION ESMALTE DE ALUMINIO',NULL,'[{\"cantidad\": \"210.00\", \"porcentaje\": null, \"item_codigo\": \"RAM014\", \"item_nombre\": \"RESINA MEDIA EN SOYA AL 50%\", \"item_general_id\": \"31\"}, {\"cantidad\": \"1.40\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.75\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"2.90\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"28.00\", \"porcentaje\": null, \"item_codigo\": \"MP-268\", \"item_nombre\": \"PIGMENTO ALUMINIO 22 NL\", \"item_general_id\": \"268\"}, {\"cantidad\": \"69.00\", \"porcentaje\": null, \"item_codigo\": \"SAV010\", \"item_nombre\": \"VARSOL\", \"item_general_id\": \"44\"}, {\"cantidad\": \"2.50\", \"porcentaje\": null, \"item_codigo\": \"SOC010\", \"item_nombre\": \"OCTOATO DE CALCIO AL 10%\", \"item_general_id\": \"35\"}, {\"cantidad\": \"2.60\", \"porcentaje\": null, \"item_codigo\": \"SOZ024\", \"item_nombre\": \"OCTOATO DE ZIRCONIO AL 24%\", \"item_general_id\": \"34\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"SOC011\", \"item_nombre\": \"OCTOATO DE COBALTO AL 12%\", \"item_general_id\": \"33\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"545124\", \"item_nombre\": \"ANTIPIEL\", \"item_general_id\": \"241\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(56,48,1,'FORMULACION EPOXICA BLANCO',NULL,'[{\"cantidad\": \"120.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"78.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"150.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"25.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"50.00\", \"porcentaje\": null, \"item_codigo\": \"556115\", \"item_nombre\": \"CARBONATO UF\", \"item_general_id\": \"235\"}, {\"cantidad\": \"4.50\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"BUTIL GLICOL\", \"item_general_id\": \"245\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"MP-270\", \"item_nombre\": \"UREA FORMAL\", \"item_general_id\": \"270\"}, {\"cantidad\": \"18.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"VAR009\", \"item_nombre\": \"ETHYL SILICATO\", \"item_general_id\": \"161\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(57,49,1,'FORMULACION EPOXICA NEGRA',NULL,'[{\"cantidad\": \"60.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"1.30\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"2.50\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"80.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"32.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"9.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"7.60\", \"porcentaje\": null, \"item_codigo\": \"VAR001\", \"item_nombre\": \"UFI PRETHOX\", \"item_general_id\": \"142\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"MP-269\", \"item_nombre\": \"ACETATO N-PROPILO\", \"item_general_id\": \"269\"}, {\"cantidad\": \"14.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"MP-261\", \"item_nombre\": \"BUTIL CELLOSOLVE\", \"item_general_id\": \"261\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(58,50,1,'FORMULACION EPOXICA GRIS',NULL,'[{\"cantidad\": \"100.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"68.00\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"50.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"165.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"2.40\", \"porcentaje\": null, \"item_codigo\": \"MP-261\", \"item_nombre\": \"BUTIL CELLOSOLVE\", \"item_general_id\": \"261\"}, {\"cantidad\": \"60.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"VAR001\", \"item_nombre\": \"UFI PRETHOX\", \"item_general_id\": \"142\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"MP-269\", \"item_nombre\": \"ACETATO N-PROPILO\", \"item_general_id\": \"269\"}, {\"cantidad\": \"30.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(59,51,1,'FORMULACION EPOXICA NEGRA RESINA 100%',NULL,'[{\"cantidad\": \"100.00\", \"porcentaje\": null, \"item_codigo\": \"MP-274\", \"item_nombre\": \"RESINA EPOXICA 100%\", \"item_general_id\": \"274\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"1.80\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"2.20\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"70.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-269\", \"item_nombre\": \"ACETATO N-PROPILO\", \"item_general_id\": \"269\"}, {\"cantidad\": \"1.00\", \"porcentaje\": null, \"item_codigo\": \"MP-261\", \"item_nombre\": \"BUTIL CELLOSOLVE\", \"item_general_id\": \"261\"}, {\"cantidad\": \"32.00\", \"porcentaje\": null, \"item_codigo\": \"PAS022\", \"item_nombre\": \"PASTA ESMALTE NEGRO\", \"item_general_id\": \"22\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(60,52,1,'FORMULACION EPOXICA POLIAMIDA VERDE',NULL,'[{\"cantidad\": \"76.30\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"1.40\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"3.80\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"16.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"10.00\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"100.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"17.50\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"MP-261\", \"item_nombre\": \"BUTIL CELLOSOLVE\", \"item_general_id\": \"261\"}, {\"cantidad\": \"26.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"1.90\", \"porcentaje\": null, \"item_codigo\": \"PE1021\", \"item_nombre\": \"PASTA ESMALTE AZUL FTALO 15:3\", \"item_general_id\": \"56\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(61,53,1,'FORMULACION EPOXICA AZUL',NULL,'[{\"cantidad\": \"13.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"0.25\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"0.75\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"12.00\", \"porcentaje\": null, \"item_codigo\": \"PE1021\", \"item_nombre\": \"PASTA ESMALTE AZUL FTALO 15:3\", \"item_general_id\": \"56\"}, {\"cantidad\": \"22.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"VAR001\", \"item_nombre\": \"UFI PRETHOX\", \"item_general_id\": \"142\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"MP-261\", \"item_nombre\": \"BUTIL CELLOSOLVE\", \"item_general_id\": \"261\"}, {\"cantidad\": \"3.50\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"0.05\", \"porcentaje\": null, \"item_codigo\": \"MP-271\", \"item_nombre\": \"BYK 066N NIVELANTE\", \"item_general_id\": \"271\"}, {\"cantidad\": \"0.23\", \"porcentaje\": null, \"item_codigo\": \"MP-272\", \"item_nombre\": \"BYK 108 ANTIESPUMANTE\", \"item_general_id\": \"272\"}, {\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(62,54,1,'FORMULACION EPOXICA ROJO OXIDO',NULL,'[{\"cantidad\": \"120.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"MP-272\", \"item_nombre\": \"BYK 108 ANTIESPUMANTE\", \"item_general_id\": \"272\"}, {\"cantidad\": \"2.50\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"22.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"PER030\", \"item_nombre\": \"OXIDO DE HIERRO ROJO R-5530\", \"item_general_id\": \"64\"}, {\"cantidad\": \"2.00\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"100.00\", \"porcentaje\": null, \"item_codigo\": \"MP-252\", \"item_nombre\": \"TALCO TY 400\", \"item_general_id\": \"252\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"MP-261\", \"item_nombre\": \"BUTIL CELLOSOLVE\", \"item_general_id\": \"261\"}, {\"cantidad\": \"50.00\", \"porcentaje\": null, \"item_codigo\": \"MP-251\", \"item_nombre\": \"CARBONATO DE CALCIO\", \"item_general_id\": \"251\"}, {\"cantidad\": \"40.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"28.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"MP-270\", \"item_nombre\": \"UREA FORMAL\", \"item_general_id\": \"270\"}, {\"cantidad\": \"16.00\", \"porcentaje\": null, \"item_codigo\": \"MP-269\", \"item_nombre\": \"ACETATO N-PROPILO\", \"item_general_id\": \"269\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(63,55,1,'FORMULACION ESM EPOXI SILICATO BLANCO',NULL,'[{\"cantidad\": \"5.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"0.25\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"0.20\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"2.90\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"521584\", \"item_nombre\": \"FOSFATO ZINC\", \"item_general_id\": \"236\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"11.30\", \"porcentaje\": null, \"item_codigo\": \"VAR009\", \"item_nombre\": \"ETHYL SILICATO\", \"item_general_id\": \"161\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(64,56,1,'FORMULACION ESM EPOXI SILICATO VERDE',NULL,'[{\"cantidad\": \"4.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"0.16\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"0.20\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"0.40\", \"porcentaje\": null, \"item_codigo\": \"MP-273\", \"item_nombre\": \"PIGMENTO VERDE OXIDO CROMO\", \"item_general_id\": \"273\"}, {\"cantidad\": \"0.20\", \"porcentaje\": null, \"item_codigo\": \"PED010\", \"item_nombre\": \"DIOXIDO DE TITANIO\", \"item_general_id\": \"37\"}, {\"cantidad\": \"1.20\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"0.80\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"9.20\", \"porcentaje\": null, \"item_codigo\": \"VAR009\", \"item_nombre\": \"ETHYL SILICATO\", \"item_general_id\": \"161\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51'),(65,57,1,'FORMULACION ESMALTE EPOXICO AMARILLO',NULL,'[{\"cantidad\": \"55.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"20.00\", \"porcentaje\": null, \"item_codigo\": \"XIL21288\", \"item_nombre\": \"XILOL\", \"item_general_id\": \"225\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"927163\", \"item_nombre\": \"CLEYTONE HY\", \"item_general_id\": \"232\"}, {\"cantidad\": \"1.50\", \"porcentaje\": null, \"item_codigo\": \"093816\", \"item_nombre\": \"DISPERSANTE\", \"item_general_id\": \"231\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"PEA010\", \"item_nombre\": \"OXIDO DE HIERRO AMARILLO Y 4021\", \"item_general_id\": \"63\"}, {\"cantidad\": \"28.00\", \"porcentaje\": null, \"item_codigo\": \"PEA011\", \"item_nombre\": \"PIGMENTO MARILLO DE CROMO AL 73\", \"item_general_id\": \"74\"}, {\"cantidad\": \"34.00\", \"porcentaje\": null, \"item_codigo\": \"NPSN CHINA\", \"item_nombre\": \"RESINA EPOXICA\", \"item_general_id\": \"226\"}, {\"cantidad\": \"3.00\", \"porcentaje\": null, \"item_codigo\": \"MP-261\", \"item_nombre\": \"BUTIL CELLOSOLVE\", \"item_general_id\": \"261\"}, {\"cantidad\": \"15.00\", \"porcentaje\": null, \"item_codigo\": null, \"item_nombre\": \"ISOBUTANOL\", \"item_general_id\": \"244\"}, {\"cantidad\": \"8.00\", \"porcentaje\": null, \"item_codigo\": \"MP-270\", \"item_nombre\": \"UREA FORMAL\", \"item_general_id\": \"270\"}, {\"cantidad\": \"0.50\", \"porcentaje\": null, \"item_codigo\": \"MP-262\", \"item_nombre\": \"TROYSSOL 366\", \"item_general_id\": \"262\"}]','VersiÃ³n inicial via fase3_formulaciones.sql (2026-05-13)','sistema','2026-05-13 20:43:51');
/*!40000 ALTER TABLE `formulaciones_versiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gestiones_cobro`
--

DROP TABLE IF EXISTS `gestiones_cobro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gestiones_cobro` (
  `id_gestion` int NOT NULL AUTO_INCREMENT,
  `facturas_id` int NOT NULL,
  `clientes_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `tipo` enum('llamada','email','visita','whatsapp') NOT NULL,
  `resultado` varchar(255) DEFAULT NULL,
  `proxima_gestion` date DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_gestion`),
  KEY `fk_gestiones_factura` (`facturas_id`),
  KEY `fk_gestiones_cliente` (`clientes_id`),
  KEY `fk_gestiones_usuario` (`usuario_id`),
  CONSTRAINT `fk_gestiones_cliente` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`id_clientes`) ON DELETE CASCADE,
  CONSTRAINT `fk_gestiones_factura` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`) ON DELETE CASCADE,
  CONSTRAINT `fk_gestiones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gestiones_cobro`
--

LOCK TABLES `gestiones_cobro` WRITE;
/*!40000 ALTER TABLE `gestiones_cobro` DISABLE KEYS */;
INSERT INTO `gestiones_cobro` VALUES (1,1,1,NULL,'llamada','No contestó. Se dejó mensaje de voz.','2026-01-10','2026-03-19 14:38:34'),(2,1,1,NULL,'whatsapp','Prometió pagar la próxima semana.','2026-01-20','2026-03-19 14:38:34'),(3,1,1,NULL,'llamada','No cumplió. Nuevo compromiso para el 25.','2026-01-25','2026-03-19 14:38:34'),(4,1,1,NULL,'visita','No estaba el encargado. Se dejó comunicado.','2026-02-01','2026-03-19 14:38:34');
/*!40000 ALTER TABLE `gestiones_cobro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_precios`
--

DROP TABLE IF EXISTS `historial_precios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_precios` (
  `id_historial` int unsigned NOT NULL AUTO_INCREMENT,
  `item_proveedor_id` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_con_iva` decimal(10,2) DEFAULT NULL,
  `fecha` date NOT NULL,
  `observacion` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historial`),
  KEY `item_proveedor_id` (`item_proveedor_id`),
  CONSTRAINT `historial_precios_ibfk_1` FOREIGN KEY (`item_proveedor_id`) REFERENCES `item_proveedor` (`id_item_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_precios`
--

LOCK TABLES `historial_precios` WRITE;
/*!40000 ALTER TABLE `historial_precios` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial_precios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instalaciones`
--

DROP TABLE IF EXISTS `instalaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instalaciones` (
  `id_instalaciones` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ciudad` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_empresa` int NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_instalaciones`),
  KEY `fk_instalaciones_empresa_idx` (`id_empresa`),
  KEY `idx_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_instalaciones_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instalaciones`
--

LOCK TABLES `instalaciones` WRITE;
/*!40000 ALTER TABLE `instalaciones` DISABLE KEYS */;
INSERT INTO `instalaciones` VALUES (1,'Sede Cordialidad','SEDE DE FABRICACIÓN DE PINTURAS',' BARRANQUILLA','Calle 99 # 6-59','3019794729',1,NULL),(2,'Sede Villa Olimpica','SEDE DE FABRICACIÓN DE PINTURAS','Galapa','','3019794729',1,NULL),(3,'Sede Juan Mina','SEDE DE FABRICACIÓN DE PINTURAS','Barranquilla','','3019794729',1,NULL);
/*!40000 ALTER TABLE `instalaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario`
--

DROP TABLE IF EXISTS `inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario` (
  `id_inventario` int NOT NULL AUTO_INCREMENT,
  `cantidad` decimal(5,2) DEFAULT NULL,
  `fecha_update` date DEFAULT NULL,
  `apartada` tinyint DEFAULT NULL,
  `item_general_id` int NOT NULL,
  `estado` tinyint DEFAULT NULL COMMENT '0 disponible\\r\\n1 No disponible',
  `movimiento_inventario_id` int DEFAULT NULL,
  `tipo` tinyint DEFAULT NULL COMMENT '1 ingreso\n2 egreso',
  `bodegas_id` int NOT NULL,
  PRIMARY KEY (`id_inventario`),
  KEY `fk_inventario_item_general1_idx` (`item_general_id`),
  KEY `fk_inventario_movimientos_inventario1_idx` (`movimiento_inventario_id`),
  KEY `fk_inventario_bodega` (`bodegas_id`),
  CONSTRAINT `fk_inventario_bodega` FOREIGN KEY (`bodegas_id`) REFERENCES `bodegas` (`id_bodegas`),
  CONSTRAINT `fk_inventario_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_inventario_movimientos_inventario1` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimiento_inventario` (`id_movimiento_inventario`)
) ENGINE=InnoDB AUTO_INCREMENT=286 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario`
--

LOCK TABLES `inventario` WRITE;
/*!40000 ALTER TABLE `inventario` DISABLE KEYS */;
INSERT INTO `inventario` VALUES (162,2.00,'2026-04-17',0,134,0,NULL,1,2),(163,1.00,'2026-04-17',0,135,0,NULL,1,2),(164,1.00,'2026-04-17',0,136,0,NULL,1,2),(165,1.00,'2026-04-17',0,137,0,NULL,1,2),(166,1.00,'2026-04-17',0,138,0,NULL,1,2),(167,1.00,'2026-04-17',0,139,0,NULL,1,2),(168,1.00,'2026-04-17',0,140,0,NULL,1,2),(169,1.00,'2026-04-17',0,141,0,NULL,1,2),(170,6.00,'2026-04-17',0,194,0,NULL,1,2),(171,9.00,'2026-05-16',0,142,0,NULL,1,2),(172,2.00,'2026-04-17',0,143,0,NULL,1,18),(173,1.00,'2026-04-17',0,144,0,NULL,1,18),(174,1.00,'2026-04-17',0,145,0,NULL,1,18),(175,1.00,'2026-04-17',0,146,0,NULL,1,18),(176,1.00,'2026-04-17',0,147,0,NULL,1,18),(177,1.00,'2026-04-17',0,189,0,NULL,1,18),(178,0.00,'2026-04-17',0,148,0,NULL,1,18),(179,1.00,'2026-04-17',0,149,0,NULL,1,18),(180,1.00,'2026-04-17',0,150,0,NULL,1,18),(181,1.00,'2026-04-17',0,151,0,NULL,1,18),(182,1.00,'2026-04-17',0,152,0,NULL,1,18),(183,2.00,'2026-04-17',0,134,0,NULL,1,18),(184,1.00,'2026-04-17',0,153,0,NULL,1,18),(185,0.00,'2026-04-17',0,154,0,NULL,1,18),(186,0.00,'2026-04-17',0,155,0,NULL,1,18),(187,4.00,'2026-04-17',0,156,0,NULL,1,18),(188,1.00,'2026-04-17',0,157,0,NULL,1,18),(189,1.00,'2026-04-17',0,158,0,NULL,1,18),(190,1.00,'2026-04-17',0,159,0,NULL,1,18),(191,1.00,'2026-04-17',0,160,0,NULL,1,18),(192,3.00,'2026-04-17',0,161,0,NULL,1,18),(193,1.00,'2026-04-17',0,190,0,NULL,1,18),(194,1.00,'2026-04-17',0,191,0,NULL,1,18),(195,2.00,'2026-04-17',0,162,0,NULL,1,18),(196,4.00,'2026-04-17',0,163,0,NULL,1,18),(197,1.00,'2026-04-17',0,219,0,NULL,1,18),(198,3.00,'2026-04-17',0,164,0,NULL,1,18),(199,1.00,'2026-04-17',0,192,0,NULL,1,18),(200,1.00,'2026-04-17',0,165,0,NULL,1,18),(201,1.00,'2026-04-17',0,166,0,NULL,1,18),(202,6.00,'2026-04-17',0,167,0,NULL,1,18),(203,6.00,'2026-04-17',0,168,0,NULL,1,18),(204,1.00,'2026-04-17',0,169,0,NULL,1,18),(205,0.00,'2026-04-17',0,170,0,NULL,1,18),(206,2.00,'2026-04-17',0,171,0,NULL,1,18),(207,1.00,'2026-04-17',0,172,0,NULL,1,18),(208,1.00,'2026-04-17',0,173,0,NULL,1,18),(209,1.00,'2026-04-17',0,136,0,NULL,1,18),(210,1.00,'2026-04-17',0,174,0,NULL,1,18),(211,1.00,'2026-04-17',0,175,0,NULL,1,18),(212,0.00,'2026-04-17',0,176,0,NULL,1,18),(213,2.00,'2026-04-17',0,177,0,NULL,1,18),(214,0.00,'2026-04-17',0,193,0,NULL,1,18),(215,2.00,'2026-04-17',0,178,0,NULL,1,18),(216,1.00,'2026-04-17',0,179,0,NULL,1,18),(217,1.00,'2026-04-17',0,180,0,NULL,1,18),(218,1.00,'2026-04-17',0,181,0,NULL,1,18),(219,1.00,'2026-04-17',0,182,0,NULL,1,18),(220,0.00,'2026-04-17',0,183,0,NULL,1,18),(221,3.00,'2026-04-17',0,184,0,NULL,1,18),(222,0.00,'2026-04-17',0,185,0,NULL,1,18),(223,2.00,'2026-04-17',0,186,0,NULL,1,18),(224,1.00,'2026-04-17',0,187,0,NULL,1,18),(225,0.00,'2026-04-17',0,188,0,NULL,1,18),(226,4.00,'2026-04-17',0,162,0,NULL,1,19),(227,1.00,'2026-04-17',0,195,0,NULL,1,19),(228,2.00,'2026-04-17',0,196,0,NULL,1,19),(229,1.00,'2026-04-17',0,197,0,NULL,1,19),(230,1.00,'2026-04-17',0,150,0,NULL,1,19),(231,2.00,'2026-04-17',0,172,0,NULL,1,19),(232,1.00,'2026-04-17',0,198,0,NULL,1,19),(233,1.00,'2026-04-17',0,199,0,NULL,1,19),(234,1.00,'2026-04-17',0,200,0,NULL,1,19),(235,1.00,'2026-04-17',0,201,0,NULL,1,19),(236,1.00,'2026-04-17',0,202,0,NULL,1,19),(237,3.00,'2026-04-17',0,203,0,NULL,1,19),(238,0.00,'2026-04-17',0,204,0,NULL,1,19),(239,0.00,'2026-04-17',0,171,0,NULL,1,19),(240,1.00,'2026-04-17',0,213,0,NULL,1,19),(241,4.00,'2026-04-17',0,205,0,NULL,1,19),(242,4.00,'2026-04-17',0,206,0,NULL,1,19),(243,3.00,'2026-04-17',0,207,0,NULL,1,19),(244,4.00,'2026-04-17',0,208,0,NULL,1,19),(245,1.00,'2026-04-17',0,209,0,NULL,1,19),(246,6.00,'2026-04-17',0,210,0,NULL,1,19),(247,1.00,'2026-04-17',0,211,0,NULL,1,19),(248,8.00,'2026-04-17',0,212,0,NULL,1,19),(249,31.00,'2026-04-17',0,161,0,NULL,1,21),(250,7.00,'2026-04-17',0,214,0,NULL,1,21),(251,4.00,'2026-04-17',0,215,0,NULL,1,21),(252,1.00,'2026-04-17',0,216,0,NULL,1,21),(253,4.00,'2026-04-17',0,217,0,NULL,1,21),(254,4.00,'2026-04-17',0,137,0,NULL,1,21),(255,113.00,'2026-04-17',0,180,0,NULL,1,21),(256,7.00,'2026-04-17',0,218,0,NULL,1,21),(257,2.00,'2026-04-17',0,219,0,NULL,1,21),(258,1.00,'2026-04-17',0,220,0,NULL,1,21),(259,1.00,'2026-04-17',0,221,0,NULL,1,21),(260,1.00,'2026-04-17',0,222,0,NULL,1,21),(285,5.50,'2026-06-12',NULL,37,1,NULL,1,1);
/*!40000 ALTER TABLE `inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario_capas`
--

DROP TABLE IF EXISTS `inventario_capas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_capas` (
  `id_capa` int NOT NULL AUTO_INCREMENT,
  `item_general_id` int NOT NULL,
  `bodegas_id` int NOT NULL,
  `proveedor_id` int DEFAULT NULL,
  `item_proveedor_id` int DEFAULT NULL,
  `orden_compra_id` int DEFAULT NULL,
  `cantidad_original` decimal(15,4) NOT NULL,
  `cantidad_disponible` decimal(15,4) NOT NULL,
  `costo_unitario` decimal(15,4) NOT NULL COMMENT 'Costo por unidad base (KG)',
  `unidad_compra_id` int DEFAULT NULL,
  `factor_conversion` decimal(15,6) DEFAULT '1.000000',
  `precio_compra` decimal(15,4) DEFAULT NULL COMMENT 'Precio original en unidad de compra',
  `fecha_ingreso` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lote_proveedor` varchar(50) DEFAULT NULL COMMENT 'Nro de lote del proveedor',
  `observaciones` text,
  `estado` tinyint DEFAULT '1' COMMENT '1=activa, 0=agotada',
  PRIMARY KEY (`id_capa`),
  KEY `idx_item_bodega` (`item_general_id`,`bodegas_id`,`estado`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_fecha` (`fecha_ingreso`),
  KEY `bodegas_id` (`bodegas_id`),
  CONSTRAINT `inventario_capas_ibfk_1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`),
  CONSTRAINT `inventario_capas_ibfk_2` FOREIGN KEY (`bodegas_id`) REFERENCES `bodegas` (`id_bodegas`),
  CONSTRAINT `inventario_capas_ibfk_3` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario_capas`
--

LOCK TABLES `inventario_capas` WRITE;
/*!40000 ALTER TABLE `inventario_capas` DISABLE KEYS */;
INSERT INTO `inventario_capas` VALUES (9,134,2,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(10,135,2,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(11,136,2,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(12,137,2,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(13,138,2,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(14,139,2,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(15,140,2,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(16,141,2,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(17,194,2,NULL,NULL,NULL,6.0000,6.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(18,142,2,NULL,NULL,NULL,19.0000,19.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(19,143,18,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(20,144,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(21,145,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(22,146,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(23,147,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(24,189,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(25,149,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(26,150,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(27,151,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(28,152,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(29,134,18,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(30,153,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(31,156,18,NULL,NULL,NULL,4.0000,4.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(32,157,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(33,158,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(34,159,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(35,160,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(36,161,18,NULL,NULL,NULL,3.0000,3.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(37,190,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(38,191,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(39,162,18,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(40,163,18,NULL,NULL,NULL,4.0000,4.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(41,219,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(42,164,18,NULL,NULL,NULL,3.0000,3.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(43,192,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(44,165,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(45,166,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(46,167,18,NULL,NULL,NULL,6.0000,6.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(47,168,18,NULL,NULL,NULL,6.0000,6.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(48,169,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(49,171,18,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(50,172,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(51,173,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(52,136,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(53,174,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(54,175,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(55,177,18,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(56,178,18,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(57,179,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(58,180,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(59,181,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(60,182,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(61,184,18,NULL,NULL,NULL,3.0000,3.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(62,186,18,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(63,187,18,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(64,162,19,NULL,NULL,NULL,4.0000,4.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(65,195,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(66,196,19,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(67,197,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(68,150,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(69,172,19,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(70,198,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(71,199,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(72,200,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(73,201,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(74,202,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(75,203,19,NULL,NULL,NULL,3.0000,3.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(76,213,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(77,205,19,NULL,NULL,NULL,4.0000,4.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(78,206,19,NULL,NULL,NULL,4.0000,4.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(79,207,19,NULL,NULL,NULL,3.0000,3.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(80,208,19,NULL,NULL,NULL,4.0000,4.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(81,209,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(82,210,19,NULL,NULL,NULL,6.0000,6.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(83,211,19,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(84,212,19,NULL,NULL,NULL,8.0000,8.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(85,161,21,NULL,NULL,NULL,31.0000,31.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(86,214,21,NULL,NULL,NULL,7.0000,7.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(87,215,21,NULL,NULL,NULL,4.0000,4.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(88,216,21,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(89,217,21,NULL,NULL,NULL,4.0000,4.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(90,137,21,NULL,NULL,NULL,7.0000,4.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(91,180,21,NULL,NULL,NULL,113.0000,113.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(92,218,21,NULL,NULL,NULL,7.0000,7.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(93,219,21,NULL,NULL,NULL,2.0000,2.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(94,220,21,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(95,221,21,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(96,222,21,NULL,NULL,NULL,1.0000,1.0000,0.0000,NULL,1.000000,NULL,'2026-04-24 03:16:48',NULL,'Migración: saldo existente sin proveedor identificado',1),(183,37,1,23,105,46,10.0000,10.0000,5000.0000,9,1.000000,5000.0000,'2026-06-12 20:44:12','TEST-LOTE-6a2c6f9c9c651',NULL,1);
/*!40000 ALTER TABLE `inventario_capas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_general`
--

DROP TABLE IF EXISTS `item_general`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_general` (
  `id_item_general` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `codigo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` tinyint DEFAULT NULL COMMENT '0 productos\\n1 materia prima\\n2 Insumos',
  `categoria_id` int DEFAULT NULL,
  `viscosidad` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `p_g` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `p_kg` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `brillo_60` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `secado` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cubrimiento` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `molienda` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ph` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `poder_tintoreo` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unidad_id` int DEFAULT NULL,
  `costo_produccion` decimal(10,2) DEFAULT NULL,
  `precio_venta_manual` decimal(12,2) DEFAULT NULL,
  `precio_manual_activo` tinyint(1) DEFAULT '0',
  `unidad_almacenaje_id` int DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_item_general`),
  UNIQUE KEY `id_item_general_UNIQUE` (`id_item_general`),
  KEY `fk_item_general_categoria1_idx` (`categoria_id`),
  KEY `fk_item_general_unidad_id_idx` (`unidad_id`),
  KEY `fk_item_almacenaje` (`unidad_almacenaje_id`),
  KEY `idx_item_general_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_item_almacenaje` FOREIGN KEY (`unidad_almacenaje_id`) REFERENCES `unidad` (`id_unidad`)
) ENGINE=InnoDB AUTO_INCREMENT=369 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_general`
--

LOCK TABLES `item_general` WRITE;
/*!40000 ALTER TABLE `item_general` DISABLE KEYS */;
INSERT INTO `item_general` VALUES (1,'BARNIZ TRANSPARENTE BRILLANTE','BAR001',0,1,'95-100 KU','3,4+/-0,05 Kg','','STD','>=95','12 HORAS','','','','',5,0.00,56000.00,1,5,NULL),(2,'ESMALTE BLANCO','ESM002',0,1,'100-105 KU','3,6+/-0,05 Kg','','','>=90','12 HORAS','100+/-5 %','','','',4,7000.00,NULL,0,NULL,NULL),(3,'ESMALTE CAOBA','ESM003',0,1,'100-105 KU','3,6+/-0,05 Kg','',NULL,'>=90','6 HORAS','100+/-5%','7.5 H',NULL,NULL,3,11000.00,NULL,0,NULL,NULL),(4,'ESMALTE NEGRO MATE','ESM004',0,1,'105-110 KU','3,9+/-0,05 Kg','',NULL,'<=15','12 HORAS','100+/-5%','6 H',NULL,NULL,4,34050.00,NULL,0,NULL,NULL),(5,'ESMALTE ROJO FIESTA','ESM005',0,1,'100-105 KU','3,6+/-0,05 Kg','','','>= 90°','12 HORAS','100+/-5%','','','',1,27144.00,NULL,0,NULL,NULL),(6,'ESMALTE NEGRO BRILLANTE','ESM006',0,1,'100-105 KU','3.4+/-0.05 Kg','','','>= 90','12 HORAS','100+/-5%','','','',3,12691.00,NULL,0,NULL,NULL),(7,'ESMALTE VERDE ESMERALDA','ESM007',0,1,'100-105 KU','3.6+/-0,05 Kg','','','>=90','12 HORAS','100+/-5%','','','',1,4372.00,NULL,0,NULL,NULL),(8,'ESMALTE GRIS PLATA','ESM008',0,1,'100-105 KU','3,6+/-0,05 Kg','','','>=90','12 HORAS','100+/-5 %','','','',7,11466.00,NULL,0,NULL,NULL),(9,'ESMALTE AZUL ESPAÑOL','ESM009',0,1,'100-105 KU','3,6+/-0,05 Kg','',NULL,'>=90','12 HORAS','100+/-5 %','7.5 H',NULL,NULL,NULL,16300.00,NULL,0,NULL,NULL),(10,'ESMALTE BLANCO MATE','ESM010',0,1,'95-100','4,2 +/- 0,1 Kg','','','15','12 HORAS','100+/-5','','','',3,17000.00,NULL,0,NULL,NULL),(11,'ESMALTE AMARILLO','ESM011',0,1,'100-105 KU','3,6+/-0,05 Kg','',NULL,'>=90','12 HORAS','100+/-5','7.5 H',NULL,NULL,NULL,4400.00,NULL,0,NULL,NULL),(12,'ESMALTE NARANJA','ESM012',0,1,'100-105','3.5+/-0.05','',NULL,'>=90','12 HORAS','100+/-5','7.5 H',NULL,NULL,NULL,14300.00,NULL,0,NULL,NULL),(13,'ESMALTE TABACO','ESM013',0,1,'100-105KU','3.5+/-0.05','',NULL,'>=90','12 HORAS','100+/-5','7.5 H',NULL,NULL,NULL,40.00,NULL,0,NULL,NULL),(14,'ANTICORROSIVO GRIS','ANT014',0,3,'105-110 KU','4.2+/-0.05 Kg','',NULL,'MATE','6 HORAS','100+/-5','5,5',NULL,NULL,NULL,1550.00,NULL,0,NULL,NULL),(15,'ANTICORROSIVO NEGRO','ANT015',0,3,'105-110 KU','4.2+/-0.05 Kg','',NULL,'MATE','6 HORAS','100+/-5','5,5',NULL,NULL,NULL,4617.00,NULL,0,NULL,NULL),(16,'ANTICORROSIVO AMARILLO','ANT016',0,3,'105-110 KU','4.2+/-0.05 Kg','',NULL,'MATE','6 HORAS','100+/-5','5,5',NULL,NULL,NULL,8640.00,NULL,0,NULL,NULL),(17,'ANTICORROSIVO ROJO','ANT017',0,3,'105-110 KU','4.2+/-0.05 Kg','',NULL,'MATE','6 HORAS','100+/-5','5,5',NULL,NULL,NULL,14300.00,NULL,0,NULL,NULL),(18,'ANTICORROSIVO BLANCO','ANT018',0,3,'105-110 KU','4.2+/-0.05 Kg','',NULL,'MATE','6 HORAS','100+/-5','5,5',NULL,NULL,NULL,855.00,NULL,0,NULL,NULL),(19,'ANTICORROSIVO VERDE','ANT019',0,3,'105-110 KU','4.2+/-0.05 Kg','',NULL,'MATE','6 HORAS','100+/-5','5,5',NULL,NULL,NULL,5400.00,NULL,0,NULL,NULL),(20,'PASTA ESMALTE VERDE ENTONADOR','PAS020',0,2,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,8105.00,NULL,0,NULL,NULL),(21,'PASTA ESMALTE AZUL ENTONADOR','PAS021',0,2,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,12215.00,NULL,0,NULL,NULL),(22,'PASTA ESMALTE NEGRO','PAS022',2,2,'100 KU','4,55','','STD',NULL,NULL,NULL,'>7H','-','STD',NULL,19945.00,NULL,0,NULL,NULL),(23,'PASTA ESMALTE ROJO CARMIN 57:1','PAS023',0,2,'100 KU','5,55','','STD',NULL,NULL,NULL,'>7H','-','STD',NULL,14152.00,NULL,0,NULL,NULL),(24,'PASTA ESMALTE NARANJA','PAS024',2,2,'100 KU','5,55','','STD',NULL,NULL,NULL,'>7H','-','STD',NULL,11447.00,NULL,0,NULL,NULL),(25,'PASTA ESMALTE AMARILLO','PAS025',0,2,'100 KU','5,55','','STD',NULL,NULL,NULL,'>7H','-','STD',NULL,12718.00,NULL,0,NULL,NULL),(26,'PASTA ESMALTE CAOBA','PAS026',2,2,'100 KU','5,55','','STD',NULL,NULL,NULL,'>7H','-','STD',NULL,7742.00,NULL,0,NULL,NULL),(27,'PASTA ESMALTE AMARILLO OXIDO','PAS027',2,2,'100 KU','5,55','','STD',NULL,NULL,NULL,'>7H','-','STD',NULL,11447.00,NULL,0,NULL,NULL),(28,'PASTA ESMALTE ROJO OXIDO','PAS028',0,2,'100 KU','5,55','','STD',NULL,NULL,NULL,'>7H','-','STD',NULL,1690.00,NULL,0,NULL,NULL),(29,'PASTA ESMALTE BLANCO','PAS029',0,2,'120','5,78','','STD',NULL,NULL,NULL,'7,5','-','100 +/- 0.5 %',NULL,10303.00,NULL,0,NULL,NULL),(30,'PASTA ESMALTE TABACO','PAS030',2,2,'95-100','5.71-5.91','','STD',NULL,NULL,NULL,'7,5','-','STD',NULL,722.00,NULL,0,NULL,NULL),(31,'RESINA MEDIA EN SOYA AL 50%','RAM014',1,0,'','','','','','','','','','',0,715.00,NULL,0,NULL,NULL),(32,'METIL ETIL CETOXIMA','AAN002',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4300.00,NULL,0,NULL,NULL),(33,'OCTOATO DE COBALTO AL 12%','SOC011',1,0,'','','','','','','','','','',0,4400.00,NULL,0,NULL,NULL),(34,'OCTOATO DE ZIRCONIO AL 24%','SOZ024',1,0,'','','','','','','','','','',0,8000.00,NULL,0,NULL,NULL),(35,'OCTOATO DE CALCIO AL 10%','SOC010',1,0,'','','','','','','','','','',0,8000.00,NULL,0,NULL,NULL),(36,'DISOLVENTE 2232 #3','SAA011',1,0,'','','','','','','','','','',0,1103.00,NULL,0,NULL,NULL),(37,'DIOXIDO DE TITANIO','PED010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,5000.00,NULL,0,NULL,NULL),(38,'OCTOATO DE ZINC AL 16%','SOZ016',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,43900.00,NULL,0,NULL,NULL),(39,'BENTOCLAY BP 184','AAS005',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,37300.00,NULL,0,NULL,NULL),(40,'ETANOL AL 96%','SAA022',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,22700.00,NULL,0,NULL,NULL),(41,'DISASTAB','AEM005',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,7000.00,NULL,0,NULL,NULL),(42,'AGUA','SIA040',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,19500.00,NULL,0,NULL,NULL),(43,'SULFATO DE MAGNESIO','AET004',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,33500.00,NULL,0,NULL,NULL),(44,'VARSOL','SAV010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,37200.00,NULL,0,NULL,NULL),(47,'[DUP->59] MICROTALC C 20','CTA011',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,8000.00,NULL,0,NULL,'2026-05-30 14:45:21'),(48,'CELITE 499','MSI006',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,11466.00,NULL,0,NULL,NULL),(50,'PASTA ESMALTE ROJO 57:1','PE1033',2,2,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,17000.00,NULL,0,NULL,NULL),(52,'PASTA AMARILLO CROMO MEDIO','PE1010',2,2,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,17000.00,NULL,0,NULL,NULL),(54,'PASTA VERDE FTALO','PE1040',2,2,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4617.00,NULL,0,NULL,NULL),(56,'PASTA ESMALTE AZUL FTALO 15:3','PE1021',2,2,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,22700.00,NULL,0,NULL,NULL),(57,'OMYACARB UF','CCC002',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,11000.00,NULL,0,NULL,NULL),(59,'MICROTALC C 20','CTA025',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(60,'CARBONATO DE CALCIO HI WHITE','CCC004',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(61,'LECITINA DE SOYA','AHU002',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(62,'[DUP->40] ETANOL AL 96%','SAM023',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,'2026-05-30 14:45:21'),(63,'OXIDO DE HIERRO AMARILLO Y 4021','PEA010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(64,'OXIDO DE HIERRO ROJO R-5530','PER030',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(65,'MICROTALC 20','CTA020',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(66,'TROYSPERSE CD1','ADI002',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(67,'PIGMENTO VERDE FTALO 7','PEV053',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(68,'PIGMENTO AZUL FTALO 15;3','PEA041',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(69,'EDAPLAN 918 / LANSPERSE SUV','ADI010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(71,'POW CARBON BLACK CHEMO','PEN081',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(72,'PIGMENTO ROJO CARMIN 57:1','PER031',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(73,'PIGMENTO NARANJA MOLIBDENO','PEN023',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(74,'PIGMENTO MARILLO DE CROMO AL 73','PEA011',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(75,'PIGMENTO OXIFERR CAOBA MARRON M 4781','PEC081',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(76,'PIGMENTO OXIFERR AMARILLO Y-4011','PEA013',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(77,'DIOXIDO DE TITANIO SULFATO 2196','PED007',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(78,'OXIFER TABACO R-4370','PET080',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,8105.00,NULL,0,NULL,NULL),(79,'[DUP->39] BENTOCLAY BP 184','AAS012',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,'2026-05-30 14:45:21'),(80,'METANOL','SAM023',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(81,'ORGANOCLAY BK 884','AAS005',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(83,'DISOLVENTE 2232 / VARSOL','SAA011',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(84,'EDAPLAN 915','ADI010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(85,'CHEMOSPERSE 77','ADI011',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(86,'ADIMON 84','AAN002',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,0,NULL,NULL),(87,'DISOLVENTE #3','SAA011',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4372.00,NULL,0,NULL,NULL),(88,'ETANOL 96%','SAA022',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4400.00,NULL,0,NULL,NULL),(89,'DISOLVENTE 2232','SAA011',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4372.00,NULL,0,NULL,NULL),(90,'DISOLVENTE 3','SAA011',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4372.00,NULL,0,NULL,NULL),(92,'OCTOATO DE ZINC 16%','SOZ016',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,16300.00,NULL,0,NULL,NULL),(93,'PASTA ESMALTE AMARILLO CROMO MEDIO','PE1010',2,2,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,14152.00,NULL,0,NULL,NULL),(94,'[DUP->77] DIOXIDO DE TITANIO SULFATO 2196','PED010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,11466.00,NULL,0,NULL,'2026-05-30 14:45:21'),(95,'BENTOCLAY BP184','AAS005',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,17000.00,NULL,0,NULL,NULL),(96,'PASTA ESMALTE AZUL 15:3','PE1021',2,2,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,11447.00,NULL,0,NULL,NULL),(97,'EDAPLAN 918','ADI010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,22700.00,NULL,0,NULL,NULL),(98,'[DUP->97] EDAPLAN 918 / LANSPERSE SUV','ADI010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,22700.00,NULL,0,NULL,'2026-05-30 14:54:46'),(99,'[DUP->85] CHEMOSPERSE 77','ADI010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,22700.00,NULL,0,NULL,'2026-05-30 14:45:21'),(100,'PIGMENTO OXIFERR ROJO R-5530','PER030',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(133,'VINILO T1 BLANCO','EBT012',0,1,'','','','','','','','','','',NULL,1.00,NULL,0,NULL,NULL),(134,'SIKA WT-100 CO','SIK001',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(135,'POLASTOCRETE','SIK002',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(136,'SIKA STABILIZER 4R CO','SIK003',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(137,'SIKALASTIC 851 R COMP A','SIK004',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(138,'PLASTIMENT TM 5-CO','SIK005',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(139,'SARNACOL 2130','SIK006',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(140,'SIKAFUND MO-CO','SIK007',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(141,'RESINA ACRILICA MASFLEX','RES001',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(142,'UFI PRETHOX','VAR001',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(143,'RESINA NEGRA (POR IDENTIFICAR)','RES002',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(144,'PASTA AZUL PHILAC','PHI001',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(145,'ADITIVO NEGRO (MUESTRA)','VAR002',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(146,'SOLVENTE (POR IDENTIFICAR)','SAA099',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(147,'PROPIL MORENO','VAR003',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(148,'SILVACOL','VAR004',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(149,'HORNESABE BLANCO','HOR001',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(150,'HORNESABE BEIGE','HOR002',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(151,'HORNESABE ALEMANA','HOR003',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(152,'HORNESABE AMARILLO','HOR004',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(153,'SIKA FLOR CURATHANE','SIK008',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(154,'CAT PU','VAR005',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(155,'SIKALASTIC 871 R COMP B','SIK009',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(156,'LODO EPOXÍCO / RESINA EN POLVO','VAR006',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(157,'CAT SKAUR 32','VAR007',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(158,'PU VDE','VAR008',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(159,'RESINAS FERROBAR 903','RES003',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(160,'SIKALASTIC 830 COMP B','SIK010',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(161,'ETHYL SILICATO','VAR009',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(162,'RESINA CORTA R4','RES004',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(163,'RESINA PU','RES005',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(164,'PASTA ROJA PHILAC','PHI002',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(165,'PINTURA NEGRA COOKROT','VAR010',0,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(166,'XLOC PHILAC SOLVENTE','PHI003',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(167,'SIKA PLAY 169','SIK011',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(168,'SIKA FLUID 169','SIK012',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(169,'SIKAMANTO FLEX COMP A','SIK013',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(170,'LATEX PESANTE PARA TEJAS','VAR011',0,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(171,'PASTA CAOBA PHILAC','PHI004',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(172,'HORNESABE BCO','HOR005',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(173,'GROUP MORENO','VAR012',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(174,'SOLVENTE SUCIO SIKA','SIK014',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(175,'PROPILER RESINA COMP B','RES006',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(176,'SELLADOR NITRO','VAR013',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(177,'BINDA POLIURETANO','VAR014',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(178,'VINILO BEIGE','VAR015',0,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(179,'RESINA BEA EPOXICA','RES007',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(180,'FRUTA ROJA','PHI005',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(181,'RESINA BLANCA','RES008',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(182,'RESINA NARANJA','RES009',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(183,'MAFA LACA','VAR016',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(184,'SIKAPLAST REVOLVER CON AMOLRED','SIK015',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(185,'PASTA AMARILLA PHILAC','PHI006',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(186,'ALCONA CATANAS','VAR017',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(187,'SODA PH CONEXA AZL','VAR018',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(188,'CANALINOSE IBC','VAR019',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(189,'RESINA SIKA (MUESTRA)','SIK022',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(190,'INJEX HORENEM ADECRIL','VAR028',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(191,'CODO EPOXÍCO SIKA','VAR029',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(192,'NEGRO - NARANJA (POR IDENTIFICAR)','VAR031',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(193,'RASPER BASE BESS','VAR032',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(194,'ROJO IBC MASFLEX','VAR030',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(195,'EPOXICA (POR IDENTIFICAR)','VAR020',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(196,'PASTA AZUL CON GENA MORENO','PHI007',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(197,'HORNESABE DORADO','HOR006',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(198,'HORNESABE DORADO COBRE','HOR007',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(199,'HORNESABE VERDE ALBOA','HOR008',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(200,'HORNESABE AMARILLA','HOR009',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(201,'SPLANDER PHILAC','PHI008',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(202,'MICA INTERIOR PHILAC','PHI009',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(203,'PASTA MORADA CARVAJAL PHILAC','PHI010',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(204,'PASTA VIOLETA','PHI011',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(205,'GPS SIKA (MUESTRA)','SIK016',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(206,'XLOC PHILAC (POR ANALIZAR)','PHI012',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(207,'ANT BES AREPHE','VAR021',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(208,'MOLOC NARANJA','VAR022',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(209,'PASTA AZUL (TAMBOR LOP)','PHI013',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(210,'POLIESTER POP LUCY','VAR023',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(211,'CAT EPOXÍCO IBC','VAR024',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(212,'VINILO POP COLONIAL','VAR025',0,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(213,'TAMBOR AMARILLO SIKA (POR IDENTIFICAR)','SIK023',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(214,'SIKAPLAST REVOLVER','SIK017',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(215,'SIKA FULL REVOLVER','SIK018',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(216,'SIKA FILM','SIK019',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(217,'SIKA TRAFIC COMP A','SIK020',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(218,'MANCHA (COLORANTE)','PHI014',2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(219,'SPLANDER','VAR026',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(220,'LACANTE','VAR027',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(221,'SOLVENTE CON BORNELO','SAA030',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(222,'2N SIKA STABILIZER 100','SIK021',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,1,NULL),(223,'BARNIZ EPOXICO ','EP01',0,4,'','','','','','','','','','',2,NULL,NULL,0,NULL,NULL),(224,'EPOXICA TRANSPARENTE','EPTR91',0,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,0.00,NULL,0,NULL,NULL),(225,'XILOL','XIL21288',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(226,'RESINA EPOXICA','NPSN CHINA',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(230,'DISPERSANTE',NULL,2,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,5000.00,NULL,0,9,NULL),(231,'DISPERSANTE','093816',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,5378.00,NULL,0,NULL,NULL),(232,'CLEYTONE HY','927163',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,2000.00,NULL,0,NULL,NULL),(233,'AZUL ULTRAMAR','018273',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,2000.00,NULL,0,NULL,NULL),(235,'CARBONATO UF','556115',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,20.00,NULL,0,NULL,NULL),(236,'FOSFATO ZINC','521584',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,1000.00,NULL,0,NULL,NULL),(241,'ANTIPIEL','545124',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,2000.00,NULL,0,NULL,NULL),(242,'P-400','545124',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,2000.00,NULL,0,NULL,NULL),(244,'ISOBUTANOL',NULL,1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(245,'BUTIL GLICOL',NULL,1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(246,'TPF','MP-246',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(247,'NONIL TERGITOL','MP-247',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(248,'MECELLOSE','MP-248',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(249,'ANTIESPUMANTE','MP-249',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(250,'DIETILEN GLICOL','MP-250',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(251,'CARBONATO DE CALCIO','MP-251',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(252,'TALCO TY 400','MP-252',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(253,'CAOLIN','MP-253',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(254,'TEXANOL','MP-254',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(255,'ACRONAL','MP-255',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(256,'BACTERICIDA','MP-256',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(257,'AMONIACO','MP-257',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(258,'HISOL ASOCIATIVO','MP-258',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(259,'FUNGICIDA','MP-259',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(260,'ACEITE DE PINO','MP-260',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(261,'BUTIL CELLOSOLVE','MP-261',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(262,'TROYSSOL 366','MP-262',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(263,'POLVO PERLADO VERDOSO','MP-263',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(264,'POLVO PERLADO RICO EN ORO','MP-264',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(265,'RESINA 000','MP-265',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(266,'RESINA MALEICA AL 60%','MP-266',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(267,'PIGMENTO CROMATO DE ZINC','MP-267',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(268,'PIGMENTO ALUMINIO 22 NL','MP-268',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(269,'ACETATO N-PROPILO','MP-269',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(270,'UREA FORMAL','MP-270',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(271,'BYK 066N NIVELANTE','MP-271',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(272,'BYK 108 ANTIESPUMANTE','MP-272',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(273,'PIGMENTO VERDE OXIDO CROMO','MP-273',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(274,'RESINA EPOXICA 100%','MP-274',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(275,'VINILO BLANCO TIPO 2','VIN275',0,5,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(276,'VINILO BLANCO TIPO 3','VIN276',0,5,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(277,'ESMALTE AZUL REAL','ESM277',0,1,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(278,'LACA CATALIZADA BRILLANTE','LAC278',0,7,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(279,'PASTA OCRE PARA VINILO','PAS279',2,2,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(280,'VINILO OCRE T1','VIN280',0,5,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(281,'ESMALTE AMARILLO CATERPILLAR','ESM281',0,1,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(282,'ESMALTE NEGRO','ESM282',0,1,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(283,'ESMALTE BLANCO T1','ESM283',0,1,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(284,'ESMALTE BLANCO 4X1','ESM284',0,1,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(285,'ESMALTE BLANCO ECONOMICO J.J','ESM285',0,1,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(286,'ESMALTE ECONOMICO BLANCO J.H','ESM286',0,1,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(287,'ESMALTE DORADO','ESM287',0,1,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(288,'ANTICORROSIVO CROMATO ZN VERDE','ANT288',0,3,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(289,'ESMALTE DE ALUMINIO','ESM289',0,1,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(290,'EPOXICA BLANCO','EPX290',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(291,'EPOXICA NEGRA','EPX291',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(292,'EPOXICA GRIS','EPX292',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(293,'EPOXICA NEGRA RESINA 100%','EPX293',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(294,'EPOXICA POLIAMIDA VERDE','EPX294',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(295,'EPOXICA AZUL','EPX295',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(296,'EPOXICA ROJO OXIDO','EPX296',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(297,'ESMALTE EPOXI SILICATO BLANCO','ESM297',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(298,'ESMALTE EPOXI SILICATO VERDE','ESM298',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(299,'ESMALTE EPOXICO AMARILLO','ESM299',0,6,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),(300,'PVA RESIFLEX 610','PRE-193781',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,5040.00,NULL,0,9,NULL),(301,'PVA RESIFLEX F55M','PRE-192392',1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,7620.00,NULL,0,9,NULL),(302,'ENDURECEDOR 100%',NULL,1,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,9,NULL),(334,'COALESCENTE',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(335,'ESPESANTE ACRILICO ASOCIATIVO',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(336,'ESPESANTE CELULOSICO',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(337,'RESINA ACRILICA EXTERIORES',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(338,'DISPERSANTE BASE SOLVENTE',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(339,'NIVELANTE',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(340,'ANTIESPUMANTE EPOXICOS',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(341,'AMARILLO CROMO MEDIO',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(342,'HUMECTANTE BASE AGUA',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(343,'HIDROFUGANTE MATE',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(344,'HIDROFUGANTE BRILLANTE',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(345,'BIOCIDA DA PLUS',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(346,'BIOCIDA DA ULTRA',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(347,'OMYACARB 15',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(348,'OMYACARB 4',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(349,'TALCO EXTRA',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL),(350,'TALCO EXTRA MEJORADO',NULL,1,0,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,0,9,NULL);
/*!40000 ALTER TABLE `item_general` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_general_formulaciones`
--

DROP TABLE IF EXISTS `item_general_formulaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_general_formulaciones` (
  `id_item_general_formulaciones` int NOT NULL AUTO_INCREMENT,
  `formulaciones_id` int NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `porcentaje` int DEFAULT NULL,
  `item_general_id` int NOT NULL,
  PRIMARY KEY (`id_item_general_formulaciones`),
  KEY `fk_item_especifico_has_formulaciones_formulaciones1_idx` (`formulaciones_id`),
  KEY `fk_item_especifico_formulaciones_item_general1_idx` (`item_general_id`),
  CONSTRAINT `fk_igf_formulaciones` FOREIGN KEY (`formulaciones_id`) REFERENCES `formulaciones` (`id_formulaciones`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_igf_item_general` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=886 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_general_formulaciones`
--

LOCK TABLES `item_general_formulaciones` WRITE;
/*!40000 ALTER TABLE `item_general_formulaciones` DISABLE KEYS */;
INSERT INTO `item_general_formulaciones` VALUES (22,3,775.00,NULL,31),(23,3,103.00,NULL,26),(24,3,8.70,NULL,41),(25,3,290.00,NULL,42),(26,3,3.00,NULL,43),(27,3,3.30,NULL,86),(28,3,5.78,NULL,33),(29,3,9.10,NULL,34),(30,3,8.26,NULL,35),(31,3,113.00,NULL,36),(32,3,114.00,NULL,44),(33,4,775.00,NULL,31),(34,4,224.00,NULL,59),(35,4,40.00,NULL,48),(36,4,12.00,NULL,81),(37,4,6.00,NULL,40),(38,4,125.00,NULL,22),(39,4,8.70,NULL,41),(40,4,290.00,NULL,42),(41,4,2.90,NULL,43),(42,4,3.35,NULL,86),(43,4,5.86,NULL,33),(44,4,9.21,NULL,34),(45,4,8.37,NULL,35),(46,4,227.00,NULL,44),(58,6,775.00,NULL,31),(59,6,125.00,NULL,22),(60,6,5.70,NULL,41),(61,6,190.00,NULL,42),(62,6,1.90,NULL,43),(63,6,3.35,NULL,86),(64,6,5.86,NULL,33),(65,6,9.21,NULL,34),(66,6,8.37,NULL,35),(67,6,227.00,NULL,44),(68,7,775.00,NULL,31),(69,7,62.00,NULL,52),(70,7,10.40,NULL,56),(71,7,108.00,NULL,54),(72,7,6.20,NULL,41),(73,7,205.00,NULL,42),(74,7,2.10,NULL,43),(75,7,3.46,NULL,86),(76,7,6.05,NULL,33),(77,7,9.51,NULL,34),(78,7,8.65,NULL,35),(79,7,113.00,NULL,36),(80,7,114.00,NULL,44),(81,8,425.00,NULL,31),(82,8,251.00,NULL,37),(83,8,2.63,NULL,38),(84,8,16.00,NULL,39),(85,8,8.00,NULL,40),(86,8,3.30,NULL,27),(87,8,17.00,NULL,22),(88,8,14.20,NULL,41),(89,8,470.00,NULL,42),(90,8,4.70,NULL,43),(91,8,5.20,NULL,86),(92,8,9.37,NULL,33),(93,8,14.72,NULL,34),(94,8,13.40,NULL,35),(95,8,197.00,NULL,36),(96,8,200.00,NULL,44),(97,9,225.00,NULL,31),(98,9,56.00,NULL,37),(99,9,0.70,NULL,38),(100,9,2.00,NULL,39),(101,9,1.00,NULL,40),(102,9,168.00,NULL,56),(103,9,11.20,NULL,50),(104,9,9.70,NULL,41),(105,9,323.00,NULL,42),(106,9,3.23,NULL,43),(107,9,5.40,NULL,86),(108,9,9.45,NULL,33),(109,9,14.86,NULL,34),(110,9,13.51,NULL,35),(111,9,197.00,NULL,36),(112,9,165.00,NULL,44),(113,10,1173.00,NULL,31),(114,10,288.00,NULL,37),(115,10,435.00,NULL,57),(116,10,84.00,NULL,48),(117,10,5.00,NULL,38),(118,10,25.00,NULL,39),(119,10,10.00,NULL,40),(120,10,14.30,NULL,41),(121,10,477.00,NULL,42),(122,10,4.80,NULL,43),(123,10,4.69,NULL,86),(124,10,8.20,NULL,33),(125,10,12.90,NULL,34),(126,10,11.70,NULL,35),(127,10,433.00,NULL,44),(138,12,1033.00,NULL,31),(139,12,180.00,NULL,24),(140,12,77.00,NULL,52),(141,12,11.00,NULL,41),(142,12,363.00,NULL,42),(143,12,3.66,NULL,43),(144,12,4.64,NULL,86),(145,12,8.13,NULL,33),(146,12,12.77,NULL,34),(147,12,11.61,NULL,35),(148,12,391.00,NULL,44),(149,13,1033.00,NULL,31),(150,13,190.00,NULL,30),(151,13,11.00,NULL,41),(152,13,363.00,NULL,42),(153,13,3.60,NULL,43),(154,13,4.50,NULL,86),(155,13,7.90,NULL,33),(156,13,12.40,NULL,34),(157,13,11.30,NULL,35),(158,13,391.00,NULL,44),(190,16,274.00,NULL,31),(191,16,47.00,NULL,63),(192,16,220.00,NULL,59),(193,16,18.00,NULL,60),(194,16,1.30,NULL,61),(195,16,6.50,NULL,39),(196,16,4.00,NULL,40),(197,16,4.80,NULL,41),(198,16,160.00,NULL,42),(199,16,1.60,NULL,43),(200,16,1.10,NULL,86),(201,16,1.92,NULL,33),(202,16,3.00,NULL,34),(203,16,2.74,NULL,35),(204,16,142.60,NULL,44),(205,17,274.00,NULL,31),(206,17,58.00,NULL,64),(207,17,220.00,NULL,59),(208,17,18.00,NULL,60),(209,17,1.30,NULL,61),(210,17,6.50,NULL,39),(211,17,4.00,NULL,40),(212,17,4.70,NULL,41),(213,17,155.60,NULL,42),(214,17,1.55,NULL,43),(215,17,1.10,NULL,86),(216,17,1.92,NULL,33),(217,17,3.00,NULL,34),(218,17,2.74,NULL,35),(219,17,142.60,NULL,44),(253,20,186.00,NULL,31),(254,20,3.00,NULL,32),(255,20,3.00,NULL,39),(256,20,8.00,NULL,66),(257,20,50.00,NULL,67),(258,20,2.00,NULL,40),(259,20,76.00,NULL,44),(260,21,186.00,NULL,31),(261,21,3.00,NULL,32),(262,21,5.00,NULL,39),(263,21,3.00,NULL,80),(264,21,15.00,NULL,61),(265,21,52.00,NULL,68),(266,21,5.00,NULL,97),(267,21,76.00,NULL,44),(268,22,242.00,NULL,31),(269,22,3.10,NULL,86),(270,22,9.00,NULL,97),(271,22,25.00,NULL,61),(272,22,59.00,NULL,71),(273,23,55.00,NULL,31),(274,23,0.80,NULL,39),(275,23,0.40,NULL,80),(276,23,0.25,NULL,86),(277,23,2.80,NULL,85),(278,23,1.60,NULL,61),(279,23,24.00,NULL,72),(280,23,34.00,NULL,44),(281,24,332.00,NULL,31),(282,24,9.00,NULL,39),(283,24,5.00,NULL,80),(284,24,3.10,NULL,86),(285,24,35.00,NULL,85),(286,24,18.90,NULL,61),(287,24,408.00,NULL,73),(288,24,150.00,NULL,44),(289,25,332.00,NULL,31),(290,25,9.00,NULL,39),(291,25,5.00,NULL,80),(292,25,3.10,NULL,86),(293,25,18.90,NULL,61),(294,25,465.00,NULL,74),(295,25,150.00,NULL,44),(296,26,295.00,NULL,31),(297,26,6.00,NULL,39),(298,26,3.00,NULL,80),(299,26,3.10,NULL,86),(300,26,35.00,NULL,97),(301,26,18.90,NULL,61),(302,26,340.00,NULL,75),(303,26,173.00,NULL,44),(304,27,295.00,NULL,31),(305,27,6.00,NULL,39),(306,27,3.00,NULL,80),(307,27,3.10,NULL,86),(308,27,18.90,NULL,61),(309,27,340.00,NULL,76),(310,27,150.00,NULL,36),(311,28,295.00,NULL,31),(312,28,6.00,NULL,39),(313,28,3.00,NULL,80),(314,28,3.10,NULL,86),(315,28,17.00,NULL,97),(316,28,18.90,NULL,61),(317,28,340.00,NULL,100),(318,28,150.00,NULL,36),(319,29,213.00,NULL,31),(320,29,22.00,NULL,39),(321,29,4.00,NULL,66),(322,29,5.00,NULL,40),(323,29,441.00,NULL,37),(324,29,63.00,NULL,44),(325,30,1.00,NULL,86),(326,30,185.00,NULL,78),(327,30,134.00,NULL,31),(328,30,6.00,NULL,66),(329,30,8.00,NULL,39),(330,30,7.00,NULL,61),(331,30,33.00,NULL,44),(332,30,2.00,NULL,40),(333,25,35.00,NULL,84),(334,27,35.00,NULL,84),(335,22,150.00,NULL,83),(385,31,180.00,NULL,42),(386,31,1.60,NULL,246),(387,31,3.00,NULL,231),(388,31,2.50,NULL,247),(389,31,2.50,NULL,248),(390,31,2.00,NULL,249),(391,31,10.00,NULL,250),(392,31,95.00,NULL,37),(393,31,200.00,NULL,251),(394,31,60.00,NULL,252),(395,31,80.00,NULL,253),(396,31,80.00,NULL,235),(397,31,5.00,NULL,254),(398,31,170.00,NULL,255),(399,31,4.00,NULL,256),(400,31,3.00,NULL,249),(401,31,127.00,NULL,42),(402,31,4.00,NULL,257),(403,31,3.00,NULL,248),(404,31,1.50,NULL,258),(405,31,2.00,NULL,259),(406,31,0.40,NULL,260),(407,32,100.00,NULL,226),(408,32,30.00,NULL,225),(409,32,20.00,NULL,244),(410,32,10.00,NULL,142),(411,32,5.00,NULL,261),(412,32,10.00,NULL,269),(413,33,0.00,NULL,42),(414,33,1.50,NULL,246),(415,33,2.00,NULL,231),(416,33,2.00,NULL,248),(417,33,2.60,NULL,247),(418,33,5.00,NULL,250),(419,33,2.50,NULL,254),(420,33,2.00,NULL,249),(421,33,35.00,NULL,37),(422,33,400.00,NULL,251),(423,33,50.00,NULL,252),(424,33,50.00,NULL,253),(425,33,80.00,NULL,255),(426,33,2.00,NULL,249),(427,33,4.00,NULL,257),(428,33,4.00,NULL,248),(429,33,4.00,NULL,256),(430,33,3.30,NULL,258),(431,33,0.40,NULL,260),(432,34,1.00,NULL,42),(433,34,1.40,NULL,246),(434,34,2.20,NULL,231),(435,34,2.00,NULL,247),(436,34,10.00,NULL,248),(437,34,60.00,NULL,37),(438,34,425.00,NULL,252),(439,34,10.00,NULL,251),(440,34,3.00,NULL,253),(441,34,36.00,NULL,250),(442,34,2.40,NULL,255),(443,34,2.00,NULL,256),(444,34,4.00,NULL,249),(445,34,2.50,NULL,248),(446,34,4.00,NULL,257),(447,34,4.00,NULL,258),(448,34,0.40,NULL,260),(449,35,180.00,NULL,31),(450,35,2.00,NULL,232),(451,35,1.00,NULL,231),(452,35,130.00,NULL,44),(453,35,3.00,NULL,235),(454,35,30.00,NULL,56),(455,35,2.00,NULL,37),(456,35,1.50,NULL,236),(457,35,1.00,NULL,33),(458,35,1.00,NULL,35),(459,35,1.00,NULL,34),(460,35,0.40,NULL,32),(461,35,0.30,NULL,242),(462,36,60.00,NULL,162),(463,36,40.00,NULL,142),(464,36,55.00,NULL,225),(465,36,2.50,NULL,261),(466,36,12.00,NULL,244),(467,36,0.30,NULL,262),(468,37,40.00,NULL,42),(469,37,2.00,NULL,247),(470,37,0.50,NULL,246),(471,37,1.50,NULL,256),(472,37,1.50,NULL,231),(473,37,40.00,NULL,63),(474,37,15.00,NULL,250),(475,37,2.00,NULL,249),(476,37,1.20,NULL,248),(477,37,0.30,NULL,258),(478,38,80.00,NULL,42),(479,38,0.70,NULL,231),(480,38,1.20,NULL,249),(481,38,0.60,NULL,247),(482,38,0.60,NULL,246),(483,38,1.00,NULL,248),(484,38,18.00,NULL,63),(485,38,40.00,NULL,252),(486,38,15.00,NULL,251),(487,38,40.00,NULL,255),(488,38,2.10,NULL,250),(489,38,0.70,NULL,254),(490,38,1.00,NULL,256),(491,38,0.90,NULL,257),(492,38,0.60,NULL,248),(493,38,0.40,NULL,260),(494,38,0.60,NULL,249),(495,38,1.50,NULL,258),(496,39,60.00,NULL,31),(497,39,0.50,NULL,231),(498,39,1.00,NULL,232),(499,39,22.00,NULL,63),(500,39,10.00,NULL,44),(501,39,1.00,NULL,236),(502,39,40.00,NULL,31),(503,39,35.00,NULL,44),(504,39,1.00,NULL,33),(505,39,1.20,NULL,34),(506,39,1.00,NULL,35),(507,39,0.40,NULL,241),(508,39,0.20,NULL,242),(509,40,180.00,NULL,31),(510,40,0.50,NULL,231),(511,40,1.50,NULL,232),(512,40,30.00,NULL,22),(513,40,30.00,NULL,44),(514,40,1.00,NULL,236),(515,40,0.50,NULL,41),(516,40,24.00,NULL,42),(517,40,1.20,NULL,35),(518,40,1.00,NULL,33),(519,40,1.30,NULL,34),(520,40,0.80,NULL,32),(521,40,0.20,NULL,242),(522,40,5.00,NULL,44),(523,41,180.00,NULL,31),(524,41,2.00,NULL,231),(525,41,5.00,NULL,232),(526,41,0.28,NULL,233),(527,41,25.00,NULL,235),(528,41,100.00,NULL,37),(529,41,5.00,NULL,236),(530,41,30.00,NULL,44),(531,41,200.00,NULL,31),(532,41,185.00,NULL,44),(533,41,2.50,NULL,33),(534,41,3.00,NULL,35),(535,41,3.50,NULL,34),(536,41,1.80,NULL,241),(537,41,0.40,NULL,242),(538,42,80.00,NULL,31),(539,42,1.50,NULL,232),(540,42,0.50,NULL,231),(541,42,5.00,NULL,44),(542,42,35.00,NULL,37),(543,42,6.00,NULL,236),(544,42,6.00,NULL,235),(545,42,40.00,NULL,44),(546,42,10.00,NULL,31),(547,42,0.90,NULL,35),(548,42,0.80,NULL,33),(549,42,0.85,NULL,34),(550,42,0.40,NULL,241),(551,42,0.50,NULL,242),(552,42,0.10,NULL,204),(553,43,40.00,NULL,31),(554,43,0.20,NULL,231),(555,43,0.70,NULL,232),(556,43,0.09,NULL,233),(557,43,9.00,NULL,37),(558,43,1.90,NULL,251),(559,43,6.00,NULL,44),(560,43,0.30,NULL,41),(561,43,11.00,NULL,42),(562,43,17.50,NULL,44),(563,43,0.25,NULL,33),(564,43,0.35,NULL,35),(565,43,0.40,NULL,34),(566,43,0.25,NULL,241),(567,44,46.00,NULL,31),(568,44,0.20,NULL,231),(569,44,0.60,NULL,232),(570,44,0.09,NULL,233),(571,44,10.20,NULL,37),(572,44,7.00,NULL,44),(573,44,0.50,NULL,41),(574,44,16.00,NULL,42),(575,44,7.00,NULL,44),(576,44,0.32,NULL,33),(577,44,0.50,NULL,35),(578,44,0.50,NULL,34),(579,44,0.20,NULL,241),(580,45,105.00,NULL,31),(581,45,1.60,NULL,232),(582,45,39.00,NULL,44),(583,45,1.40,NULL,33),(584,45,1.60,NULL,34),(585,45,1.50,NULL,35),(586,45,6.40,NULL,263),(587,45,1.60,NULL,264),(588,45,0.50,NULL,241),(589,46,35.00,NULL,265),(590,46,1.20,NULL,232),(591,46,0.50,NULL,231),(592,46,6.50,NULL,37),(593,46,25.00,NULL,267),(594,46,4.00,NULL,63),(595,46,38.00,NULL,252),(596,46,38.00,NULL,251),(597,46,20.00,NULL,225),(598,46,15.00,NULL,266),(599,46,3.00,NULL,244),(600,46,50.00,NULL,265),(601,46,0.50,NULL,33),(602,46,0.60,NULL,35),(603,46,0.50,NULL,34),(604,46,0.60,NULL,22),(605,46,1.20,NULL,56),(606,46,30.00,NULL,225),(607,47,210.00,NULL,31),(608,47,1.40,NULL,232),(609,47,0.75,NULL,231),(610,47,2.90,NULL,244),(611,47,28.00,NULL,268),(612,47,69.00,NULL,44),(613,47,2.50,NULL,35),(614,47,2.60,NULL,34),(615,47,1.20,NULL,33),(616,47,1.00,NULL,241),(617,48,120.00,NULL,226),(618,48,1.50,NULL,231),(619,48,25.00,NULL,225),(620,48,2.00,NULL,232),(621,48,3.00,NULL,236),(622,48,78.00,NULL,37),(623,48,150.00,NULL,252),(624,48,40.00,NULL,226),(625,48,25.00,NULL,225),(626,48,50.00,NULL,235),(627,48,4.50,NULL,245),(628,48,1.00,NULL,270),(629,48,18.00,NULL,244),(630,48,10.00,NULL,161),(631,49,60.00,NULL,226),(632,49,1.00,NULL,231),(633,49,1.30,NULL,232),(634,49,2.50,NULL,236),(635,49,10.00,NULL,225),(636,49,80.00,NULL,252),(637,49,8.00,NULL,251),(638,49,32.00,NULL,22),(639,49,20.00,NULL,226),(640,49,9.00,NULL,244),(641,49,7.60,NULL,142),(642,49,8.00,NULL,269),(643,49,14.00,NULL,225),(644,49,1.00,NULL,261),(645,50,100.00,NULL,226),(646,50,5.00,NULL,232),(647,50,1.50,NULL,231),(648,50,15.00,NULL,225),(649,50,4.00,NULL,236),(650,50,68.00,NULL,37),(651,50,50.00,NULL,251),(652,50,165.00,NULL,252),(653,50,2.40,NULL,261),(654,50,60.00,NULL,226),(655,50,15.00,NULL,244),(656,50,3.00,NULL,22),(657,50,10.00,NULL,142),(658,50,8.00,NULL,269),(659,50,30.00,NULL,225),(660,51,100.00,NULL,274),(661,51,0.80,NULL,231),(662,51,1.80,NULL,232),(663,51,2.20,NULL,236),(664,51,2.00,NULL,225),(665,51,70.00,NULL,252),(666,51,8.00,NULL,251),(667,51,5.00,NULL,244),(668,51,4.00,NULL,269),(669,51,1.00,NULL,261),(670,51,32.00,NULL,22),(671,52,76.30,NULL,226),(672,52,1.40,NULL,231),(673,52,5.00,NULL,232),(674,52,20.00,NULL,225),(675,52,3.80,NULL,37),(676,52,16.00,NULL,252),(677,52,10.00,NULL,236),(678,52,100.00,NULL,251),(679,52,17.50,NULL,63),(680,52,5.00,NULL,261),(681,52,26.00,NULL,225),(682,52,1.90,NULL,56),(683,53,13.00,NULL,226),(684,53,0.25,NULL,231),(685,53,0.75,NULL,232),(686,53,12.00,NULL,56),(687,53,22.00,NULL,226),(688,53,2.00,NULL,225),(689,53,2.00,NULL,142),(690,53,3.00,NULL,244),(691,53,1.50,NULL,261),(692,53,3.50,NULL,225),(693,53,0.05,NULL,271),(694,53,0.23,NULL,272),(695,53,4.00,NULL,252),(696,53,20.00,NULL,251),(697,54,120.00,NULL,226),(698,54,2.00,NULL,272),(699,54,2.50,NULL,232),(700,54,22.00,NULL,225),(701,54,40.00,NULL,64),(702,54,2.00,NULL,236),(703,54,100.00,NULL,252),(704,54,8.00,NULL,261),(705,54,50.00,NULL,251),(706,54,40.00,NULL,226),(707,54,28.00,NULL,244),(708,54,1.50,NULL,270),(709,54,16.00,NULL,269),(710,54,20.00,NULL,225),(711,55,5.00,NULL,226),(712,55,0.25,NULL,231),(713,55,0.20,NULL,232),(714,55,2.90,NULL,37),(715,55,0.50,NULL,236),(716,55,0.50,NULL,225),(717,55,11.30,NULL,161),(718,56,4.00,NULL,226),(719,56,0.16,NULL,232),(720,56,0.20,NULL,231),(721,56,0.40,NULL,273),(722,56,0.20,NULL,37),(723,56,1.20,NULL,63),(724,56,0.80,NULL,225),(725,56,9.20,NULL,161),(726,57,55.00,NULL,226),(727,57,20.00,NULL,225),(728,57,1.50,NULL,232),(729,57,1.50,NULL,231),(730,57,0.50,NULL,63),(731,57,28.00,NULL,74),(732,57,34.00,NULL,226),(733,57,3.00,NULL,261),(734,57,15.00,NULL,244),(735,57,8.00,NULL,270),(736,57,0.50,NULL,262),(737,1,115.00,NULL,31),(738,1,66.00,NULL,44),(739,1,1.30,NULL,33),(740,1,1.30,NULL,34),(741,1,1.60,NULL,35),(742,1,0.50,NULL,241),(743,2,180.00,NULL,31),(744,2,2.00,NULL,231),(745,2,5.00,NULL,232),(746,2,0.28,NULL,233),(747,2,180.00,NULL,37),(748,2,20.00,NULL,235),(749,2,3.00,NULL,236),(750,2,0.00,NULL,44),(751,2,180.00,NULL,31),(752,2,15.00,NULL,44),(753,2,2.00,NULL,41),(754,2,0.00,NULL,42),(755,2,2.00,NULL,33),(756,2,2.00,NULL,34),(757,2,2.00,NULL,35),(758,2,2.00,NULL,241),(759,2,0.35,NULL,242),(760,5,100.00,NULL,31),(761,5,0.80,NULL,232),(762,5,18.00,NULL,23),(763,5,0.80,NULL,236),(764,5,50.00,NULL,44),(765,5,1.00,NULL,33),(766,5,1.30,NULL,34),(767,5,1.20,NULL,35),(768,5,0.80,NULL,32),(769,11,100.00,NULL,31),(770,11,16.00,NULL,74),(771,11,1.00,NULL,232),(772,11,0.50,NULL,231),(773,11,0.75,NULL,236),(774,11,10.00,NULL,44),(775,11,1.00,NULL,235),(776,11,25.00,NULL,44),(777,11,0.50,NULL,41),(778,11,15.00,NULL,42),(779,11,0.50,NULL,33),(780,11,0.50,NULL,34),(781,11,0.50,NULL,35),(782,11,0.40,NULL,241),(783,14,160.00,NULL,31),(784,14,1.50,NULL,232),(785,14,1.00,NULL,231),(786,14,35.00,NULL,44),(787,14,20.00,NULL,37),(788,14,1.50,NULL,236),(789,14,120.00,NULL,252),(790,14,35.00,NULL,44),(791,14,1.00,NULL,41),(792,14,65.00,NULL,42),(793,14,0.50,NULL,22),(794,14,1.00,NULL,35),(795,14,0.80,NULL,34),(796,14,0.50,NULL,33),(797,14,0.40,NULL,241),(798,15,210.00,NULL,31),(799,15,1.50,NULL,232),(800,15,0.60,NULL,231),(801,15,15.00,NULL,44),(802,15,1.50,NULL,236),(803,15,130.00,NULL,252),(804,15,38.00,NULL,22),(805,15,0.80,NULL,33),(806,15,1.00,NULL,34),(807,15,1.00,NULL,35),(808,15,45.00,NULL,44),(809,15,0.80,NULL,241),(810,18,160.00,NULL,31),(811,18,1.50,NULL,232),(812,18,1.00,NULL,231),(813,18,1.50,NULL,236),(814,18,30.00,NULL,44),(815,18,60.00,NULL,252),(816,18,25.00,NULL,37),(817,18,50.00,NULL,251),(818,18,55.00,NULL,44),(819,18,1.00,NULL,41),(820,18,52.00,NULL,42),(821,18,1.00,NULL,35),(822,18,1.20,NULL,34),(823,18,0.80,NULL,33),(824,18,0.80,NULL,241),(825,19,95.00,NULL,31),(826,19,0.80,NULL,232),(827,19,0.30,NULL,231),(828,19,5.00,NULL,63),(829,19,1.00,NULL,37),(830,19,66.00,NULL,252),(831,19,33.00,NULL,251),(832,19,10.00,NULL,44),(833,19,1.00,NULL,56),(834,19,0.50,NULL,35),(835,19,0.40,NULL,33),(836,19,0.60,NULL,34),(837,19,0.40,NULL,241);
/*!40000 ALTER TABLE `item_general_formulaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_proveedor`
--

DROP TABLE IF EXISTS `item_proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_proveedor` (
  `id_item_proveedor` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `codigo` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `precio_con_iva` decimal(10,2) DEFAULT NULL,
  `disponible` tinyint DEFAULT NULL COMMENT '1 Disponible 2 No disponible',
  `descripcion` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `proveedor_id` int DEFAULT NULL,
  `item_general_id` int DEFAULT NULL,
  `unidad_compra_id` int DEFAULT NULL,
  `factor_conversion` decimal(15,6) NOT NULL DEFAULT '1.000000',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_item_proveedor`),
  UNIQUE KEY `id_item_proveedor_UNIQUE` (`id_item_proveedor`),
  KEY `fk_item_proveedor_proveedores1_idx` (`proveedor_id`),
  KEY `idx_item_proveedor_deleted_at` (`deleted_at`),
  KEY `fk_ip_item_general` (`item_general_id`),
  CONSTRAINT `fk_ip_item_general` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ip_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id_proveedor`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_proveedor`
--

LOCK TABLES `item_proveedor` WRITE;
/*!40000 ALTER TABLE `item_proveedor` DISABLE KEYS */;
INSERT INTO `item_proveedor` VALUES (40,'TALCO TY 400 G','QUI-MAT-0001','Materia Prima',1504.00,1790.00,1,NULL,23,NULL,NULL,1.000000,NULL),(41,'OMIYACARB UF','QUI-MAT-0002','Materia Prima',1828.00,2175.00,1,NULL,23,NULL,NULL,1.000000,NULL),(42,'COLARDIT ANTIESPUMANTE','QUI-ADIT-0001','Insumo',7143.00,8500.00,1,NULL,23,NULL,NULL,1.000000,NULL),(43,'COLARCRYL ACRONAL 50','QUI-ADIT-0002','Insumo',5252.00,6250.00,1,NULL,23,NULL,NULL,1.000000,NULL),(44,'COLARCIDE BACTERICIDA','QUI-BIO-0001','Insumo',6387.00,7600.00,1,NULL,23,NULL,NULL,1.000000,NULL),(45,'COLARDIT REGULADOR PH','QUI-ADIT-0003','Insumo',6555.00,7800.00,1,NULL,23,NULL,NULL,1.000000,NULL),(46,'DISPERSANTE','QUI-ADIT-0004','Insumo',5378.00,6400.00,1,'',23,230,NULL,1.000000,NULL),(47,'COLARDIT AS ASOCIATIVO','QUI-ADIT-0005','Insumo',9916.00,11800.00,1,NULL,23,NULL,NULL,1.000000,NULL),(48,'COLARBAG FUNGICIDA','QUI-BIO-0002','Insumo',20840.00,24800.00,1,NULL,23,NULL,NULL,1.000000,NULL),(49,'BRITEX CALCINADO','QUI-MAT-0003','Materia Prima',2605.00,3100.00,1,NULL,23,NULL,NULL,1.000000,NULL),(50,'WEKCELO C7 CELULOSICO','QUI-ESP-0001','Insumo',18403.00,21900.00,1,NULL,23,NULL,NULL,1.000000,NULL),(51,'CARBONATO DE CALCIO M325','CARBM325','Materia Prima',300.00,357.00,1,'',24,NULL,9,1.000000,NULL),(52,'CARBONATO DE CALCIO M600','CARBM600','Materia Prima',460.00,547.00,1,'',24,NULL,9,1.000000,NULL),(53,'RESINA EPOXICA','NPSN CHINA','Materia Prima',15069.00,17932.00,1,'',25,226,9,1.000000,NULL),(54,'RESINA KR 828 100%','KER828','Materia Prima',10300.00,12257.00,1,'',26,NULL,9,1.000000,NULL),(55,'ENDURECEDOR 100%','NT-1515X70','Materia Prima',20000.00,23800.00,1,'',26,302,9,1.000000,NULL),(56,'ENDURECEDOR 100%','NX-5454','Materia Prima',19700.00,23443.00,1,'',26,NULL,9,1.000000,NULL),(57,'XILOL','XIL21288','Materia Prima',6120.00,7283.00,1,'',27,225,9,1.000000,NULL),(58,'THINNER ','TH2092','Materia Prima',15961.00,18994.00,1,'',27,NULL,3,1.000000,NULL),(59,'VARSOL','VAR9218','Materia Prima',5961.00,7094.00,1,'',27,NULL,NULL,1.000000,NULL),(60,'VARSOL','VARPD281','Materia Prima',1230.00,1464.00,1,'',28,NULL,NULL,1.000000,NULL),(61,'THINNER','TH921298','Materia Prima',880.00,1047.00,1,'',28,NULL,3,1.000000,NULL),(62,'RESINA MEDIA EN SOYA AL 50%','RA-7','Materia Prima',6200.00,7378.00,1,'',29,NULL,9,1.000000,NULL),(63,'RESINA UREA FORMALDEHIDO','RN-9E','Materia Prima',8050.00,9580.00,1,'',29,NULL,9,1.000000,NULL),(64,'RESINA CORTA EN PALMISTE AL 55%','RA-4 ','Materia Prima',7350.00,8747.00,1,'',29,NULL,9,1.000000,NULL),(65,'RESINA CORTA EN SOYA AL 53%','RA-15','Materia Prima',6900.00,8211.00,1,'',29,NULL,9,1.000000,NULL),(66,'RESINA CORTA EN SOYA AL 55% (+ SOL)','RA-15M','Materia Prima',6900.00,8211.00,1,'',29,NULL,9,1.000000,NULL),(67,'RESINA CORTA EN SOYA AL 45%','RA-16','Materia Prima',6950.00,8271.00,1,'',29,NULL,9,1.000000,NULL),(68,'RESINA MALEICA SOLIDA','RM-1','Materia Prima',11300.00,13447.00,1,'',29,NULL,9,1.000000,NULL),(69,'RESINA MEDIA EN TOFA AL 50%','RA-22','Materia Prima',7550.00,8985.00,1,'',29,NULL,9,1.000000,NULL),(70,'RESINA MEDIA EN SOYA AL 50%','RA-23','Materia Prima',6350.00,7557.00,1,'',29,NULL,9,1.000000,NULL),(71,'RESINA LARGA EN SOYA AL 70%','RA-25','Materia Prima',7300.00,8687.00,1,'',29,NULL,9,1.000000,NULL),(72,'RESINA CHAIN STOPPED AL 60%','RA-37','Materia Prima',7200.00,8568.00,1,'',29,NULL,9,1.000000,NULL),(73,'RESINA CORTA EN TOFA AL 55%','RA-44','Materia Prima',8200.00,9758.00,1,'',29,NULL,9,1.000000,NULL),(74,'RESINA UREA FORMALDEHIDO','RN-9E','Materia Prima',8050.00,9580.00,1,'',29,NULL,9,1.000000,NULL),(75,'XILOL','XILB6800','Materia Prima',5714.29,6800.00,1,'',30,225,9,1.000000,NULL),(76,'ISOBUTANOL','ISOB7100','Materia Prima',5966.39,7100.00,1,'',30,244,9,1.000000,NULL),(77,'Butil Glicol','BUTB890','Materia Prima',7478.99,8900.00,1,'',30,245,9,1.000000,NULL),(78,'PVA RESIFLEX 610','PRE-193781','Materia Prima',5040.00,5998.00,1,'',31,300,9,1.000000,NULL),(79,'PVA RESIFLEX F55M','PRE-192392','Materia Prima',7620.00,9068.00,1,'',31,301,9,1.000000,NULL),(80,'DISPERS PA45','PA45','Materia Prima',8500.00,10115.00,1,'Dispersante base agua. Tambor 200 kg',32,231,9,1.000000,NULL),(81,'RWANTA C-12','C-12','Materia Prima',10000.00,11900.00,1,'Coalescente. Tambor 200 kg',32,334,9,1.000000,NULL),(82,'AKASIL RE20','RE20','Materia Prima',18000.00,21420.00,1,'Antiespumante base agua. Tambor 200 kg',32,249,9,1.000000,NULL),(83,'REOLOG 30','REOLOG30','Materia Prima',11500.00,13685.00,1,'Espesante acrilico asociativo. Tambor 200 kg',32,335,9,1.000000,NULL),(84,'HEM 150.000S','HEM150000S','Materia Prima',19500.00,23205.00,1,'Espesante celulosico',32,336,9,1.000000,NULL),(85,'FLEX CA50','CA50','Materia Prima',6800.00,8092.00,1,'Resina Acrilica para exteriores',32,337,9,1.000000,NULL),(86,'LANSPERSE SUV','LANSPERSE','Materia Prima',25000.00,29750.00,1,'Dispersante base solvente para esmaltes. Tambor 200 kg',32,338,9,1.000000,NULL),(87,'SILOEN LA 271','LA271','Materia Prima',80000.00,95200.00,1,'Nivelante. Cunete 20 kg',32,339,9,1.000000,NULL),(88,'SILOEN DA290','DA290','Materia Prima',90000.00,107100.00,1,'Antiespumante para epoxicos. Cunete 20 kg',32,340,9,1.000000,NULL),(89,'AZUL 15.3',NULL,'Materia Prima',29500.00,35105.00,1,'Pigmento Azul Ftalo PB15:3',32,68,9,1.000000,NULL),(90,'VERDE 7',NULL,'Materia Prima',31000.00,36890.00,1,'Pigmento Verde Ftalo PG7',32,67,9,1.000000,NULL),(91,'CROMATO DE ZINC',NULL,'Materia Prima',19000.00,22610.00,1,'Pigmento Cromato de Zinc',32,267,9,1.000000,NULL),(92,'FOSFATO DE ZINC',NULL,'Materia Prima',20500.00,24395.00,1,'Pigmento Fosfato de Zinc',32,236,9,1.000000,NULL),(93,'AMARILLO CROMO MEDIO',NULL,'Materia Prima',20500.00,24395.00,1,'Pigmento Amarillo Cromo Medio',32,341,9,1.000000,NULL),(94,'CAOLIN CALCINADO',NULL,'Materia Prima',2900.00,3451.00,1,'Caolin Calcinado',32,253,9,1.000000,NULL),(95,'HUMECTANTE BASE AGUA CERO VOC',NULL,'Materia Prima',29000.00,34510.00,1,'Humectante base agua, cero VOC',32,342,9,1.000000,NULL),(96,'ACRONAL (resina acrilica estirenada)',NULL,'Materia Prima',7563.03,9000.00,1,'kg ex-bulto 20kg, IVA incl',33,255,9,1.000000,NULL),(97,'LATECOL (espesante acrilico)',NULL,'Materia Prima',7563.03,9000.00,1,'kg ex-bulto 20kg, IVA incl',33,335,9,1.000000,NULL),(98,'PVA (resina vinil acrilica 50-55%)',NULL,'Materia Prima',6722.69,8000.00,1,'kg ex-bulto 20kg, IVA incl',33,301,9,1.000000,NULL),(99,'TEXANOL / COALESCENTE',NULL,'Materia Prima',14705.88,17500.00,1,'kg ex-bulto 20kg, IVA incl',33,254,9,1.000000,NULL),(100,'ANTIESPUMANTE',NULL,'Materia Prima',9663.87,11500.00,1,'kg ex-bulto 20kg, IVA incl',33,249,9,1.000000,NULL),(101,'DISPERSANTE',NULL,'Materia Prima',5546.22,6600.00,1,'kg ex-bulto 25kg, IVA incl',33,231,9,1.000000,NULL),(102,'OMYACARB UF',NULL,'Materia Prima',2352.94,2800.00,1,'kg ex-bulto 25kg, IVA incl',33,57,9,1.000000,NULL),(103,'TALCO TY 400',NULL,'Materia Prima',1722.69,2050.00,1,'kg ex-bulto 40kg, IVA incl',33,252,9,1.000000,NULL),(104,'CAOLINA TIZA CAOMIN',NULL,'Materia Prima',2890.76,3440.00,1,'kg ex-25kg ASUMIDO, confirmar',33,253,9,1.000000,NULL),(105,'DIOXIDO DE TITANIO',NULL,'Materia Prima',12100.84,14400.00,1,'kg ex-bulto 25kg, IVA incl',33,37,9,1.000000,NULL),(106,'HUMECTANTE',NULL,'Materia Prima',12184.87,14500.00,1,'kg ex-bulto 20kg, IVA incl',33,342,9,1.000000,NULL),(107,'HIDROFUGANTE MATE',NULL,'Materia Prima',10084.03,12000.00,1,'kg ex-cunete 20kg, IVA incl',33,343,9,1.000000,NULL),(108,'HIDROFUGANTE BRILLANTE',NULL,'Materia Prima',11764.71,14000.00,1,'kg ex-cunete 20kg, IVA incl',33,344,9,1.000000,NULL),(109,'BIOCIDA DA PLUS',NULL,'Materia Prima',9243.70,11000.00,1,'kg ex-bulto 20kg, IVA incl',33,345,9,1.000000,NULL),(110,'BIOCIDA DA ULTRA',NULL,'Materia Prima',14285.71,17000.00,1,'kg ex-bulto 20kg, IVA incl',33,346,9,1.000000,NULL),(111,'OMYACARB 15',NULL,'Materia Prima',571.43,680.00,1,'kg ex-bulto 25kg, IVA incl',33,347,9,1.000000,NULL),(112,'OMYACARB 4',NULL,'Materia Prima',840.34,1000.00,1,'kg ex-bulto 25kg, IVA incl',33,348,9,1.000000,NULL),(113,'TALCO EXTRA',NULL,'Materia Prima',1323.53,1575.00,1,'kg ex-bulto 40kg, IVA incl',33,349,9,1.000000,NULL),(114,'TALCO EXTRA MEJORADO',NULL,'Materia Prima',1575.63,1875.00,1,'kg ex-bulto 40kg, IVA incl',33,350,9,1.000000,NULL);
/*!40000 ALTER TABLE `item_proveedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_sync_auditoria`
--

DROP TABLE IF EXISTS `item_sync_auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_sync_auditoria` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cluster_id` int unsigned DEFAULT NULL COMMENT 'Cluster que originó la fusión (null si merge manual suelto).',
  `keep_id` int NOT NULL,
  `remove_id` int NOT NULL,
  `nombre_keep_antes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre_keep_despues` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre_remove_original` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `costo_keep_antes` decimal(15,4) DEFAULT NULL,
  `costo_keep_despues` decimal(15,4) DEFAULT NULL,
  `afectados` json DEFAULT NULL COMMENT 'Conteo de filas reapuntadas por tabla.',
  `detalle_movimientos` json DEFAULT NULL COMMENT 'IDs movidos (item_proveedor, inventario_capas) para UNDO parcial.',
  `responsable` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `revertido` tinyint(1) NOT NULL DEFAULT '0',
  `revertido_at` datetime DEFAULT NULL,
  `revertido_por` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keep_id` (`keep_id`),
  KEY `cluster_id` (`cluster_id`),
  KEY `revertido` (`revertido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_sync_auditoria`
--

LOCK TABLES `item_sync_auditoria` WRITE;
/*!40000 ALTER TABLE `item_sync_auditoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `item_sync_auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_sync_cluster_items`
--

DROP TABLE IF EXISTS `item_sync_cluster_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_sync_cluster_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cluster_id` int unsigned NOT NULL,
  `item_general_id` int NOT NULL,
  `rol` enum('keep','merge','excluido') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'merge',
  `confianza_item` enum('alta','media','baja') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'media',
  `motivo_revision` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Ej. "verificar con ficha técnica" cuando baja confianza.',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cluster_id_item_general_id` (`cluster_id`,`item_general_id`),
  KEY `item_general_id` (`item_general_id`),
  CONSTRAINT `item_sync_cluster_items_cluster_id_foreign` FOREIGN KEY (`cluster_id`) REFERENCES `item_sync_clusters` (`id_cluster`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_sync_cluster_items`
--

LOCK TABLES `item_sync_cluster_items` WRITE;
/*!40000 ALTER TABLE `item_sync_cluster_items` DISABLE KEYS */;
INSERT INTO `item_sync_cluster_items` VALUES (70,27,249,'keep','alta',NULL,'2026-06-01 21:36:07'),(71,27,272,'merge','alta',NULL,'2026-06-01 21:36:07'),(72,28,256,'keep','alta',NULL,'2026-06-01 21:36:07'),(73,28,345,'merge','alta',NULL,'2026-06-01 21:36:07'),(74,28,346,'merge','alta',NULL,'2026-06-01 21:36:07'),(75,29,39,'keep','alta',NULL,'2026-06-01 21:36:07'),(76,29,95,'merge','alta',NULL,'2026-06-01 21:36:07'),(77,30,261,'keep','alta',NULL,'2026-06-01 21:36:07'),(78,30,245,'merge','alta',NULL,'2026-06-01 21:36:07'),(79,31,271,'keep','alta',NULL,'2026-06-01 21:36:07'),(80,31,339,'merge','alta',NULL,'2026-06-01 21:36:07'),(81,32,89,'merge','alta',NULL,'2026-06-01 21:36:07'),(82,32,36,'keep','alta',NULL,'2026-06-01 21:36:07'),(83,32,83,'merge','alta',NULL,'2026-06-01 21:36:07'),(84,33,87,'keep','alta',NULL,'2026-06-01 21:36:07'),(85,33,90,'merge','alta',NULL,'2026-06-01 21:36:07'),(86,34,338,'merge','alta',NULL,'2026-06-01 21:36:07'),(87,34,69,'merge','alta',NULL,'2026-06-01 21:36:07'),(88,34,97,'keep','alta',NULL,'2026-06-01 21:36:07'),(89,35,335,'merge','alta',NULL,'2026-06-01 21:36:07'),(90,35,258,'keep','alta',NULL,'2026-06-01 21:36:07'),(91,36,88,'merge','alta',NULL,'2026-06-01 21:36:07'),(92,36,40,'keep','alta',NULL,'2026-06-01 21:36:07'),(93,37,65,'merge','alta',NULL,'2026-06-01 21:36:07'),(94,37,59,'keep','alta',NULL,'2026-06-01 21:36:07'),(95,38,92,'merge','alta',NULL,'2026-06-01 21:36:07'),(96,38,38,'keep','alta',NULL,'2026-06-01 21:36:07'),(97,39,64,'keep','alta',NULL,'2026-06-01 21:36:07'),(98,39,100,'merge','alta',NULL,'2026-06-01 21:36:07'),(99,40,337,'keep','media','Marca o grado específico, verificar equivalencia funcional con otras resinas acrílicas.','2026-06-01 21:36:07'),(100,40,141,'merge','media','Marca o grado específico, verificar equivalencia funcional con otras resinas acrílicas.','2026-06-01 21:36:07'),(101,41,179,'merge','alta',NULL,'2026-06-01 21:36:07'),(102,41,226,'keep','alta',NULL,'2026-06-01 21:36:07'),(103,42,158,'merge','alta',NULL,'2026-06-01 21:36:07'),(104,42,163,'keep','alta',NULL,'2026-06-01 21:36:07'),(105,43,168,'keep','media','Nombres de producto ligeramente diferentes, verificar si son el mismo material funcional.','2026-06-01 21:36:07'),(106,43,167,'merge','media','Nombres de producto ligeramente diferentes, verificar si son el mismo material funcional.','2026-06-01 21:36:07'),(107,44,214,'keep','alta',NULL,'2026-06-01 21:36:07'),(108,44,184,'merge','alta',NULL,'2026-06-01 21:36:07'),(109,45,219,'keep','alta',NULL,'2026-06-01 21:36:07'),(110,45,201,'merge','alta',NULL,'2026-06-01 21:36:07'),(111,46,349,'keep','media','Verificar si \'Extra\' y \'Extra Mejorado\' son funcionalmente equivalentes o si el \'mejorado\' implica un cambio de grado relevante.','2026-06-01 21:36:07'),(112,46,350,'merge','media','Verificar si \'Extra\' y \'Extra Mejorado\' son funcionalmente equivalentes o si el \'mejorado\' implica un cambio de grado relevante.','2026-06-01 21:36:07'),(113,47,206,'merge','alta',NULL,'2026-06-01 21:36:07'),(114,47,166,'keep','alta',NULL,'2026-06-01 21:36:07'),(115,48,52,'keep','alta',NULL,'2026-06-01 21:36:07'),(116,48,93,'merge','alta',NULL,'2026-06-01 21:36:07'),(117,49,96,'merge','alta',NULL,'2026-06-01 21:36:07'),(118,49,56,'keep','alta',NULL,'2026-06-01 21:36:07');
/*!40000 ALTER TABLE `item_sync_cluster_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_sync_clusters`
--

DROP TABLE IF EXISTS `item_sync_clusters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_sync_clusters` (
  `id_cluster` int unsigned NOT NULL AUTO_INCREMENT,
  `clave_grupo` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Slug de identidad química (ej. dioxido-titanio-rutilo).',
  `identidad_quimica` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nombre químico canónico ("Dióxido de titanio rutilo").',
  `nombre_base_propuesto` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nombre base limpio que sugiere la IA para el keep.',
  `nombre_base_aprobado` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nombre base que fija el humano (gana sobre el propuesto).',
  `confianza` enum('alta','media','baja') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'media',
  `razonamiento` text COLLATE utf8mb4_general_ci COMMENT 'Explicación de la IA: por qué son el mismo material.',
  `tipo` tinyint NOT NULL COMMENT '1=Materia Prima, 2=Insumo. Un cluster no mezcla tipos.',
  `estado` enum('propuesto','revisado','aprobado','fusionado','descartado') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'propuesto',
  `keep_id_sugerido` int DEFAULT NULL COMMENT 'item_general que la IA sugiere conservar.',
  `keep_id_aprobado` int DEFAULT NULL COMMENT 'item_general que el humano confirma conservar.',
  `lote_ia` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Id de la corrida de clasificación (re-correr sin pisar).',
  `modelo_ia` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aprobado_por` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fusionado_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cluster`),
  KEY `estado` (`estado`),
  KEY `lote_ia` (`lote_ia`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_sync_clusters`
--

LOCK TABLES `item_sync_clusters` WRITE;
/*!40000 ALTER TABLE `item_sync_clusters` DISABLE KEYS */;
INSERT INTO `item_sync_clusters` VALUES (27,'antiespumante','Antiespumante','Antiespumante',NULL,'alta','Ambos son agentes antiespumantes de uso general, uno genérico y otro de marca específica. El \'Antiespumante Epoxicos\' (ID 340) se considera funcionalmente distinto por su aplicación específica.',1,'propuesto',249,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(28,'biocida','Biocida','Biocida',NULL,'alta','Bactericida es un tipo de biocida. Los ítems \'BIOCIDA DA PLUS\' y \'BIOCIDA DA ULTRA\' son productos biocidas de marca. El \'Fungicida\' (ID 259) se mantiene separado por ser una función más específica.',1,'propuesto',256,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(29,'bentoclay-bp-184','Bentoclay BP 184','Bentoclay BP 184',NULL,'alta','Mismo producto, solo diferencia de espacio en el nombre.',1,'propuesto',39,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(30,'butil-glicol','2-Butoxietanol','Butil Glicol',NULL,'alta','Butil Cellosolve es un nombre comercial común para Butil Glicol (2-Butoxietanol).',1,'propuesto',261,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(31,'nivelante','Aditivo Nivelante','Nivelante',NULL,'alta','Ambos son aditivos nivelantes, uno de marca específica (BYK 066N) y otro genérico que incluye una referencia de marca (SILOEN LA 271).',1,'propuesto',271,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(32,'disolvente-2232-varsol','Mezcla de hidrocarburos alifáticos (tipo Varsol)','Disolvente 2232 / Varsol',NULL,'alta','Variantes del mismo disolvente, con o sin referencia a Varsol o un número de lote/grado. Se asume que \'2232 #3\' es una variante del \'2232\'.',1,'propuesto',36,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(33,'disolvente-3','Disolvente #3','Disolvente #3',NULL,'alta','Mismo disolvente, solo diferencia de formato en el nombre.',1,'propuesto',87,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(34,'dispersante-polimerico-base-solvente','Dispersante polimérico para sistemas base solvente','Dispersante polimérico base solvente',NULL,'alta','EDAPLAN 918 y LANSPERSE SUV son nombres comerciales para el mismo tipo de dispersante base solvente. El ítem \'DISPERSANTE BASE SOLVENTE\' es una descripción genérica que coincide.',1,'propuesto',97,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(35,'espesante-acrilico-asociativo','Espesante acrílico asociativo','Espesante acrílico asociativo',NULL,'alta','HISOL Asociativo es un nombre comercial para un espesante acrílico asociativo, funcionalmente idéntico al \'ESPESANTE ACRILICO ASOCIATIVO\' genérico.',1,'propuesto',258,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(36,'etanol-96','Etanol 96%','Etanol 96%',NULL,'alta','Mismo producto, solo diferencia de formato en el nombre.',1,'propuesto',40,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(37,'microtalco-20','Talc (grado 20)','Microtalco 20',NULL,'alta','Mismo grado de microtalco, \'C\' es una variación menor en el nombre que no indica una diferencia funcional significativa.',1,'propuesto',59,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(38,'octoato-zinc-16','Octoato de Zinc 16%','Octoato de Zinc 16%',NULL,'alta','Mismo producto, solo diferencia de formato en el nombre.',1,'propuesto',38,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(39,'oxido-hierro-rojo-r5530','Óxido de Hierro Rojo (CI Pigment Red 101) grado R-5530','Óxido de Hierro Rojo R-5530',NULL,'alta','Ambos ítems se refieren al mismo pigmento de óxido de hierro rojo con la misma referencia de grado R-5530.',1,'propuesto',64,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(40,'resina-acrilica','Resina Acrílica para recubrimientos','Resina Acrílica',NULL,'media','Ambos son descritos como resinas acrílicas. \'Exteriores\' y \'Masflex\' podrían indicar grados o marcas distintas, pero funcionalmente son resinas acrílicas. Se recomienda verificar fichas técnicas para confirmar equivalencia de propiedades.',1,'propuesto',337,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(41,'resina-epoxica','Resina Epóxica','Resina Epóxica',NULL,'alta','Ambos ítems describen resinas epóxicas. \'BEA\' es una marca, pero la identidad química funcional es la misma.',1,'propuesto',226,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(42,'resina-poliuretano','Resina Poliuretano','Resina Poliuretano',NULL,'alta','Ambos ítems se refieren a resinas de poliuretano. \'VDE\' es una referencia de grado o marca.',1,'propuesto',163,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(43,'aditivo-sika-169','Aditivo Sika serie 169','Aditivo Sika 169',NULL,'media','Ambos productos son de la marca Sika y comparten la referencia \'169\'. \'Fluid\' y \'Play\' podrían indicar variaciones en la formulación o aplicación, pero es probable que sean el mismo material base o variantes muy cercanas. Se recomienda verificar fichas técnicas.',1,'propuesto',168,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(44,'sikaplast-revolver','Aditivo Sikaplast Revolver','Sikaplast Revolver',NULL,'alta','Ambos son productos \'Sikaplast Revolver\'. La adición de \'CON AMOLRED\' en uno es una especificación o variante del mismo producto base.',1,'propuesto',214,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(45,'splander','Aditivo Splander','Splander',NULL,'alta','Ambos ítems se refieren al producto \'Splander\'. \'PHILAC\' es una marca o proveedor adicional.',1,'propuesto',219,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(46,'talco-extra','Talco industrial grado extra','Talco Extra',NULL,'media','Ambos son talco grado \'Extra\'. La denominación \'MEJORADO\' en uno podría indicar una diferencia en propiedades (ej. granulometría, blancura) que afecte su funcionalidad en ciertas aplicaciones, aunque la base química es la misma. Se recomienda verificar fichas técnicas.',1,'propuesto',349,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(47,'solvente-xloc-philac','Solvente XLOC Philac','Solvente XLOC Philac',NULL,'alta','Ambos ítems se refieren al mismo producto \'XLOC PHILAC\'. Uno especifica \'SOLVENTE\', lo cual es la función del material.',1,'propuesto',166,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(48,'pasta-amarillo-cromo-medio','Pasta de pigmento Amarillo Cromo (CI Pigment Yellow 34) tono medio','Pasta Pigmento Amarillo Cromo Medio',NULL,'alta','Ambos ítems describen una pasta de pigmento amarillo cromo de tono medio. La adición de \'ESMALTE\' en uno especifica su uso, pero no cambia la identidad química funcional de la pasta.',2,'propuesto',52,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07'),(49,'pasta-azul-ftalo-15-3','Pasta de pigmento Azul Ftalocianina (CI Pigment Blue 15:3)','Pasta Pigmento Azul Ftalocianina 15:3',NULL,'alta','Ambos ítems describen la misma pasta de pigmento azul ftalocianina 15:3 para esmaltes.',2,'propuesto',56,NULL,'IA-20260601213435','gemini:gemini-2.5-flash',NULL,NULL,'2026-06-01 21:36:07','2026-06-01 21:36:07');
/*!40000 ALTER TABLE `item_sync_clusters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username_attempt` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_fecha` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=227 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026-04-17-000001','App\\Database\\Migrations\\CreateTamboresTable','default','App',1776779676,1),(2,'2026-04-21-000001','App\\Database\\Migrations\\CreateRequisicionesCompraTable','default','App',1776779681,2),(3,'2026-04-21-000002','App\\Database\\Migrations\\AddUnidadBaseAndItemProveedorCompra','default','App',1776799102,3),(4,'2026-04-22-000001','App\\Database\\Migrations\\MergeUnidadEmpaqueIntoUnidadCompra','default','App',1777059242,4),(5,'2026-04-23-000001','App\\Database\\Migrations\\CreateInventarioCapasSystem','default','App',1777059242,4),(6,'2026-04-24-000001','App\\Database\\Migrations\\CreateProduccionInsumosDetalle','default','App',1777059242,4),(7,'2026-05-11-000001','App\\Database\\Migrations\\AddRolToUsuarios','default','App',1778543978,5),(8,'2026-05-11-000002','App\\Database\\Migrations\\CreatePermisosRolModulo','default','App',1778543978,5),(9,'2026-05-11-000003','App\\Database\\Migrations\\CreateLoginAttempts','default','App',1778543998,6),(10,'2026-05-13-000001','App\\Database\\Migrations\\ExtendMovimientoInventario','default','App',1778685332,7),(11,'2026-05-13-000002','App\\Database\\Migrations\\AddNombreToUsuarios','default','App',1778689057,8),(12,'2026-05-13-000003','App\\Database\\Migrations\\AddLoteProveedorToProduccionInsumos','default','App',1778689809,9),(13,'2026-05-13-000004','App\\Database\\Migrations\\CreateFormulacionesVersiones','default','App',1778690919,10),(14,'2026-05-13-000005','App\\Database\\Migrations\\CreateNotificaciones','default','App',1778691884,11),(15,'2026-05-13-000006','App\\Database\\Migrations\\AddSugeridaToRequisiciones','default','App',1778692918,12),(17,'2026-05-13-000007','App\\Database\\Migrations\\AddSoftDeletes','default','App',1778701102,14),(57,'2026-05-14-000001','App\\Database\\Migrations\\AddSoftDeleteToItemProveedor','default','App',1779725132,16),(58,'2026-05-14-000002','App\\Database\\Migrations\\CreateConfiguracionSistema','default','App',1779725132,16),(59,'2026-05-14-000003','App\\Database\\Migrations\\SeedConfiguracionTributaria','default','App',1779725132,16),(60,'2026-05-14-000004','App\\Database\\Migrations\\SeedConfiguracionUmbrales','default','App',1779725132,16),(61,'2026-05-14-000005','App\\Database\\Migrations\\CreateNumeracionDocumentos','default','App',1779725132,16),(62,'2026-05-14-000006','App\\Database\\Migrations\\ExtendEmpresa','default','App',1779725132,16),(63,'2026-05-14-000007','App\\Database\\Migrations\\SeedConfiguracionSeguridadFinanciero','default','App',1779725132,16),(64,'2026-05-14-000008','App\\Database\\Migrations\\SeedAvatarPalette','default','App',1779725132,16),(65,'2026-05-14-000009','App\\Database\\Migrations\\SeedDefaultLogo','default','App',1779725132,16),(66,'2026-05-15-000001','App\\Database\\Migrations\\AddTrazabilidadModulo','default','App',1779725132,16),(67,'2026-05-15-000002','App\\Database\\Migrations\\AddCostosModulos','default','App',1779725132,16),(68,'2026-05-15-000003','App\\Database\\Migrations\\RemoveCostosIndirectosModulo','default','App',1779725132,16),(69,'2026-05-19-000001','App\\Database\\Migrations\\AddIvaPctToOrdenesCompra','default','App',1779725132,16),(70,'2026-05-19-000002','App\\Database\\Migrations\\MergeCostosIntoRentabilidad','default','App',1779725132,16),(71,'2026-05-19-000003','App\\Database\\Migrations\\AddCostosProduccionModulo','default','App',1779725132,16),(72,'2026-05-20-000001','App\\Database\\Migrations\\AddSaludSistemaModulo','default','App',1779725132,16),(73,'2026-05-20-000002','App\\Database\\Migrations\\CreateCostosSnapshot','default','App',1779725132,16),(74,'2026-05-20-000003','App\\Database\\Migrations\\AddSuperadminRol','default','App',1779725132,16),(75,'2026-05-21-000001','App\\Database\\Migrations\\AddTokenVersionToUsuarios','default','App',1779725132,16),(76,'2026-05-25-000001','App\\Database\\Migrations\\CreateRefreshTokens','default','App',1779898393,17),(77,'2026-05-25-000002','App\\Database\\Migrations\\DropTamboresTable','default','App',1779898393,17),(78,'2026-05-27-000001','App\\Database\\Migrations\\AddSoftDeleteToBasicEntities','default','App',1780074079,18),(81,'2026-06-01-000001','App\\Database\\Migrations\\CreateItemSyncSugerencias','default','App',1780345207,19),(82,'2026-06-01-000002','App\\Database\\Migrations\\CreateItemSyncAuditoria','default','App',1780345207,19),(83,'2026-06-01-000003','App\\Database\\Migrations\\WidenItemGeneralFichaTecnica','default','App',1780433241,20),(84,'2026-06-03-000001','App\\Database\\Migrations\\AddMissingForeignKeys','default','App',1780497144,21),(85,'2020-02-22-222222','Tests\\Support\\Database\\Migrations\\ExampleMigration','tests','Tests\\Support',1781296784,22),(87,'2026-05-13-000008','App\\Database\\Migrations\\RemisionesStockTracking','default','App',1781298189,23);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimiento_inventario`
--

DROP TABLE IF EXISTS `movimiento_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimiento_inventario` (
  `id_movimiento_inventario` int NOT NULL AUTO_INCREMENT,
  `tipo_movimiento` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `fecha_movimiento` datetime DEFAULT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `referencia_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_general_id` int DEFAULT NULL COMMENT 'ID del producto o materia prima afectada',
  `bodega_id` int DEFAULT NULL COMMENT 'ID de la bodega donde ocurrió el movimiento',
  `referencia_id` int DEFAULT NULL COMMENT 'ID de la tabla origen (ej: ID de la Orden, Factura o Traspaso)',
  `costo_unitario` decimal(15,2) DEFAULT NULL COMMENT 'Costo unitario en el instante exacto del movimiento',
  `saldo_anterior` decimal(15,2) DEFAULT NULL COMMENT 'Cantidad en bodega antes del movimiento',
  `saldo_nuevo` decimal(15,2) DEFAULT NULL COMMENT 'Cantidad en bodega después del movimiento',
  `responsable` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nombre de la persona responsable del movimiento',
  `metadata` json DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_movimiento_inventario`),
  UNIQUE KEY `id_movimiento_inventario_UNIQUE` (`id_movimiento_inventario`),
  KEY `fk_movimiento_item` (`item_general_id`),
  KEY `fk_movimiento_bodega` (`bodega_id`),
  CONSTRAINT `fk_movimiento_bodega` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id_bodegas`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_movimiento_item` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimiento_inventario`
--

LOCK TABLES `movimiento_inventario` WRITE;
/*!40000 ALTER TABLE `movimiento_inventario` DISABLE KEYS */;
INSERT INTO `movimiento_inventario` VALUES (36,'ENTRADA',1000.00,'2026-05-16 15:55:15','Recepción OC #OC-001 línea 47','ORDEN_COMPRA',300,1,36,5040.00,0.00,1000.00,'root','{\"numero_oc\": \"OC-001\", \"proveedor_id\": \"31\", \"unidad_compra\": \"9\", \"lote_proveedor\": \"LT-2026-A0450\", \"factor_conversion\": 1, \"item_proveedor_id\": \"78\", \"precio_unit_compra\": 5040, \"item_proveedor_nombre\": \"PVA RESIFLEX 610\", \"cantidad_recibida_unidad_compra\": 1000}','2026-05-16 15:55:15'),(50,'ENTRADA',200.00,'2026-05-17 01:12:21','Recepción OC #OC-001 línea 48','ORDEN_COMPRA',301,1,36,7620.00,0.00,200.00,'root','{\"numero_oc\": \"OC-001\", \"proveedor_id\": \"31\", \"unidad_compra\": \"9\", \"lote_proveedor\": \"LT-2026-A0450\", \"factor_conversion\": 1, \"item_proveedor_id\": \"79\", \"precio_unit_compra\": 7620, \"item_proveedor_nombre\": \"PVA RESIFLEX F55M\", \"cantidad_recibida_unidad_compra\": 200}','2026-05-17 01:12:21'),(51,'AJUSTE',5.00,'2026-05-18 20:06:26','Ajuste manual: removido de bodega Bodega principal','AJUSTE_MANUAL',133,1,NULL,NULL,5.00,0.00,'root','{\"accion\": \"remove_from_bodega\", \"motivo\": null, \"bodega_id\": 1, \"bodega_nombre\": \"Bodega principal\", \"cantidad_removida\": 5}','2026-05-18 20:06:26'),(52,'ENTRADA',10.00,'2026-05-25 14:59:54','Recepción OC #OC-020 línea 53','ORDEN_COMPRA',230,1,39,5000.00,0.00,10.00,'root','{\"numero_oc\": \"OC-020\", \"proveedor_id\": \"23\", \"unidad_compra\": null, \"lote_proveedor\": \"TEST-LOTE-6a1463ead3dd0\", \"factor_conversion\": 1, \"item_proveedor_id\": \"46\", \"precio_unit_compra\": 5000, \"item_proveedor_nombre\": \"DISPERSANTE\", \"cantidad_recibida_unidad_compra\": 10}','2026-05-25 14:59:54'),(53,'SALIDA',1.50,'2026-05-25 14:59:56','Despacho remisión REM-2026-0001 línea 17','REMISION',1,1,18,0.00,11.00,9.50,'root','{\"cliente_id\": \"1\", \"detalle_id\": 17, \"descripcion\": \"Test despacho\", \"remision_id\": 18, \"remision_numero\": \"REM-2026-0001\", \"capas_consumidas\": 1}','2026-05-25 14:59:56'),(54,'ENTRADA',1.50,'2026-05-25 14:59:57','Anulación remisión REM-2026-0001 (reintegro de stock)','ANULACION',1,NULL,18,0.00,9.50,11.00,'root','{\"remision_id\": 18, \"origen_estado\": \"Despachada\", \"remision_numero\": \"REM-2026-0001\", \"capas_restauradas\": 1}','2026-05-25 14:59:57'),(55,'ENTRADA',10.00,'2026-05-25 16:04:45','Recepción OC #OC-022 línea 55','ORDEN_COMPRA',230,1,41,5000.00,10.00,20.00,'root','{\"numero_oc\": \"OC-022\", \"proveedor_id\": \"23\", \"unidad_compra\": null, \"lote_proveedor\": \"TEST-LOTE-6a14731da95e8\", \"factor_conversion\": 1, \"item_proveedor_id\": \"46\", \"precio_unit_compra\": 5000, \"item_proveedor_nombre\": \"DISPERSANTE\", \"cantidad_recibida_unidad_compra\": 10}','2026-05-25 16:04:45'),(56,'SALIDA',1.50,'2026-05-25 16:04:48','Despacho remisión REM-2026-0003 línea 19','REMISION',1,1,20,0.00,9.50,8.00,'root','{\"cliente_id\": \"1\", \"detalle_id\": 19, \"descripcion\": \"Test despacho\", \"remision_id\": 20, \"remision_numero\": \"REM-2026-0003\", \"capas_consumidas\": 1}','2026-05-25 16:04:48'),(57,'ENTRADA',1.50,'2026-05-25 16:04:48','Anulación remisión REM-2026-0003 (reintegro de stock)','ANULACION',1,NULL,20,0.00,9.50,11.00,'root','{\"remision_id\": 20, \"origen_estado\": \"Despachada\", \"remision_numero\": \"REM-2026-0003\", \"capas_restauradas\": 1}','2026-05-25 16:04:48'),(58,'ENTRADA',1000.00,'2026-05-27 20:42:54','Recepción OC #OC-002 línea 49','ORDEN_COMPRA',300,1,37,5040.00,1000.00,2000.00,'root','{\"numero_oc\": \"OC-002\", \"proveedor_id\": \"31\", \"unidad_compra\": \"9\", \"lote_proveedor\": \"LOT-OC37-20260527\", \"factor_conversion\": 1, \"item_proveedor_id\": \"78\", \"precio_unit_compra\": 5040, \"item_proveedor_nombre\": \"PVA RESIFLEX 610\", \"cantidad_recibida_unidad_compra\": 1000}','2026-05-27 20:42:54'),(59,'ENTRADA',1000.00,'2026-05-27 21:00:27','Recepción OC #OC-002 línea 49','ORDEN_COMPRA',300,1,37,5040.00,1000.00,2000.00,'root','{\"numero_oc\": \"OC-002\", \"proveedor_id\": \"31\", \"unidad_compra\": \"9\", \"lote_proveedor\": \"LOT-OC37-20260527\", \"factor_conversion\": 1, \"item_proveedor_id\": \"78\", \"precio_unit_compra\": 5040, \"item_proveedor_nombre\": \"PVA RESIFLEX 610\", \"cantidad_recibida_unidad_compra\": 1000}','2026-05-27 21:00:27'),(60,'SALIDA',1.50,'2026-06-12 20:39:46','Despacho remisión REM-2026-0005 línea 21','REMISION',137,21,22,0.00,7.00,5.50,'root','{\"cliente_id\": \"1\", \"detalle_id\": 21, \"descripcion\": \"Test despacho\", \"remision_id\": 22, \"remision_numero\": \"REM-2026-0005\", \"capas_consumidas\": 1}','2026-06-12 20:39:46'),(61,'SALIDA',1.50,'2026-06-12 20:41:53','Despacho remisión REM-2026-0007 línea 23','REMISION',137,21,24,0.00,5.50,4.00,'root','{\"cliente_id\": \"1\", \"detalle_id\": 23, \"descripcion\": \"Test despacho\", \"remision_id\": 24, \"remision_numero\": \"REM-2026-0007\", \"capas_consumidas\": 1}','2026-06-12 20:41:53'),(62,'ENTRADA',10.00,'2026-06-12 20:44:12','Recepción OC #OC-004 línea 60','ORDEN_COMPRA',37,1,46,5000.00,0.00,10.00,'root','{\"numero_oc\": \"OC-004\", \"proveedor_id\": \"23\", \"unidad_compra\": \"9\", \"lote_proveedor\": \"TEST-LOTE-6a2c6f9c9c651\", \"factor_conversion\": 1, \"item_proveedor_id\": \"105\", \"precio_unit_compra\": 5000, \"item_proveedor_nombre\": \"DIOXIDO DE TITANIO\", \"cantidad_recibida_unidad_compra\": 10}','2026-06-12 20:44:12'),(63,'SALIDA',1.50,'2026-06-12 20:57:04','Despacho remisión REM-2026-0009 línea 25','REMISION',37,1,26,5000.00,10.00,8.50,'root','{\"cliente_id\": \"1\", \"detalle_id\": 25, \"descripcion\": \"Test despacho\", \"remision_id\": 26, \"remision_numero\": \"REM-2026-0009\", \"capas_consumidas\": 1}','2026-06-12 20:57:04'),(64,'ENTRADA',1.50,'2026-06-12 20:57:04','Anulación remisión REM-2026-0009 (reintegro de stock)','ANULACION',37,NULL,26,5000.00,8.50,10.00,'root','{\"remision_id\": 26, \"origen_estado\": \"Despachada\", \"remision_numero\": \"REM-2026-0009\", \"capas_restauradas\": 1}','2026-06-12 20:57:04'),(65,'SALIDA',1.50,'2026-06-12 20:59:24','Despacho remisión REM-2026-0011 línea 27','REMISION',37,1,28,5000.00,8.50,7.00,'root','{\"cliente_id\": \"1\", \"detalle_id\": 27, \"descripcion\": \"Test despacho\", \"remision_id\": 28, \"remision_numero\": \"REM-2026-0011\", \"capas_consumidas\": 1}','2026-06-12 20:59:24'),(66,'ENTRADA',1.50,'2026-06-12 20:59:24','Anulación remisión REM-2026-0011 (reintegro de stock)','ANULACION',37,NULL,28,5000.00,8.50,10.00,'root','{\"remision_id\": 28, \"origen_estado\": \"Despachada\", \"remision_numero\": \"REM-2026-0011\", \"capas_restauradas\": 1}','2026-06-12 20:59:24'),(67,'SALIDA',1.50,'2026-06-12 20:59:53','Despacho remisión REM-2026-0013 línea 29','REMISION',37,1,30,5000.00,7.00,5.50,'root','{\"cliente_id\": \"1\", \"detalle_id\": 29, \"descripcion\": \"Test despacho\", \"remision_id\": 30, \"remision_numero\": \"REM-2026-0013\", \"capas_consumidas\": 1}','2026-06-12 20:59:53'),(68,'ENTRADA',1.50,'2026-06-12 20:59:54','Anulación remisión REM-2026-0013 (reintegro de stock)','ANULACION',37,NULL,30,5000.00,8.50,10.00,'root','{\"remision_id\": 30, \"origen_estado\": \"Despachada\", \"remision_numero\": \"REM-2026-0013\", \"capas_restauradas\": 1}','2026-06-12 20:59:54');
/*!40000 ALTER TABLE `movimiento_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas_credito`
--

DROP TABLE IF EXISTS `notas_credito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notas_credito` (
  `id_nota_credito` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `facturas_id` int NOT NULL,
  `clientes_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `estado` enum('Activa','Anulada') DEFAULT 'Activa',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nota_credito`),
  UNIQUE KEY `numero` (`numero`),
  KEY `fk_notas_factura` (`facturas_id`),
  KEY `fk_notas_cliente` (`clientes_id`),
  KEY `fk_notas_usuario` (`usuario_id`),
  CONSTRAINT `fk_notas_cliente` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`id_clientes`),
  CONSTRAINT `fk_notas_factura` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`),
  CONSTRAINT `fk_notas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas_credito`
--

LOCK TABLES `notas_credito` WRITE;
/*!40000 ALTER TABLE `notas_credito` DISABLE KEYS */;
/*!40000 ALTER TABLE `notas_credito` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `rol_target` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `titulo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT '0',
  `leida_at` datetime DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_leida` (`user_id`,`leida`),
  KEY `idx_notif_rol_leida` (`rol_target`,`leida`),
  KEY `idx_notif_tipo` (`tipo`),
  KEY `idx_notif_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,NULL,'admin','factura_vencimiento','Factura FAC-20 en mora','Distribuidora Andina S.A.S · 154 días vencida · saldo $125.000','/cartera',1,'2026-05-16 19:56:12','{\"saldo\": 125000, \"dedup_key\": \"factura-mora-1-2026-05-15\", \"dias_mora\": 154, \"id_facturas\": 1}','2026-05-15 19:11:42');
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `numeracion_documentos`
--

DROP TABLE IF EXISTS `numeracion_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `numeracion_documentos` (
  `id_numeracion` int unsigned NOT NULL AUTO_INCREMENT,
  `tipo_doc` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `prefijo` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `padding` tinyint unsigned NOT NULL DEFAULT '4',
  `proximo_numero` int unsigned NOT NULL DEFAULT '1',
  `anio_actual` smallint unsigned DEFAULT NULL,
  `reinicia_anual` tinyint NOT NULL DEFAULT '1',
  `resolucion_dian` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_resolucion` date DEFAULT NULL,
  `rango_min` int unsigned DEFAULT NULL,
  `rango_max` int unsigned DEFAULT NULL,
  `fecha_vigencia_hasta` date DEFAULT NULL,
  `activo` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_numeracion`),
  KEY `tipo_doc_activo` (`tipo_doc`,`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `numeracion_documentos`
--

LOCK TABLES `numeracion_documentos` WRITE;
/*!40000 ALTER TABLE `numeracion_documentos` DISABLE KEYS */;
INSERT INTO `numeracion_documentos` VALUES (1,'factura','FAC-{Y}-',4,1,2026,1,NULL,NULL,NULL,NULL,NULL,1,'2026-05-25 16:05:32','2026-05-25 16:05:32','migration'),(2,'cotizacion','COT-{Y}-',4,1,2026,1,NULL,NULL,NULL,NULL,NULL,1,'2026-05-25 16:05:32','2026-05-25 16:05:32','migration'),(3,'remision','REM-{Y}-',4,17,2026,1,NULL,NULL,NULL,NULL,NULL,1,'2026-05-25 16:05:32','2026-06-12 21:00:23','migration'),(4,'orden_compra','OC-',3,6,NULL,0,NULL,NULL,NULL,NULL,NULL,1,'2026-05-25 16:05:32','2026-06-12 20:44:13','migration'),(5,'nota_credito','NC-',3,1,NULL,0,NULL,NULL,NULL,NULL,NULL,1,'2026-05-25 16:05:32','2026-05-25 16:05:32','migration');
/*!40000 ALTER TABLE `numeracion_documentos` ENABLE KEYS */;
UNLOCK TABLES;

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
  `iva_pct` decimal(5,2) DEFAULT NULL COMMENT 'Porcentaje de IVA aplicado a la OC al momento de su cierre. NULL = legacy sin IVA.',
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
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_compra`
--

LOCK TABLES `ordenes_compra` WRITE;
/*!40000 ALTER TABLE `ordenes_compra` DISABLE KEYS */;
INSERT INTO `ordenes_compra` VALUES (36,'OC-001',31,1,'2026-05-07','2026-05-07','Recibida',6564000.00,19.00,'','2026-05-16 15:50:09',NULL),(37,'OC-002',31,1,'2026-05-15','2026-05-18','Recibida',5040000.00,19.00,'','2026-05-16 15:52:32',NULL),(45,'OC-003',30,1,'2026-05-27','2026-06-03','Enviada',1094118.20,19.00,'OC de ejemplo (borrador) - 3 productos de BRENTANG','2026-05-27 20:51:05',NULL),(46,'OC-004',23,1,'2026-06-12','2026-06-17','Recibida',50000.00,19.00,'Test integración OC','2026-06-12 20:44:12',NULL),(47,'OC-005',23,1,'2026-06-12',NULL,'Borrador',5000.00,19.00,NULL,'2026-06-12 20:44:13',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_compra_detalle`
--

LOCK TABLES `ordenes_compra_detalle` WRITE;
/*!40000 ALTER TABLE `ordenes_compra_detalle` DISABLE KEYS */;
INSERT INTO `ordenes_compra_detalle` VALUES (47,36,78,300,'PVA RESIFLEX 610',1000.00,5040.00,5040000.00,1000.00,'2026-05-16 15:55:15'),(48,36,79,301,'PVA RESIFLEX F55M',200.00,7620.00,1524000.00,200.00,'2026-05-17 01:12:21'),(49,37,78,300,'PVA RESIFLEX 610',1000.00,5040.00,5040000.00,1000.00,'2026-05-27 21:00:27'),(57,45,75,225,'XILOL',100.00,5714.29,571429.00,0.00,NULL),(58,45,76,244,'ISOBUTANOL',50.00,5966.39,298319.50,0.00,NULL),(59,45,77,245,'BUTIL GLICOL',30.00,7478.99,224369.70,0.00,NULL),(60,46,105,37,'Test',10.00,5000.00,50000.00,10.00,'2026-06-12 20:44:12'),(61,47,105,NULL,NULL,5.00,1000.00,5000.00,0.00,NULL);
/*!40000 ALTER TABLE `ordenes_compra_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_cliente`
--

DROP TABLE IF EXISTS `pagos_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_cliente` (
  `id_pagos_cliente` int NOT NULL AUTO_INCREMENT,
  `fecha_pago` date DEFAULT NULL,
  `monto` decimal(7,1) DEFAULT NULL,
  `metodo_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` enum('pago_total','abono') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pago_total',
  `numero_referencia` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `observaciones` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `clientes_id` int DEFAULT NULL,
  `facturas_id` int DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id_pagos_cliente`),
  UNIQUE KEY `id_pagos_cliente_UNIQUE` (`id_pagos_cliente`),
  KEY `fk_pagos_cliente_clientes1_idx` (`clientes_id`),
  KEY `fk_pagos_cliente_facturas1_idx` (`facturas_id`),
  KEY `fk_pagos_usuario` (`usuario_id`),
  CONSTRAINT `fk_pagos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_cliente`
--

LOCK TABLES `pagos_cliente` WRITE;
/*!40000 ALTER TABLE `pagos_cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos_rol_modulo`
--

DROP TABLE IF EXISTS `permisos_rol_modulo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos_rol_modulo` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `rol` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `modulo` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `activo` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rol_modulo` (`rol`,`modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos_rol_modulo`
--

LOCK TABLES `permisos_rol_modulo` WRITE;
/*!40000 ALTER TABLE `permisos_rol_modulo` DISABLE KEYS */;
INSERT INTO `permisos_rol_modulo` VALUES (1,'admin','panel-principal',1),(2,'admin','catalogo',1),(3,'admin','inventario-global',1),(4,'admin','formulaciones',1),(5,'admin','produccion',1),(6,'admin','rentabilidad',1),(7,'admin','comercial',1),(8,'admin','compras',1),(9,'admin','cartera',1),(10,'admin','clientes',1),(11,'admin','proveedores',1),(12,'admin','movimientos',1),(13,'admin','pagos',1),(16,'admin','roles',1),(17,'operador','panel-principal',1),(18,'operador','catalogo',1),(19,'operador','inventario-global',1),(20,'operador','formulaciones',1),(21,'operador','produccion',1),(22,'operador','compras',1),(23,'operador','clientes',1),(24,'operador','proveedores',1),(25,'operador','movimientos',1),(26,'operador','pagos',1),(69,'visor','panel-principal',1),(70,'visor','catalogo',1),(71,'visor','inventario-global',1),(72,'visor','formulaciones',1),(73,'visor','rentabilidad',1),(74,'visor','comercial',1),(75,'visor','clientes',1),(76,'visor','proveedores',1),(77,'visor','movimientos',1),(115,'admin','trazabilidad',1),(116,'operador','trazabilidad',1),(117,'visor','trazabilidad',1),(121,'admin','costos-produccion',1),(122,'operador','costos-produccion',1),(123,'visor','costos-produccion',1),(124,'admin','salud-sistema',1),(125,'operador','salud-sistema',1),(126,'visor','salud-sistema',1),(127,'superadmin','cartera',1),(128,'superadmin','catalogo',1),(129,'superadmin','clientes',1),(130,'superadmin','comercial',1),(131,'superadmin','compras',1),(132,'superadmin','costos-produccion',1),(133,'superadmin','formulaciones',1),(134,'superadmin','inventario-global',1),(135,'superadmin','movimientos',1),(136,'superadmin','pagos',1),(137,'superadmin','panel-principal',1),(138,'superadmin','produccion',1),(139,'superadmin','proveedores',1),(140,'superadmin','rentabilidad',1),(141,'superadmin','roles',1),(142,'superadmin','salud-sistema',1),(143,'superadmin','trazabilidad',1);
/*!40000 ALTER TABLE `permisos_rol_modulo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preparacion_consumo_capas`
--

DROP TABLE IF EXISTS `preparacion_consumo_capas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preparacion_consumo_capas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `preparacion_id` int NOT NULL,
  `capa_id` int NOT NULL,
  `item_general_id` int NOT NULL,
  `cantidad_consumida` decimal(15,4) NOT NULL,
  `costo_unitario` decimal(15,4) NOT NULL,
  `costo_total` decimal(15,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_preparacion` (`preparacion_id`),
  KEY `idx_capa` (`capa_id`),
  CONSTRAINT `preparacion_consumo_capas_ibfk_1` FOREIGN KEY (`preparacion_id`) REFERENCES `preparaciones` (`id_preparaciones`),
  CONSTRAINT `preparacion_consumo_capas_ibfk_2` FOREIGN KEY (`capa_id`) REFERENCES `inventario_capas` (`id_capa`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preparacion_consumo_capas`
--

LOCK TABLES `preparacion_consumo_capas` WRITE;
/*!40000 ALTER TABLE `preparacion_consumo_capas` DISABLE KEYS */;
/*!40000 ALTER TABLE `preparacion_consumo_capas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preparaciones`
--

DROP TABLE IF EXISTS `preparaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preparaciones` (
  `id_preparaciones` int NOT NULL AUTO_INCREMENT,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `observaciones` text,
  `estado` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=PENDIENTE, 1=EN_PROCESO, 2=COMPLETADA, 3=CANCELADA',
  `item_general_id` int DEFAULT NULL,
  `formulacion_version_id` int DEFAULT NULL,
  `unidad_id` int DEFAULT NULL,
  PRIMARY KEY (`id_preparaciones`),
  KEY `fk_preparaciones_item_general1_idx` (`item_general_id`),
  KEY `fk_preparaciones_unidad1_idx` (`unidad_id`),
  KEY `idx_prep_form_ver` (`formulacion_version_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preparaciones`
--

LOCK TABLES `preparaciones` WRITE;
/*!40000 ALTER TABLE `preparaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `preparaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preparaciones_costos_indirectos`
--

DROP TABLE IF EXISTS `preparaciones_costos_indirectos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preparaciones_costos_indirectos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `preparaciones_id` int NOT NULL,
  `costos_indirectos_id` int DEFAULT NULL,
  `valor_aplicado` decimal(15,2) DEFAULT '0.00',
  `nombre` varchar(255) NOT NULL DEFAULT '',
  `categoria` varchar(100) NOT NULL DEFAULT 'otros',
  PRIMARY KEY (`id`),
  KEY `preparaciones_id` (`preparaciones_id`),
  KEY `costos_indirectos_id` (`costos_indirectos_id`),
  CONSTRAINT `preparaciones_costos_indirectos_ibfk_1` FOREIGN KEY (`preparaciones_id`) REFERENCES `preparaciones` (`id_preparaciones`),
  CONSTRAINT `preparaciones_costos_indirectos_ibfk_2` FOREIGN KEY (`costos_indirectos_id`) REFERENCES `costos_indirectos` (`id_costos_indirectos`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preparaciones_costos_indirectos`
--

LOCK TABLES `preparaciones_costos_indirectos` WRITE;
/*!40000 ALTER TABLE `preparaciones_costos_indirectos` DISABLE KEYS */;
/*!40000 ALTER TABLE `preparaciones_costos_indirectos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preparaciones_has_item_general`
--

DROP TABLE IF EXISTS `preparaciones_has_item_general`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preparaciones_has_item_general` (
  `preparaciones_id_preparaciones` int NOT NULL,
  `item_general_id` int NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `porcentajes` int DEFAULT NULL,
  PRIMARY KEY (`preparaciones_id_preparaciones`,`item_general_id`),
  KEY `fk_preparaciones_has_item_general_item_general1_idx` (`item_general_id`),
  KEY `fk_preparaciones_has_item_general_preparaciones1_idx` (`preparaciones_id_preparaciones`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preparaciones_has_item_general`
--

LOCK TABLES `preparaciones_has_item_general` WRITE;
/*!40000 ALTER TABLE `preparaciones_has_item_general` DISABLE KEYS */;
INSERT INTO `preparaciones_has_item_general` VALUES (21,22,3.00,1),(21,37,68.00,13),(21,142,10.00,2),(21,225,30.00,3),(21,226,60.00,19),(21,231,1.50,0),(21,232,5.00,1),(21,236,4.00,1),(21,244,15.00,3),(21,251,50.00,9),(21,252,165.00,31),(21,261,2.40,0),(21,269,8.00,1);
/*!40000 ALTER TABLE `preparaciones_has_item_general` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produccion_insumos_detalle`
--

DROP TABLE IF EXISTS `produccion_insumos_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produccion_insumos_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `preparacion_id` int NOT NULL,
  `item_general_id` int NOT NULL,
  `proveedor_id` int DEFAULT NULL,
  `lote_proveedor` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bodega_id` int DEFAULT NULL,
  `cantidad` decimal(15,4) NOT NULL,
  `costo_unitario` decimal(15,4) NOT NULL,
  `subtotal` decimal(15,4) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pid_insumos` (`preparacion_id`),
  KEY `idx_item_insumos` (`item_general_id`),
  KEY `idx_prov_insumos` (`proveedor_id`),
  KEY `idx_pid_lote` (`lote_proveedor`),
  CONSTRAINT `produccion_insumos_detalle_item_general_id_foreign` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`),
  CONSTRAINT `produccion_insumos_detalle_preparacion_id_foreign` FOREIGN KEY (`preparacion_id`) REFERENCES `preparaciones` (`id_preparaciones`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produccion_insumos_detalle`
--

LOCK TABLES `produccion_insumos_detalle` WRITE;
/*!40000 ALTER TABLE `produccion_insumos_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `produccion_insumos_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedor` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `nombre_encargado` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre_empresa` varchar(27) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero_documento` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`),
  UNIQUE KEY `id_proveedor_UNIQUE` (`id_proveedor`),
  KEY `idx_proveedor_deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedor`
--

LOCK TABLES `proveedor` WRITE;
/*!40000 ALTER TABLE `proveedor` DISABLE KEYS */;
INSERT INTO `proveedor` VALUES (23,'MARTHA PINO VILLA','COLARQUIM','800226277-6','Cl. 110 #75A-620 Bodega 14, Riomar','3135730324','servicioalclientebq@colarquim.com',NULL),(24,'PMA','PMA','1004914866','','','',NULL),(25,'LILIANA HERRERA','CONQUIMICA','890919549','','3113676010','',NULL),(26,'Carlos Pérez','RECIEND','1','','','',NULL),(27,'María Gómez','PROQUIMICOS','1','','310 3782317','',NULL),(28,'Carlos Rodríguez','PROCESOS Y DISOLVENTES','1','','','',NULL),(29,'María Gómez','EVER POL','1','','','',NULL),(30,'DIANA PEREZ','BRENTANG','10001914855','Cl. 30 #15-360, Barranquilla','','',NULL),(31,'XIOMARA ','PREFLEX','860508470-0','Cra 2 #56-25, CAZUCA','3106793731','xpineda@preflex.com.co',NULL),(32,'María Gómez','isGroup','800987654-2','','','',NULL),(33,NULL,'distriatlantico','900751588-5',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `proveedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refresh_tokens`
--

DROP TABLE IF EXISTS `refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refresh_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Hash SHA-256 del refresh token. NUNCA el token plano.',
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `token_hash` (`token_hash`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `refresh_tokens_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refresh_tokens`
--

LOCK TABLES `refresh_tokens` WRITE;
/*!40000 ALTER TABLE `refresh_tokens` DISABLE KEYS */;
INSERT INTO `refresh_tokens` VALUES (3,2,'e9d5febaf1ce5966791afceed3fd1407fd7a790d09bab103635bd321bd26002b','2026-06-03 20:36:42','2026-05-27 20:36:42',0),(4,2,'523553bf1e83f5f1cdaefd70ddf49d82734e8cc5b3f2908e5b99c6fa5821c888','2026-06-05 16:54:48','2026-05-29 16:54:48',0),(5,2,'1b3424b2f8b31473a4fded36a16bea3edb64f9bebb7eb422d82687567d907de1','2026-06-06 14:25:18','2026-05-30 14:25:18',0),(6,2,'b9e833fc48da19809bb6176c5704f86a4c3da38d77f92d89a1e198d3ab516fc4','2026-06-06 14:25:30','2026-05-30 14:25:30',0),(7,5,'879f8e6892ac89d3fa94aadcf5741691c639825d1fa4f737db8920daa4f97950','2026-06-08 20:33:48','2026-06-01 20:33:48',0),(8,2,'7a06ab54ac43fd5df2ac8653b968887a80b2de2f94db927dcb5e514bf02254a5','2026-06-08 21:26:48','2026-06-01 21:26:48',0),(9,2,'5250a86cfa00decef6ff2a1ce2279bd0ea758fded0df199385744ac015f9acf5','2026-06-19 20:39:45','2026-06-12 20:39:45',0),(10,2,'d50b06d34f6363d86e7cb724e0af8d34f7ad7d17f05ad2588c3b8883fccee181','2026-06-19 20:39:47','2026-06-12 20:39:47',0),(11,2,'e55ec171ee53a61744eeb9b75b5d6aad7730329e2d65b67022e1218d81c48e80','2026-06-19 20:39:47','2026-06-12 20:39:47',0),(12,2,'8c8c1a822007ee5cbeef3575ad32e6557bae9d110f9c944b6608b84f5ce77bf2','2026-06-19 20:41:53','2026-06-12 20:41:53',0),(13,2,'91d3bc5aaf4818d7a26bb1b156a76ec8f4f91401d99ff1150c0613b558690e1b','2026-06-19 20:41:54','2026-06-12 20:41:54',0),(14,2,'df16a4e70d1692b506e4e8269bfd6c675be9655742c1f2fabd9ade5d8160074f','2026-06-19 20:41:55','2026-06-12 20:41:55',0),(15,2,'89a169c5be7e62c1dd67c4843acaa668b2feae7b435746094996baecb751c45e','2026-06-19 20:43:53','2026-06-12 20:43:53',0),(16,2,'98dcabc2bb03759ac004d634635d02429ce7b2990829e48609763bb4ae55a8f1','2026-06-19 20:44:11','2026-06-12 20:44:11',0),(17,2,'603da3550060ea0e508edaadab326eb60a5c906965bb253049aebfebb5973b88','2026-06-19 20:44:13','2026-06-12 20:44:13',0),(18,2,'e6169b00c1d9fea2e16a23ae93829c5e70e6e3655b53acf271d62e55fcc69ace','2026-06-19 20:44:13','2026-06-12 20:44:13',0),(19,2,'bf1896630c2daef234d69be1ca6e4cd649d3d1455f436fc6ec19a690d8e2673f','2026-06-19 20:44:14','2026-06-12 20:44:14',0),(20,2,'bb51797deb8707af071ab36967e80dd3a20cb1cc59e84aba0d9ab5b6d2d14049','2026-06-19 20:44:14','2026-06-12 20:44:14',0),(21,2,'a635c148d1fb941da13c9d2dfe64daf42be0c11664796c75f2f050254a427c1d','2026-06-19 20:44:14','2026-06-12 20:44:14',0),(22,2,'bd3542fb34245f66c3cd6a1762a54e695261e8a65b3c0b0a9ede3353903ea179','2026-06-19 20:44:51','2026-06-12 20:44:51',0),(23,2,'713625539b52671ff91adede0dd34fffe52a5cae50857cc5f5b30803c975befe','2026-06-19 20:57:03','2026-06-12 20:57:03',0),(24,2,'50006e16dab51bbdfcf0077961dda75dc8d021f4dcc41c0568ea30b9c8e2eefd','2026-06-19 20:57:04','2026-06-12 20:57:04',0),(25,2,'b71eced54ef0d21b882ce9d89b6bc9bdda4923829baf21b5277f245825f9c860','2026-06-19 20:57:05','2026-06-12 20:57:05',0),(26,2,'fbf3c1b4042c43baf1fccb9b2f81645900c492c9d2c13ddc7d68c24e8f415275','2026-06-19 20:59:23','2026-06-12 20:59:23',0),(27,2,'ede43b6419948d520ff8afe2a3b7739e8461093058f825dd2964818d65598f95','2026-06-19 20:59:24','2026-06-12 20:59:24',0),(28,2,'3bbb368b8e7c38d36b98f456085ae3ebecf09f1598c4341e0fe9b8d20f0b32d9','2026-06-19 20:59:24','2026-06-12 20:59:24',0),(29,2,'f61a816eb14036d94c3127cd9573eaf4f23af8f8f3e4295732fd1f14dac1c9b6','2026-06-19 20:59:51','2026-06-12 20:59:51',0),(30,2,'0dcb8420644efb749965d6ca925133334d04b4a32180edf78115f0d1887a0b0f','2026-06-19 20:59:53','2026-06-12 20:59:53',0),(31,2,'47452b944f833d9153a67254f0070a6da561e8dc001190a280326b2adc7bc38b','2026-06-19 20:59:54','2026-06-12 20:59:54',0),(32,2,'cb3371c3c87d274308c5b7a35ef442688e8b1d2fd2e1c54a1ad56ed0c70a73a4','2026-06-19 20:59:54','2026-06-12 20:59:54',0),(33,2,'97aaf747c0b9733489991763b41dd914d5dc9ea4c43dc472b96f4632bad94585','2026-06-19 20:59:55','2026-06-12 20:59:55',0),(34,2,'ea3421d66aac8509c1e575a276c0f1f254b285bc48eb95cdc0cfe543fbe959e3','2026-06-19 20:59:55','2026-06-12 20:59:55',0),(35,2,'213523c1da47d7545aae6193cc16e934593d1277782a8870493ebbc16fe79363','2026-06-19 21:00:22','2026-06-12 21:00:22',0),(36,2,'9a615b3dda7c0a2e30c0d2f9c5bcf844d98530f927f8be12a2b5cc5f65ac5dc0','2026-06-19 21:00:23','2026-06-12 21:00:23',0),(37,2,'e625c6e427afdd4ce7550808ac0b1f502441772caee0ef1d361a7c6573ac6464','2026-06-19 21:00:23','2026-06-12 21:00:23',0),(38,2,'eba2578e103e8104442b4020499add02b6798e9aac5f76bec12f3c03cf3d0f38','2026-06-19 21:00:55','2026-06-12 21:00:55',0),(39,2,'07755249ea990ef96efa7c8dbe96f0a23d450df56cbea5bb0d127d533437219f','2026-06-19 21:01:03','2026-06-12 21:01:03',0),(40,2,'1a29d98a4ae7ce2f113903c033b43c7fa8bfaaa8f600cb0a942dab2bd51eeab8','2026-06-19 21:01:05','2026-06-12 21:01:05',0);
/*!40000 ALTER TABLE `refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remision_consumo_capas`
--

DROP TABLE IF EXISTS `remision_consumo_capas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remision_consumo_capas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `remision_id` int NOT NULL,
  `remision_detalle_id` int NOT NULL,
  `capa_id` int NOT NULL,
  `item_general_id` int NOT NULL,
  `cantidad_consumida` decimal(15,4) NOT NULL,
  `costo_unitario` decimal(15,4) NOT NULL,
  `costo_total` decimal(15,4) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rcc_rem` (`remision_id`),
  KEY `idx_rcc_det` (`remision_detalle_id`),
  KEY `idx_rcc_capa` (`capa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remision_consumo_capas`
--

LOCK TABLES `remision_consumo_capas` WRITE;
/*!40000 ALTER TABLE `remision_consumo_capas` DISABLE KEYS */;
/*!40000 ALTER TABLE `remision_consumo_capas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remisiones`
--

DROP TABLE IF EXISTS `remisiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remisiones` (
  `id_remisiones` int unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `cliente_id` int NOT NULL,
  `fecha_remision` date NOT NULL,
  `estado` enum('Pendiente','Despachada','Facturada','Anulada') NOT NULL DEFAULT 'Pendiente',
  `direccion_entrega` varchar(255) DEFAULT NULL,
  `observaciones` text,
  `facturas_id` int DEFAULT NULL,
  `movimiento_inventario_id` int DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_remisiones`),
  UNIQUE KEY `numero` (`numero`),
  KEY `cliente_id` (`cliente_id`),
  KEY `facturas_id` (`facturas_id`),
  KEY `movimiento_inventario_id` (`movimiento_inventario_id`),
  KEY `idx_remisiones_deleted_at` (`deleted_at`),
  CONSTRAINT `remisiones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id_clientes`),
  CONSTRAINT `remisiones_ibfk_2` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`),
  CONSTRAINT `remisiones_ibfk_3` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimiento_inventario` (`id_movimiento_inventario`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remisiones`
--

LOCK TABLES `remisiones` WRITE;
/*!40000 ALTER TABLE `remisiones` DISABLE KEYS */;
INSERT INTO `remisiones` VALUES (18,'REM-2026-0001',1,'2026-05-25','Anulada',NULL,NULL,NULL,NULL,'2026-05-25 14:59:56',NULL),(19,'REM-2026-0002',1,'2026-05-25','Pendiente',NULL,NULL,NULL,NULL,'2026-05-25 14:59:57',NULL),(20,'REM-2026-0003',1,'2026-05-25','Anulada',NULL,NULL,NULL,NULL,'2026-05-25 16:04:47',NULL),(21,'REM-2026-0004',1,'2026-05-25','Pendiente',NULL,NULL,NULL,NULL,'2026-05-25 16:04:48',NULL),(22,'REM-2026-0005',1,'2026-06-12','',NULL,NULL,NULL,NULL,'2026-06-12 20:39:46',NULL),(23,'REM-2026-0006',1,'2026-06-12','Pendiente',NULL,NULL,NULL,NULL,'2026-06-12 20:39:47',NULL),(24,'REM-2026-0007',1,'2026-06-12','',NULL,NULL,NULL,NULL,'2026-06-12 20:41:53',NULL),(25,'REM-2026-0008',1,'2026-06-12','Pendiente',NULL,NULL,NULL,NULL,'2026-06-12 20:41:54',NULL),(26,'REM-2026-0009',1,'2026-06-12','Anulada',NULL,NULL,NULL,NULL,'2026-06-12 20:57:03',NULL),(27,'REM-2026-0010',1,'2026-06-12','Pendiente',NULL,NULL,NULL,NULL,'2026-06-12 20:57:04',NULL),(28,'REM-2026-0011',1,'2026-06-12','Anulada',NULL,NULL,NULL,NULL,'2026-06-12 20:59:23',NULL),(29,'REM-2026-0012',1,'2026-06-12','Pendiente',NULL,NULL,NULL,NULL,'2026-06-12 20:59:24',NULL),(30,'REM-2026-0013',1,'2026-06-12','Anulada',NULL,NULL,NULL,NULL,'2026-06-12 20:59:53',NULL),(31,'REM-2026-0014',1,'2026-06-12','Pendiente',NULL,NULL,NULL,NULL,'2026-06-12 20:59:54',NULL),(32,'REM-2026-0015',1,'2026-06-12','Pendiente',NULL,NULL,NULL,NULL,'2026-06-12 21:00:22',NULL),(33,'REM-2026-0016',1,'2026-06-12','Pendiente',NULL,NULL,NULL,NULL,'2026-06-12 21:00:23',NULL);
/*!40000 ALTER TABLE `remisiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remisiones_detalle`
--

DROP TABLE IF EXISTS `remisiones_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remisiones_detalle` (
  `id_detalle` int unsigned NOT NULL AUTO_INCREMENT,
  `remisiones_id` int unsigned NOT NULL,
  `item_general_id` int DEFAULT NULL,
  `bodega_id` int DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `precio_unit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_detalle`),
  KEY `remisiones_id` (`remisiones_id`),
  KEY `idx_remdet_item` (`item_general_id`),
  CONSTRAINT `remisiones_detalle_ibfk_1` FOREIGN KEY (`remisiones_id`) REFERENCES `remisiones` (`id_remisiones`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remisiones_detalle`
--

LOCK TABLES `remisiones_detalle` WRITE;
/*!40000 ALTER TABLE `remisiones_detalle` DISABLE KEYS */;
INSERT INTO `remisiones_detalle` VALUES (17,18,NULL,NULL,'Test despacho',1.50,10000.00,15000.00),(18,19,NULL,NULL,'Test sin stock',999999.00,1.00,999999.00),(19,20,NULL,NULL,'Test despacho',1.50,10000.00,15000.00),(20,21,NULL,NULL,'Test sin stock',999999.00,1.00,999999.00),(21,22,NULL,NULL,'Test despacho',1.50,10000.00,15000.00),(22,23,NULL,NULL,'Test sin stock',999999.00,1.00,999999.00),(23,24,NULL,NULL,'Test despacho',1.50,10000.00,15000.00),(24,25,NULL,NULL,'Test sin stock',999999.00,1.00,999999.00),(25,26,NULL,NULL,'Test despacho',1.50,10000.00,15000.00),(26,27,NULL,NULL,'Test sin stock',999999.00,1.00,999999.00),(27,28,NULL,NULL,'Test despacho',1.50,10000.00,15000.00),(28,29,NULL,NULL,'Test sin stock',999999.00,1.00,999999.00),(29,30,NULL,NULL,'Test despacho',1.50,10000.00,15000.00),(30,31,NULL,NULL,'Test sin stock',999999.00,1.00,999999.00);
/*!40000 ALTER TABLE `remisiones_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requisiciones_compra`
--

DROP TABLE IF EXISTS `requisiciones_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `requisiciones_compra` (
  `id_requisicion` int unsigned NOT NULL AUTO_INCREMENT,
  `preparacion_id` int unsigned NOT NULL,
  `item_general_id` int unsigned NOT NULL,
  `item_proveedor_id` int unsigned DEFAULT NULL,
  `proveedor_id` int unsigned DEFAULT NULL,
  `cantidad_necesaria` decimal(10,4) NOT NULL,
  `cantidad_disponible` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `cantidad_solicitada` decimal(10,4) NOT NULL,
  `precio_unitario` decimal(14,2) DEFAULT NULL,
  `estado` enum('SUGERIDA','PENDIENTE','APROBADA','CONVERTIDA','CANCELADA') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PENDIENTE',
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `orden_compra_id` int unsigned DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_requisicion`),
  KEY `preparacion_id` (`preparacion_id`),
  KEY `estado` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requisiciones_compra`
--

LOCK TABLES `requisiciones_compra` WRITE;
/*!40000 ALTER TABLE `requisiciones_compra` DISABLE KEYS */;
/*!40000 ALTER TABLE `requisiciones_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tambor_movimientos`
--

DROP TABLE IF EXISTS `tambor_movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tambor_movimientos` (
  `id_tambor_movimiento` int NOT NULL AUTO_INCREMENT,
  `tambor_id` int NOT NULL,
  `tipo` tinyint DEFAULT NULL COMMENT '1=entrada 2=salida',
  `cantidad` decimal(10,2) DEFAULT NULL,
  `referencia_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `referencia_id` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  PRIMARY KEY (`id_tambor_movimiento`),
  KEY `fk_tambor_mov_tambor` (`tambor_id`),
  CONSTRAINT `fk_tambor_mov_tambor` FOREIGN KEY (`tambor_id`) REFERENCES `tambores` (`id_tambor`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tambor_movimientos`
--

LOCK TABLES `tambor_movimientos` WRITE;
/*!40000 ALTER TABLE `tambor_movimientos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tambor_movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidad`
--

DROP TABLE IF EXISTS `unidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidad` (
  `id_unidad` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `estados` tinyint DEFAULT NULL,
  `escala` decimal(10,5) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_unidad`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidad`
--

LOCK TABLES `unidad` WRITE;
/*!40000 ALTER TABLE `unidad` DISABLE KEYS */;
INSERT INTO `unidad` VALUES (1,'TAMBOR','',1,50.00000,NULL),(2,'CUÑETE','',1,5.00000,NULL),(3,'GALON','',1,1.00000,NULL),(4,'1/2 GALON','',1,0.50000,NULL),(5,'1/4 GALON','',1,0.25000,NULL),(6,'1/8 GALON','',1,0.12500,NULL),(7,'1/16 GALON','',1,0.06250,NULL),(8,'1/32 GALON','',1,0.03125,NULL),(9,'KILO','',1,1.00000,NULL),(10,'GRAMO','',1,0.00100,NULL),(11,'LIBRA','',1,0.45300,NULL),(12,'LITRO','',1,0.26417,NULL),(13,'UNIDAD',NULL,NULL,1.00000,NULL),(14,'CAJA',NULL,NULL,1.00000,NULL),(15,'BULTO',NULL,NULL,1.00000,NULL),(16,'CANECA',NULL,NULL,1.00000,NULL);
/*!40000 ALTER TABLE `unidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuarios` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rol` enum('superadmin','admin','operador','visor') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'operador',
  `password_must_change` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Si 1, el usuario debe cambiar password al próximo login.',
  `token_version` int unsigned NOT NULL DEFAULT '1' COMMENT 'Incrementa al cambiar rol o password — invalida tokens viejos.',
  PRIMARY KEY (`id_usuarios`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (2,'root','Jorge Herrera','$2y$10$zcSxsrQHkHFxPddPk/.TFeeFceYqtUeb3wtlLSxfnDG4Ll5dL1Szu','admin',0,1),(5,'1juanherrera','Juan Herrera','$2y$10$gbWzSg/j.GwJHlMT9LKKMujex17dmeanFGm2myxFAyRl7f.jh/J8a','superadmin',1,1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-12 21:03:29
