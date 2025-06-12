-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 11:28 AM
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
(3, 'mostafa', '$2y$10$jxxUQ0kdtxiwsNgdYOI14OYitRv4Y48IWV532flJcr6VueSW7bEDy', 'mostafa taha', 'MostafaTa39690@gmail.com', '01234567890', 'الجيزة العياط', '2025-06-11', 'male', '12121121212121', NULL, '2025-06-07 20:26:55', '2025-06-08 10:58:33', 1, 1),
(4, 'mostafa0', '$2y$10$/TwbaBfs2yeOK2MG/m4vf.CVHYupZ00mBEyX2bJNhw7FKYqsn5rVa', 'mostafa mtaha', 'hk303792mmmm@gmail.com', '01003504114', 'الجيزة العياط', '2025-07-08', 'male', '0000000000', NULL, '2025-06-08 11:34:14', NULL, 1, 0),
(5, 'mostafamtaha', '$2y$10$okOwiQM88agHdINnIcIymueXoHE74BYvRiI2tIhAMZenX2Ur4TVlK', 'mostafa mtaha', 'hk303794mmmm@gmail.com', '01003504111', 'الجيزة العياط', '2025-06-24', 'male', '0000000000', NULL, '2025-06-08 11:35:47', NULL, 1, 0),
(6, 'a', '$2y$10$lcDtFo6GSwnFqB3oFMig5eCaTy0L2TzIcjdNnrRGGxUjNaQ/stOrS', 'asdf hk303792w@&quot;', 'mostafamta347@gmail.com', '01148138347', 'الجيزة العياط', '2025-05-22', 'male', '0000000000', NULL, '2025-06-08 11:38:17', '2025-06-08 11:39:33', 1, 1),
(7, 'test', '$2y$10$Elni61wYg7oJHYt3rt7qfu5TSlJebRxsovj1k2G2QjGiidpqvYwsm', 't', 't@gmail.com', '01003504118', 'الجيزة العياط', '2025-04-24', 'male', '0000000000', 'uploads/profiles/68454e0f548a9.jpeg', '2025-06-08 11:47:11', NULL, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `lesson_name` varchar(255) NOT NULL,
  `question_type` enum('بنك اسئلة','اختبارات شاملة','تحدى نفسك','إمتحان الوزارة') NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `exam_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`id`, `user_id`, `subject`, `lesson_name`, `question_type`, `score`, `total_questions`, `exam_date`) VALUES
(67, 1, 'شسيب', 'شسيب', 'بنك اسئلة', 2, 1, '2025-06-04 11:43:47'),
(68, 1, 'شسيب', 'شسيب', 'بنك اسئلة', 0, 1, '2025-06-04 14:49:41'),
(69, 1, 'شسيب', 'شسيب', 'بنك اسئلة', 0, 1, '2025-06-04 20:40:37'),
(70, 1, 'شسيب', 'شسيب', 'بنك اسئلة', 0, 1, '2025-06-04 22:19:47'),
(71, 4, 'شسيب', 'شسيب', 'بنك اسئلة', 0, 1, '2025-06-04 23:27:35'),
(72, 4, 'شسيب', 'شسيب', 'بنك اسئلة', 1, 1, '2025-06-04 23:27:50'),
(73, 1, 'شسيب', 'شسيب', 'تحدى نفسك', 0, 1, '2025-06-05 12:30:44'),
(74, 1, 'd', 'dd', 'بنك اسئلة', 1, 1, '2025-06-05 18:08:08'),
(75, 1, 'dasdfsdfasdfasdfadsfdf', 'ddasdfsdfasdfasdfsadf', 'بنك اسئلة', 1, 1, '2025-06-05 18:28:27');

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
(20, 6, '::1', '2025-06-08 11:38:55', 1);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_image` varchar(255) DEFAULT NULL,
  `option1` text NOT NULL,
  `option2` text NOT NULL,
  `option3` text NOT NULL,
  `option4` text NOT NULL,
  `correct_answer` int(1) NOT NULL COMMENT '1-4',
  `subject` varchar(100) NOT NULL,
  `lesson_name` varchar(255) NOT NULL,
  `lesson` varchar(100) DEFAULT NULL,
  `question_type` enum('بنك اسئلة','اختبارات شاملة','تحدى نفسك','إمتحان الوزارة') NOT NULL,
  `note1` text DEFAULT NULL,
  `note2` text DEFAULT NULL,
  `note3` text DEFAULT NULL,
  `note4` text DEFAULT NULL,
  `added_date` datetime NOT NULL DEFAULT current_timestamp(),
  `added_by` int(11) DEFAULT NULL COMMENT 'ID of admin who added',
  `modified_date` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL COMMENT 'ID of admin who modified',
  `difficulty_level` enum('سهل','متوسط','صعب') DEFAULT 'متوسط'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `question_text`, `question_image`, `option1`, `option2`, `option3`, `option4`, `correct_answer`, `subject`, `lesson_name`, `lesson`, `question_type`, `note1`, `note2`, `note3`, `note4`, `added_date`, `added_by`, `modified_date`, `modified_by`, `difficulty_level`) VALUES
