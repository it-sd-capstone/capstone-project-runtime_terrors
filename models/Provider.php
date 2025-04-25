<?php

class Provider {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get provider's profile details securely
    // This replaces getProviderData with the method name your controller expects
    public function getById($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, pp.*
                FROM users u
                JOIN provider_profiles pp ON u.user_id = pp.provider_id
                WHERE u.user_id = ? AND u.role = 'provider'
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc() ?: [];
        } catch (Exception $e) {
            error_log("Error fetching provider data: " . $e->getMessage());
            return [];
        }
    }

    // Alias to maintain compatibility
    public function getProviderData($provider_id) {
        return $this->getById($provider_id);
    }

    // Update provider profile securely with transactions
    public function updateProfile($provider_id, $first_name, $last_name, $specialization, $phone, $bio) {
        try {
            $this->db->begin_transaction();

            // Update users table
            $stmt = $this->db->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, phone = ?
                WHERE user_id = ? AND role = 'provider'
            ");
            $stmt->bind_param("sssi", $first_name, $last_name, $phone, $provider_id);
            $success = $stmt->execute();

            if (!$success) {
                throw new Exception("Profile update failed (users table).");
            }

            // Update provider_profiles table
            $stmt = $this->db->prepare("
                UPDATE provider_profiles 
                SET specialization = ?, bio = ?
                WHERE provider_id = ?
            ");
            $stmt->bind_param("ssi", $specialization, $bio, $provider_id);
            $success = $stmt->execute();

            if (!$success) {
                throw new Exception("Profile update failed (provider_profiles table).");
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error updating profile: " . $e->getMessage());
            return false;
        }
    }

    // Change provider password securely
    public function changePassword($provider_id, $current_password, $new_password) {
        try {
            // Verify current password
            $stmt = $this->db->prepare("
                SELECT password_hash 
                FROM users 
                WHERE user_id = ? AND role = 'provider'
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result || !password_verify($current_password, $result['password_hash'])) {
                return false;
            }

            // Update to new password (hashed)
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password_hash = ? 
                WHERE user_id = ? AND role = 'provider'
            ");
            $stmt->bind_param("si", $new_password_hash, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }

    // Get provider availability
    public function getAvailability($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM availability 
                WHERE provider_id = ? AND availability_date >= CURDATE()
                ORDER BY availability_date, start_time
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching availability: " . $e->getMessage());
            return [];
        }
    }
    public function getAvailableSlots($provider_id) {
        return $this->getAvailability($provider_id);
    }

    // Add provider availability
    public function addAvailability($provider_id, $date, $start_time, $end_time) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO availability (provider_id, availability_date, start_time, end_time, is_available)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->bind_param("isss", $provider_id, $date, $start_time, $end_time);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding availability: " . $e->getMessage());
            return false;
        }
    }

    // Provider Services Management - modified to work with your schema
    // This now uses the services and provider_services tables
    public function addService($provider_id, $service_id, $custom_duration = null, $custom_notes = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO provider_services (provider_id, service_id, custom_duration, custom_notes)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiis", $provider_id, $service_id, $custom_duration, $custom_notes);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding service: " . $e->getMessage());
            return false;
        }
    }

    public function getServices($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT ps.*, s.name, s.description, s.duration, s.is_active
                FROM provider_services ps
                JOIN services s ON ps.service_id = s.service_id
                WHERE ps.provider_id = ?
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching services: " . $e->getMessage());
            return [];
        }
    }

    public function updateService($provider_service_id, $provider_id, $custom_duration, $custom_notes) {
        try {
            $stmt = $this->db->prepare("
                UPDATE provider_services 
                SET custom_duration = ?, custom_notes = ?
                WHERE provider_service_id = ? AND provider_id = ?
            ");
            $stmt->bind_param("isii", $custom_duration, $custom_notes, $provider_service_id, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating service: " . $e->getMessage());
            return false;
        }
    }

    public function deleteService($provider_service_id, $provider_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM provider_services 
                WHERE provider_service_id = ? AND provider_id = ?
            ");
            $stmt->bind_param("ii", $provider_service_id, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting service: " . $e->getMessage());
            return false;
        }
    }

    // Get all providers (needed for listing providers)
    public function getAll() {
        try {
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.first_name, u.last_name, 
                       pp.specialization, pp.title, pp.bio, pp.accepting_new_patients
                FROM users u
                JOIN provider_profiles pp ON u.user_id = pp.provider_id
                WHERE u.role = 'provider' AND u.is_active = 1
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching all providers: " . $e->getMessage());
            return [];
        }
    }
        public function getRecurringSchedules($provider_id) {
            $query = "SELECT * FROM recurring_schedules 
                    WHERE provider_id = ? 
                    ORDER BY day_of_week, start_time";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $schedules = [];
            
            while ($row = $result->fetch_assoc()) {
                $schedules[] = $row;
            }
            
            return $schedules;
        }
    
    // Get booked appointments for a provider - essential for ProviderController
    public function getBookedAppointments($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.first_name AS patient_first_name,
                       u.last_name AS patient_last_name,
                       s.name AS service_name
                FROM appointments a
                JOIN users u ON a.patient_id = u.user_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.provider_id = ?
                ORDER BY a.appointment_date, a.start_time
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching provider appointments: " . $e->getMessage());
            return [];
        }
    }
}
?>