<?php
class Appointment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Retrieve appointments for a provider
    public function getByProvider($provider_id) {
        $stmt = $this->db->prepare("
            SELECT a.appointment_id, a.patient_id, a.appointment_date, a.start_time, a.end_time, 
                   a.status, p.first_name AS patient_name, s.name AS service_name
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            JOIN services s ON a.service_id = s.service_id
            WHERE a.provider_id = ?
            ORDER BY a.appointment_date, a.start_time
        ");
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve an appointment by ID
    public function getById($appointment_id) {
        $stmt = $this->db->prepare("
            SELECT a.*, p.first_name AS patient_name, s.name AS service_name, pr.first_name AS provider_name
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            JOIN users pr ON a.provider_id = pr.user_id
            JOIN services s ON a.service_id = s.service_id
            WHERE a.appointment_id = ?
        ");
        $stmt->execute([$appointment_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Book a new appointment
    public function bookAppointment($patient_id, $provider_id, $service_id, $date, $start_time, $end_time, $notes) {
        $stmt = $this->db->prepare("
            INSERT INTO appointments (patient_id, provider_id, service_id, appointment_date, start_time, end_time, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, 'scheduled', ?)
        ");
        return $stmt->execute([$patient_id, $provider_id, $service_id, $date, $start_time, $end_time, $notes]);
    }
    public function cancelAppointment($appointment_id, $reason) {
        $stmt = $this->db->prepare("
            UPDATE appointments SET status = 'canceled', reason = ? WHERE appointment_id = ?
        ");
        $stmt->execute([$reason, $appointment_id]);
    
        // Log in `appointment_history`
        $stmt = $this->db->prepare("
            INSERT INTO appointment_history (appointment_id, action, changed_fields, old_values, new_values, user_id) 
            VALUES (?, 'canceled', 'status', 'scheduled', 'canceled', ?)
        ");
        $stmt->execute([$appointment_id, $_SESSION['user_id'] ?? 1]); // Replace with actual user ID logic
    }
}
?>