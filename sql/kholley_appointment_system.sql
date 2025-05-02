CREATE TABLE `activity_log` (
  `log_id` int(11) PRIMARY KEY NOT NULL,
  `user_id` int(11) DEFAULT null,
  `description` text NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `created_at` datetime DEFAULT (current_timestamp()),
  `ip_address` varchar(45) DEFAULT null,
  `details` text DEFAULT null,
  `related_id` int(11) DEFAULT null,
  `related_type` varchar(50) DEFAULT null
);

CREATE TABLE `appointments` (
  `appointment_id` int(11) PRIMARY KEY NOT NULL,
  `patient_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` ENUM ('scheduled', 'confirmed', 'completed', 'canceled', 'no_show') NOT NULL DEFAULT 'scheduled',
  `type` ENUM ('in_person', 'virtual', 'phone') NOT NULL DEFAULT 'in_person',
  `notes` text DEFAULT null,
  `reason` text DEFAULT null,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `confirmed_at` datetime DEFAULT null,
  `canceled_at` datetime DEFAULT null,
  `created_at` datetime DEFAULT (current_timestamp()),
  `updated_at` datetime DEFAULT (current_timestamp())
);

CREATE TABLE `appointment_history` (
  `history_id` int(11) PRIMARY KEY NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `action` ENUM ('created', 'updated', 'canceled', 'rescheduled', 'completed', 'no_show') NOT NULL,
  `changed_fields` text DEFAULT null,
  `old_values` text DEFAULT null,
  `new_values` text DEFAULT null,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT (current_timestamp())
);

CREATE TABLE `appointment_ratings` (
  `rating_id` int(11) PRIMARY KEY NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT null,
  `created_at` datetime DEFAULT (current_timestamp())
);

CREATE TABLE `auth_sessions` (
  `session_id` int(11) PRIMARY KEY NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT null,
  `user_agent` varchar(255) DEFAULT null,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT (current_timestamp()),
  `last_active` datetime DEFAULT null
);

CREATE TABLE `availability` (
  `availability_id` int(11) PRIMARY KEY NOT NULL,
  `provider_id` int(11) NOT NULL,
  `availability_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT (current_timestamp()),
  `updated_at` datetime DEFAULT (current_timestamp())
);

CREATE TABLE `notifications` (
  `notification_id` int(11) PRIMARY KEY NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT null,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` ENUM ('email', 'sms', 'app', 'system') NOT NULL DEFAULT 'email',
  `status` ENUM ('pending', 'sent', 'failed', 'read') NOT NULL DEFAULT 'pending',
  `scheduled_for` datetime DEFAULT null,
  `sent_at` datetime DEFAULT null,
  `created_at` datetime DEFAULT (current_timestamp()),
  `is_system` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `audience` ENUM ('all', 'admin', 'provider', 'patient') DEFAULT 'all'
);

