-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 25, 2024 at 03:19 PM
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

DROP TABLE IF EXISTS `policies`;
CREATE TABLE IF NOT EXISTS `policies` (
  `policy_id` bigint NOT NULL AUTO_INCREMENT,
  `application_id` bigint NOT NULL,
  `issuance_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `sum_insured` bigint NOT NULL,
  `premium_paid` bigint NOT NULL,
  `policy_status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`policy_id`),
  KEY `application_id` (`application_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `payment_status` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_id` (`application_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
