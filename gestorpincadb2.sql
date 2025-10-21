-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-10-2025 a las 23:59:37
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
-- Base de datos: `gestorpincadb2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bodegas`
--

CREATE TABLE `bodegas` (
  `id_bodegas` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT NULL COMMENT '0 inactiva 1 activa',
  `instalaciones_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bodegas`
--

INSERT INTO `bodegas` (`id_bodegas`, `nombre`, `descripcion`, `estado`, `instalaciones_id`) VALUES
(1, 'Bodega Santa Camila', 'BODEGA INSUMOS, MATERIAS PRIMAS Y PRODUCTOS', 1, 1),
(2, 'Bodega Villa Olimpica', 'Instalación de acopio y despacho situada en la zona de Villa Olímpica, ideal para operaciones urbanas gracias a su cercanía con áreas residenciales y comerciales.', 1, 2),
(3, 'Bodega Juan Mina', 'Punto estratégico en la Vía Cordialidad, orientado al manejo de inventarios y distribución regional, con conexiones hacia rutas intermunicipales.', 1, 3),
(8, 'Bodega san juan', 'BODEGA INSUMOS Y MATERIAS PRIMAS', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(13) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

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
  `costo_mod` int(11) DEFAULT NULL COMMENT '0  inactivo\n1 activo',
  `estado` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nit` varchar(11) DEFAULT NULL,
  `razon_social` varchar(100) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `ciudad` varchar(45) DEFAULT NULL,
  `telefono` varchar(45) DEFAULT NULL,
  `pagina_web` varchar(100) DEFAULT NULL
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
  `estado` tinyint(4) DEFAULT NULL COMMENT '0 inactiva\\n1 activa',
  `defecto` tinyint(4) DEFAULT 0 COMMENT '1 por defecto',
  `item_general_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones`
--

CREATE TABLE `instalaciones` (
  `id_instalaciones` int(11) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `ciudad` varchar(45) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `telefono` varchar(45) DEFAULT NULL,
  `id_empresa` int(11) NOT NULL
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
  `id_inventario` int(11) NOT NULL,
  `cantidad` decimal(5,2) DEFAULT NULL,
  `fecha_update` varchar(0) DEFAULT NULL,
  `apartada` tinyint(4) DEFAULT NULL,
  `item_general_id` int(11) NOT NULL,
  `estado` tinyint(5) DEFAULT NULL COMMENT '0 disponible\\r\\n1 No disponible',
  `movimiento_inventario_id` int(11) DEFAULT NULL,
  `tipo` tinyint(4) DEFAULT NULL COMMENT '1 ingreso\n2 egreso',
  `bodegas_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_general`
--

CREATE TABLE `item_general` (
  `id_item_general` int(11) NOT NULL,
  `nombre` varchar(36) DEFAULT NULL,
  `codigo` varchar(6) DEFAULT NULL,
  `tipo` tinyint(4) DEFAULT NULL COMMENT '0 productos\\n1 materia prima\\n2 Insumos',
  `categoria_id` int(11) DEFAULT NULL,
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
-- Volcado de datos para la tabla `item_general`
--

INSERT INTO `item_general` (`id_item_general`, `nombre`, `codigo`, `tipo`, `categoria_id`, `viscosidad`, `p_g`, `color`, `brillo_60`, `secado`, `cubrimiento`, `molienda`, `ph`, `poder_tintoreo`, `volumen`, `cantidad`, `unidad_id`, `costo_produccion`) VALUES
(1, 'BARNIZ TRANSPARENTE BRILLANTE', 'BAR001', 0, 4, '95-100 KU', '3,4+/-0,05 Kg', 'STD', '>=95', '12 HORAS', NULL, NULL, NULL, NULL, '370.0', NULL, NULL, 0.00),
(2, 'ESMALTE BLANCO', 'ESM002', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, '719.0', NULL, NULL, 7000.00),
(3, 'ESMALTE CAOBA', 'ESM003', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '6 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '398.0', NULL, NULL, 11000.00),
(4, 'ESMALTE NEGRO MATE', 'ESM004', 0, 1, '105-110 KU', '3,9+/-0,05 Kg', NULL, '<=15', '12 HORAS', '100+/-5%', '6 H', NULL, NULL, '440.0', NULL, NULL, 34050.00),
(5, 'ESMALTE ROJO FIESTA', 'ESM005', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>= 90°', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '376.0', NULL, NULL, 27144.00),
(6, 'ESMALTE NEGRO BRILLANTE', 'ESM006', 0, 1, '100-105 KU', '3.4+/-0.05 Kg', NULL, '>= 90', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '397.0', NULL, NULL, 12691.00),
(7, 'ESMALTE VERDE ESMERALDA', 'ESM007', 0, 1, '100-105 KU', '3.6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5%', '7.5 H', NULL, NULL, '396.0', NULL, NULL, 4372.00),
(8, 'ESMALTE GRIS PLATA', 'ESM008', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, '712.0', NULL, NULL, 11466.00),
(9, 'ESMALTE AZUL ESPAÑOL', 'ESM009', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5 %', '7.5 H', NULL, NULL, '616.0', NULL, NULL, 16300.00),
(10, 'ESMALTE BLANCO MATE', 'ESM010', 0, 1, '95-100', '4,2 +/- 0,1 Kg', NULL, '15', '12 HORAS', '100+/-5', '6 H', NULL, NULL, '711.0', NULL, NULL, 17000.00),
(11, 'ESMALTE AMARILLO', 'ESM011', 0, 1, '100-105 KU', '3,6+/-0,05 Kg', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, '595.0', NULL, NULL, 4400.00),
(12, 'ESMALTE NARANJA', 'ESM012', 0, 1, '100-105', '3.5+/-0.05', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, '599.0', NULL, NULL, 14300.00),
(13, 'ESMALTE TABACO', 'ESM013', 0, 1, '100-105KU', '3.5+/-0.05', NULL, '>=90', '12 HORAS', '100+/-5', '7.5 H', NULL, NULL, '578.0', NULL, NULL, 40.00),
(14, 'ANTICORROSIVO GRIS', 'ANT014', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '813.0', NULL, NULL, 1550.00),
(15, 'ANTICORROSIVO NEGRO', 'ANT015', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '168.0', NULL, NULL, 4617.00),
(16, 'ANTICORROSIVO AMARILLO', 'ANT016', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '212.0', NULL, NULL, 8640.00),
(17, 'ANTICORROSIVO ROJO', 'ANT017', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '213.0', NULL, NULL, 14300.00),
(18, 'ANTICORROSIVO BLANCO', 'ANT018', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '801.0', NULL, NULL, 855.00),
(19, 'ANTICORROSIVO VERDE', 'ANT019', 0, 3, '105-110 KU', '4.2+/-0.05 Kg', NULL, 'MATE', '6 HORAS', '100+/-5', '5,5', NULL, NULL, '178.0', NULL, NULL, 5400.00),
(20, 'PASTA ESMALTE VERDE ENTONADOR', 'PAS020', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '328.0', NULL, NULL, 8105.00),
(21, 'PASTA ESMALTE AZUL ENTONADOR', 'PAS021', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '345.0', NULL, NULL, 12215.00),
(22, 'PASTA ESMALTE NEGRO', 'PAS022', 2, 2, '100 KU', '4,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '488.1', NULL, NULL, 19945.00),
(23, 'PASTA ESMALTE ROJO CARMIN 57:1', 'PAS023', 0, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '119.0', NULL, NULL, 14152.00),
(24, 'PASTA ESMALTE NARANJA', 'PAS024', 2, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '961.0', NULL, NULL, 11447.00),
(25, 'PASTA ESMALTE AMARILLO', 'PAS025', 0, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '1018.0', NULL, NULL, 12718.00),
(26, 'PASTA ESMALTE CAOBA', 'PAS026', 2, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '874.0', NULL, NULL, 7742.00),
(27, 'PASTA ESMALTE AMARILLO OXIDO', 'PAS027', 2, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '851.0', NULL, NULL, 11447.00),
(28, 'PASTA ESMALTE ROJO OXIDO', 'PAS028', 0, 2, '100 KU', '5,55', 'STD', NULL, NULL, NULL, '>7H', '-', 'STD', '833.0', NULL, NULL, 1690.00),
(29, 'PASTA ESMALTE BLANCO', 'PAS029', 0, 2, '120', '5,78', 'STD', NULL, NULL, NULL, '7,5', '-', '100 +/- 0.5 %', '748.0', NULL, NULL, 10303.00),
(30, 'PASTA ESMALTE TABACO', 'PAS030', 2, 2, '95-100', '5.71-5.91', 'STD', NULL, NULL, NULL, '7,5', '-', 'STD', '376.0', NULL, NULL, 722.00),
(31, 'RESINA MEDIA EN SOYA AL 50%', 'RAM014', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 715.00),
(32, 'METIL ETIL CETOXIMA', 'AAN002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4300.00),
(33, 'OCTOATO DE COBALTO AL 12%', 'SOC011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4400.00),
(34, 'OCTOATO DE ZIRCONIO AL 24%', 'SOZ024', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00),
(35, 'OCTOATO DE CALCIO AL 10%', 'SOC010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00),
(36, 'DISOLVENTE 2232 #3', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1103.00),
(37, 'DIOXIDO DE TITANIO SULFATO', 'PED010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(38, 'OCTOATO DE ZINC AL 16%', 'SOZ016', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 43900.00),
(39, 'BENTOCLAY BP 184', 'AAS005', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 37300.00),
(40, 'ETANOL AL 96%', 'SAA022', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(41, 'DISASTAB GAT', 'AEM005', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7000.00),
(42, 'AGUA', 'SIA040', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19500.00),
(43, 'SULFATO DE MAGNESIO', 'AET004', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 33500.00),
(44, 'VARSOL', 'SAV010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 37200.00),
(46, 'DISASTAB GAT', 'AEM004', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10400.00),
(47, 'MICROTALC C 20', 'CTA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8000.00),
(48, 'CELITE 499', 'MSI006', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11466.00),
(50, 'PASTA ESMALTE ROJO 57:1', 'PE1033', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17000.00),
(52, 'PASTA AMARILLO CROMO MEDIO', 'PE1010', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17000.00),
(54, 'PASTA VERDE FTALO', 'PE1040', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4617.00),
(56, 'PASTA ESMALTE AZUL FTALO 15:3', 'PE1021', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(57, 'OMYACARB UF', 'CCC002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11000.00),
(59, 'MICROTALC C 20', 'CTA025', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(60, 'CARBONATO DE CALCIO HI WHITE', 'CCC004', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(61, 'LECITINA DE SOYA', 'AHU002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(62, 'ETANOL AL 96%', 'SAM023', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(63, 'OXIDO DE HIERRO AMARILLO Y 4021', 'PEA010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(64, 'OXIDO DE HIERRO ROJO R-5530', 'PER030', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(65, 'MICROTALC 20', 'CTA020', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(66, 'TROYSPERSE CD1', 'ADI002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(67, 'PIGMENTO VERDE FTALO 7', 'PEV053', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(68, 'PIGMENTO AZUL FTALO 15;3', 'PEA041', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(69, 'EDAPLAN 918 / LANSPERSE SUV', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(70, 'RESINA MEDIA EN SOYA AL 50%', 'MS-45', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(71, 'POW CARBON BLACK CHEMO', 'PEN081', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(72, 'PIGMENTO ROJO CARMIN 57:1', 'PER031', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(73, 'PIGMENTO NARANJA MOLIBDENO', 'PEN023', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(74, 'PIGMENTO MARILLO DE CROMO AL 73', 'PEA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(75, 'PIGMENTO OXIFERR CAOBA MARRON M 4781', 'PEC081', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(76, 'PIGMENTO OXIFERR AMARILLO Y-4011', 'PEA013', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(77, 'DIOXIDO DE TITANIO SULFATO 2196', 'PED007', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(78, 'OXIFER TABACO R-4370', 'PET080', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8105.00),
(79, 'BENTOCLAY BP 184', 'AAS012', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(80, 'METANOL', 'SAM023', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(81, 'ORGANOCLAY BK 884', 'AAS005', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(83, 'DISOLVENTE 2232 / VARSOL', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(84, 'EDAPLAN 915', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(85, 'CHEMOSPERSE 77', 'ADI011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(86, 'ADIMON 84', 'AAN002', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(87, 'DISOLVENTE #3', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4372.00),
(88, 'ETANOL 96%', 'SAA022', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4400.00),
(89, 'DISOLVENTE 2232', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4372.00),
(90, 'DISOLVENTE 3', 'SAA011', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4372.00),
(92, 'OCTOATO DE ZINC 16%', 'SOZ016', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 16300.00),
(93, 'PASTA ESMALTE AMARILLO CROMO MEDIO', 'PE1010', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 14152.00),
(94, 'DIOXIDO DE TITANIO SULFATO 2196', 'PED010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11466.00),
(95, 'BENTOCLAY BP184', 'AAS005', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17000.00),
(96, 'PASTA ESMALTE AZUL 15:3', 'PE1021', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11447.00),
(97, 'EDAPLAN 918', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(98, 'EDAPLAN 918 / LANSPERSE SUV', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(99, 'CHEMOSPERSE 77', 'ADI010', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22700.00),
(100, 'PIGMENTO OXIFERR ROJO R-5530', 'PER030', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_general_formulaciones`
--

CREATE TABLE `item_general_formulaciones` (
  `id_item_general_formulaciones` int(11) NOT NULL,
  `formulaciones_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `porcentaje` int(11) DEFAULT NULL,
  `item_general_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Estructura de tabla para la tabla `preparaciones_has_item_general`
--

CREATE TABLE `preparaciones_has_item_general` (
  `preparaciones_id_preparaciones` int(11) NOT NULL,
  `item_general_id_item_general` int(11) NOT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `porcentajes` int(11) DEFAULT NULL
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
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bodegas`
--
ALTER TABLE `bodegas`
  ADD CONSTRAINT `fk_bodegas_instalaciones1` FOREIGN KEY (`instalaciones_id`) REFERENCES `instalaciones` (`id_instalaciones`) ON UPDATE CASCADE;

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
-- Filtros para la tabla `instalaciones`
--
ALTER TABLE `instalaciones`
  ADD CONSTRAINT `fk_instalaciones_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `fk_inventario_bodega` FOREIGN KEY (`bodegas_id`) REFERENCES `bodegas` (`id_bodegas`),
  ADD CONSTRAINT `fk_inventario_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_inventario_movimientos_inventario1` FOREIGN KEY (`movimiento_inventario_id`) REFERENCES `movimiento_inventario` (`id_movimiento_inventario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `item_general`
--
ALTER TABLE `item_general`
  ADD CONSTRAINT `fk_item_general_categoria1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id_categoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_item_general_unidad_id` FOREIGN KEY (`unidad_id`) REFERENCES `unidad` (`id_unidad`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Filtros para la tabla `item_general_formulaciones`
--
ALTER TABLE `item_general_formulaciones`
  ADD CONSTRAINT `fk_item_general_formulaciones_item_general1` FOREIGN KEY (`item_general_id`) REFERENCES `item_general` (`id_item_general`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_item_general_has_formulaciones_formulaciones1` FOREIGN KEY (`formulaciones_id`) REFERENCES `formulaciones` (`id_formulaciones`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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

--
-- Filtros para la tabla `preparaciones_has_item_general`
--
ALTER TABLE `preparaciones_has_item_general`
  ADD CONSTRAINT `fk_preparaciones_has_item_general_item_general1` FOREIGN KEY (`item_general_id_item_general`) REFERENCES `item_general` (`id_item_general`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_preparaciones_has_item_general_preparaciones1` FOREIGN KEY (`preparaciones_id_preparaciones`) REFERENCES `preparaciones` (`id_preparaciones`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
