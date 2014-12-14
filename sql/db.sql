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
  `dropbox_accessToken` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `hash` varchar(32) COLLATE utf8_bin NOT NULL,
  `data` mediumblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `session_data`
--

CREATE TABLE `session_data` (
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `hash` varchar(32) NOT NULL DEFAULT '',
  `session_data` blob NOT NULL,
  `session_expire` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
`ID` int(11) NOT NULL,
  `nick` varchar(15) COLLATE utf8_bin NOT NULL,
  `password` varchar(32) COLLATE utf8_bin NOT NULL,
  `email` varchar(50) COLLATE utf8_bin NOT NULL,
  `RND` varchar(32) COLLATE utf8_bin NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `validation` varchar(5) COLLATE utf8_bin NOT NULL,
  `recover_code_due_date` date NOT NULL,
  `creation_date` date NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=62 ;

-- --------------------------------------------------------

--
-- Table structure for table `variables`
--

CREATE TABLE `variables` (
  `IDuser` int(11) NOT NULL,
  `IDwidget` int(11) NOT NULL,
  `variable` varchar(30) COLLATE utf8_bin NOT NULL,
  `value` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `widgets`
--

CREATE TABLE `widgets` (
`ID` int(11) NOT NULL,
  `name` varchar(30) COLLATE utf8_bin NOT NULL,
  `ownerID` int(11) NOT NULL,
  `published` int(11) NOT NULL DEFAULT '-1' COMMENT 'Si se publica cambiar a 0 o + desde php. Nunca volver a -1',
  `tags` text COLLATE utf8_bin,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `widgets-content`
--

CREATE TABLE `widgets-content` (
  `IDwidget` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `hash` varchar(32) COLLATE utf8_bin NOT NULL,
  `mimetype` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `widgets-user`
--

CREATE TABLE `widgets-user` (
  `IDuser` int(11) NOT NULL,
  `IDwidget` int(11) NOT NULL,
  `autoupdate` tinyint(1) NOT NULL DEFAULT '1',
  `version` int(11) NOT NULL COMMENT 'Mirar cuando autoupdate = 0'
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
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access-token`
--
ALTER TABLE `access-token`
 ADD PRIMARY KEY (`IDuser`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
 ADD PRIMARY KEY (`hash`);

--
-- Indexes for table `session_data`
--
ALTER TABLE `session_data`
 ADD PRIMARY KEY (`session_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `nick` (`nick`), ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `variables`
--
ALTER TABLE `variables`
 ADD PRIMARY KEY (`IDuser`,`IDwidget`,`variable`), ADD KEY `variables IDwidget` (`IDwidget`);

--
-- Indexes for table `widgets`
--
ALTER TABLE `widgets`
 ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `name` (`name`), ADD KEY `ownerID` (`ownerID`);

--
-- Indexes for table `widgets-content`
--
ALTER TABLE `widgets-content`
 ADD PRIMARY KEY (`IDwidget`,`version`,`name`);

--
-- Indexes for table `widgets-user`
--
ALTER TABLE `widgets-user`
 ADD PRIMARY KEY (`IDuser`,`IDwidget`), ADD KEY `widgets-user IDwidget` (`IDwidget`);

--
-- Indexes for table `widgets-versions`
--
ALTER TABLE `widgets-versions`
 ADD PRIMARY KEY (`IDwidget`,`version`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=62;
--
-- AUTO_INCREMENT for table `widgets`
--
ALTER TABLE `widgets`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `variables`
--
ALTER TABLE `variables`
ADD CONSTRAINT `variables IDwidget` FOREIGN KEY (`IDwidget`) REFERENCES `widgets` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `variables IDuser` FOREIGN KEY (`IDuser`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `widgets`
--
ALTER TABLE `widgets`
ADD CONSTRAINT `widget owner` FOREIGN KEY (`ownerID`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `widgets-content`
--
ALTER TABLE `widgets-content`
ADD CONSTRAINT `widgets-content IDwidget` FOREIGN KEY (`IDwidget`) REFERENCES `widgets` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `widgets-user`
--
ALTER TABLE `widgets-user`
ADD CONSTRAINT `widgets-user IDuser` FOREIGN KEY (`IDuser`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `widgets-user IDwidget` FOREIGN KEY (`IDwidget`) REFERENCES `widgets` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `widgets-versions`
--
ALTER TABLE `widgets-versions`
ADD CONSTRAINT `widgets-versions IDwidget` FOREIGN KEY (`IDwidget`) REFERENCES `widgets` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;





-- This will allow global variables
INSERT INTO `users` (`ID`, `nick`, `password`, `email`, `RND`, `level`, `validation`, `recover_code_due_date`, `creation_date`) VALUES ('0', 'global', '-', '-', '-', '0', '', '', '');
INSERT INTO `widgets` (`ID`, `name`, `ownerID`, `published`) VALUES ('-1', 'global', '0', '-1');
