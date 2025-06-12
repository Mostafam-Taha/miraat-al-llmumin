-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 12:14 PM
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
-- Database: `sahat_alllm`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `national_id` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `address`, `birth_date`, `gender`, `national_id`, `profile_image`, `registration_date`, `last_login`, `is_active`, `is_verified`) VALUES
(3, 'mostafa', '$2y$10$jxxUQ0kdtxiwsNgdYOI14OYitRv4Y48IWV532flJcr6VueSW7bEDy', 'mostafa taha', 'MostafaTa39690@gmail.com', '01234567890', 'الجيزة العياط', '2025-06-11', 'male', '12121121212121', NULL, '2025-06-07 20:26:55', '2025-06-08 12:38:45', 1, 1),
(4, 'mostafa0', '$2y$10$/TwbaBfs2yeOK2MG/m4vf.CVHYupZ00mBEyX2bJNhw7FKYqsn5rVa', 'mostafa mtaha', 'hk303792mmmm@gmail.com', '01003504114', 'الجيزة العياط', '2025-07-08', 'male', '0000000000', NULL, '2025-06-08 11:34:14', NULL, 1, 0),
(5, 'mostafamtaha', '$2y$10$okOwiQM88agHdINnIcIymueXoHE74BYvRiI2tIhAMZenX2Ur4TVlK', 'mostafa mtaha', 'hk303794mmmm@gmail.com', '01003504111', 'الجيزة العياط', '2025-06-24', 'male', '0000000000', NULL, '2025-06-08 11:35:47', NULL, 1, 0),
(6, 'a', '$2y$10$lcDtFo6GSwnFqB3oFMig5eCaTy0L2TzIcjdNnrRGGxUjNaQ/stOrS', 'asdf hk303792w@&quot;', 'mostafamta347@gmail.com', '01148138347', 'الجيزة العياط', '2025-05-22', 'male', '0000000000', NULL, '2025-06-08 11:38:17', '2025-06-08 11:39:33', 1, 1),
(7, 'test', '$2y$10$Elni61wYg7oJHYt3rt7qfu5TSlJebRxsovj1k2G2QjGiidpqvYwsm', 't', 't@gmail.com', '01003504118', 'الجيزة العياط', '2025-04-24', 'male', '0000000000', 'uploads/profiles/68454e0f548a9.jpeg', '2025-06-08 11:47:11', '2025-06-08 12:44:57', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime DEFAULT current_timestamp(),
  `is_successful` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `admin_id`, `ip_address`, `attempt_time`, `is_successful`) VALUES
