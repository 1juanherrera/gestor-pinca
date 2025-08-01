-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-07-2025 a las 23:55:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

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
(2, 'PREPARACION ESMALTE BLANCO', NULL, 1, 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `item_id` smallint(6) DEFAULT NULL,
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
  `unidad_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `item_especifico`
--

INSERT INTO `item_especifico` (`id_item_especifico`, `viscosidad`, `p_g`, `color`, `brillo_60`, `secado`, `cubrimiento`, `molienda`, `ph`, `poder_tintoreo`, `volumen`, `cantidad`, `unidad_id`) VALUES
(1, '95-100 KU', '3,4+/-0,05 Kg', 'STD', '>=95', '12 HORAS', NULL, NULL, NULL, NULL, '370.0', NULL, NULL),
(2, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, '719.0', NULL, NULL),
(3, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '6 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '398.0', NULL, NULL),
(4, '105-110 KU', '3,9+/-0,05 Kg', NULL, '<=15', '12 HORAS', '100+/-5%', '6 H', NULL, NULL, '440.0', NULL, NULL),
(5, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>= 90°', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '376.0', NULL, NULL),
(6, '100-105 KU', '3.4+/-0.05 Kg', NULL, '>= 90', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '397.0', NULL, NULL),
(7, '100-105 KU', '3.6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '396.0', NULL, NULL),
(8, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, '712.0', NULL, NULL),
(9, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, '616.0', NULL, NULL),
(10, '95-100', '4,2 +/- 0,1 Kg', NULL, '15', '12 HORAS', '100+/-5', '6 H', NULL, NULL, '711.0', NULL, NULL),
(11, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, '595.0', NULL, NULL),
(12, '100-105', '3.5+/-0.05', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, '599.0', NULL, NULL),
(13, '100-105KU', '3.5+/-0.05', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, '578.0', NULL, NULL),
(14, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '813.0', NULL, NULL),
(15, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '168.0', NULL, NULL),
(16, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '212.0', NULL, NULL),
(17, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '213.0', NULL, NULL),
(18, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '801.0', NULL, NULL),
(19, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '178.0', NULL, NULL),
(20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '328.0', NULL, NULL),
(21, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '345.0', NULL, NULL),
(22, '100 KU', '4,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '488.1', NULL, NULL),
(23, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '119.0', NULL, NULL),
(24, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '961.0', NULL, NULL),
(25, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '1018.0', NULL, NULL),
(26, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '874.0', NULL, NULL),
(27, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '851.0', NULL, NULL),
(28, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '833.0', NULL, NULL),
(29, '120', '5,78', 'STD', NULL, NULL, NULL, '7,5', '-', '100 +/- 0.5 %', '748.0', NULL, NULL),
(30, '95-100', '5.71-5.91', 'STD', NULL, NULL, NULL, '7,5', '-', 'STD', '376.0', NULL, NULL),
(31, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(59, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(68, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(69, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(72, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(73, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(74, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(75, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(76, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(77, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(78, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(79, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(80, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(81, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(84, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(85, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(86, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(88, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(89, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(90, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(92, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(93, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(94, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(95, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(96, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(97, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(98, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(99, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
(21, 44, 2, 200.00, NULL);

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
  MODIFY `id_formulaciones` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `item_especifico`
--
ALTER TABLE `item_especifico`
  MODIFY `id_item_especifico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `item_especifico_formulaciones`
--
ALTER TABLE `item_especifico_formulaciones`
  MODIFY `id_item_especifico_formulaciones` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `item_general`
--
ALTER TABLE `item_general`
  MODIFY `id_item_general` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

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
-- Filtros para la tabla `costos_produccion`
--
ALTER TABLE `costos_produccion`
  ADD CONSTRAINT `fk_costos_produccion_preparaciones1` FOREIGN KEY (`preparaciones_id`) REFERENCES `preparaciones` (`id_preparaciones`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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