(238, 'asdf', 'uploads/questions/question_68415a02e8799.jpg', 'asdf', 'adfs', 'adfsas', 'asdfadfsd', 1, 'شسيب', 'شسيب', NULL, 'تحدى نفسك', 'asdf', 'asdfasdf', 'fasdfsadf', 'fwerwqer', '2025-06-05 11:49:06', 1, NULL, NULL, 'متوسط'),
(239, 'asdf', NULL, 'asdfasdf', 'asdfsdfasdf', 'adsfadsfdsaf', 'asdfadsfds', 1, 'd', 'dd', NULL, 'بنك اسئلة', 'asdfadfasf', 'asdfsdadfsdaf', 'asdfasdfsdaf', 'asdfdafd', '2025-06-05 12:16:48', 1, NULL, NULL, 'متوسط'),
(240, 'asdf', '', 'asdfasdf', 'asdfsdfasdf', 'adsfadsfdsaf', 'asdfadsfds', 1, 'dasdfsdfasdfasdfadsfdf', 'ddasdfsdfasdfasdfsadf', NULL, 'بنك اسئلة', 'asdfadfasf', 'asdfsdadfsdaf', 'asdfasdfsdaf', 'asdfdafd', '2025-06-05 18:28:05', 1, '2025-06-08 10:51:54', NULL, 'متوسط');

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

