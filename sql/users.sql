-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2025 at 10:53 PM
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `registration_date` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime NOT NULL,
  `student_class` varchar(50) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `show_rank` tinyint(1) NOT NULL DEFAULT 1,
  `show_scores` tinyint(1) NOT NULL DEFAULT 1,
  `show_tests` tinyint(1) NOT NULL DEFAULT 1,
  `show_class` tinyint(1) NOT NULL DEFAULT 1,
  `show_avatar` tinyint(1) NOT NULL DEFAULT 1,
  `share_token` varchar(32) DEFAULT NULL,
  `share_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`, `registration_date`, `last_login`, `student_class`, `avatar`, `show_rank`, `show_scores`, `show_tests`, `show_class`, `show_avatar`, `share_token`, `share_token_expiry`) VALUES
(1, 'mostafa', 'mostafamta347@gmail.com', '+201003504114', '$2y$10$jcdMU9Aa2srHivnxoV27QOs2/w6zP1LPl3x.YRbGbWesxX4aErJ1K', '2025-05-29 04:06:05', '2025-06-04 22:44:54', 'الصف الثالث الثانوي', 'uploads/avatars/user_1_1749036995.jpg', 0, 0, 0, 0, 1, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `share_token` (`share_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
