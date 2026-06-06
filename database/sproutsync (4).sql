-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2026 at 02:22 PM
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
-- Database: `sproutsync`
--

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `type` enum('watered','needs_water','low_battery','sensor_offline','co2_alert','general') DEFAULT 'general',
  `title` varchar(160) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `plant_id`, `type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 15, 3, 'general', 'New plant added', 'Aloe Vera was added to your garden.', 0, '2026-06-06 12:08:26'),
(2, 15, 4, 'general', 'New plant added', 'Sweet basil was added to your garden.', 0, '2026-06-06 12:10:51');

-- --------------------------------------------------------

--
-- Table structure for table `plants`
--

CREATE TABLE `plants` (
  `plant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `species_id` int(11) DEFAULT NULL,
  `nickname` varchar(120) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `location` varchar(120) DEFAULT NULL,
  `planted_date` date DEFAULT NULL,
  `last_watered` datetime DEFAULT NULL,
  `status` enum('healthy','needs_water','overwatered','wilting','dead') DEFAULT 'healthy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plants`
--

INSERT INTO `plants` (`plant_id`, `user_id`, `species_id`, `nickname`, `image_url`, `location`, `planted_date`, `last_watered`, `status`, `created_at`) VALUES
(1, 1, 1, 'Kitchen Desk Basil', NULL, 'Kitchen Windowsill', '2026-05-01', '2026-05-22 10:00:00', 'needs_water', '2026-05-22 16:15:00'),
(2, 1, 4, 'Spiky Companion', NULL, 'Living Room Balcony', '2026-02-10', '2026-05-18 14:20:00', 'healthy', '2026-05-22 16:20:00'),
(3, 15, 4, 'Aloe Vera', 'assets/plants/uploads/plant_15_1780747706_94b060.jpg', 'Indoor', '2026-06-06', NULL, 'healthy', '2026-06-06 12:08:26'),
(4, 15, 1, 'Sweet basil', 'assets/plants/uploads/plant_15_1780747851_d4daca.jpg', 'Indoor', '2026-06-06', NULL, 'healthy', '2026-06-06 12:10:51');

-- --------------------------------------------------------

--
-- Table structure for table `plant_species`
--

