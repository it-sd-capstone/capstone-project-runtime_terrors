<?php
class Provider {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get provider's available slots, ensuring they are not booked
    public function getAvailableSlots($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.first_name, u.last_name 
                FROM availability a
                JOIN users u ON a.provider_id = u.user_id
                WHERE a.is_available = 1 AND a.provider_id = ? AND a.availability_date >= CURDATE()
                ORDER BY a.availability_date, a.start_time
            ");
            $stmt->execute([$provider_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getAvailableSlots: " . $e->getMessage());
            return [];
        }
    }

    // Get booked appointments for a provider
    public function getBookedAppointments($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.first_name AS patient_name
                FROM appointments a
                JOIN users u ON a.patient_id = u.user_id
                WHERE a.provider_id = ?
            ");
            $stmt->execute([$provider_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching booked appointments: " . $e->getMessage());
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
            return $stmt->execute([$provider_id, $date, $start_time, $end_time]);
        } catch (Exception $e) {
            error_log("Error adding availability: " . $e->getMessage());
            return false;
        }
    }

    // Check if a time slot is already booked
    public function isSlotBooked($availability_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM appointments WHERE availability_id = ?
            ");
            $stmt->execute([$availability_id]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error in isSlotBooked: " . $e->getMessage());
            return false;
        }
    }

    // Get provider's availability
    public function getAvailability($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM availability
                WHERE provider_id = ? AND availability_date >= CURDATE()
                ORDER BY availability_date, start_time
            ");
            
            // Bind the parameter using mysqli syntax
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            
            // Get result set and fetch rows with mysqli syntax
            $result = $stmt->get_result();
            $availability = [];
            
            while ($row = $result->fetch_assoc()) {
                $availability[] = $row;
            }
            
            $stmt->close();
            return $availability;
            
        } catch (Exception $e) {
            error_log("Error in getAvailability: " . $e->getMessage());
            return [];
        }
    }

    // Add a service
    public function addService($provider_id, $service_name, $description, $price) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO provider_services (provider_id, service_name, description, price)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$provider_id, $service_name, $description, $price]);
        } catch (Exception $e) {
            error_log("Error adding service: " . $e->getMessage());
            return false;
        }
    }

    // Fetch all services for a provider
    public function getServices($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM provider_services WHERE provider_id = ?
            ");
            $stmt->execute([$provider_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching services: " . $e->getMessage());
            return [];
        }
    }

    // Update a service
    public function updateService($service_id, $provider_id, $service_name, $description, $price) {
        try {
            $stmt = $this->db->prepare("
                UPDATE provider_services 
                SET service_name = ?, description = ?, price = ? 
                WHERE provider_service_id = ? AND provider_id = ?
            ");
            return $stmt->execute([$service_name, $description, $price, $service_id, $provider_id]);
        } catch (Exception $e) {
            error_log("Error updating service: " . $e->getMessage());
            return false;
        }
    }

    // Delete a service
    public function deleteService($service_id, $provider_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM provider_services WHERE provider_service_id = ? AND provider_id = ?
            ");
            return $stmt->execute([$service_id, $provider_id]);
        } catch (Exception $e) {
            error_log("Error deleting service: " . $e->getMessage());
            return false;
        }
    }
}
?>