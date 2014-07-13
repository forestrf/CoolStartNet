-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-07-2014 a las 15:53:04
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
-- Estructura de tabla para la tabla `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `hash` varchar(32) COLLATE utf8_bin NOT NULL,
  `data` mediumblob NOT NULL,
  `mimetype` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'text/plain',
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(15) COLLATE utf8_bin NOT NULL,
  `password` varchar(32) COLLATE utf8_bin NOT NULL,
  `RND` varchar(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `nick` (`nick`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variables`
--

CREATE TABLE IF NOT EXISTS `variables` (
  `IDuser` int(11) NOT NULL,
  `IDwidget` int(11) NOT NULL,
  `variable` varchar(30) COLLATE utf8_bin NOT NULL,
  `value` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`IDuser`,`IDwidget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets`
--

CREATE TABLE IF NOT EXISTS `widgets` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_bin NOT NULL,
  `ownerID` int(11) NOT NULL,
  `published` int(11) NOT NULL DEFAULT '-1' COMMENT 'Si se publica cambiar a 0 o + desde php. Nunca volver a -1',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets-content`
--

CREATE TABLE IF NOT EXISTS `widgets-content` (
  `IDwidget` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `hash` varchar(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`IDwidget`,`version`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets-user`
--

CREATE TABLE IF NOT EXISTS `widgets-user` (
  `IDuser` int(11) NOT NULL,
  `IDwidget` int(11) NOT NULL,
  `autoupdate` tinyint(1) NOT NULL DEFAULT '1',
  `version` int(11) NOT NULL COMMENT 'Mirar cuando autoupdate = 0',
  PRIMARY KEY (`IDuser`,`IDwidget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets-versions`
--

CREATE TABLE IF NOT EXISTS `widgets-versions` (
  `IDwidget` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL COMMENT '0 = privada, 1 = pública',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = oculto, 1 = visible',
  `coment` tinytext COLLATE utf8_bin NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IDwidget`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