CREATE TABLE `plant_species` (
  `species_id` int(11) NOT NULL,
  `common_name` varchar(120) NOT NULL,
  `scientific_name` varchar(160) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `water_amount_ml` int(11) DEFAULT NULL,
  `water_frequency_days` int(11) DEFAULT NULL,
  `ideal_moisture_min` decimal(5,2) DEFAULT NULL,
  `ideal_moisture_max` decimal(5,2) DEFAULT NULL,
  `ideal_temp_min` decimal(5,2) DEFAULT NULL,
  `ideal_temp_max` decimal(5,2) DEFAULT NULL,
  `sunlight_level` enum('low','medium','high','full_sun') DEFAULT 'medium',
  `growth_time_days` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plant_species`
--

INSERT INTO `plant_species` (`species_id`, `common_name`, `scientific_name`, `description`, `image_url`, `water_amount_ml`, `water_frequency_days`, `ideal_moisture_min`, `ideal_moisture_max`, `ideal_temp_min`, `ideal_temp_max`, `sunlight_level`, `growth_time_days`, `created_at`) VALUES
(1, 'Basil', 'Ocimum basilicum', 'Aromatic culinary herb.', 'assets/plants/basil.jpg', 200, 2, 40.00, 60.00, 18.00, 30.00, 'high', 60, '2026-05-22 16:07:19'),
(2, 'Tomato', 'Solanum lycopersicum', 'Popular fruiting vegetable.', 'assets/plants/tomato.jpg', 500, 2, 50.00, 70.00, 18.00, 27.00, 'full_sun', 90, '2026-05-22 16:07:19'),
(3, 'Sunflower', 'Helianthus annuus', 'Tall flowering plant.', 'assets/plants/sunflower.jpg', 350, 3, 35.00, 55.00, 18.00, 33.00, 'full_sun', 100, '2026-05-22 16:07:19'),
(4, 'Aloe Vera', 'Aloe barbadensis miller', 'Succulent, low water needs.', 'assets/plants/aloe-vera.jpg', 150, 14, 10.00, 30.00, 15.00, 27.00, 'medium', 730, '2026-05-22 16:07:19'),
(5, 'Rose', 'Rosa', 'Classic flowering shrub.', 'assets/plants/rose.jpg', 400, 3, 40.00, 60.00, 15.00, 28.00, 'high', 120, '2026-05-22 16:07:19');

-- --------------------------------------------------------

--
-- Table structure for table `scans`
--

CREATE TABLE `scans` (
  `scan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `identified_species` int(11) DEFAULT NULL,
  `scan_type` enum('identify','health_check') DEFAULT 'identify',
  `image_url` varchar(255) DEFAULT NULL,
  `result_name` varchar(160) DEFAULT NULL,
  `confidence` decimal(5,2) DEFAULT NULL,
  `detected_moisture` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scans`
--

INSERT INTO `scans` (`scan_id`, `user_id`, `identified_species`, `scan_type`, `image_url`, `result_name`, `confidence`, `detected_moisture`, `notes`, `scanned_at`) VALUES
(1, 1, NULL, 'identify', NULL, 'Rose of Sharon', 0.86, NULL, 'Source: Pl@ntNet. Best scientific match: Hibiscus syriacus', '2026-05-23 10:01:03'),
(2, 1, NULL, 'identify', NULL, 'Rose of Sharon', 0.73, NULL, 'Source: Pl@ntNet. Best scientific match: Hibiscus syriacus', '2026-05-23 10:01:20'),
(3, 1, NULL, 'identify', NULL, 'Song-of-India', 0.54, NULL, 'Source: Pl@ntNet. Best scientific match: Dracaena reflexa', '2026-05-23 10:26:17'),
(4, 1, NULL, 'identify', NULL, 'Utricularia limosa', 0.00, NULL, 'Source: Pl@ntNet. Best scientific match: Utricularia limosa', '2026-05-23 12:27:12'),
(5, 1, NULL, 'identify', NULL, 'Cantaloupe', 0.00, NULL, 'Source: Pl@ntNet. Best scientific match: Cucumis melo', '2026-05-23 12:27:16'),
(6, 1, NULL, 'identify', NULL, 'Song-of-India', 0.88, NULL, 'Source: Pl@ntNet. Best scientific match: Dracaena reflexa', '2026-05-23 12:27:34'),
(7, 1, NULL, 'identify', NULL, 'Song-of-India', 0.69, NULL, 'Source: Pl@ntNet. Best scientific match: Dracaena reflexa', '2026-05-23 12:28:28'),
(8, 1, NULL, 'identify', NULL, 'Song-of-India', 0.29, NULL, 'Source: Pl@ntNet. Best scientific match: Dracaena reflexa', '2026-05-23 12:30:34'),
(9, 1, NULL, 'identify', NULL, 'Song-of-India', 0.13, NULL, 'Source: Pl@ntNet. Best scientific match: Dracaena reflexa', '2026-05-23 12:30:42'),
(10, 1, 5, 'identify', NULL, 'Bengal rose', 0.34, NULL, 'Source: Pl@ntNet. Best scientific match: Rosa chinensis', '2026-05-23 13:31:39'),
(11, 1, NULL, 'identify', NULL, 'Albaida', 0.04, NULL, 'Source: Pl@ntNet. Best scientific match: Anthyllis cytisoides', '2026-05-23 17:01:19'),
(12, 1, 4, 'identify', NULL, 'Aloe vera', 0.61, NULL, 'Source: Pl@ntNet. Best scientific match: Aloe vera', '2026-05-23 17:01:34'),
(13, 1, NULL, 'identify', NULL, 'Didier\'s tulip', 0.25, NULL, 'Source: Pl@ntNet. Best scientific match: Tulipa gesneriana', '2026-05-23 17:02:11'),
(14, 1, NULL, 'identify', NULL, 'Ivy geranium', 0.77, NULL, 'Source: Pl@ntNet. Best scientific match: Pelargonium peltatum', '2026-05-24 13:46:39'),
(15, 1, NULL, 'identify', NULL, 'Peace lily', 0.21, NULL, 'Source: Pl@ntNet. Best scientific match: Spathiphyllum wallisii', '2026-05-24 16:32:24'),
(16, 1, NULL, 'identify', NULL, 'Spatheflower', 0.21, NULL, 'Source: Pl@ntNet. Best scientific match: Spathiphyllum cannifolium', '2026-05-24 16:32:33'),
(17, 1, NULL, 'identify', NULL, 'Peace lily', 0.36, NULL, 'Source: Pl@ntNet. Best scientific match: Spathiphyllum wallisii', '2026-05-24 16:35:30'),
(18, 1, NULL, 'identify', NULL, 'Christmas Cactus', 0.86, NULL, 'Source: Pl@ntNet. Best scientific match: Schlumbergera russelliana', '2026-05-24 16:35:40'),
(19, 1, 4, 'identify', NULL, 'Aloe Vera', 0.77, NULL, 'Source: Pl@ntNet. Best scientific match: Aloe officinalis', '2026-05-25 08:13:05'),
(20, 1, 2, 'identify', NULL, 'Garden tomato', 0.35, NULL, 'Source: Pl@ntNet. Best scientific match: Solanum lycopersicum', '2026-05-25 08:13:31'),
(21, 1, 2, 'identify', NULL, 'Garden tomato', 0.40, NULL, 'Source: Pl@ntNet. Best scientific match: Solanum lycopersicum', '2026-05-25 08:19:38'),
(22, 1, 4, 'identify', NULL, 'Aloe Vera', 0.68, NULL, 'Source: Pl@ntNet. Best scientific match: Aloe officinalis', '2026-05-25 08:20:00'),
(23, 1, 4, 'identify', NULL, 'Aloe Vera', 0.75, NULL, 'Source: Pl@ntNet. Best scientific match: Aloe officinalis', '2026-05-25 08:27:52'),
(24, 1, 4, 'identify', NULL, 'Aloe Vera', 0.80, NULL, 'Source: Pl@ntNet. Best scientific match: Aloe officinalis', '2026-05-25 08:28:01'),
(25, 1, 4, 'identify', NULL, 'Aloe Vera', 0.64, NULL, 'Source: Pl@ntNet. Best scientific match: Aloe officinalis', '2026-05-25 12:30:59'),
(26, 1, NULL, 'identify', NULL, 'Bluestem yucca', 0.52, NULL, 'Source: Pl@ntNet. Best scientific match: Yucca gigantea', '2026-05-25 13:30:59'),
(27, 1, NULL, 'identify', NULL, 'Rubberplant', 0.84, NULL, 'Source: Pl@ntNet. Best scientific match: Ficus elastica', '2026-05-25 14:13:22'),
(28, 1, NULL, 'identify', NULL, 'Rubberplant', 0.85, NULL, 'Source: Pl@ntNet. Best scientific match: Ficus elastica', '2026-05-25 14:13:57'),
(29, 1, NULL, 'identify', NULL, 'Rubberplant', 0.87, NULL, 'Source: Pl@ntNet. Best scientific match: Ficus elastica', '2026-05-25 14:14:04'),
(30, 1, NULL, 'identify', NULL, 'Hybrid Pelargonium', 0.85, NULL, 'Source: Pl@ntNet. Best scientific match: Pelargonium Ã— hybridum', '2026-05-25 14:15:43'),
(31, 1, NULL, 'identify', NULL, 'Nandina', 0.84, NULL, 'Source: Pl@ntNet. Best scientific match: Nandina domestica', '2026-05-25 16:25:11'),
(32, 1, NULL, 'identify', NULL, 'Butterfly Gaura', 0.06, NULL, 'Source: Pl@ntNet. Best scientific match: Gaura lindheimeri', '2026-05-25 16:25:50'),
(33, 1, NULL, 'identify', NULL, 'Japanese Box', 0.78, NULL, 'Source: Pl@ntNet. Best scientific match: Buxus microphylla', '2026-05-25 17:56:39'),
(34, 1, NULL, 'identify', NULL, 'Hybrid Pelargonium', 0.55, NULL, 'Source: Pl@ntNet. Best scientific match: Pelargonium Ã— hybridum', '2026-05-27 08:10:55'),
(35, 1, NULL, 'identify', NULL, 'Black Velvet Petunia', 0.58, NULL, 'Source: Pl@ntNet. Best scientific match: Petunia spp.', '2026-05-27 08:11:11'),
(36, 15, 4, 'identify', NULL, 'Aloe Vera', 0.88, NULL, 'Source: Pl@ntNet. Best scientific match: Aloe officinalis', '2026-06-06 12:08:21'),
(37, 15, 1, 'identify', NULL, 'Sweet basil', 0.82, NULL, 'Source: Pl@ntNet. Best scientific match: Ocimum basilicum', '2026-06-06 12:08:41');

-- --------------------------------------------------------

--
-- Table structure for table `sensors`
--

CREATE TABLE `sensors` (
  `sensor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `device_code` varchar(80) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `status` enum('active','inactive','offline') DEFAULT 'active',
  `battery_level` int(11) DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensor_readings`
--

CREATE TABLE `sensor_readings` (
  `reading_id` bigint(20) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  `moisture` decimal(5,2) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `co2_level` decimal(7,2) DEFAULT NULL,
  `humidity` decimal(5,2) DEFAULT NULL,
  `light_level` decimal(7,2) DEFAULT NULL,
  `ph_level` decimal(4,2) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `surname` varchar(60) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `surname`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'xhenis', 'elezaj', 'xheniss', 'xheniselezi@gmail.com', '$2y$10$HQQMgqMXrjWRJsWC7JsNheo24K/JCzObLQj98E4GmTwSCDlQ841Am', '2026-05-22 16:12:55'),
(2, 'Enid', 'Enid', 'Enid', 'Enid@gmail.com', '$2y$10$KSe8TIHgOwvTblwX6iaP6u/nmeyH8gv/LCVCQnHW7wMe38GOgshri', '2026-05-23 09:58:32'),
(3, 'tenor', 'sopa', 'tenorii', 'tenor.sopa@gmail.com', '$2y$10$FwgL1qg.Muj1LvtQyQ1Fj.JPH93XT9d5JNvSW1bg0UfGlbCSXnQou', '2026-05-23 12:26:15'),
(4, 'Donesa', 'Demiri', 'Donesa', 'demiridonesaa@gnail.com', '$2y$10$qqP17caJkDuYH0Jfq.U/quTJXb6uWIC3/vh9jXKgtA2nPHMBEj83C', '2026-05-23 12:26:26'),
(5, 'Testu', 'Testi', 'Tester', 't@gmail.com', '$2y$10$bXWueC3Pb0X8hG3invhkXuGjt18LvFEPRnZ2vXv9bJpRH3i3PPNnq', '2026-05-23 12:26:39'),
(6, 'Dalina', 'Shabanhaxhaj ', 'Dalina', 'dalinashabanhaxhaj4@gmail.com', '$2y$10$sHrh7vjpgGyrMPb/Btpe9O41I4slN597TXZlM0XvaZQpRSm/ZeqLG', '2026-05-23 12:26:41'),
(7, 'Edi', 'Haziri', 'Edi', 'edihaziri9@gmail.com', '$2y$10$XPvUWh3Q9iv234OYpl2lLeMNqDveNyD28Z.0R5Jek5C2xBb/cj7ga', '2026-05-23 12:29:05'),
(8, 'Edonis Bislimi', 'Bislimi', 'edonisbislimi12@gmail.com', 'edonisbislimi12@gmail.com', '$2y$10$QHVbEy1c8K0Q0UAm7fuAIumP/.ZxIA0RLXhrs.7utEAvCXi4QbBWK', '2026-05-23 12:29:09'),
(9, 'Itp', 'Prizren', 'Itp', 'itp@gmail.com', '$2y$10$N8V80USuYFuBwMQC0g2tB.rf8c5qGWRSHQ0hLyYHshbPsVjdKF/W.', '2026-05-23 13:34:25'),
(10, 'Erbar', 'Tahiri', 'erbar', 'alterbar11@gmail.com', '$2y$10$amJQpUdpKCA7d2Z0JPBiauUANKgi.KbxuREaeuh1Ri5C/LBiDPRKS', '2026-05-24 16:30:58'),
(11, 'Alben', 'Zeqiri', 'Albenzeqirii', 'albenzeqiri85@gmail.com', '$2y$10$Ud47sl/Q4tS0pCK9DfQI4.yD5PF8lIpXC7mYWSbEf2Lgm9Ca7nEMy', '2026-05-25 13:30:33'),
(12, 'Eliza', 'Braha', 'eliza', 'eliza@gmail.com', '$2y$10$bOQ.w4UsKJNQMILDTghfJOrXRHqEmgV0vkZUnaS2WZwNG.rSb5xhm', '2026-05-25 14:11:38'),
(13, 'Arti', 'Ramadani', 'arti', 'a@gmail.com', '$2y$10$p2ryhDYdVC9LoP3DGBme2uMrmah7pyQE7eDs9duxI.aZgNVGePQaO', '2026-06-01 14:42:57'),
(14, 'Korab', 'Aliu', 'Rabi', 'korabaliu25@gmail.com', '$2y$10$msbhtznjK9gFCxMumKpoSOuwEqhWlWirrD5.6Fh6e0k1L43ajuifu', '2026-06-02 15:08:14'),
(15, 'Arleon', 'Qerimi', 'arlo', 'arlindaosmani00@gmail.com', '$2y$10$7wk8haTwv26yLY.2gRNmD.Pu3cKWPBbtOpgU7fv3BMQr1rZ3KA.De', '2026-06-06 10:08:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plant_id` (`plant_id`);

--
-- Indexes for table `plants`
--
ALTER TABLE `plants`
  ADD PRIMARY KEY (`plant_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `species_id` (`species_id`);

--
-- Indexes for table `plant_species`
--
ALTER TABLE `plant_species`
  ADD PRIMARY KEY (`species_id`);

--
-- Indexes for table `scans`
--
ALTER TABLE `scans`
  ADD PRIMARY KEY (`scan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `identified_species` (`identified_species`);

--
-- Indexes for table `sensors`
--
ALTER TABLE `sensors`
  ADD PRIMARY KEY (`sensor_id`),
  ADD UNIQUE KEY `device_code` (`device_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plant_id` (`plant_id`);

--
-- Indexes for table `sensor_readings`
--
ALTER TABLE `sensor_readings`
  ADD PRIMARY KEY (`reading_id`),
  ADD KEY `idx_sensor_time` (`sensor_id`,`recorded_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plants`
--
ALTER TABLE `plants`
  MODIFY `plant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `plant_species`
--
ALTER TABLE `plant_species`
  MODIFY `species_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `scans`
--
ALTER TABLE `scans`
  MODIFY `scan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `sensors`
--
ALTER TABLE `sensors`
  MODIFY `sensor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sensor_readings`
--
ALTER TABLE `sensor_readings`
  MODIFY `reading_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`plant_id`) ON DELETE CASCADE;

--
-- Constraints for table `plants`
--
ALTER TABLE `plants`
  ADD CONSTRAINT `plants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plants_ibfk_2` FOREIGN KEY (`species_id`) REFERENCES `plant_species` (`species_id`) ON DELETE SET NULL;

--
-- Constraints for table `scans`
--
ALTER TABLE `scans`
  ADD CONSTRAINT `scans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scans_ibfk_2` FOREIGN KEY (`identified_species`) REFERENCES `plant_species` (`species_id`) ON DELETE SET NULL;

--
-- Constraints for table `sensors`
--
ALTER TABLE `sensors`
  ADD CONSTRAINT `sensors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sensors_ibfk_2` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`plant_id`) ON DELETE SET NULL;

--
-- Constraints for table `sensor_readings`
--
ALTER TABLE `sensor_readings`
  ADD CONSTRAINT `sensor_readings_ibfk_1` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`sensor_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
