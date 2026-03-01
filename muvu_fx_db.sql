-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 02:50 PM
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
-- Database: `muvu_fx_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, NULL, 'User Registration', 'Registered new user: accountant', '::1', '2026-02-24 12:45:34'),
(2, NULL, 'Logout', 'User logged out', '::1', '2026-02-24 12:45:46'),
(3, 10, 'Login', 'User logged in', '::1', '2026-02-25 10:26:28'),
(4, 10, 'Logout', 'User logged out', '::1', '2026-02-25 10:30:39'),
(5, 10, 'Login', 'User logged in', '::1', '2026-02-25 10:30:41'),
(6, 10, 'Logout', 'User logged out', '::1', '2026-02-25 10:42:26'),
(7, 10, 'Login', 'User logged in', '::1', '2026-02-25 10:42:51'),
(8, 10, 'Logout', 'User logged out', '::1', '2026-02-25 10:43:25'),
(9, 10, 'Login', 'User logged in', '::1', '2026-02-25 10:43:27'),
(10, 10, 'Logout', 'User logged out', '::1', '2026-02-25 10:44:32'),
(11, 10, 'Login', 'User logged in', '::1', '2026-02-25 10:44:34'),
(12, 10, 'User Registration', 'Registered new user: staff', '::1', '2026-02-25 11:45:58'),
(13, 10, 'User Registration', 'Registered new user: staff1', '::1', '2026-02-25 12:28:04'),
(15, 10, 'Logout', 'User logged out', '::1', '2026-02-25 13:08:10'),
(16, NULL, 'Login', 'User logged in', '::1', '2026-02-25 13:08:21'),
(17, NULL, 'Logout', 'User logged out', '::1', '2026-02-25 13:48:49'),
(18, NULL, 'Login', 'User logged in', '::1', '2026-02-25 13:48:51'),
(20, NULL, 'Logout', 'User logged out', '::1', '2026-02-25 13:59:23'),
(21, 10, 'Login', 'User logged in', '::1', '2026-02-25 13:59:28'),
(22, 10, 'Logout', 'User logged out', '::1', '2026-02-25 14:01:39'),
(23, NULL, 'Login', 'User logged in', '::1', '2026-02-25 14:01:45'),
(25, NULL, 'Logout', 'User logged out', '::1', '2026-02-25 14:05:19'),
(26, 10, 'Login', 'User logged in', '::1', '2026-02-25 14:05:22'),
(28, 10, 'Logout', 'User logged out', '::1', '2026-02-25 14:21:24'),
(29, 10, 'Login', 'User logged in', '::1', '2026-02-25 14:21:26'),
(30, 10, 'Add Product', 'Added product: TECNO POP9 Screen', '::1', '2026-02-25 15:03:24'),
(31, 10, 'Edit Product', 'Edited product: TECNO POP9 Screen', '::1', '2026-02-25 15:20:18'),
(32, 10, 'Delete Sale', 'Deleted sale ID: 4', '::1', '2026-02-25 15:30:52'),
(33, 10, 'Edit Product', 'Edited product: AirTel Imagina Screen', '::1', '2026-02-25 15:52:13'),
(34, 10, 'Logout', 'User logged out', '::1', '2026-02-25 16:16:26'),
(35, NULL, 'Login', 'User logged in', '::1', '2026-02-25 16:16:32'),
(36, NULL, 'Logout', 'User logged out', '::1', '2026-02-25 16:17:10'),
(37, NULL, 'Login', 'User logged in', '::1', '2026-02-25 18:18:09'),
(38, NULL, 'Logout', 'User logged out', '::1', '2026-02-25 18:24:28'),
(39, 10, 'Login', 'User logged in', '::1', '2026-02-25 18:24:33'),
(40, 10, 'Add Product', 'Added product: Speaker SP', '::1', '2026-02-25 18:25:50'),
(41, 10, 'Logout', 'User logged out', '::1', '2026-02-25 18:27:34'),
(42, NULL, 'Login', 'User logged in', '::1', '2026-02-25 18:27:41'),
(43, NULL, 'Logout', 'User logged out', '::1', '2026-02-25 18:30:27'),
(44, 10, 'Login', 'User logged in', '::1', '2026-02-25 18:30:31'),
(45, 10, 'Edit Product', 'Edited product: Speaker SP', '::1', '2026-02-25 18:39:29'),
(46, 10, 'Edit Product', 'Edited product: Headphone', '::1', '2026-02-25 18:40:14'),
(47, 10, 'Edit Product', 'Edited product: Battery', '::1', '2026-02-25 18:41:02'),
(48, 10, 'Restock Product', 'Added 10 units to product ID: 1', '::1', '2026-02-25 18:42:51'),
(49, 10, 'Restock Product', 'Added 10 units to product ID: 5', '::1', '2026-02-25 18:43:05'),
(50, 10, 'Restock Product', 'Added 10 units to product ID: 3', '::1', '2026-02-25 18:44:59'),
(51, 10, 'Logout', 'User logged out', '::1', '2026-02-25 18:48:45'),
(52, 10, 'Login', 'User logged in', '::1', '2026-02-26 05:54:47'),
(53, 10, 'Delete Sale', 'Deleted sale ID: 6', '::1', '2026-02-26 06:34:12'),
(54, 10, 'Logout', 'User logged out', '::1', '2026-02-26 06:37:37'),
(55, 10, 'Login', 'User logged in', '::1', '2026-02-26 07:01:44'),
(56, 10, 'Bulk Restock', 'Processed bulk restock', '::1', '2026-02-26 07:26:58'),
(57, 10, 'Bulk Restock', 'Processed bulk restock', '::1', '2026-02-26 07:26:59'),
(58, 10, 'Bulk Restock', 'Processed bulk restock', '::1', '2026-02-26 07:27:00'),
(59, 10, 'Bulk Restock', 'Processed bulk restock', '::1', '2026-02-26 07:27:00'),
(60, 10, 'Bulk Restock', 'Processed bulk restock', '::1', '2026-02-26 07:27:00'),
(61, 10, 'Bulk Restock', 'Processed bulk restock', '::1', '2026-02-26 07:27:00'),
(62, 10, 'Bulk Restock', 'Processed bulk restock', '::1', '2026-02-26 07:27:00'),
(63, 10, 'Bulk Restock', 'Processed bulk restock', '::1', '2026-02-26 07:27:00'),
(64, 10, 'Add Sale', 'Sold 20 of product ID 2', '::1', '2026-02-26 07:31:57'),
(65, 10, 'Add Sale', 'Sold 10 of product ID 1', '::1', '2026-02-26 07:32:13'),
(66, 10, 'Add Sale', 'Sold 82 of product ID 3', '::1', '2026-02-26 07:32:40'),
(67, 10, 'Add Sale', 'Sold 50 of product ID 5', '::1', '2026-02-26 07:33:12'),
(68, 10, 'Logout', 'User logged out', '::1', '2026-02-26 07:40:40'),
(69, 10, 'Login', 'User logged in', '::1', '2026-02-26 09:07:32'),
(70, 10, 'Logout', 'User logged out', '::1', '2026-02-26 09:13:23'),
(71, 10, 'Login', 'User logged in', '::1', '2026-02-26 09:19:55'),
(72, 10, 'Logout', 'User logged out', '::1', '2026-02-26 09:26:15'),
(73, 10, 'Login', 'User logged in', '::1', '2026-02-26 09:27:55'),
(74, 10, 'Logout', 'User logged out', '::1', '2026-02-26 10:13:09'),
(75, NULL, 'Login', 'User logged in', '::1', '2026-02-26 10:13:14'),
(76, NULL, 'Logout', 'User logged out', '::1', '2026-02-26 11:19:01'),
(77, 10, 'Login', 'User logged in', '::1', '2026-02-26 11:19:05'),
(78, 10, 'Logout', 'User logged out', '::1', '2026-02-26 12:05:09'),
(79, 10, 'Login', 'User logged in', '::1', '2026-02-26 12:05:24'),
(80, 10, 'Logout', 'User logged out', '::1', '2026-02-26 12:06:50'),
(81, 10, 'Login', 'User logged in', '::1', '2026-02-26 12:15:58'),
(82, 10, 'Logout', 'User logged out', '::1', '2026-02-26 12:16:24'),
(83, NULL, 'Login', 'User logged in', '::1', '2026-02-26 12:16:30'),
(84, NULL, 'Logout', 'User logged out', '::1', '2026-02-26 12:27:47'),
(85, 10, 'Login', 'User logged in', '::1', '2026-02-26 12:27:52'),
(86, 10, 'Login', 'User logged in', '127.0.0.1', '2026-02-26 12:56:06'),
(87, 10, 'Add Sale', 'Sold 2 of product ID 4', '127.0.0.1', '2026-02-26 12:57:54'),
(88, 10, 'Bulk Restock', 'Processed bulk restock', '127.0.0.1', '2026-02-26 12:59:00'),
(89, 10, 'Logout', 'User logged out', '127.0.0.1', '2026-02-26 13:02:22'),
(90, 10, 'Login', 'User logged in', '127.0.0.1', '2026-02-26 13:03:16'),
(91, 10, 'Logout', 'User logged out', '127.0.0.1', '2026-02-26 13:03:30'),
(92, 10, 'Logout', 'User logged out', '::1', '2026-02-26 13:04:12'),
(93, 10, 'Login', 'User logged in', '::1', '2026-02-26 13:04:20'),
(94, 10, 'Delete User', 'Deleted user ID: 11', '::1', '2026-02-26 13:04:36'),
(95, 10, 'User Registration', 'Registered new user: mukamana', '::1', '2026-02-26 13:05:56'),
(96, 10, 'Logout', 'User logged out', '::1', '2026-02-26 13:06:08'),
(97, 13, 'Login', 'User logged in', '::1', '2026-02-26 13:06:21'),
(98, 13, 'Add Sale', 'Sold 1 of product ID 5', '::1', '2026-02-26 13:07:42'),
(99, 13, 'Logout', 'User logged out', '::1', '2026-02-26 13:07:54'),
(100, 13, 'Login', 'User logged in', '127.0.0.1', '2026-02-26 13:08:07'),
(101, 13, 'Login', 'User logged in', '::1', '2026-02-26 13:21:52');

-- --------------------------------------------------------

--
-- Table structure for table `daily_summary`
--

CREATE TABLE `daily_summary` (
  `id` int(11) NOT NULL,
  `summary_date` date NOT NULL,
  `total_sales` decimal(10,2) DEFAULT 0.00,
  `total_profit` decimal(10,2) DEFAULT 0.00,
  `total_expenses` decimal(10,2) DEFAULT 0.00,
  `net_profit` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10,
  `buying_price` decimal(10,2) DEFAULT 0.00,
  `selling_price` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `description`, `quantity`, `low_stock_threshold`, `buying_price`, `selling_price`, `created_at`, `updated_at`) VALUES
(1, 'Battery', 'This is a battery for TECNO Phones', 10, 1, 3000.00, 5000.00, '2026-02-24 12:36:21', '2026-02-26 12:59:00'),
(2, 'AirTel Imagina Screen', 'This is a screen for AirTel Imagaine phones', 40, 1, 3500.00, 7000.00, '2026-02-24 12:38:34', '2026-02-26 12:59:00'),
(3, 'Headphone', 'Zero to A', 11, 1, 500.00, 800.00, '2026-02-25 14:07:02', '2026-02-26 12:59:00'),
(4, 'TECNO POP9 Screen', 'This is a screen for POP 9', 58, 1, 8000.00, 10000.00, '2026-02-25 15:03:24', '2026-02-26 12:59:00'),
(5, 'Speaker SP', 'This is a small speaker for home', 42, 0, 50000.00, 70000.00, '2026-02-25 18:25:49', '2026-02-26 13:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `product_history`
--

CREATE TABLE `product_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `restock_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_history`
--

