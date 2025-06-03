-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 11:14 PM
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
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`, `registration_date`, `last_login`, `student_class`, `avatar`) VALUES
(1, 'mostafa', 'mostafamta347@gmail.com', '+201003504114', '$2y$10$jcdMU9Aa2srHivnxoV27QOs2/w6zP1LPl3x.YRbGbWesxX4aErJ1K', '2025-05-29 04:06:05', '2025-06-02 17:24:18', 'الصف الثالث الثانوي', 'uploads/avatars/user_1_1748888243.jpg'),
(2, 'user', 'mostafamta347@gmail.com', '+201111111111', '$2y$10$A5K1St.tgD17iGo4rS3kQeHn3zsLSH5M.j5.FKJ0PcH9epeM7nNcm', '2025-05-29 04:08:28', '2025-06-02 23:48:30', 'الصف الثاني', 'uploads/avatars/user_2_1748897439.jpg'),
(3, 'طالب_نموذجي', 'student@example.com', '0123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-05-31 13:07:57', '2025-05-31 13:07:57', 'الصف الأول الثانوي', NULL),
(4, 'test', 'MostafaTa39690@gmail.com', '+201003504118', '$2y$10$Qqb4jNX/UiS932LccFqBQOCaRzEPC7dVP5Z8OIwmGTCkFbsBYQoem', '2025-06-01 14:19:34', '2025-06-01 14:19:49', 'الصف الثالث الثانوي', NULL),
(5, 'rootddd', 'mostafamta347@gmail.com', '+201003504113', '$2y$10$sVLk1yinj1Pv70ZDm.UIKeL0OPWrw0jH9JNkbmPy7K/xeIoW8a4EO', '2025-06-02 14:02:11', '2025-06-02 14:02:22', 'الصف الثالث الثانوي', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