CREATE TABLE `student_answers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `exam_result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_answer` int(1) NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `answer_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_answers`
--

INSERT INTO `student_answers` (`id`, `user_id`, `exam_result_id`, `question_id`, `user_answer`, `is_correct`, `answer_date`) VALUES
(1, 1, 5, 4, 3, 0, '2025-05-31 18:00:52'),
(2, 1, 1, 5, 3, 0, '2025-05-31 18:00:52'),
(3, 1, 2, 6, 3, 0, '2025-05-31 18:00:52'),
(4, 1, 0, 4, 0, 0, '2025-05-31 18:01:06'),
(5, 1, 4, 5, 0, 0, '2025-05-31 18:01:06'),
(6, 1, 5, 6, 0, 0, '2025-05-31 18:01:06'),
(7, 1, 0, 4, 0, 0, '2025-05-31 18:01:22'),
(8, 1, 7, 5, 0, 0, '2025-05-31 18:01:22'),
(9, 1, 8, 6, 0, 0, '2025-05-31 18:01:23'),
(10, 1, 6, 6, 3, 0, '2025-05-31 18:01:39'),
(11, 1, 10, 5, 3, 0, '2025-05-31 18:01:39'),
(12, 1, 11, 4, 3, 0, '2025-05-31 18:01:39'),
(13, 1, 7, 6, 3, 0, '2025-05-31 18:01:45'),
(14, 1, 13, 4, 3, 0, '2025-05-31 18:01:45'),
(15, 1, 14, 5, 3, 0, '2025-05-31 18:01:45'),
(16, 1, 8, 10, 2, 0, '2025-05-31 18:14:22'),
(17, 1, 9, 10, 2, 0, '2025-05-31 18:14:43'),
(18, 1, 10, 6, 3, 0, '2025-05-31 18:19:03'),
(19, 1, 10, 5, 4, 0, '2025-05-31 18:19:03'),
(20, 1, 10, 4, 2, 0, '2025-05-31 18:19:03'),
(21, 1, 11, 6, 2, 0, '2025-05-31 18:21:15'),
(22, 1, 11, 5, 3, 0, '2025-05-31 18:21:15'),
(23, 1, 11, 4, 3, 0, '2025-05-31 18:21:15'),
(24, 1, 12, 16, 0, 0, '2025-05-31 18:32:38'),
(25, 1, 12, 36, 2, 1, '2025-05-31 18:32:38'),
(26, 1, 12, 46, 0, 0, '2025-05-31 18:32:39'),
(27, 1, 12, 56, 2, 1, '2025-05-31 18:32:39'),
(28, 1, 12, 26, 2, 1, '2025-05-31 18:32:39'),
(29, 1, 13, 65, 1, 1, '2025-05-31 18:36:46'),
(30, 1, 13, 62, 0, 0, '2025-05-31 18:36:46'),
(31, 1, 13, 64, 0, 0, '2025-05-31 18:36:46'),
(32, 1, 13, 63, 1, 1, '2025-05-31 18:36:46'),
(33, 1, 14, 73, 3, 1, '2025-05-31 18:40:48'),
(34, 1, 15, 101, 2, 1, '2025-05-31 18:47:15'),
(35, 2, 16, 104, 1, 1, '2025-05-31 19:01:16'),
(36, 2, 16, 98, 1, 1, '2025-05-31 19:01:16'),
(37, 2, 17, 100, 2, 1, '2025-05-31 19:03:43'),
(38, 2, 18, 100, 3, 0, '2025-05-31 19:06:51'),
(39, 2, 19, 149, 0, 0, '2025-05-31 19:38:14'),
(40, 2, 19, 133, 1, 0, '2025-05-31 19:38:14'),
(41, 2, 19, 158, 0, 0, '2025-05-31 19:38:14'),
(42, 2, 19, 172, 2, 1, '2025-05-31 19:38:14'),
(43, 2, 19, 129, 1, 1, '2025-05-31 19:38:14'),
(44, 2, 19, 143, 0, 0, '2025-05-31 19:38:14'),
(45, 2, 19, 162, 2, 1, '2025-05-31 19:38:14'),
(46, 2, 19, 178, 2, 1, '2025-05-31 19:38:15'),
(47, 2, 20, 195, 0, 0, '2025-05-31 19:42:08'),
(48, 2, 20, 190, 1, 0, '2025-05-31 19:42:08'),
(49, 2, 20, 197, 3, 1, '2025-05-31 19:42:08'),
(50, 2, 20, 211, 2, 1, '2025-05-31 19:42:08'),
(51, 1, 21, 196, 4, 1, '2025-05-31 19:44:20'),
(52, 1, 22, 190, 2, 1, '2025-05-31 19:51:10'),
(53, 1, 22, 197, 2, 0, '2025-05-31 19:51:10'),
(54, 1, 22, 195, 0, 0, '2025-05-31 19:51:10'),
(55, 1, 22, 211, 3, 0, '2025-05-31 19:51:10'),
(56, 1, 24, 211, 2, 1, '2025-05-31 21:09:26'),
(57, 1, 24, 195, 2, 0, '2025-05-31 21:09:26'),
(58, 1, 24, 190, 2, 1, '2025-05-31 21:09:26'),
(59, 1, 24, 197, 3, 1, '2025-05-31 21:09:26'),
(60, 1, 25, 203, 2, 1, '2025-05-31 21:13:07'),
(61, 1, 29, 191, 3, 0, '2025-05-31 21:18:14'),
(62, 1, 29, 201, 3, 0, '2025-05-31 21:18:14'),
(63, 1, 29, 187, 3, 0, '2025-05-31 21:18:14'),
(64, 1, 29, 207, 3, 0, '2025-05-31 21:18:14'),
(65, 1, 30, 187, 4, 0, '2025-05-31 21:24:04'),
(66, 1, 31, 187, 4, 0, '2025-05-31 21:24:21'),
(67, 1, 31, 201, 4, 0, '2025-05-31 21:24:21'),
(68, 1, 31, 207, 3, 0, '2025-05-31 21:24:21'),
(69, 1, 31, 191, 2, 1, '2025-05-31 21:24:21'),
(70, 1, 32, 191, 1, 0, '2025-05-31 21:25:38'),
(71, 1, 32, 207, 2, 1, '2025-05-31 21:25:38'),
(72, 1, 32, 187, 1, 1, '2025-05-31 21:25:38'),
(73, 1, 32, 201, 2, 1, '2025-05-31 21:25:38'),
(74, 1, 33, 207, 1, 0, '2025-05-31 21:37:44'),
(75, 1, 34, 207, 1, 0, '2025-05-31 21:39:21'),
(76, 1, 34, 191, 2, 1, '2025-05-31 21:39:21'),
(77, 1, 34, 187, 1, 1, '2025-05-31 21:39:21'),
(78, 1, 34, 201, 2, 1, '2025-05-31 21:39:21'),
(79, 1, 35, 187, 1, 1, '2025-05-31 21:41:59'),
(80, 1, 35, 191, 2, 1, '2025-05-31 21:41:59'),
(81, 1, 35, 201, 2, 1, '2025-05-31 21:41:59'),
(82, 1, 35, 207, 2, 1, '2025-05-31 21:42:00'),
(83, 1, 36, 191, 2, 1, '2025-05-31 21:43:32'),
(84, 1, 36, 201, 2, 1, '2025-05-31 21:43:32'),
(85, 1, 36, 207, 2, 1, '2025-05-31 21:43:32'),
(86, 1, 36, 187, 1, 1, '2025-05-31 21:43:32'),
(87, 1, 37, 214, 3, 0, '2025-05-31 21:46:06'),
(88, 1, 38, 214, 2, 0, '2025-05-31 21:47:05'),
(89, 1, 39, 195, 1, 0, '2025-05-31 21:47:38'),
(90, 1, 39, 197, 4, 0, '2025-05-31 21:47:38'),
(91, 1, 39, 190, 3, 0, '2025-05-31 21:47:38'),
(92, 1, 39, 211, 3, 0, '2025-05-31 21:47:39'),
(93, 1, 40, 215, 1, 1, '2025-05-31 21:48:02'),
(94, 1, 41, 186, 3, 0, '2025-05-31 21:49:02'),
(95, 1, 41, 193, 4, 0, '2025-05-31 21:49:02'),
(96, 1, 41, 209, 4, 0, '2025-05-31 21:49:02'),
(97, 1, 41, 202, 3, 1, '2025-05-31 21:49:02'),
(98, 1, 41, 199, 3, 0, '2025-05-31 21:49:02'),
(99, 1, 42, 216, 1, 1, '2025-05-31 21:49:58'),
(100, 1, 42, 211, 2, 1, '2025-05-31 21:49:59'),
(101, 1, 42, 190, 3, 0, '2025-05-31 21:49:59'),
(102, 1, 42, 195, 3, 1, '2025-05-31 21:49:59'),
(103, 1, 42, 197, 3, 1, '2025-05-31 21:49:59'),
(104, 1, 43, 190, 2, 1, '2025-05-31 21:58:16'),
(105, 1, 43, 197, 3, 1, '2025-05-31 21:58:16'),
(106, 1, 43, 195, 4, 0, '2025-05-31 21:58:16'),
(107, 1, 43, 211, 4, 0, '2025-05-31 21:58:16'),
(108, 1, 43, 216, 1, 1, '2025-05-31 21:58:16'),
(109, 1, 44, 202, 3, 1, '2025-05-31 22:02:48'),
(110, 1, 44, 209, 1, 0, '2025-05-31 22:02:48'),
(111, 1, 44, 193, 1, 1, '2025-05-31 22:02:48'),
(112, 1, 44, 186, 1, 1, '2025-05-31 22:02:48'),
(113, 1, 44, 199, 2, 1, '2025-05-31 22:02:48'),
(114, 1, 45, 210, 2, 0, '2025-05-31 23:14:21'),
(115, 1, 45, 200, 1, 1, '2025-05-31 23:14:21'),
(116, 1, 45, 212, 1, 0, '2025-05-31 23:14:21'),
(117, 1, 45, 188, 1, 0, '2025-05-31 23:14:22'),
(118, 1, 45, 198, 2, 0, '2025-05-31 23:14:22'),
(119, 1, 45, 185, 3, 0, '2025-05-31 23:14:22'),
(120, 1, 46, 188, 1, 0, '2025-06-01 07:37:47'),
(121, 1, 46, 200, 2, 0, '2025-06-01 07:37:47'),
(122, 1, 46, 198, 2, 0, '2025-06-01 07:37:47'),
(123, 1, 46, 210, 1, 1, '2025-06-01 07:37:47'),
(124, 1, 46, 185, 2, 0, '2025-06-01 07:37:47'),
(125, 1, 46, 212, 1, 0, '2025-06-01 07:37:47'),
(126, 1, 47, 203, 1, 0, '2025-06-01 07:52:45'),
(127, 1, 48, 215, 1, 1, '2025-06-01 07:57:09'),
(128, 1, 49, 197, 1, 0, '2025-06-01 09:08:17'),
(129, 1, 49, 190, 2, 1, '2025-06-01 09:08:17'),
(130, 1, 49, 195, 2, 0, '2025-06-01 09:08:17'),
(131, 1, 49, 216, 1, 1, '2025-06-01 09:08:17'),
(132, 1, 49, 211, 2, 1, '2025-06-01 09:08:17'),
(133, 1, 50, 211, 1, 0, '2025-06-01 11:08:51'),
(134, 1, 50, 197, 3, 1, '2025-06-01 11:08:51'),
(135, 1, 50, 195, 3, 1, '2025-06-01 11:08:51'),
(136, 1, 50, 190, 2, 1, '2025-06-01 11:08:51'),
(137, 1, 50, 216, 1, 1, '2025-06-01 11:08:51'),
(138, 1, 51, 214, 1, 1, '2025-06-01 11:13:06'),
(139, 1, 52, 216, 1, 1, '2025-06-01 11:13:51'),
(140, 1, 52, 195, 3, 1, '2025-06-01 11:13:51'),
(141, 1, 52, 211, 2, 1, '2025-06-01 11:13:51'),
(142, 1, 52, 190, 2, 1, '2025-06-01 11:13:51'),
(143, 1, 52, 197, 3, 1, '2025-06-01 11:13:51'),
(144, 1, 53, 192, 3, 1, '2025-06-01 11:21:45'),
(145, 1, 53, 208, 3, 1, '2025-06-01 11:21:45'),
(146, 1, 54, 189, 3, 0, '2025-06-01 11:28:35'),
(147, 1, 55, 192, 3, 1, '2025-06-01 11:29:13'),
(148, 1, 55, 208, 3, 1, '2025-06-01 11:29:13'),
(149, 1, 56, 196, 4, 1, '2025-06-01 11:30:16'),
(150, 2, 57, 197, 3, 1, '2025-06-01 11:32:25'),
(151, 2, 57, 216, 1, 1, '2025-06-01 11:32:25'),
(152, 2, 57, 211, 2, 1, '2025-06-01 11:32:25'),
(153, 2, 57, 195, 3, 1, '2025-06-01 11:32:25'),
(154, 2, 57, 190, 2, 1, '2025-06-01 11:32:25'),
(155, 2, 58, 190, 1, 0, '2025-06-01 11:38:46'),
(156, 2, 58, 211, 3, 0, '2025-06-01 11:38:46'),
(157, 2, 58, 197, 4, 0, '2025-06-01 11:38:46'),
(158, 2, 58, 216, 1, 1, '2025-06-01 11:38:46'),
(159, 2, 58, 195, 1, 0, '2025-06-01 11:38:46'),
(160, 1, 59, 217, 2, 1, '2025-06-01 20:36:24'),
(161, 1, 60, 221, 2, 0, '2025-06-02 12:49:09'),
(162, 7, 61, 214, 3, 0, '2025-06-03 01:14:57'),
(163, 1, 62, 191, 4, 0, '2025-06-03 14:12:31'),
(164, 1, 62, 187, 4, 0, '2025-06-03 14:12:31'),
(165, 1, 62, 201, 2, 1, '2025-06-03 14:12:31'),
(166, 1, 62, 207, 2, 1, '2025-06-03 14:12:31'),
(167, 1, 63, 185, 1, 1, '2025-06-04 02:11:31'),
(168, 1, 63, 200, 1, 1, '2025-06-04 02:11:31'),
(169, 1, 63, 188, 3, 1, '2025-06-04 02:11:31'),
(170, 1, 63, 198, 1, 1, '2025-06-04 02:11:31'),
(171, 1, 63, 210, 1, 1, '2025-06-04 02:11:31'),
(172, 1, 64, 215, 3, 0, '2025-06-04 02:49:38'),
(173, 1, 65, 197, 2, 0, '2025-06-04 02:50:16'),
(174, 1, 65, 216, 3, 0, '2025-06-04 02:50:16'),
(175, 1, 65, 195, 3, 1, '2025-06-04 02:50:16'),
(176, 1, 65, 190, 2, 1, '2025-06-04 02:50:16'),
(177, 1, 66, 197, 1, 0, '2025-06-04 02:57:09'),
(178, 1, 66, 216, 2, 0, '2025-06-04 02:57:09'),
(179, 1, 66, 190, 2, 1, '2025-06-04 02:57:09'),
(180, 1, 66, 195, 2, 0, '2025-06-04 02:57:09'),
(181, 1, 67, 237, 1, 0, '2025-06-04 11:43:47'),
(182, 1, 68, 237, 3, 0, '2025-06-04 14:49:41'),
(183, 1, 69, 237, 4, 0, '2025-06-04 20:40:37'),
(184, 1, 70, 237, 1, 0, '2025-06-04 22:19:47'),
(185, 4, 71, 237, 1, 0, '2025-06-04 23:27:35'),
(186, 4, 72, 237, 2, 1, '2025-06-04 23:27:50'),
(187, 1, 73, 238, 3, 0, '2025-06-05 12:30:44'),
(188, 1, 74, 239, 1, 1, '2025-06-05 18:08:08'),
(189, 1, 75, 240, 1, 1, '2025-06-05 18:28:27');

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
(1, 'mostafa', 'mostafamta347@gmail.com', '+201003504114', '$2y$10$jcdMU9Aa2srHivnxoV27QOs2/w6zP1LPl3x.YRbGbWesxX4aErJ1K', '2025-05-29 04:06:05', '2025-06-07 23:54:39', 'الصف الثالث الثانوي', 'uploads/avatars/user_1_1749036995.jpg', 0, 0, 0, 1, 1, 'a3928f69c758107d5ce92c48012d3d8f', '2025-07-04 23:15:05'),
(8, 'Kerols Hany', 'test@gmail.com', '+201111111112', '$2y$10$v7SDEolK2B4/0ziORCn.8ezPdSBEgqumjLmtQObq0io51T/DBKUc2', '2025-06-05 14:59:27', '0000-00-00 00:00:00', 'الصف الثالث الثانوي', NULL, 1, 1, 1, 1, 1, NULL, NULL);

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
(25, 7, '808cb690cae3b292', '01003504118', '2025-06-08 11:47:11', '2025-06-08 12:47:11', 0);

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
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `exam_result_id` (`exam_result_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `share_token` (`share_token`);

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
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `student_answers`
--
ALTER TABLE `student_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `login_attempts_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD CONSTRAINT `verification_codes_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
