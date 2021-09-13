-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2021 at 03:47 AM
-- Server version: 10.4.20-MariaDB
-- PHP Version: 8.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbrobot`
--
CREATE DATABASE IF NOT EXISTS `dbrobot` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish2_ci;
USE `dbrobot`;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `nombre` varchar(30) COLLATE utf8_spanish2_ci NOT NULL,
  `x` float UNSIGNED NOT NULL,
  `y` float UNSIGNED NOT NULL,
  `z` float UNSIGNED NOT NULL,
  `pinConfig` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`nombre`, `x`, `y`, `z`, `pinConfig`) VALUES
('Origen', 0, 0, 10, 1),
('Lavado', 63.61, 70, 50, 1),
('Vacío', 63.61, 12.86, 41, 1),
('Limpieza', 46.1, 121.34, 49.3, 1),
('Muestra', 12.52, 232.45, 52.3, 1),
('Retícula', 168.51, 4.92, 59.2, 1),
('Usuario', 300, 250, 0, 1),
('Origen', 0, 0, 10, 2),
('Lavado', 63.64, 62, 60, 2),
('Vacío', 63.64, 9.98, 54, 2),
('Limpieza', 46.6, 119.44, 63, 2),
('Muestra', 12.77, 230.09, 66.3, 2),
('Retícula', 168.27, 2.33, 68, 2),
('Usuario', 300, 250, 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `lavado`
--

DROP TABLE IF EXISTS `lavado`;
CREATE TABLE `lavado` (
  `ciclos` int(11) NOT NULL,
  `oscilaciones` int(11) NOT NULL,
  `toques` int(11) NOT NULL,
  `vacio` int(11) NOT NULL,
  `uvacio` int(11) NOT NULL,
  `tmuestra` int(3) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `lavado`
--

INSERT INTO `lavado` (`ciclos`, `oscilaciones`, `toques`, `vacio`, `uvacio`, `tmuestra`, `ID`) VALUES
(1, 3, 6, 1, 1, 2, '2020-12-7-12-34-12'),
(1, 1, 3, 1, 1, 1, '2020-2-10-11-15-17'),
(1, 1, 1, 1, 1, 1, '2020-2-10-12-3-21');

-- --------------------------------------------------------

--
-- Table structure for table `pines`
--

DROP TABLE IF EXISTS `pines`;
CREATE TABLE `pines` (
  `PinesX` int(10) NOT NULL,
  `PinesY` int(10) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `pines`
--

INSERT INTO `pines` (`PinesX`, `PinesY`, `ID`) VALUES
(12, 4, '2020-12-7-12-34-12'),
(6, 4, '2020-2-10-11-15-17'),
(12, 4, '2020-2-10-12-3-21');

-- --------------------------------------------------------

--
-- Table structure for table `raspberry`
--

DROP TABLE IF EXISTS `raspberry`;
CREATE TABLE `raspberry` (
  `id` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
  `nombre` varchar(30) COLLATE utf8_spanish2_ci NOT NULL,
  `tipo` varchar(10) COLLATE utf8_spanish2_ci NOT NULL,
  `valor` varchar(10) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `raspberry`
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
-- Table structure for table `reticula`
--

DROP TABLE IF EXISTS `reticula`;
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

--
-- Dumping data for table `reticula`
--

INSERT INTO `reticula` (`XCoords`, `YCoords`, `XSpace`, `YSpace`, `XDots`, `YDots`, `DuplicateDots`, `PlateState`, `TotalPlates`, `ID`) VALUES
(3, 3, 300, 300, 8, 8, 8, 1, 1, '2020-12-7-12-34-12'),
(5, 5, 500, 600, 5, 4, 2, 1, 0.625, '2020-2-10-11-15-17'),
(5, 5, 200, 200, 2, 2, 1, 1, 0.5, '2020-2-10-12-3-21');

-- --------------------------------------------------------

--
-- Table structure for table `rutinas`
--

DROP TABLE IF EXISTS `rutinas`;
CREATE TABLE `rutinas` (
  `nombreRutina` varchar(30) COLLATE utf8_spanish2_ci NOT NULL,
  `Temporal` tinyint(1) NOT NULL,
  `rutinaIniciada` int(1) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `rutinas`
--

INSERT INTO `rutinas` (`nombreRutina`, `Temporal`, `rutinaIniciada`, `ID`) VALUES
('Oficial', 0, 0, '2020-12-7-12-34-12'),
('Pi', 0, 0, '2020-2-10-11-15-17'),
('Prueba', 0, 0, '2020-2-10-12-3-21');

-- --------------------------------------------------------

--
-- Table structure for table `slide`
--

DROP TABLE IF EXISTS `slide`;
CREATE TABLE `slide` (
  `columnasplaca` int(11) NOT NULL,
  `filasplaca` int(11) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `slide`
--

INSERT INTO `slide` (`columnasplaca`, `filasplaca`, `ID`) VALUES
(1, 2, '2020-12-7-12-34-12'),
(2, 3, '2020-2-10-11-15-17'),
(1, 1, '2020-2-10-12-3-21');

-- --------------------------------------------------------

--
-- Table structure for table `tipopin`
--

DROP TABLE IF EXISTS `tipopin`;
CREATE TABLE `tipopin` (
  `IDPin` varchar(10) COLLATE utf8_spanish2_ci NOT NULL,
  `nombrePin` varchar(20) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `PinSelect` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `tipopin`
--

INSERT INTO `tipopin` (`IDPin`, `nombrePin`, `PinSelect`) VALUES
('1', 'ceramico', 0),
('2', 'acero', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lavado`
--
ALTER TABLE `lavado`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `pines`
--
ALTER TABLE `pines`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `reticula`
--
ALTER TABLE `reticula`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `rutinas`
--
ALTER TABLE `rutinas`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `slide`
--
ALTER TABLE `slide`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tipopin`
--
ALTER TABLE `tipopin`
  ADD PRIMARY KEY (`IDPin`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
