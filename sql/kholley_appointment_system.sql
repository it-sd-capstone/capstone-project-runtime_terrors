-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 02:42 AM
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
) ENGINE=InnoDB AUTO_INCREMENT=473 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `description`, `category`, `created_at`, `ip_address`, `details`, `related_id`, `related_type`) VALUES
(282, 3, 'Auth: login_success', 'authentication', '2025-05-13 18:22:21', '127.0.0.1', NULL, NULL, NULL),
(283, 3, 'Auth: logout', 'authentication', '2025-05-13 18:26:59', '127.0.0.1', NULL, NULL, NULL),
(284, 2, 'Auth: logout', 'authentication', '2025-05-13 18:27:19', '127.0.0.1', NULL, NULL, NULL),
(285, 0, 'Admin created new provider: Smith Martin', '3', '2025-05-13 18:30:26', '127.0.0.1', NULL, NULL, NULL),
(286, 3, 'Auth: logout', 'authentication', '2025-05-13 18:32:31', '127.0.0.1', NULL, NULL, NULL),
(287, 63, 'Auth: logout', 'authentication', '2025-05-13 18:34:03', '127.0.0.1', NULL, NULL, NULL),
(288, 3, 'Auth: logout', 'authentication', '2025-05-13 18:35:09', '127.0.0.1', NULL, NULL, NULL),
(289, 63, 'Auth: logout', 'authentication', '2025-05-13 18:35:13', '127.0.0.1', NULL, NULL, NULL),
(290, 0, 'Admin created new provider: Samantha Lee', '3', '2025-05-13 18:42:29', '127.0.0.1', NULL, NULL, NULL),
(291, 0, 'Admin created new provider: Samantha Smith', '3', '2025-05-13 19:07:03', '127.0.0.1', NULL, NULL, NULL),
(292, 3, 'Auth: logout', 'authentication', '2025-05-13 19:08:06', '127.0.0.1', NULL, NULL, NULL),
(293, NULL, 'Auth: login_failed - Email: provider@example.com', 'authentication', '2025-05-13 19:08:34', '127.0.0.1', NULL, NULL, NULL),
(294, 65, 'Auth: logout', 'authentication', '2025-05-13 19:09:21', '127.0.0.1', NULL, NULL, NULL),
(295, 65, 'Auth: login_failed - Email not verified: provider@example.com', 'authentication', '2025-05-13 19:09:36', '127.0.0.1', NULL, NULL, NULL),
(296, 3, 'User deleted', 'security', '2025-05-13 19:44:34', '127.0.0.1', '{\"deleted_user_id\":\"65\",\"performed_by\":3,\"user_data\":null}', 65, 'user'),
(297, 0, 'Admin created new provider: Samantha Smith', '3', '2025-05-13 19:44:51', '127.0.0.1', NULL, NULL, NULL),
(298, 3, 'Auth: logout', 'authentication', '2025-05-13 19:45:00', '127.0.0.1', NULL, NULL, NULL),
(299, 66, 'Auth: login_failed - Email not verified: provider@example.com', 'authentication', '2025-05-13 19:45:06', '127.0.0.1', NULL, NULL, NULL),
(300, 0, 'Admin created new provider: stan smith', '3', '2025-05-13 19:48:19', '127.0.0.1', NULL, NULL, NULL),
(301, 3, 'Auth: logout', 'authentication', '2025-05-13 19:48:24', '127.0.0.1', NULL, NULL, NULL),
(302, 67, 'Auth: login_failed - Email not verified: provider2@example.com', 'authentication', '2025-05-13 19:48:31', '127.0.0.1', NULL, NULL, NULL),
(303, 1, 'Auth: logout', 'authentication', '2025-05-13 19:49:43', '::1', NULL, NULL, NULL),
(304, 3, 'User deleted', 'security', '2025-05-13 19:54:17', '127.0.0.1', '{\"deleted_user_id\":\"67\",\"performed_by\":3,\"user_data\":null}', 67, 'user'),
(305, 3, 'User deleted', 'security', '2025-05-13 19:54:25', '127.0.0.1', '{\"deleted_user_id\":\"66\",\"performed_by\":3,\"user_data\":null}', 66, 'user'),
(306, 0, 'Admin created new provider: Samantha Smith', '3', '2025-05-13 19:55:56', '127.0.0.1', NULL, NULL, NULL),
(307, 3, 'Auth: logout', 'authentication', '2025-05-13 19:56:04', '127.0.0.1', NULL, NULL, NULL),
(308, 68, 'Auth: logout', 'authentication', '2025-05-13 19:56:33', '127.0.0.1', NULL, NULL, NULL),
(309, 68, 'Auth: login_success', 'authentication', '2025-05-13 19:56:46', '127.0.0.1', NULL, NULL, NULL),
(310, 68, 'Auth: logout', 'authentication', '2025-05-13 19:56:48', '127.0.0.1', NULL, NULL, NULL),
(311, 0, 'Admin created new provider: Stan Smith', '3', '2025-05-13 19:57:20', '127.0.0.1', NULL, NULL, NULL),
(312, 3, 'Auth: logout', 'authentication', '2025-05-13 19:58:03', '127.0.0.1', NULL, NULL, NULL),
(313, 69, 'Auth: logout', 'authentication', '2025-05-13 19:58:28', '127.0.0.1', NULL, NULL, NULL),
(314, 68, 'Auth: logout', 'authentication', '2025-05-13 19:58:40', '127.0.0.1', NULL, NULL, NULL),
(315, 0, 'Admin created new provider: Johnny  Lee', '3', '2025-05-13 19:59:28', '127.0.0.1', NULL, NULL, NULL),
(316, 3, 'Auth: logout', 'authentication', '2025-05-13 19:59:41', '127.0.0.1', NULL, NULL, NULL),
(317, 70, 'Auth: logout', 'authentication', '2025-05-13 20:00:06', '127.0.0.1', NULL, NULL, NULL),
(318, 0, 'Admin created new provider: Omar Clackson', '3', '2025-05-13 20:01:01', '127.0.0.1', NULL, NULL, NULL),
(319, 3, 'Auth: logout', 'authentication', '2025-05-13 20:01:07', '127.0.0.1', NULL, NULL, NULL),
(320, 71, 'Auth: logout', 'authentication', '2025-05-13 20:01:39', '127.0.0.1', NULL, NULL, NULL),
(321, 0, 'Admin created new provider: Tammy Lee', '3', '2025-05-13 20:02:48', '127.0.0.1', NULL, NULL, NULL),
(322, 3, 'Auth: logout', 'authentication', '2025-05-13 20:02:54', '127.0.0.1', NULL, NULL, NULL),
(323, 72, 'Auth: logout', 'authentication', '2025-05-13 20:03:22', '127.0.0.1', NULL, NULL, NULL),
(324, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-13 20:05:47', '127.0.0.1', NULL, NULL, NULL),
(325, NULL, 'User: created (User ID: 73)', 'user', '2025-05-13 20:05:47', '127.0.0.1', NULL, NULL, NULL),
(326, 73, 'Auth: login_success', 'authentication', '2025-05-13 20:06:13', '127.0.0.1', NULL, NULL, NULL),
(327, 73, 'Auth: logout', 'authentication', '2025-05-13 20:06:18', '127.0.0.1', NULL, NULL, NULL),
(328, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-13 20:07:04', '127.0.0.1', NULL, NULL, NULL),
(329, NULL, 'User: created (User ID: 74)', 'user', '2025-05-13 20:07:04', '127.0.0.1', NULL, NULL, NULL),
(330, 74, 'Auth: login_success', 'authentication', '2025-05-13 20:07:32', '127.0.0.1', NULL, NULL, NULL),
(331, 74, 'Auth: logout', 'authentication', '2025-05-13 20:07:35', '127.0.0.1', NULL, NULL, NULL),
(332, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-13 20:08:21', '127.0.0.1', NULL, NULL, NULL),
(333, NULL, 'User: created (User ID: 75)', 'user', '2025-05-13 20:08:21', '127.0.0.1', NULL, NULL, NULL),
(334, 75, 'Auth: login_success', 'authentication', '2025-05-13 20:08:41', '127.0.0.1', NULL, NULL, NULL),
(335, 75, 'Auth: logout', 'authentication', '2025-05-13 20:08:45', '127.0.0.1', NULL, NULL, NULL),
(336, 3, 'Auth: logout', 'authentication', '2025-05-13 21:45:16', '::1', NULL, NULL, NULL),
(337, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-13 21:46:15', '::1', NULL, NULL, NULL),
(338, NULL, 'User: created (User ID: 76)', 'user', '2025-05-13 21:46:15', '::1', NULL, NULL, NULL),
(339, 76, 'Auth: login_success', 'authentication', '2025-05-13 21:46:38', '::1', NULL, NULL, NULL),
(340, 76, 'Auth: logout', 'authentication', '2025-05-13 21:46:57', '::1', NULL, NULL, NULL),
(341, 3, 'Auth: logout', 'authentication', '2025-05-13 22:18:17', '127.0.0.1', NULL, NULL, NULL),
(342, 3, 'Auth: logout', 'authentication', '2025-05-13 22:24:56', '127.0.0.1', NULL, NULL, NULL),
(343, 68, 'Auth: login_success', 'authentication', '2025-05-13 22:25:08', '127.0.0.1', NULL, NULL, NULL),
(344, 68, 'Auth: logout', 'authentication', '2025-05-13 22:25:11', '127.0.0.1', NULL, NULL, NULL),
(345, 3, 'Auth: logout', 'authentication', '2025-05-13 22:29:04', '127.0.0.1', NULL, NULL, NULL),
(346, NULL, 'Auth: login_failed - Email: provider@example.com', 'authentication', '2025-05-13 22:29:17', '127.0.0.1', NULL, NULL, NULL),
(347, 68, 'Auth: login_success', 'authentication', '2025-05-13 22:29:35', '127.0.0.1', NULL, NULL, NULL),
(348, 68, 'Auth: logout', 'authentication', '2025-05-14 09:04:12', '127.0.0.1', NULL, NULL, NULL),
(349, 68, 'Auth: logout', 'authentication', '2025-05-14 09:04:52', '127.0.0.1', NULL, NULL, NULL),
(350, 68, 'Auth: logout', 'authentication', '2025-05-14 11:49:55', '127.0.0.1', NULL, NULL, NULL),
(351, 69, 'Auth: login_success', 'authentication', '2025-05-14 11:50:15', '127.0.0.1', NULL, NULL, NULL),
(352, 69, 'Auth: logout', 'authentication', '2025-05-14 11:51:27', '127.0.0.1', NULL, NULL, NULL),
(353, 70, 'Auth: login_success', 'authentication', '2025-05-14 11:51:47', '127.0.0.1', NULL, NULL, NULL),
(354, 70, 'Auth: logout', 'authentication', '2025-05-14 11:55:35', '127.0.0.1', NULL, NULL, NULL),
(355, 70, 'Auth: login_success', 'authentication', '2025-05-14 11:55:56', '127.0.0.1', NULL, NULL, NULL),
(356, 70, 'Auth: logout', 'authentication', '2025-05-14 11:56:15', '127.0.0.1', NULL, NULL, NULL),
(357, 71, 'Auth: login_success', 'authentication', '2025-05-14 11:56:36', '127.0.0.1', NULL, NULL, NULL),
(358, 71, 'Auth: logout', 'authentication', '2025-05-14 11:57:30', '127.0.0.1', NULL, NULL, NULL),
(359, 72, 'Auth: login_success', 'authentication', '2025-05-14 11:57:51', '127.0.0.1', NULL, NULL, NULL),
(360, 72, 'Auth: logout', 'authentication', '2025-05-14 11:58:55', '127.0.0.1', NULL, NULL, NULL),
(361, 73, 'Auth: logout', 'authentication', '2025-05-14 12:23:28', '127.0.0.1', NULL, NULL, NULL),
(362, 3, 'Auth: login_success', 'authentication', '2025-05-14 12:23:46', '127.0.0.1', NULL, NULL, NULL),
(363, 3, 'Auth: logout', 'authentication', '2025-05-14 12:46:50', '127.0.0.1', NULL, NULL, NULL),
(364, 0, 'Patient scheduled appointment with provider #68', '73', '2025-05-14 12:47:23', '127.0.0.1', NULL, NULL, NULL),
(365, 73, 'Auth: logout', 'authentication', '2025-05-14 12:48:42', '127.0.0.1', NULL, NULL, NULL),
(366, 73, 'Auth: logout', 'authentication', '2025-05-14 12:54:12', '127.0.0.1', NULL, NULL, NULL),
(367, 68, 'Auth: logout', 'authentication', '2025-05-14 12:56:11', '127.0.0.1', NULL, NULL, NULL),
(368, 3, 'Auth: logout', 'authentication', '2025-05-14 12:57:27', '127.0.0.1', NULL, NULL, NULL),
(369, 68, 'Auth: logout', 'authentication', '2025-05-14 13:10:26', '127.0.0.1', NULL, NULL, NULL),
(370, 3, 'Auth: logout', 'authentication', '2025-05-14 13:13:02', '127.0.0.1', NULL, NULL, NULL),
(371, 68, 'Auth: logout', 'authentication', '2025-05-14 13:17:10', '127.0.0.1', NULL, NULL, NULL),
(372, 73, 'Auth: logout', 'authentication', '2025-05-14 13:24:05', '127.0.0.1', NULL, NULL, NULL),
(373, 68, 'Auth: logout', 'authentication', '2025-05-14 13:30:27', '127.0.0.1', NULL, NULL, NULL),
(374, 3, 'User deleted', 'security', '2025-05-14 14:17:53', '127.0.0.1', '{\"deleted_user_id\":\"77\",\"performed_by\":3,\"user_data\":null}', 77, 'user'),
(375, 3, 'User deleted', 'security', '2025-05-14 14:23:20', '127.0.0.1', '{\"deleted_user_id\":\"78\",\"performed_by\":3,\"user_data\":null}', 78, 'user'),
(376, 0, 'Admin created new provider: Test Test', '3', '2025-05-14 14:32:22', '127.0.0.1', NULL, NULL, NULL),
(377, 3, 'Auth: logout', 'authentication', '2025-05-14 14:32:35', '127.0.0.1', NULL, NULL, NULL),
(378, 79, 'Auth: logout', 'authentication', '2025-05-14 14:39:12', '127.0.0.1', NULL, NULL, NULL),
(379, 3, 'User deleted', 'security', '2025-05-14 14:51:07', '127.0.0.1', '{\"deleted_user_id\":\"79\",\"performed_by\":3,\"user_data\":null}', 79, 'user'),
(380, 0, 'Admin created new provider: Test Test', '3', '2025-05-14 14:51:39', '127.0.0.1', NULL, NULL, NULL),
(381, 3, 'User deleted', 'security', '2025-05-14 14:52:08', '127.0.0.1', '{\"deleted_user_id\":\"80\",\"performed_by\":3,\"user_data\":null}', 80, 'user'),
(382, 0, 'Admin created new provider: Test Test', '3', '2025-05-14 14:54:03', '127.0.0.1', NULL, NULL, NULL),
(383, 3, 'User deleted', 'security', '2025-05-14 14:54:32', '127.0.0.1', '{\"deleted_user_id\":\"81\",\"performed_by\":3,\"user_data\":null}', 81, 'user'),
(384, 3, 'User deleted', 'security', '2025-05-14 15:48:23', '127.0.0.1', '{\"deleted_user_id\":\"82\",\"performed_by\":3,\"user_data\":null}', 82, 'user'),
(385, 3, 'User deleted', 'security', '2025-05-14 15:49:15', '127.0.0.1', '{\"deleted_user_id\":\"83\",\"performed_by\":3,\"user_data\":null}', 83, 'user'),
(386, 3, 'Auth: logout', 'authentication', '2025-05-14 15:56:57', '127.0.0.1', NULL, NULL, NULL),
(387, 68, 'Auth: logout', 'authentication', '2025-05-14 15:57:19', '127.0.0.1', NULL, NULL, NULL),
(388, 73, 'Auth: logout', 'authentication', '2025-05-14 16:02:25', '127.0.0.1', NULL, NULL, NULL),
(389, 3, 'Auth: logout', 'authentication', '2025-05-14 16:03:18', '127.0.0.1', NULL, NULL, NULL),
(390, 69, 'Auth: login_success', 'authentication', '2025-05-14 16:03:36', '127.0.0.1', NULL, NULL, NULL),
(391, 69, 'Auth: logout', 'authentication', '2025-05-14 16:06:08', '127.0.0.1', NULL, NULL, NULL),
(392, 73, 'Auth: logout', 'authentication', '2025-05-14 16:20:53', '127.0.0.1', NULL, NULL, NULL),
(393, 68, 'Auth: logout', 'authentication', '2025-05-14 16:21:30', '127.0.0.1', NULL, NULL, NULL),
(394, 69, 'Auth: login_success', 'authentication', '2025-05-14 16:21:48', '127.0.0.1', NULL, NULL, NULL),
(395, 69, 'Appointment: notes_updated (ID: 56)', 'appointment', '2025-05-14 16:22:34', '127.0.0.1', '{\"updated_by\":69,\"updated_by_role\":\"provider\",\"notes\":\"note\"}', 56, 'appointment'),
(396, 69, 'Auth: logout', 'authentication', '2025-05-14 16:24:23', '127.0.0.1', NULL, NULL, NULL),
(397, 68, 'Auth: logout', 'authentication', '2025-05-14 16:26:04', '127.0.0.1', NULL, NULL, NULL),
(398, NULL, 'Auth: login_failed - Email: provider2@example.com', 'authentication', '2025-05-14 16:26:46', '127.0.0.1', NULL, NULL, NULL),
(399, 69, 'Auth: login_success', 'authentication', '2025-05-14 16:26:56', '127.0.0.1', NULL, NULL, NULL),
(400, 69, 'Appointment: notes_updated (ID: 56)', 'appointment', '2025-05-14 16:27:44', '127.0.0.1', '{\"updated_by\":69,\"updated_by_role\":\"provider\",\"notes\":\"note\"}', 56, 'appointment'),
(401, 69, 'Appointment: notes_updated (ID: 56)', 'appointment', '2025-05-14 16:29:41', '127.0.0.1', '{\"updated_by\":69,\"updated_by_role\":\"provider\",\"notes\":\"note\"}', 56, 'appointment'),
(402, 69, 'Auth: logout', 'authentication', '2025-05-14 16:34:57', '127.0.0.1', NULL, NULL, NULL),
(403, 73, 'Auth: logout', 'authentication', '2025-05-14 16:41:54', '127.0.0.1', NULL, NULL, NULL),
(404, 69, 'Auth: login_success', 'authentication', '2025-05-14 16:42:16', '127.0.0.1', NULL, NULL, NULL),
(405, 69, 'Appointment: notes_updated (ID: 56)', 'appointment', '2025-05-14 16:43:42', '127.0.0.1', '{\"updated_by\":69,\"updated_by_role\":\"provider\",\"notes\":\"note\"}', 56, 'appointment'),
(406, 69, 'Auth: logout', 'authentication', '2025-05-14 16:50:56', '127.0.0.1', NULL, NULL, NULL),
(407, 73, 'Auth: logout', 'authentication', '2025-05-14 16:59:50', '127.0.0.1', NULL, NULL, NULL),
(408, 69, 'Auth: login_success', 'authentication', '2025-05-14 17:00:06', '127.0.0.1', NULL, NULL, NULL),
(409, 69, 'Auth: logout', 'authentication', '2025-05-14 17:31:53', '127.0.0.1', NULL, NULL, NULL),
(410, 0, 'Patient scheduled appointment with provider #68', '73', '2025-05-14 17:32:36', '127.0.0.1', NULL, NULL, NULL),
(411, 73, 'Appointment: rescheduled (ID: 56)', 'appointment', '2025-05-14 18:19:07', '127.0.0.1', '{\"previous_date\":\"2025-05-14\",\"previous_time\":\"07:00:00\",\"new_date\":\"2025-05-20\",\"new_time\":\"08:00:00\",\"rescheduled_by\":73,\"rescheduled_by_role\":\"patient\"}', 56, 'appointment'),
(412, 73, 'Appointment: rescheduled (ID: 56)', 'appointment', '2025-05-14 18:25:23', '127.0.0.1', '{\"previous_date\":\"2025-05-20\",\"previous_time\":\"08:00:00\",\"new_date\":\"2025-05-20\",\"new_time\":\"10:35:00\",\"rescheduled_by\":73,\"rescheduled_by_role\":\"patient\"}', 56, 'appointment'),
(413, 73, 'Appointment: rescheduled (ID: 57)', 'appointment', '2025-05-14 18:27:38', '127.0.0.1', '{\"previous_date\":\"2025-05-23\",\"previous_time\":\"07:00:00\",\"new_date\":\"2025-05-21\",\"new_time\":\"08:15:00\",\"rescheduled_by\":73,\"rescheduled_by_role\":\"patient\"}', 57, 'appointment'),
(414, 73, 'Appointment: rescheduled (ID: 56)', 'appointment', '2025-05-14 18:32:31', '127.0.0.1', '{\"previous_date\":\"2025-05-20\",\"previous_time\":\"10:35:00\",\"new_date\":\"2025-05-22\",\"new_time\":\"08:00:00\",\"rescheduled_by\":73,\"rescheduled_by_role\":\"patient\"}', 56, 'appointment'),
(415, 73, 'Appointment: rescheduled (ID: 57)', 'appointment', '2025-05-14 18:39:48', '127.0.0.1', '{\"previous_date\":\"2025-05-21\",\"previous_time\":\"08:15:00\",\"new_date\":\"2025-05-23\",\"new_time\":\"16:15:00\",\"rescheduled_by\":73,\"rescheduled_by_role\":\"patient\"}', 57, 'appointment'),
(416, 73, 'Auth: logout', 'authentication', '2025-05-14 18:41:13', '127.0.0.1', NULL, NULL, NULL),
(417, 68, 'Auth: logout', 'authentication', '2025-05-14 18:45:40', '127.0.0.1', NULL, NULL, NULL),
(418, 73, 'Appointment: rescheduled (ID: 56)', 'appointment', '2025-05-14 18:46:27', '127.0.0.1', '{\"previous_date\":\"2025-05-22\",\"previous_time\":\"08:00:00\",\"new_date\":\"2025-05-22\",\"new_time\":\"14:20:00\",\"rescheduled_by\":73,\"rescheduled_by_role\":\"patient\"}', 56, 'appointment'),
(419, 73, 'Auth: logout', 'authentication', '2025-05-14 19:17:05', '127.0.0.1', NULL, NULL, NULL),
(420, 3, 'Auth: logout', 'authentication', '2025-05-14 19:43:00', '127.0.0.1', NULL, NULL, NULL),
(421, 73, 'Appointment: rescheduled (ID: 56)', 'appointment', '2025-05-14 19:44:08', '127.0.0.1', '{\"previous_date\":\"2025-05-22\",\"previous_time\":\"14:20:00\",\"new_date\":\"2025-05-18\",\"new_time\":\"11:40:00\",\"rescheduled_by\":73,\"rescheduled_by_role\":\"patient\"}', 56, 'appointment'),
(422, 73, 'Auth: logout', 'authentication', '2025-05-14 19:44:36', '127.0.0.1', NULL, NULL, NULL),
(423, 68, 'Auth: logout', 'authentication', '2025-05-14 19:45:34', '127.0.0.1', NULL, NULL, NULL),
(424, 3, 'Auth: logout', 'authentication', '2025-05-14 19:57:45', '127.0.0.1', NULL, NULL, NULL),
(425, 68, 'Appointment: notes_updated (ID: 57)', 'appointment', '2025-05-14 19:59:10', '127.0.0.1', '{\"updated_by\":68,\"updated_by_role\":\"provider\",\"notes\":\"note\"}', 57, 'appointment'),
(426, 68, 'Auth: logout', 'authentication', '2025-05-14 20:08:30', '127.0.0.1', NULL, NULL, NULL),
(427, 73, 'Auth: logout', 'authentication', '2025-05-14 20:55:14', '127.0.0.1', NULL, NULL, NULL),
(428, 3, 'Auth: logout', 'authentication', '2025-05-14 21:37:29', '127.0.0.1', NULL, NULL, NULL),
(429, 68, 'Auth: logout', 'authentication', '2025-05-14 21:52:03', '127.0.0.1', NULL, NULL, NULL),
(430, 3, 'Auth: logout', 'authentication', '2025-05-14 22:34:01', '127.0.0.1', NULL, NULL, NULL),
(431, 0, 'Patient scheduled appointment with provider #69', '73', '2025-05-14 22:34:36', '127.0.0.1', NULL, NULL, NULL),
(432, 0, 'Patient scheduled appointment with provider #70', '73', '2025-05-14 23:15:15', '127.0.0.1', NULL, NULL, NULL),
(433, 73, 'Auth: logout', 'authentication', '2025-05-14 23:36:16', '127.0.0.1', NULL, NULL, NULL),
(434, 0, 'Patient scheduled appointment with provider #70', '73', '2025-05-14 23:38:14', '127.0.0.1', NULL, NULL, NULL),
(435, 0, 'Patient scheduled appointment with provider #70', '73', '2025-05-14 23:51:19', '127.0.0.1', NULL, NULL, NULL),
(436, 73, 'Appointment: rescheduled (ID: 58)', 'appointment', '2025-05-14 23:55:02', '127.0.0.1', '{\"previous_date\":\"2025-05-15\",\"previous_time\":\"07:00:00\",\"new_date\":\"2025-05-20\",\"new_time\":\"11:40:00\",\"rescheduled_by\":73,\"rescheduled_by_role\":\"patient\"}', 58, 'appointment'),
(437, 68, 'Auth: logout', 'authentication', '2025-05-15 06:53:43', '127.0.0.1', NULL, NULL, NULL),
(438, 3, 'User deleted', 'security', '2025-05-15 06:58:38', '127.0.0.1', '{\"deleted_user_id\":\"84\",\"performed_by\":3,\"user_data\":null}', 84, 'user'),
(439, 3, 'Auth: logout', 'authentication', '2025-05-15 07:14:34', '127.0.0.1', NULL, NULL, NULL),
(440, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-15 07:16:18', '127.0.0.1', NULL, NULL, NULL),
(441, NULL, 'User: created (User ID: 85)', 'user', '2025-05-15 07:16:18', '127.0.0.1', NULL, NULL, NULL),
(442, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-15 07:21:13', '127.0.0.1', NULL, NULL, NULL),
(443, NULL, 'User: created (User ID: 86)', 'user', '2025-05-15 07:21:13', '127.0.0.1', NULL, NULL, NULL),
(444, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-15 07:25:35', '127.0.0.1', NULL, NULL, NULL),
(445, NULL, 'User: created (User ID: 87)', 'user', '2025-05-15 07:25:35', '127.0.0.1', NULL, NULL, NULL),
(446, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-15 07:41:06', '127.0.0.1', NULL, NULL, NULL),
(447, NULL, 'User: created (User ID: 88)', 'user', '2025-05-15 07:41:06', '127.0.0.1', NULL, NULL, NULL),
(448, 3, 'Auth: logout', 'authentication', '2025-05-15 08:25:53', '127.0.0.1', NULL, NULL, NULL),
(449, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-15 09:10:26', '127.0.0.1', NULL, NULL, NULL),
(450, NULL, 'User: created (User ID: 89)', 'user', '2025-05-15 09:10:26', '127.0.0.1', NULL, NULL, NULL),
(451, 89, 'Auth: login_success', 'authentication', '2025-05-15 09:11:09', '127.0.0.1', NULL, NULL, NULL),
(452, 89, 'Auth: logout', 'authentication', '2025-05-15 09:11:19', '127.0.0.1', NULL, NULL, NULL),
(453, 3, 'Auth: logout', 'authentication', '2025-05-15 09:34:23', '127.0.0.1', NULL, NULL, NULL),
(454, 3, 'Auth: logout', 'authentication', '2025-05-15 09:35:14', '127.0.0.1', NULL, NULL, NULL),
(455, 3, 'User deleted', 'security', '2025-05-15 09:36:05', '127.0.0.1', '{\"deleted_user_id\":\"89\",\"performed_by\":3,\"user_data\":null}', 89, 'user'),
(456, 3, 'User deleted', 'security', '2025-05-15 09:36:18', '127.0.0.1', '{\"deleted_user_id\":\"88\",\"performed_by\":3,\"user_data\":null}', 88, 'user'),
(457, 3, 'User deleted', 'security', '2025-05-15 09:36:35', '127.0.0.1', '{\"deleted_user_id\":\"87\",\"performed_by\":3,\"user_data\":null}', 87, 'user'),
(458, 3, 'User deleted', 'security', '2025-05-15 09:36:46', '127.0.0.1', '{\"deleted_user_id\":\"86\",\"performed_by\":3,\"user_data\":null}', 86, 'user'),
(459, 3, 'User deleted', 'security', '2025-05-15 09:36:58', '127.0.0.1', '{\"deleted_user_id\":\"85\",\"performed_by\":3,\"user_data\":null}', 85, 'user'),
(460, 3, 'Auth: logout', 'authentication', '2025-05-15 09:38:20', '127.0.0.1', NULL, NULL, NULL),
(461, 3, 'Auth: logout', 'authentication', '2025-05-15 17:28:51', '127.0.0.1', NULL, NULL, NULL),
(462, 3, 'Auth: logout', 'authentication', '2025-05-15 18:02:07', '127.0.0.1', NULL, NULL, NULL),
(463, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-15 18:04:17', '127.0.0.1', NULL, NULL, NULL),
(464, NULL, 'User: created (User ID: 90)', 'user', '2025-05-15 18:04:17', '127.0.0.1', NULL, NULL, NULL),
(465, 3, 'Auth: logout', 'authentication', '2025-05-15 18:25:45', '127.0.0.1', NULL, NULL, NULL),
(466, NULL, 'Auth: registered - New patient account created, verification email failed', 'authentication', '2025-05-15 18:26:32', '127.0.0.1', NULL, NULL, NULL),
(467, NULL, 'User: created (User ID: 91)', 'user', '2025-05-15 18:26:32', '127.0.0.1', NULL, NULL, NULL),
(468, NULL, 'Auth: registered - New patient account created, verification email sent', 'authentication', '2025-05-15 18:33:36', '127.0.0.1', NULL, NULL, NULL),
(469, NULL, 'User: created (User ID: 92)', 'user', '2025-05-15 18:33:36', '127.0.0.1', NULL, NULL, NULL),
(470, 3, 'User deleted', 'security', '2025-05-15 18:34:24', '127.0.0.1', '{\"deleted_user_id\":\"92\",\"performed_by\":3,\"user_data\":null}', 92, 'user'),
(471, 3, 'User deleted', 'security', '2025-05-15 18:34:51', '127.0.0.1', '{\"deleted_user_id\":\"90\",\"performed_by\":3,\"user_data\":null}', 90, 'user'),
(472, 3, 'User deleted', 'security', '2025-05-15 18:35:14', '127.0.0.1', '{\"deleted_user_id\":\"91\",\"performed_by\":3,\"user_data\":null}', 91, 'user');

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
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `provider_id`, `service_id`, `appointment_date`, `start_time`, `end_time`, `status`, `type`, `notes`, `reason`, `reminder_sent`, `confirmed_at`, `canceled_at`, `created_at`, `updated_at`) VALUES
(56, 73, 69, 35, '2025-05-18', '11:40:00', '12:10:00', 'confirmed', 'in_person', 'note', 'Canceled by administrator', 0, NULL, '2025-05-14 21:52:27', '2025-05-14 12:47:23', '2025-05-14 21:52:40'),
(57, 73, 68, 35, '2025-05-23', '16:15:00', '16:45:00', 'confirmed', 'in_person', 'note', 'Canceled by administrator', 0, NULL, '2025-05-14 21:15:17', '2025-05-14 17:32:36', '2025-05-14 21:55:16'),
(58, 73, 69, 38, '2025-05-20', '11:40:00', '12:40:00', 'scheduled', 'in_person', '', 'visit', 0, NULL, NULL, '2025-05-14 22:34:36', '2025-05-14 23:55:02'),
(59, 73, 70, 42, '2025-05-14', '07:00:00', '07:40:00', 'scheduled', 'in_person', '', 'n', 0, NULL, NULL, '2025-05-14 23:15:14', '2025-05-14 23:15:14'),
(60, 73, 70, 42, '2025-05-16', '07:00:00', '07:40:00', 'scheduled', 'in_person', '', 'n', 0, NULL, NULL, '2025-05-14 23:38:14', '2025-05-14 23:38:14'),
(61, 73, 70, 42, '2025-05-16', '08:25:00', '09:05:00', 'scheduled', 'in_person', '', 'm', 0, NULL, NULL, '2025-05-14 23:51:19', '2025-05-14 23:51:19');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  KEY `appointment_id` (`appointment_id`),
  KEY `idx_notifications_user_id` (`user_id`),
  KEY `idx_notifications_is_read` (`is_read`),
  KEY `idx_notifications_created_at` (`created_at`),
  KEY `idx_notifications_appointment_id` (`appointment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `appointment_id`, `subject`, `message`, `type`, `status`, `scheduled_for`, `sent_at`, `created_at`, `is_system`, `is_read`, `audience`) VALUES
(14, 73, 56, 'Appointment Rescheduled', 'Your appointment has been rescheduled to May 22, 2025 at 8:00 AM', '', 'pending', NULL, NULL, '2025-05-14 18:32:31', 0, 1, NULL),
(15, 69, 56, 'Appointment Rescheduled', 'An appointment has been rescheduled to May 22, 2025 at 8:00 AM', '', 'pending', NULL, NULL, '2025-05-14 18:32:31', 0, 0, NULL),
(16, 73, 57, 'Appointment Rescheduled', 'Your appointment has been rescheduled to May 23, 2025 at 4:15 PM', '', 'pending', NULL, NULL, '2025-05-14 18:39:48', 0, 1, NULL),
(17, 68, 57, 'Appointment Rescheduled', 'An appointment has been rescheduled to May 23, 2025 at 4:15 PM', '', 'pending', NULL, NULL, '2025-05-14 18:39:48', 0, 0, NULL),
(18, 73, 56, 'Appointment Rescheduled', 'Your appointment has been rescheduled to May 22, 2025 at 2:20 PM', '', 'pending', NULL, NULL, '2025-05-14 18:46:27', 0, 1, NULL),
(19, 69, 56, 'Appointment Rescheduled', 'An appointment has been rescheduled to May 22, 2025 at 2:20 PM', '', 'pending', NULL, NULL, '2025-05-14 18:46:27', 0, 0, NULL),
(20, 73, 56, 'Appointment Rescheduled', 'Your appointment has been rescheduled to May 18, 2025 at 11:40 AM', '', 'pending', NULL, NULL, '2025-05-14 19:44:08', 0, 1, NULL),
(21, 69, 56, 'Appointment Rescheduled', 'An appointment has been rescheduled to May 18, 2025 at 11:40 AM', '', 'pending', NULL, NULL, '2025-05-14 19:44:08', 0, 0, NULL),
(22, 73, 58, 'Appointment Rescheduled', 'Your appointment has been rescheduled to May 20, 2025 at 11:40 AM', '', 'pending', NULL, NULL, '2025-05-14 23:55:02', 0, 0, NULL),
(23, 69, 58, 'Appointment Rescheduled', 'An appointment has been rescheduled to May 20, 2025 at 11:40 AM', '', 'pending', NULL, NULL, '2025-05-14 23:55:02', 0, 0, NULL),
(25, 3, NULL, 'System Update', 'System updated to version 2.1.0', '', '', NULL, NULL, '2025-05-16 00:25:14', 1, 0, 'admin'),
(26, 3, NULL, 'Database Backup', 'Weekly database backup completed successfully', '', '', NULL, NULL, '2025-05-16 00:25:14', 1, 0, 'admin');

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
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_profiles`
--

INSERT INTO `patient_profiles` (`patient_id`, `user_id`, `phone`, `date_of_birth`, `address`, `emergency_contact`, `emergency_contact_phone`, `medical_conditions`, `medical_history`, `insurance_info`, `created_at`, `updated_at`) VALUES
(73, 73, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-14 01:05:46', '2025-05-14 01:05:46'),
(74, 74, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-14 01:07:04', '2025-05-14 01:07:04'),
(75, 75, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-14 01:08:20', '2025-05-14 01:08:20'),
(76, 76, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-14 02:46:14', '2025-05-14 02:46:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=2676 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provider_availability`
--

INSERT INTO `provider_availability` (`availability_id`, `provider_id`, `availability_date`, `start_time`, `end_time`, `is_available`, `schedule_type`, `created_at`, `is_recurring`, `weekdays`, `max_appointments`, `service_id`) VALUES
(1938, 69, '2025-05-15', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1939, 69, '2025-05-15', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1940, 69, '2025-05-15', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1941, 69, '2025-05-15', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1942, 69, '2025-05-15', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1943, 69, '2025-05-15', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1944, 69, '2025-05-15', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1945, 69, '2025-05-15', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1946, 69, '2025-05-15', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1947, 69, '2025-05-15', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1948, 69, '2025-05-18', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1949, 69, '2025-05-18', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1950, 69, '2025-05-18', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1951, 69, '2025-05-18', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1952, 69, '2025-05-18', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1953, 69, '2025-05-18', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1954, 69, '2025-05-18', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1955, 69, '2025-05-18', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1956, 69, '2025-05-18', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1957, 69, '2025-05-18', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1958, 69, '2025-05-20', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1959, 69, '2025-05-20', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1960, 69, '2025-05-20', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1961, 69, '2025-05-20', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1962, 69, '2025-05-20', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1963, 69, '2025-05-20', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1964, 69, '2025-05-20', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1965, 69, '2025-05-20', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1966, 69, '2025-05-20', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1967, 69, '2025-05-20', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1968, 69, '2025-05-22', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1969, 69, '2025-05-22', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1970, 69, '2025-05-22', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1971, 69, '2025-05-22', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1972, 69, '2025-05-22', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1973, 69, '2025-05-22', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1974, 69, '2025-05-22', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1975, 69, '2025-05-22', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1976, 69, '2025-05-22', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1977, 69, '2025-05-22', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1978, 69, '2025-05-25', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1979, 69, '2025-05-25', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1980, 69, '2025-05-25', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1981, 69, '2025-05-25', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1982, 69, '2025-05-25', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1983, 69, '2025-05-25', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1984, 69, '2025-05-25', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1985, 69, '2025-05-25', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1986, 69, '2025-05-25', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1987, 69, '2025-05-25', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1988, 69, '2025-05-27', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1989, 69, '2025-05-27', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1990, 69, '2025-05-27', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1991, 69, '2025-05-27', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1992, 69, '2025-05-27', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1993, 69, '2025-05-27', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1994, 69, '2025-05-27', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1995, 69, '2025-05-27', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1996, 69, '2025-05-27', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1997, 69, '2025-05-27', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(1998, 69, '2025-05-29', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(1999, 69, '2025-05-29', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2000, 69, '2025-05-29', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2001, 69, '2025-05-29', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2002, 69, '2025-05-29', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2003, 69, '2025-05-29', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2004, 69, '2025-05-29', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2005, 69, '2025-05-29', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2006, 69, '2025-05-29', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2007, 69, '2025-05-29', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2008, 69, '2025-06-01', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2009, 69, '2025-06-01', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2010, 69, '2025-06-01', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2011, 69, '2025-06-01', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2012, 69, '2025-06-01', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2013, 69, '2025-06-01', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2014, 69, '2025-06-01', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2015, 69, '2025-06-01', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2016, 69, '2025-06-01', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2017, 69, '2025-06-01', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2018, 69, '2025-06-03', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2019, 69, '2025-06-03', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2020, 69, '2025-06-03', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2021, 69, '2025-06-03', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2022, 69, '2025-06-03', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2023, 69, '2025-06-03', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2024, 69, '2025-06-03', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2025, 69, '2025-06-03', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2026, 69, '2025-06-03', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2027, 69, '2025-06-03', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2028, 69, '2025-06-05', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2029, 69, '2025-06-05', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2030, 69, '2025-06-05', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2031, 69, '2025-06-05', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2032, 69, '2025-06-05', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2033, 69, '2025-06-05', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2034, 69, '2025-06-05', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2035, 69, '2025-06-05', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2036, 69, '2025-06-05', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2037, 69, '2025-06-05', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2038, 69, '2025-06-08', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2039, 69, '2025-06-08', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2040, 69, '2025-06-08', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2041, 69, '2025-06-08', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2042, 69, '2025-06-08', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2043, 69, '2025-06-08', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2044, 69, '2025-06-08', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2045, 69, '2025-06-08', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2046, 69, '2025-06-08', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2047, 69, '2025-06-08', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2048, 69, '2025-06-10', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2049, 69, '2025-06-10', '08:00:00', '08:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2050, 69, '2025-06-10', '08:50:00', '09:50:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2051, 69, '2025-06-10', '09:50:00', '10:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2052, 69, '2025-06-10', '10:40:00', '11:40:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2053, 69, '2025-06-10', '11:40:00', '12:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2054, 69, '2025-06-10', '12:30:00', '13:30:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2055, 69, '2025-06-10', '13:30:00', '14:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2056, 69, '2025-06-10', '14:20:00', '15:20:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 38),
(2057, 69, '2025-06-10', '15:20:00', '16:10:00', 1, 'availability', '2025-05-14 16:51:15', 0, NULL, 1, 39),
(2058, 70, '2025-05-14', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2059, 70, '2025-05-14', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2060, 70, '2025-05-14', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2061, 70, '2025-05-14', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2062, 70, '2025-05-14', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2063, 70, '2025-05-14', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2064, 70, '2025-05-14', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2065, 70, '2025-05-14', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2066, 70, '2025-05-14', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2067, 70, '2025-05-14', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2068, 70, '2025-05-14', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2069, 70, '2025-05-14', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2070, 70, '2025-05-14', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2071, 70, '2025-05-14', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2072, 70, '2025-05-16', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2073, 70, '2025-05-16', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2074, 70, '2025-05-16', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2075, 70, '2025-05-16', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2076, 70, '2025-05-16', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2077, 70, '2025-05-16', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2078, 70, '2025-05-16', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2079, 70, '2025-05-16', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2080, 70, '2025-05-16', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2081, 70, '2025-05-16', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2082, 70, '2025-05-16', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2083, 70, '2025-05-16', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2084, 70, '2025-05-16', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2085, 70, '2025-05-16', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2086, 70, '2025-05-19', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2087, 70, '2025-05-19', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2088, 70, '2025-05-19', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2089, 70, '2025-05-19', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2090, 70, '2025-05-19', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2091, 70, '2025-05-19', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2092, 70, '2025-05-19', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2093, 70, '2025-05-19', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2094, 70, '2025-05-19', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2095, 70, '2025-05-19', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2096, 70, '2025-05-19', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2097, 70, '2025-05-19', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2098, 70, '2025-05-19', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2099, 70, '2025-05-19', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2100, 70, '2025-05-21', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2101, 70, '2025-05-21', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2102, 70, '2025-05-21', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2103, 70, '2025-05-21', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2104, 70, '2025-05-21', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2105, 70, '2025-05-21', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2106, 70, '2025-05-21', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2107, 70, '2025-05-21', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2108, 70, '2025-05-21', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2109, 70, '2025-05-21', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2110, 70, '2025-05-21', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2111, 70, '2025-05-21', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2112, 70, '2025-05-21', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2113, 70, '2025-05-21', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2114, 70, '2025-05-23', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2115, 70, '2025-05-23', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2116, 70, '2025-05-23', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2117, 70, '2025-05-23', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2118, 70, '2025-05-23', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2119, 70, '2025-05-23', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2120, 70, '2025-05-23', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2121, 70, '2025-05-23', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2122, 70, '2025-05-23', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2123, 70, '2025-05-23', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2124, 70, '2025-05-23', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2125, 70, '2025-05-23', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2126, 70, '2025-05-23', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2127, 70, '2025-05-23', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2128, 70, '2025-05-26', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2129, 70, '2025-05-26', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2130, 70, '2025-05-26', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2131, 70, '2025-05-26', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2132, 70, '2025-05-26', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2133, 70, '2025-05-26', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2134, 70, '2025-05-26', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2135, 70, '2025-05-26', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2136, 70, '2025-05-26', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2137, 70, '2025-05-26', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2138, 70, '2025-05-26', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2139, 70, '2025-05-26', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2140, 70, '2025-05-26', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2141, 70, '2025-05-26', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2142, 70, '2025-05-28', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2143, 70, '2025-05-28', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2144, 70, '2025-05-28', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2145, 70, '2025-05-28', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2146, 70, '2025-05-28', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2147, 70, '2025-05-28', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2148, 70, '2025-05-28', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2149, 70, '2025-05-28', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2150, 70, '2025-05-28', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2151, 70, '2025-05-28', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2152, 70, '2025-05-28', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2153, 70, '2025-05-28', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2154, 70, '2025-05-28', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2155, 70, '2025-05-28', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2156, 70, '2025-05-30', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2157, 70, '2025-05-30', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2158, 70, '2025-05-30', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2159, 70, '2025-05-30', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2160, 70, '2025-05-30', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2161, 70, '2025-05-30', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2162, 70, '2025-05-30', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2163, 70, '2025-05-30', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2164, 70, '2025-05-30', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2165, 70, '2025-05-30', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2166, 70, '2025-05-30', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2167, 70, '2025-05-30', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2168, 70, '2025-05-30', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2169, 70, '2025-05-30', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2170, 70, '2025-06-02', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2171, 70, '2025-06-02', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2172, 70, '2025-06-02', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2173, 70, '2025-06-02', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2174, 70, '2025-06-02', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2175, 70, '2025-06-02', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2176, 70, '2025-06-02', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2177, 70, '2025-06-02', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2178, 70, '2025-06-02', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2179, 70, '2025-06-02', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2180, 70, '2025-06-02', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2181, 70, '2025-06-02', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2182, 70, '2025-06-02', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2183, 70, '2025-06-02', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2184, 70, '2025-06-04', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2185, 70, '2025-06-04', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2186, 70, '2025-06-04', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2187, 70, '2025-06-04', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2188, 70, '2025-06-04', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2189, 70, '2025-06-04', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2190, 70, '2025-06-04', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2191, 70, '2025-06-04', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2192, 70, '2025-06-04', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2193, 70, '2025-06-04', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2194, 70, '2025-06-04', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2195, 70, '2025-06-04', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2196, 70, '2025-06-04', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2197, 70, '2025-06-04', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2198, 70, '2025-06-06', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2199, 70, '2025-06-06', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2200, 70, '2025-06-06', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2201, 70, '2025-06-06', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2202, 70, '2025-06-06', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2203, 70, '2025-06-06', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2204, 70, '2025-06-06', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2205, 70, '2025-06-06', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2206, 70, '2025-06-06', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2207, 70, '2025-06-06', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2208, 70, '2025-06-06', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2209, 70, '2025-06-06', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2210, 70, '2025-06-06', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2211, 70, '2025-06-06', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2212, 70, '2025-06-09', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2213, 70, '2025-06-09', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2214, 70, '2025-06-09', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2215, 70, '2025-06-09', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2216, 70, '2025-06-09', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2217, 70, '2025-06-09', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2218, 70, '2025-06-09', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2219, 70, '2025-06-09', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2220, 70, '2025-06-09', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2221, 70, '2025-06-09', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2222, 70, '2025-06-09', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2223, 70, '2025-06-09', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2224, 70, '2025-06-09', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2225, 70, '2025-06-09', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2226, 70, '2025-06-11', '07:00:00', '07:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2227, 70, '2025-06-11', '07:40:00', '08:25:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2228, 70, '2025-06-11', '08:25:00', '09:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2229, 70, '2025-06-11', '09:05:00', '09:50:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2230, 70, '2025-06-11', '09:50:00', '10:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2231, 70, '2025-06-11', '10:30:00', '11:15:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2232, 70, '2025-06-11', '11:15:00', '11:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2233, 70, '2025-06-11', '11:55:00', '12:40:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2234, 70, '2025-06-11', '12:40:00', '13:20:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2235, 70, '2025-06-11', '13:20:00', '14:05:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2236, 70, '2025-06-11', '14:05:00', '14:45:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2237, 70, '2025-06-11', '14:45:00', '15:30:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2238, 70, '2025-06-11', '15:30:00', '16:10:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 42),
(2239, 70, '2025-06-11', '16:10:00', '16:55:00', 1, 'availability', '2025-05-14 16:55:27', 0, NULL, 1, 40),
(2240, 71, '2025-05-15', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2241, 71, '2025-05-15', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2242, 71, '2025-05-15', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2243, 71, '2025-05-15', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2244, 71, '2025-05-15', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2245, 71, '2025-05-15', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2246, 71, '2025-05-15', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2247, 71, '2025-05-15', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2248, 71, '2025-05-15', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2249, 71, '2025-05-15', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2250, 71, '2025-05-15', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2251, 71, '2025-05-15', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2252, 71, '2025-05-15', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2253, 71, '2025-05-15', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2254, 71, '2025-05-15', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2255, 71, '2025-05-15', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2256, 71, '2025-05-17', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2257, 71, '2025-05-17', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2258, 71, '2025-05-17', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2259, 71, '2025-05-17', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2260, 71, '2025-05-17', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2261, 71, '2025-05-17', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2262, 71, '2025-05-17', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2263, 71, '2025-05-17', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2264, 71, '2025-05-17', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2265, 71, '2025-05-17', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2266, 71, '2025-05-17', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2267, 71, '2025-05-17', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2268, 71, '2025-05-17', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2269, 71, '2025-05-17', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2270, 71, '2025-05-17', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2271, 71, '2025-05-17', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2272, 71, '2025-05-20', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2273, 71, '2025-05-20', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2274, 71, '2025-05-20', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2275, 71, '2025-05-20', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2276, 71, '2025-05-20', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2277, 71, '2025-05-20', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2278, 71, '2025-05-20', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2279, 71, '2025-05-20', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2280, 71, '2025-05-20', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2281, 71, '2025-05-20', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2282, 71, '2025-05-20', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2283, 71, '2025-05-20', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2284, 71, '2025-05-20', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2285, 71, '2025-05-20', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2286, 71, '2025-05-20', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2287, 71, '2025-05-20', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2288, 71, '2025-05-22', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2289, 71, '2025-05-22', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2290, 71, '2025-05-22', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2291, 71, '2025-05-22', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2292, 71, '2025-05-22', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2293, 71, '2025-05-22', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2294, 71, '2025-05-22', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2295, 71, '2025-05-22', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2296, 71, '2025-05-22', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2297, 71, '2025-05-22', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2298, 71, '2025-05-22', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2299, 71, '2025-05-22', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2300, 71, '2025-05-22', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2301, 71, '2025-05-22', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2302, 71, '2025-05-22', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2303, 71, '2025-05-22', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2304, 71, '2025-05-24', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2305, 71, '2025-05-24', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2306, 71, '2025-05-24', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2307, 71, '2025-05-24', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2308, 71, '2025-05-24', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2309, 71, '2025-05-24', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2310, 71, '2025-05-24', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2311, 71, '2025-05-24', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2312, 71, '2025-05-24', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2313, 71, '2025-05-24', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2314, 71, '2025-05-24', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2315, 71, '2025-05-24', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2316, 71, '2025-05-24', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2317, 71, '2025-05-24', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2318, 71, '2025-05-24', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2319, 71, '2025-05-24', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2320, 71, '2025-05-27', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2321, 71, '2025-05-27', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2322, 71, '2025-05-27', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2323, 71, '2025-05-27', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2324, 71, '2025-05-27', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2325, 71, '2025-05-27', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2326, 71, '2025-05-27', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2327, 71, '2025-05-27', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2328, 71, '2025-05-27', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2329, 71, '2025-05-27', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2330, 71, '2025-05-27', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2331, 71, '2025-05-27', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2332, 71, '2025-05-27', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2333, 71, '2025-05-27', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2334, 71, '2025-05-27', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2335, 71, '2025-05-27', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2336, 71, '2025-05-29', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2337, 71, '2025-05-29', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2338, 71, '2025-05-29', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2339, 71, '2025-05-29', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2340, 71, '2025-05-29', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2341, 71, '2025-05-29', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2342, 71, '2025-05-29', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2343, 71, '2025-05-29', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2344, 71, '2025-05-29', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2345, 71, '2025-05-29', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2346, 71, '2025-05-29', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2347, 71, '2025-05-29', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2348, 71, '2025-05-29', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2349, 71, '2025-05-29', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2350, 71, '2025-05-29', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2351, 71, '2025-05-29', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2352, 71, '2025-05-31', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2353, 71, '2025-05-31', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2354, 71, '2025-05-31', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2355, 71, '2025-05-31', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2356, 71, '2025-05-31', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2357, 71, '2025-05-31', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2358, 71, '2025-05-31', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2359, 71, '2025-05-31', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2360, 71, '2025-05-31', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2361, 71, '2025-05-31', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2362, 71, '2025-05-31', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2363, 71, '2025-05-31', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2364, 71, '2025-05-31', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2365, 71, '2025-05-31', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2366, 71, '2025-05-31', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2367, 71, '2025-05-31', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2368, 71, '2025-06-03', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2369, 71, '2025-06-03', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2370, 71, '2025-06-03', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2371, 71, '2025-06-03', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2372, 71, '2025-06-03', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2373, 71, '2025-06-03', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2374, 71, '2025-06-03', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2375, 71, '2025-06-03', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2376, 71, '2025-06-03', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2377, 71, '2025-06-03', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2378, 71, '2025-06-03', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2379, 71, '2025-06-03', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2380, 71, '2025-06-03', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2381, 71, '2025-06-03', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2382, 71, '2025-06-03', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2383, 71, '2025-06-03', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2384, 71, '2025-06-05', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2385, 71, '2025-06-05', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2386, 71, '2025-06-05', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2387, 71, '2025-06-05', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2388, 71, '2025-06-05', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2389, 71, '2025-06-05', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2390, 71, '2025-06-05', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2391, 71, '2025-06-05', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2392, 71, '2025-06-05', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2393, 71, '2025-06-05', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2394, 71, '2025-06-05', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2395, 71, '2025-06-05', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2396, 71, '2025-06-05', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2397, 71, '2025-06-05', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2398, 71, '2025-06-05', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2399, 71, '2025-06-05', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2400, 71, '2025-06-07', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2401, 71, '2025-06-07', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2402, 71, '2025-06-07', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2403, 71, '2025-06-07', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2404, 71, '2025-06-07', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2405, 71, '2025-06-07', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2406, 71, '2025-06-07', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43);
INSERT INTO `provider_availability` (`availability_id`, `provider_id`, `availability_date`, `start_time`, `end_time`, `is_available`, `schedule_type`, `created_at`, `is_recurring`, `weekdays`, `max_appointments`, `service_id`) VALUES
(2407, 71, '2025-06-07', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2408, 71, '2025-06-07', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2409, 71, '2025-06-07', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2410, 71, '2025-06-07', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2411, 71, '2025-06-07', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2412, 71, '2025-06-07', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2413, 71, '2025-06-07', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2414, 71, '2025-06-07', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2415, 71, '2025-06-07', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2416, 71, '2025-06-10', '07:00:00', '08:00:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2417, 71, '2025-06-10', '08:00:00', '08:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2418, 71, '2025-06-10', '08:15:00', '09:15:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2419, 71, '2025-06-10', '09:15:00', '09:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2420, 71, '2025-06-10', '09:30:00', '10:30:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 43),
(2421, 71, '2025-06-10', '10:30:00', '10:45:00', 1, 'availability', '2025-05-14 16:57:23', 0, NULL, 1, 41),
(2422, 71, '2025-06-10', '10:45:00', '11:45:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 43),
(2423, 71, '2025-06-10', '11:45:00', '12:00:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 41),
(2424, 71, '2025-06-10', '12:00:00', '13:00:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 43),
(2425, 71, '2025-06-10', '13:00:00', '13:15:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 41),
(2426, 71, '2025-06-10', '13:15:00', '14:15:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 43),
(2427, 71, '2025-06-10', '14:15:00', '14:30:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 41),
(2428, 71, '2025-06-10', '14:30:00', '15:30:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 43),
(2429, 71, '2025-06-10', '15:30:00', '15:45:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 41),
(2430, 71, '2025-06-10', '15:45:00', '16:45:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 43),
(2431, 71, '2025-06-10', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 16:57:24', 0, NULL, 1, 41),
(2432, 72, '2025-05-14', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2433, 72, '2025-05-14', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2434, 72, '2025-05-14', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2435, 72, '2025-05-14', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2436, 72, '2025-05-14', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2437, 72, '2025-05-14', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2438, 72, '2025-05-14', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2439, 72, '2025-05-14', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2440, 72, '2025-05-17', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2441, 72, '2025-05-17', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2442, 72, '2025-05-17', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2443, 72, '2025-05-17', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2444, 72, '2025-05-17', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2445, 72, '2025-05-17', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2446, 72, '2025-05-17', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2447, 72, '2025-05-17', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2448, 72, '2025-05-18', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2449, 72, '2025-05-18', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2450, 72, '2025-05-18', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2451, 72, '2025-05-18', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2452, 72, '2025-05-18', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2453, 72, '2025-05-18', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2454, 72, '2025-05-18', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2455, 72, '2025-05-18', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2456, 72, '2025-05-21', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2457, 72, '2025-05-21', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2458, 72, '2025-05-21', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2459, 72, '2025-05-21', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2460, 72, '2025-05-21', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2461, 72, '2025-05-21', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2462, 72, '2025-05-21', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2463, 72, '2025-05-21', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2464, 72, '2025-05-24', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2465, 72, '2025-05-24', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2466, 72, '2025-05-24', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2467, 72, '2025-05-24', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2468, 72, '2025-05-24', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2469, 72, '2025-05-24', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2470, 72, '2025-05-24', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2471, 72, '2025-05-24', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2472, 72, '2025-05-25', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2473, 72, '2025-05-25', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2474, 72, '2025-05-25', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2475, 72, '2025-05-25', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2476, 72, '2025-05-25', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2477, 72, '2025-05-25', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2478, 72, '2025-05-25', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2479, 72, '2025-05-25', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2480, 72, '2025-05-28', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2481, 72, '2025-05-28', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2482, 72, '2025-05-28', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2483, 72, '2025-05-28', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2484, 72, '2025-05-28', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2485, 72, '2025-05-28', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2486, 72, '2025-05-28', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2487, 72, '2025-05-28', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2488, 72, '2025-05-31', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2489, 72, '2025-05-31', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2490, 72, '2025-05-31', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2491, 72, '2025-05-31', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2492, 72, '2025-05-31', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2493, 72, '2025-05-31', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2494, 72, '2025-05-31', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2495, 72, '2025-05-31', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2496, 72, '2025-06-01', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2497, 72, '2025-06-01', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2498, 72, '2025-06-01', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2499, 72, '2025-06-01', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2500, 72, '2025-06-01', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2501, 72, '2025-06-01', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2502, 72, '2025-06-01', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2503, 72, '2025-06-01', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2504, 72, '2025-06-04', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2505, 72, '2025-06-04', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2506, 72, '2025-06-04', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 38),
(2507, 72, '2025-06-04', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 42),
(2508, 72, '2025-06-04', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:49', 0, NULL, 1, 35),
(2509, 72, '2025-06-04', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 38),
(2510, 72, '2025-06-04', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2511, 72, '2025-06-04', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2512, 72, '2025-06-07', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2513, 72, '2025-06-07', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2514, 72, '2025-06-07', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 38),
(2515, 72, '2025-06-07', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2516, 72, '2025-06-07', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2517, 72, '2025-06-07', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 38),
(2518, 72, '2025-06-07', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2519, 72, '2025-06-07', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2520, 72, '2025-06-08', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2521, 72, '2025-06-08', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2522, 72, '2025-06-08', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 38),
(2523, 72, '2025-06-08', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2524, 72, '2025-06-08', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2525, 72, '2025-06-08', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 38),
(2526, 72, '2025-06-08', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2527, 72, '2025-06-08', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2528, 72, '2025-06-11', '08:00:00', '08:40:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2529, 72, '2025-06-11', '08:40:00', '09:25:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2530, 72, '2025-06-11', '09:25:00', '10:25:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 38),
(2531, 72, '2025-06-11', '10:25:00', '11:05:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2532, 72, '2025-06-11', '11:05:00', '11:50:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2533, 72, '2025-06-11', '11:50:00', '12:50:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 38),
(2534, 72, '2025-06-11', '12:50:00', '13:30:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 42),
(2535, 72, '2025-06-11', '13:30:00', '14:15:00', 1, 'availability', '2025-05-14 16:58:50', 0, NULL, 1, 35),
(2556, 68, '2025-05-16', '07:00:00', '07:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2557, 68, '2025-05-16', '07:45:00', '08:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2558, 68, '2025-05-16', '08:15:00', '08:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2559, 68, '2025-05-16', '08:45:00', '09:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2560, 68, '2025-05-16', '09:00:00', '09:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2561, 68, '2025-05-16', '09:45:00', '10:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2562, 68, '2025-05-16', '10:15:00', '10:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2563, 68, '2025-05-16', '10:45:00', '11:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2564, 68, '2025-05-16', '11:00:00', '11:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2565, 68, '2025-05-16', '11:45:00', '12:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2566, 68, '2025-05-16', '12:15:00', '12:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2567, 68, '2025-05-16', '12:45:00', '13:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2568, 68, '2025-05-16', '13:00:00', '13:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2569, 68, '2025-05-16', '13:45:00', '14:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2570, 68, '2025-05-16', '14:15:00', '14:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2571, 68, '2025-05-16', '14:45:00', '15:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2572, 68, '2025-05-16', '15:00:00', '15:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2573, 68, '2025-05-16', '15:45:00', '16:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2574, 68, '2025-05-16', '16:15:00', '16:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2575, 68, '2025-05-16', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2576, 68, '2025-05-19', '07:00:00', '07:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2577, 68, '2025-05-19', '07:45:00', '08:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2578, 68, '2025-05-19', '08:15:00', '08:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2579, 68, '2025-05-19', '08:45:00', '09:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2580, 68, '2025-05-19', '09:00:00', '09:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2581, 68, '2025-05-19', '09:45:00', '10:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2582, 68, '2025-05-19', '10:15:00', '10:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2583, 68, '2025-05-19', '10:45:00', '11:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2584, 68, '2025-05-19', '11:00:00', '11:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2585, 68, '2025-05-19', '11:45:00', '12:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2586, 68, '2025-05-19', '12:15:00', '12:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2587, 68, '2025-05-19', '12:45:00', '13:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2588, 68, '2025-05-19', '13:00:00', '13:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2589, 68, '2025-05-19', '13:45:00', '14:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2590, 68, '2025-05-19', '14:15:00', '14:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2591, 68, '2025-05-19', '14:45:00', '15:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2592, 68, '2025-05-19', '15:00:00', '15:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2593, 68, '2025-05-19', '15:45:00', '16:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2594, 68, '2025-05-19', '16:15:00', '16:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2595, 68, '2025-05-19', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2596, 68, '2025-05-21', '07:00:00', '07:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2597, 68, '2025-05-21', '07:45:00', '08:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2598, 68, '2025-05-21', '08:15:00', '08:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2599, 68, '2025-05-21', '08:45:00', '09:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2600, 68, '2025-05-21', '09:00:00', '09:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2601, 68, '2025-05-21', '09:45:00', '10:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2602, 68, '2025-05-21', '10:15:00', '10:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2603, 68, '2025-05-21', '10:45:00', '11:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2604, 68, '2025-05-21', '11:00:00', '11:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2605, 68, '2025-05-21', '11:45:00', '12:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2606, 68, '2025-05-21', '12:15:00', '12:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2607, 68, '2025-05-21', '12:45:00', '13:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2608, 68, '2025-05-21', '13:00:00', '13:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2609, 68, '2025-05-21', '13:45:00', '14:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2610, 68, '2025-05-21', '14:15:00', '14:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2611, 68, '2025-05-21', '14:45:00', '15:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2612, 68, '2025-05-21', '15:00:00', '15:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2613, 68, '2025-05-21', '15:45:00', '16:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2614, 68, '2025-05-21', '16:15:00', '16:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2615, 68, '2025-05-21', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2616, 68, '2025-05-23', '07:00:00', '07:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2617, 68, '2025-05-23', '07:45:00', '08:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2618, 68, '2025-05-23', '08:15:00', '08:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2619, 68, '2025-05-23', '08:45:00', '09:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2620, 68, '2025-05-23', '09:00:00', '09:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2621, 68, '2025-05-23', '09:45:00', '10:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2622, 68, '2025-05-23', '10:15:00', '10:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2623, 68, '2025-05-23', '10:45:00', '11:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2624, 68, '2025-05-23', '11:00:00', '11:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2625, 68, '2025-05-23', '11:45:00', '12:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2626, 68, '2025-05-23', '12:15:00', '12:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2627, 68, '2025-05-23', '12:45:00', '13:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2628, 68, '2025-05-23', '13:00:00', '13:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2629, 68, '2025-05-23', '13:45:00', '14:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2630, 68, '2025-05-23', '14:15:00', '14:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2631, 68, '2025-05-23', '14:45:00', '15:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2632, 68, '2025-05-23', '15:00:00', '15:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2633, 68, '2025-05-23', '15:45:00', '16:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2634, 68, '2025-05-23', '16:15:00', '16:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2635, 68, '2025-05-23', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2636, 68, '2025-05-26', '07:00:00', '07:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2637, 68, '2025-05-26', '07:45:00', '08:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2638, 68, '2025-05-26', '08:15:00', '08:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2639, 68, '2025-05-26', '08:45:00', '09:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2640, 68, '2025-05-26', '09:00:00', '09:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2641, 68, '2025-05-26', '09:45:00', '10:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2642, 68, '2025-05-26', '10:15:00', '10:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2643, 68, '2025-05-26', '10:45:00', '11:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2644, 68, '2025-05-26', '11:00:00', '11:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2645, 68, '2025-05-26', '11:45:00', '12:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2646, 68, '2025-05-26', '12:15:00', '12:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2647, 68, '2025-05-26', '12:45:00', '13:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2648, 68, '2025-05-26', '13:00:00', '13:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2649, 68, '2025-05-26', '13:45:00', '14:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2650, 68, '2025-05-26', '14:15:00', '14:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2651, 68, '2025-05-26', '14:45:00', '15:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2652, 68, '2025-05-26', '15:00:00', '15:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2653, 68, '2025-05-26', '15:45:00', '16:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2654, 68, '2025-05-26', '16:15:00', '16:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2655, 68, '2025-05-26', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2656, 68, '2025-05-28', '07:00:00', '07:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2657, 68, '2025-05-28', '07:45:00', '08:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2658, 68, '2025-05-28', '08:15:00', '08:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2659, 68, '2025-05-28', '08:45:00', '09:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2660, 68, '2025-05-28', '09:00:00', '09:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2661, 68, '2025-05-28', '09:45:00', '10:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2662, 68, '2025-05-28', '10:15:00', '10:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2663, 68, '2025-05-28', '10:45:00', '11:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2664, 68, '2025-05-28', '11:00:00', '11:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2665, 68, '2025-05-28', '11:45:00', '12:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2666, 68, '2025-05-28', '12:15:00', '12:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2667, 68, '2025-05-28', '12:45:00', '13:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2668, 68, '2025-05-28', '13:00:00', '13:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2669, 68, '2025-05-28', '13:45:00', '14:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2670, 68, '2025-05-28', '14:15:00', '14:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2671, 68, '2025-05-28', '14:45:00', '15:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41),
(2672, 68, '2025-05-28', '15:00:00', '15:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 35),
(2673, 68, '2025-05-28', '15:45:00', '16:15:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 36),
(2674, 68, '2025-05-28', '16:15:00', '16:45:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 37),
(2675, 68, '2025-05-28', '16:45:00', '17:00:00', 1, 'availability', '2025-05-14 18:07:49', 0, NULL, 1, 41);

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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provider_profiles`
--

INSERT INTO `provider_profiles` (`profile_id`, `provider_id`, `specialization`, `title`, `bio`, `accepting_new_patients`, `max_patients_per_day`, `profile_image`, `created_at`, `updated_at`) VALUES
(25, 68, 'Practisioner', NULL, 'Hello', 1, 20, NULL, '2025-05-14 00:55:56', '2025-05-15 00:58:33'),
(26, 69, '', '', '', 1, 0, NULL, '2025-05-14 00:57:20', '2025-05-14 00:57:20'),
(27, 70, '', '', '', 1, 0, NULL, '2025-05-14 00:59:28', '2025-05-14 00:59:28'),
(28, 71, '', '', '', 1, 0, NULL, '2025-05-14 01:01:01', '2025-05-14 01:01:01'),
(29, 72, '', '', '', 1, 0, NULL, '2025-05-14 01:02:48', '2025-05-14 01:02:48');

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
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provider_services`
--

INSERT INTO `provider_services` (`provider_service_id`, `provider_id`, `service_id`, `custom_duration`, `custom_notes`, `created_at`) VALUES
(35, 68, 35, NULL, NULL, '2025-05-13 22:26:11'),
(36, 68, 36, NULL, NULL, '2025-05-13 22:26:18'),
(37, 68, 37, NULL, NULL, '2025-05-13 22:26:24'),
(38, 68, 41, NULL, NULL, '2025-05-13 22:26:28'),
(39, 69, 38, NULL, NULL, '2025-05-13 22:26:40'),
(40, 69, 39, NULL, NULL, '2025-05-13 22:26:46'),
(41, 70, 40, NULL, NULL, '2025-05-13 22:26:57'),
(42, 70, 42, NULL, NULL, '2025-05-13 22:27:01'),
(43, 71, 43, NULL, NULL, '2025-05-13 22:27:20'),
(44, 71, 41, NULL, NULL, '2025-05-13 22:27:23'),
(45, 72, 35, NULL, NULL, '2025-05-13 22:27:37'),
(46, 72, 42, NULL, NULL, '2025-05-13 22:27:44'),
(47, 72, 38, NULL, NULL, '2025-05-13 22:27:52');

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
  PRIMARY KEY (`schedule_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recurring_schedules`
--

INSERT INTO `recurring_schedules` (`schedule_id`, `provider_id`, `day_of_week`, `start_time`, `end_time`, `is_active`, `schedule_type`, `effective_from`, `effective_until`, `created_at`, `updated_at`) VALUES
(13, 68, 1, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:49:07', '2025-05-14 11:49:07'),
(14, 68, 3, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:49:25', '2025-05-14 11:49:25'),
(15, 68, 5, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:49:30', '2025-05-14 11:49:30'),
(16, 69, 2, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:50:46', '2025-05-14 11:50:46'),
(17, 69, 4, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:50:55', '2025-05-14 11:50:55'),
(18, 69, 0, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:51:04', '2025-05-14 11:51:04'),
(22, 70, 1, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:55:03', '2025-05-14 11:55:03'),
(23, 70, 3, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:55:08', '2025-05-14 11:55:08'),
(24, 70, 5, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:55:13', '2025-05-14 11:55:13'),
(25, 71, 2, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:57:03', '2025-05-14 11:57:03'),
(26, 71, 4, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:57:08', '2025-05-14 11:57:08'),
(27, 71, 6, '07:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:57:13', '2025-05-14 11:57:13'),
(28, 72, 6, '08:00:00', '15:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:58:22', '2025-05-14 11:58:22'),
(29, 72, 0, '08:00:00', '15:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:58:27', '2025-05-14 11:58:27'),
(30, 72, 3, '08:00:00', '15:00:00', 1, 'availability', NULL, NULL, '2025-05-14 11:58:40', '2025-05-14 11:58:40'),
(31, 68, 4, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, '2025-05-14 21:38:34', '2025-05-14 21:38:34');

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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `name`, `description`, `duration`, `price`, `is_active`, `created_at`) VALUES
(35, 'Inital Consultaion', 'Comprehensive first-time visit for new patients, including medical history review and health assessment', 45, 150.00, 1, '2025-05-14 03:20:52'),
(36, 'Regular Check-up', 'Routine examination to monitor overall health status and ongoing conditions', 30, 85.00, 1, '2025-05-14 03:21:17'),
(37, 'Urgent Care Visit', 'Immediate care for non-emergency health concerns requiring prompt attention ', 30, 120.00, 1, '2025-05-14 03:21:44'),
(38, 'Mental Health Evaluation', 'Initial assessment of mental health status to determine appropriate therapy approach', 60, 175.00, 1, '2025-05-14 03:22:08'),
(39, 'Therapy Session', 'One-on-one therapy session focusing on specific mental health concerns', 50, 130.00, 1, '2025-05-14 03:22:36'),
(40, 'Nutritional Counseling', 'Professional guidance on dietary needs and healthy eating habits', 45, 95.00, 1, '2025-05-14 03:22:57'),
(41, 'Vaccination', 'Administration of recommended vaccines with brief consultation', 15, 65.00, 1, '2025-05-14 03:23:21'),
(42, ' Health Screening', 'Comprehensive screening for common health issues and risk factors', 40, 110.00, 1, '2025-05-14 03:23:46'),
(43, 'Allergy Testing', 'Assessment to identify specific allergic triggers and sensitivities', 60, 195.00, 1, '2025-05-14 03:24:07');

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
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `role`, `is_active`, `is_verified`, `email_verified_at`, `created_at`, `last_login`, `verification_token`, `reset_token`, `reset_token_expires`, `token_expires`, `password_change_required`) VALUES
(3, 'admin@example.com', '$2y$10$mn369d8QchCNaqnZ9DA15OJejiRrlHCzw33TIkq01qepowE42AWkS', 'Admin', 'User', NULL, 'admin', 1, 1, NULL, '2025-04-17 08:59:28', '2025-05-16 01:25:45', NULL, NULL, NULL, NULL, 0),
(68, 'provider@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Samantha', 'Smith', '(656) 537-6347', 'provider', 1, 1, NULL, '2025-05-13 19:55:56', '2025-05-13 22:29:35', NULL, NULL, NULL, NULL, 0),
(69, 'provider2@example.com', '$2y$10$J/sDVq0FXbe/FpodSn/ifOLwHJ4wUVjCtLqWbS0MW1w2pREpHG8h2', 'Stan', 'Smith', '(766) 765-7777', 'provider', 1, 1, NULL, '2025-05-13 19:57:20', '2025-05-14 17:00:06', NULL, NULL, NULL, NULL, 0),
(70, 'provider3@example.com', '$2y$10$0x2CPgCVmYF0hCN2xQYzrO0.JetdfBjnOyp5fV69W/wyn.McKrsou', 'Johnny ', 'Lee', '(465) 484-1315', 'provider', 1, 1, NULL, '2025-05-13 19:59:28', '2025-05-14 11:55:56', NULL, NULL, NULL, NULL, 0),
(71, 'provider4@example.com', '$2y$10$RGFbqG3XnrymDsmq4IRK9.PtY4qKleqheodz4Nmh2xUndWXcO6WNu', 'Omar', 'Clackson', '(554) 864-4464', 'provider', 1, 1, NULL, '2025-05-13 20:01:01', '2025-05-14 11:56:36', NULL, NULL, NULL, NULL, 0),
(72, 'provider5@example.com', '$2y$10$YxiuYX7ioK0CjxrDOsq5BOb0Ge3MncExR4ThiN3JXzgP3EsgSp7le', 'Tammy', 'Lee', '(676) 776-7455', 'provider', 1, 1, NULL, '2025-05-13 20:02:48', '2025-05-14 11:57:51', NULL, NULL, NULL, NULL, 0),
(73, 'Patient@example.com', '$2y$10$xMG2OCzj.CYx7nlxIv3I/.Il3FM2LPt.L409HVKGP2B.5UmsMwRmW', 'Jamison', 'Brantly', '(464) 454-6464', 'patient', 1, 1, '2025-05-13 20:05:56', '2025-05-13 20:05:46', '2025-05-13 20:06:13', NULL, NULL, NULL, NULL, 0),
(74, 'Patient2@example.com', '$2y$10$kgXTrbpiZd9MCf3Qc.t1A.tDakVxID9Nc4f34y2IeHFc05viGGyqm', 'Dylan', 'Braun', '(567) 586-8576', 'patient', 1, 1, '2025-05-13 20:07:10', '2025-05-13 20:07:04', '2025-05-13 20:07:32', NULL, NULL, NULL, NULL, 0),
(75, 'patient3@example.com', '$2y$10$iUdCNinCdA1euYFYEsMVque3iI1fivukacgMiaBOlLJR/sii0dAX2', 'Bobby', 'Brown', '(645) 777-7474', 'patient', 1, 1, '2025-05-13 20:08:23', '2025-05-13 20:08:20', '2025-05-13 20:08:41', NULL, NULL, NULL, NULL, 0),
(76, 'patient4@example.com', '$2y$10$F.0U3MFgst6mUFoYG9CchOxqVP.oqT60VK9hPT92bzOkPfMm7A53K', 'Tommy', 'Roger', '(131) 654-5132', 'patient', 1, 1, '2025-05-13 21:46:19', '2025-05-13 21:46:14', '2025-05-13 21:46:38', NULL, NULL, NULL, NULL, 0);

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
