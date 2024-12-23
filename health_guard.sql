-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 07, 2024 at 10:16 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `health_guard`
--

-- --------------------------------------------------------

--
-- Table structure for table `payment_link`
--

DROP TABLE IF EXISTS `payment_link`;
CREATE TABLE IF NOT EXISTS `payment_link` (
  `id` int NOT NULL AUTO_INCREMENT,
  `application_id` bigint NOT NULL,
  `total_premium` bigint NOT NULL,
  `shortened_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `response_url` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_id` (`application_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment_link`
--

INSERT INTO `payment_link` (`id`, `application_id`, `total_premium`, `shortened_code`, `response_url`) VALUES
(5, 52126, 2360, 'eadCDq70LZuVWs', 'http://localhost/InsureScout/payment_return.php?provider_id=1&premium=2360&success=true&application_id=52126');

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

DROP TABLE IF EXISTS `proposals`;
CREATE TABLE IF NOT EXISTS `proposals` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `pincode` int NOT NULL,
  `contact` varchar(255) NOT NULL,
  `age` int NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `dob` date NOT NULL,
  `nominee_name` varchar(255) NOT NULL,
  `nominee_relation` varchar(255) NOT NULL,
  `nominee_dob` date NOT NULL,
  `nominee_contact` varchar(255) NOT NULL,
  `tenure` int NOT NULL,
  `net_premium` bigint NOT NULL,
  `total_premium` bigint NOT NULL,
  `sum_insured` bigint NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `application_id` bigint NOT NULL,
  `payment` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_id` (`application_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `name`, `email`, `address`, `pincode`, `contact`, `age`, `gender`, `dob`, `nominee_name`, `nominee_relation`, `nominee_dob`, `nominee_contact`, `tenure`, `net_premium`, `total_premium`, `sum_insured`, `start_date`, `end_date`, `application_id`, `payment`) VALUES
(17, 'Ali Jawad', 'jawad@gmail.com', 'Kunnil, P.O Mogral Puthur', 671124, '9746542694', 24, 'male', '2000-07-02', 'Jabbu', 'Brother', '2000-07-02', '7012085535', 1, 2000, 2360, 1000000, '2024-10-06', '2025-10-05', 52126, '1');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
