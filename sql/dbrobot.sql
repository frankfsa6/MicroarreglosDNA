-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-09-2021 a las 04:11:00
-- Versión del servidor: 10.4.21-MariaDB
-- Versión de PHP: 8.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dbrobot`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config`
--

CREATE TABLE `config` (
  `nombre` varchar(30) COLLATE utf8_spanish2_ci NOT NULL,
  `x` float UNSIGNED NOT NULL,
  `y` float UNSIGNED NOT NULL,
  `z` float UNSIGNED NOT NULL,
  `IDPin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `config`
--

INSERT INTO `config` (`nombre`, `x`, `y`, `z`, `IDPin`) VALUES
('Origen', 0, 0, 10, 1),
('Vacío', 63.61, 12.86, 41, 1),
('Lavado', 63.61, 70, 50, 1),
('Limpieza', 46.1, 121.34, 49.3, 1),
('Muestra', 12.52, 232.45, 52.3, 1),
('Retícula', 168.51, 4.92, 54.2, 1),
('Usuario', 300, 250, 0, 1),
('Origen', 0, 0, 10, 2),
('Vacío', 63.64, 9.98, 54, 2),
('Lavado', 63.64, 62, 60, 2),
('Limpieza', 46.6, 119.44, 63, 2),
('Muestra', 12.77, 230.09, 66.3, 2),
('Retícula', 168.27, 2.33, 68, 2),
('Usuario', 300, 250, 0, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lavado`
--

CREATE TABLE `lavado` (
  `ciclos` int(11) NOT NULL,
  `oscilaciones` int(11) NOT NULL,
  `toques` int(11) NOT NULL,
  `vacio` int(11) NOT NULL,
  `uvacio` int(11) NOT NULL,
  `tmuestra` int(3) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pines`
--

CREATE TABLE `pines` (
  `PinesX` int(10) NOT NULL,
  `PinesY` int(10) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL,
  `IDPin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `raspberry`
--

CREATE TABLE `raspberry` (
  `id` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
  `nombre` varchar(30) COLLATE utf8_spanish2_ci NOT NULL,
  `tipo` varchar(10) COLLATE utf8_spanish2_ci NOT NULL,
  `valor` varchar(10) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `raspberry`
--

INSERT INTO `raspberry` (`id`, `nombre`, `tipo`, `valor`) VALUES
('pulX', 'Motor en eje X', 'gpio', '19'),
('dirX', 'Dirección en eje X', 'gpio', '16'),
('pulY', 'Motor en eje Y', 'gpio', '20'),
('dirY', 'Dirección en eje Y', 'gpio', '6'),
('pulZ', 'Motor en eje Z', 'gpio', '12'),
('dirZ', 'Dirección en eje Z', 'gpio', '5'),
('limX', 'Sensor de límite en eje X', 'gpio', '21'),
('limY', 'Sensor de límite en eje Y', 'gpio', '27'),
('limZ', 'Sensor de límite en eje Z', 'gpio', '4'),
('bomV', 'Bomba de vacío', 'gpio', '22'),
('bomA', 'Bomba de agua', 'gpio', '23'),
('botE', 'Botón de emergencia', 'gpio', '26'),
('pasosRevX', 'Pasos / rev', 'pasos', '2000'),
('pasosRevY', 'Pasos / rev', 'pasos', '2000'),
('pasosRevZ', 'Pasos / rev', 'pasos', '2000'),
('torX', 'mm / rev', 'tornillo', '12'),
('torY', 'mm / rev', 'tornillo', '12'),
('torZ', 'mm / rev', 'tornillo', '8.04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reticula`
--

CREATE TABLE `reticula` (
  `XCoords` float NOT NULL,
  `YCoords` float NOT NULL,
  `XSpace` float NOT NULL,
  `YSpace` float NOT NULL,
  `XDots` int(3) NOT NULL,
  `YDots` int(3) NOT NULL,
  `DuplicateDots` int(3) NOT NULL,
  `PlateState` tinyint(1) NOT NULL,
  `TotalPlates` float NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutinas`
--

CREATE TABLE `rutinas` (
  `nombreRutina` varchar(30) COLLATE utf8_spanish2_ci NOT NULL,
  `Temporal` tinyint(1) NOT NULL,
  `rutinaIniciada` int(1) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `slide`
--

CREATE TABLE `slide` (
  `columnasplaca` int(11) NOT NULL,
  `filasplaca` int(11) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipopin`
--

CREATE TABLE `tipopin` (
  `IDPin` varchar(10) COLLATE utf8_spanish2_ci NOT NULL,
  `nombrePin` varchar(20) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `PinSelect` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `tipopin`
--

INSERT INTO `tipopin` (`IDPin`, `nombrePin`, `PinSelect`) VALUES
('1', 'ceramico', 0),
('2', 'acero', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `lavado`
--
ALTER TABLE `lavado`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pines`
--
ALTER TABLE `pines`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `reticula`
--
ALTER TABLE `reticula`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `rutinas`
--
ALTER TABLE `rutinas`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `slide`
--
ALTER TABLE `slide`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `tipopin`
--
ALTER TABLE `tipopin`
  ADD PRIMARY KEY (`IDPin`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
