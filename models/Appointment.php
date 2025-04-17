<?php

class Appointment {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getByProvider($provider_id) {
        try {
            if ($this->db instanceof mysqli) {
                // MySQLi implementation
                $query = "SELECT a.*, s.name as service_name, 
                          CONCAT(u.first_name, ' ', u.last_name) as patient_name 
                          FROM appointments a
                          JOIN services s ON a.service_id = s.service_id
                          JOIN users u ON a.patient_id = u.user_id
                          WHERE a.provider_id = ?
                          ORDER BY a.appointment_date, a.start_time";
            
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $provider_id);
                $stmt->execute();
            
                $result = $stmt->get_result();
            
                $appointments = [];
                while ($row = $result->fetch_assoc()) {
                    $appointments[] = $row;
                }
            
                return $appointments;
            
            } elseif ($this->db instanceof PDO) {
                // PDO implementation
                $query = "SELECT a.*, s.name as service_name, 
                          CONCAT(u.first_name, ' ', u.last_name) as patient_name
                          FROM appointments a
                          JOIN services s ON a.service_id = s.service_id
                          JOIN users u ON a.patient_id = u.user_id
                          WHERE a.provider_id = :provider_id
                          ORDER BY a.appointment_date, a.start_time";
            
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':provider_id', $provider_id, PDO::PARAM_INT);
                $stmt->execute();
            
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Unsupported database connection type");
            }
        } catch (Exception $e) {
            error_log("Error in getByProvider: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getByPatient($patient_id) {
        try {
            if ($this->db instanceof mysqli) {
                // MySQLi implementation
                $query = "SELECT a.*, s.name as service_name, 
                          CONCAT(u.first_name, ' ', u.last_name) as provider_name
                          FROM appointments a
                          JOIN services s ON a.service_id = s.service_id
                          JOIN users u ON a.provider_id = u.user_id
                          WHERE a.patient_id = ?
                          ORDER BY a.appointment_date, a.start_time";
            
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $patient_id);
                $stmt->execute();
            
                $result = $stmt->get_result();
            
                $appointments = [];
                while ($row = $result->fetch_assoc()) {
                    $appointments[] = $row;
                }
            
                return $appointments;
            
            } elseif ($this->db instanceof PDO) {
                // PDO implementation
                $query = "SELECT a.*, s.name as service_name, 
                          CONCAT(u.first_name, ' ', u.last_name) as provider_name
                          FROM appointments a
                          JOIN services s ON a.service_id = s.service_id
                          JOIN users u ON a.provider_id = u.user_id
                          WHERE a.patient_id = :patient_id
                          ORDER BY a.appointment_date, a.start_time";
            
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
                $stmt->execute();
            
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Unsupported database connection type");
            }
        } catch (Exception $e) {
            error_log("Error in getByPatient: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function isSlotBooked($availability_id) {
        $stmt = $this->db->prepare("SELECT is_booked FROM provider_availability WHERE availability_id = ?");
        $stmt->execute([$availability_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['is_booked'] == 1;
    }
    
    public function create($patient_id, $availability_id) {
        try {
            $this->db->beginTransaction();
            
            // Get availability details
            $stmt = $this->db->prepare("SELECT * FROM provider_availability WHERE availability_id = ?");
            $stmt->execute([$availability_id]);
            $availability = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$availability) {
                $this->db->rollBack();
                return false;
            }
            
            // Create appointment
            $stmt = $this->db->prepare("INSERT INTO appointments (patient_id, provider_id, appointment_date, start_time, end_time, status)
                                        VALUES (?, ?, ?, ?, ?, 'scheduled')");
            $stmt->execute([
                $patient_id,
                $availability['provider_id'],
                $availability['available_date'],
                $availability['start_time'],
                $availability['end_time']
            ]);
            
            // Mark availability as booked
            $stmt = $this->db->prepare("UPDATE provider_availability SET is_booked = 1 WHERE availability_id = ?");
            $stmt->execute([$availability_id]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function getAllAppointments() {
        $stmt = $this->db->prepare("SELECT a.*, 
                                   p.first_name as patient_first_name, p.last_name as patient_last_name,
                                   pr.first_name as provider_first_name, pr.last_name as provider_last_name
                                   FROM appointments a
                                   JOIN users p ON a.patient_id = p.user_id
                                   JOIN users pr ON a.provider_id = pr.user_id
                                   ORDER BY a.appointment_date, a.start_time");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAppointmentById($appointment_id) {
        $stmt = $this->db->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateStatus($appointment_id, $status) {
        $stmt = $this->db->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
        return $stmt->execute([$status, $appointment_id]);
    }
}
