-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 02, 2024 at 01:56 PM
-- Server version: 8.0.40-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_capture`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_users`
--

CREATE TABLE `app_users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `role` enum('user','admin') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `profile_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token_expires` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_users`
--

INSERT INTO `app_users` (`id`, `username`, `email`, `password`, `full_name`, `created_at`, `updated_at`, `last_login`, `status`, `role`, `profile_image`, `reset_token`, `reset_token_expires`) VALUES
(2, 'mayo', 'o@mm.com', '$2y$10$tK9euQ997KFCq9TeN3po4u6q1CbwzNP1daq1A5vVcCa1wsXDSzzQi', NULL, '2024-11-30 22:14:55', '2024-12-02 13:06:50', '2024-12-02 13:06:50', 'active', 'user', NULL, NULL, NULL),
(5, 'Pedro', 'johnbetho.c.088@gmail.com', '$2y$10$IKqdmBqWyfBRyH8aEEOs0.yGx8ZOB9CFu6Ts7Ea63RNsjRCkrXIzS', 'John Pedro', '2024-12-02 01:09:40', '2024-12-02 08:26:54', '2024-12-02 08:23:56', 'active', 'user', NULL, NULL, NULL),
(6, 'dgo', 'dgo@lwpl.org', '$2y$10$aJs2m5YalT7FBiM4NPKeD.qOZGq2t5NDo/Q4NAOC7ySElePSfBH4i', NULL, '2024-12-02 05:14:47', '2024-12-02 10:36:24', '2024-12-02 10:36:24', 'active', 'user', NULL, NULL, NULL),
(7, 'highzic', 'highzictemitope@gmail.com', '$2y$10$VElHNYoZvmE27csrWVl39OszHVPb.aKbgvje6v4Q10PmjnsYN0R2a', NULL, '2024-12-02 07:36:53', '2024-12-02 07:37:27', '2024-12-02 07:37:27', 'active', 'user', NULL, NULL, NULL),
(8, 'Jay', 'johnpedro.c.243233@gmail.com', '$2y$10$xsVwKYMq9tRvwJHo.f7kjuU.zeJkQJ.9b/KhphaQDL3owWWO40sNu', NULL, '2024-12-02 08:22:14', '2024-12-02 08:22:14', NULL, 'active', 'user', NULL, NULL, NULL),
(9, 'Oyin', 'o@gm.com', '$2y$10$Ak.VvOHzO6x9fpLwUoZ6ieuRg1oM3HYMkEHyVQzHTsJ0fVnuwXOd2', NULL, '2024-12-02 08:34:33', '2024-12-02 08:35:08', '2024-12-02 08:35:08', 'active', 'user', NULL, NULL, NULL),
(10, 'DesignbyMo', 'ibukunabimbola32@gmail.com', '$2y$10$KsnUrnfO8HCjynohdGpzx.byhx/BlBY2BluTa372UI/sUaoXbwwI2', NULL, '2024-12-02 08:57:37', '2024-12-02 09:03:23', '2024-12-02 09:03:23', 'active', 'user', NULL, NULL, NULL),
(11, 'mayor', 'mayor@m.com', '$2y$10$mqMFhBrkhDdwQYPQLnTHWeIk43oZforVabyy2XGb8XIN.lUJe.NJe', NULL, '2024-12-02 09:05:17', '2024-12-02 09:05:30', '2024-12-02 09:05:30', 'active', 'user', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `captured_data`
--

CREATE TABLE `captured_data` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `captured_data`
--

INSERT INTO `captured_data` (`id`, `user_id`, `name`, `email`, `phone`, `country`, `created_at`) VALUES
(1, 2, 'Odunoye mayowa', 'odunoyemayowa@gmail.com', '09152818550', 'Nigeria', '2024-11-23 08:40:17'),
(2, 2, 'Mo', 'Mo@mm.com', '9999999999', 'Niger', '2024-11-23 08:48:08'),
(4, 2, 'adeola o', 'aod@gm.com', '09152818555', 'Nigeria', '2024-11-23 08:59:05');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Penetrating with Languages', '2024-11-30 10:49:08'),
(2, 'Say Yes to Kids', '2024-11-30 10:49:08'),
(3, 'No One Left Behind', '2024-11-30 10:49:08'),
(4, 'Teens Teevolution', '2024-11-30 10:49:08'),
(5, 'Youths Aglow', '2024-11-30 10:49:08'),
(6, 'Every Minister An Outreach', '2024-11-30 10:49:08'),
(7, 'Digital', '2024-11-30 10:49:08'),
(8, 'Dignitaries Distribution', '2024-11-30 10:49:08'),
(9, 'Strategic Distributions', '2024-11-30 10:49:08');

-- --------------------------------------------------------

--
-- Table structure for table `data_capture`
--

CREATE TABLE `data_capture` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_capture`
--

INSERT INTO `data_capture` (`id`, `user_id`, `name`, `email`, `phone`, `country`, `created_at`) VALUES
(1, 2, 'oyin', 'o@mm.com', '9989898999', 'Nigeria', '2024-11-23 08:44:16');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `thumbnail_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_type` enum('video') COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `category` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tags` text COLLATE utf8mb4_general_ci,
  `upload_date` datetime NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `user_id`, `file_path`, `thumbnail_path`, `file_type`, `description`, `category`, `tags`, `upload_date`, `last_modified`) VALUES
