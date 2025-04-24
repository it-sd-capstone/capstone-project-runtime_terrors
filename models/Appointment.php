<?php
class Appointment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Retrieve Appointments for a Specific Provider
    public function getByProvider($provider_id) {
        $stmt = $this->db->prepare("
            SELECT a.*, p.first_name AS patient_name, s.name AS service_name
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            JOIN services s ON a.service_id = s.service_id
            WHERE a.provider_id = ?
            ORDER BY a.appointment_date, a.start_time
        ");
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve Appointments for a Specific Patient
    public function getByPatient($patient_id) {
        $stmt = $this->db->prepare("
            SELECT a.*, pr.first_name AS provider_name, s.name AS service_name
            FROM appointments a
            JOIN users pr ON a.provider_id = pr.user_id
            JOIN services s ON a.service_id = s.service_id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date, a.start_time
        ");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Book a New Appointment
    public function bookAppointment($patient_id, $provider_id, $service_id, $date, $start_time, $end_time, $notes) {
        $stmt = $this->db->prepare("
            INSERT INTO appointments (patient_id, provider_id, service_id, appointment_date, start_time, end_time, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, 'scheduled', ?)
        ");
        return $stmt->execute([$patient_id, $provider_id, $service_id, $date, $start_time, $end_time, $notes]);
    }

    // Reschedule an Appointment
    public function rescheduleAppointment($appointment_id, $new_date, $new_time) {
        $stmt = $this->db->prepare("
            UPDATE appointments SET appointment_date = ?, start_time = ?, status = 'rescheduled' 
            WHERE appointment_id = ?
        ");
        return $stmt->execute([$new_date, $new_time, $appointment_id]);
    }

    // Cancel an Appointment & Log History
    public function cancelAppointment($appointment_id, $reason, $user_id) {
        $stmt = $this->db->prepare("
            UPDATE appointments SET status = 'canceled', reason = ? WHERE appointment_id = ?
        ");
        $stmt->execute([$reason, $appointment_id]);

        // Log cancellation in appointment_history
        $stmt = $this->db->prepare("
            INSERT INTO appointment_history (appointment_id, action, changed_fields, old_values, new_values, user_id) 
            VALUES (?, 'canceled', 'status', 'scheduled', 'canceled', ?)
        ");
        return $stmt->execute([$appointment_id, $user_id]);
    }

    // Get Requests Pending Provider Approval
    public function getRequests($provider_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM appointments WHERE provider_id = ? AND status = 'pending'
        ");
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Approve Appointment Request
    public function approveRequest($request_id) {
        $stmt = $this->db->prepare("
            UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?
        ");
        return $stmt->execute([$request_id]);
    }

    // Decline Appointment Request
    public function declineRequest($request_id) {
        $stmt = $this->db->prepare("
            UPDATE appointments SET status = 'canceled' WHERE appointment_id = ?
        ");
        return $stmt->execute([$request_id]);
    }
}
?>