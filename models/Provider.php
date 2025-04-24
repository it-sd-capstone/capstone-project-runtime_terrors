<?php

class Provider {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get provider's profile details securely
    public function getProviderData($provider_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM providers WHERE provider_id = ?");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc() ?: [];
        } catch (Exception $e) {
            error_log("Error fetching provider data: " . $e->getMessage());
            return [];
        }
    }

    // Update provider profile securely with transactions
    public function updateProfile($provider_id, $first_name, $last_name, $specialty, $phone, $bio) {
        try {
            $this->db->begin_transaction();

            $stmt = $this->db->prepare("
                UPDATE providers SET first_name = ?, last_name = ?, specialty = ?, phone = ?, bio = ?
                WHERE provider_id = ?
            ");
            $stmt->bind_param("sssssi", $first_name, $last_name, $specialty, $phone, $bio, $provider_id);
            $success = $stmt->execute();

            if (!$success) {
                throw new Exception("Profile update failed.");
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
            $stmt = $this->db->prepare("SELECT password FROM providers WHERE provider_id = ?");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result || !password_verify($current_password, $result['password'])) {
                return false;
            }

            // Update to new password (hashed)
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE providers SET password = ? WHERE provider_id = ?");
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

    // Provider Services Management (CRUD)
    public function addService($provider_id, $service_name, $description, $price) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO provider_services (provider_id, service_name, description, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("issd", $provider_id, $service_name, $description, $price);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding service: " . $e->getMessage());
            return false;
        }
    }

    public function getServices($provider_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM provider_services WHERE provider_id = ?");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching services: " . $e->getMessage());
            return [];
        }
    }

    public function updateService($service_id, $provider_id, $service_name, $description, $price) {
        try {
            $stmt = $this->db->prepare("
                UPDATE provider_services SET service_name = ?, description = ?, price = ? 
                WHERE provider_service_id = ? AND provider_id = ?
            ");
            $stmt->bind_param("ssdii", $service_name, $description, $price, $service_id, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating service: " . $e->getMessage());
            return false;
        }
    }

    public function deleteService($service_id, $provider_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM provider_services WHERE provider_service_id = ? AND provider_id = ?");
            $stmt->bind_param("ii", $service_id, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting service: " . $e->getMessage());
            return false;
        }
    }
}
?>