INSERT INTO `product_history` (`id`, `product_id`, `quantity`, `total_cost`, `restock_date`) VALUES
(1, 3, 9, 4500.00, '2026-02-26 09:26:58'),
(2, 5, 9, 450000.00, '2026-02-26 09:26:58'),
(3, 4, 5, 40000.00, '2026-02-26 09:26:58'),
(4, 3, 9, 4500.00, '2026-02-26 09:26:59'),
(5, 5, 9, 450000.00, '2026-02-26 09:26:59'),
(6, 4, 5, 40000.00, '2026-02-26 09:26:59'),
(7, 3, 9, 4500.00, '2026-02-26 09:27:00'),
(8, 5, 9, 450000.00, '2026-02-26 09:27:00'),
(9, 4, 5, 40000.00, '2026-02-26 09:27:00'),
(10, 3, 9, 4500.00, '2026-02-26 09:27:00'),
(11, 5, 9, 450000.00, '2026-02-26 09:27:00'),
(12, 4, 5, 40000.00, '2026-02-26 09:27:00'),
(13, 3, 9, 4500.00, '2026-02-26 09:27:00'),
(14, 5, 9, 450000.00, '2026-02-26 09:27:00'),
(15, 4, 5, 40000.00, '2026-02-26 09:27:00'),
(16, 3, 9, 4500.00, '2026-02-26 09:27:00'),
(17, 5, 9, 450000.00, '2026-02-26 09:27:00'),
(18, 4, 5, 40000.00, '2026-02-26 09:27:00'),
(19, 3, 9, 4500.00, '2026-02-26 09:27:00'),
(20, 5, 9, 450000.00, '2026-02-26 09:27:00'),
(21, 4, 5, 40000.00, '2026-02-26 09:27:00'),
(22, 3, 9, 4500.00, '2026-02-26 09:27:00'),
(23, 5, 9, 450000.00, '2026-02-26 09:27:00'),
(24, 4, 5, 40000.00, '2026-02-26 09:27:00'),
(25, 2, 10, 35000.00, '2026-02-26 14:59:00'),
(26, 1, 10, 30000.00, '2026-02-26 14:59:00'),
(27, 3, 10, 5000.00, '2026-02-26 14:59:00'),
(28, 5, 10, 500000.00, '2026-02-26 14:59:00'),
(29, 4, 10, 80000.00, '2026-02-26 14:59:00');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `profit` decimal(10,2) NOT NULL,
  `sold_by` int(11) DEFAULT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `quantity`, `unit_price`, `total_amount`, `profit`, `sold_by`, `sale_date`) VALUES
