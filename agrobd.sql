-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 16, 2025 at 09:04 PM
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
(13, 51, 150.00, 135.00, 10, '2025-08-16 21:24:56', '2025-08-18 21:24:00', 1, '2025-08-16 15:24:56', '2025-08-16 15:24:56'),
(14, 45, 300.00, 255.00, 15, '2025-08-16 21:25:15', '2025-08-19 21:25:00', 1, '2025-08-16 15:25:15', '2025-08-16 15:25:15'),
(15, 40, 40.00, 38.40, 4, '2025-08-16 21:25:33', '2025-08-18 21:25:00', 1, '2025-08-16 15:25:33', '2025-08-16 15:25:33'),
(16, 36, 1000.00, 890.00, 11, '2025-08-16 21:25:49', '2025-08-19 21:25:00', 1, '2025-08-16 15:25:49', '2025-08-16 15:25:49'),
(17, 31, 80.00, 74.40, 7, '2025-08-16 21:26:06', '2025-08-20 21:26:00', 1, '2025-08-16 15:26:06', '2025-08-16 15:26:06'),
(18, 27, 100.00, 97.00, 3, '2025-08-16 21:26:21', '2025-08-19 21:26:00', 1, '2025-08-16 15:26:21', '2025-08-16 15:26:21'),
(19, 25, 75.00, 73.50, 2, '2025-08-16 21:26:40', '2025-08-21 21:26:00', 1, '2025-08-16 15:26:40', '2025-08-16 15:26:40'),
(20, 53, 100.00, 98.00, 2, '2025-08-16 21:44:20', '2025-08-18 21:44:00', 0, '2025-08-16 15:44:20', '2025-08-16 15:44:35'),
(21, 53, 100.00, 99.00, 1, '2025-08-16 21:54:57', '2025-08-17 21:54:00', 0, '2025-08-16 15:54:57', '2025-08-16 15:55:00'),
(22, 52, 65.00, 63.05, 3, '2025-08-17 00:26:03', '2025-08-19 00:25:00', 1, '2025-08-16 18:26:03', '2025-08-16 18:26:03');

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
  `delivery_option` varchar(20) DEFAULT 'standard',
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `total_amount`, `delivery_location`, `notes`, `delivery_option`, `delivery_fee`, `status`, `delivered_at`, `created_at`) VALUES
(35, 10, 214.40, 'Savar, Dhaka, Dhaka', 'Make sure they are fresh.', 'fast', 40.00, 'Processing', NULL, '2025-08-16 15:56:20'),
(36, 10, 90.00, 'Savar, Dhaka, Dhaka', 'Best', 'standard', 0.00, 'Pending', NULL, '2025-08-16 16:11:00'),
(37, 10, 65.00, 'Savar, Dhaka, Dhaka', 'Best', 'standard', 0.00, 'Delivered', '2025-08-16 16:45:48', '2025-08-16 16:27:16'),
(38, 10, 80.00, 'Savar, Dhaka, Dhaka', 'Good', 'standard', 0.00, 'Shipped', NULL, '2025-08-16 16:28:52'),
(39, 13, 30.00, 'Dhaka', 'better', 'standard', 0.00, 'Pending', NULL, '2025-08-16 17:04:57'),
(40, 13, 30.00, 'DhaKA', 'Good', 'standard', 0.00, 'Pending', NULL, '2025-08-16 17:06:23'),
(41, 13, 90.00, 'Dhaka', 'Good', 'standard', 0.00, 'Pending', NULL, '2025-08-16 17:15:39'),
(42, 13, 80.00, 'Dhaka', 'Best', 'standard', 0.00, 'Delivered', '2025-08-16 18:27:44', '2025-08-16 17:27:29'),
(43, 13, 220.00, 'Dhaka', 'Fast', 'fast', 40.00, 'Delivered', '2025-08-16 18:26:47', '2025-08-16 18:24:32');

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
(76, 35, 31, 1, 74.40),
(77, 35, 53, 1, 100.00),
(78, 36, 49, 1, 90.00),
(79, 37, 24, 1, 65.00),
(80, 38, 43, 1, 80.00),
(81, 39, 50, 1, 30.00),
(82, 40, 50, 1, 30.00),
(83, 41, 49, 1, 90.00),
(84, 42, 43, 1, 80.00),
(85, 43, 49, 2, 90.00);

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
(31, 35, 'AGRO-BKASH-1755359780', 'bKash', 'Completed', '2025-08-16 15:56:20'),
(32, 36, 'AGRO-COD-1755360660', 'COD', 'Completed', '2025-08-16 16:11:00'),
(33, 37, 'AGRO-COD-1755361636', 'COD', 'Completed', '2025-08-16 16:27:16'),
(34, 38, 'AGRO-NAGAD-1755361732', 'Nagad', 'Completed', '2025-08-16 16:28:52'),
(35, 39, 'AGRO-COD-1755363897', 'COD', 'Completed', '2025-08-16 17:04:57'),
(36, 40, 'AGRO-COD-1755363983', 'COD', 'Completed', '2025-08-16 17:06:23'),
(37, 41, 'AGRO-BKASH-1755364539', 'bKash', 'Completed', '2025-08-16 17:15:39'),
(38, 42, 'AGRO-CARD-1755365249', 'Card', 'Completed', '2025-08-16 17:27:29'),
(39, 43, 'AGRO-NAGAD-1755368672', 'Nagad', 'Completed', '2025-08-16 18:24:32');

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
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `display_unit` varchar(20) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `name`, `description`, `price`, `unit`, `quantity`, `display_unit`, `stock`, `category`, `image_path`, `created_at`) VALUES
(19, 12, 'Banana', 'Fresh Banana from the Meherpur.', 10.00, 'pc', 1.00, '1.00 pc', 300, 'Fruit', 'images/uploads/Banana.jpg', '2025-08-16 13:08:24'),
(20, 12, 'Amla', 'Fresh Amla', 100.00, 'kg', 1.00, '1.00 kg', 100, 'Fruit', 'images/uploads/Amla.png', '2025-08-16 13:09:48'),
(21, 12, 'Tomato', 'Fresh Vegetables from Farmers.', 60.00, 'kg', 1.00, '1.00 kg', 50, 'Vegetable', 'images/uploads/Tomato.jpg', '2025-08-16 13:12:32'),
(22, 12, 'TeaselGroud', 'Fresh Vegetables', 40.00, 'kg', 1.00, '1.00 kg', 20, 'Vegetable', 'images/uploads/TeaselGroud.jpg', '2025-08-16 13:13:51'),
(23, 12, 'Spinach', 'Fresh Vegetables from farmers.', 10.00, 'kg', 1.00, '1.00 kg', 37, 'Vegetable', 'images/uploads/Spinach.jpg', '2025-08-16 13:15:22'),
(24, 12, 'Pumpkin', 'Fresh Pumpkin', 65.00, 'kg', 1.00, '1.00 kg', 9, 'Vegetable', 'images/uploads/Pumpkin.jpg', '2025-08-16 13:17:35'),
(25, 12, 'Prawn', 'Fresh From Farmers.', 75.00, 'kg', 1.00, '1.00 kg', 60, 'Vegetable', 'images/uploads/Prawn.jpg', '2025-08-16 13:18:46'),
(26, 12, 'Potato', 'Fresh from farmers land.', 25.00, 'kg', 1.00, '1.00 kg', 500, 'Vegetable', 'images/uploads/Potato.jpg', '2025-08-16 13:19:33'),
(27, 12, 'Turmeric', 'Fresh', 100.00, 'gm', 150.00, '150 gm', 100, 'Spice', 'images/uploads/Turmeric.jpg', '2025-08-16 13:48:14'),
(28, 12, 'Black Seed', 'Good Quality', 150.00, 'gm', 100.00, '100 gm', 260, 'Spice', 'images/uploads/Black Seed.jpg', '2025-08-16 14:04:35'),
(29, 12, 'Onion', 'Special Fresh Onion', 75.00, 'kg', 1.00, '1 kg', 200, 'Spice', 'images/uploads/Onion.jpg', '2025-08-16 14:11:33'),
(30, 12, 'Garlic', 'Good', 90.00, 'gm', 50.00, '50 gm', 30, 'Spice', 'images/uploads/Garlic.jpg', '2025-08-16 14:12:18'),
(31, 12, 'Chili', 'Red Hot Chili', 80.00, 'gm', 200.00, '200 gm', 44, 'Spice', 'images/uploads/Chili.jpg', '2025-08-16 14:58:25'),
(32, 12, 'Black Pepper', 'Good Quality', 200.00, 'gm', 250.00, '250 gm', 200, 'Spice', 'images/uploads/Black Pepper.jpg', '2025-08-16 15:00:11'),
(33, 12, 'Cardamom', 'Best Quality', 500.00, 'gm', 500.00, '500 gm', 5, 'Spice', 'images/uploads/cardamom.jpg', '2025-08-16 15:01:03'),
(34, 12, 'Ginger', 'Fresh', 120.00, 'gm', 75.00, '75 gm', 10, 'Spice', 'images/uploads/Ginger.jpg', '2025-08-16 15:01:49'),
(35, 12, 'Cumin', 'Better', 350.00, 'gm', 150.00, '150 gm', 6, 'Spice', 'images/uploads/Cumin.jpg', '2025-08-16 15:03:54'),
(36, 12, 'Cinnamon', 'Best in the Quality', 1000.00, 'gm', 750.00, '750 gm', 5, 'Spice', 'images/uploads/Cinnamon.jpg', '2025-08-16 15:05:05'),
(37, 12, 'Clove', 'Best', 250.00, 'gm', 50.00, '50 gm', 7, 'Spice', 'images/uploads/Clove.jpg', '2025-08-16 15:05:53'),
(38, 12, 'Sesame Seed', 'Good Quality', 176.00, 'gm', 250.00, '250 gm', 400, 'Spice', 'images/uploads/Sesame Seed.jpg', '2025-08-16 15:07:11'),
(39, 12, 'Watermelon', 'Best', 120.00, 'kg', 1.00, '1 kg', 100, 'Fruit', 'images/uploads/Watermelon.jpg', '2025-08-16 15:08:30'),
(40, 12, 'Papaya', 'Good', 40.00, 'kg', 1.00, '1 kg', 20, 'Fruit', 'images/uploads/Papaya.jpg', '2025-08-16 15:09:01'),
(41, 12, 'Pineapple', 'Best', 60.00, 'kg', 1.00, '1 kg', 20, 'Fruit', 'images/uploads/Pineapple.jpg', '2025-08-16 15:09:44'),
(42, 12, 'Mango', 'Best in Quality', 75.00, 'kg', 1.00, '1 kg', 3, 'Fruit', 'images/uploads/Mango.jpg', '2025-08-16 15:10:22'),
(43, 12, 'Jackfruit', 'Good', 80.00, 'pc', 1.00, '1 pc', 298, 'Fruit', 'images/uploads/Jackfruit.jpg', '2025-08-16 15:11:25'),
(44, 12, 'Wood Apple', 'Good in the Quality', 90.00, 'pc', 1.00, '1 pc', 10, 'Fruit', 'images/uploads/Wood Apple.jpg', '2025-08-16 15:12:10'),
(45, 12, 'Dragon Fruit', 'Best in the Market', 300.00, 'kg', 1.00, '1 kg', 5, 'Fruit', 'images/uploads/Dragon Fruit.jpg', '2025-08-16 15:13:02'),
(46, 12, 'Beetroot', 'Best', 70.00, 'kg', 1.00, '1 kg', 5, 'Vegetable', 'images/uploads/Beetroot.jpg', '2025-08-16 15:14:30'),
(47, 12, 'Cabbage', 'Nice', 35.00, 'pc', 1.00, '1 pc', 100, 'Vegetable', 'images/uploads/cabbage.jpg', '2025-08-16 15:15:19'),
(48, 12, 'Cauliflower', 'Nice', 45.00, 'pc', 1.00, '1 pc', 20, 'Vegetable', 'images/uploads/Cauliflower.jpg', '2025-08-16 15:16:30'),
(49, 12, 'Carrot', 'Best', 90.00, 'kg', 1.00, '1 kg', 3, 'Vegetable', 'images/uploads/Carrot.jpg', '2025-08-16 15:17:21'),
(50, 12, 'Cucumber', 'Good', 30.00, 'kg', 1.00, '1 kg', 21, 'Vegetable', 'images/uploads/Cucumber.jpg', '2025-08-16 15:18:09'),
(51, 12, 'কাটিমন-আম', 'Best in the Market', 150.00, 'kg', 1.00, '1 kg', 4, 'Fruit', 'images/uploads/কাটিমন-আম.png', '2025-08-16 15:22:02'),
(52, 12, 'ব্যানানা-আম(Mango)', 'Best', 65.00, 'kg', 1.00, '1 kg', 200, 'Fruit', 'images/uploads/ব্যানানা-আম.png', '2025-08-16 15:23:03'),
(53, 12, 'গোপালভোগ-আম', 'Nice', 100.00, 'kg', 1.00, '1 kg', 77, 'Fruit', 'images/uploads/গোপালভোগ-আম.png', '2025-08-16 15:23:58');

