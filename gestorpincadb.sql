-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 16-01-2026 a las 21:54:38
-- Versión del servidor: 8.0.44
-- Versión de PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gestorpincadb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bodegas`
--

CREATE TABLE `bodegas` (
  `id_bodegas` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` tinyint DEFAULT NULL COMMENT '0 inactiva 1 activa',
  `instalaciones_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bodegas`
--

INSERT INTO `bodegas` (`id_bodegas`, `nombre`, `descripcion`, `estado`, `instalaciones_id`) VALUES
(1, 'Bodega principal', 'BODEGA INSUMOS, MATERIAS PRIMAS Y PRODUCTOS', 1, 1),
(2, 'Villa Olimpica', 'Instalación de acopio y despacho situada en la zona de Villa Olímpica, ideal para operaciones urbanas gracias a su cercanía con áreas residenciales y comerciales.', 1, 2),
(3, 'Juan Mina', 'Punto estratégico en la Vía Cordialidad, orientado al manejo de inventarios y distribución regional, con conexiones hacia rutas intermunicipales.', 1, 3),
(8, 'Laboratorio', 'Área de bodega con acondicionamiento tipo laboratorio', 1, 1),
(15, 'Centro de insumos', 'Área destinada al almacenamiento y distribución de insumos.', 1, 1),
(16, 'Depósito especializado', 'Espacio seguro para almacenamiento bajo condiciones controladas.', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int NOT NULL,
  `nombre` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre`) VALUES
(1, 'ESMALTE'),
(2, 'PASTA'),
(3, 'ANTICORROSIVO'),
(4, 'BARNIZ');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_clientes` int NOT NULL,
  `nombre_encargado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre_empresa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero_documento` bigint DEFAULT NULL,
  `direccion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` bigint DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` tinyint NOT NULL DEFAULT '2' COMMENT '1 Empresa 2 Particular',
  `estado` tinyint NOT NULL DEFAULT '1' COMMENT '1 activo 2 inactivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_clientes`, `nombre_encargado`, `nombre_empresa`, `numero_documento`, `direccion`, `telefono`, `email`, `tipo`, `estado`) VALUES
