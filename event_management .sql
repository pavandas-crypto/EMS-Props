-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2026 at 09:06 AM
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
-- Table structure for table `custom_fields`
--

CREATE TABLE `custom_fields` (
  `custom_id` bigint(20) NOT NULL,
  `event_id` bigint(20) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `field_type` enum('text','textarea','number','email','phone','dropdown','radio','checkbox','date','time','file','url') DEFAULT NULL,
  `default_value` text DEFAULT NULL,
  `placeholder` varchar(150) DEFAULT NULL,
  `options_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options_json`)),
  `validation_regex` varchar(255) DEFAULT NULL,
  `required` tinyint(1) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `custom_fields`
--

INSERT INTO `custom_fields` (`custom_id`, `event_id`, `field_name`, `field_type`, `default_value`, `placeholder`, `options_json`, `validation_regex`, `required`, `sort_order`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 1, 'Food Preference', 'dropdown', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL),
(2, 1, 'Experience (Years)', 'number', NULL, NULL, NULL, NULL, 0, 2, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `custom_field_responses`
--

CREATE TABLE `custom_field_responses` (
  `response_id` bigint(20) NOT NULL,
  `registration_id` bigint(20) DEFAULT NULL,
  `custom_id` bigint(20) DEFAULT NULL,
  `VALUE` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `custom_field_responses`
--

INSERT INTO `custom_field_responses` (`response_id`, `registration_id`, `custom_id`, `VALUE`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Veg', NULL, NULL),
(2, 1, 2, '3', NULL, NULL),
(3, 2, 1, 'Non-Veg', NULL, NULL),
(4, 2, 2, '5', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` bigint(20) NOT NULL,
  `event_name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `address` text DEFAULT NULL,
  `event_for` enum('all','tssia_members') DEFAULT NULL,
  `image_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `description`, `start_date_time`, `end_date_time`, `address`, `event_for`, `image_id`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'Tech Conference 2026', '', '2026-06-01 10:00:00', '2026-06-01 17:00:00', 'Mumbai Expo Center', 'all', 2, NULL, '2026-04-25 02:46:14', 1, 1),
(2, 'Papu Birthday', '', '2026-04-26 11:06:00', '2026-04-26 15:06:00', '', 'tssia_members', 3, '2026-04-25 02:07:29', '2026-04-25 03:03:51', 1, 1);

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

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `image_id` bigint(20) NOT NULL,
  `url` text DEFAULT NULL,
  `alt_text` varchar(150) DEFAULT NULL,
  `file_name` varchar(150) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`image_id`, `url`, `alt_text`, `file_name`, `file_type`, `file_size`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'event1.jpg', 'Tech Event Banner', 'event1.jpg', 'image/jpeg', 500, NULL, NULL, 1, NULL),
(2, 'uploads/69ec5c2ea5550_Mindstix_Foundation_Trust_logo_design__1_-removebg-preview.png', 'Tech Conference 2026', '69ec5c2ea5550_Mindstix_Foundation_Trust_logo_design__1_-removebg-preview.png', 'image/png', 80345, '2026-04-25 02:46:14', NULL, 1, NULL),
(3, 'uploads/69ec604f265b3_Herof.jpg', 'Papu Birthday', '69ec604f265b3_Herof.jpg', 'image/jpeg', 22833, '2026-04-25 03:03:51', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `participant_id` bigint(20) NOT NULL,
  `NAME` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`participant_id`, `NAME`, `email`, `phone`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'Rahul Sharma', 'rahul@mail.com', '9876543210', NULL, NULL, NULL, NULL),
(2, 'Priya Mehta', 'priya@mail.com', '9123456780', NULL, NULL, NULL, NULL),
(3, 'Test User', 'testuser@example.com', '1234567890', '2026-04-25 03:16:12', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `passes`
--

CREATE TABLE `passes` (
  `pass_id` bigint(20) NOT NULL,
  `registration_id` bigint(20) DEFAULT NULL,
  `template_id` bigint(20) DEFAULT NULL,
  `pass_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passes`
--

INSERT INTO `passes` (`pass_id`, `registration_id`, `template_id`, `pass_number`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 1, 1, 'PASS001', NULL, NULL, 1, NULL),
(2, 2, 1, 'PASS002', NULL, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `qr_id` bigint(20) NOT NULL,
  `pass_id` bigint(20) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_codes`
--

INSERT INTO `qr_codes` (`qr_id`, `pass_id`, `qr_code`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 1, 'QR_PASS_001', NULL, NULL, 1, NULL),
(2, 2, 'QR_PASS_002', NULL, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registration_success_settings`
--

CREATE TABLE `registration_success_settings` (
  `success_id` bigint(20) NOT NULL,
  `event_id` bigint(20) DEFAULT NULL,
  `success_title` varchar(150) DEFAULT NULL,
  `success_message` text DEFAULT NULL,
  `show_approval_notice` tinyint(1) DEFAULT 0,
  `approval_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_success_settings`
--

INSERT INTO `registration_success_settings` (`success_id`, `event_id`, `success_title`, `success_message`, `show_approval_notice`, `approval_message`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 1, 'Registration Successful 🎉', 'You have successfully registered for the event.', 1, 'Your registration is under review. Please wait for approval confirmation via email.', NULL, '2026-04-25 02:46:14', 1, 1),
(2, 2, 'Registration Successful 🎉', 'You have successfully registered for the event.', 0, 'Your registration is under review. Please wait for approval confirmation via email.', '2026-04-25 02:07:29', '2026-04-25 03:03:51', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `scan_logs`
--

CREATE TABLE `scan_logs` (
  `log_id` bigint(20) NOT NULL,
  `event_id` bigint(20) DEFAULT NULL,
  `registration_id` bigint(20) DEFAULT NULL,
  `scanned_by` bigint(20) DEFAULT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scan_logs`
--

INSERT INTO `scan_logs` (`log_id`, `event_id`, `registration_id`, `scanned_by`, `scanned_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, '2026-04-25 00:31:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `status_master`
--

CREATE TABLE `status_master` (
  `status_id` bigint(20) NOT NULL,
  `TYPE` enum('attendance','registration') DEFAULT NULL,
  `NAME` varchar(50) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status_master`
--

INSERT INTO `status_master` (`status_id`, `TYPE`, `NAME`, `description`, `created_at`, `updated_at`) VALUES
(1, 'registration', 'pending', NULL, NULL, NULL),
(2, 'registration', 'approved', NULL, NULL, NULL),
(3, 'attendance', 'not_present', NULL, NULL, NULL),
(4, 'attendance', 'present', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_fields`
--

CREATE TABLE `ticket_fields` (
  `field_id` bigint(20) NOT NULL,
  `template_id` bigint(20) DEFAULT NULL,
  `NAME` varchar(100) DEFAULT NULL,
  `json_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_config`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_templates`
--

CREATE TABLE `ticket_templates` (
  `template_id` bigint(20) NOT NULL,
  `template_name` varchar(100) DEFAULT NULL,
  `layout_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`layout_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_templates`
--

INSERT INTO `ticket_templates` (`template_id`, `template_name`, `layout_json`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'Default Ticket', NULL, NULL, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','verifier') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Tester One', 'admin@mail.com', '$2y$12$kEcp/PIOtyZbg5JVfDMvuebv5pDQfjBmY.zgcHI.zaqREPq0LClB2', 'admin', '2026-04-25 00:31:25', NULL),
(2, 'Verifier One', 'verifier1@mail.com', '123456', 'verifier', '2026-04-25 00:31:25', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD PRIMARY KEY (`custom_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `custom_field_responses`
--
ALTER TABLE `custom_field_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `registration_id` (`registration_id`),
  ADD KEY `custom_id` (`custom_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `image_id` (`image_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

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
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`participant_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `passes`
--
ALTER TABLE `passes`
  ADD PRIMARY KEY (`pass_id`),
  ADD UNIQUE KEY `pass_number` (`pass_number`),
  ADD KEY `registration_id` (`registration_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`qr_id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`),
  ADD KEY `pass_id` (`pass_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `registration_success_settings`
--
ALTER TABLE `registration_success_settings`
  ADD PRIMARY KEY (`success_id`),
  ADD UNIQUE KEY `event_id` (`event_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `scan_logs`
--
ALTER TABLE `scan_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `registration_id` (`registration_id`),
  ADD KEY `scanned_by` (`scanned_by`);

--
-- Indexes for table `status_master`
--
ALTER TABLE `status_master`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `ticket_fields`
--
ALTER TABLE `ticket_fields`
  ADD PRIMARY KEY (`field_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `ticket_templates`
--
ALTER TABLE `ticket_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `custom_fields`
--
ALTER TABLE `custom_fields`
  MODIFY `custom_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `custom_field_responses`
--
ALTER TABLE `custom_field_responses`
  MODIFY `response_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `registration_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `participant_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `passes`
--
ALTER TABLE `passes`
  MODIFY `pass_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `qr_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `registration_success_settings`
--
ALTER TABLE `registration_success_settings`
  MODIFY `success_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `scan_logs`
--
ALTER TABLE `scan_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `status_master`
--
ALTER TABLE `status_master`
  MODIFY `status_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ticket_fields`
--
ALTER TABLE `ticket_fields`
  MODIFY `field_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_templates`
--
ALTER TABLE `ticket_templates`
  MODIFY `template_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD CONSTRAINT `custom_fields_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `custom_fields_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `custom_fields_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `custom_field_responses`
--
ALTER TABLE `custom_field_responses`
  ADD CONSTRAINT `custom_field_responses_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`registration_id`),
  ADD CONSTRAINT `custom_field_responses_ibfk_2` FOREIGN KEY (`custom_id`) REFERENCES `custom_fields` (`custom_id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `images` (`image_id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

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

--
-- Constraints for table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `images_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `participants_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `passes`
--
ALTER TABLE `passes`
  ADD CONSTRAINT `passes_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`registration_id`),
  ADD CONSTRAINT `passes_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `ticket_templates` (`template_id`),
  ADD CONSTRAINT `passes_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `passes_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`pass_id`) REFERENCES `passes` (`pass_id`),
  ADD CONSTRAINT `qr_codes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `qr_codes_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `registration_success_settings`
--
ALTER TABLE `registration_success_settings`
  ADD CONSTRAINT `registration_success_settings_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `registration_success_settings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `registration_success_settings_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `scan_logs`
--
ALTER TABLE `scan_logs`
  ADD CONSTRAINT `scan_logs_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `scan_logs_ibfk_2` FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`registration_id`),
  ADD CONSTRAINT `scan_logs_ibfk_3` FOREIGN KEY (`scanned_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `ticket_fields`
--
ALTER TABLE `ticket_fields`
  ADD CONSTRAINT `ticket_fields_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `ticket_templates` (`template_id`),
  ADD CONSTRAINT `ticket_fields_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `ticket_fields_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `ticket_templates`
--
ALTER TABLE `ticket_templates`
  ADD CONSTRAINT `ticket_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `ticket_templates_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