(4, 2, 'media_674af4b2b4e31_1732965554.webm', NULL, 'video', 'vev', NULL, '[36]', '2024-11-30 12:19:14', '2024-11-30 11:19:14'),
(5, 2, 'media_674af4f1cf2cb_1732965617.webm', NULL, 'video', 'fe', NULL, '[36]', '2024-11-30 12:20:17', '2024-11-30 11:20:17');

-- --------------------------------------------------------

--
-- Table structure for table `media_categories`
--

CREATE TABLE `media_categories` (
  `media_id` int NOT NULL,
  `category_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_tags`
--

CREATE TABLE `media_tags` (
  `media_id` int NOT NULL,
  `tag_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_tags`
--

INSERT INTO `media_tags` (`media_id`, `tag_id`, `created_at`) VALUES
(4, 36, '2024-11-30 11:19:14'),
(5, 36, '2024-11-30 11:20:17');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `category`, `created_at`) VALUES
(1, 'Penetrating with Languages', 'Campaigns', '2024-11-30 11:06:45'),
(2, 'Say Yes to Kids', 'Campaigns', '2024-11-30 11:06:45'),
(3, 'No One Left Behind', 'Campaigns', '2024-11-30 11:06:45'),
(4, 'Teens Teevolution', 'Campaigns', '2024-11-30 11:06:45'),
(5, 'Youths Aglow', 'Campaigns', '2024-11-30 11:06:45'),
(6, 'Every Minister An Outreach', 'Campaigns', '2024-11-30 11:06:45'),
(7, 'Digital', 'Campaigns', '2024-11-30 11:06:45'),
(8, 'Dignitaries Distribution', 'Campaigns', '2024-11-30 11:06:45'),
(9, 'Strategic Distributions', 'Campaigns', '2024-11-30 11:06:45'),
(10, 'Planes', 'Vehicles', '2024-11-30 11:06:45'),
(11, 'Buses', 'Vehicles', '2024-11-30 11:06:45'),
(12, 'Trains', 'Vehicles', '2024-11-30 11:06:45'),
(13, 'Airports', 'Transport Terminals', '2024-11-30 11:06:45'),
(14, 'Bus Terminals', 'Transport Terminals', '2024-11-30 11:06:45'),
(15, 'Train Terminals', 'Transport Terminals', '2024-11-30 11:06:45'),
(16, 'Apartment Blocks', 'Homes', '2024-11-30 11:06:45'),
(17, 'Detached Houses', 'Homes', '2024-11-30 11:06:45'),
(18, 'Residential Estates', 'Homes', '2024-11-30 11:06:45'),
(19, 'Neighbourhoods', 'Homes', '2024-11-30 11:06:45'),
(20, 'The inner cities', 'Communities', '2024-11-30 11:06:45'),
(21, 'The Hinterlands', 'Communities', '2024-11-30 11:06:45'),
(22, 'Communities in crisis', 'Communities', '2024-11-30 11:06:45'),
(23, 'Schools', 'Institutions', '2024-11-30 11:06:45'),
(24, 'Universities', 'Institutions', '2024-11-30 11:06:45'),
(25, 'Colleges', 'Institutions', '2024-11-30 11:06:45'),
(26, 'Military Bases', 'Institutions', '2024-11-30 11:06:45'),
(27, 'Police Stations', 'Institutions', '2024-11-30 11:06:45'),
(28, 'Prisons', 'Institutions', '2024-11-30 11:06:45'),
(29, 'Hospitals', 'Institutions', '2024-11-30 11:06:45'),
(30, 'Government Offices', 'Institutions', '2024-11-30 11:06:45'),
(31, 'Shopping Malls', 'Public Places', '2024-11-30 11:06:45'),
(32, 'Markets', 'Public Places', '2024-11-30 11:06:45'),
(33, 'Parks', 'Public Places', '2024-11-30 11:06:45'),
(34, 'Beaches', 'Public Places', '2024-11-30 11:06:45'),
(35, 'Tourist Sites', 'Public Places', '2024-11-30 11:06:45'),
(36, 'Sports Venues', 'Public Places', '2024-11-30 11:06:45'),
(37, 'Entertainment Centers', 'Public Places', '2024-11-30 11:06:45'),
(38, 'Churches', 'Religious Places', '2024-11-30 11:06:45'),
(39, 'Mosques', 'Religious Places', '2024-11-30 11:06:45'),
(40, 'Temples', 'Religious Places', '2024-11-30 11:06:45'),
(41, 'Synagogues', 'Religious Places', '2024-11-30 11:06:45'),
(42, 'Office Complexes', 'Business Places', '2024-11-30 11:06:45'),
(43, 'Industrial Areas', 'Business Places', '2024-11-30 11:06:45'),
(44, 'Business Districts', 'Business Places', '2024-11-30 11:06:45'),
(45, 'Commercial Centers', 'Business Places', '2024-11-30 11:06:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `kingschat_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `instagram_access_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `twitter_oauth_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `twitter_oauth_token_secret` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `points` int DEFAULT '0',
  `kingschat_access_token` text COLLATE utf8mb4_general_ci,
  `kingschat_refresh_token` text COLLATE utf8mb4_general_ci,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `country`, `password`, `reset_token`, `reset_expiry`, `kingschat_id`, `profile_picture`, `instagram_access_token`, `twitter_oauth_token`, `twitter_oauth_token_secret`, `points`, `kingschat_access_token`, `kingschat_refresh_token`, `token_expiry`) VALUES
(1, 'Admin User', 'admin@example.com', NULL, 'Nigeria', '$2y$10$YourHashedPasswordHere', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(2, 'may', 'odunoyemayowa@gmail.com', '', 'Nigeria', '$2y$10$Q9tI3GpW7bYlqSPIRBGrveYUwg/GQuYbv5hou2hmdA5JwTLYkk0cu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(3, '', '', '', '', '$2y$10$.PeAWhhtCgXuWAyjQgLTMOuKnyHpDTn2OztTCvDGRb6TcG8n/J2Q.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_media`
--

CREATE TABLE `user_media` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `media_type` enum('photo','video') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tags` text COLLATE utf8mb4_general_ci,
  `video_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_users`
--
ALTER TABLE `app_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indexes for table `captured_data`
--
ALTER TABLE `captured_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unique_category_name` (`name`);

--
-- Indexes for table `data_capture`
--
ALTER TABLE `data_capture`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `media_categories`
--
ALTER TABLE `media_categories`
  ADD PRIMARY KEY (`media_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `media_tags`
--
ALTER TABLE `media_tags`
  ADD PRIMARY KEY (`media_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `kingschat_id` (`kingschat_id`),
  ADD KEY `idx_kingschat_id` (`kingschat_id`);

--
-- Indexes for table `user_media`
--
ALTER TABLE `user_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_users`
--
ALTER TABLE `app_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `captured_data`
--
ALTER TABLE `captured_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `data_capture`
--
ALTER TABLE `data_capture`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_media`
--
ALTER TABLE `user_media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `captured_data`
--
ALTER TABLE `captured_data`
  ADD CONSTRAINT `captured_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `data_capture`
--
ALTER TABLE `data_capture`
  ADD CONSTRAINT `data_capture_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_categories`
--
ALTER TABLE `media_categories`
  ADD CONSTRAINT `media_categories_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_tags`
--
ALTER TABLE `media_tags`
  ADD CONSTRAINT `media_tags_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_media`
--
ALTER TABLE `user_media`
  ADD CONSTRAINT `user_media_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