(1, 'Carlos Mendoza', 'Distribuidora Andina S.A.S', 900123456, 'Calle 45 #32-10, Barranquilla', 3014567890, 'c.mendoza@andina.com', 2, 1),
(2, 'Juliana Pérez', 'Soluciones del Caribe Ltda', 801987654, 'Carrera 21 #55-22, Cartagena', 3157894321, 'juliana.perez@caribe.com', 1, 2),
(3, 'Mauricio Torres', 'Pinturas Torres & Cía', 1023456789, 'Av. Murillo #12-80, Barranquilla', 3001122334, 'm.torres@ptorres.com', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos_item`
--

CREATE TABLE `costos_item` (
  `id` int NOT NULL,
  `item_general_id` int NOT NULL,
  `costo_unitario` decimal(18,2) DEFAULT NULL,
  `costo_mp_galon` decimal(10,0) DEFAULT NULL,
  `costo_cunete` decimal(10,0) NOT NULL,
  `costo_tambor` decimal(10,0) NOT NULL,
  `periodo` varchar(7) COLLATE utf8mb4_general_ci DEFAULT NULL,
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
  `estado` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `costos_item`
--

INSERT INTO `costos_item` (`id`, `item_general_id`, `costo_unitario`, `costo_mp_galon`, `costo_cunete`, `costo_tambor`, `periodo`, `metodo_calculo`, `fecha_calculo`, `costo_mp_kg`, `envase`, `etiqueta`, `bandeja`, `plastico`, `volumen`, `precio_venta`, `cantidad_total`, `costo_mod`, `estado`) VALUES
(1, 1, 0.00, 2000, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 370, 2000.00, 0, 600, NULL),
(2, 31, 7000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(3, 32, 11000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(4, 33, 34050.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(5, 34, 27144.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(6, 35, 12691.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(7, 36, 4372.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(8, 37, 11466.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(9, 38, 16300.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(10, 39, 17000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(11, 40, 4400.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(12, 41, 14300.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(13, 42, 40.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(14, 43, 1550.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(15, 44, 4617.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(17, 46, 14300.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(18, 47, 855.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(19, 48, 5400.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(21, 50, 12215.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(23, 52, 14152.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(25, 54, 12718.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(27, 56, 11447.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(28, 57, 1690.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(30, 59, 722.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(31, 60, 715.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(32, 61, 4300.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(33, 62, 4400.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(34, 63, 8000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(35, 64, 8000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(36, 65, 1103.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(37, 66, 22700.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(38, 67, 43900.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(39, 68, 37300.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(40, 69, 22700.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(41, 70, 7000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(42, 71, 19500.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(43, 72, 33500.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(44, 73, 37200.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(45, 74, 21850.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(46, 75, 10400.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(47, 76, 8000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(48, 77, 11466.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(49, 78, 13000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(50, 79, 17000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(51, 80, 2900.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(52, 81, 17000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(54, 83, 4617.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(55, 84, 22700.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(56, 85, 22700.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(57, 86, 11000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(58, 2, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 719, 20000.00, 0, 600, NULL),
(59, 3, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 398, 170000.00, 0, 600, NULL),
(60, 4, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 440, 0.00, 0, 600, NULL),
(61, 5, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 376, 0.00, 0, 600, NULL),
(62, 6, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 397, 0.00, 0, 600, NULL),
(63, 7, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 396, 0.00, 0, 600, NULL),
(64, 8, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 712, 0.00, 0, 600, NULL),
(65, 9, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 616, 0.00, 0, 600, NULL),
(66, 10, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 711, 0.00, 0, 600, NULL),
(67, 11, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 595, 0.00, 0, 600, NULL),
(68, 12, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 599, 0.00, 0, 600, NULL),
(69, 13, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 578, 0.00, 0, 600, NULL),
(70, 14, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 813, 0.00, 0, 600, NULL),
(71, 15, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 168, 0.00, 0, 600, NULL),
(72, 16, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 212, 0.00, 0, 600, NULL),
(73, 17, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 213, 0.00, 0, 600, NULL),
(74, 18, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 801, 0.00, 0, 600, NULL),
(75, 19, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 3600.00, 350.00, 140, 153, 178, 0.00, 0, 600, NULL),
(76, 20, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 328, 0.00, 0, 150, NULL),
(77, 21, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 345, 0.00, 0, 150, NULL),
(78, 22, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 488, 0.00, 0, 150, NULL),
(79, 23, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 119, 0.00, 0, 150, NULL),
(80, 24, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 961, 0.00, 0, 150, NULL),
(81, 25, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 1018, 0.00, 0, 150, NULL),
(82, 26, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 874, 0.00, 0, 150, NULL),
(83, 27, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 851, 0.00, 0, 150, NULL),
(84, 28, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 833, 0.00, 0, 150, NULL),
(85, 29, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 748, 0.00, 0, 150, NULL),
(86, 30, 0.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-07', 0, 0.00, 0.00, 0, 0, 376, 0.00, 0, 150, NULL),
(87, 87, 4372.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(88, 88, 4400.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(89, 89, 4372.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(90, 90, 4372.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(92, 92, 16300.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(93, 93, 14152.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(94, 94, 11466.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(95, 95, 17000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(96, 96, 11447.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(97, 97, 22700.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(98, 98, 22700.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(99, 99, 22700.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-10', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(108, 100, 8000.00, 0, 0, 0, NULL, 'MANUAL', '2025-06-15', 0, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, NULL),
(128, 114, 60.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 60, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(129, 115, 7880.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 7880, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(130, 116, 5900.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 5900, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(131, 117, 16000.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 16000, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(132, 118, 10000.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 10000, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(133, 119, 9200.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 9200, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(134, 120, 9800.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 9800, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(135, 121, 16600.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 16600, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(136, 122, 11100.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 11100, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(137, 123, 558.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 558, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(138, 124, 1520.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 1520, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(139, 125, 2450.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 2450, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(140, 126, 1800.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 1800, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(141, 127, 4850.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 4850, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(142, 128, 7200.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 7200, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(143, 129, 15000.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 15000, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(144, 130, 11700.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 11700, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(145, 131, 6905.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 6905, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(146, 132, 23000.00, 0, 0, 0, NULL, 'MANUAL', '2026-01-12', 23000, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
(147, 133, 0.00, 0, 0, 0, NULL, 'Manual', '2026-01-16', 0, 0.00, 0.00, 0, 0, 1, 0.00, 1, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos_produccion`
--

CREATE TABLE `costos_produccion` (
  `id` int NOT NULL,
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
  `preparaciones_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_facturas`
--

CREATE TABLE `detalle_facturas` (
  `id_detalle_facturas` int NOT NULL,
  `cantidad` tinyint DEFAULT NULL,
  `precio_unitario` decimal(7,1) DEFAULT NULL,
  `subtotal` decimal(7,1) DEFAULT NULL,
  `facturas_id` int DEFAULT NULL,
  `item_general_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int NOT NULL,
  `nit` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `razon_social` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ciudad` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pagina_web` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nit`, `razon_social`, `descripcion`, `ciudad`, `telefono`, `pagina_web`) VALUES
(1, '901314182', 'PINTURAS INDUSTRIALES DEL CARIBE S.A.S', 'Comercio al por mayor de materiales de construcción, artículos de ferretería, pinturas, productos de vidrio, equipo y materiales de fontanería y calefacción. - 4663', 'Barranquilla', '3019794729', 'https://pinca.com.co/');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_facturas` int NOT NULL,
  `numero` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cliente_id` int DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `estado` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Pendiente, Pagada',
  `subtotal` decimal(10,2) DEFAULT NULL,
  `impuestos` decimal(10,2) DEFAULT NULL,
  `retencion` decimal(10,2) DEFAULT NULL,
  `movimiento_inventario_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id_facturas`, `numero`, `cliente_id`, `fecha_emision`, `total`, `estado`, `subtotal`, `impuestos`, `retencion`, `movimiento_inventario_id`) VALUES
(1, 'FAC-20', 1, '2025-11-12', 350000.00, 'Pendiente', 300000.00, 57000.00, 7000.00, 6),
(2, '89211291', 2, '2025-01-12', 750000.00, 'Pagada', 300000.00, 57000.00, 7000.00, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formulaciones`
--

CREATE TABLE `formulaciones` (
  `id_formulaciones` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `estado` tinyint DEFAULT NULL COMMENT '0 inactiva\\n1 activa',
  `defecto` tinyint DEFAULT '0' COMMENT '1 por defecto',
  `item_general_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `formulaciones`
--

INSERT INTO `formulaciones` (`id_formulaciones`, `nombre`, `descripcion`, `estado`, `defecto`, `item_general_id`) VALUES
(1, 'PREPARACIÓN BARNIZ TRANSPARENTE BIRILLANTE', NULL, 1, 1, 1),
(2, 'PREPARACION ESMALTE BLANCO', NULL, 1, 1, 2),
(3, 'PREPARACION ESMALTE CAOBA', NULL, 1, 1, 3),
(4, 'PREPARACION ESMALTE NEGRO MATE', NULL, 1, 1, 4),
(5, 'PREPARACIÓN ESMALTE ROJO FIESTA', NULL, 1, 1, 5),
(6, 'PREPARACION ESMALTE NEGRO BRILLANTE', NULL, 1, 1, 6),
(7, 'PREPARACION ESMALTE VERDE ESMERALDA', NULL, 1, 1, 7),
(8, 'PREPARACION ESMALTE GRIS PLATA', NULL, 1, 1, 8),
(9, 'PREPARACION ESMALTE AZUL ESPAÑOL', NULL, 1, 1, 9),
(10, 'PREPARACION ESMALTE BLANCO MATE', NULL, 1, 1, 10),
(11, 'PREPARACION ESMALTE AMARILLO', NULL, 1, 1, 11),
(12, 'PREPARACION ESMALTE NARANJA', NULL, 1, 1, 12),
(13, 'PREPARACION ESMALTE TABACO', NULL, 1, 1, 13),
(14, 'PREPARACION ANTICORROSIVO GRIS', NULL, 1, 1, 14),
(15, 'PREPARACION ANTICORROSIVO NEGRO', NULL, 1, 1, 15),
(16, 'PREPARACION ANTICORROSIVO AMARILLO', NULL, 1, 1, 16),
(17, 'PREPARACION ANTICORROSIVO ROJO', NULL, 1, 1, 17),
(18, 'PREPARACION ANTICORROSIVO BLANCO', NULL, 1, 1, 18),
(19, 'PREPARACION ANTICORROSIVO VERDE', NULL, 1, 1, 19),
(20, 'PREPARACION PASTA ESMALTE VERDE ENTONADOR', NULL, 1, 1, 20),
(21, 'PREPARACION PASTA ESMALTE AZUL ENTONADOR', NULL, 1, 1, 21),
(22, 'PREPARACION PASTA ESMALTE NEGRO', NULL, 1, 1, 22),
(23, 'PREPARACION PASTA ESMALTE ROJO CARMIN 57:1', NULL, 1, 1, 23),
(24, 'PREPARACION PASTA ESMALTE NARANJA', NULL, 1, 1, 24),
(25, 'PREPARACION PASTA ESMALTE AMARILLO', NULL, 1, 1, 25),
(26, 'PREPARACION PASTA ESMALTE CAOBA', NULL, 1, 1, 26),
(27, 'PREPARACION PASTA ESMALTE AMARILLO OXIDO', NULL, 1, 1, 27),
(28, 'PREPARACION PASTA ESMALTE ROJO OXIDO', NULL, 1, 1, 28),
(29, 'PREPARACION PASTA ESMALTE BLANCO', NULL, 1, 1, 29),
(30, 'PREPARACION PASTA ESMALTE TABACO', NULL, 1, 1, 30),
(48, 'Formulación - VINILO T1 BLANCO', NULL, 1, 1, 133);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones`
--

CREATE TABLE `instalaciones` (
  `id_instalaciones` int NOT NULL,
  `nombre` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ciudad` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_empresa` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instalaciones`
--

INSERT INTO `instalaciones` (`id_instalaciones`, `nombre`, `descripcion`, `ciudad`, `direccion`, `telefono`, `id_empresa`) VALUES
(1, 'Sede Cordialidad', 'SEDE DE FABRICACIÓN DE PINTURAS', ' BARRANQUILLA', 'Calle 99 # 6-59', '3019794729', 1),
(2, 'Sede Villa Olimpica', 'SEDE DE FABRICACIÓN DE PINTURAS', 'Galapa', '', '3019794729', 1),
(3, 'Sede Juan Mina', 'SEDE DE FABRICACIÓN DE PINTURAS', 'Barranquilla', '', '3019794729', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id_inventario` int NOT NULL,
  `cantidad` decimal(5,2) DEFAULT NULL,
  `fecha_update` date DEFAULT NULL,
  `apartada` tinyint DEFAULT NULL,
  `item_general_id` int NOT NULL,
  `estado` tinyint DEFAULT NULL COMMENT '0 disponible\\r\\n1 No disponible',
  `movimiento_inventario_id` int DEFAULT NULL,
  `tipo` tinyint DEFAULT NULL COMMENT '1 ingreso\n2 egreso',
  `bodegas_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_inventario`, `cantidad`, `fecha_update`, `apartada`, `item_general_id`, `estado`, `movimiento_inventario_id`, `tipo`, `bodegas_id`) VALUES
(1, 10.00, NULL, 0, 1, 0, NULL, 1, 1),
(2, 0.00, NULL, 0, 2, 0, NULL, 1, 1),
(3, 0.00, NULL, 0, 3, 0, NULL, 1, 1),
(4, 0.00, NULL, 0, 4, 0, NULL, 1, 1),
(5, 0.00, NULL, 0, 5, 0, NULL, 1, 1),
(6, 0.00, NULL, 0, 6, 0, NULL, 1, 1),
(7, 0.00, NULL, 0, 7, 0, NULL, 1, 1),
(8, 0.00, NULL, 0, 8, 0, NULL, 1, 1),
(9, 0.00, NULL, 0, 9, 0, NULL, 1, 1),
(10, 0.00, NULL, 0, 10, 0, NULL, 1, 1),
(11, 0.00, NULL, 0, 11, 0, NULL, 1, 1),
(12, 0.00, NULL, 0, 12, 0, NULL, 1, 1),
(13, 0.00, NULL, 0, 13, 0, NULL, 1, 1),
(14, 0.00, NULL, 0, 14, 0, NULL, 1, 1),
(15, 0.00, NULL, 0, 15, 0, NULL, 1, 1),
(16, 0.00, NULL, 0, 16, 0, NULL, 1, 1),
(17, 0.00, NULL, 0, 17, 0, NULL, 1, 1),
(18, 0.00, NULL, 0, 18, 0, NULL, 1, 1),
(19, 0.00, NULL, 0, 19, 0, NULL, 1, 1),
(20, 0.00, NULL, 0, 20, 0, NULL, 1, 1),
(21, 0.00, NULL, 0, 21, 0, NULL, 1, 1),
(22, 0.00, NULL, 0, 22, 0, NULL, 1, 1),
(23, 0.00, NULL, 0, 23, 0, NULL, 1, 1),
(24, 0.00, NULL, 0, 24, 0, NULL, 1, 1),
(25, 0.00, NULL, 0, 25, 0, NULL, 1, 1),
(26, 0.00, NULL, 0, 26, 0, NULL, 1, 1),
(27, 0.00, NULL, 0, 27, 0, NULL, 1, 1),
(28, 0.00, NULL, 0, 28, 0, NULL, 1, 1),
(29, 0.00, NULL, 0, 29, 0, NULL, 1, 1),
(30, 0.00, NULL, 0, 30, 0, NULL, 1, 1),
(31, 20.00, NULL, 0, 31, 0, NULL, 1, 1),
(32, 5.00, NULL, 0, 32, 0, NULL, 1, 1),
(33, 0.00, NULL, 0, 33, 0, NULL, 1, 1),
(34, 0.00, NULL, 0, 34, 0, NULL, 1, 1),
(35, 0.00, NULL, 0, 35, 0, NULL, 1, 1),
(36, 0.00, NULL, 0, 36, 0, NULL, 1, 1),
(37, 0.00, NULL, 0, 37, 0, NULL, 1, 1),
(38, 0.00, NULL, 0, 38, 0, NULL, 1, 1),
(39, 0.00, NULL, 0, 39, 0, NULL, 1, 1),
(40, 0.00, NULL, 0, 40, 0, NULL, 1, 1),
(41, 0.00, NULL, 0, 41, 0, NULL, 1, 1),
(42, 0.00, NULL, 0, 42, 0, NULL, 1, 1),
(43, 0.00, NULL, 0, 43, 0, NULL, 1, 1),
(44, 0.00, NULL, 0, 44, 0, NULL, 1, 1),
(45, 0.00, NULL, 0, 46, 0, NULL, 1, 1),
(46, 0.00, NULL, 0, 47, 0, NULL, 1, 1),
(47, 0.00, NULL, 0, 48, 0, NULL, 1, 1),
(48, 0.00, NULL, 0, 50, 0, NULL, 1, 1),
(49, 0.00, NULL, 0, 52, 0, NULL, 1, 1),
(50, 0.00, NULL, 0, 54, 0, NULL, 1, 1),
(51, 0.00, NULL, 0, 56, 0, NULL, 1, 1),
(52, 0.00, NULL, 0, 57, 0, NULL, 1, 1),
(53, 0.00, NULL, 0, 59, 0, NULL, 1, 1),
(54, 0.00, NULL, 0, 60, 0, NULL, 1, 1),
(55, 0.00, NULL, 0, 61, 0, NULL, 1, 1),
(56, 0.00, NULL, 0, 62, 0, NULL, 1, 1),
(57, 0.00, NULL, 0, 63, 0, NULL, 1, 1),
(58, 0.00, NULL, 0, 64, 0, NULL, 1, 1),
(59, 0.00, NULL, 0, 65, 0, NULL, 1, 1),
(60, 0.00, NULL, 0, 66, 0, NULL, 1, 1),
(61, 0.00, NULL, 0, 67, 0, NULL, 1, 1),
(62, 0.00, NULL, 0, 68, 0, NULL, 1, 1),
(63, 0.00, NULL, 0, 69, 0, NULL, 1, 1),
(64, 0.00, NULL, 0, 70, 0, NULL, 1, 1),
(65, 0.00, NULL, 0, 71, 0, NULL, 1, 1),
(66, 0.00, NULL, 0, 72, 0, NULL, 1, 1),
(67, 0.00, NULL, 0, 73, 0, NULL, 1, 1),
(68, 0.00, NULL, 0, 74, 0, NULL, 1, 1),
(69, 0.00, NULL, 0, 75, 0, NULL, 1, 1),
(70, 0.00, NULL, 0, 76, 0, NULL, 1, 1),
(71, 0.00, NULL, 0, 77, 0, NULL, 1, 1),
(72, 0.00, NULL, 0, 78, 0, NULL, 1, 1),
(73, 0.00, NULL, 0, 79, 0, NULL, 1, 1),
(74, 0.00, NULL, 0, 80, 0, NULL, 1, 1),
(75, 0.00, NULL, 0, 81, 0, NULL, 1, 1),
(76, 0.00, NULL, 0, 83, 0, NULL, 1, 1),
(77, 0.00, NULL, 0, 84, 0, NULL, 1, 1),
(78, 0.00, NULL, 0, 85, 0, NULL, 1, 1),
(79, 0.00, NULL, 0, 86, 0, NULL, 1, 1),
(80, 0.00, NULL, 0, 87, 0, NULL, 1, 1),
(81, 0.00, NULL, 0, 88, 0, NULL, 1, 1),
(82, 0.00, NULL, 0, 89, 0, NULL, 1, 1),
(83, 0.00, NULL, 0, 90, 0, NULL, 1, 1),
(84, 0.00, NULL, 0, 92, 0, NULL, 1, 1),
(85, 0.00, NULL, 0, 93, 0, NULL, 1, 1),
(86, 0.00, NULL, 0, 94, 0, NULL, 1, 1),
(87, 0.00, NULL, 0, 95, 0, NULL, 1, 1),
(88, 0.00, NULL, 0, 96, 0, NULL, 1, 1),
(89, 0.00, NULL, 0, 97, 0, NULL, 1, 1),
(90, 0.00, NULL, 0, 98, 0, NULL, 1, 1),
(91, 0.00, NULL, 0, 99, 0, NULL, 1, 1),
(92, 0.00, NULL, 0, 100, 0, NULL, 1, 1),
(108, 0.00, NULL, 0, 114, 0, NULL, 1, 1),
(109, 0.00, NULL, 0, 115, 0, NULL, 1, 1),
(110, 0.00, NULL, 0, 116, 0, NULL, 1, 1),
(111, 0.00, NULL, 0, 117, 0, NULL, 1, 1),
(112, 0.00, NULL, 0, 118, 0, NULL, 1, 1),
(113, 0.00, NULL, 0, 119, 0, NULL, 1, 1),
(114, 0.00, NULL, 0, 120, 0, NULL, 1, 1),
(115, 0.00, NULL, 0, 121, 0, NULL, 1, 1),
(116, 0.00, NULL, 0, 122, 0, NULL, 1, 1),
(117, 0.00, NULL, 0, 123, 0, NULL, 1, 1),
(118, 0.00, NULL, 0, 124, 0, NULL, 1, 1),
(119, 0.00, NULL, 0, 125, 0, NULL, 1, 1),
(120, 0.00, NULL, 0, 126, 0, NULL, 1, 1),
(121, 0.00, NULL, 0, 127, 0, NULL, 1, 1),
(122, 0.00, NULL, 0, 128, 0, NULL, 1, 1),
(123, 0.00, NULL, 0, 129, 0, NULL, 1, 1),
(124, 0.00, NULL, 0, 130, 0, NULL, 1, 1),
(125, 0.00, NULL, 0, 131, 0, NULL, 1, 1),
(126, 0.00, NULL, 0, 132, 0, NULL, 1, 1),
(139, 5.00, NULL, 0, 133, 1, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_general`
--

CREATE TABLE `item_general` (
  `id_item_general` int NOT NULL,
  `nombre` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `codigo` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` tinyint DEFAULT NULL COMMENT '0 productos\\n1 materia prima\\n2 Insumos',
  `categoria_id` int DEFAULT NULL,
  `viscosidad` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `p_g` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `color` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `brillo_60` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `secado` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cubrimiento` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `molienda` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ph` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `poder_tintoreo` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unidad_id` int DEFAULT NULL,
  `costo_produccion` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `item_general`
--

INSERT INTO `item_general` (`id_item_general`, `nombre`, `codigo`, `tipo`, `categoria_id`, `viscosidad`, `p_g`, `color`, `brillo_60`, `secado`, `cubrimiento`, `molienda`, `ph`, `poder_tintoreo`, `unidad_id`, `costo_produccion`) VALUES
(1, 'BARNIZ TRANSPARENTE BRILLANTE', 'BAR001', 0, 4, '95-100 KU', '3,4+/-0,05 Kg', 'STD', '>=95', '12 HORAS', NULL, NULL, NULL, NULL, 1, 0.00),
(2, 'ESMALTE BLANCO', 'ESM002', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, 2, 7000.00),
(3, 'ESMALTE CAOBA', 'ESM003', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '6 HORAS', '100+/-5%', '7.5 H', NULL, NULL, 3, 11000.00),
(4, 'ESMALTE NEGRO MATE', 'ESM004', 0, 1, '105-110 KU', '3,9+/-0,05 Kg', NULL, '<=15', '12 HORAS', '100+/-5%', '6 H', NULL, NULL, 4, 34050.00),
(5, 'ESMALTE ROJO FIESTA', 'ESM005', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>= 90°', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, NULL, 27144.00),
(6, 'ESMALTE NEGRO BRILLANTE', 'ESM006', 0, 1, '100-105 KU', '3.4+/-0.05 Kg', NULL, '>= 90', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, NULL, 12691.00),
(7, 'ESMALTE VERDE ESMERALDA', 'ESM007', 0, 1, '100-105 KU', '3.6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, NULL, 4372.00),
(8, 'ESMALTE GRIS PLATA', 'ESM008', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, NULL, 11466.00),
(9, 'ESMALTE AZUL ESPAÑOL', 'ESM009', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, NULL, 16300.00),
(10, 'ESMALTE BLANCO MATE', 'ESM010', 0, 1, '95-100', '4,2 +/- 0,1 Kg', NULL, '15', '12 HORAS', '100+/-5', '6 H', NULL, NULL, NULL, 17000.00),
(11, 'ESMALTE AMARILLO', 'ESM011', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, NULL, 4400.00),
(12, 'ESMALTE NARANJA', 'ESM012', 0, 1, '100-105', '3.5+/-0.05', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, NULL, 14300.00),
(13, 'ESMALTE TABACO', 'ESM013', 0, 1, '100-105KU', '3.5+/-0.05', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, NULL, 40.00),
(14, 'ANTICORROSIVO GRIS', 'ANT014', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, NULL, 1550.00),
(15, 'ANTICORROSIVO NEGRO', 'ANT015', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, NULL, 4617.00),
(16, 'ANTICORROSIVO AMARILLO', 'ANT016', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, NULL, 8640.00),
(17, 'ANTICORROSIVO ROJO', 'ANT017', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, NULL, 14300.00),
(18, 'ANTICORROSIVO BLANCO', 'ANT018', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, NULL, 855.00),
(19, 'ANTICORROSIVO VERDE', 'ANT019', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, NULL, 5400.00),
(20, 'PASTA ESMALTE VERDE ENTONADOR', 'PAS020', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8105.00),
(21, 'PASTA ESMALTE AZUL ENTONADOR', 'PAS021', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 12215.00),
(22, 'PASTA ESMALTE NEGRO', 'PAS022', 2, 2, '100 KU', '4,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', NULL, 19945.00),
(23, 'PASTA ESMALTE ROJO CARMIN 57:1', 'PAS023', 0, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', NULL, 14152.00),
(24, 'PASTA ESMALTE NARANJA', 'PAS024', 2, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', NULL, 11447.00),
(25, 'PASTA ESMALTE AMARILLO', 'PAS025', 0, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', NULL, 12718.00),
(26, 'PASTA ESMALTE CAOBA', 'PAS026', 2, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', NULL, 7742.00),
(27, 'PASTA ESMALTE AMARILLO OXIDO', 'PAS027', 2, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', NULL, 11447.00),
(28, 'PASTA ESMALTE ROJO OXIDO', 'PAS028', 0, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', NULL, 1690.00),
(29, 'PASTA ESMALTE BLANCO', 'PAS029', 0, 2, '120', '5,78', 'STD', NULL, NULL, NULL, '7,5', '-', '100 +/- 0.5 %', NULL, 10303.00),
(30, 'PASTA ESMALTE TABACO', 'PAS030', 2, 2, '95-100', '5.71-5.91', 'STD', NULL, NULL, NULL, '7,5', '-', 'STD', NULL, 722.00),
(31, 'RESINA MEDIA EN SOYA AL 50%', 'RAM014', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 715.00),
(32, 'METIL ETIL CETOXIMA', 'AAN002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4300.00),
(33, 'OCTOATO DE COBALTO AL 12%', 'SOC011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4400.00),
(34, 'OCTOATO DE ZIRCONIO AL 24%', 'SOZ024', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00),
(35, 'OCTOATO DE CALCIO AL 10%', 'SOC010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00),
(36, 'DISOLVENTE 2232 #3', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1103.00),
(37, 'DIOXIDO DE TITANIO SULFATO', 'PED010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(38, 'OCTOATO DE ZINC AL 16%', 'SOZ016', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 43900.00),
(39, 'BENTOCLAY BP 184', 'AAS005', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 37300.00),
(40, 'ETANOL AL 96%', 'SAA022', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(41, 'DISASTAB GAT', 'AEM005', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7000.00),
(42, 'AGUA', 'SIA040', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19500.00),
(43, 'SULFATO DE MAGNESIO', 'AET004', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 33500.00),
(44, 'VARSOL', 'SAV010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 37200.00),
(46, 'DISASTAB GAT', 'AEM004', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10400.00),
(47, 'MICROTALC C 20', 'CTA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00),
(48, 'CELITE 499', 'MSI006', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11466.00),
(50, 'PASTA ESMALTE ROJO 57:1', 'PE1033', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17000.00),
(52, 'PASTA AMARILLO CROMO MEDIO', 'PE1010', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17000.00),
(54, 'PASTA VERDE FTALO', 'PE1040', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4617.00),
(56, 'PASTA ESMALTE AZUL FTALO 15:3', 'PE1021', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(57, 'OMYACARB UF', 'CCC002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11000.00),
(59, 'MICROTALC C 20', 'CTA025', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(60, 'CARBONATO DE CALCIO HI WHITE', 'CCC004', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(61, 'LECITINA DE SOYA', 'AHU002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(62, 'ETANOL AL 96%', 'SAM023', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(63, 'OXIDO DE HIERRO AMARILLO Y 4021', 'PEA010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(64, 'OXIDO DE HIERRO ROJO R-5530', 'PER030', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(65, 'MICROTALC 20', 'CTA020', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(66, 'TROYSPERSE CD1', 'ADI002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(67, 'PIGMENTO VERDE FTALO 7', 'PEV053', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(68, 'PIGMENTO AZUL FTALO 15;3', 'PEA041', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(69, 'EDAPLAN 918 / LANSPERSE SUV', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(70, 'RESINA MEDIA EN SOYA AL 50%', 'MS-45', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(71, 'POW CARBON BLACK CHEMO', 'PEN081', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(72, 'PIGMENTO ROJO CARMIN 57:1', 'PER031', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(73, 'PIGMENTO NARANJA MOLIBDENO', 'PEN023', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(74, 'PIGMENTO MARILLO DE CROMO AL 73', 'PEA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(75, 'PIGMENTO OXIFERR CAOBA MARRON M 4781', 'PEC081', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(76, 'PIGMENTO OXIFERR AMARILLO Y-4011', 'PEA013', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(77, 'DIOXIDO DE TITANIO SULFATO 2196', 'PED007', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(78, 'OXIFER TABACO R-4370', 'PET080', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8105.00),
(79, 'BENTOCLAY BP 184', 'AAS012', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(80, 'METANOL', 'SAM023', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(81, 'ORGANOCLAY BK 884', 'AAS005', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(83, 'DISOLVENTE 2232 / VARSOL', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(84, 'EDAPLAN 915', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(85, 'CHEMOSPERSE 77', 'ADI011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(86, 'ADIMON 84', 'AAN002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(87, 'DISOLVENTE #3', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4372.00),
(88, 'ETANOL 96%', 'SAA022', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4400.00),
(89, 'DISOLVENTE 2232', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4372.00),
(90, 'DISOLVENTE 3', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4372.00),
(92, 'OCTOATO DE ZINC 16%', 'SOZ016', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 16300.00),
(93, 'PASTA ESMALTE AMARILLO CROMO MEDIO', 'PE1010', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 14152.00),
(94, 'DIOXIDO DE TITANIO SULFATO 2196', 'PED010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11466.00),
(95, 'BENTOCLAY BP184', 'AAS005', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17000.00),
(96, 'PASTA ESMALTE AZUL 15:3', 'PE1021', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11447.00),
(97, 'EDAPLAN 918', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(98, 'EDAPLAN 918 / LANSPERSE SUV', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(99, 'CHEMOSPERSE 77', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(100, 'PIGMENTO OXIFERR ROJO R-5530', 'PER030', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(114, 'Agua (MP)', 'MP-001', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(115, 'TPF (MP)', 'MP-002', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(116, 'Dispersante (MP)', 'MP-003', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(117, 'Masellose (MP)', 'MP-004', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(118, 'Tergitol (MP)', 'MP-005', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(119, 'Dietilen (MP)', 'MP-006', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(120, 'Texanol (MP)', 'MP-007', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(121, 'Antiespumante (MP)', 'MP-008', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(122, 'Titanio (MP)', 'MP-009', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(123, 'Carbonato de calcio (MP)', 'MP-010', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(124, 'Talco Ty 400 (MP)', 'MP-011', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(125, 'Caolin (MP)', 'MP-012', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(126, 'Carbonato UF (MP)', 'MP-013', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(127, 'Acronal (MP)', 'MP-014', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(128, 'Bactericida (MP)', 'MP-015', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(129, 'Aceite Pino (MP)', 'MP-016', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(130, 'Aisol 700 (MP)', 'MP-017', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(131, 'Amoniaco (MP)', 'MP-018', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(132, 'Fungicida (MP)', 'MP-019', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(133, 'VINILO T1 BLANCO', 'EBT012', 0, 1, '', '', '', '', '', '', '', '', '', NULL, 1.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_general_formulaciones`
--

CREATE TABLE `item_general_formulaciones` (
  `id_item_general_formulaciones` int NOT NULL,
  `formulaciones_id` int NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `porcentaje` int DEFAULT NULL,
  `item_general_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `item_general_formulaciones`
--

INSERT INTO `item_general_formulaciones` (`id_item_general_formulaciones`, `formulaciones_id`, `cantidad`, `porcentaje`, `item_general_id`) VALUES
(1, 2, 914.00, NULL, 31),
(2, 1, 932.00, NULL, 31),
(3, 1, 3.72, NULL, 32),
(4, 1, 6.52, NULL, 33),
(5, 1, 10.25, NULL, 34),
(6, 1, 9.32, NULL, 35),
(7, 1, 301.00, NULL, 36),
(8, 2, 425.00, NULL, 31),
(9, 2, 293.00, NULL, 37),
(10, 2, 2.63, NULL, 38),
(11, 2, 16.00, NULL, 39),
(12, 2, 8.00, NULL, 40),
(13, 2, 14.20, NULL, 41),
(14, 2, 470.00, NULL, 42),
(15, 2, 4.70, NULL, 43),
(16, 2, 5.20, NULL, 86),
(17, 2, 9.37, NULL, 33),
(18, 2, 14.72, NULL, 34),
(19, 2, 13.40, NULL, 35),
(20, 2, 197.00, NULL, 36),
(21, 2, 200.00, NULL, 44),
(22, 3, 775.00, NULL, 31),
(23, 3, 103.00, NULL, 26),
(24, 3, 8.70, NULL, 41),
(25, 3, 290.00, NULL, 42),
(26, 3, 3.00, NULL, 43),
(27, 3, 3.30, NULL, 86),
(28, 3, 5.78, NULL, 33),
(29, 3, 9.10, NULL, 34),
(30, 3, 8.26, NULL, 35),
(31, 3, 113.00, NULL, 36),
(32, 3, 114.00, NULL, 44),
(33, 4, 775.00, NULL, 31),
(34, 4, 224.00, NULL, 47),
(35, 4, 40.00, NULL, 48),
(36, 4, 12.00, NULL, 81),
(37, 4, 6.00, NULL, 40),
(38, 4, 125.00, NULL, 22),
(39, 4, 8.70, NULL, 41),
(40, 4, 290.00, NULL, 42),
(41, 4, 2.90, NULL, 43),
(42, 4, 3.35, NULL, 86),
(43, 4, 5.86, NULL, 33),
(44, 4, 9.21, NULL, 34),
(45, 4, 8.37, NULL, 35),
(46, 4, 227.00, NULL, 44),
(47, 5, 775.00, NULL, 31),
(48, 5, 36.56, NULL, 50),
(49, 5, 79.40, NULL, 24),
(50, 5, 6.00, NULL, 41),
(51, 5, 200.00, NULL, 42),
(52, 5, 2.00, NULL, 43),
(53, 5, 3.33, NULL, 86),
(54, 5, 5.83, NULL, 33),
(55, 5, 9.16, NULL, 34),
(56, 5, 8.32, NULL, 35),
(57, 5, 227.00, NULL, 36),
(58, 6, 775.00, NULL, 31),
(59, 6, 125.00, NULL, 22),
(60, 6, 5.70, NULL, 41),
(61, 6, 190.00, NULL, 42),
(62, 6, 1.90, NULL, 43),
(63, 6, 3.35, NULL, 86),
(64, 6, 5.86, NULL, 33),
(65, 6, 9.21, NULL, 34),
(66, 6, 8.37, NULL, 35),
(67, 6, 227.00, NULL, 44),
(68, 7, 775.00, NULL, 31),
(69, 7, 62.00, NULL, 52),
(70, 7, 10.40, NULL, 56),
(71, 7, 108.00, NULL, 54),
(72, 7, 6.20, NULL, 41),
(73, 7, 205.00, NULL, 42),
(74, 7, 2.10, NULL, 43),
(75, 7, 3.46, NULL, 86),
(76, 7, 6.05, NULL, 33),
(77, 7, 9.51, NULL, 34),
(78, 7, 8.65, NULL, 35),
(79, 7, 113.00, NULL, 36),
(80, 7, 114.00, NULL, 44),
(81, 8, 425.00, NULL, 31),
(82, 8, 251.00, NULL, 37),
(83, 8, 2.63, NULL, 38),
(84, 8, 16.00, NULL, 39),
(85, 8, 8.00, NULL, 40),
(86, 8, 3.30, NULL, 27),
(87, 8, 17.00, NULL, 22),
(88, 8, 14.20, NULL, 41),
(89, 8, 470.00, NULL, 42),
(90, 8, 4.70, NULL, 43),
(91, 8, 5.20, NULL, 86),
(92, 8, 9.37, NULL, 33),
(93, 8, 14.72, NULL, 34),
(94, 8, 13.40, NULL, 35),
(95, 8, 197.00, NULL, 36),
(96, 8, 200.00, NULL, 44),
(97, 9, 225.00, NULL, 31),
(98, 9, 56.00, NULL, 37),
(99, 9, 0.70, NULL, 38),
(100, 9, 2.00, NULL, 39),
(101, 9, 1.00, NULL, 40),
(102, 9, 168.00, NULL, 56),
(103, 9, 11.20, NULL, 50),
(104, 9, 9.70, NULL, 41),
(105, 9, 323.00, NULL, 42),
(106, 9, 3.23, NULL, 43),
(107, 9, 5.40, NULL, 86),
(108, 9, 9.45, NULL, 33),
(109, 9, 14.86, NULL, 34),
(110, 9, 13.51, NULL, 35),
(111, 9, 197.00, NULL, 36),
(112, 9, 165.00, NULL, 44),
(113, 10, 1173.00, NULL, 31),
(114, 10, 288.00, NULL, 37),
(115, 10, 435.00, NULL, 57),
(116, 10, 84.00, NULL, 48),
(117, 10, 5.00, NULL, 38),
(118, 10, 25.00, NULL, 39),
(119, 10, 10.00, NULL, 40),
(120, 10, 14.30, NULL, 41),
(121, 10, 477.00, NULL, 42),
(122, 10, 4.80, NULL, 43),
(123, 10, 4.69, NULL, 86),
(124, 10, 8.20, NULL, 33),
(125, 10, 12.90, NULL, 34),
(126, 10, 11.70, NULL, 35),
(127, 10, 433.00, NULL, 44),
(128, 11, 1033.00, NULL, 31),
(129, 11, 294.70, NULL, 52),
(130, 11, 11.13, NULL, 41),
(131, 11, 371.00, NULL, 42),
(132, 11, 3.70, NULL, 43),
(133, 11, 4.72, NULL, 86),
(134, 11, 8.26, NULL, 33),
(135, 11, 13.00, NULL, 34),
(136, 11, 11.81, NULL, 35),
(137, 11, 391.00, NULL, 44),
(138, 12, 1033.00, NULL, 31),
(139, 12, 180.00, NULL, 24),
(140, 12, 77.00, NULL, 52),
(141, 12, 11.00, NULL, 41),
(142, 12, 363.00, NULL, 42),
(143, 12, 3.66, NULL, 43),
(144, 12, 4.64, NULL, 86),
(145, 12, 8.13, NULL, 33),
(146, 12, 12.77, NULL, 34),
(147, 12, 11.61, NULL, 35),
(148, 12, 391.00, NULL, 44),
(149, 13, 1033.00, NULL, 31),
(150, 13, 190.00, NULL, 30),
(151, 13, 11.00, NULL, 41),
(152, 13, 363.00, NULL, 42),
(153, 13, 3.60, NULL, 43),
(154, 13, 4.50, NULL, 86),
(155, 13, 7.90, NULL, 33),
(156, 13, 12.40, NULL, 34),
(157, 13, 11.30, NULL, 35),
(158, 13, 391.00, NULL, 44),
(159, 14, 1056.00, NULL, 31),
(160, 14, 186.00, NULL, 77),
(161, 14, 848.00, NULL, 59),
(162, 14, 70.00, NULL, 60),
(163, 14, 5.00, NULL, 61),
(164, 14, 25.00, NULL, 39),
(165, 14, 5.00, NULL, 40),
(166, 14, 17.80, NULL, 41),
(167, 14, 593.00, NULL, 42),
(168, 14, 5.93, NULL, 43),
(169, 14, 4.30, NULL, 86),
(170, 14, 7.40, NULL, 33),
(171, 14, 11.60, NULL, 34),
(172, 14, 10.60, NULL, 35),
(173, 14, 20.00, NULL, 22),
(174, 14, 550.00, NULL, 44),
(175, 15, 256.00, NULL, 31),
(176, 15, 37.00, NULL, 22),
(177, 15, 2.30, NULL, 61),
(178, 15, 46.00, NULL, 60),
(179, 15, 132.00, NULL, 59),
(180, 15, 4.00, NULL, 79),
(181, 15, 2.00, NULL, 40),
(182, 15, 3.70, NULL, 41),
(183, 15, 123.00, NULL, 42),
(184, 15, 1.30, NULL, 43),
(185, 15, 1.10, NULL, 86),
(186, 15, 2.00, NULL, 33),
(187, 15, 3.00, NULL, 34),
(188, 15, 2.80, NULL, 35),
(189, 15, 89.60, NULL, 44),
(190, 16, 274.00, NULL, 31),
(191, 16, 47.00, NULL, 63),
(192, 16, 220.00, NULL, 59),
(193, 16, 18.00, NULL, 60),
(194, 16, 1.30, NULL, 61),
(195, 16, 6.50, NULL, 39),
(196, 16, 4.00, NULL, 40),
(197, 16, 4.80, NULL, 41),
(198, 16, 160.00, NULL, 42),
(199, 16, 1.60, NULL, 43),
(200, 16, 1.10, NULL, 86),
(201, 16, 1.92, NULL, 33),
(202, 16, 3.00, NULL, 34),
(203, 16, 2.74, NULL, 35),
(204, 16, 142.60, NULL, 44),
(205, 17, 274.00, NULL, 31),
(206, 17, 58.00, NULL, 64),
(207, 17, 220.00, NULL, 59),
(208, 17, 18.00, NULL, 60),
(209, 17, 1.30, NULL, 61),
(210, 17, 6.50, NULL, 39),
(211, 17, 4.00, NULL, 40),
(212, 17, 4.70, NULL, 41),
(213, 17, 155.60, NULL, 42),
(214, 17, 1.55, NULL, 43),
(215, 17, 1.10, NULL, 86),
(216, 17, 1.92, NULL, 33),
(217, 17, 3.00, NULL, 34),
(218, 17, 2.74, NULL, 35),
(219, 17, 142.60, NULL, 44),
(220, 18, 1056.00, NULL, 31),
(221, 18, 165.00, NULL, 77),
(222, 18, 230.00, NULL, 65),
(223, 18, 688.00, NULL, 60),
(224, 18, 5.00, NULL, 38),
(225, 18, 25.00, NULL, 39),
(226, 18, 5.00, NULL, 40),
(227, 18, 17.55, NULL, 41),
(228, 18, 585.26, NULL, 42),
(229, 18, 5.85, NULL, 43),
(230, 18, 4.30, NULL, 86),
(231, 18, 7.40, NULL, 33),
(232, 18, 11.60, NULL, 34),
(233, 18, 10.60, NULL, 35),
(234, 18, 550.00, NULL, 44),
(235, 19, 256.00, NULL, 31),
(236, 19, 36.00, NULL, 77),
(237, 19, 10.00, NULL, 63),
(238, 19, 20.00, NULL, 96),
(239, 19, 3.00, NULL, 22),
(240, 19, 2.30, NULL, 61),
(241, 19, 46.00, NULL, 60),
(242, 19, 132.00, NULL, 59),
(243, 19, 4.00, NULL, 39),
(244, 19, 2.00, NULL, 40),
(245, 19, 3.90, NULL, 41),
(246, 19, 130.00, NULL, 42),
(247, 19, 1.30, NULL, 43),
(248, 19, 1.10, NULL, 86),
(249, 19, 2.00, NULL, 33),
(250, 19, 3.00, NULL, 34),
(251, 19, 2.80, NULL, 35),
(252, 19, 89.60, NULL, 44),
(253, 20, 186.00, NULL, 31),
(254, 20, 3.00, NULL, 32),
(255, 20, 3.00, NULL, 39),
(256, 20, 8.00, NULL, 66),
(257, 20, 50.00, NULL, 67),
(258, 20, 2.00, NULL, 40),
(259, 20, 76.00, NULL, 44),
(260, 21, 186.00, NULL, 31),
(261, 21, 3.00, NULL, 32),
(262, 21, 5.00, NULL, 79),
(263, 21, 3.00, NULL, 80),
(264, 21, 15.00, NULL, 61),
(265, 21, 52.00, NULL, 68),
(266, 21, 5.00, NULL, 97),
(267, 21, 76.00, NULL, 44),
(268, 22, 242.00, NULL, 31),
(269, 22, 3.10, NULL, 86),
(270, 22, 9.00, NULL, 97),
(271, 22, 25.00, NULL, 61),
(272, 22, 59.00, NULL, 71),
(273, 23, 55.00, NULL, 31),
(274, 23, 0.80, NULL, 39),
(275, 23, 0.40, NULL, 80),
(276, 23, 0.25, NULL, 86),
(277, 23, 2.80, NULL, 85),
(278, 23, 1.60, NULL, 61),
(279, 23, 24.00, NULL, 72),
(280, 23, 34.00, NULL, 44),
(281, 24, 332.00, NULL, 31),
(282, 24, 9.00, NULL, 39),
(283, 24, 5.00, NULL, 80),
(284, 24, 3.10, NULL, 86),
(285, 24, 35.00, NULL, 85),
(286, 24, 18.90, NULL, 61),
(287, 24, 408.00, NULL, 73),
(288, 24, 150.00, NULL, 44),
(289, 25, 332.00, NULL, 31),
(290, 25, 9.00, NULL, 39),
(291, 25, 5.00, NULL, 80),
(292, 25, 3.10, NULL, 86),
(293, 25, 18.90, NULL, 61),
(294, 25, 465.00, NULL, 74),
(295, 25, 150.00, NULL, 44),
(296, 26, 295.00, NULL, 31),
(297, 26, 6.00, NULL, 39),
(298, 26, 3.00, NULL, 80),
(299, 26, 3.10, NULL, 86),
(300, 26, 35.00, NULL, 97),
(301, 26, 18.90, NULL, 61),
(302, 26, 340.00, NULL, 75),
(303, 26, 173.00, NULL, 44),
(304, 27, 295.00, NULL, 31),
(305, 27, 6.00, NULL, 39),
(306, 27, 3.00, NULL, 80),
(307, 27, 3.10, NULL, 86),
(308, 27, 18.90, NULL, 61),
(309, 27, 340.00, NULL, 76),
(310, 27, 150.00, NULL, 36),
(311, 28, 295.00, NULL, 31),
(312, 28, 6.00, NULL, 39),
(313, 28, 3.00, NULL, 80),
(314, 28, 3.10, NULL, 86),
(315, 28, 17.00, NULL, 97),
(316, 28, 18.90, NULL, 61),
(317, 28, 340.00, NULL, 100),
(318, 28, 150.00, NULL, 36),
(319, 29, 213.00, NULL, 31),
(320, 29, 22.00, NULL, 39),
(321, 29, 4.00, NULL, 66),
(322, 29, 5.00, NULL, 40),
(323, 29, 441.00, NULL, 37),
(324, 29, 63.00, NULL, 44),
(325, 30, 1.00, NULL, 86),
(326, 30, 185.00, NULL, 78),
(327, 30, 134.00, NULL, 31),
(328, 30, 6.00, NULL, 66),
(329, 30, 8.00, NULL, 39),
(330, 30, 7.00, NULL, 61),
(331, 30, 33.00, NULL, 44),
(332, 30, 2.00, NULL, 40),
(333, 25, 35.00, NULL, 84),
(334, 27, 35.00, NULL, 84),
(335, 22, 150.00, NULL, 83),
(366, 50, 5.00, 0, 35),
(367, 50, 10.00, 0, 47);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_proveedor`
--

CREATE TABLE `item_proveedor` (
  `id_item_proveedor` int NOT NULL,
  `nombre` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `codigo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unidad_empaque` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `precio_con_iva` decimal(10,2) DEFAULT NULL,
  `disponible` tinyint DEFAULT NULL COMMENT '1 Disponible 2 No disponible',
  `descripcion` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `proveedor_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `item_proveedor`
--

INSERT INTO `item_proveedor` (`id_item_proveedor`, `nombre`, `codigo`, `tipo`, `unidad_empaque`, `precio_unitario`, `precio_con_iva`, `disponible`, `descripcion`, `proveedor_id`) VALUES
(6, 'Tubería PVC 1/2\" x 6m', 'PVC-12-6', 'Fontanería', 'Kg', 5000.00, 0.00, 1, 'Tubería de PVC para conducción de agua fría', 2),
(7, 'Codo PVC 1/2\" 90°', 'CDO-12-90', 'Fontanería', 'Kg', 2000.00, 1002.00, 1, 'Codo de PVC para unión de tuberías en ángulo recto', 2),
(8, 'Brocha 3 Pulgadas Profesional', 'BRC-3P', 'Herramientas ', 'Kg', 200.00, 500.00, 1, 'Brocha de cerdas sintéticas ideal para pintura acrílica', 2),
(9, 'Rodillo de Lana 9\"', 'RDL-9L', 'Herramientas', 'Kg', 0.00, 0.00, 1, 'Rodillo de lana para pintura en superficies rugosas', 2),
(10, 'Lija de Agua 220', 'LJ-220', 'Abrasivos', 'Kg', 0.00, 0.00, 1, 'Lija fina para acabado de superficies pintadas', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_inventario`
--

CREATE TABLE `movimiento_inventario` (
  `id_movimiento_inventario` int NOT NULL,
  `tipo_movimiento` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `fecha_movimiento` date DEFAULT NULL,
  `descripcion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `referencia_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `movimiento_inventario`
--

INSERT INTO `movimiento_inventario` (`id_movimiento_inventario`, `tipo_movimiento`, `cantidad`, `fecha_movimiento`, `descripcion`, `referencia_tipo`) VALUES
(6, 'Entrada', 120.00, '2025-01-10', 'Compra de materiales para producción de pintura', 'COMPRA-2025-001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_cliente`
--

CREATE TABLE `pagos_cliente` (
  `id_pagos_cliente` int NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `monto` decimal(7,1) DEFAULT NULL,
  `metodo_pago` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `observaciones` varchar(0) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `clientes_id` int DEFAULT NULL,
  `facturas_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preparaciones`
--

CREATE TABLE `preparaciones` (
  `id_preparaciones` int NOT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `item_general_id` int DEFAULT NULL,
  `unidad_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preparaciones_has_item_general`
--

CREATE TABLE `preparaciones_has_item_general` (
  `preparaciones_id_preparaciones` int NOT NULL,
  `item_general_id_item_general` int NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `porcentajes` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id_proveedor` int NOT NULL,
  `nombre_encargado` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre_empresa` varchar(27) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero_documento` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`id_proveedor`, `nombre_encargado`, `nombre_empresa`, `numero_documento`, `direccion`, `telefono`, `email`) VALUES
(2, 'Camila Peñaranda', 'Solventes Industriales Ltd', '800987654-2', 'Carrera 9 #12-34, Medellín', '3152345678', 'camila.penaranda@solventes.co'),
(8, 'Carlos Pérez', 'Aquaterra S.A.S.', '178231745-2', '', '01 8000 510 99', 'servilab@aquaterra.com.co');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidad`
--

CREATE TABLE `unidad` (
  `id_unidad` int NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `estados` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `unidad`
--

INSERT INTO `unidad` (`id_unidad`, `nombre`, `descripcion`, `estados`) VALUES
(1, 'TAMBOR', '', 1),
(2, 'CUÑETE', '', 1),
(3, 'GALON', '', 1),
(4, '1/2 GALON', '', 1),
(5, '1/4 GALON', '', 1),
(6, '1/8 GALON', '', 1),
(7, '1/16 GALON', '', 1),
(8, '1/32 GALON', '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuarios` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuarios`, `username`, `password`) VALUES
(2, 'root', '$2y$10$zcSxsrQHkHFxPddPk/.TFeeFceYqtUeb3wtlLSxfnDG4Ll5dL1Szu');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bodegas`
--
ALTER TABLE `bodegas`
  ADD PRIMARY KEY (`id_bodegas`),
  ADD KEY `fk_bodegas_instalaciones1_idx` (`instalaciones_id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_clientes`);

--
-- Indices de la tabla `costos_item`
--
ALTER TABLE `costos_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_general_id` (`item_general_id`);

--
-- Indices de la tabla `costos_produccion`
--
ALTER TABLE `costos_produccion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD KEY `fk_costos_produccion_preparaciones1_idx` (`preparaciones_id`);

--
-- Indices de la tabla `detalle_facturas`
--
ALTER TABLE `detalle_facturas`
  ADD PRIMARY KEY (`id_detalle_facturas`),
  ADD UNIQUE KEY `id_detalle_facturas_UNIQUE` (`id_detalle_facturas`),
  ADD KEY `fk_detalle_facturas_facturas1_idx` (`facturas_id`),
  ADD KEY `fk_detalle_facturas_item_general1_idx` (`item_general_id`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id_facturas`),
  ADD UNIQUE KEY `id_facturas_UNIQUE` (`id_facturas`),
  ADD KEY `fk_facturas_movimientos_inventario1_idx` (`movimiento_inventario_id`);

--
-- Indices de la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  ADD PRIMARY KEY (`id_formulaciones`),
  ADD UNIQUE KEY `id_formulaciones_UNIQUE` (`id_formulaciones`),
  ADD KEY `fk_formulaciones_item_general1_idx` (`item_general_id`);

--
-- Indices de la tabla `instalaciones`
--
ALTER TABLE `instalaciones`
  ADD PRIMARY KEY (`id_instalaciones`),
  ADD KEY `fk_instalaciones_empresa_idx` (`id_empresa`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_inventario`),
  ADD KEY `fk_inventario_item_general1_idx` (`item_general_id`),
  ADD KEY `fk_inventario_movimientos_inventario1_idx` (`movimiento_inventario_id`),
  ADD KEY `fk_inventario_bodega` (`bodegas_id`);

--
-- Indices de la tabla `item_general`
--
ALTER TABLE `item_general`
  ADD PRIMARY KEY (`id_item_general`),
  ADD UNIQUE KEY `id_item_general_UNIQUE` (`id_item_general`),
  ADD KEY `fk_item_general_categoria1_idx` (`categoria_id`),
  ADD KEY `fk_item_general_unidad_id_idx` (`unidad_id`);

--
-- Indices de la tabla `item_general_formulaciones`
--
ALTER TABLE `item_general_formulaciones`
  ADD PRIMARY KEY (`id_item_general_formulaciones`),
  ADD KEY `fk_item_especifico_has_formulaciones_formulaciones1_idx` (`formulaciones_id`),
  ADD KEY `fk_item_especifico_formulaciones_item_general1_idx` (`item_general_id`);

--
-- Indices de la tabla `item_proveedor`
--
ALTER TABLE `item_proveedor`
  ADD PRIMARY KEY (`id_item_proveedor`),
  ADD UNIQUE KEY `id_item_proveedor_UNIQUE` (`id_item_proveedor`),
  ADD KEY `fk_item_proveedor_proveedores1_idx` (`proveedor_id`);

--
-- Indices de la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD PRIMARY KEY (`id_movimiento_inventario`),
  ADD UNIQUE KEY `id_movimiento_inventario_UNIQUE` (`id_movimiento_inventario`);

--
-- Indices de la tabla `pagos_cliente`
--
ALTER TABLE `pagos_cliente`
  ADD PRIMARY KEY (`id_pagos_cliente`),
  ADD UNIQUE KEY `id_pagos_cliente_UNIQUE` (`id_pagos_cliente`),
  ADD KEY `fk_pagos_cliente_clientes1_idx` (`clientes_id`),
  ADD KEY `fk_pagos_cliente_facturas1_idx` (`facturas_id`);

--
-- Indices de la tabla `preparaciones`
--
ALTER TABLE `preparaciones`
  ADD PRIMARY KEY (`id_preparaciones`),
  ADD KEY `fk_preparaciones_item_general1_idx` (`item_general_id`),
  ADD KEY `fk_preparaciones_unidad1_idx` (`unidad_id`);

--
-- Indices de la tabla `preparaciones_has_item_general`
--
ALTER TABLE `preparaciones_has_item_general`
  ADD PRIMARY KEY (`preparaciones_id_preparaciones`,`item_general_id_item_general`),
  ADD KEY `fk_preparaciones_has_item_general_item_general1_idx` (`item_general_id_item_general`),
  ADD KEY `fk_preparaciones_has_item_general_preparaciones1_idx` (`preparaciones_id_preparaciones`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `id_proveedor_UNIQUE` (`id_proveedor`);

--
-- Indices de la tabla `unidad`
--
ALTER TABLE `unidad`
  ADD PRIMARY KEY (`id_unidad`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuarios`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bodegas`
--
ALTER TABLE `bodegas`
  MODIFY `id_bodegas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_clientes` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `costos_item`
--
ALTER TABLE `costos_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT de la tabla `costos_produccion`
--
ALTER TABLE `costos_produccion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_facturas`
--
ALTER TABLE `detalle_facturas`
  MODIFY `id_detalle_facturas` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_facturas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  MODIFY `id_formulaciones` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `instalaciones`
--
ALTER TABLE `instalaciones`
  MODIFY `id_instalaciones` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id_inventario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT de la tabla `item_general`
--
ALTER TABLE `item_general`
  MODIFY `id_item_general` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT de la tabla `item_general_formulaciones`
--
ALTER TABLE `item_general_formulaciones`
  MODIFY `id_item_general_formulaciones` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=368;

--
-- AUTO_INCREMENT de la tabla `item_proveedor`
--
ALTER TABLE `item_proveedor`
  MODIFY `id_item_proveedor` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  MODIFY `id_movimiento_inventario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pagos_cliente`
--
ALTER TABLE `pagos_cliente`
  MODIFY `id_pagos_cliente` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preparaciones`
--
ALTER TABLE `preparaciones`
  MODIFY `id_preparaciones` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id_proveedor` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `unidad`
--
ALTER TABLE `unidad`
  MODIFY `id_unidad` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuarios` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bodegas`
--
ALTER TABLE `bodegas`
  ADD CONSTRAINT `fk_bodegas_instalaciones1` FOREIGN KEY (`instalaciones_id`) REFERENCES `instalaciones` (`id_instalaciones`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `costos_item`
--
ALTER TABLE `costos_item`
  ADD CONSTRAINT `costos_item_ibfk_1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `costos_produccion`
--
ALTER TABLE `costos_produccion`
  ADD CONSTRAINT `fk_costos_produccion_preparaciones` FOREIGN KEY (`preparaciones_id`) REFERENCES `preparaciones` (`id_preparaciones`);

--
-- Filtros para la tabla `detalle_facturas`
--
ALTER TABLE `detalle_facturas`
  ADD CONSTRAINT `fk_detalle_facturas_facturas1` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`),
  ADD CONSTRAINT `fk_detalle_facturas_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_facturas_movimientos_inventario1` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimiento_inventario` (`id_movimiento_inventario`);

--
-- Filtros para la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  ADD CONSTRAINT `fk_formulaciones_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Filtros para la tabla `instalaciones`
--
ALTER TABLE `instalaciones`
  ADD CONSTRAINT `fk_instalaciones_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `fk_inventario_bodega` FOREIGN KEY (`bodegas_id`) REFERENCES `bodegas` (`id_bodegas`),
  ADD CONSTRAINT `fk_inventario_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_inventario_movimientos_inventario1` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimiento_inventario` (`id_movimiento_inventario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
