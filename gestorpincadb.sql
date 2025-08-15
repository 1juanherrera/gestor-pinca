-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-08-2025 a las 07:28:00
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

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
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(13) DEFAULT NULL
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
  `id_clientes` int(11) NOT NULL,
  `nombre_encargado` varchar(13) DEFAULT NULL,
  `nombre_empresa` varchar(11) DEFAULT NULL,
  `numero_documento` bigint(20) DEFAULT NULL,
  `direccion` varchar(14) DEFAULT NULL,
  `telefono` bigint(20) DEFAULT NULL,
  `email` varchar(29) DEFAULT NULL,
  `facturas_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos_item`
--

CREATE TABLE `costos_item` (
  `id` int(11) NOT NULL,
  `item_general_id` int(11) NOT NULL,
  `costo_unitario` int(11) DEFAULT NULL,
  `costo_mp_galon` int(11) DEFAULT NULL,
  `periodo` varchar(7) DEFAULT NULL,
  `metodo_calculo` varchar(6) DEFAULT NULL,
  `fecha_calculo` date DEFAULT NULL,
  `costo_mp_kg` int(11) DEFAULT NULL,
  `envase` int(11) DEFAULT NULL,
  `etiqueta` int(11) DEFAULT NULL,
  `bandeja` int(11) DEFAULT NULL,
  `plastico` int(11) DEFAULT NULL,
  `costo_total` int(11) DEFAULT NULL,
  `volumen` decimal(10,0) DEFAULT NULL,
  `precio_venta` int(11) DEFAULT NULL,
  `cantidad_total` int(11) DEFAULT NULL,
  `costo_mod` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `costos_item`
--

INSERT INTO `costos_item` (`id`, `item_general_id`, `costo_unitario`, `costo_mp_galon`, `periodo`, `metodo_calculo`, `fecha_calculo`, `costo_mp_kg`, `envase`, `etiqueta`, `bandeja`, `plastico`, `costo_total`, `volumen`, `precio_venta`, `cantidad_total`, `costo_mod`) VALUES
(1, 1, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 370, 0, 0, 600),
(2, 31, 7000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3, 32, 11000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4, 33, 34050, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5, 34, 27144, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6, 35, 12691, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(7, 36, 4372, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(8, 37, 11466, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(9, 38, 16300, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(10, 39, 17000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(11, 40, 4400, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(12, 41, 14300, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(13, 42, 40, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(14, 43, 1550, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(15, 44, 4617, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(17, 46, 14300, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(18, 47, 855, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(19, 48, 5400, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(21, 50, 12215, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(23, 52, 14152, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(25, 54, 12718, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(27, 56, 11447, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(28, 57, 1690, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(30, 59, 722, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(31, 60, 715, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(32, 61, 4300, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(33, 62, 4400, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(34, 63, 8000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(35, 64, 8000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(36, 65, 1103, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(37, 66, 22700, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(38, 67, 43900, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(39, 68, 37300, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(40, 69, 22700, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(41, 70, 7000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(42, 71, 19500, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(43, 72, 33500, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(44, 73, 37200, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(45, 74, 21850, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(46, 75, 10400, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(47, 76, 8000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(48, 77, 11466, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(49, 78, 13000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(50, 79, 17000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(51, 80, 2900, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(52, 81, 17000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(54, 83, 4617, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(55, 84, 22700, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(56, 85, 22700, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(57, 86, 11000, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(58, 2, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 719, 0, 0, 600),
(59, 3, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 398, 0, 0, 600),
(60, 4, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 440, 0, 0, 600),
(61, 5, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 376, 0, 0, 600),
(62, 6, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 397, 0, 0, 600),
(63, 7, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 396, 0, 0, 600),
(64, 8, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 712, 0, 0, 600),
(65, 9, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 616, 0, 0, 600),
(66, 10, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 711, 0, 0, 600),
(67, 11, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 595, 0, 0, 600),
(68, 12, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 599, 0, 0, 600),
(69, 13, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 578, 0, 0, 600),
(70, 14, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 813, 0, 0, 600),
(71, 15, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 168, 0, 0, 600),
(72, 16, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 212, 0, 0, 600),
(73, 17, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 213, 0, 0, 600),
(74, 18, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 801, 0, 0, 600),
(75, 19, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 3600, 350, 140, 153, 0, 178, 0, 0, 600),
(76, 20, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 328, 0, 0, 150),
(77, 21, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 345, 0, 0, 150),
(78, 22, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 488, 0, 0, 150),
(79, 23, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 119, 0, 0, 150),
(80, 24, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 961, 0, 0, 150),
(81, 25, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 1018, 0, 0, 150),
(82, 26, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 874, 0, 0, 150),
(83, 27, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 851, 0, 0, 150),
(84, 28, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 833, 0, 0, 150),
(85, 29, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 748, 0, 0, 150),
(86, 30, 0, 0, '2025-06', 'MANUAL', '2025-06-07', 0, 0, 0, 0, 0, 0, 376, 0, 0, 150),
(87, 87, 4372, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(88, 88, 4400, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(89, 89, 4372, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(90, 90, 4372, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(92, 92, 16300, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(93, 93, 14152, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(94, 94, 11466, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(95, 95, 17000, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(96, 96, 11447, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(97, 97, 22700, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(98, 98, 22700, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(99, 99, 22700, 0, '2025-06', 'MANUAL', '2025-06-10', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(108, 100, 8000, 0, '2025-06', 'MANUAL', '2025-06-15', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos_produccion`
--

CREATE TABLE `costos_produccion` (
  `id` int(11) NOT NULL,
  `costo_unitario` mediumint(9) DEFAULT NULL,
  `costo_mp_galon` tinyint(4) DEFAULT NULL,
  `periodo` varchar(7) DEFAULT NULL,
  `metodo_calculo` varchar(6) DEFAULT NULL,
  `fecha_calculo` varchar(0) DEFAULT NULL,
  `costo_mp_kg` tinyint(4) DEFAULT NULL,
  `envase` smallint(6) DEFAULT NULL,
  `etiqueta` smallint(6) DEFAULT NULL,
  `bandeja` smallint(6) DEFAULT NULL,
  `plastico` smallint(6) DEFAULT NULL,
  `costo_total` tinyint(4) DEFAULT NULL,
  `volumen` varchar(5) DEFAULT NULL,
  `precio_venta` tinyint(4) DEFAULT NULL,
  `cantidad_total` tinyint(4) DEFAULT NULL,
  `costo_mod` smallint(6) DEFAULT NULL,
  `preparaciones_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_facturas`
--

CREATE TABLE `detalle_facturas` (
  `id_detalle_facturas` int(11) NOT NULL,
  `cantidad` tinyint(4) DEFAULT NULL,
  `precio_unitario` decimal(7,1) DEFAULT NULL,
  `subtotal` decimal(7,1) DEFAULT NULL,
  `facturas_id` int(11) DEFAULT NULL,
  `item_general_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_facturas` int(11) NOT NULL,
  `numero` varchar(6) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `fecha_emision` varchar(0) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `estado` varchar(9) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `impuestos` decimal(10,2) DEFAULT NULL,
  `retencion` decimal(10,2) DEFAULT NULL,
  `movimiento_inventario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formulaciones`
--

CREATE TABLE `formulaciones` (
  `id_formulaciones` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT NULL COMMENT '0 inactiva\n1 activa',
  `defecto` tinyint(4) DEFAULT 0 COMMENT '1 por defecto',
  `item_general_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `formulaciones`
--

INSERT INTO `formulaciones` (`id_formulaciones`, `nombre`, `descripcion`, `estado`, `defecto`, `item_general_id`) VALUES
(1, 'PREPARACIÓN BARNIZ', NULL, 1, 1, 1),
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
(30, 'PREPARACION PASTA ESMALTE TABACO', NULL, 1, 1, 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `cantidad` decimal(5,2) DEFAULT NULL,
  `fecha_update` varchar(0) DEFAULT NULL,
  `apartada` tinyint(4) DEFAULT NULL,
  `item_general_id` int(11) NOT NULL COMMENT '0 disponible\n1 No disponible',
  `estado` varchar(45) DEFAULT NULL,
  `movimiento_inventario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_especifico`
--

CREATE TABLE `item_especifico` (
  `id_item_especifico` int(11) NOT NULL,
  `viscosidad` varchar(10) DEFAULT NULL,
  `p_g` varchar(14) DEFAULT NULL,
  `color` varchar(3) DEFAULT NULL,
  `brillo_60` varchar(6) DEFAULT NULL,
  `secado` varchar(8) DEFAULT NULL,
  `cubrimiento` varchar(9) DEFAULT NULL,
  `molienda` varchar(5) DEFAULT NULL,
  `ph` varchar(1) DEFAULT NULL,
  `poder_tintoreo` varchar(13) DEFAULT NULL,
  `volumen` varchar(6) DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `unidad_id` int(11) DEFAULT NULL,
  `costo_produccion` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `item_especifico`
--

INSERT INTO `item_especifico` (`id_item_especifico`, `viscosidad`, `p_g`, `color`, `brillo_60`, `secado`, `cubrimiento`, `molienda`, `ph`, `poder_tintoreo`, `volumen`, `cantidad`, `unidad_id`, `costo_produccion`) VALUES
(1, '95-100 KU', '3,4+/-0,05 Kg', 'STD', '>=95', '12 HORAS', NULL, NULL, NULL, NULL, '370.0', NULL, NULL, 0.00),
(2, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, '719.0', NULL, NULL, 7000.00),
(3, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '6 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '398.0', NULL, NULL, 11000.00),
(4, '105-110 KU', '3,9+/-0,05 Kg', NULL, '<=15', '12 HORAS', '100+/-5%', '6 H', NULL, NULL, '440.0', NULL, NULL, 34050.00),
(5, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>= 90°', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '376.0', NULL, NULL, 27144.00),
(6, '100-105 KU', '3.4+/-0.05 Kg', NULL, '>= 90', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '397.0', NULL, NULL, 12691.00),
(7, '100-105 KU', '3.6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '396.0', NULL, NULL, 4372.00),
(8, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, '712.0', NULL, NULL, 11466.00),
(9, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, '616.0', NULL, NULL, 16300.00),
(10, '95-100', '4,2 +/- 0,1 Kg', NULL, '15', '12 HORAS', '100+/-5', '6 H', NULL, NULL, '711.0', NULL, NULL, 17000.00),
(11, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, '595.0', NULL, NULL, 4400.00),
(12, '100-105', '3.5+/-0.05', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, '599.0', NULL, NULL, 14300.00),
(13, '100-105KU', '3.5+/-0.05', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, '578.0', NULL, NULL, 40.00),
(14, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '813.0', NULL, NULL, 1550.00),
(15, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '168.0', NULL, NULL, 4617.00),
(16, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '212.0', NULL, NULL, 8640.00),
(17, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '213.0', NULL, NULL, 14300.00),
(18, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '801.0', NULL, NULL, 855.00),
(19, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '178.0', NULL, NULL, 5400.00),
(20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '328.0', NULL, NULL, 8105.00),
(21, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '345.0', NULL, NULL, 12215.00),
(22, '100 KU', '4,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '488.1', NULL, NULL, 19945.00),
(23, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '119.0', NULL, NULL, 14152.00),
(24, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '961.0', NULL, NULL, 11447.00),
(25, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '1018.0', NULL, NULL, 12718.00),
(26, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '874.0', NULL, NULL, 7742.00),
(27, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '851.0', NULL, NULL, 11447.00),
(28, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '833.0', NULL, NULL, 1690.00),
(29, '120', '5,78', 'STD', NULL, NULL, NULL, '7,5', '-', '100 +/- 0.5 %', '748.0', NULL, NULL, 10303.00),
(30, '95-100', '5.71-5.91', 'STD', NULL, NULL, NULL, '7,5', '-', 'STD', '376.0', NULL, NULL, 722.00),
(31, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 715.00),
(32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4300.00),
(33, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4400.00),
(34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00),
(35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00),
(36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1103.00),
(37, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(38, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 43900.00),
(39, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 37300.00),
(40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(41, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7000.00),
(42, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19500.00),
(43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 33500.00),
(44, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 37200.00),
(45, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21850.00),
(46, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10400.00),
(47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00),
(48, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11466.00),
(49, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13000.00),
(50, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17000.00),
(51, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2900.00),
(52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17000.00),
(53, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8105.00),
(54, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4617.00),
(55, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(56, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11000.00),
(58, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(59, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(60, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(61, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(62, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(63, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(64, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(65, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(66, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(67, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(68, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(69, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(70, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(72, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(73, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(74, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(75, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(76, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(77, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(78, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8105.00),
(79, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(80, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(81, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(82, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(84, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(85, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(86, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4372.00),
(88, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4400.00),
(89, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4372.00),
(90, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4372.00),
(91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8105.00),
(92, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 16300.00),
(93, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 14152.00),
(94, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11466.00),
(95, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17000.00),
(96, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11447.00),
(97, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(98, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(99, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_especifico_formulaciones`
--

CREATE TABLE `item_especifico_formulaciones` (
  `id_item_especifico_formulaciones` int(11) NOT NULL,
  `item_especifico_id` int(11) NOT NULL,
  `formulaciones_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `porcentaje` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `item_especifico_formulaciones`
--

INSERT INTO `item_especifico_formulaciones` (`id_item_especifico_formulaciones`, `item_especifico_id`, `formulaciones_id`, `cantidad`, `porcentaje`) VALUES
(1, 31, 1, 932.00, NULL),
(2, 32, 1, 3.72, NULL),
(3, 33, 1, 6.52, NULL),
(4, 34, 1, 10.25, NULL),
(5, 35, 1, 9.32, NULL),
(6, 36, 1, 301.00, NULL),
(7, 31, 2, 425.00, NULL),
(8, 37, 2, 293.00, NULL),
(9, 38, 2, 2.63, NULL),
(10, 39, 2, 16.00, NULL),
(11, 40, 2, 8.00, NULL),
(12, 31, 2, 914.00, NULL),
(13, 41, 2, 14.20, NULL),
(14, 42, 2, 470.00, NULL),
(15, 43, 2, 4.70, NULL),
(16, 86, 2, 5.20, NULL),
(17, 33, 2, 9.37, NULL),
(18, 34, 2, 14.72, NULL),
(19, 35, 2, 13.40, NULL),
(20, 36, 2, 197.00, NULL),
(21, 44, 2, 200.00, NULL),
(22, 31, 1, 932.00, NULL),
(23, 32, 1, 3.72, NULL),
(24, 33, 1, 6.52, NULL),
(25, 34, 1, 10.25, NULL),
(26, 35, 1, 9.32, NULL),
(27, 36, 1, 301.00, NULL),
(28, 31, 2, 425.00, NULL),
(29, 37, 2, 293.00, NULL),
(30, 38, 2, 2.63, NULL),
(31, 39, 2, 16.00, NULL),
(32, 40, 2, 8.00, NULL),
(33, 31, 2, 914.00, NULL),
(34, 41, 2, 14.20, NULL),
(35, 42, 2, 470.00, NULL),
(36, 43, 2, 4.70, NULL),
(37, 86, 2, 5.20, NULL),
(38, 33, 2, 9.37, NULL),
(39, 34, 2, 14.72, NULL),
(40, 35, 2, 13.40, NULL),
(41, 36, 2, 197.00, NULL),
(42, 44, 2, 200.00, NULL),
(359, 31, 1, 932.00, NULL),
(360, 32, 1, 3.72, NULL),
(361, 33, 1, 6.52, NULL),
(362, 34, 1, 10.25, NULL),
(363, 35, 1, 9.32, NULL),
(364, 36, 1, 301.00, NULL),
(365, 31, 2, 425.00, NULL),
(366, 37, 2, 293.00, NULL),
(367, 38, 2, 2.63, NULL),
(368, 39, 2, 16.00, NULL),
(369, 40, 2, 8.00, NULL),
(370, 31, 2, 914.00, NULL),
(371, 41, 2, 14.20, NULL),
(372, 42, 2, 470.00, NULL),
(373, 43, 2, 4.70, NULL),
(374, 86, 2, 5.20, NULL),
(375, 33, 2, 9.37, NULL),
(376, 34, 2, 14.72, NULL),
(377, 35, 2, 13.40, NULL),
(378, 36, 2, 197.00, NULL),
(379, 44, 2, 200.00, NULL),
(380, 31, 3, 775.00, NULL),
(381, 26, 3, 103.00, NULL),
(382, 41, 3, 8.70, NULL),
(383, 42, 3, 290.00, NULL),
(384, 43, 3, 3.00, NULL),
(385, 86, 3, 3.30, NULL),
(386, 33, 3, 5.78, NULL),
(387, 34, 3, 9.10, NULL),
(388, 35, 3, 8.26, NULL),
(389, 36, 3, 113.00, NULL),
(390, 44, 3, 114.00, NULL),
(391, 31, 4, 775.00, NULL),
(392, 47, 4, 224.00, NULL),
(393, 48, 4, 40.00, NULL),
(394, 81, 4, 12.00, NULL),
(395, 40, 4, 6.00, NULL),
(396, 22, 4, 125.00, NULL),
(397, 41, 4, 8.70, NULL),
(398, 42, 4, 290.00, NULL),
(399, 43, 4, 2.90, NULL),
(400, 86, 4, 3.35, NULL),
(401, 33, 4, 5.86, NULL),
(402, 34, 4, 9.21, NULL),
(403, 35, 4, 8.37, NULL),
(404, 44, 4, 227.00, NULL),
(405, 31, 5, 775.00, NULL),
(406, 50, 5, 36.56, NULL),
(407, 24, 5, 79.40, NULL),
(408, 41, 5, 6.00, NULL),
(409, 42, 5, 200.00, NULL),
(410, 43, 5, 2.00, NULL),
(411, 86, 5, 3.33, NULL),
(412, 33, 5, 5.83, NULL),
(413, 34, 5, 9.16, NULL),
(414, 35, 5, 8.32, NULL),
(415, 36, 5, 227.00, NULL),
(416, 31, 6, 775.00, NULL),
(417, 22, 6, 125.00, NULL),
(418, 41, 6, 5.70, NULL),
(419, 42, 6, 190.00, NULL),
(420, 43, 6, 1.90, NULL),
(421, 86, 6, 3.35, NULL),
(422, 33, 6, 5.86, NULL),
(423, 34, 6, 9.21, NULL),
(424, 35, 6, 8.37, NULL),
(425, 44, 6, 227.00, NULL),
(426, 31, 7, 775.00, NULL),
(427, 52, 7, 62.00, NULL),
(428, 56, 7, 10.40, NULL),
(429, 54, 7, 108.00, NULL),
(430, 41, 7, 6.20, NULL),
(431, 42, 7, 205.00, NULL),
(432, 43, 7, 2.10, NULL),
(433, 86, 7, 3.46, NULL),
(434, 33, 7, 6.05, NULL),
(435, 34, 7, 9.51, NULL),
(436, 35, 7, 8.65, NULL),
(437, 36, 7, 113.00, NULL),
(438, 44, 7, 114.00, NULL),
(439, 31, 8, 425.00, NULL),
(440, 37, 8, 251.00, NULL),
(441, 38, 8, 2.63, NULL),
(442, 39, 8, 16.00, NULL),
(443, 40, 8, 8.00, NULL),
(444, 27, 8, 3.30, NULL),
(445, 22, 8, 17.00, NULL),
(446, 31, 8, 914.00, NULL),
(447, 41, 8, 14.20, NULL),
(448, 42, 8, 470.00, NULL),
(449, 43, 8, 4.70, NULL),
(450, 86, 8, 5.20, NULL),
(451, 33, 8, 9.37, NULL),
(452, 34, 8, 14.72, NULL),
(453, 35, 8, 13.40, NULL),
(454, 36, 8, 197.00, NULL),
(455, 44, 8, 200.00, NULL),
(456, 31, 9, 225.00, NULL),
(457, 37, 9, 56.00, NULL),
(458, 38, 9, 0.70, NULL),
(459, 39, 9, 2.00, NULL),
(460, 40, 9, 1.00, NULL),
(461, 56, 9, 168.00, NULL),
(462, 50, 9, 11.20, NULL),
(463, 31, 9, 1014.00, NULL),
(464, 41, 9, 9.70, NULL),
(465, 42, 9, 323.00, NULL),
(466, 43, 9, 3.23, NULL),
(467, 86, 9, 5.40, NULL),
(468, 33, 9, 9.45, NULL),
(469, 34, 9, 14.86, NULL),
(470, 35, 9, 13.51, NULL),
(471, 36, 9, 197.00, NULL),
(472, 44, 9, 165.00, NULL),
(473, 31, 10, 1173.00, NULL),
(474, 37, 10, 288.00, NULL),
(475, 57, 10, 435.00, NULL),
(476, 48, 10, 84.00, NULL),
(477, 38, 10, 5.00, NULL),
(478, 39, 10, 25.00, NULL),
(479, 40, 10, 10.00, NULL),
(480, 41, 10, 14.30, NULL),
(481, 42, 10, 477.00, NULL),
(482, 43, 10, 4.80, NULL),
(483, 86, 10, 4.69, NULL),
(484, 33, 10, 8.20, NULL),
(485, 34, 10, 12.90, NULL),
(486, 35, 10, 11.70, NULL),
(487, 44, 10, 433.00, NULL),
(488, 31, 11, 1033.00, NULL),
(489, 52, 11, 294.70, NULL),
(490, 41, 11, 11.13, NULL),
(491, 42, 11, 371.00, NULL),
(492, 43, 11, 3.70, NULL),
(493, 86, 11, 4.72, NULL),
(494, 33, 11, 8.26, NULL),
(495, 34, 11, 13.00, NULL),
(496, 35, 11, 11.81, NULL),
(497, 44, 11, 391.00, NULL),
(498, 31, 12, 1033.00, NULL),
(499, 24, 12, 180.00, NULL),
(500, 52, 12, 77.00, NULL),
(501, 41, 12, 11.00, NULL),
(502, 42, 12, 363.00, NULL),
(503, 43, 12, 3.66, NULL),
(504, 86, 12, 4.64, NULL),
(505, 33, 12, 8.13, NULL),
(506, 34, 12, 12.77, NULL),
(507, 35, 12, 11.61, NULL),
(508, 44, 12, 391.00, NULL),
(509, 31, 13, 1033.00, NULL),
(510, 30, 13, 190.00, NULL),
(511, 41, 13, 11.00, NULL),
(512, 42, 13, 363.00, NULL),
(513, 43, 13, 3.60, NULL),
(514, 86, 13, 4.50, NULL),
(515, 33, 13, 7.90, NULL),
(516, 34, 13, 12.40, NULL),
(517, 35, 13, 11.30, NULL),
(518, 44, 13, 391.00, NULL),
(519, 31, 14, 1056.00, NULL),
(520, 77, 14, 186.00, NULL),
(521, 59, 14, 848.00, NULL),
(522, 60, 14, 70.00, NULL),
(523, 61, 14, 5.00, NULL),
(524, 39, 14, 25.00, NULL),
(525, 40, 14, 5.00, NULL),
(526, 41, 14, 17.80, NULL),
(527, 42, 14, 593.00, NULL),
(528, 43, 14, 5.93, NULL),
(529, 86, 14, 4.30, NULL),
(530, 33, 14, 7.40, NULL),
(531, 34, 14, 11.60, NULL),
(532, 35, 14, 10.60, NULL),
(533, 22, 14, 20.00, NULL),
(534, 44, 14, 550.00, NULL),
(535, 31, 15, 256.00, NULL),
(536, 22, 15, 37.00, NULL),
(537, 61, 15, 2.30, NULL),
(538, 60, 15, 46.00, NULL),
(539, 59, 15, 132.00, NULL),
(540, 79, 15, 4.00, NULL),
(541, 40, 15, 2.00, NULL),
(542, 41, 15, 3.70, NULL),
(543, 42, 15, 123.00, NULL),
(544, 43, 15, 1.30, NULL),
(545, 86, 15, 1.10, NULL),
(546, 33, 15, 2.00, NULL),
(547, 34, 15, 3.00, NULL),
(548, 35, 15, 2.80, NULL),
(549, 44, 15, 89.60, NULL),
(550, 31, 16, 274.00, NULL),
(551, 63, 16, 47.00, NULL),
(552, 59, 16, 220.00, NULL),
(553, 60, 16, 18.00, NULL),
(554, 61, 16, 1.30, NULL),
(555, 39, 16, 6.50, NULL),
(556, 40, 16, 4.00, NULL),
(557, 41, 16, 4.80, NULL),
(558, 42, 16, 160.00, NULL),
(559, 43, 16, 1.60, NULL),
(560, 86, 16, 1.10, NULL),
(561, 33, 16, 1.92, NULL),
(562, 34, 16, 3.00, NULL),
(563, 35, 16, 2.74, NULL),
(564, 44, 16, 142.60, NULL),
(565, 31, 17, 274.00, NULL),
(566, 64, 17, 58.00, NULL),
(567, 59, 17, 220.00, NULL),
(568, 60, 17, 18.00, NULL),
(569, 61, 17, 1.30, NULL),
(570, 39, 17, 6.50, NULL),
(571, 40, 17, 4.00, NULL),
(572, 41, 17, 4.70, NULL),
(573, 42, 17, 155.60, NULL),
(574, 43, 17, 1.55, NULL),
(575, 86, 17, 1.10, NULL),
(576, 33, 17, 1.92, NULL),
(577, 34, 17, 3.00, NULL),
(578, 35, 17, 2.74, NULL),
(579, 44, 17, 142.60, NULL),
(580, 31, 18, 1056.00, NULL),
(581, 77, 18, 165.00, NULL),
(582, 65, 18, 230.00, NULL),
(583, 60, 18, 688.00, NULL),
(584, 38, 18, 5.00, NULL),
(585, 39, 18, 25.00, NULL),
(586, 40, 18, 5.00, NULL),
(587, 41, 18, 17.55, NULL),
(588, 42, 18, 585.26, NULL),
(589, 43, 18, 5.85, NULL),
(590, 86, 18, 4.30, NULL),
(591, 33, 18, 7.40, NULL),
(592, 34, 18, 11.60, NULL),
(593, 35, 18, 10.60, NULL),
(594, 44, 18, 550.00, NULL),
(595, 31, 19, 256.00, NULL),
(596, 77, 19, 36.00, NULL),
(597, 63, 19, 10.00, NULL),
(598, 96, 19, 20.00, NULL),
(599, 22, 19, 3.00, NULL),
(600, 61, 19, 2.30, NULL),
(601, 60, 19, 46.00, NULL),
(602, 59, 19, 132.00, NULL),
(603, 39, 19, 4.00, NULL),
(604, 40, 19, 2.00, NULL),
(605, 41, 19, 3.90, NULL),
(606, 42, 19, 130.00, NULL),
(607, 43, 19, 1.30, NULL),
(608, 86, 19, 1.10, NULL),
(609, 33, 19, 2.00, NULL),
(610, 34, 19, 3.00, NULL),
(611, 35, 19, 2.80, NULL),
(612, 44, 19, 89.60, NULL),
(613, 31, 20, 186.00, NULL),
(614, 32, 20, 3.00, NULL),
(615, 39, 20, 3.00, NULL),
(616, 66, 20, 8.00, NULL),
(617, 67, 20, 50.00, NULL),
(618, 40, 20, 2.00, NULL),
(619, 44, 20, 76.00, NULL),
(620, 31, 21, 186.00, NULL),
(621, 32, 21, 3.00, NULL),
(622, 79, 21, 5.00, NULL),
(623, 80, 21, 3.00, NULL),
(624, 61, 21, 15.00, NULL),
(625, 68, 21, 52.00, NULL),
(626, 97, 21, 5.00, NULL),
(627, 44, 21, 76.00, NULL),
(628, 31, 22, 242.00, NULL),
(629, 86, 22, 3.10, NULL),
(630, 97, 22, 9.00, NULL),
(631, 61, 22, 25.00, NULL),
(632, 71, 22, 59.00, NULL),
(633, 31, 23, 55.00, NULL),
(634, 39, 23, 0.80, NULL),
(635, 80, 23, 0.40, NULL),
(636, 86, 23, 0.25, NULL),
(637, 85, 23, 2.80, NULL),
(638, 61, 23, 1.60, NULL),
(639, 72, 23, 24.00, NULL),
(640, 44, 23, 34.00, NULL),
(641, 31, 24, 332.00, NULL),
(642, 39, 24, 9.00, NULL),
(643, 80, 24, 5.00, NULL),
(644, 86, 24, 3.10, NULL),
(645, 85, 24, 35.00, NULL),
(646, 61, 24, 18.90, NULL),
(647, 73, 24, 408.00, NULL),
(648, 44, 24, 150.00, NULL),
(649, 31, 25, 332.00, NULL),
(650, 39, 25, 9.00, NULL),
(651, 80, 25, 5.00, NULL),
(652, 86, 25, 3.10, NULL),
(653, 61, 25, 18.90, NULL),
(654, 74, 25, 465.00, NULL),
(655, 44, 25, 150.00, NULL),
(656, 31, 26, 295.00, NULL),
(657, 39, 26, 6.00, NULL),
(658, 80, 26, 3.00, NULL),
(659, 86, 26, 3.10, NULL),
(660, 97, 26, 35.00, NULL),
(661, 61, 26, 18.90, NULL),
(662, 75, 26, 340.00, NULL),
(663, 44, 26, 173.00, NULL),
(664, 31, 27, 295.00, NULL),
(665, 39, 27, 6.00, NULL),
(666, 80, 27, 3.00, NULL),
(667, 86, 27, 3.10, NULL),
(668, 61, 27, 18.90, NULL),
(669, 76, 27, 340.00, NULL),
(670, 36, 27, 150.00, NULL),
(671, 31, 28, 295.00, NULL),
(672, 39, 28, 6.00, NULL),
(673, 80, 28, 3.00, NULL),
(674, 86, 28, 3.10, NULL),
(675, 97, 28, 17.00, NULL),
(676, 61, 28, 18.90, NULL),
(677, 100, 28, 340.00, NULL),
(678, 36, 28, 150.00, NULL),
(679, 31, 29, 213.00, NULL),
(680, 39, 29, 22.00, NULL),
(681, 66, 29, 4.00, NULL),
(682, 40, 29, 5.00, NULL),
(683, 37, 29, 441.00, NULL),
(684, 44, 29, 63.00, NULL),
(685, 86, 30, 1.00, NULL),
(686, 78, 30, 185.00, NULL),
(687, 31, 30, 134.00, NULL),
(688, 66, 30, 6.00, NULL),
(689, 39, 30, 8.00, NULL),
(690, 61, 30, 7.00, NULL),
(691, 44, 30, 33.00, NULL),
(692, 40, 30, 2.00, NULL),
(693, 84, 25, 35.00, NULL),
(694, 84, 27, 35.00, NULL),
(695, 83, 22, 150.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_especifico_preparaciones`
--

CREATE TABLE `item_especifico_preparaciones` (
  `item_especifico_id` int(11) NOT NULL,
  `preparaciones_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `unidad_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_general`
--

CREATE TABLE `item_general` (
  `id_item_general` int(11) NOT NULL,
  `nombre` varchar(36) DEFAULT NULL,
  `codigo` varchar(6) DEFAULT NULL,
  `tipo` tinyint(4) DEFAULT NULL COMMENT '0 productos\n1 materia prima\n2 Insumos',
  `categoria_id` int(11) DEFAULT NULL,
  `item_especifico_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `item_general`
--

INSERT INTO `item_general` (`id_item_general`, `nombre`, `codigo`, `tipo`, `categoria_id`, `item_especifico_id`) VALUES
(1, 'BARNIZ TRANSPARENTE BRILLANTE', 'BAR001', 0, 4, 1),
(2, 'ESMALTE BLANCO', 'ESM002', 0, 1, 2),
(3, 'ESMALTE CAOBA', 'ESM003', 0, 1, 3),
(4, 'ESMALTE NEGRO MATE', 'ESM004', 0, 1, 4),
(5, 'ESMALTE ROJO FIESTA', 'ESM005', 0, 1, 5),
(6, 'ESMALTE NEGRO BRILLANTE', 'ESM006', 0, 1, 6),
(7, 'ESMALTE VERDE ESMERALDA', 'ESM007', 0, 1, 7),
(8, 'ESMALTE GRIS PLATA', 'ESM008', 0, 1, 8),
(9, 'ESMALTE AZUL ESPAÑOL', 'ESM009', 0, 1, 9),
(10, 'ESMALTE BLANCO MATE', 'ESM010', 0, 1, 10),
(11, 'ESMALTE AMARILLO', 'ESM011', 0, 1, 11),
(12, 'ESMALTE NARANJA', 'ESM012', 0, 1, 12),
(13, 'ESMALTE TABACO', 'ESM013', 0, 1, 13),
(14, 'ANTICORROSIVO GRIS', 'ANT014', 0, 3, 14),
(15, 'ANTICORROSIVO NEGRO', 'ANT015', 0, 3, 15),
(16, 'ANTICORROSIVO AMARILLO', 'ANT016', 0, 3, 16),
(17, 'ANTICORROSIVO ROJO', 'ANT017', 0, 3, 17),
(18, 'ANTICORROSIVO BLANCO', 'ANT018', 0, 3, 18),
(19, 'ANTICORROSIVO VERDE', 'ANT019', 0, 3, 19),
(20, 'PASTA ESMALTE VERDE ENTONADOR', 'PAS020', 0, 2, 20),
(21, 'PASTA ESMALTE AZUL ENTONADOR', 'PAS021', 0, 2, 21),
(22, 'PASTA ESMALTE NEGRO', 'PAS022', 2, 2, 22),
(23, 'PASTA ESMALTE ROJO CARMIN 57:1', 'PAS023', 0, 2, 23),
(24, 'PASTA ESMALTE NARANJA', 'PAS024', 2, 2, 24),
(25, 'PASTA ESMALTE AMARILLO', 'PAS025', 0, 2, 25),
(26, 'PASTA ESMALTE CAOBA', 'PAS026', 2, 2, 26),
(27, 'PASTA ESMALTE AMARILLO OXIDO', 'PAS027', 2, 2, 27),
(28, 'PASTA ESMALTE ROJO OXIDO', 'PAS028', 0, 2, 28),
(29, 'PASTA ESMALTE BLANCO', 'PAS029', 0, 2, 29),
(30, 'PASTA ESMALTE TABACO', 'PAS030', 2, 2, 30),
(31, 'RESINA MEDIA EN SOYA AL 50%', 'RAM014', 1, NULL, 31),
(32, 'METIL ETIL CETOXIMA', 'AAN002', 1, NULL, 32),
(33, 'OCTOATO DE COBALTO AL 12%', 'SOC011', 1, NULL, 33),
(34, 'OCTOATO DE ZIRCONIO AL 24%', 'SOZ024', 1, NULL, 34),
(35, 'OCTOATO DE CALCIO AL 10%', 'SOC010', 1, NULL, 35),
(36, 'DISOLVENTE 2232 #3', 'SAA011', 1, NULL, 36),
(37, 'DIOXIDO DE TITANIO SULFATO', 'PED010', 1, NULL, 37),
(38, 'OCTOATO DE ZINC AL 16%', 'SOZ016', 1, NULL, 38),
(39, 'BENTOCLAY BP 184', 'AAS005', 1, NULL, 39),
(40, 'ETANOL AL 96%', 'SAA022', 1, NULL, 40),
(41, 'DISASTAB GAT', 'AEM005', 1, NULL, 41),
(42, 'AGUA', 'SIA040', 1, NULL, 42),
(43, 'SULFATO DE MAGNESIO', 'AET004', 1, NULL, 43),
(44, 'VARSOL', 'SAV010', 1, NULL, 44),
(46, 'DISASTAB GAT', 'AEM004', 1, NULL, 46),
(47, 'MICROTALC C 20', 'CTA011', 1, NULL, 47),
(48, 'CELITE 499', 'MSI006', 1, NULL, 48),
(50, 'PASTA ESMALTE ROJO 57:1', 'PE1033', 2, NULL, 50),
(52, 'PASTA AMARILLO CROMO MEDIO', 'PE1010', 2, NULL, 52),
(54, 'PASTA VERDE FTALO', 'PE1040', 2, NULL, 54),
(56, 'PASTA ESMALTE AZUL FTALO 15:3', 'PE1021', 2, NULL, 56),
(57, 'OMYACARB UF', 'CCC002', 1, NULL, 57),
(59, 'MICROTALC C 20', 'CTA025', 1, NULL, 59),
(60, 'CARBONATO DE CALCIO HI WHITE', 'CCC004', 1, NULL, 60),
(61, 'LECITINA DE SOYA', 'AHU002', 1, NULL, 61),
(62, 'ETANOL AL 96%', 'SAM023', 1, NULL, 62),
(63, 'OXIDO DE HIERRO AMARILLO Y 4021', 'PEA010', 1, NULL, 63),
(64, 'OXIDO DE HIERRO ROJO R-5530', 'PER030', 1, NULL, 64),
(65, 'MICROTALC 20', 'CTA020', 1, NULL, 65),
(66, 'TROYSPERSE CD1', 'ADI002', 1, NULL, 66),
(67, 'PIGMENTO VERDE FTALO 7', 'PEV053', 1, NULL, 67),
(68, 'PIGMENTO AZUL FTALO 15;3', 'PEA041', 1, NULL, 68),
(69, 'EDAPLAN 918 / LANSPERSE SUV', 'ADI010', 1, NULL, 69),
(70, 'RESINA MEDIA EN SOYA AL 50%', 'MS-45', 1, NULL, 70),
(71, 'POW CARBON BLACK CHEMO', 'PEN081', 1, NULL, 71),
(72, 'PIGMENTO ROJO CARMIN 57:1', 'PER031', 1, NULL, 72),
(73, 'PIGMENTO NARANJA MOLIBDENO', 'PEN023', 1, NULL, 73),
(74, 'PIGMENTO MARILLO DE CROMO AL 73', 'PEA011', 1, NULL, 74),
(75, 'PIGMENTO OXIFERR CAOBA MARRON M 4781', 'PEC081', 1, NULL, 75),
(76, 'PIGMENTO OXIFERR AMARILLO Y-4011', 'PEA013', 1, NULL, 76),
(77, 'DIOXIDO DE TITANIO SULFATO 2196', 'PED007', 1, NULL, 77),
(78, 'OXIFER TABACO R-4370', 'PET080', 1, NULL, 78),
(79, 'BENTOCLAY BP 184', 'AAS012', 1, NULL, 79),
(80, 'METANOL', 'SAM023', 1, NULL, 80),
(81, 'ORGANOCLAY BK 884', 'AAS005', 1, NULL, 81),
(83, 'DISOLVENTE 2232 / VARSOL', 'SAA011', 1, NULL, 83),
(84, 'EDAPLAN 915', 'ADI010', 1, NULL, 84),
(85, 'CHEMOSPERSE 77', 'ADI011', 1, NULL, 85),
(86, 'ADIMON 84', 'AAN002', 1, NULL, 86),
(87, 'DISOLVENTE #3', 'SAA011', 1, NULL, 87),
(88, 'ETANOL 96%', 'SAA022', 1, NULL, 88),
(89, 'DISOLVENTE 2232', 'SAA011', 1, NULL, 89),
(90, 'DISOLVENTE 3', 'SAA011', 1, NULL, 90),
(92, 'OCTOATO DE ZINC 16%', 'SOZ016', 1, NULL, 92),
(93, 'PASTA ESMALTE AMARILLO CROMO MEDIO', 'PE1010', 2, NULL, 93),
(94, 'DIOXIDO DE TITANIO SULFATO 2196', 'PED010', 1, NULL, 94),
(95, 'BENTOCLAY BP184', 'AAS005', 1, NULL, 95),
(96, 'PASTA ESMALTE AZUL 15:3', 'PE1021', 2, NULL, 96),
(97, 'EDAPLAN 918', 'ADI010', 1, NULL, 97),
(98, 'EDAPLAN 918 / LANSPERSE SUV', 'ADI010', 1, NULL, 98),
(99, 'CHEMOSPERSE 77', 'ADI010', 1, NULL, 99),
(100, 'PIGMENTO OXIFERR ROJO R-5530', 'PER030', 1, NULL, 100);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_proveedor`
--

CREATE TABLE `item_proveedor` (
  `id_item_proveedor` int(11) NOT NULL,
  `nombre` varchar(55) DEFAULT NULL,
  `codigo` varchar(10) DEFAULT NULL,
  `tipo` varchar(13) DEFAULT NULL,
  `unidad_empaque` varchar(13) DEFAULT NULL,
  `precio_unitario` mediumint(9) DEFAULT NULL,
  `precio_con_iva` decimal(7,1) DEFAULT NULL,
  `disponible` tinyint(4) DEFAULT NULL,
  `descripcion` varchar(55) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_inventario`
--

CREATE TABLE `movimiento_inventario` (
  `id_movimiento_inventario` int(11) NOT NULL,
  `tipo_movimiento` varchar(6) DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `fecha_movimiento` varchar(0) DEFAULT NULL,
  `descripcion` varchar(32) DEFAULT NULL,
  `referencia_tipo` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_cliente`
--

CREATE TABLE `pagos_cliente` (
  `id_pagos_cliente` int(11) NOT NULL,
  `fecha_pago` varchar(0) DEFAULT NULL,
  `monto` decimal(7,1) DEFAULT NULL,
  `metodo_pago` varchar(8) DEFAULT NULL,
  `observaciones` varchar(0) DEFAULT NULL,
  `clientes_id` int(11) DEFAULT NULL,
  `facturas_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preparaciones`
--

CREATE TABLE `preparaciones` (
  `id_preparaciones` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `item_general_id` int(11) DEFAULT NULL,
  `unidad_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_encargado` varchar(16) DEFAULT NULL,
  `nombre_empresa` varchar(27) DEFAULT NULL,
  `numero_documento` varchar(11) DEFAULT NULL,
  `direccion` varchar(45) DEFAULT NULL,
  `telefono` varchar(14) DEFAULT NULL,
  `email` varchar(34) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidad`
--

CREATE TABLE `unidad` (
  `id_unidad` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `estados` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_clientes`),
  ADD KEY `fk_clientes_facturas1_idx` (`facturas_id`);

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
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_inventario_item_general1_idx` (`item_general_id`),
  ADD KEY `fk_inventario_movimientos_inventario1_idx` (`movimiento_inventario_id`);

--
-- Indices de la tabla `item_especifico`
--
ALTER TABLE `item_especifico`
  ADD PRIMARY KEY (`id_item_especifico`),
  ADD UNIQUE KEY `id_item_especifico_UNIQUE` (`id_item_especifico`),
  ADD KEY `fk_item_especifico_unidad1_idx` (`unidad_id`);

--
-- Indices de la tabla `item_especifico_formulaciones`
--
ALTER TABLE `item_especifico_formulaciones`
  ADD PRIMARY KEY (`id_item_especifico_formulaciones`),
  ADD KEY `fk_item_especifico_has_formulaciones_formulaciones1_idx` (`formulaciones_id`),
  ADD KEY `fk_item_especifico_has_formulaciones_item_especifico1_idx` (`item_especifico_id`);

--
-- Indices de la tabla `item_especifico_preparaciones`
--
ALTER TABLE `item_especifico_preparaciones`
  ADD PRIMARY KEY (`item_especifico_id`,`preparaciones_id`),
  ADD KEY `fk_item_especifico_has_preparaciones_preparaciones1_idx` (`preparaciones_id`),
  ADD KEY `fk_item_especifico_has_preparaciones_item_especifico1_idx` (`item_especifico_id`),
  ADD KEY `fk_item_especifico_has_preparaciones_unidad1_idx` (`unidad_id`);

--
-- Indices de la tabla `item_general`
--
ALTER TABLE `item_general`
  ADD PRIMARY KEY (`id_item_general`),
  ADD UNIQUE KEY `id_item_general_UNIQUE` (`id_item_general`),
  ADD KEY `fk_item_general_categoria1_idx` (`categoria_id`),
  ADD KEY `fk_item_general_item_especifico1_idx` (`item_especifico_id`);

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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `costos_item`
--
ALTER TABLE `costos_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT de la tabla `costos_produccion`
--
ALTER TABLE `costos_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_facturas`
--
ALTER TABLE `detalle_facturas`
  MODIFY `id_detalle_facturas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_facturas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  MODIFY `id_formulaciones` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `item_especifico`
--
ALTER TABLE `item_especifico`
  MODIFY `id_item_especifico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `item_especifico_formulaciones`
--
ALTER TABLE `item_especifico_formulaciones`
  MODIFY `id_item_especifico_formulaciones` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=696;

--
-- AUTO_INCREMENT de la tabla `item_general`
--
ALTER TABLE `item_general`
  MODIFY `id_item_general` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `item_proveedor`
--
ALTER TABLE `item_proveedor`
  MODIFY `id_item_proveedor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  MODIFY `id_movimiento_inventario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_cliente`
--
ALTER TABLE `pagos_cliente`
  MODIFY `id_pagos_cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preparaciones`
--
ALTER TABLE `preparaciones`
  MODIFY `id_preparaciones` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `unidad`
--
ALTER TABLE `unidad`
  MODIFY `id_unidad` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_facturas1` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `costos_item`
--
ALTER TABLE `costos_item`
  ADD CONSTRAINT `costos_item_ibfk_1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `costos_produccion`
--
ALTER TABLE `costos_produccion`
  ADD CONSTRAINT `fk_costos_produccion_preparaciones` FOREIGN KEY (`preparaciones_id`) REFERENCES `preparaciones` (`id_preparaciones`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `detalle_facturas`
--
ALTER TABLE `detalle_facturas`
  ADD CONSTRAINT `fk_detalle_facturas_facturas1` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_detalle_facturas_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_facturas_movimientos_inventario1` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimiento_inventario` (`id_movimiento_inventario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  ADD CONSTRAINT `fk_formulaciones_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `fk_inventario_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_inventario_movimientos_inventario1` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimiento_inventario` (`id_movimiento_inventario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `item_especifico`
--
ALTER TABLE `item_especifico`
  ADD CONSTRAINT `fk_item_especifico_unidad1` FOREIGN KEY (`unidad_id`) REFERENCES `unidad` (`id_unidad`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `item_especifico_formulaciones`
--
ALTER TABLE `item_especifico_formulaciones`
  ADD CONSTRAINT `fk_item_especifico_has_formulaciones_formulaciones1` FOREIGN KEY (`formulaciones_id`) REFERENCES `formulaciones` (`id_formulaciones`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_item_especifico_has_formulaciones_item_especifico1` FOREIGN KEY (`item_especifico_id`) REFERENCES `item_especifico` (`id_item_especifico`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `item_especifico_preparaciones`
--
ALTER TABLE `item_especifico_preparaciones`
  ADD CONSTRAINT `fk_item_especifico_has_preparaciones_item_especifico1` FOREIGN KEY (`item_especifico_id`) REFERENCES `item_especifico` (`id_item_especifico`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_item_especifico_has_preparaciones_preparaciones1` FOREIGN KEY (`preparaciones_id`) REFERENCES `preparaciones` (`id_preparaciones`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_item_especifico_has_preparaciones_unidad1` FOREIGN KEY (`unidad_id`) REFERENCES `unidad` (`id_unidad`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `item_general`
--
ALTER TABLE `item_general`
  ADD CONSTRAINT `fk_item_general_categoria1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id_categoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_item_general_item_especifico1` FOREIGN KEY (`item_especifico_id`) REFERENCES `item_especifico` (`id_item_especifico`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `item_proveedor`
--
ALTER TABLE `item_proveedor`
  ADD CONSTRAINT `fk_item_proveedor_proveedores1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id_proveedor`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `pagos_cliente`
--
ALTER TABLE `pagos_cliente`
  ADD CONSTRAINT `fk_pagos_cliente_clientes1` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`id_clientes`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_pagos_cliente_facturas1` FOREIGN KEY (`facturas_id`) REFERENCES `facturas` (`id_facturas`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `preparaciones`
--
ALTER TABLE `preparaciones`
  ADD CONSTRAINT `fk_preparaciones_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_preparaciones_unidad1` FOREIGN KEY (`unidad_id`) REFERENCES `unidad` (`id_unidad`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
