<?php
class Appointment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ✅ Get appointments for a provider
    public function getByProvider($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, s.name AS service_name, 
                       CONCAT(u.first_name, ' ', u.last_name) AS patient_name 
                FROM appointments a
                JOIN services s ON a.service_id = s.service_id
                JOIN users u ON a.patient_id = u.user_id
                WHERE a.provider_id = ?
                ORDER BY a.appointment_date, a.start_time
            ");
            $stmt->execute([$provider_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getByProvider: " . $e->getMessage());
            return [];
        }
    }

    // ✅ Get appointments for a patient
    public function getByPatient($patient_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, s.name AS service_name, 
                       CONCAT(u.first_name, ' ', u.last_name) AS provider_name
                FROM appointments a
                JOIN services s ON a.service_id = s.service_id
                JOIN users u ON a.provider_id = u.user_id
                WHERE a.patient_id = ?
                ORDER BY a.appointment_date, a.start_time
            ");
            $stmt->execute([$patient_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getByPatient: " . $e->getMessage());
            return [];
        }
    }

    // ✅ Check if an appointment slot is already booked
    public function isSlotBooked($availability_id) {
        try {
            $stmt = $this->db->prepare("SELECT is_booked FROM availability WHERE availability_id = ?");
            $stmt->execute([$availability_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['is_booked'] == 1;
        } catch (Exception $e) {
            error_log("Error checking slot booking: " . $e->getMessage());
            return false;
        }
    }

    // ✅ Create an appointment while ensuring atomic transaction safety
    public function create($patient_id, $availability_id) {
        try {
            $this->db->beginTransaction();

            // Verify availability exists
            $stmt = $this->db->prepare("SELECT * FROM availability WHERE availability_id = ?");
            $stmt->execute([$availability_id]);
            $availability = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$availability) {
                throw new Exception("Availability slot not found");
            }

            // Insert appointment record
            $stmt = $this->db->prepare("
                INSERT INTO appointments (patient_id, provider_id, appointment_date, start_time, end_time, status)
                VALUES (?, ?, ?, ?, ?, 'scheduled')
            ");
            $stmt->execute([
                $patient_id,
                $availability['provider_id'],
                $availability['availability_date'],
                $availability['start_time'],
                $availability['end_time']
            ]);

            // Mark availability as booked
            $stmt = $this->db->prepare("UPDATE availability SET is_booked = 1 WHERE availability_id = ?");
            $stmt->execute([$availability_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating appointment: " . $e->getMessage());
            return false;
        }
    }

    // ✅ Retrieve all appointments in the system
    public function getAllAppointments() {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       p.first_name AS patient_first_name, p.last_name AS patient_last_name,
                       pr.first_name AS provider_first_name, pr.last_name AS provider_last_name
                FROM appointments a
                JOIN users p ON a.patient_id = p.user_id
                JOIN users pr ON a.provider_id = pr.user_id
                ORDER BY a.appointment_date, a.start_time
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching all appointments: " . $e->getMessage());
            return [];
        }
    }

    // ✅ Fetch appointment details by ID
    public function getAppointmentById($appointment_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
            $stmt->execute([$appointment_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching appointment by ID: " . $e->getMessage());
            return null;
        }
    }

    // ✅ Update appointment status (e.g., Confirmed, Completed, Canceled)
    public function updateStatus($appointment_id, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            return $stmt->execute([$status, $appointment_id]);
        } catch (Exception $e) {
            error_log("Error updating appointment status: " . $e->getMessage());
            return false;
        }
    }
}
?>