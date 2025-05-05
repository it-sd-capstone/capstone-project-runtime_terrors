-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 02:54 AM
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
  `log_id` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `description`, `category`, `created_at`, `ip_address`, `details`, `related_id`, `related_type`) VALUES
(0, 1, 'Auth: logout', 'authentication', '2025-05-02 12:49:35', '::1', NULL, NULL, NULL),
(1, 3, 'Admin logged in', 'general', '2025-04-25 12:38:20', NULL, NULL, NULL, NULL),
(2, 3, 'Created new provider account: Dr. Jane Smith', 'general', '2025-04-25 11:08:20', NULL, NULL, NULL, NULL),
(3, 3, 'Updated system settings: appointment reminder time changed to 24 hours', 'general', '2025-04-25 09:08:20', NULL, NULL, NULL, NULL),
(4, 2, 'Provider logged in', 'general', '2025-04-24 13:08:20', NULL, NULL, NULL, NULL),
(5, 1, 'Patient logged in', 'general', '2025-04-23 13:08:20', NULL, NULL, NULL, NULL),
(6, 1, 'Auth: logout', 'authentication', '2025-04-26 13:44:32', '::1', NULL, NULL, NULL),
(7, 1, 'Auth: logout', 'authentication', '2025-04-26 13:45:12', '::1', NULL, NULL, NULL),
(8, 2, 'Auth: logout', 'authentication', '2025-04-26 13:56:36', '::1', NULL, NULL, NULL),
(9, 2, 'Auth: logout', 'authentication', '2025-04-26 13:58:41', '::1', NULL, NULL, NULL),
(10, 3, 'Auth: logout', 'authentication', '2025-04-26 14:00:11', '::1', NULL, NULL, NULL),
(11, 2, 'Auth: logout', 'authentication', '2025-04-26 14:18:36', '::1', NULL, NULL, NULL),
(19, 1, 'Auth: logout', 'authentication', '2025-04-26 17:27:49', '::1', NULL, NULL, NULL),
(20, 1, 'Auth: logout', 'authentication', '2025-04-27 12:56:19', '::1', NULL, NULL, NULL),
(21, 3, 'Auth: logout', 'authentication', '2025-04-27 13:07:44', '::1', NULL, NULL, NULL),
(22, 1, 'Auth: logout', 'authentication', '2025-04-27 13:08:08', '::1', NULL, NULL, NULL),
(23, 2, 'Auth: logout', 'authentication', '2025-04-27 13:08:25', '::1', NULL, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `provider_id`, `service_id`, `appointment_date`, `start_time`, `end_time`, `status`, `type`, `notes`, `reason`, `reminder_sent`, `confirmed_at`, `canceled_at`, `created_at`, `updated_at`) VALUES
(1, 1, 16, 2, '2025-04-24', '17:55:00', '18:25:00', 'confirmed', 'in_person', 'Notes', 'Visit', 0, NULL, NULL, '2025-04-23 16:16:55', '2025-04-23 16:16:55'),
(2, 10, 16, 1, '2025-04-26', '13:32:00', '14:02:00', 'confirmed', 'in_person', 'notes', 'reason', 0, NULL, NULL, '2025-04-25 13:32:49', '2025-04-25 13:32:49'),
(3, 1, 16, 1, '2025-04-28', '10:00:00', '10:30:00', 'scheduled', 'in_person', '', '', 0, NULL, NULL, '2025-04-26 15:40:00', '2025-04-26 15:40:00'),
(4, 1, 16, 2, '2025-04-29', '11:00:00', '11:45:00', 'scheduled', 'in_person', '', '', 0, NULL, NULL, '2025-04-26 15:40:30', '2025-04-26 15:40:30'),
(5, 1, 2, 1, '2025-04-29', '14:00:00', '14:30:00', 'scheduled', 'in_person', '', '', 0, NULL, NULL, '2025-04-26 15:40:45', '2025-04-26 15:40:45'),
(6, 1, 2, 2, '2025-04-29', '15:00:00', '15:45:00', 'scheduled', 'in_person', '', '', 0, NULL, NULL, '2025-04-26 15:41:00', '2025-04-26 15:41:00'),
(7, 1, 2, 1, '2025-04-30', '09:00:00', '09:30:00', 'scheduled', 'in_person', '', '', 0, NULL, NULL, '2025-04-26 15:41:15', '2025-04-26 15:41:15'),
(8, 1, 2, 2, '2025-04-30', '09:58:00', '10:43:00', 'scheduled', 'in_person', '', '', 0, NULL, NULL, '2025-04-26 15:41:23', '2025-04-26 15:58:58'),
(9, 1, 16, 1, '2025-04-30', '09:30:00', '10:00:00', 'scheduled', 'in_person', '', '', 0, NULL, NULL, '2025-04-26 15:41:55', '2025-04-26 15:41:55'),
(10, 1, 16, 2, '2025-04-30', '09:32:00', '10:17:00', 'scheduled', 'in_person', '', '', 0, NULL, NULL, '2025-04-26 16:32:36', '2025-04-26 16:32:36'),
(11, 1, 2, 1, '2025-05-08', '10:11:00', '10:41:00', 'canceled', 'in_person', '', 'Canceled by administrator', 0, NULL, '2025-05-04 17:46:54', '2025-05-04 16:59:02', '2025-05-04 17:46:54'),
(12, 1, 2, 2, '2025-05-07', '10:04:00', '10:49:00', 'canceled', 'in_person', '', 'Canceled by administrator', 0, NULL, '2025-05-04 17:30:35', '2025-05-04 17:16:58', '2025-05-04 17:30:35'),
(13, 1, 2, 2, '2025-05-05', '09:00:00', '09:45:00', 'canceled', 'in_person', '', 'Canceled by administrator', 0, NULL, '2025-05-04 17:30:41', '2025-05-04 17:17:30', '2025-05-04 17:30:41'),
(14, 1, 2, 2, '2025-05-06', '14:00:00', '14:45:00', 'canceled', 'in_person', 'Note', 'Canceled by administrator', 0, NULL, '2025-05-04 17:30:37', '2025-05-04 17:22:37', '2025-05-04 17:30:37'),
(15, 1, 2, 2, '2025-05-05', '09:00:00', '09:45:00', 'canceled', 'in_person', '', 'No reason provided', 0, NULL, '2025-05-04 18:23:39', '2025-05-04 17:31:30', '2025-05-04 18:23:39'),
(16, 1, 2, 1, '2025-05-07', '10:04:00', '10:34:00', 'canceled', 'in_person', '', 'Canceled by administrator', 0, NULL, '2025-05-04 17:46:58', '2025-05-04 17:32:59', '2025-05-04 17:46:58'),
(17, 1, 2, 1, '2025-05-08', '10:11:00', '10:41:00', 'canceled', 'in_person', '', 'Canceled by administrator', 0, NULL, '2025-05-04 17:46:56', '2025-05-04 17:37:38', '2025-05-04 17:46:56'),
(18, 1, 2, 1, '2025-05-06', '14:00:00', '14:30:00', 'canceled', 'in_person', '', 'No reason provided', 0, NULL, '2025-05-04 18:23:48', '2025-05-04 17:43:25', '2025-05-04 18:23:48'),
(19, 1, 2, 1, '2025-05-07', '10:04:00', '10:34:00', 'canceled', 'in_person', '', 'No reason provided', 0, NULL, '2025-05-04 18:23:50', '2025-05-04 17:47:24', '2025-05-04 18:23:50'),
(20, 1, 2, 1, '2025-05-05', '09:00:00', '09:30:00', 'canceled', 'in_person', 'note', 'No reason provided', 0, NULL, '2025-05-04 19:06:42', '2025-05-04 18:26:30', '2025-05-04 19:06:42'),
(21, 1, 2, 1, '2025-05-06', '14:00:00', '14:30:00', 'canceled', 'in_person', '', 'No reason provided', 0, NULL, '2025-05-04 19:06:45', '2025-05-04 18:54:57', '2025-05-04 19:06:45'),
(22, 1, 2, 1, '2025-05-07', '10:04:00', '10:34:00', 'scheduled', '', '', '', 0, NULL, NULL, '2025-05-04 19:35:26', '2025-05-04 19:35:26');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_history`
--

DROP TABLE IF EXISTS `appointment_history`;
CREATE TABLE IF NOT EXISTS `appointment_history` (
  `history_id` int(11) NOT NULL,
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

--
-- Dumping data for table `appointment_history`
--

INSERT INTO `appointment_history` (`history_id`, `appointment_id`, `action`, `changed_fields`, `old_values`, `new_values`, `user_id`, `created_at`) VALUES
(1, 1, 'created', NULL, NULL, NULL, 1, '2025-04-17 08:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_ratings`
--

