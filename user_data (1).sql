-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2024 at 11:15 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `captured_data`
--

CREATE TABLE `captured_data` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `captured_data`
--

INSERT INTO `captured_data` (`id`, `user_id`, `name`, `email`, `phone`, `country`, `created_at`) VALUES
(6, 8, 'john mokri', 'odamokri@gmail.com', '09000099090', 'Nigeria', '2024-07-23 13:53:43'),
(7, 8, 'jane dello', 'o.oo@gmail.com', '090909090909', 'Bahrain', '2024-07-23 13:54:39'),
(8, 8, 'jane doe', 'janedoe@gmail.com', '09000000000', 'Ireland', '2024-07-23 14:02:24'),
(9, 3, 'may jonah', 'john@gmail.com', '0900900909', 'Algeria', '2024-07-23 14:07:03'),
(10, 3, 'Jonah ', 'hanson@gmail.com', '09065085570', 'Nigeria', '2024-07-23 15:31:06'),
(11, 3, 'Mayowa', 'o.o@gmail.com', '056868600666', 'Argentina', '2024-07-24 11:09:29'),
(12, 3, 'Mazwelll', 'odunoyemayowa@icloud.com', '09163255569', 'Belgium', '2024-07-24 11:09:54');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `kingschat_id` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `instagram_access_token` varchar(255) DEFAULT NULL,
  `twitter_oauth_token` varchar(255) DEFAULT NULL,
  `twitter_oauth_token_secret` varchar(255) DEFAULT NULL,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `country`, `password`, `reset_token`, `reset_expiry`, `kingschat_id`, `profile_picture`, `instagram_access_token`, `twitter_oauth_token`, `twitter_oauth_token_secret`, `points`) VALUES
(3, 'mayowa odunoye', 'odunoyemayowa@gmail.com', '09152818550', 'Nigeria', '$2y$10$alqrtKrL8Cuh.7qEDeuKkOjDNAnzUxmesgdlugvbTezwRAD0LQLfq', '3bf2450c5ddb247f8a6bc5351a155310aa44a028e54b8306976e1905c44ff6b090229c18271764c16f5fcdd0ee8875032599', '2024-07-23 18:01:55', NULL, 'uploads/IMG_2279.jpeg', NULL, NULL, NULL, 0),
(4, 'MaryJoy Utomi', 'utomimaryjoy@gmail.com', '09109109099', 'Nigeria', '$2y$10$.CFlAqYiVZovAwg/IVtfFeE5sCMMhIApE3Eqe1IF414diIUVPHYcu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(8, 'isaac John', 'isaacjan@gmail.com', '', 'Nigeria', '$2y$10$0y5Nmz5aHPrKF97QtCAWT.6A3UUhM.eosmMg6wKhmwwvTlbwM3PUm', NULL, NULL, NULL, 'uploads/pancakes.jpg', NULL, NULL, NULL, 0),
(9, 'John Chibuike Asogwa', 'johnbetho.c.088@gmail.com', '', 'Nigeria', '$2y$10$dvHQCPQ6bH8t.qPQJFYg/enC36rIY6hsMcqjqlhWilGc5gMk2vgYO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(10, 'Abimbola Ibukun Esther ', 'ibukunabimbola32@gmail.com', '', 'Nigeria', '$2y$10$86ETAX5/hNTDV8z.mBrhzeuO/ypzzBTWtQbp3D1wutuSQaX38eC8O', NULL, NULL, NULL, 'uploads/IMG_6756.jpeg', NULL, NULL, NULL, 0),
(11, 'maxwell johansen', 'aodunoye@gmail.com', '', 'Nigeria', '$2y$10$a0rVqA9Vb8uj/Gjfcml2q..nqgZErPJVjfwJCjyFpP/QBZfQwOpCi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(12, '', '', '', '', '$2y$10$yZzb9NmC8d9IyPf/Kgz7.u3AssysNcznmbccFJbBK6soGrDxbcyPC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(48, 'john', 'jay@gmail.com', '', 'nigeria', '$2y$10$8zrWc1R.sJZ7GJbKUGHAG.8f47UiuSYzkF9RPr6e5wkdZ2IZqQbQW', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(55, 'jane', 'jane@gmail.com', '', 'nigeria', '$2y$10$Gs3KU6415heN9PM8k0QT9.KKrGT4EJJEnHiyTbpAfRQeKNaF1rV5q', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(61, 'Jayden', 'Jayden@gmail.com', '', 'Nigeria', '$2y$10$BH4DyZSJEVhKxYAEmSUnoO3n0P1c04muuVa2SdMb9Ov3KhI4XT1rS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_media`
--

CREATE TABLE `user_media` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `media_type` varchar(10) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_media`
--

INSERT INTO `user_media` (`id`, `user_id`, `file_path`, `media_type`, `uploaded_at`) VALUES
(1, 3, 'uploads/pancakes.jpg', 'image', '2024-07-23 15:47:59'),
(2, 11, 'uploads/669fd2491531d_may.jpg', 'image', '2024-07-23 15:54:49'),
(3, 3, 'uploads/669fd3665bcba_image.jpg', 'image', '2024-07-23 15:59:34'),
(4, 3, 'uploads/669fd44e461fa_image.jpg', 'image', '2024-07-23 16:03:26'),
(5, 3, 'uploads/66a0ed3462b5c_image.jpg', 'image', '2024-07-24 12:01:56'),
(6, 3, 'uploads/66a0eea6a6c76_image.jpg', 'image', '2024-07-24 12:08:06'),
(7, 3, 'uploads/66a0ef4ec9e33_image.jpg', 'image', '2024-07-24 12:10:54'),
(8, 3, 'uploads/66a0ff510b72d_image.jpg', 'image', '2024-07-24 13:19:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `captured_data`
--
ALTER TABLE `captured_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `kingschat_id` (`kingschat_id`);

--
-- Indexes for table `user_media`
--
ALTER TABLE `user_media`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `captured_data`
--
ALTER TABLE `captured_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `user_media`
--
ALTER TABLE `user_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
