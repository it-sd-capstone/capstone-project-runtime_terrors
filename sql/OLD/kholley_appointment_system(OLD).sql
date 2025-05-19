-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 03:51 AM
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
CREATE TABLE IF NOT EXISTS `activity_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `created_at` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE IF NOT EXISTS `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  KEY `provider_id` (`provider_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_history`
--

DROP TABLE IF EXISTS `appointment_history`;
CREATE TABLE IF NOT EXISTS `appointment_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `action` enum('created','updated','canceled','rescheduled','completed','no_show') NOT NULL,
  `changed_fields` text DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_process_logs`
--

DROP TABLE IF EXISTS `appointment_process_logs`;
CREATE TABLE IF NOT EXISTS `appointment_process_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_role` varchar(20) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `action` varchar(50) NOT NULL,
  `entity` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `additional_data` text DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `entity` (`entity`,`entity_id`),
  KEY `timestamp` (`timestamp`),
  KEY `user_id` (`user_id`,`user_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_ratings`
--

DROP TABLE IF EXISTS `appointment_ratings`;
CREATE TABLE IF NOT EXISTS `appointment_ratings` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`rating_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_sessions`
--

DROP TABLE IF EXISTS `auth_sessions`;
CREATE TABLE IF NOT EXISTS `auth_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_active` datetime DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'system',
  `status` enum('pending','sent','failed','read') NOT NULL DEFAULT 'pending',
  `scheduled_for` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_system` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `audience` enum('all','admin','provider','patient') DEFAULT 'all',
  PRIMARY KEY (`notification_id`),
  UNIQUE KEY `unique_system_notification` (`subject`(100),`message`(100),`is_system`,`audience`),
  KEY `user_id` (`user_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `idx_notifications_user_id` (`user_id`),
  KEY `idx_notifications_is_read` (`is_read`),
  KEY `idx_notifications_created_at` (`created_at`),
  KEY `idx_notifications_appointment_id` (`appointment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE IF NOT EXISTS `notification_preferences` (
  `preference_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `sms_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `appointment_reminders` tinyint(1) NOT NULL DEFAULT 1,
  `system_updates` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_time` int(11) NOT NULL DEFAULT 24,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`preference_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient_profiles`
--

DROP TABLE IF EXISTS `patient_profiles`;
CREATE TABLE IF NOT EXISTS `patient_profiles` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(50) DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `insurance_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`patient_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_availability`
--

DROP TABLE IF EXISTS `provider_availability`;
CREATE TABLE IF NOT EXISTS `provider_availability` (
  `availability_id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `availability_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `schedule_type` varchar(50) DEFAULT 'availability',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_recurring` tinyint(1) DEFAULT 0,
  `weekdays` varchar(20) DEFAULT NULL,
  `max_appointments` int(11) DEFAULT 1,
  `service_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`availability_id`),
  KEY `fk_availability_service` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_profiles`
--

DROP TABLE IF EXISTS `provider_profiles`;
CREATE TABLE IF NOT EXISTS `provider_profiles` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `accepting_new_patients` tinyint(1) DEFAULT 1,
  `max_patients_per_day` int(11) DEFAULT 20,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`profile_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_services`
--

DROP TABLE IF EXISTS `provider_services`;
CREATE TABLE IF NOT EXISTS `provider_services` (
  `provider_service_id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `custom_duration` int(11) DEFAULT NULL COMMENT 'Override default duration if needed',
  `custom_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`provider_service_id`),
  KEY `provider_id` (`provider_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recurring_schedules`
--

DROP TABLE IF EXISTS `recurring_schedules`;
CREATE TABLE IF NOT EXISTS `recurring_schedules` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `day_of_week` int(11) NOT NULL COMMENT '0=Sunday, 1=Monday, etc.',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `schedule_type` varchar(50) DEFAULT 'availability',
  `effective_from` date DEFAULT NULL,
  `effective_until` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  `specific_date` date DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) DEFAULT 30,
  `price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('patient','provider','admin','staff') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `password_change_required` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `role`, `is_active`, `is_verified`, `email_verified_at`, `created_at`, `last_login`, `verification_token`, `reset_token`, `reset_token_expires`, `token_expires`, `password_change_required`) VALUES
(3, 'admin@example.com', '$2y$10$mn369d8QchCNaqnZ9DA15OJejiRrlHCzw33TIkq01qepowE42AWkS', 'Admin', 'User', NULL, 'admin', 1, 1, NULL, '2025-04-17 08:59:28', '2025-05-18 15:30:57', NULL, NULL, NULL, NULL, 0),
(127, 'provider@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Jennifer', 'Smith', '(555) 123-4567', 'provider', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(128, 'provider2@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Michael', 'Johnson', '(555) 234-5678', 'provider', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(129, 'provider3@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'David', 'Williams', '(555) 345-6789', 'provider', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(130, 'provider4@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Sarah', 'Brown', '(555) 456-7890', 'provider', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(131, 'provider5@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'James', 'Davis', '(555) 567-8901', 'provider', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(132, 'provider6@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Emily', 'Miller', '(555) 678-9012', 'provider', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(133, 'patient@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Robert', 'Anderson', '(555) 789-0123', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(134, 'patient2@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Lisa', 'Taylor', '(555) 890-1234', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(135, 'patient3@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Thomas', 'Moore', '(555) 901-2345', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(136, 'patient4@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Jessica', 'Jackson', '(555) 012-3456', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(137, 'patient5@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Daniel', 'White', '(555) 123-4567', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(138, 'patient6@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Michelle', 'Harris', '(555) 234-5678', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(139, 'patient7@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Kevin', 'Martin', '(555) 345-6789', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(140, 'patient8@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Patricia', 'Thompson', '(555) 456-7890', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(141, 'patient9@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Christopher', 'Garcia', '(555) 567-8901', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0),
(142, 'patient10@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Amanda', 'Martinez', '(555) 678-9012', 'patient', 1, 1, '2025-05-18 16:27:05', '2025-05-18 16:27:05', NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `selector` varchar(16) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

DROP TABLE IF EXISTS `waitlist`;
CREATE TABLE IF NOT EXISTS `waitlist` (
  `waitlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time DEFAULT NULL,
  `flexibility` enum('strict','flexible_time','flexible_day','flexible_provider') DEFAULT 'strict',
  `is_fulfilled` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`waitlist_id`),
  KEY `patient_id` (`patient_id`),
  KEY `provider_id` (`provider_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `appointment_history`
--
ALTER TABLE `appointment_history`
  ADD CONSTRAINT `appointment_history_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `appointment_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `appointment_ratings`
--
ALTER TABLE `appointment_ratings`
  ADD CONSTRAINT `appointment_ratings_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `appointment_ratings_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointment_ratings_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `auth_sessions`
--
ALTER TABLE `auth_sessions`
  ADD CONSTRAINT `auth_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`);

--
-- Constraints for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `patient_profiles`
--
ALTER TABLE `patient_profiles`
  ADD CONSTRAINT `patient_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `provider_availability`
--
ALTER TABLE `provider_availability`
  ADD CONSTRAINT `fk_availability_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `provider_profiles`
--
ALTER TABLE `provider_profiles`
  ADD CONSTRAINT `provider_profiles_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_services`
--
ALTER TABLE `provider_services`
  ADD CONSTRAINT `provider_services_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `provider_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `recurring_schedules`
--
ALTER TABLE `recurring_schedules`
  ADD CONSTRAINT `recurring_schedules_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD CONSTRAINT `waitlist_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `waitlist_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `waitlist_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services_old` (`service_id`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