DROP TABLE IF EXISTS `appointment_ratings`;
CREATE TABLE IF NOT EXISTS `appointment_ratings` (
  `rating_id` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_ratings`
--

INSERT INTO `appointment_ratings` (`rating_id`, `appointment_id`, `patient_id`, `provider_id`, `rating`, `comment`, `created_at`) VALUES
(5, 1, 1, 16, 4, 'Virtual appointment went smoothly, doctor was attentive', '2025-04-27 05:08:47'),
(6, 2, 10, 16, 3, 'Average experience, long wait time before appointment', '2025-04-27 05:08:47'),
(7, 3, 1, 16, 5, 'Excellent experience, highly recommend!', '2025-04-27 05:08:47'),
(8, 4, 1, 16, 4, 'Good appointment, doctor was knowledgeable.', '2025-04-27 05:08:47'),
(9, 5, 1, 2, 5, 'The doctor answered all my questions thoroughly', '2025-04-27 05:08:47'),
(10, 6, 1, 2, 4, 'Short wait time and professional service', '2025-04-27 05:08:47'),
(11, 7, 1, 2, 4, 'Appointment ran slightly late but care was excellent', '2025-04-27 05:08:47'),
(12, 1, 1, 16, 5, 'Great service! The doctor was very thorough.', '2025-04-27 05:09:53'),
(13, 2, 10, 16, 4, 'Very professional, but had to wait a bit.', '2025-04-27 05:09:53'),
(14, 3, 1, 16, 5, 'Excellent experience, highly recommend!', '2025-04-27 05:09:53'),
(15, 4, 1, 16, 4, 'Good appointment, doctor was knowledgeable.', '2025-04-27 05:09:53'),
(16, 5, 1, 2, 5, 'The doctor answered all my questions thoroughly', '2025-04-27 05:09:53'),
(17, 6, 1, 2, 4, 'Short wait time and professional service', '2025-04-27 05:09:53'),
(18, 7, 1, 2, 4, 'Appointment ran slightly late but care was excellent', '2025-04-27 05:09:53');

-- --------------------------------------------------------

--
-- Table structure for table `auth_sessions`
--

DROP TABLE IF EXISTS `auth_sessions`;
CREATE TABLE IF NOT EXISTS `auth_sessions` (
  `session_id` int(11) NOT NULL,
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
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('email','sms','app','system') NOT NULL DEFAULT 'email',
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
  KEY `appointment_id` (`appointment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `appointment_id`, `subject`, `message`, `type`, `status`, `scheduled_for`, `sent_at`, `created_at`, `is_system`, `is_read`, `audience`) VALUES
