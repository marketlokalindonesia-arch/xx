-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2025 at 10:46 PM
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
-- Database: `setoran_harian1`
--

-- --------------------------------------------------------

--
-- Table structure for table `bbm_distribution`
--

CREATE TABLE `bbm_distribution` (
  `id` int(11) NOT NULL,
  `bbm_group_id` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `jumlah_drigen` decimal(10,2) DEFAULT NULL,
  `pajak` decimal(12,2) DEFAULT NULL,
  `beban` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_flow_management`
--

CREATE TABLE `cash_flow_management` (
  `id` int(11) NOT NULL,
  `bbm_group_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `store_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `type` enum('Pemasukan','Pengeluaran') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_name` varchar(100) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `store_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_name`, `employee_code`, `store_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Lika', 'PTM002', 2, 1, '2025-10-21 18:47:07', '2025-10-21 18:47:07'),
(2, 'Putri', 'PTM001', 2, 1, '2025-10-21 18:47:19', '2025-10-21 18:47:19'),
(3, 'Ayu', 'TBH001', 1, 1, '2025-10-21 18:47:56', '2025-10-21 18:47:56'),
(4, 'Yosef', 'TBH002', 1, 1, '2025-10-21 18:48:09', '2025-10-21 18:48:09'),
(5, 'Nadia', NULL, 4, 1, '2025-10-22 12:35:46', '2025-10-22 12:35:46');

-- --------------------------------------------------------

--
-- Table structure for table `pemasukan`
--

CREATE TABLE `pemasukan` (
  `id` int(11) NOT NULL,
  `setoran_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL,
  `setoran_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `setoran`
--

CREATE TABLE `setoran` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `store_name` varchar(100) DEFAULT NULL,
  `jam_masuk` time NOT NULL,
  `jam_keluar` time NOT NULL,
  `nomor_awal` decimal(10,2) NOT NULL,
  `nomor_akhir` decimal(10,2) NOT NULL,
  `total_liter` decimal(10,2) NOT NULL,
  `qris` int(11) NOT NULL DEFAULT 0,
  `cash` int(11) NOT NULL,
  `total_setoran` int(11) NOT NULL,
  `total_pengeluaran` int(11) NOT NULL DEFAULT 0,
  `total_pemasukan` int(11) NOT NULL DEFAULT 0,
  `total_keseluruhan` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `setoran`
--

INSERT INTO `setoran` (`id`, `tanggal`, `employee_id`, `employee_name`, `store_id`, `store_name`, `jam_masuk`, `jam_keluar`, `nomor_awal`, `nomor_akhir`, `total_liter`, `qris`, `cash`, `total_setoran`, `total_pengeluaran`, `total_pemasukan`, `total_keseluruhan`, `created_at`, `updated_at`) VALUES
(5, '2025-10-22', 5, 'Nadia', 4, 'SAGUBA', '12:12:00', '03:31:00', 13.00, 123.00, 110.00, 1233, 1263767, 1265000, 0, 0, 1263767, '2025-10-22 12:36:32', '2025-10-22 12:36:32');

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` int(11) NOT NULL,
  `store_name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `store_name`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Tiban Hills', 'Jl. Raya Utama No. 1', '2025-10-21 18:12:30', '2025-10-21 18:46:57'),
(2, 'Patam Lestari', 'Kampung Tua Patam Lestari', '2025-10-21 18:12:30', '2025-10-21 18:46:46'),
(4, 'SAGUBA', 'Sagulung Baru', '2025-10-22 12:35:20', '2025-10-22 12:35:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin', 'admin', '2025-10-21 18:12:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bbm_distribution`
--
ALTER TABLE `bbm_distribution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`store_id`),
  ADD KEY `bbm_group_id` (`bbm_group_id`);

--
-- Indexes for table `cash_flow_management`
--
ALTER TABLE `cash_flow_management`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`store_id`),
  ADD KEY `tanggal` (`tanggal`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`store_id`);

--
-- Indexes for table `pemasukan`
--
ALTER TABLE `pemasukan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `setoran_id` (`setoran_id`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `setoran_id` (`setoran_id`);

--
-- Indexes for table `setoran`
--
ALTER TABLE `setoran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `store_id` (`store_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bbm_distribution`
--
ALTER TABLE `bbm_distribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_flow_management`
--
ALTER TABLE `cash_flow_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pemasukan`
--
ALTER TABLE `pemasukan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `setoran`
--
ALTER TABLE `setoran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bbm_distribution`
--
ALTER TABLE `bbm_distribution`
  ADD CONSTRAINT `bbm_distribution_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `bbm_distribution_ibfk_2` FOREIGN KEY (`bbm_group_id`) REFERENCES `cash_flow_management` (`id`);

--
-- Constraints for table `cash_flow_management`
--
ALTER TABLE `cash_flow_management`
  ADD CONSTRAINT `cash_flow_management_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pemasukan`
--
ALTER TABLE `pemasukan`
  ADD CONSTRAINT `pemasukan_ibfk_1` FOREIGN KEY (`setoran_id`) REFERENCES `setoran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`setoran_id`) REFERENCES `setoran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `setoran`
--
ALTER TABLE `setoran`
  ADD CONSTRAINT `setoran_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `setoran_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
