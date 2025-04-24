-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 09:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kholley_appointment_system`
--
CREATE DATABASE IF NOT EXISTS `kholley_appointment_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `kholley_appointment_system`;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('scheduled','confirmed','completed','canceled','no_show') NOT NULL DEFAULT 'scheduled',
  `type` enum('in_person','virtual','phone') NOT NULL DEFAULT 'in_person',
  `notes` text DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `confirmed_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `provider_id`, `service_id`, `appointment_date`, `start_time`, `end_time`, `status`, `type`, `notes`, `reason`, `reminder_sent`, `confirmed_at`, `canceled_at`, `created_at`, `updated_at`) VALUES
(1, 1, 16, 2, '2025-04-24', '17:55:00', '18:25:00', 'confirmed', 'in_person', 'Notes', 'Visit', 0, NULL, NULL, '2025-04-23 16:16:55', '2025-04-23 16:16:55');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_history`
--

DROP TABLE IF EXISTS `appointment_history`;
CREATE TABLE `appointment_history` (
  `history_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `action` enum('created','updated','canceled','rescheduled','completed','no_show') NOT NULL,
  `changed_fields` text DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_history`
--

INSERT INTO `appointment_history` (`history_id`, `appointment_id`, `action`, `changed_fields`, `old_values`, `new_values`, `user_id`, `created_at`) VALUES
(1, 1, 'created', NULL, NULL, NULL, 1, '2025-04-17 08:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `auth_sessions`
--

DROP TABLE IF EXISTS `auth_sessions`;
CREATE TABLE `auth_sessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_active` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

DROP TABLE IF EXISTS `availability`;
CREATE TABLE `availability` (
  `availability_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `availability_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`availability_id`, `provider_id`, `availability_date`, `start_time`, `end_time`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 2, '2025-04-15', '09:00:00', '12:00:00', 1, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(2, 2, '2025-04-16', '14:00:00', '17:00:00', 1, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(4, 2, '2025-04-18', '10:04:00', '22:04:00', 1, '2025-04-17 10:05:05', '2025-04-17 10:05:05'),
(5, 2, '2025-04-17', '10:11:00', '22:11:00', 1, '2025-04-17 10:11:09', '2025-04-17 10:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('email','sms','app','system') NOT NULL DEFAULT 'email',
  `status` enum('pending','sent','failed','read') NOT NULL DEFAULT 'pending',
  `scheduled_for` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `appointment_id`, `subject`, `message`, `type`, `status`, `scheduled_for`, `sent_at`, `created_at`) VALUES
(1, 1, 1, 'Appointment Confirmation', 'Your appointment on April 15, 2025 at 10:00 AM has been scheduled.', 'email', 'sent', '2025-04-17 09:00:00', '2025-04-17 09:00:05', '2025-04-17 08:59:28'),
(0, 16, NULL, 'Your Provider Account Has Been Created', 'Hello john doe,\n\nAn account has been created for you as a provider in our appointment system.\n\nYour temporary login credentials are:\nEmail: john@doe.com\nPassword: c1291205\n\nPlease login and change your password as soon as possible at: http://localhost/appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth\n\nThank you,\nAppointment System Admin', 'email', 'pending', NULL, NULL, '2025-04-22 20:16:51');

-- --------------------------------------------------------

--
-- Table structure for table `patient_profiles`
--

DROP TABLE IF EXISTS `patient_profiles`;
CREATE TABLE `patient_profiles` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `insurance_info` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_profiles`
--

INSERT INTO `patient_profiles` (`patient_id`, `user_id`, `phone`, `date_of_birth`, `address`, `insurance_info`, `medical_history`, `created_at`, `updated_at`) VALUES
(1, 0, NULL, NULL, NULL, NULL, NULL, '2025-04-23 00:49:59', '2025-04-23 00:49:59');

-- --------------------------------------------------------

--
-- Table structure for table `provider_availability`
--

DROP TABLE IF EXISTS `provider_availability`;
CREATE TABLE `provider_availability` (
  `availability_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provider_availability`
--

INSERT INTO `provider_availability` (`availability_id`, `provider_id`, `available_date`, `start_time`, `end_time`, `is_available`, `created_at`) VALUES
(1, 2, '2025-04-15', '09:00:00', '12:00:00', 1, '2025-04-17 08:59:28'),
(2, 2, '2025-04-16', '14:00:00', '17:00:00', 1, '2025-04-17 08:59:28'),
(4, 2, '2025-04-18', '10:04:00', '22:04:00', 1, '2025-04-17 10:05:05'),
(5, 2, '2025-04-17', '10:11:00', '22:11:00', 1, '2025-04-17 10:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `provider_profiles`
--

DROP TABLE IF EXISTS `provider_profiles`;
CREATE TABLE `provider_profiles` (
  `profile_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `accepting_new_patients` tinyint(1) DEFAULT 1,
  `max_patients_per_day` int(11) DEFAULT 20,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provider_profiles`
--

INSERT INTO `provider_profiles` (`profile_id`, `provider_id`, `specialization`, `title`, `bio`, `accepting_new_patients`, `max_patients_per_day`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 16, 'Practisioner', 'MD', 'Hello', 1, 20, NULL, '2025-04-23 01:16:51', '2025-04-23 01:16:51');

-- --------------------------------------------------------

--
-- Table structure for table `provider_services`
--

DROP TABLE IF EXISTS `provider_services`;
CREATE TABLE `provider_services` (
  `provider_service_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `custom_duration` int(11) DEFAULT NULL COMMENT 'Override default duration if needed',
  `custom_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provider_services`
--

INSERT INTO `provider_services` (`provider_service_id`, `provider_id`, `service_id`, `custom_duration`, `custom_notes`, `created_at`) VALUES
(1, 2, 1, NULL, NULL, '2025-04-17 08:59:28'),
(2, 2, 2, NULL, NULL, '2025-04-17 08:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `recurring_schedules`
--

DROP TABLE IF EXISTS `recurring_schedules`;
CREATE TABLE `recurring_schedules` (
  `schedule_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `day_of_week` int(11) NOT NULL COMMENT '0=Sunday, 1=Monday, etc.',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `effective_from` date DEFAULT NULL,
  `effective_until` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recurring_schedules`
--

INSERT INTO `recurring_schedules` (`schedule_id`, `provider_id`, `day_of_week`, `start_time`, `end_time`, `is_active`, `effective_from`, `effective_until`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '09:00:00', '17:00:00', 1, '2025-04-01', NULL, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(2, 2, 3, '09:00:00', '17:00:00', 1, '2025-04-01', NULL, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(3, 2, 5, '09:00:00', '12:00:00', 1, '2025-04-01', NULL, '2025-04-17 08:59:28', '2025-04-17 08:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) DEFAULT 30,
  `price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `name`, `description`, `duration`, `price`, `is_active`, `created_at`) VALUES
(1, 'Regular Checkup', 'Standard medical examination', 30, 75.00, 1, '2025-04-22 23:34:16'),
(2, 'Consultation', 'Medical consultation with specialist', 45, 125.00, 1, '2025-04-22 23:34:16');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'appointment_reminder_time', '24', 'Hours before appointment to send reminder', '2025-04-17 08:59:28'),
(2, 'enable_sms_notifications', 'true', 'Enable SMS notifications', '2025-04-17 08:59:28'),
(3, 'enable_email_notifications', 'true', 'Enable email notifications', '2025-04-17 08:59:28'),
(4, 'business_hours', '{\"monday\":\"9:00-17:00\",\"tuesday\":\"9:00-17:00\",\"wednesday\":\"9:00-17:00\",\"thursday\":\"9:00-17:00\",\"friday\":\"9:00-13:00\"}', 'Business hours by day', '2025-04-17 08:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('patient','provider','admin','staff') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `password_change_required` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `role`, `is_active`, `email_verified_at`, `created_at`, `last_login`, `verification_token`, `reset_token`, `reset_token_expires`, `token_expires`, `password_change_required`) VALUES
(1, 'patient@example.com', '$2y$10$example_hash', 'John', 'Doe', NULL, 'patient', 1, NULL, '2025-04-17 08:59:28', NULL, NULL, NULL, NULL, NULL, 0),
(2, 'provider@example.com', '$2y$10$example_hash', 'Dr. Smith', 'MD', NULL, 'provider', 1, NULL, '2025-04-17 08:59:28', NULL, NULL, NULL, NULL, NULL, 0),
(3, 'admin@example.com', '$2y$10$example_hash', 'Admin', 'User', NULL, 'admin', 1, NULL, '2025-04-17 08:59:28', NULL, NULL, NULL, NULL, NULL, 0),
(10, 'Kholley@student.cvtc.edu', '$2y$10$xiDwOmotNFAOn.5R0XJ1huUpLb681/phtw/TCkT9wlddp9s1DiRnG', 'Kaleb', 'Holley', '7156191363', 'patient', 1, '2025-04-22 09:54:17', '2025-04-22 09:54:02', '2025-04-22 09:54:47', NULL, 'd9472a925ebd1c3138bb4731edcaf0a207e72c66c1bd00be9783e31f632bb69e', '2025-04-22 15:55:40', '2025-04-23 14:54:02', 0),
(16, 'john@doe.com', '$2y$10$nxPUCopIWXPZw442f6B4UOJv.H/6wty9qrJmSqXzYPCmo6i6Zn8sW', 'john', 'doe', '1234567897', 'provider', 1, NULL, '2025-04-22 20:16:51', '2025-04-22 20:25:22', NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

DROP TABLE IF EXISTS `waitlist`;
CREATE TABLE `waitlist` (
  `waitlist_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time DEFAULT NULL,
  `flexibility` enum('strict','flexible_time','flexible_day','flexible_provider') DEFAULT 'strict',
  `is_fulfilled` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `idx_appointment_date` (`appointment_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `patient_profiles`
--
ALTER TABLE `patient_profiles`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `provider_profiles`
--
ALTER TABLE `provider_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patient_profiles`
--
ALTER TABLE `patient_profiles`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `provider_profiles`
--
ALTER TABLE `provider_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `provider_profiles`
--
ALTER TABLE `provider_profiles`
  ADD CONSTRAINT `provider_profiles_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
