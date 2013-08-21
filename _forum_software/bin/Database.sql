-- phpMyAdmin SQL Dump
-- version 3.3.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 15, 2010 at 12:14 PM
-- Server version: 5.1.51
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `my13053_jackpf`
--

-- --------------------------------------------------------

--
-- Table structure for table `Alias`
--

CREATE TABLE IF NOT EXISTS `Alias` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(75) NOT NULL,
  `Email` varbinary(75) NOT NULL,
  `Alias` varbinary(15) NOT NULL,
  `Password` varbinary(15) NOT NULL,
  `User` varchar(255) NOT NULL,
  `Profile` longtext NOT NULL,
  `Picture` varchar(100) NOT NULL,
  `Signature` varchar(255) NOT NULL,
  `Status` varchar(30) NOT NULL,
  `Unix` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username` (`Alias`),
  UNIQUE KEY `email` (`Email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=65684 ;

-- --------------------------------------------------------

--
-- Table structure for table `Alias_Stats`
--

CREATE TABLE IF NOT EXISTS `Alias_Stats` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Alias` varbinary(100) NOT NULL,
  `Type` varchar(50) NOT NULL,
  `Unix` int(11) NOT NULL,
  `Unix_Total` int(11) NOT NULL,
  `URI` varchar(250) NOT NULL,
  `Online` tinyint(4) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Alias` (`Alias`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5508 ;

-- --------------------------------------------------------

--
-- Table structure for table `Blog`
--

CREATE TABLE IF NOT EXISTS `Blog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID2` int(11) NOT NULL,
  `Type` varchar(25) NOT NULL,
  `Author` varbinary(15) DEFAULT NULL,
  `Category` varchar(50) NOT NULL,
  `Subject` varchar(250) NOT NULL,
  `Entry` text,
  `Unix` int(11) NOT NULL,
  `Edit` int(11) NOT NULL,
  `Status` varchar(25) NOT NULL,
  `Options` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=199 ;

-- --------------------------------------------------------

--
-- Table structure for table `Forum`
--

CREATE TABLE IF NOT EXISTS `Forum` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(30) NOT NULL,
  `Forum` int(11) NOT NULL,
  `Thread` int(11) NOT NULL,
  `Author` varbinary(15) DEFAULT NULL,
  `Subject` varchar(75) NOT NULL,
  `Post` longtext NOT NULL,
  `Unix` int(11) NOT NULL,
  `Edit` varchar(30) NOT NULL,
  `Stats` int(11) NOT NULL,
  `Status` varchar(255) NOT NULL,
  `Mod` varchar(255) NOT NULL,
  `Options` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Type` (`Type`),
  KEY `Forum` (`Forum`),
  KEY `Thread` (`Thread`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3152 ;

-- --------------------------------------------------------

--
-- Table structure for table `Forum_Data`
--

CREATE TABLE IF NOT EXISTS `Forum_Data` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Alias` varchar(15) NOT NULL,
  `Forum` int(11) NOT NULL,
  `Thread` int(11) NOT NULL,
  `PostCount` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Alias` (`Alias`,`Forum`,`Thread`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

--
-- Table structure for table `Forum_Subscription`
--

CREATE TABLE IF NOT EXISTS `Forum_Subscription` (
  `ID` int(5) NOT NULL AUTO_INCREMENT,
  `Alias` varbinary(15) DEFAULT NULL,
  `Thread` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=192 ;

-- --------------------------------------------------------

--
-- Table structure for table `IM`
--

CREATE TABLE IF NOT EXISTS `IM` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID2` int(11) NOT NULL,
  `Type` varchar(4) NOT NULL,
  `Author` varchar(15) NOT NULL,
  `Post` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1568 ;

-- --------------------------------------------------------

--
-- Table structure for table `Message`
--

CREATE TABLE IF NOT EXISTS `Message` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(10) NOT NULL,
  `Alias` varbinary(10) NOT NULL,
  `Subject` varchar(250) NOT NULL,
  `Message` longtext NOT NULL,
  `Author` varbinary(15) NOT NULL,
  `Unix` int(11) NOT NULL,
  `Edit` varchar(30) NOT NULL,
  `Status` varchar(10) NOT NULL,
  `Options` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6007 ;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Userlevel` float NOT NULL,
  `Mod` varchar(7) NOT NULL,
  `Owner` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;