(1, 3, '::1', '2025-06-07 20:28:24', 1),
(2, 3, '::1', '2025-06-07 20:34:05', 1),
(3, 3, '::1', '2025-06-07 20:55:16', 1),
(4, 3, '::1', '2025-06-07 21:14:59', 1),
(5, 3, '::1', '2025-06-07 21:15:33', 0),
(6, 3, '::1', '2025-06-07 21:15:45', 1),
(7, 3, '::1', '2025-06-07 21:22:13', 1),
(8, 3, '::1', '2025-06-07 23:09:39', 1),
(9, 3, '::1', '2025-06-08 10:48:39', 0),
(10, 3, '::1', '2025-06-08 10:48:51', 1),
(11, 3, '::1', '2025-06-08 10:50:17', 1),
(12, 3, '::1', '2025-06-08 10:57:55', 1),
(13, 3, '::1', '2025-06-08 10:58:51', 1),
(14, 3, '::1', '2025-06-08 11:01:38', 1),
(15, 3, '::1', '2025-06-08 11:02:10', 1),
(16, 3, '::1', '2025-06-08 11:02:28', 1),
(17, 3, '::1', '2025-06-08 11:22:24', 1),
(18, 3, '::1', '2025-06-08 11:22:27', 1),
(19, 3, '::1', '2025-06-08 11:22:58', 1),
(20, 6, '::1', '2025-06-08 11:38:55', 1),
(21, 3, '::1', '2025-06-08 12:37:39', 0),
(22, 3, '::1', '2025-06-08 12:37:49', 1),
(23, 3, '::1', '2025-06-08 12:38:30', 1),
(24, 7, '::1', '2025-06-08 12:39:48', 1),
(25, 7, '::1', '2025-06-08 12:39:48', 1),
(26, 7, '::1', '2025-06-08 12:43:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

CREATE TABLE `verification_codes` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `code` varchar(16) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_codes`
--

INSERT INTO `verification_codes` (`id`, `admin_id`, `code`, `phone`, `created_at`, `expires_at`, `is_used`) VALUES
(4, 3, '8fa562a8488e860e', '01234567890', '2025-06-07 20:28:24', '2025-06-07 21:28:24', 1),
(5, 3, '3f90da4964d8d70e', '01234567890', '2025-06-07 20:34:05', '2025-06-07 21:34:05', 1),
(6, 3, 'c6a01b3a667cafbd', '01234567890', '2025-06-07 20:55:16', '2025-06-07 21:55:16', 1),
(7, 3, '70ede3c1d2d41bf4', '01234567890', '2025-06-07 21:14:58', '2025-06-07 22:14:58', 1),
(8, 3, '17cd566905e8ccd7', '01234567890', '2025-06-07 21:15:45', '2025-06-07 22:15:45', 1),
(9, 3, '7bffecb63b9790fe', '01234567890', '2025-06-07 21:22:13', '2025-06-07 22:22:13', 1),
(10, 3, '2cb43a204ade039a', '01234567890', '2025-06-07 23:09:39', '2025-06-08 00:09:39', 1),
(11, 3, '52db16a756b3e83f', '01234567890', '2025-06-08 10:48:51', '2025-06-08 11:48:51', 1),
(12, 3, '3dbaad790ec4efaf', '01234567890', '2025-06-08 10:50:17', '2025-06-08 11:50:17', 0),
(13, 3, 'afc84a76d58aef3c', '01234567890', '2025-06-08 10:57:55', '2025-06-08 11:57:55', 1),
(14, 3, 'ea9e9f7255ab1c71', '01234567890', '2025-06-08 10:58:51', '2025-06-08 11:58:51', 0),
(15, 3, 'b870d39ceb010ed0', '01234567890', '2025-06-08 11:01:38', '2025-06-08 12:01:38', 0),
(16, 3, 'a8535ff97db30a59', '01234567890', '2025-06-08 11:02:10', '2025-06-08 12:02:10', 0),
(17, 3, 'bab93bd30c173e2f', '01234567890', '2025-06-08 11:02:28', '2025-06-08 12:02:28', 0),
(18, 3, '5b10978e962da128', '01234567890', '2025-06-08 11:22:24', '2025-06-08 12:22:24', 0),
(19, 3, '19ce0b19ab314aee', '01234567890', '2025-06-08 11:22:27', '2025-06-08 12:22:27', 0),
(20, 3, '67938ce0b4d6c930', '01234567890', '2025-06-08 11:22:58', '2025-06-08 12:22:58', 0),
(21, 4, '7ae600e6965004a5', '01003504114', '2025-06-08 11:34:14', '2025-06-08 12:34:14', 0),
(22, 5, '2c56a60041e25ba0', '01003504111', '2025-06-08 11:35:48', '2025-06-08 12:35:48', 0),
(23, 6, '9b1a563e6edd908b', '01148138347', '2025-06-08 11:38:17', '2025-06-08 12:38:17', 0),
(24, 6, '4619e1373dbff0d9', '01148138347', '2025-06-08 11:38:55', '2025-06-08 12:38:55', 1),
(25, 7, '808cb690cae3b292', '01003504118', '2025-06-08 11:47:11', '2025-06-08 12:47:11', 0),
(26, 3, '9d5ff593bd36fe4f', '01234567890', '2025-06-08 12:37:49', '2025-06-08 13:37:49', 1),
(27, 3, '4a0eac4e176f623e', '01234567890', '2025-06-08 12:38:30', '2025-06-08 13:38:30', 1),
(28, 7, '270a29fa2dc9c773', '01003504118', '2025-06-08 12:39:48', '2025-06-08 13:39:48', 1),
(29, 7, '3cfbb58f3bba3371', '01003504118', '2025-06-08 12:39:48', '2025-06-08 13:39:48', 0),
(30, 7, 'fed0faef8b034db6', '01003504118', '2025-06-08 12:43:57', '2025-06-08 13:43:57', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `login_attempts_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD CONSTRAINT `verification_codes_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
