<?php

class Appointment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Schedule an appointment securely
    public function scheduleAppointment($patient_id, $provider_id, $appointment_date, $start_time) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO appointments (patient_id, provider_id, appointment_date, start_time, status)
                VALUES (?, ?, ?, ?, 'Scheduled')
            ");
            $stmt->bind_param("iiss", $patient_id, $provider_id, $appointment_date, $start_time);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error scheduling appointment: " . $e->getMessage());
            return false;
        }
    }
    public function isSlotAvailable($provider_id, $appointment_date, $appointment_time) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM appointments 
                WHERE provider_id = ? AND appointment_date = ? AND start_time = ?
            ");
            $stmt->bind_param("iss", $provider_id, $appointment_date, $appointment_time);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            return $count == 0; // True if slot is available
        } catch (Exception $e) {
            error_log("Error checking availability: " . $e->getMessage());
            return false;
        }
    }

    // Get upcoming appointments for a patient
    public function getUpcomingAppointments($patient_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, p.first_name AS provider_name, s.service_name 
                FROM appointments a
                JOIN providers p ON a.provider_id = p.provider_id
                JOIN provider_services s ON a.service_id = s.provider_service_id
                WHERE a.patient_id = ? AND a.status = 'Scheduled'
                ORDER BY a.appointment_date, a.start_time
            ");
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching upcoming appointments: " . $e->getMessage());
            return [];
        }
    }

    // Get past appointments for a patient
    public function getPastAppointments($patient_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, p.first_name AS provider_name, s.service_name 
                FROM appointments a
                JOIN providers p ON a.provider_id = p.provider_id
                JOIN provider_services s ON a.service_id = s.provider_service_id
                WHERE a.patient_id = ? AND a.status = 'Completed'
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
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, p.first_name AS provider_name, s.service_name 
                FROM appointments a
                JOIN providers p ON a.provider_id = p.provider_id
                JOIN provider_services s ON a.service_id = s.provider_service_id
                WHERE a.appointment_id = ?
            ");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc() ?: [];
        } catch (Exception $e) {
            error_log("Error fetching appointment details: " . $e->getMessage());
            return [];
        }
    }

    // Reschedule an appointment securely
    public function rescheduleAppointment($appointment_id, $new_date, $new_time) {
        try {
            $stmt = $this->db->prepare("
                UPDATE appointments 
                SET appointment_date = ?, start_time = ?, status = 'Rescheduled'
                WHERE appointment_id = ?
            ");
            $stmt->bind_param("ssi", $new_date, $new_time, $appointment_id);
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
                SET status = 'Canceled', cancel_reason = ?
                WHERE appointment_id = ?
            ");
            $stmt->bind_param("si", $reason, $appointment_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error canceling appointment: " . $e->getMessage());
            return false;
        }
    }
}
?>