-- --------------------------------------------------------

--
-- Table structure for table `recently_viewed`
--

CREATE TABLE `recently_viewed` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recently_viewed`
--

INSERT INTO `recently_viewed` (`id`, `user_id`, `product_id`, `viewed_at`) VALUES
(12, 10, 30, '2025-08-16 14:56:40'),
(13, 10, 31, '2025-08-16 14:58:31'),
(15, 10, 36, '2025-08-16 15:27:35'),
(16, 10, 51, '2025-08-16 15:55:11'),
(17, 13, 50, '2025-08-16 17:23:20'),
(20, 13, 45, '2025-08-16 17:23:54'),
(21, 13, 44, '2025-08-16 18:23:36');

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
(4, 45, 13, 5, 'Very good Products.', '2025-08-16 17:23:54');

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
  `google_id` varchar(255) DEFAULT NULL,
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

INSERT INTO `users` (`id`, `name`, `email`, `google_id`, `phone`, `division`, `district`, `city`, `role`, `profile_image_path`, `password`, `created_at`) VALUES
(10, 'Fahim Khan', 'talha@gmail.com', NULL, '01776199963', 'Dhaka', 'Dhaka', 'Savar', 'Buyer', 'images/profiles/user_1755348580_talha.jpg', '$2y$10$HTor2wyT3hEN50TaXqPleefWt8YD6tgnKhECl6CcUbgEYKD78D0Ce', '2025-08-16 12:49:40'),
(11, 'Abul Mia', 'talha1@gmail.com', NULL, '01776199964', 'Barishal', 'Bhola', 'Bhola Sadar', 'Buyer', 'images/profiles/user_1755348787_professional-profile-pictures-1080-x-1080-460wjhrkbwdcp1ig.jpg', '$2y$10$PewtimMWhW410lS9aCf2Huw6yG/NGYQFLp0U8dNhsoloxfMdFT0SS', '2025-08-16 12:53:07'),
(12, 'AgrokartBD', 'agrokart@gmail.com', NULL, '01776199967', 'Dhaka', 'Dhaka', 'Savar', 'Seller', NULL, '$2y$10$ZqAk/NsPqSH0Vs.cMkV2B.lcLehIKZwEHEYOS5TYaL4heMldPQrRe', '2025-08-16 12:55:07'),
(13, 'Talha Khan', 'fahimtalha9@gmail.com', '104522100124813673638', '01776199963', NULL, NULL, NULL, 'Buyer', 'images/profiles/user_13_google_1755363087.jpg', '$2y$10$8YM2d9JlKR0WjYvzoI2LvuQYtaWNezA4oBvxYicSZQYmGzB6RE3k2', '2025-08-16 16:51:26');

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--

CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_cart`
--

INSERT INTO `user_cart` (`id`, `user_id`, `product_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(66, 10, 49, 1, 90.00, '2025-08-16 18:31:20', '2025-08-16 18:31:20'),
(67, 13, 49, 1, 90.00, '2025-08-16 19:01:57', '2025-08-16 19:01:57');

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
-- Indexes for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
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
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `seller_hidden_customers`
--
ALTER TABLE `seller_hidden_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

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
