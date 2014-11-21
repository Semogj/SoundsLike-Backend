-- phpMyAdmin SQL Dump
-- version 3.5.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 20, 2013 at 12:27 PM
-- Server version: 5.5.28-1-log
-- PHP Version: 5.4.4-9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `virus_as`
--

-- --------------------------------------------------------

--
-- Table structure for table `SoundSegment`
--

CREATE TABLE IF NOT EXISTS `SoundSegment` (
  `idSoundSegment` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start` decimal(10,3) unsigned NOT NULL,
  `end` decimal(10,3) unsigned NOT NULL,
  `videoId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idSoundSegment`),
  KEY `fk_SoundSegment_Video_idx` (`videoId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10563 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `SoundSegmentSimilarities`
--
CREATE TABLE IF NOT EXISTS `SoundSegmentSimilarities` (
`id1` int(10) unsigned
,`start1` decimal(10,3) unsigned
,`end1` decimal(10,3) unsigned
,`videoId1` int(10) unsigned
,`id2` int(10) unsigned
,`start2` decimal(10,3) unsigned
,`end2` decimal(10,3) unsigned
,`videoId2` int(10) unsigned
,`value` int(11) unsigned
,`lastUpdate` int(11)
);
-- --------------------------------------------------------

--
-- Table structure for table `SoundSimilarity`
--

CREATE TABLE IF NOT EXISTS `SoundSimilarity` (
  `soundSegmentId1` int(10) unsigned NOT NULL,
  `soundSegmentId2` int(10) unsigned NOT NULL,
  `value` int(11) unsigned NOT NULL COMMENT 'The first three digits (at the right) are the decimal value!',
  `lastUpdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`soundSegmentId1`,`soundSegmentId2`),
  KEY `soundSegmentId1` (`soundSegmentId1`),
  KEY `soundSegmentId2` (`soundSegmentId2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `SoundTag`
--

CREATE TABLE IF NOT EXISTS `SoundTag` (
  `idSoundTag` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tagName` varchar(30) NOT NULL DEFAULT '',
  `soundSegmentId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'other',
  `insertedTime` int(11) NOT NULL DEFAULT '-1',
  `confidence` int(4) unsigned NOT NULL DEFAULT '100' COMMENT 'The first two digits are the decimal value!',
  PRIMARY KEY (`idSoundTag`),
  UNIQUE KEY `SECOND_PRIMARY` (`soundSegmentId`,`userId`,`tagName`),
  KEY `fk_SoundTag_User1_idx` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50 ;

--
-- Triggers `SoundTag`
--
DROP TRIGGER IF EXISTS `SoundTag.insertedTime update`;
DELIMITER //
CREATE TRIGGER `SoundTag.insertedTime update` BEFORE INSERT ON `SoundTag`
 FOR EACH ROW if (new.insertedTime = -1)  
  then  
    set new.insertedTime = unix_timestamp();  
  end if
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `idUser` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userName` varchar(30) NOT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `Video`
--

CREATE TABLE IF NOT EXISTS `Video` (
  `idVideo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `textId` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `genres` varchar(200) NOT NULL DEFAULT '',
  `actors` varchar(500) NOT NULL DEFAULT '',
  `year` int(10) unsigned NOT NULL DEFAULT '0',
  `location` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`idVideo`),
  UNIQUE KEY `textId_UNIQUE` (`textId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Structure for view `SoundSegmentSimilarities`
--
DROP TABLE IF EXISTS `SoundSegmentSimilarities`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `SoundSegmentSimilarities` AS select `s1`.`idSoundSegment` AS `id1`,`s1`.`start` AS `start1`,`s1`.`end` AS `end1`,`s1`.`videoId` AS `videoId1`,`s2`.`idSoundSegment` AS `id2`,`s2`.`start` AS `start2`,`s2`.`end` AS `end2`,`s2`.`videoId` AS `videoId2`,`s`.`value` AS `value`,`s`.`lastUpdate` AS `lastUpdate` from ((`SoundSimilarity` `s` join `SoundSegment` `s1`) join `SoundSegment` `s2`) where ((`s`.`soundSegmentId1` = `s1`.`idSoundSegment`) and (`s`.`soundSegmentId2` = `s2`.`idSoundSegment`) and (`s`.`soundSegmentId1` <> `s`.`soundSegmentId2`));

--
-- Constraints for dumped tables
--

--
-- Constraints for table `SoundSegment`
--
ALTER TABLE `SoundSegment`
  ADD CONSTRAINT `SoundSegment_ibfk_1` FOREIGN KEY (`videoId`) REFERENCES `Video` (`idVideo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `SoundSimilarity`
--
ALTER TABLE `SoundSimilarity`
  ADD CONSTRAINT `SoundSimilarity_ibfk_1` FOREIGN KEY (`soundSegmentId1`) REFERENCES `SoundSegment` (`idSoundSegment`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `SoundSimilarity_ibfk_2` FOREIGN KEY (`soundSegmentId2`) REFERENCES `SoundSegment` (`idSoundSegment`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `SoundTag`
--
ALTER TABLE `SoundTag`
  ADD CONSTRAINT `SoundTag_ibfk_1` FOREIGN KEY (`soundSegmentId`) REFERENCES `SoundSegment` (`idSoundSegment`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `SoundTag_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `User` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
