-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 21, 2026 at 10:59 AM
-- Server version: 8.4.7
-- PHP Version: 8.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chloephones`
--

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
CREATE TABLE IF NOT EXISTS `company` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service_charge_value` decimal(6,2) NOT NULL DEFAULT '0.00',
  `vat_charge_value` decimal(6,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`id`, `company_name`, `service_charge_value`, `vat_charge_value`, `currency`) VALUES
(1, 'Chloe Phone Center', 0.00, 0.00, 'UGX');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attempted_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip_address_attempted_at` (`ip_address`,`attempted_at`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-04-17-000001', 'App\\Database\\Migrations\\CreateInventorySchema', 'default', 'App', 1776442742, 1),
(2, '2026-04-21-100434', 'App\\Database\\Migrations\\CreateNewTable', 'default', 'App', 1776767989, 2);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_is_read` (`user_id`,`is_read`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_name` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_address` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_phone` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_time` int NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `service_charge_rate` decimal(6,2) NOT NULL DEFAULT '0.00',
  `service_charge` decimal(12,2) NOT NULL DEFAULT '0.00',
  `vat_charge_rate` decimal(6,2) NOT NULL DEFAULT '0.00',
  `vat_charge` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_status` tinyint(1) NOT NULL DEFAULT '2',
  `user_id` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_no` (`bill_no`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `bill_no`, `customer_name`, `customer_address`, `customer_phone`, `date_time`, `gross_amount`, `service_charge_rate`, `service_charge`, `vat_charge_rate`, `vat_charge`, `net_amount`, `discount`, `paid_status`, `user_id`) VALUES
(6, 'N0:20260418152228', 'asdfgh', 'qwertyt324354657', '23456787', 1776525748, 300000.00, 0.00, 0.00, 0.00, 0.00, 300000.00, 0.00, 2, 1),
(7, 'N0:20260418152448', 'cvnbm', 'wercvbnm,', '324565768', 1776525888, 300000.00, 0.00, 0.00, 0.00, 0.00, 300000.00, 0.00, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders_item`
--

DROP TABLE IF EXISTS `orders_item`;
CREATE TABLE IF NOT EXISTS `orders_item` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `order_id_product_id` (`order_id`,`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders_item`
--

INSERT INTO `orders_item` (`id`, `order_id`, `product_id`, `rate`, `amount`) VALUES
(6, 6, 4, 300000.00, 300000.00),
(7, 7, 2, 300000.00, 300000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `imei` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_general_ci,
  `storage` int DEFAULT NULL,
  `ram` int DEFAULT NULL,
  `warehouse_id` int UNSIGNED DEFAULT NULL,
  `availability` tinyint(1) NOT NULL DEFAULT '1',
  `date_added` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imei` (`imei`),
  KEY `warehouse_id` (`warehouse_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `imei`, `price`, `description`, `storage`, `ram`, `warehouse_id`, `availability`, `date_added`) VALUES
(5, 'samsunaga', 'SDFGHJKF876543eefg', 300000.00, '', 128, 4, 1, 1, '2026-04-18'),
(2, 'samsunaga', 'SDFGHJKF876543', 300000.00, '', 220, 3, 1, 0, '2026-04-17'),
(4, 'samsunaga', 'SDFGHJKF876543eef', 300000.00, '', 128, 4, 1, 0, '2026-04-18'),
(6, 'samsunaga', 'SDFGHJKF876543ec', 300000.00, '', 128, 4, 1, 1, '2026-04-18'),
(7, 'samsunaga', 'SDFGHJKF876543ed', 300000.00, '', 128, 4, 2, 1, '2026-04-18'),
(8, 'samsunaga', 'SDFGHJKF876543s', 300000.00, '', 128, 4, 2, 1, '2026-04-18'),
(9, 'samsunaga', 'SDFGHJK876543s', 300000.00, '', 128, 4, 2, 1, '2026-04-18'),
(10, 'samsunaga', 'SDFGHJKF876543de', 300000.00, '', 128, 4, 2, 1, '2026-04-18');

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

DROP TABLE IF EXISTS `stores`;
CREATE TABLE IF NOT EXISTS `stores` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `assigned_user_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `name`, `active`, `assigned_user_id`, `created_at`, `updated_at`) VALUES
(1, 'MPIGI', 1, 2, NULL, NULL),
(2, 'Maya', 1, 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `firstname` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lastname` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `firstname`, `lastname`, `phone`, `gender`, `created_at`, `updated_at`, `is_active`, `last_login_at`) VALUES
(1, 'admin', '$2y$12$ySVkmqxpxyKPsEd30uDzJOuSjnSilxVCx.XHtm3rLB4GZTny6Bwx.', 'admin@gmail.com', 'System', 'Admin', '', NULL, '2026-04-17 16:26:21', '2026-04-17 16:48:26', 1, NULL),
(2, 'user', '$2y$12$lLH2qVzBgR.yzlcyGnawdOprUUTdODdseJz4f74ibl4yaBrl7D/VW', 'user@gmail.com', 'Default', 'User', '', NULL, '2026-04-17 16:48:26', '2026-04-17 16:48:26', 1, NULL),
(3, 'Faith', '$2y$12$PRdL/2WHzSTyaPnCqPYIIOpHvDTmZIYEc6isOOljW8XLmWMqxA2Oa', 'faith@gmail.com', 'Ambrose', 'Ahimbisibwe', '0704625130', 2, NULL, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
CREATE TABLE IF NOT EXISTS `user_group` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `group_id` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_group_id` (`user_id`,`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_group`
--

INSERT INTO `user_group` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE IF NOT EXISTS `user_groups` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `permission` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`id`, `group_name`, `permission`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'a:24:{i:0;s:10:\"createUser\";i:1;s:10:\"updateUser\";i:2;s:8:\"viewUser\";i:3;s:10:\"deleteUser\";i:4;s:11:\"createGroup\";i:5;s:11:\"updateGroup\";i:6;s:9:\"viewGroup\";i:7;s:11:\"deleteGroup\";i:8;s:11:\"createStore\";i:9;s:11:\"updateStore\";i:10;s:9:\"viewStore\";i:11;s:11:\"deleteStore\";i:12;s:13:\"createProduct\";i:13;s:13:\"updateProduct\";i:14;s:11:\"viewProduct\";i:15;s:13:\"deleteProduct\";i:16;s:11:\"createOrder\";i:17;s:11:\"updateOrder\";i:18;s:9:\"viewOrder\";i:19;s:11:\"deleteOrder\";i:20;s:13:\"updateCompany\";i:21;s:11:\"viewReports\";i:22;s:13:\"updateSetting\";i:23;s:11:\"viewProfile\";}', '2026-04-17 16:26:21', '2026-04-17 16:26:21'),
(2, 'User', 'a:11:{i:0;s:10:\"updateUser\";i:1;s:8:\"viewUser\";i:2;s:11:\"updateGroup\";i:3;s:9:\"viewGroup\";i:4;s:11:\"updateStore\";i:5;s:9:\"viewStore\";i:6;s:13:\"updateProduct\";i:7;s:11:\"viewProduct\";i:8;s:11:\"updateOrder\";i:9;s:9:\"viewOrder\";i:10;s:13:\"updateCompany\";}', '2026-04-17 16:48:25', '2026-04-17 16:48:25');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
