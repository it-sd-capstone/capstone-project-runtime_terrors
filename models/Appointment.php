<?php

class Appointment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Schedule an appointment securely
    public function scheduleAppointment($patient_id, $provider_id, $service_id, $appointment_date, $start_time, $end_time, $type = 'in_person', $notes = null, $reason = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO appointments (
                    patient_id, provider_id, service_id, appointment_date, 
                    start_time, end_time, status, type, notes, reason
                )
                VALUES (?, ?, ?, ?, ?, ?, 'scheduled', ?, ?, ?)
            ");
            $stmt->bind_param("iiissssss", $patient_id, $provider_id, $service_id, 
                             $appointment_date, $start_time, $end_time, $type, $notes, $reason);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error scheduling appointment: " . $e->getMessage());
            return false;
        }
    }
    public function isSlotAvailable($provider_id, $appointment_date, $start_time) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM appointments
                WHERE provider_id = ? AND appointment_date = ? AND start_time = ? 
                AND status NOT IN ('canceled', 'no_show')
            ");
            $stmt->bind_param("iss", $provider_id, $appointment_date, $start_time);
            if (!$stmt->execute()) {
                error_log("SQL Execution Error: " . $stmt->error);
                return false;
            }
    
            // Initialize variable
            $count = 0; 
            $stmt->store_result(); // Required for accurate fetching
            $stmt->bind_result($count);
            $stmt->fetch();
    
            return isset($count) && $count == 0; // True if slot is available
        } catch (Exception $e) {
            error_log("Error checking availability: " . $e->getMessage());
            return false;
        }
    
    }
    public function isSlotTaken($provider_id, $date, $time_slot) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM appointments 
            WHERE provider_id = ? AND appointment_date = ? AND time_slot = ?
        ");
        $stmt->bind_param("iss", $provider_id, $date, $time_slot);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_array();
        return $result[0] > 0;
    }

    // Get upcoming appointments for a patient
    public function getUpcomingAppointments($patient_id) {
        if ($this->db instanceof mysqli) {
            $query = "SELECT a.*, s.name as service_name, 
                  CONCAT(u.first_name, ' ', u.last_name) as provider_name
                  FROM appointments a
                  JOIN services s ON a.service_id = s.service_id
                  JOIN users u ON a.provider_id = u.user_id
                  WHERE a.patient_id = ? AND a.appointment_date >= CURDATE()
                  ORDER BY a.appointment_date, a.start_time";
        
            $stmt = $this->db->prepare($query);
            // Debug the SQL error if prepare fails
            if (!$stmt) {
                error_log("SQL Error in getUpcomingAppointments: " . $this->db->error);
                return [];
            }
        
            $stmt->bind_param("i", $patient_id);
            $result = $stmt->execute();
        
            // Debug execution error
            if (!$result) {
                error_log("Execute Error in getUpcomingAppointments: " . $stmt->error);
                return [];
            }
        
            $result = $stmt->get_result();
        
            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        
            return $appointments;
        }
    
        // Add PDO implementation if needed
    
        return [];
    }

    // Get past appointments for a patient
    public function getPastAppointments($patient_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.first_name AS provider_first_name,
                       u.last_name AS provider_last_name,
                       s.name AS service_name
                FROM appointments a
                JOIN users u ON a.provider_id = u.user_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.patient_id = ? 
                AND (a.status = 'completed' OR a.appointment_date < CURDATE())
                ORDER BY a.appointment_date DESC
            ");
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching past appointments: " . $e->getMessage());
            return [];
        }
    }

    // Retrieve an appointment by ID
    public function getAppointmentById($appointment_id) {
        $query = "SELECT a.*, 
              CONCAT(u.first_name, ' ', u.last_name) as provider_name,
              s.name as service_name
              FROM appointments a
              JOIN users u ON a.provider_id = u.user_id
              JOIN services s ON a.service_id = s.service_id
              WHERE a.appointment_id = ?";
              
        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$appointment_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    
        return null;
    }

    // Add the missing getByProvider method
    public function getByProvider($provider_id) {
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

    // Reschedule an appointment securely
    public function rescheduleAppointment($appointment_id, $new_date, $new_start_time, $new_end_time) {
        try {
            $stmt = $this->db->prepare("
                UPDATE appointments
                SET appointment_date = ?, 
                    start_time = ?, 
                    end_time = ?,
                    status = 'scheduled',
                    updated_at = CURRENT_TIMESTAMP
                WHERE appointment_id = ?
            ");
            $stmt->bind_param("sssi", $new_date, $new_start_time, $new_end_time, $appointment_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error rescheduling appointment: " . $e->getMessage());
            return false;
        }
    }

    // Cancel an appointment securely
    public function cancelAppointment($appointment_id, $reason) {
        try {
            $stmt = $this->db->prepare("
                UPDATE appointments
                SET status = 'canceled', 
                    reason = ?,
                    canceled_at = CURRENT_TIMESTAMP,
                    updated_at = CURRENT_TIMESTAMP
                WHERE appointment_id = ?
            ");
            $stmt->bind_param("si", $reason, $appointment_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error canceling appointment: " . $e->getMessage());
            return false;
        }
    }
    public function fetchAppointments() {
        require_once MODEL_PATH . '/Appointments.php';
        $appointmentModel = new Appointments($this->db);
    
        $patient_id = $_SESSION['user_id'];
        $total = $appointmentModel->countAppointments($patient_id);
        $completed = $appointmentModel->countCompletedAppointments($patient_id);
        $upcoming = $appointmentModel->countUpcomingAppointments($patient_id);
    
        header("Content-Type: application/json");
        echo json_encode([
            "total" => $total,
            "completed" => $completed,
            "upcoming" => $upcoming
        ]);
        exit;
    }
}
?>