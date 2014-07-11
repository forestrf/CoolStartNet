-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-07-2014 a las 03:37:41
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
-- Estructura de tabla para la tabla `contenido`
--

CREATE TABLE IF NOT EXISTS `contenido` (
  `hash` varchar(32) COLLATE utf8_bin NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(15) COLLATE utf8_bin NOT NULL,
  `password` varchar(30) COLLATE utf8_bin NOT NULL,
  `RND` varchar(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `nick` (`nick`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variables`
--

CREATE TABLE IF NOT EXISTS `variables` (
  `IDusuario` int(11) NOT NULL,
  `IDwidget` int(11) NOT NULL,
  `variable` varchar(30) COLLATE utf8_bin NOT NULL,
  `valor` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`IDusuario`,`IDwidget`,`variable`),
  KEY `IDUsuario` (`IDusuario`),
  KEY `IDWidget` (`IDwidget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets`
--

CREATE TABLE IF NOT EXISTS `widgets` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8_bin NOT NULL,
  `propietarioID` int(11) NOT NULL,
  `publicado` int(11) NOT NULL DEFAULT '-1' COMMENT 'Si se publica cambiar a 0 o + desde php. Nunca volver a -1',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets-contenido`
--

CREATE TABLE IF NOT EXISTS `widgets-contenido` (
  `IDwidget` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `nombre` varchar(30) COLLATE utf8_bin NOT NULL,
  `hash` varchar(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`IDwidget`,`version`,`nombre`),
  KEY `IDwidget` (`IDwidget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets-usuario`
--

CREATE TABLE IF NOT EXISTS `widgets-usuario` (
  `IDusuario` int(11) NOT NULL,
  `IDwidget` int(11) NOT NULL,
  `autoupdate` tinyint(1) NOT NULL DEFAULT '1',
  `version` int(11) NOT NULL COMMENT 'Mirar cuando autoupdate = 0',
  PRIMARY KEY (`IDusuario`,`IDwidget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets-versiones`
--

CREATE TABLE IF NOT EXISTS `widgets-versiones` (
  `IDwidget` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `publico` tinyint(1) NOT NULL COMMENT '0 = privada, 1 = pública',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = oculto, 1 = visible',
  `comentario` tinytext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`IDwidget`,`version`),
  KEY `IDwidget` (`IDwidget`)
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
