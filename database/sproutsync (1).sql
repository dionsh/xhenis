-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2026 at 06:57 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `plants`
--

CREATE TABLE `plants` (
  `plant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `species_id` int(11) DEFAULT NULL,
  `nickname` varchar(120) DEFAULT NULL,
  `location` varchar(120) DEFAULT NULL,
  `planted_date` date DEFAULT NULL,
  `last_watered` datetime DEFAULT NULL,
  `status` enum('healthy','needs_water','overwatered','wilting','dead') DEFAULT 'healthy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'Basil', 'Ocimum basilicum', 'Aromatic culinary herb.', NULL, 200, 2, 40.00, 60.00, 18.00, 30.00, 'high', 60, '2026-05-22 16:07:19'),
(2, 'Tomato', 'Solanum lycopersicum', 'Popular fruiting vegetable.', NULL, 500, 2, 50.00, 70.00, 18.00, 27.00, 'full_sun', 90, '2026-05-22 16:07:19'),
(3, 'Sunflower', 'Helianthus annuus', 'Tall flowering plant.', NULL, 350, 3, 35.00, 55.00, 18.00, 33.00, 'full_sun', 100, '2026-05-22 16:07:19'),
(4, 'Aloe Vera', 'Aloe barbadensis miller', 'Succulent, low water needs.', NULL, 150, 14, 10.00, 30.00, 15.00, 27.00, 'medium', 730, '2026-05-22 16:07:19'),
(5, 'Rose', 'Rosa', 'Classic flowering shrub.', NULL, 400, 3, 40.00, 60.00, 15.00, 28.00, 'high', 120, '2026-05-22 16:07:19');

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
(1, 'xhenis', 'elezaj', 'xheniss', 'xheniselezi@gmail.com', '$2y$10$HQQMgqMXrjWRJsWC7JsNheo24K/JCzObLQj98E4GmTwSCDlQ841Am', '2026-05-22 16:12:55');

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
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plants`
--
ALTER TABLE `plants`
  MODIFY `plant_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plant_species`
--
ALTER TABLE `plant_species`
  MODIFY `species_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `scans`
--
ALTER TABLE `scans`
  MODIFY `scan_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
