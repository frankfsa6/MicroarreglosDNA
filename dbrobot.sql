-- MySQL dump 10.18  Distrib 10.3.27-MariaDB, for debian-linux-gnueabihf (armv8l)
--
-- Host: localhost    Database: dbrobot
-- ------------------------------------------------------
-- Server version	10.3.27-MariaDB-0+deb10u1

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
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `nombre` varchar(30) COLLATE utf8_spanish2_ci NOT NULL,
  `x` float unsigned NOT NULL,
  `y` float unsigned NOT NULL,
  `z` float unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES ('Origen',0,0,0),('Lavado',61.98,63.426,59.752),('Vacío',61.98,8.814,53.9645),('Limpieza',45.438,120.444,60.4),('Muestra',10.638,228.93,63.596),('Retícula',167.43,3.27,65),('Usuario',300,250,0);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lavado`
--

DROP TABLE IF EXISTS `lavado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lavado` (
  `ciclos` int(11) NOT NULL,
  `oscilaciones` int(11) NOT NULL,
  `toques` int(11) NOT NULL,
  `vacio` int(11) NOT NULL,
  `uvacio` int(11) NOT NULL,
  `tmuestra` int(3) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lavado`
--

LOCK TABLES `lavado` WRITE;
/*!40000 ALTER TABLE `lavado` DISABLE KEYS */;
INSERT INTO `lavado` VALUES (1,3,6,1,1,2,'2020-12-7-12-34-12'),(1,1,3,1,1,1,'2020-2-10-11-15-17'),(1,1,1,1,1,1,'2020-2-10-12-3-21');
/*!40000 ALTER TABLE `lavado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pines`
--

DROP TABLE IF EXISTS `pines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pines` (
  `PinesX` int(10) NOT NULL,
  `PinesY` int(10) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pines`
--

LOCK TABLES `pines` WRITE;
/*!40000 ALTER TABLE `pines` DISABLE KEYS */;
INSERT INTO `pines` VALUES (12,4,'2020-12-7-12-34-12'),(6,4,'2020-2-10-11-15-17'),(12,4,'2020-2-10-12-3-21');
/*!40000 ALTER TABLE `pines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raspberry`
--

DROP TABLE IF EXISTS `raspberry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raspberry` (
  `id` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
  `nombre` varchar(30) COLLATE utf8_spanish2_ci NOT NULL,
  `tipo` varchar(10) COLLATE utf8_spanish2_ci NOT NULL,
  `valor` varchar(10) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raspberry`
--

LOCK TABLES `raspberry` WRITE;
/*!40000 ALTER TABLE `raspberry` DISABLE KEYS */;
INSERT INTO `raspberry` VALUES ('pulX','Motor en eje X','gpio','19'),('dirX','Dirección en eje X','gpio','16'),('pulY','Motor en eje Y','gpio','20'),('dirY','Dirección en eje Y','gpio','6'),('pulZ','Motor en eje Z','gpio','12'),('dirZ','Dirección en eje Z','gpio','5'),('limX','Sensor de límite en eje X','gpio','21'),('limY','Sensor de límite en eje Y','gpio','27'),('limZ','Sensor de límite en eje Z','gpio','4'),('bomV','Bomba de vacío','gpio','22'),('bomA','Bomba de agua','gpio','23'),('botE','Botón de emergencia','gpio','26'),('pasosRevX','Pasos / rev','pasos','2000'),('pasosRevY','Pasos / rev','pasos','2000'),('pasosRevZ','Pasos / rev','pasos','2000'),('torX','mm / rev','tornillo','12'),('torY','mm / rev','tornillo','12'),('torZ','mm / rev','tornillo','8.04');
/*!40000 ALTER TABLE `raspberry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reticula`
--

DROP TABLE IF EXISTS `reticula`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reticula`
--

LOCK TABLES `reticula` WRITE;
/*!40000 ALTER TABLE `reticula` DISABLE KEYS */;
INSERT INTO `reticula` VALUES (3,3,300,300,8,8,8,1,1,'2020-12-7-12-34-12'),(5,5,500,600,5,4,2,1,0.625,'2020-2-10-11-15-17'),(5,5,200,200,2,2,1,1,0.5,'2020-2-10-12-3-21');
/*!40000 ALTER TABLE `reticula` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rutinas`
--

DROP TABLE IF EXISTS `rutinas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rutinas` (
  `nombreRutina` varchar(30) COLLATE utf8_spanish2_ci NOT NULL,
  `Temporal` tinyint(1) NOT NULL,
  `rutinaIniciada` int(1) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rutinas`
--

LOCK TABLES `rutinas` WRITE;
/*!40000 ALTER TABLE `rutinas` DISABLE KEYS */;
INSERT INTO `rutinas` VALUES ('Oficial',0,0,'2020-12-7-12-34-12'),('Pi',0,0,'2020-2-10-11-15-17'),('Prueba',0,0,'2020-2-10-12-3-21');
/*!40000 ALTER TABLE `rutinas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slide`
--

DROP TABLE IF EXISTS `slide`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slide` (
  `columnasplaca` int(11) NOT NULL,
  `filasplaca` int(11) NOT NULL,
  `ID` varchar(19) COLLATE utf8_spanish2_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slide`
--

LOCK TABLES `slide` WRITE;
/*!40000 ALTER TABLE `slide` DISABLE KEYS */;
INSERT INTO `slide` VALUES (1,2,'2020-12-7-12-34-12'),(2,3,'2020-2-10-11-15-17'),(1,1,'2020-2-10-12-3-21');
/*!40000 ALTER TABLE `slide` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-07-02 13:46:51
