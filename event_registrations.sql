-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2026 at 08:50 AM
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
-- Database: `event_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `registration_id` bigint(20) NOT NULL,
  `participant_id` bigint(20) DEFAULT NULL,
  `event_id` bigint(20) DEFAULT NULL,
  `organization` varchar(150) DEFAULT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `tssia_membership_id` varchar(100) DEFAULT NULL,
  `registration_status_id` bigint(20) DEFAULT NULL,
  `attendance_status_id` bigint(20) DEFAULT NULL,
  `registered_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`registration_id`, `participant_id`, `event_id`, `organization`, `designation`, `tssia_membership_id`, `registration_status_id`, `attendance_status_id`, `registered_at`, `verified_at`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 1, 1, 'TCS', 'Engineer', 'TSSIA123', 2, 3, '2026-04-25 00:31:25', NULL, NULL, '2026-04-25 02:05:17', 1, NULL),
(2, 2, 1, 'Infosys', 'Manager', 'TSSIA456', 2, 3, '2026-04-25 00:31:25', NULL, NULL, NULL, 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `unique_registration` (`participant_id`,`event_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `registration_status_id` (`registration_status_id`),
  ADD KEY `attendance_status_id` (`attendance_status_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `registration_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`participant_id`),
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `event_registrations_ibfk_3` FOREIGN KEY (`registration_status_id`) REFERENCES `status_master` (`status_id`),
  ADD CONSTRAINT `event_registrations_ibfk_4` FOREIGN KEY (`attendance_status_id`) REFERENCES `status_master` (`status_id`),
  ADD CONSTRAINT `event_registrations_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `event_registrations_ibfk_6` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
