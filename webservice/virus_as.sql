-- phpMyAdmin SQL Dump
-- version 3.5.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 18, 2013 at 09:19 PM
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=77 ;

--
-- Dumping data for table `SoundSegment`
--

INSERT INTO `SoundSegment` (`idSoundSegment`, `start`, `end`, `videoId`) VALUES
(1, 0, 10, 6),
(2, 10, 20, 6),
(3, 20, 30, 6),
(4, 30, 40, 6),
(5, 40, 50, 6),
(6, 50, 60, 6),
(7, 60, 70, 6),
(8, 70, 80, 6),
(9, 80, 90, 6),
(10, 90, 100, 6),
(11, 100, 110, 6),
(12, 110, 120, 6),
(13, 120, 130, 6),
(14, 130, 140, 6),
(15, 140, 150, 6),
(16, 150, 160, 6),
(17, 160, 170, 6),
(18, 170, 180, 6),
(19, 180, 190, 6),
(20, 190, 200, 6),
(21, 200, 210, 6),
(22, 210, 220, 6),
(23, 220, 230, 6),
(24, 230, 240, 6),
(25, 240, 250, 6),
(26, 250, 260, 6),
(27, 260, 270, 6),
(28, 270, 280, 6),
(29, 280, 290, 6),
(30, 290, 300, 6),
(31, 300, 310, 6),
(32, 310, 320, 6),
(33, 320, 330, 6),
(34, 330, 340, 6),
(35, 340, 350, 6),
(36, 350, 360, 6),
(37, 360, 370, 6),
(38, 370, 380, 6),
(39, 380, 390, 6),
(40, 390, 400, 6),
(41, 400, 410, 6),
(42, 410, 420, 6),
(43, 420, 430, 6),
(44, 430, 440, 6),
(45, 440, 450, 6),
(46, 450, 460, 6),
(47, 460, 470, 6),
(48, 470, 480, 6),
(49, 480, 490, 6),
(50, 490, 500, 6),
(51, 500, 510, 6),
(52, 510, 520, 6),
(53, 520, 530, 6),
(54, 530, 540, 6),
(55, 540, 550, 6),
(56, 550, 560, 6),
(57, 560, 570, 6),
(58, 570, 580, 6),
(59, 580, 590, 6),
(60, 590, 600, 6),
(61, 600, 610, 6),
(62, 610, 620, 6),
(63, 620, 630, 6),
(64, 630, 640, 6),
(65, 640, 650, 6),
(66, 650, 660, 6),
(67, 660, 670, 6),
(68, 670, 680, 6),
(69, 680, 690, 6),
(70, 690, 700, 6),
(71, 700, 710, 6),
(72, 710, 720, 6),
(73, 720, 730, 6),
(74, 730, 734, 6),
(75, 0, 10, 1),
(76, 10, 20, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `SoundSegmentSimilarities`
--
CREATE TABLE IF NOT EXISTS `SoundSegmentSimilarities` (
`id1` int(10) unsigned
,`start1` int(10) unsigned
,`end1` int(10) unsigned
,`videoId1` int(10) unsigned
,`id2` int(10) unsigned
,`start2` int(10) unsigned
,`end2` int(10) unsigned
,`videoId2` int(10) unsigned
,`value` int(11)
,`lastUpdate` int(11)
);
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

--
-- Dumping data for table `SoundSimilarity`
--

INSERT INTO `SoundSimilarity` (`soundSegmentId1`, `soundSegmentId2`, `value`, `lastUpdate`) VALUES
(1, 7, 75, 0),
(7, 4, 70, 0),
(7, 9, 50, 0),
(7, 12, 100, 0),
(7, 31, 55, 0),
(75, 8, 1000, 0),
(75, 76, 1000, 0),
(76, 7, 1010, 0);

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
  PRIMARY KEY (`idSoundTag`),
  UNIQUE KEY `SECOND_PRIMARY` (`soundSegmentId`,`userId`,`tagName`),
  KEY `fk_SoundTag_User1_idx` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=44 ;

--
-- Dumping data for table `SoundTag`
--

INSERT INTO `SoundTag` (`idSoundTag`, `tagName`, `soundSegmentId`, `userId`, `type`, `insertedTime`) VALUES
(31, 'tag1', 7, 1, 'other', 0),
(32, 'tag2', 7, 1, 'other', 0),
(33, 'tag3', 7, 1, 'other', 0),
(34, 'tag1', 7, 2, 'other', 0),
(35, 'tag3', 7, 2, 'other', 0),
(36, 'tag10', 7, 2, 'other', 0),
(37, 'tag11', 7, 2, 'other', 0),
(38, 'tag11', 6, 2, 'other', 0),
(39, 'tag11', 5, 2, 'other', 0),
(40, 'tag144', 4, 2, 'other', 0),
(41, 'tag3', 7, 3, 'other', 0),
(42, 'tag15', 6, 3, 'other', 0),
(43, 'otherTag', 6, 3, 'other', 1361218727);

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

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`idUser`, `userName`) VALUES
(1, 'user1'),
(2, 'user2'),
(3, 'user3');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `Video`
--

INSERT INTO `Video` (`idVideo`, `textId`, `title`, `genres`, `actors`, `year`, `location`) VALUES
(1, 'house.s01.01', 'House Season 1 Episode 1', '', '', 0, ''),
(2, 'house.s01.02', 'House Season 1 Episode 2', '', '', 0, ''),
(3, 'house.s01.03', 'House Season 1 Episode 3', '', '', 0, ''),
(4, 'house.s01.04', 'House Season 1 Episode 4', '', '', 0, ''),
(5, 'house.s01.05', 'House Season 1 Episode 5', '', '', 0, ''),
(6, 'Tears-Of-Steel', 'Teers Of Steel', 'Short, Sci-Fi', '', 2012, 'Tears-Of-Steel/tears_of_steel_1080p');

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
