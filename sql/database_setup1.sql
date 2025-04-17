-- Set up the database
CREATE DATABASE IF NOT EXISTS `kholley_appointment_system`;
USE `kholley_appointment_system`;

-- Table: Users (Patients & Providers)
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `role` ENUM('patient','provider','admin') NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: Services (Types of Appointments)
CREATE TABLE `services` (
    `service_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `duration` INT NOT NULL, -- Duration in minutes
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: Provider Availability
CREATE TABLE `provider_availability` (
    `availability_id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider_id` INT NOT NULL,
    `available_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `is_available` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`provider_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
);

-- Table: Appointments (Booking)
CREATE TABLE `appointments` (
    `appointment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT NOT NULL,
    `provider_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    `availability_id` INT DEFAULT NULL, -- References provider_availability
    `appointment_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `status` ENUM('pending','confirmed','completed','canceled') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`provider_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`service_id`) ON DELETE CASCADE,
    FOREIGN KEY (`availability_id`) REFERENCES `provider_availability`(`availability_id`)
);

-- Sample Users
INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `role`) VALUES
('patient@example.com', '$2y$10$example_hash', 'John', 'Doe', 'patient'),
('provider@example.com', '$2y$10$example_hash', 'Dr. Smith', 'MD', 'provider'),
('admin@example.com', '$2y$10$example_hash', 'Admin', 'User', 'admin');

-- Sample Services
INSERT INTO `services` (`name`, `description`, `duration`) VALUES
('Regular Checkup', 'Standard medical examination', 30),
('Therapy Session', 'One-hour counseling session', 60);

-- Sample Provider Availability
INSERT INTO `provider_availability` (`provider_id`, `available_date`, `start_time`, `end_time`) VALUES
(2, '2025-04-15', '09:00:00', '12:00:00'),
(2, '2025-04-16', '14:00:00', '17:00:00');

-- Sample Appointments
INSERT INTO `appointments` (`patient_id`, `provider_id`, `service_id`, `availability_id`, `appointment_date`, `start_time`, `end_time`, `status`) VALUES
public function getAvailableSlots() {
    $query = "SELECT pa.*, u.first_name as provider_name 
              FROM provider_availability pa
              JOIN users u ON pa.provider_id = u.user_id
              WHERE pa.is_available = 1";
    $stmt = $this->db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}