-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 12, 2024 at 12:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bandwebsitedatabase`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`Category_ID`, `Category_Name`) VALUES
(1001, 'Records'),
(1002, 'T-Shirts'),
(1003, 'Hoodies'),
(1004, 'Jackets'),
(1005, 'Pants'),
(1006, 'Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `Item_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Description` varchar(50) NOT NULL,
  `Image_URL` varchar(255) NOT NULL,
  `Category_ID` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Availability` tinyint(1) NOT NULL,
  `Quantity_Available` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`Item_ID`, `Name`, `Description`, `Image_URL`, `Category_ID`, `Price`, `Availability`, `Quantity_Available`) VALUES
(1, 'First Album', 'Description', 'http://localhost/BandWebsite/ProductImages/FordSUV.jpg', 1001, 120.00, 0, 0),
(2, 'Second Album', 'Description', 'http://localhost/BandWebsite/ProductImages/FordSedan.png', 1001, 80.00, 0, 0),
(3, 'T-Shirt 1', 'Description', 'http://localhost/BandWebsite/ProductImages/FordVan.png', 1002, 80.00, 0, 0),
(4, 'Hoodie', 'Description', 'http://localhost/BandWebsite/ProductImages/BMWSUV.jpg', 1003, 120.00, 0, 0),
(5, 'Denim Jacket', 'Description', 'http://localhost/BandWebsite/ProductImages/BMWSedan.png', 1004, 80.00, 0, 0),
(6, 'Bomber', 'Description', 'http://localhost/BandWebsite/ProductImages/BMWVan.png', 1004, 280.00, 0, 0),
(7, 'Pants', 'Description', 'http://localhost/BandWebsite/ProductImages/MazdaSUV.jpg', 1005, 160.00, 1, 1),
(8, 'Jeans', 'Description', 'http://localhost/BandWebsite/ProductImages/MazdaSedan.png', 1005, 90.00, 1, 1),
(9, 'Sweatpants', 'Description', 'http://localhost/BandWebsite/ProductImages/MazdaVan.jpg', 1005, 380.00, 0, 0),
(10, 'T-Shirt 2', 'Description', 'http://localhost/BandWebsite/ProductImages/SuzukiSUV.jpeg', 1002, 100.00, 0, 0),
(11, 'Stickers', 'Description', 'http://localhost/BandWebsite/ProductImages/SuzukiSedan.jpeg', 1006, 50.00, 0, 0),
(12, 'Cap', 'Description', 'http://localhost/BandWebsite/ProductImages/SuzukiVan.png', 1006, 120.00, 0, 0),
(13, 'Tote Bag', 'Description', 'http://localhost/BandWebsite/ProductImages/ToyotaSUV.jpg', 1006, 175.00, 0, 0),
(14, 'Patch', 'Description', 'http://localhost/BandWebsite/ProductImages/ToyotaSedan.jpg', 1006, 80.00, 1, 3),
(15, 'T-Shirt 3', 'Description', 'http://localhost/BandWebsite/ProductImages/ToyotaVan.jpg', 1002, 300.00, 1, 10);

--
-- Triggers `items`
--
DELIMITER $$
CREATE TRIGGER `update_availability` BEFORE UPDATE ON `items` FOR EACH ROW BEGIN
    IF NEW.Quantity_Available = 0 THEN
        SET NEW.Availability = 0;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `Order_ID` int(11) NOT NULL,
  `Order_Status` enum('confirmed','unconfirmed') NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Order_Date` date NOT NULL,
  `Total_Amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderinformationtable`
--

CREATE TABLE `orderinformationtable` (
  `Order_Item_ID` int(11) NOT NULL,
  `Order_ID` int(11) NOT NULL,
  `Item_ID` int(11) NOT NULL,
  `Order_Quantity` int(11) NOT NULL,
  `Item_Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `Payment_ID` int(11) NOT NULL,
  `Order_ID` int(11) NOT NULL,
  `Payment_Date` date NOT NULL,
  `Payment_Method` enum('Credit_Card','Paypal') NOT NULL,
  `Payment_Status` enum('confirmed','unconfirmed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_ID` int(11) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Street` varchar(50) NOT NULL,
  `Suburb` varchar(50) NOT NULL,
  `State` varchar(5) NOT NULL,
  `Email_Address` varchar(50) NOT NULL,
  `Mobile_Number` int(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`Category_ID`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`Item_ID`),
  ADD KEY `items_category` (`Category_ID`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`Order_ID`),
  ADD KEY `User_Identification` (`User_ID`);

--
-- Indexes for table `orderinformationtable`
--
ALTER TABLE `orderinformationtable`
  ADD PRIMARY KEY (`Order_Item_ID`),
  ADD KEY `OrderID` (`Order_ID`),
  ADD KEY `ItemID` (`Item_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `Item_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderinformationtable`
--
ALTER TABLE `orderinformationtable`
  MODIFY `Order_Item_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_category` FOREIGN KEY (`Category_ID`) REFERENCES `categories` (`Category_ID`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `User_Identification` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`);

--
-- Constraints for table `orderinformationtable`
--
ALTER TABLE `orderinformationtable`
  ADD CONSTRAINT `ItemID` FOREIGN KEY (`Item_ID`) REFERENCES `items` (`Item_ID`),
  ADD CONSTRAINT `OrderID` FOREIGN KEY (`Order_ID`) REFERENCES `order` (`Order_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