(1, 1, 1, 'Appointment Confirmation', 'Your appointment on April 15, 2025 at 10:00 AM has been scheduled.', 'email', 'sent', '2025-04-17 09:00:00', '2025-04-17 09:00:05', '2025-04-17 08:59:28', 0, 1, 'patient'),
(2, 16, NULL, 'Your Provider Account Has Been Created', 'Hello john doe,\n\nAn account has been created for you as a provider in our appointment system.\n\nYour temporary login credentials are:\nEmail: john@doe.com\nPassword: c1291205\n\nPlease login and change your password as soon as possible at: http://localhost/appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth\n\nThank you,\nAppointment System Admin', 'email', 'pending', NULL, NULL, '2025-04-22 20:16:51', 0, 0, 'provider'),
(3, 3, NULL, 'System Update v2.0', 'The appointment system has been updated to version 2.0', 'system', 'sent', NULL, NULL, '2025-04-25 12:04:04', 1, 0, 'all'),
(4, 3, NULL, 'New Provider Added', 'Dr. Jane Smith has joined the practice', 'system', 'sent', NULL, NULL, '2025-04-25 11:04:04', 1, 0, 'admin'),
(5, 3, NULL, 'Maintenance Notice Tonight', 'The system will be undergoing maintenance tonight at 11 PM', 'system', 'sent', NULL, NULL, '2025-04-25 10:04:04', 1, 0, 'all'),
(6, 3, NULL, 'Holiday Hours Announcement', 'The practice will be closed on December 25th for Christmas', 'system', 'sent', NULL, NULL, '2025-04-24 13:04:04', 1, 0, 'all'),
(7, 3, NULL, 'System Update Completed', 'The appointment system has been successfully updated to version 2.0', 'system', 'sent', NULL, NULL, '2025-04-25 12:05:49', 1, 0, 'all'),
(8, 3, NULL, 'Welcome New Provider', 'Dr. Jane Smith has joined our medical team', 'system', 'sent', NULL, NULL, '2025-04-25 11:05:49', 1, 0, 'admin'),
(9, 3, NULL, 'Scheduled Maintenance', 'The system will be undergoing scheduled maintenance tonight at 11 PM', 'system', 'sent', NULL, NULL, '2025-04-25 10:05:49', 1, 0, 'all'),
(10, 3, NULL, 'Christmas Closure', 'The practice will be closed on December 25th for the Christmas holiday', 'system', 'sent', NULL, NULL, '2025-04-24 13:05:49', 1, 0, 'all');

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE IF NOT EXISTS `notification_preferences` (
  `preference_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `sms_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `appointment_reminders` tinyint(1) NOT NULL DEFAULT 1,
  `system_updates` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_time` int(11) NOT NULL DEFAULT 24,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`preference_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient_profiles`
--

DROP TABLE IF EXISTS `patient_profiles`;
CREATE TABLE IF NOT EXISTS `patient_profiles` (
  `patient_id` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_profiles`
--

INSERT INTO `patient_profiles` (`patient_id`, `user_id`, `phone`, `date_of_birth`, `address`, `emergency_contact`, `emergency_contact_phone`, `medical_conditions`, `medical_history`, `insurance_info`, `created_at`, `updated_at`) VALUES
(1, 1, '2120001234', '1997-10-31', '123 Main Street', 'example', '1112223333', 'Na', NULL, '{\"provider\":\"Example\",\"policy_number\":\"8778599455d\"}', '2025-04-23 00:49:59', '2025-04-26 22:46:30'),
(17, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-04 18:48:54', '2025-05-04 18:48:54'),
(18, 18, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-04 18:54:30', '2025-05-04 18:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `provider_availability`
--

DROP TABLE IF EXISTS `provider_availability`;
CREATE TABLE IF NOT EXISTS `provider_availability` (
  `availability_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `availability_date` date DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `weekdays` varchar(20) DEFAULT NULL,
  `max_appointments` INT DEFAULT 0,
  PRIMARY KEY (`availability_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provider_availability`
--

INSERT INTO `provider_availability` (`availability_id`, `provider_id`, `availability_date`, `start_time`, `end_time`, `is_available`, `created_at`, `is_recurring`, `weekdays`) VALUES
(1, 2, '2025-05-05', '09:00:00', '12:00:00', 1, '2025-04-17 08:59:28', 1, '1,2,3,4,5'),
(2, 2, '2025-05-06', '14:00:00', '17:00:00', 1, '2025-04-17 08:59:28', 1, '1,2,3,4,5'),
(4, 2, '2025-05-07', '10:04:00', '22:04:00', 1, '2025-04-17 10:05:05', 1, '1,2,3,4,5'),
(5, 2, '2025-05-08', '10:11:00', '22:11:00', 1, '2025-04-17 10:11:09', 1, '1,2,3,4,5');

-- --------------------------------------------------------

--
-- Table structure for table `provider_profiles`
--

DROP TABLE IF EXISTS `provider_profiles`;
CREATE TABLE IF NOT EXISTS `provider_profiles` (
  `profile_id` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provider_profiles`
--

INSERT INTO `provider_profiles` (`profile_id`, `provider_id`, `specialization`, `title`, `bio`, `accepting_new_patients`, `max_patients_per_day`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 16, 'Practisioner', 'MD', 'Hello', 1, 20, NULL, '2025-04-23 01:16:51', '2025-04-23 01:16:51'),
(2, 2, 'General Medicine', 'MD', 'Experienced general practitioner', 1, 20, NULL, '2025-05-04 20:15:52', '2025-05-04 20:15:52');

-- --------------------------------------------------------

--
-- Table structure for table `provider_services`
--

DROP TABLE IF EXISTS `provider_services`;
CREATE TABLE IF NOT EXISTS `provider_services` (
  `provider_service_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `custom_duration` int(11) DEFAULT NULL COMMENT 'Override default duration if needed',
  `custom_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`provider_service_id`),
  KEY `provider_id` (`provider_id`),
  KEY `service_id` (`service_id`)
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
CREATE TABLE IF NOT EXISTS `recurring_schedules` (
  `schedule_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `day_of_week` int(11) NOT NULL COMMENT '0=Sunday, 1=Monday, etc.',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `effective_from` date DEFAULT NULL,
  `effective_until` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`schedule_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recurring_schedules`
--

INSERT INTO `recurring_schedules` (`schedule_id`, `provider_id`, `day_of_week`, `start_time`, `end_time`, `is_active`, `effective_from`, `effective_until`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '14:00:00', '22:00:00', 1, '2025-04-01', NULL, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(2, 2, 3, '09:00:00', '17:00:00', 1, '2025-04-01', NULL, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(3, 2, 5, '09:00:00', '12:00:00', 1, '2025-04-01', NULL, '2025-04-17 08:59:28', '2025-04-17 08:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `service_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) DEFAULT 30,
  `price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`service_id`)
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
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`setting_id`)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `role`, `is_active`, `is_verified`, `email_verified_at`, `created_at`, `last_login`, `verification_token`, `reset_token`, `reset_token_expires`, `token_expires`, `password_change_required`) VALUES
(1, 'patient@example.com', '$2y$10$example_hash', 'John', 'Doe', '2120001234', 'patient', 1, 1, NULL, '2025-04-17 08:59:28', NULL, NULL, NULL, NULL, NULL, 0),
(2, 'provider@example.com', '$2y$10$example_hash', 'Dr. Smith', 'MD', NULL, 'provider', 1, 1, NULL, '2025-04-17 08:59:28', NULL, NULL, NULL, NULL, NULL, 0),
(3, 'admin@example.com', '$2y$10$q/l96RJfI9YX3D1jtI/ZBue7hSr.F6/zVjzrOI1loV9lNj3W.hMY.', 'Admin', 'User', NULL, 'admin', 1, 1, NULL, '2025-04-17 08:59:28', '2025-05-02 12:52:04', NULL, NULL, NULL, NULL, 0),
(10, 'Kholley@student.cvtc.edu', '$2y$10$xiDwOmotNFAOn.5R0XJ1huUpLb681/phtw/TCkT9wlddp9s1DiRnG', 'Kaleb', 'Holley', '7156191363', 'patient', 1, 1, '2025-04-22 09:54:17', '2025-04-22 09:54:02', '2025-04-22 09:54:47', NULL, 'd9472a925ebd1c3138bb4731edcaf0a207e72c66c1bd00be9783e31f632bb69e', '2025-04-22 15:55:40', '2025-04-23 14:54:02', 0),
(16, 'john@doe.com', '$2y$10$nxPUCopIWXPZw442f6B4UOJv.H/6wty9qrJmSqXzYPCmo6i6Zn8sW', 'john', 'doe', '1234567897', 'provider', 1, 1, NULL, '2025-04-22 20:16:51', '2025-04-22 20:25:22', NULL, NULL, NULL, NULL, 0),
(17, 'trash@gmail.com', '$argon2id$v=19$m=65536,t=4,p=3$cmJXQ2pBVjlVbzR5eVgxSQ$IsmEEXxajVGZfigueXnsUqfYDdPRIjHXCGf1AEk81G8', 'Kaleb', 'Holley', '(715) 619-1363', 'patient', 1, 1, '2025-05-04 13:49:29', '2025-05-04 13:48:54', NULL, NULL, NULL, NULL, NULL, 0),
(18, 'kalebholley43@gmail.com', '$2y$10$JYJGb6bvRyph/Ciqn9pA9eu6Jl7e3PAtPKTU64dhhUbGRVXnpf4O.', 'Kaleb', 'Holley', '(715) 619-1363', 'patient', 1, 1, '2025-05-04 13:54:45', '2025-05-04 13:54:30', '2025-05-04 13:54:58', NULL, NULL, NULL, NULL, 0);

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
  `waitlist_id` int(11) NOT NULL,
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
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

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
  ADD CONSTRAINT `provider_availability_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

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
  ADD CONSTRAINT `waitlist_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