(2, 2, 2, 7000.00, 14000.00, 7000.00, 10, '2026-02-25 10:39:22'),
(3, 1, 3, 5000.00, 15000.00, 6000.00, NULL, '2026-02-25 13:56:14'),
(5, 5, 3, 80000.00, 240000.00, 90000.00, NULL, '2026-02-25 18:32:39'),
(7, 2, 20, 7000.00, 140000.00, 70000.00, 10, '2026-02-26 07:31:57'),
(8, 1, 10, 5000.00, 50000.00, 20000.00, 10, '2026-02-26 07:32:13'),
(9, 3, 82, 800.00, 65600.00, 24600.00, 10, '2026-02-26 07:32:40'),
(10, 5, 50, 70000.00, 3500000.00, 1000000.00, 10, '2026-02-26 07:33:12'),
(11, 4, 2, 10000.00, 20000.00, 4000.00, 10, '2026-02-26 12:57:54'),
(12, 5, 1, 70000.00, 70000.00, 20000.00, 13, '2026-02-26 13:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `role`, `created_at`, `updated_at`) VALUES
(10, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@muvufx.com', '0786874837', 'admin', '2026-02-25 10:24:20', '2026-02-25 11:44:34'),
(13, 'mukamana', '$2y$10$DtGpzkTUb9E4pq/bKAgED.E6.a./e7Ijh7Od0BN89BfQzALRuSruS', 'MUKAMANA Ange', 'mukamana@gmail.com', '0784785254', 'staff', '2026-02-26 13:05:56', '2026-02-26 13:05:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `daily_summary`
--
ALTER TABLE `daily_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `summary_date` (`summary_date`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_history`
--
ALTER TABLE `product_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `sold_by` (`sold_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `daily_summary`
--
ALTER TABLE `daily_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_history`
--
ALTER TABLE `product_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_history`
--
ALTER TABLE `product_history`
  ADD CONSTRAINT `product_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`sold_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
