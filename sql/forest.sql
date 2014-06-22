-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-06-2014 a las 01:22:35
-- Versión del servidor: 5.5.32
-- Versión de PHP: 5.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `forest`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nick` text COLLATE utf8_bin NOT NULL,
  `password` text COLLATE utf8_bin NOT NULL,
  `RND` varchar(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variables`
--

CREATE TABLE IF NOT EXISTS `variables` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDusuario` int(11) NOT NULL,
  `IDwidget` int(11) NOT NULL,
  `variable` varchar(30) COLLATE utf8_bin NOT NULL,
  `valor` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IDWidget` (`IDwidget`,`variable`),
  KEY `IDWidget_2` (`IDwidget`),
  KEY `IDUsuario` (`IDusuario`),
  KEY `IDUsuario_2` (`IDusuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets`
--

CREATE TABLE IF NOT EXISTS `widgets` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8_bin NOT NULL,
  `variables` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets-usuario`
--

CREATE TABLE IF NOT EXISTS `widgets-usuario` (
  `IDusuario` int(11) NOT NULL,
  `IDwidget` int(11) NOT NULL,
  KEY `IDusuario` (`IDusuario`,`IDwidget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `variables`
--
ALTER TABLE `variables`
  ADD CONSTRAINT `variables_ibfk_1` FOREIGN KEY (`IDUsuario`) REFERENCES `usuarios` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `variables_ibfk_2` FOREIGN KEY (`IDWidget`) REFERENCES `widgets` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
