-- phpMyAdmin SQL Dump
-- version 4.2.7
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 23, 2014 at 02:16 AM
-- Server version: 5.5.39
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db`
--

-- --------------------------------------------------------

--
-- Table structure for table `access-token`
--

CREATE TABLE `access-token` (
	`IDuser` int(11) NOT NULL,
	`dropbox_accessToken` text COLLATE utf8_bin NOT NULL,
	PRIMARY KEY (`IDuser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
	`hash` varchar(32) COLLATE utf8_bin NOT NULL,
	`data` mediumblob NOT NULL,
	PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `session_data`
--

CREATE TABLE `session_data` (
	`session_id` varchar(32) NOT NULL DEFAULT '',
	`hash` varchar(32) NOT NULL DEFAULT '',
	`session_data` blob NOT NULL,
	`session_expire` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
	`IDuser` int(11) NOT NULL AUTO_INCREMENT,
	`nick` varchar(15) COLLATE utf8_bin NOT NULL,
	`password` varchar(32) COLLATE utf8_bin NOT NULL,
	`email` varchar(50) COLLATE utf8_bin NOT NULL,
	`RND` varchar(32) COLLATE utf8_bin NOT NULL,
	`level` int(11) NOT NULL DEFAULT '0',
	`validation` varchar(5) COLLATE utf8_bin NOT NULL,
	`recover_code_due_date` date NOT NULL,
	`creation_date` date NOT NULL,
	PRIMARY KEY (`IDuser`),
	UNIQUE KEY `nick` (`nick`),
	UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `variables`
--

CREATE TABLE `variables` (
	`IDuser` int(11) NOT NULL,
	`IDwidget` int(11) NOT NULL,
	`variable` varchar(30) COLLATE utf8_bin NOT NULL,
	`value` text COLLATE utf8_bin NOT NULL,
	PRIMARY KEY (`IDuser`,`IDwidget`,`variable`),
	KEY `variables IDwidget` (`IDwidget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `widgets`
--

CREATE TABLE `widgets` (
	`IDwidget` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(30) COLLATE utf8_bin NOT NULL,
	`ownerID` int(11) NOT NULL,
	`published` int(11) NOT NULL DEFAULT '-1' COMMENT 'Si se publica cambiar a 0 o + desde php. Nunca volver a -1',
	`creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`IDwidget`),
	UNIQUE KEY `name` (`name`),
	KEY `ownerID` (`ownerID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `widgets-content`
--

CREATE TABLE `widgets-content` (
	`IDwidget` int(11) NOT NULL,
	`version` int(11) NOT NULL,
	`name` varchar(50) COLLATE utf8_bin NOT NULL,
	`hash` varchar(32) COLLATE utf8_bin NOT NULL,
	`mimetype` text COLLATE utf8_bin NOT NULL,
	PRIMARY KEY (`IDwidget`,`version`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `widgets-user`
--

CREATE TABLE `widgets-user` (
	`IDuser` int(11) NOT NULL,
	`IDwidget` int(11) NOT NULL,
	`autoupdate` tinyint(1) NOT NULL DEFAULT '1',
	`version` int(11) NOT NULL COMMENT 'Mirar cuando autoupdate = 0',
	PRIMARY KEY (`IDuser`,`IDwidget`),
	KEY `widgets-user IDwidget` (`IDwidget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `widgets-versions`
--

CREATE TABLE `widgets-versions` (
	`IDwidget` int(11) NOT NULL,
	`version` int(11) NOT NULL,
	`public` tinyint(1) NOT NULL COMMENT '0 = privada, 1 = pública',
	`visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = oculto, 1 = visible',
	`comment` tinytext COLLATE utf8_bin NOT NULL,
	`creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`IDwidget`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `variables`
--
ALTER TABLE `variables`
ADD CONSTRAINT `variables IDwidget` FOREIGN KEY (`IDwidget`) REFERENCES `widgets` (`IDwidget`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `variables IDuser` FOREIGN KEY (`IDuser`) REFERENCES `users` (`IDuser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `widgets`
--
ALTER TABLE `widgets`
ADD CONSTRAINT `widget owner` FOREIGN KEY (`ownerID`) REFERENCES `users` (`IDuser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `widgets-content`
--
ALTER TABLE `widgets-content`
ADD CONSTRAINT `widgets-content IDwidget` FOREIGN KEY (`IDwidget`) REFERENCES `widgets` (`IDwidget`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `widgets-user`
--
ALTER TABLE `widgets-user`
ADD CONSTRAINT `widgets-user IDuser` FOREIGN KEY (`IDuser`) REFERENCES `users` (`IDuser`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `widgets-user IDwidget` FOREIGN KEY (`IDwidget`) REFERENCES `widgets` (`IDwidget`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `widgets-versions`
--
ALTER TABLE `widgets-versions`
ADD CONSTRAINT `widgets-versions IDwidget` FOREIGN KEY (`IDwidget`) REFERENCES `widgets` (`IDwidget`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;





-- This will allow global variables
INSERT INTO `users` (`IDuser`, `nick`, `password`, `email`, `RND`, `level`, `validation`, `recover_code_due_date`, `creation_date`) VALUES ('0', 'global', '-', '-', '-', '0', '', '', '');
INSERT INTO `widgets` (`IDwidget`, `name`, `ownerID`, `published`) VALUES ('-1', 'global', '0', '-1');
