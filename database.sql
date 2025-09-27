-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2025 at 12:10 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vogie_web`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(255) DEFAULT NULL,
  `remember_token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `is_active`, `remember_token`, `remember_token_expires`, `created_at`) VALUES
(10, 'kakuja', 'test@test.com', '0643543443', '', 'user', 1, NULL, NULL, '2025-08-30 23:37:53'),
(15, 'Administrator', 'admin@vogie.com', '0600000000', '$2y$10$k1Y0I/7lN5q1rr5IEiRhSuCbA1FhRARhgsvzkCHsJD6BqCBCp/tre', 'admin', 1, NULL, NULL, '2025-09-05 14:28:32'),
(18, 'Mohammed', 'mohammed@gmail.com', '0656487885', '', 'user', 1, NULL, NULL, '2025-09-25 17:34:43'),
(19, 'Amin', 'dokali@gmail.com', '0767654531', '', 'user', 1, NULL, NULL, '2025-09-25 21:21:10'),
(20, 'Kamal', 'samhi', '8753425226', '', 'user', 1, NULL, NULL, '2025-09-25 21:21:49'),
(21, 'Salma', 'tijani', '0656432878', '', 'user', 1, NULL, NULL, '2025-09-25 21:22:34'),
(22, 'Siham', 'koulim', '0764423242', '', 'user', 1, NULL, NULL, '2025-09-25 21:23:10'),
(24, 'Hamid', 'hamid222@gmail.com', '0656478552', '', 'user', 1, NULL, NULL, '2025-09-26 13:16:11'),
(25, 'Jamila Toujani', 'Jamilatt@gmail.com', '0645782536', '', 'user', 1, NULL, NULL, '2025-09-26 14:00:58'),
(26, 'Amin', 'example124@gmail.com', '0606060607', '', 'user', 1, NULL, NULL, '2025-09-27 08:57:55'),
(27, 'Mohammed ennouh', 'example3333@gmail.com', '0746532432', '', 'user', 1, NULL, NULL, '2025-09-27 09:40:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