CREATE TABLE `notification_preferences` (
  `preference_id` int(11) PRIMARY KEY NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `sms_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `appointment_reminders` tinyint(1) NOT NULL DEFAULT 1,
  `system_updates` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_time` int(11) NOT NULL DEFAULT 24,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `patient_profiles` (
  `patient_id` int(11) PRIMARY KEY NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT null,
  `date_of_birth` date DEFAULT null,
  `address` text DEFAULT null,
  `emergency_contact` varchar(255) DEFAULT null,
  `emergency_contact_phone` varchar(50) DEFAULT null,
  `medical_conditions` text DEFAULT null,
  `medical_history` text DEFAULT null,
  `insurance_info` text DEFAULT null,
  `created_at` timestamp NOT NULL DEFAULT (current_timestamp()),
  `updated_at` timestamp NOT NULL DEFAULT (current_timestamp())
);

CREATE TABLE `provider_availability` (
  `availability_id` int(11) PRIMARY KEY NOT NULL,
  `provider_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT (current_timestamp())
);

CREATE TABLE `provider_profiles` (
  `profile_id` int(11) PRIMARY KEY NOT NULL,
  `provider_id` int(11) NOT NULL,
  `specialization` varchar(100) DEFAULT null,
  `title` varchar(50) DEFAULT null,
  `bio` text DEFAULT null,
  `accepting_new_patients` tinyint(1) DEFAULT 1,
  `max_patients_per_day` int(11) DEFAULT 20,
  `profile_image` varchar(255) DEFAULT null,
  `created_at` timestamp NOT NULL DEFAULT (current_timestamp()),
  `updated_at` timestamp NOT NULL DEFAULT (current_timestamp())
);

CREATE TABLE `provider_services` (
  `provider_service_id` int(11) PRIMARY KEY NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `custom_duration` int(11) DEFAULT null COMMENT 'Override default duration if needed',
  `custom_notes` text DEFAULT null,
  `created_at` datetime DEFAULT (current_timestamp())
);

CREATE TABLE `recurring_schedules` (
  `schedule_id` int(11) PRIMARY KEY NOT NULL,
  `provider_id` int(11) NOT NULL,
  `day_of_week` int(11) NOT NULL COMMENT '0=Sunday, 1=Monday, etc.',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `effective_from` date DEFAULT null,
  `effective_until` date DEFAULT null,
  `created_at` datetime DEFAULT (current_timestamp()),
  `updated_at` datetime DEFAULT (current_timestamp())
);

CREATE TABLE `services` (
  `service_id` int(11) PRIMARY KEY NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT null,
  `duration` int(11) DEFAULT 30,
  `price` decimal(10,2) DEFAULT null,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT (current_timestamp())
);

CREATE TABLE `settings` (
  `setting_id` int(11) PRIMARY KEY NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT null,
  `description` text DEFAULT null,
  `updated_at` datetime DEFAULT (current_timestamp())
);

CREATE TABLE `users` (
  `user_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT null,
  `role` ENUM ('patient', 'provider', 'admin', 'staff') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `email_verified_at` datetime DEFAULT null,
  `created_at` datetime DEFAULT (current_timestamp()),
  `last_login` datetime DEFAULT null,
  `verification_token` varchar(64) DEFAULT null,
  `reset_token` varchar(64) DEFAULT null,
  `reset_token_expires` datetime DEFAULT null,
  `token_expires` datetime DEFAULT null,
  `password_change_required` tinyint(1) DEFAULT 0,
  AUTO_INCREMENT=17
);

CREATE TABLE `user_tokens` (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  selector VARCHAR(16) NOT NULL,
  token VARCHAR(64) NOT NULL,
  expires DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE `waitlist` (
  `waitlist_id` int(11) PRIMARY KEY NOT NULL,
  `patient_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time DEFAULT null,
  `flexibility` ENUM ('strict', 'flexible_time', 'flexible_day', 'flexible_provider') DEFAULT 'strict',
  `is_fulfilled` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT (current_timestamp()),
  `updated_at` datetime DEFAULT (current_timestamp())
);

ALTER TABLE `activity_log` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `appointments` ADD FOREIGN KEY (`patient_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `appointments` ADD FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `appointments` ADD FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

ALTER TABLE `appointment_history` ADD FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`);

ALTER TABLE `appointment_history` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `appointment_ratings` ADD FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`);

ALTER TABLE `appointment_ratings` ADD FOREIGN KEY (`patient_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `appointment_ratings` ADD FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `auth_sessions` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `availability` ADD FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `notifications` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `notifications` ADD FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`);

ALTER TABLE `notification_preferences` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `patient_profiles` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `provider_availability` ADD FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `provider_profiles` ADD FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `provider_services` ADD FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `provider_services` ADD FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

ALTER TABLE `recurring_schedules` ADD FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `waitlist` ADD FOREIGN KEY (`patient_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `waitlist` ADD FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `waitlist` ADD FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);
