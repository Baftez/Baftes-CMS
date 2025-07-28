-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.34 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for wow_website
CREATE DATABASE IF NOT EXISTS `wow_website` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `wow_website`;

-- Dumping structure for table wow_website.news
CREATE TABLE IF NOT EXISTS `news` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `publication_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_publication_date` (`publication_date` DESC)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table wow_website.news: ~2 rows (approximately)
REPLACE INTO `news` (`id`, `title`, `content`, `author`, `publication_date`) VALUES
	(1, 'Major Server Stability Improvements!', '<div>We\'re excited to announce significant upgrades to our server infrastructure, resulting in greatly improved stability and reduced latency. Players should now experience a smoother and more reliable gameplay environment. We appreciate your patience as we continue to enhance your experience! These improvements include optimized database queries, increased bandwidth, and a new load-balancing system to handle peak player counts more efficiently. We are committed to providing the best possible experience for all our adventurers.</div><div><br></div>', 'Baftes', '2025-07-24 22:34:08'),
	(2, 'New Guild Event: "The Scourge Invasion"', 'Prepare for a new challenge! Our next major guild event, "The Scourge Invasion," will begin on August 5th. Guilds will compete to repel waves of undead forces across Azeroth for exclusive rewards and bragging rights. More details on rules and prizes will be posted soon on our forums. This event will feature unique mechanics, challenging boss encounters, and opportunities for both PvE and PvP guilds to shine. Start recruiting your allies and preparing your strategies!', 'Baftes', '2025-07-24 22:34:30');

-- Dumping structure for table wow_website.web_users
CREATE TABLE IF NOT EXISTS `web_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_date` timestamp NULL DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table wow_website.web_users: ~1 rows (approximately)
REPLACE INTO `web_users` (`id`, `username`, `email`, `password_hash`, `registration_date`, `last_login_ip`, `last_login_date`, `role`) VALUES
	(1, 'Admin', 'admin@hotmail.com', '$2y$10$LrWcj2ieaoxfeTayACE/Y.qHYM2s/g7JsmgKD2wtwPeKGs.GFGlfS', '2025-07-25 05:33:11', '127.0.0.1', '2025-07-28 11:03:02', 'admin');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
