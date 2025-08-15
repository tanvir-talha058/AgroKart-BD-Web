-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2025 at 10:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `user_cart`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


--
-- Database: `agrobd`
--

-- --------------------------------------------------------

--
-- Table structure for table `hot_deals`
--

CREATE TABLE `hot_deals` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) NOT NULL,
  `discount_percentage` int(11) NOT NULL,
  `start_date` datetime DEFAULT current_timestamp(),
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hot_deals`
--

INSERT INTO `hot_deals` (`id`, `product_id`, `original_price`, `discount_price`, `discount_percentage`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 45.00, 36.00, 20, '2025-08-13 20:15:35', NULL, 1, '2025-08-13 14:15:35', '2025-08-13 14:15:35'),
(2, 3, 1233.00, 986.40, 20, '2025-08-13 20:15:35', NULL, 1, '2025-08-13 14:15:35', '2025-08-13 14:15:35'),
(3, 6, 23.00, 18.40, 20, '2025-08-13 20:15:35', NULL, 1, '2025-08-13 14:15:35', '2025-08-13 14:15:35'),
(4, 2, 123.00, 92.25, 25, '2025-08-13 20:15:35', NULL, 1, '2025-08-13 14:15:35', '2025-08-13 14:15:35'),
(5, 8, 211.00, 158.25, 25, '2025-08-13 20:15:35', NULL, 1, '2025-08-13 14:15:35', '2025-08-13 14:15:35'),
(7, 1, 45.00, 31.50, 30, '2025-08-13 20:15:35', NULL, 1, '2025-08-13 14:15:35', '2025-08-13 14:15:35'),
(8, 2, 123.00, 86.10, 30, '2025-08-13 20:15:35', NULL, 1, '2025-08-13 14:15:35', '2025-08-13 14:15:35');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_location` text NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `total_amount`, `delivery_location`, `notes`, `status`, `created_at`) VALUES
(1, 4, 45.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Delivered', '2025-08-01 09:59:23'),
(2, 4, 2132434.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Delivered', '2025-08-01 10:11:59'),
(3, 4, 89.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Pending', '2025-08-03 14:14:01'),
(4, 4, 267.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Pending', '2025-08-03 22:19:47'),
(5, 4, 66.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Cancelled', '2025-08-03 22:20:55'),
(6, 4, 33.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Cancelled', '2025-08-04 13:16:51'),
(7, 4, 45.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Pending', '2025-08-04 21:24:00'),
(8, 4, 1233.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Cancelled', '2025-08-04 21:24:27'),
(9, 4, 23423.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Cancelled', '2025-08-04 21:42:27'),
(10, 4, 54.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Pending', '2025-08-04 21:43:14'),
(11, 4, 225.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Cancelled', '2025-08-04 21:49:32'),
(12, 4, 21.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Cancelled', '2025-08-04 22:06:07'),
(13, 4, 33.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Processing', '2025-08-04 22:23:12'),
(14, 4, 23881.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Delivered', '2025-08-05 17:14:45'),
(15, 4, 87.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Shipped', '2025-08-06 15:52:58'),
(16, 4, 23971.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Delivered', '2025-08-06 16:56:54'),
(17, 4, 55.00, 'Bogura Sadar, Bogura, Rajshahi', NULL, 'Shipped', '2025-08-06 22:23:56'),
(18, 4, 570.00, 'Bogura Sadar, Bogura, Rajshahi', 'ff', 'Delivered', '2025-08-09 21:08:59'),
(19, 4, 22.00, 'Bogura Sadar, Bogura, Rajshahi', 'talha', 'Delivered', '2025-08-12 13:21:45'),
(20, 2, 2571.00, 'Test Location 1', NULL, 'Delivered', '2025-08-11 04:00:00'),
(21, 2, 234230.00, 'Test Location 2', NULL, 'Delivered', '2025-08-12 04:00:00'),
(22, 2, 369.00, 'Test Location 2', NULL, 'Delivered', '2025-08-12 04:00:00'),
(23, 2, 189.00, 'Test Location 2', NULL, 'Delivered', '2025-08-12 04:00:00'),
(24, 4, 46.00, 'Bogura Sadar, Bogura, Rajshahi', '', 'Delivered', '2025-08-12 22:02:39'),
(25, 4, 67.00, 'Bogura Sadar, Bogura, Rajshahi', '', 'Delivered', '2025-08-13 10:11:53'),
(26, 4, 965.00, 'Bogura Sadar, Bogura, Rajshahi', 'Hi there', 'Delivered', '2025-08-13 10:39:51'),
(27, 4, 921.00, 'Bogura Sadar, Bogura, Rajshahi', '', 'Delivered', '2025-08-13 15:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 45.00),
(2, 2, 10, 1, 2132434.00),
(3, 3, 15, 2, 22.00),
(4, 3, 11, 1, 12.00),
(5, 3, 12, 1, 33.00),
(6, 4, 15, 1, 22.00),
(7, 4, 14, 1, 33.00),
(8, 4, 7, 1, 212.00),
(9, 5, 13, 1, 33.00),
(10, 5, 12, 1, 33.00),
(11, 6, 14, 1, 33.00),
(12, 7, 14, 1, 33.00),
(13, 7, 11, 1, 12.00),
(14, 8, 3, 1, 1233.00),
(15, 9, 4, 1, 23423.00),
(16, 10, 5, 1, 21.00),
(17, 10, 12, 1, 33.00),
(18, 11, 15, 5, 22.00),
(19, 11, 9, 5, 23.00),
(20, 12, 5, 1, 21.00),
(21, 13, 12, 1, 33.00),
(22, 14, 9, 3, 23.00),
(23, 14, 12, 1, 33.00),
(24, 14, 8, 1, 211.00),
(25, 14, 4, 1, 23423.00),
(26, 14, 2, 1, 123.00),
(27, 14, 15, 1, 22.00),
(28, 15, 13, 1, 33.00),
(29, 15, 12, 1, 33.00),
(30, 15, 5, 1, 21.00),
(31, 16, 1, 2, 45.00),
(32, 16, 9, 3, 23.00),
(33, 16, 12, 1, 33.00),
(34, 16, 8, 1, 211.00),
(35, 16, 4, 1, 23423.00),
(36, 16, 2, 1, 123.00),
(37, 16, 15, 1, 22.00),
(38, 17, 15, 1, 22.00),
(39, 17, 13, 1, 33.00),
(40, 18, 15, 6, 22.00),
(41, 18, 16, 3, 123.00),
(42, 18, 6, 3, 23.00),
(43, 19, 15, 1, 22.00),
(44, 20, 5, 1, 21.00),
(45, 20, 3, 2, 1233.00),
(46, 20, 5, 4, 21.00),
(47, 21, 4, 5, 23423.00),
(48, 21, 4, 5, 23423.00),
(49, 22, 2, 3, 123.00),
(50, 23, 5, 4, 21.00),
(51, 23, 5, 5, 21.00),
(52, 24, 6, 2, 23.00),
(53, 25, 6, 1, 23.00),
(54, 25, 15, 2, 22.00),
(55, 26, 17, 2, 111.00),
(56, 26, 15, 2, 22.00),
(57, 26, 12, 2, 33.00),
(58, 26, 8, 3, 211.00),
(59, 27, 17, 2, 111.00),
(60, 27, 12, 2, 33.00),
(61, 27, 8, 3, 211.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `method` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `transaction_id`, `method`, `status`, `created_at`) VALUES
(1, 1, 'AGRO-NAGAD-1754042363', 'Nagad', 'Completed', '2025-08-01 09:59:23'),
(2, 2, 'AGRO-CARD-1754043119', 'Card', 'Completed', '2025-08-01 10:11:59'),
(3, 3, 'AGRO-BKASH-1754230441', 'bKash', 'Completed', '2025-08-03 14:14:01'),
(4, 4, 'AGRO-NAGAD-1754259587', 'Nagad', 'Completed', '2025-08-03 22:19:47'),
(5, 5, 'AGRO-CARD-1754259655', 'Card', 'Completed', '2025-08-03 22:20:55'),
(6, 6, 'AGRO-COD-1754313411', 'COD', 'Completed', '2025-08-04 13:16:51'),
(7, 7, 'AGRO-COD-1754342640', 'COD', 'Completed', '2025-08-04 21:24:00'),
(8, 8, 'AGRO-CARD-1754342667', 'Card', 'Completed', '2025-08-04 21:24:27'),
(9, 9, 'AGRO-BKASH-1754343747', 'bKash', 'Completed', '2025-08-04 21:42:27'),
(10, 10, 'AGRO-COD-1754343794', 'COD', 'Completed', '2025-08-04 21:43:14'),
(11, 11, 'AGRO-COD-1754344172', 'COD', 'Completed', '2025-08-04 21:49:32'),
(12, 12, 'AGRO-NAGAD-1754345167', 'Nagad', 'Completed', '2025-08-04 22:06:07'),
(13, 13, 'AGRO-CARD-1754346193', 'Card', 'Completed', '2025-08-04 22:23:13'),
(14, 14, 'AGRO-NAGAD-1754414085', 'Nagad', 'Completed', '2025-08-05 17:14:45'),
(15, 15, 'AGRO-CARD-1754495578', 'Card', 'Completed', '2025-08-06 15:52:58'),
(16, 16, 'AGRO-COD-1754499414', 'COD', 'Completed', '2025-08-06 16:56:54'),
(17, 17, 'AGRO-COD-1754519036', 'COD', 'Completed', '2025-08-06 22:23:56'),
(18, 18, 'AGRO-NAGAD-1754773739', 'Nagad', 'Completed', '2025-08-09 21:08:59'),
(19, 19, 'AGRO-COD-1755004905', 'COD', 'Completed', '2025-08-12 13:21:45'),
(20, 24, 'AGRO-BKASH-1755036159', 'bKash', 'Completed', '2025-08-12 22:02:39'),
(21, 25, 'AGRO-COD-1755079913', 'COD', 'Completed', '2025-08-13 10:11:53'),
(22, 26, 'AGRO-NAGAD-1755081591', 'Nagad', 'Completed', '2025-08-13 10:39:51'),
(23, 27, 'AGRO-BKASH-1755099202', 'bKash', 'Completed', '2025-08-13 15:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(10) NOT NULL DEFAULT 'kg',
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `name`, `description`, `price`, `unit`, `stock`, `category`, `image_path`, `created_at`) VALUES
(1, 3, 'Potato', 'dasdwe', 45.00, 'kg', 2342, 'Vegetable', 'images/uploads/Patates.jpg', '2025-08-01 09:57:32'),
(2, 3, 'Potato', 'weqwrrtqwerr', 123.00, 'kg', 121, 'Fruit', 'images/uploads/Patates(1).jpg', '2025-08-01 10:08:05'),
(3, 3, 'Potato1', 'wel;kduihqwaiudhqw', 1233.00, 'kg', 0, 'Vegetable', 'images/uploads/Patates(2).jpg', '2025-08-01 10:08:26'),
(4, 3, 'Potato', 'dfwer', 23423.00, 'kg', 0, 'Spice', 'images/uploads/Patates(3).jpg', '2025-08-01 10:08:48'),
(5, 3, 'Potatoooo', 'das', 21.00, 'kg', 0, 'Spice', 'images/uploads/Patates(4).jpg', '2025-08-01 10:09:11'),
(6, 3, 'GG', 'dswqd', 23.00, 'kg', 15, 'Vegetable', 'images/uploads/Patates(5).jpg', '2025-08-01 10:09:30'),
(7, 3, 'Jinger', 'dasda', 212.00, 'kg', 0, 'Vegetable', 'images/uploads/Patates(6).jpg', '2025-08-01 10:09:48'),
(8, 3, 'GG', 'fsdf', 211.00, 'kg', 14, 'Fruit', 'images/uploads/Patates(7).jpg', '2025-08-01 10:10:05'),
(9, 3, 'ee', 'fdff', 23.00, 'kg', 1, 'Vegetable', 'images/uploads/Patates(8).jpg', '2025-08-01 10:10:28'),
(10, 3, 'only one', 'ttt', 2132434.00, 'kg', 0, 'Vegetable', 'images/uploads/talha.jpg', '2025-08-01 10:11:44'),
(11, 3, 'Potato', 'ff', 12.00, 'kg', 0, 'Vegetable', 'images/uploads/Patates(9).jpg', '2025-08-01 10:13:00'),
(12, 3, 'Potato', 'dfd', 33.00, 'kg', 22, 'Vegetable', 'images/uploads/talha(1).jpg', '2025-08-01 10:13:11'),
(13, 3, 'Potatoooo', 'fgfg', 33.00, 'kg', 30, 'Fruit', 'images/uploads/talha(2).jpg', '2025-08-01 10:13:36'),
(14, 3, 'GG', 'fdf', 33.00, 'kg', 0, 'Spice', 'images/uploads/talha(3).jpg', '2025-08-01 10:13:49'),
(15, 3, 'Jinger', 'dd', 22.00, 'kg', 0, 'Vegetable', 'images/uploads/talha(4).jpg', '2025-08-01 10:14:40'),
(16, 3, 'GG', 'de', 123.00, 'kg', 222208, 'Spice', 'images/uploads/Screenshot 2025-07-07 223653.png', '2025-08-06 15:45:30'),
(17, 3, 'Potato', 'aaaaaaaaaaaA,DSBAKDGQAKUJDGAUJKDGAGDKJAGDGAJKDGAGDKJGAJKGDJKAGDGAJKGDJAGDAGJDGAGDAGDGAKJDGAGJDKGAKJDGKAGDKAJGDJGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGG', 111.00, 'kg', 107, 'Spice', 'images/uploads/Screenshot 2025-06-20 001202.png', '2025-08-06 17:42:37');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 12, 4, 4, 'goood', '2025-08-03 13:50:51'),
(2, 15, 4, 3, 'nice', '2025-08-06 17:13:34');

-- --------------------------------------------------------

--
-- Table structure for table `seller_hidden_customers`
--

CREATE TABLE `seller_hidden_customers` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `hidden_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `division` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `role` enum('Buyer','Seller') NOT NULL,
  `profile_image_path` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `division`, `district`, `city`, `role`, `profile_image_path`, `password`, `created_at`) VALUES
(1, 'Fahim Faisal Talha', 'talha@gmail.com', '01776199963', NULL, NULL, NULL, 'Seller', NULL, '$2y$10$x8YHVIPGRffeFHLvIBrBK.tzbtecWjQn6gwQECdBADA0ufa2VVdSq', '2025-07-10 17:42:54'),
(2, 'Fahim Faisal', 'talha1@gmail.com', '01776199964', NULL, NULL, NULL, 'Buyer', 'images/profiles/user_2_1752171000.jpg', '$2y$10$QikQTfdWUHZHL8n8Ju/JAekg.ShXHPyZvx131Q0.SYZKJp4uQXNMO', '2025-07-10 17:46:33'),
(3, 'Fahim Faisal Talha', 'talha2@gmail.com', '01776199963', 'Dhaka', 'Faridpur', 'Faridpur Sadar', 'Seller', NULL, '$2y$10$mbKteklzh4wXYJ/mPfNH4uobCss1zWK0q.9TlRjD8sG9hayN56Vc.', '2025-08-01 09:52:57'),
(4, 'Lamborghini', 'talhafahimfaisal@gmail.com', '01776199964', 'Rajshahi', 'Bogura', 'Bogura Sadar', 'Buyer', 'images/profiles/user_4_1754317762.jpg', '$2y$10$aGSfVUQ8a7gRsmOyp412v.oA0c7gYHBHMTExlWFEngnYOlfBnzquy', '2025-08-01 09:58:41'),
(5, 'BDFootballHub', 'talha3@gmail.com', '01776199963', 'Rajshahi', 'Pabna', 'Pabna Sadar', 'Buyer', NULL, '$2y$10$IoCTqllRU2To9wqR5bUKVuQ4xBsGvL7IwlGnnxibbko0vRwc8yWAa', '2025-08-06 19:28:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--


CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buyer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_location` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `buyer_id` (`buyer_id`),
  FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

 This table references `orders` and `products`.

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `method` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_cart`
--

INSERT INTO `user_cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(6, 5, 1, 2, '2025-08-06 19:29:02', '2025-08-06 19:44:15'),
(8, 5, 16, 2, '2025-08-06 19:44:10', '2025-08-06 19:44:16'),
(9, 5, 9, 1, '2025-08-06 19:44:13', '2025-08-06 19:44:13'),
(10, 5, 8, 1, '2025-08-06 19:44:13', '2025-08-06 19:44:13'),
(11, 5, 6, 1, '2025-08-06 19:44:14', '2025-08-06 19:44:14'),
(12, 5, 2, 1, '2025-08-06 19:44:14', '2025-08-06 19:44:14'),
(13, 5, 15, 1, '2025-08-06 19:44:16', '2025-08-06 19:44:16'),
(14, 5, 17, 1, '2025-08-06 19:44:17', '2025-08-06 19:44:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hot_deals`
--
ALTER TABLE `hot_deals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`

  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `seller_hidden_customers`
--
ALTER TABLE `seller_hidden_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_seller_customer` (`seller_id`,`customer_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_seller_hidden_customers` (`seller_id`,`customer_id`);

--
-- Indexes for table `users`
--

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product_unique` (`user_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hot_deals`
--
ALTER TABLE `hot_deals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `seller_hidden_customers`
--
ALTER TABLE `seller_hidden_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hot_deals`
--
ALTER TABLE `hot_deals`
  ADD CONSTRAINT `hot_deals_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_hidden_customers`
--
ALTER TABLE `seller_hidden_customers`
  ADD CONSTRAINT `seller_hidden_customers_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `seller_hidden_customers_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD CONSTRAINT `user_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
