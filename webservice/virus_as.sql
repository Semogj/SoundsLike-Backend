-- phpMyAdmin SQL Dump
-- version 3.5.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 28, 2012 at 09:19 PM
-- Server version: 5.5.28-1
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
CREATE DATABASE `virus_as` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `virus_as`;

-- --------------------------------------------------------

--
-- Table structure for table `SoundSegment`
--

CREATE TABLE IF NOT EXISTS `SoundSegment` (
  `idSoundSegment` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start` int(10) unsigned NOT NULL,
  `end` int(10) unsigned NOT NULL,
  `videoId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idSoundSegment`),
  KEY `fk_SoundSegment_Video_idx` (`videoId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `SoundSimilarity`
--

CREATE TABLE IF NOT EXISTS `SoundSimilarity` (
  `soundSegmentId1` int(10) unsigned NOT NULL,
  `soundSegmentId2` int(10) unsigned NOT NULL,
  `value` int(11) NOT NULL,
  `lastUpdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`soundSegmentId1`,`soundSegmentId2`),
  KEY `fk_SoundSegment_has_SoundSegment_SoundSegment2_idx` (`soundSegmentId2`),
  KEY `fk_SoundSegment_has_SoundSegment_SoundSegment1_idx` (`soundSegmentId1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `SoundTag`
--

CREATE TABLE IF NOT EXISTS `SoundTag` (
  `soundSegmentId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `tagName` varchar(30) NOT NULL,
  `idSoundTag` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT 'other',
  PRIMARY KEY (`soundSegmentId`,`userId`,`tagName`),
  UNIQUE KEY `soundSegmentId_UNIQUE` (`soundSegmentId`),
  UNIQUE KEY `idSoudTag_UNIQUE` (`idSoundTag`),
  KEY `fk_SoundTag_User1_idx` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `idUser` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userName` varchar(30) NOT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`idVideo`),
  UNIQUE KEY `textId_UNIQUE` (`textId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `Video`
--

INSERT INTO `Video` (`idVideo`, `textId`, `title`, `genres`, `actors`, `year`) VALUES
(1, 'house.s01.01', 'House Season 1 Episode 1', '', '', 0),
(2, 'house.s01.02', 'House Season 1 Episode 2', '', '', 0),
(3, 'house.s01.03', 'House Season 1 Episode 3', '', '', 0),
(4, 'house.s01.04', 'House Season 1 Episode 4', '', '', 0),
(5, 'house.s01.05', 'House Season 1 Episode 5', '', '', 0);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `SoundSegment`
--
ALTER TABLE `SoundSegment`
  ADD CONSTRAINT `fk_SoundSegment_Video` FOREIGN KEY (`videoId`) REFERENCES `Video` (`idVideo`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `SoundSimilarity`
--
ALTER TABLE `SoundSimilarity`
  ADD CONSTRAINT `fk_SoundSegment_has_SoundSegment_SoundSegment1` FOREIGN KEY (`soundSegmentId1`) REFERENCES `SoundSegment` (`idSoundSegment`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_SoundSegment_has_SoundSegment_SoundSegment2` FOREIGN KEY (`soundSegmentId2`) REFERENCES `SoundSegment` (`idSoundSegment`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `SoundTag`
--
ALTER TABLE `SoundTag`
  ADD CONSTRAINT `fk_SoundTag_SoundSegment1` FOREIGN KEY (`soundSegmentId`) REFERENCES `SoundSegment` (`idSoundSegment`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_SoundTag_User1` FOREIGN KEY (`userId`) REFERENCES `User` (`idUser`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
