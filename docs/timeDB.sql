-- phpMyAdmin SQL Dump
-- version 3.2.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 17, 2010 at 11:54 AM
-- Server version: 5.1.40
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `CorporateIS`
--

-- --------------------------------------------------------

--
-- Table structure for table `Customer`
--

CREATE TABLE IF NOT EXISTS `Customer` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Customer`
--

INSERT INTO `Customer` (`ID`, `Name`) VALUES
(1, 'Простой');

-- --------------------------------------------------------

--
-- Table structure for table `Delivery`
--

CREATE TABLE IF NOT EXISTS `Delivery` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` int(11) NOT NULL,
  `RealDate` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `Delivery`
--


-- --------------------------------------------------------

--
-- Table structure for table `ExecutionPlan`
--

CREATE TABLE IF NOT EXISTS `ExecutionPlan` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderProductID` int(11) NOT NULL,
  `AddOpportunity` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `OrderProductID` (`OrderProductID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `ExecutionPlan`
--

INSERT INTO `ExecutionPlan` (`ID`, `OrderProductID`, `AddOpportunity`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `Helper`
--

CREATE TABLE IF NOT EXISTS `Helper` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `Value` varchar(64) COLLATE utf8_unicode_ci NOT NULL,  
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Helper`
--

INSERT INTO `Helper` (`ID`, `Key`, `Value`) VALUES
(1, 'startTime', '1290593352 ');


-- --------------------------------------------------------

--
-- Table structure for table `Nomenclature`
--

CREATE TABLE IF NOT EXISTS `Nomenclature` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DeliveryID` int(11) NOT NULL,
  `RawID` int(11) NOT NULL,
  `Count` int(11) NOT NULL,
  `RealCount` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `DeliveryID` (`DeliveryID`),
  KEY `RawID` (`RawID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `Nomenclature`
--


-- --------------------------------------------------------

--
-- Table structure for table `Order`
--

CREATE TABLE IF NOT EXISTS `Order` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) NOT NULL,
  `OrderTypeID` int(11) NOT NULL,
  `TimeRegistration` int(11) NOT NULL,
  `TimeExecution` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `OrderTypeID` (`OrderTypeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Order`
--

INSERT INTO `Order` (`ID`, `CustomerID`, `OrderTypeID`, `TimeRegistration`, `TimeExecution`) VALUES
(1, 1, 2, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `OrderProduct`
--

CREATE TABLE IF NOT EXISTS `OrderProduct` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Count` int(11) NOT NULL,
  `RealCount` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `OrderID` (`OrderID`),
  KEY `ProductID` (`ProductID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `OrderProduct`
--

INSERT INTO `OrderProduct` (`ID`, `OrderID`, `ProductID`, `Count`, `RealCount`) VALUES
(1, 1, 4, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `OrderType`
--

CREATE TABLE IF NOT EXISTS `OrderType` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `Days` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `OrderType`
--

INSERT INTO `OrderType` (`ID`, `Type`, `Days`) VALUES
(1, 'Отказ', 0),
(2, 'Несрочный заказ', 1400),
(3, 'Срочный заказ', 700);

-- --------------------------------------------------------

--
-- Table structure for table `Product`
--

CREATE TABLE IF NOT EXISTS `Product` (
  `ID` int(11) NOT NULL,
  `Name` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `ExecutionTime` int(11) NOT NULL,
  `RetunningTime` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Product`
--

INSERT INTO `Product` (`ID`, `Name`, `ExecutionTime`, `RetunningTime`) VALUES
(1, 'A', 2, 5),
(2, 'B', 4, 5),
(3, 'C', 8, 5),
(4, 'Простой', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Raw`
--

CREATE TABLE IF NOT EXISTS `Raw` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `Count` int(11) NOT NULL,
  `Volume` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;

--
-- Dumping data for table `Raw`
--

INSERT INTO `Raw` (`ID`, `Name`, `Count`, `Volume`) VALUES
(1, 'a', 0, 1),
(2, 'b', 0, 1),
(3, 'c', 0, 1),
(4, 'd', 0, 1),
(5, 'e', 0, 1),
(6, 'f', 0, 1),
(7, 'g', 0, 1),
(8, 'h', 0, 1),
(9, 'i', 0, 1),
(10, 'j', 0, 1),
(11, 'k', 0, 1),
(12, 'l', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `RawRequiment`
--

CREATE TABLE IF NOT EXISTS `RawRequiment` (
  `ProductID` int(11) NOT NULL,
  `RawID` int(11) NOT NULL,
  `Count` int(11) NOT NULL,
  KEY `RawID` (`RawID`),
  KEY `ProductID` (`ProductID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `RawRequiment`
--

INSERT INTO `RawRequiment` (`ProductID`, `RawID`, `Count`) VALUES
(1, 1, 1),
(1, 2, 1),
(1, 3, 1),
(1, 4, 1),
(1, 5, 1),
(1, 6, 0),
(1, 7, 0),
(1, 8, 0),
(1, 9, 0),
(1, 10, 0),
(1, 11, 0),
(1, 12, 0),
(2, 1, 0),
(2, 2, 0),
(2, 3, 0),
(2, 4, 1),
(2, 5, 1),
(2, 6, 1),
(2, 7, 1),
(2, 8, 1),
(2, 9, 0),
(2, 10, 0),
(2, 11, 0),
(2, 12, 0),
(3, 1, 0),
(3, 2, 0),
(3, 3, 0),
(3, 4, 0),
(3, 5, 0),
(3, 6, 0),
(3, 7, 0),
(3, 8, 1),
(3, 9, 1),
(3, 10, 1),
(3, 11, 1),
(3, 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `Warehouse`
--

CREATE TABLE IF NOT EXISTS `Warehouse` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Count` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `Warehouse`
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `ExecutionPlan`
--
ALTER TABLE `ExecutionPlan`
  ADD CONSTRAINT `executionplan_ibfk_1` FOREIGN KEY (`OrderProductID`) REFERENCES `orderproduct` (`ID`);

--
-- Constraints for table `Nomenclature`
--
ALTER TABLE `Nomenclature`
  ADD CONSTRAINT `nomenclature_ibfk_1` FOREIGN KEY (`DeliveryID`) REFERENCES `delivery` (`ID`),
  ADD CONSTRAINT `nomenclature_ibfk_2` FOREIGN KEY (`RawID`) REFERENCES `raw` (`ID`);

--
-- Constraints for table `Order`
--
ALTER TABLE `Order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`ID`),
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`OrderTypeID`) REFERENCES `ordertype` (`ID`);

--
-- Constraints for table `OrderProduct`
--
ALTER TABLE `OrderProduct`
  ADD CONSTRAINT `orderproduct_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `order` (`ID`),
  ADD CONSTRAINT `orderproduct_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ID`);

--
-- Constraints for table `RawRequiment`
--
ALTER TABLE `RawRequiment`
  ADD CONSTRAINT `rawrequiment_ibfk_1` FOREIGN KEY (`RawID`) REFERENCES `raw` (`ID`),
  ADD CONSTRAINT `rawrequiment_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ID`);
