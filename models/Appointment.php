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

    // Get all appointments with patient and provider details
    public function getAllAppointments() {
        try {
            $query = "SELECT a.*,
                CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
                CONCAT(pr.first_name, ' ', pr.last_name) AS provider_name,
                s.name AS service_name
                FROM appointments a
                JOIN users p ON a.patient_id = p.user_id
                JOIN users pr ON a.provider_id = pr.user_id
                JOIN services s ON a.service_id = s.service_id
                ORDER BY a.appointment_date DESC, a.start_time DESC";
        
            $result = $this->db->query($query);
            $appointments = [];
        
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $appointments[] = $row;
                }
            }
        
            return $appointments;
        } catch (Exception $e) {
            error_log("Error getting all appointments: " . $e->getMessage());
            return [];
        }
    }

    // Get count of appointments by status
    public function getCountByStatus($status) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM appointments WHERE status = ?");
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $row = $result->fetch_assoc();
                return $row['count'];
            }
            return 0;
        } catch (Exception $e) {
            error_log("Error counting appointments by status: " . $e->getMessage());
            return 0;
        }
    }

    // Update appointment status
    public function updateStatus($appointmentId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            $stmt->bind_param("si", $status, $appointmentId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating appointment status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the total count of appointments
     * @return int Total number of appointments
     */
    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM appointments");
        if ($stmt) {
            $result = $stmt->fetch_assoc();
            return $result['count'];
        }
        return 0;
    }
    
        /**
     * Get appointments by date range
     * 
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @param int $providerId Optional provider ID to filter by
     * @return array List of appointments in the date range
     */
    public function getAppointmentsByDateRange($startDate, $endDate, $providerId = null) {
        try {
            $query = "SELECT a.*,
                CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
                CONCAT(pr.first_name, ' ', pr.last_name) AS provider_name,
                s.name AS service_name
                FROM appointments a
                JOIN users p ON a.patient_id = p.user_id
                JOIN users pr ON a.provider_id = pr.user_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.appointment_date BETWEEN ? AND ?";
            
            $params = [$startDate, $endDate];
            $types = "ss";
            
            if ($providerId) {
                $query .= " AND a.provider_id = ?";
                $params[] = $providerId;
                $types .= "i";
            }
            
            $query .= " ORDER BY a.appointment_date, a.start_time";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
            
            return $appointments;
        } catch (Exception $e) {
            error_log("Error getting appointments by date range: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a specific appointment slot is already booked
     * 
     * @param int $providerId Provider ID
     * @param string $date Appointment date (Y-m-d format)
     * @param string $startTime Start time (H:i:s format)
     * @param string $endTime End time (H:i:s format)
     * @param int $excludeAppointmentId Optional appointment ID to exclude
     * @return bool True if slot is booked, false otherwise
     */
    public function isSlotBooked($providerId, $date, $startTime, $endTime, $excludeAppointmentId = null) {
        try {
            $query = "SELECT COUNT(*) as count 
                FROM appointments 
                WHERE provider_id = ? 
                AND appointment_date = ? 
                AND (
                    (start_time <= ? AND end_time > ?) OR 
                    (start_time < ? AND end_time >= ?) OR
                    (start_time >= ? AND end_time <= ?)
                )
                AND status NOT IN ('canceled', 'no_show')";
            
            $params = [
                $providerId, $date, 
                $startTime, $startTime, 
                $endTime, $endTime,
                $startTime, $endTime
            ];
            $types = "isssssss";
            
            if ($excludeAppointmentId) {
                $query .= " AND appointment_id != ?";
                $params[] = $excludeAppointmentId;
                $types .= "i";
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error checking if slot is booked: " . $e->getMessage());
            return true; // Assume booked if error occurs to prevent double booking
        }
    }

    /**
     * Get appointment statistics (daily, weekly, monthly)
     * 
     * @param string $period Period type ('daily', 'weekly', 'monthly')
     * @param string $startDate Start date for statistics (Y-m-d format)
     * @param int $limit Number of periods to return
     * @return array Statistics data
     */
    public function getAppointmentStatistics($period = 'weekly', $startDate = null, $limit = 8) {
        try {
            if (!$startDate) {
                $startDate = date('Y-m-d');
            }
            
            switch ($period) {
                case 'daily':
                    $groupFormat = '%Y-%m-%d';
                    $intervalUnit = 'DAY';
                    break;
                case 'monthly':
                    $groupFormat = '%Y-%m';
                    $intervalUnit = 'MONTH';
                    break;
                case 'weekly':
                default:
                    $groupFormat = '%Y-%u'; // Year and week number
                    $intervalUnit = 'WEEK';
                    break;
            }
            
            $query = "
                SELECT 
                    DATE_FORMAT(appointment_date, ?) as period,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled,
                    SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show
                FROM 
                    appointments
                WHERE 
                    appointment_date <= ?
                    AND appointment_date >= DATE_SUB(?, INTERVAL ? {$intervalUnit})
                GROUP BY 
                    period
                ORDER BY 
                    appointment_date DESC
                LIMIT ?
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssii", $groupFormat, $startDate, $startDate, $limit, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $statistics = [];
            while ($row = $result->fetch_assoc()) {
                $statistics[] = $row;
            }
            
            return $statistics;
        } catch (Exception $e) {
            error_log("Error getting appointment statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find available appointment slots for a provider
     * 
     * @param int $providerId Provider ID
     * @param string $date Date to check (Y-m-d format)
     * @param int $serviceId Optional service ID to consider service duration
     * @return array Available time slots
     */
    public function findAvailableSlots($providerId, $date, $serviceId = null) {
        try {
            // First get provider availability for the day
            $availQuery = "
                SELECT 
                    available_date, start_time, end_time
                FROM 
                    provider_availability
                WHERE 
                    provider_id = ? AND available_date = ?
            ";
            
            $availStmt = $this->db->prepare($availQuery);
            $availStmt->bind_param("is", $providerId, $date);
            $availStmt->execute();
            $availResult = $availStmt->get_result();
            
            // If provider has no availability that day, return empty array
            if ($availResult->num_rows === 0) {
                return [];
            }
            
            $availability = [];
            while ($row = $availResult->fetch_assoc()) {
                $availability[] = $row;
            }
            
            // Get service duration if service ID is provided
            $slotDuration = 30; // Default 30-minute slots
            if ($serviceId) {
                $serviceQuery = "SELECT duration FROM services WHERE service_id = ?";
                $serviceStmt = $this->db->prepare($serviceQuery);
                $serviceStmt->bind_param("i", $serviceId);
                $serviceStmt->execute();
                $serviceResult = $serviceStmt->get_result();
                
                if ($serviceRow = $serviceResult->fetch_assoc()) {
                    $slotDuration = $serviceRow['duration'];
                }
            }
            
            // Get booked appointments for the day
            $bookedQuery = "
                SELECT 
                    start_time, end_time
                FROM 
                    appointments
                WHERE 
                    provider_id = ? 
                    AND appointment_date = ?
                    AND status NOT IN ('canceled', 'no_show')
                ORDER BY 
                    start_time
            ";
            
            $bookedStmt = $this->db->prepare($bookedQuery);
            $bookedStmt->bind_param("is", $providerId, $date);
            $bookedStmt->execute();
            $bookedResult = $bookedStmt->get_result();
            
            $bookedSlots = [];
            while ($row = $bookedResult->fetch_assoc()) {
                $bookedSlots[] = $row;
            }
            
            // Generate available time slots
            $availableSlots = [];
            
            foreach ($availability as $avail) {
                $startTime = strtotime($avail['start_time']);
                $endTime = strtotime($avail['end_time']);
                
                // Generate slots in provider's available time
                for ($time = $startTime; $time < $endTime; $time += $slotDuration * 60) {
                    $slotStart = date('H:i:s', $time);
                    $slotEnd = date('H:i:s', $time + $slotDuration * 60);
                    
                    // Check if this slot overlaps with any booked appointment
                    $isBooked = false;
                    foreach ($bookedSlots as $booked) {
                        $bookedStart = $booked['start_time'];
                        $bookedEnd = $booked['end_time'];
                        
                        if (
                            ($slotStart < $bookedEnd && $slotEnd > $bookedStart)
                        ) {
                            $isBooked = true;
                            break;
                        }
                    }
                    
                    if (!$isBooked) {
                        $availableSlots[] = [
                            'date' => $date,
                            'start_time' => $slotStart,
                            'end_time' => $slotEnd
                        ];
                    }
                }
            }
            
            return $availableSlots;
        } catch (Exception $e) {
            error_log("Error finding available slots: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Get available provider slots for appointments
     * @return array Available time slots
     */
    public function getAvailableSlots() {
        $slots = [];
        $stmt = $this->db->query("
            SELECT pa.*, u.first_name, u.last_name 
            FROM provider_availability pa
            JOIN users u ON pa.provider_id = u.user_id
            WHERE pa.is_available = 1
                AND pa.available_date >= CURDATE()
            ORDER BY pa.available_date, pa.start_time
        ");
        
        if ($stmt) {
            while ($row = $stmt->fetch_assoc()) {
                $slots[] = $row;
            }
        }
        
        return $slots;
    }
    /**
     * Update all appointment fields
     * 
     * @param int $appointmentId Appointment ID
     * @param array $data Appointment data to update
     * @return bool Success status
     */
    public function updateAppointment($appointmentId, $data) {
        try {
            $query = "UPDATE appointments SET 
                patient_id = ?, provider_id = ?, service_id = ?,
                appointment_date = ?, start_time = ?, end_time = ?,
                status = ?, type = ?, notes = ?, reason = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE appointment_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param(
                "iiisssssssi", 
                $data['patient_id'], $data['provider_id'], $data['service_id'],
                $data['appointment_date'], $data['start_time'], $data['end_time'],
                $data['status'], $data['type'], $data['notes'], $data['reason'],
                $appointmentId
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating appointment: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Book an appointment with availability check in a single transaction
     * 
     * @param int $patientId Patient ID
     * @param int $availabilityId Availability ID
     * @param int $serviceId Service ID
     * @param string $type Appointment type
     * @param string $notes Notes
     * @param string $reason Reason for appointment
     * @return array|bool Array with appointment ID on success, false on failure
     */
    public function bookAppointment($patientId, $availabilityId, $serviceId, $type = 'in_person', $notes = '', $reason = '') {
        // Start transaction
        $this->db->begin_transaction();
        
        try {
            // Get availability details
            $stmt = $this->db->prepare("
                SELECT provider_id, available_date, start_time, end_time
                FROM provider_availability
                WHERE availability_id = ? AND is_available = 1
            ");
            $stmt->bind_param("i", $availabilityId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                throw new Exception("Selected time slot is not available");
            }
            
            $availability = $result->fetch_assoc();
            
            // Get service duration
            $stmtService = $this->db->prepare("SELECT duration FROM services WHERE service_id = ?");
            $stmtService->bind_param("i", $serviceId);
            $stmtService->execute();
            $serviceResult = $stmtService->get_result();
            
            if (!$serviceResult || $serviceResult->num_rows === 0) {
                throw new Exception("Service not found");
            }
            
            $service = $serviceResult->fetch_assoc();
            
            // Calculate end time based on service duration
            $startTime = new DateTime($availability['start_time']);
            $endTime = clone $startTime;
            $endTime->add(new DateInterval('PT' . $service['duration'] . 'M'));
            $endTimeStr = $endTime->format('H:i:s');
            
            // Schedule the appointment
            $result = $this->scheduleAppointment(
                $patientId,
                $availability['provider_id'],
                $serviceId,
                $availability['available_date'],
                $availability['start_time'],
                $endTimeStr,
                $type,
                $notes,
                $reason
            );
            
            if (!$result) {
                throw new Exception("Failed to create appointment");
            }
            
            // Mark the availability as no longer available
            $updateStmt = $this->db->prepare("
                UPDATE provider_availability
                SET is_available = 0
                WHERE availability_id = ?
            ");
            $updateStmt->bind_param("i", $availabilityId);
            
            if (!$updateStmt->execute()) {
                throw new Exception("Failed to update availability");
            }
            
            // Get the appointment ID
            $stmt = $this->db->prepare("
                SELECT MAX(appointment_id) as id 
                FROM appointments
                WHERE patient_id = ? AND provider_id = ? 
                AND appointment_date = ? AND start_time = ?
            ");
            $providerId = $availability['provider_id'];
            $appointmentDate = $availability['available_date'];
            $startTimeStr = $availability['start_time'];
            
            $stmt->bind_param("iiss", $patientId, $providerId, $appointmentDate, $startTimeStr);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $this->db->commit();
            
            return [
                'appointment_id' => $row['id'],
                'provider_id' => $providerId,
                'appointment_date' => $appointmentDate,
                'start_time' => $startTimeStr,
                'end_time' => $endTimeStr,
                'service_id' => $serviceId
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error booking appointment: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Restore availability slot when an appointment is canceled
     * 
     * @param int $availabilityId Availability ID
     * @return bool Success status
     */
    public function restoreAvailabilitySlot($availabilityId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE provider_availability 
                SET is_available = 1 
                WHERE availability_id = ?
            ");
            $stmt->bind_param("i", $availabilityId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error restoring availability: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get appointment counts by type
     * 
     * @return array Counts of appointments by type
     */
    public function getAppointmentCountsByType() {
        try {
            $query = "
                SELECT 
                    type, 
                    COUNT(*) as count
                FROM 
                    appointments
                WHERE 
                    status NOT IN ('canceled', 'no_show')
                GROUP BY 
                    type
            ";
            
            $result = $this->db->query($query);
            
            $counts = [];
            while ($row = $result->fetch_assoc()) {
                $counts[$row['type']] = $row['count'];
            }
            
            return $counts;
        } catch (Exception $e) {
            error_log("Error getting appointment counts by type: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get count of booked slots
     * @return int Number of booked appointment slots
     */
    public function getBookedSlotsCount() {
        try {
            // Join appointments to availability based on provider, date and time overlap
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT a.appointment_id) as count 
                FROM appointments a
                JOIN provider_availability pa ON 
                    a.provider_id = pa.provider_id AND
                    a.appointment_date = pa.available_date AND
                    a.start_time >= pa.start_time AND
                    a.end_time <= pa.end_time
                WHERE a.status NOT IN ('canceled', 'no_show')
            ");
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($row = $result->fetch_assoc()) {
                return $row['count'];
            }
            return 0;
        } catch (Exception $e) {
            error_log("Database error in getBookedSlotsCount: " . $e->getMessage());
            return 0;
        }
    }
}
?>