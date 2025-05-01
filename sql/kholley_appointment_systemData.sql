-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 08:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS=0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kholley_appointment_system`
--

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `role`, `is_active`, `email_verified_at`, `created_at`, `last_login`, `verification_token`, `reset_token`, `reset_token_expires`, `token_expires`, `password_change_required`) VALUES
(1, 'patient@example.com', '$2y$10$example_hash', 'John', 'Doe', '2120001234', 'patient', 1, NULL, '2025-04-17 08:59:28', NULL, NULL, NULL, NULL, NULL, 0),
(2, 'provider@example.com', '$2y$10$example_hash', 'Dr. Smith', 'MD', NULL, 'provider', 1, NULL, '2025-04-17 08:59:28', NULL, NULL, NULL, NULL, NULL, 0),
(3, 'admin@example.com', '$2y$10$q/l96RJfI9YX3D1jtI/ZBue7hSr.F6/zVjzrOI1loV9lNj3W.hMY.', 'Admin', 'User', NULL, 'admin', 1, NULL, '2025-04-17 08:59:28', NULL, NULL, NULL, NULL, NULL, 0),
(10, 'Kholley@student.cvtc.edu', '$2y$10$xiDwOmotNFAOn.5R0XJ1huUpLb681/phtw/TCkT9wlddp9s1DiRnG', 'Kaleb', 'Holley', '7156191363', 'patient', 1, '2025-04-22 09:54:17', '2025-04-22 09:54:02', '2025-04-22 09:54:47', NULL, 'd9472a925ebd1c3138bb4731edcaf0a207e72c66c1bd00be9783e31f632bb69e', '2025-04-22 15:55:40', '2025-04-23 14:54:02', 0),
(16, 'john@doe.com', '$2y$10$nxPUCopIWXPZw442f6B4UOJv.H/6wty9qrJmSqXzYPCmo6i6Zn8sW', 'john', 'doe', '1234567897', 'provider', 1, NULL, '2025-04-22 20:16:51', '2025-04-22 20:25:22', NULL, NULL, NULL, NULL, 0);

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `name`, `description`, `duration`, `price`, `is_active`, `created_at`) VALUES
(1, 'Regular Checkup', 'Standard medical examination', 30, 75.00, 1, '2025-04-22 23:34:16'),
(2, 'Consultation', 'Medical consultation with specialist', 45, 125.00, 1, '2025-04-22 23:34:16');

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `description`, `category`, `created_at`, `ip_address`, `details`, `related_id`, `related_type`) VALUES
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
(10, 1, 16, 2, '2025-04-30', '09:32:00', '10:17:00', 'scheduled', 'in_person', '', '', 0, NULL, NULL, '2025-04-26 16:32:36', '2025-04-26 16:32:36');

--
-- Dumping data for table `appointment_history`
--

INSERT INTO `appointment_history` (`history_id`, `appointment_id`, `action`, `changed_fields`, `old_values`, `new_values`, `user_id`, `created_at`) VALUES
(1, 1, 'created', NULL, NULL, NULL, 1, '2025-04-17 08:59:28');

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

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`availability_id`, `provider_id`, `availability_date`, `start_time`, `end_time`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 2, '2025-04-15', '09:00:00', '12:00:00', 1, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(2, 2, '2025-04-16', '14:00:00', '17:00:00', 1, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(4, 2, '2025-04-18', '10:04:00', '22:04:00', 1, '2025-04-17 10:05:05', '2025-04-17 10:05:05'),
(5, 2, '2025-04-17', '10:11:00', '22:11:00', 1, '2025-04-17 10:11:09', '2025-04-17 10:11:09');

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

--
-- Dumping data for table `patient_profiles`
--

INSERT INTO `patient_profiles` (`patient_id`, `user_id`, `phone`, `date_of_birth`, `address`, `emergency_contact`, `emergency_contact_phone`, `medical_conditions`, `medical_history`, `insurance_info`, `created_at`, `updated_at`) VALUES
(1, 1, '2120001234', '1997-10-31', '123 Main Street', 'example', '1112223333', 'Na', NULL, '{\"provider\":\"Example\",\"policy_number\":\"8778599455d\"}', '2025-04-23 00:49:59', '2025-04-26 22:46:30');

--
-- Dumping data for table `provider_availability`
--

INSERT INTO `provider_availability` (`availability_id`, `provider_id`, `available_date`, `start_time`, `end_time`, `is_available`, `created_at`) VALUES
(1, 2, '2025-04-15', '09:00:00', '12:00:00', 1, '2025-04-17 08:59:28'),
(2, 2, '2025-04-16', '14:00:00', '17:00:00', 1, '2025-04-17 08:59:28'),
(4, 2, '2025-04-18', '10:04:00', '22:04:00', 1, '2025-04-17 10:05:05'),
(5, 2, '2025-04-17', '10:11:00', '22:11:00', 1, '2025-04-17 10:11:09');

--
-- Dumping data for table `provider_profiles`
--

INSERT INTO `provider_profiles` (`profile_id`, `provider_id`, `specialization`, `title`, `bio`, `accepting_new_patients`, `max_patients_per_day`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 16, 'Practisioner', 'MD', 'Hello', 1, 20, NULL, '2025-04-23 01:16:51', '2025-04-23 01:16:51');

--
-- Dumping data for table `provider_services`
--

INSERT INTO `provider_services` (`provider_service_id`, `provider_id`, `service_id`, `custom_duration`, `custom_notes`, `created_at`) VALUES
(1, 2, 1, NULL, NULL, '2025-04-17 08:59:28'),
(2, 2, 2, NULL, NULL, '2025-04-17 08:59:28');

--
-- Dumping data for table `recurring_schedules`
--

INSERT INTO `recurring_schedules` (`schedule_id`, `provider_id`, `day_of_week`, `start_time`, `end_time`, `is_active`, `effective_from`, `effective_until`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '09:00:00', '17:00:00', 1, '2025-04-01', NULL, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(2, 2, 3, '09:00:00', '17:00:00', 1, '2025-04-01', NULL, '2025-04-17 08:59:28', '2025-04-17 08:59:28'),
(3, 2, 5, '09:00:00', '12:00:00', 1, '2025-04-01', NULL, '2025-04-17 08:59:28', '2025-04-17 08:59:28');

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'appointment_reminder_time', '24', 'Hours before appointment to send reminder', '2025-04-17 08:59:28'),
(2, 'enable_sms_notifications', 'true', 'Enable SMS notifications', '2025-04-17 08:59:28'),
(3, 'enable_email_notifications', 'true', 'Enable email notifications', '2025-04-17 08:59:28'),
(4, 'business_hours', '{\"monday\":\"9:00-17:00\",\"tuesday\":\"9:00-17:00\",\"wednesday\":\"9:00-17:00\",\"thursday\":\"9:00-17:00\",\"friday\":\"9:00-13:00\"}', 'Business hours by day', '2025-04-17 08:59:28');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
