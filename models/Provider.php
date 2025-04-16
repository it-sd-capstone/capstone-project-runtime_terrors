<?php
class Provider {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get all available slots for a provider (excluding booked ones)
    public function getAvailableSlots($provider_id) {
        $stmt = $this->db->prepare("
            SELECT pa.*
            FROM provider_availability pa
            LEFT JOIN appointments a ON pa.availability_id = a.availability_id
            WHERE pa.provider_id = ? AND a.availability_id IS NULL
        ");
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get booked appointments for a provider
    public function getBookedAppointments($provider_id) {
        $stmt = $this->db->prepare("
            SELECT a.*, u.first_name AS patient_name
            FROM appointments a
            JOIN users u ON a.patient_id = u.user_id
            WHERE a.provider_id = ?
        ");
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add provider availability
    public function addAvailability($provider_id, $available_date, $start_time, $end_time) {
        $stmt = $this->db->prepare("
            INSERT INTO provider_availability (provider_id, available_date, start_time, end_time)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$provider_id, $available_date, $start_time, $end_time]);
    }

    // Check if an availability slot is already booked
    public function isSlotBooked($availability_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM appointments WHERE availability_id = ?
        ");
        $stmt->execute([$availability_id]);
        return $stmt->fetchColumn() > 0;
    }
}
?>