<?php

require_once __DIR__ . '/../helpers/system_notifications.php';
class Appointment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function scheduleAppointment($patient_id, $provider_id, $service_id, $date, $start_time, $end_time, $type, $notes, $reason) {
        error_log("scheduleAppointment called with: patient=$patient_id, provider=$provider_id, service=$service_id, date=$date, start=$start_time, end=$end_time");
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO appointments (
                    patient_id, provider_id, service_id, appointment_date,
                    start_time, end_time, status, type, notes, reason, created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, 'confirmed', ?, ?, ?, NOW())
            ");
            if (!$stmt) {
                error_log("SQL prepare error: " . $this->db->error);
                return false;
            }
            $stmt->bind_param("iiissssss", 
                $patient_id, $provider_id, $service_id,
                $date, $start_time, $end_time, $type, $notes, $reason
            );
            $result = $stmt->execute();
            if (!$result) {
                error_log("SQL execution error: " . $stmt->error);
                return false;
            }
            $appointment_id = $stmt->insert_id;
            $stmt->close();
            return $appointment_id > 0 ? $appointment_id : false;
        } catch (Exception $e) {
            error_log("Error in scheduleAppointment: " . $e->getMessage());
            return false;
        }
    }

    public function isSlotAvailable($provider_id, $appointment_date, $start_time, $end_time, $exclude_appointment_id = null) {
        try {
            // NEW CODE: Check if appointment time is in the past
            $now = new DateTime();
            $appointmentDateTime = new DateTime($appointment_date . ' ' . $start_time);
            if ($appointmentDateTime <= $now) {
                error_log("Attempted to check availability for past time: {$appointment_date} {$start_time}");
                return false;
            }
            
            // Rest of the existing method remains unchanged
            $query = "
                SELECT COUNT(*) as count FROM appointments
                WHERE provider_id = ? AND appointment_date = ?
                AND (
                    (start_time < ? AND end_time > ?) OR
                    (start_time < ? AND end_time > ?) OR
                    (start_time >= ? AND end_time <= ?)
                )
                AND status NOT IN ('canceled', 'no_show')
            ";
            $params = [
                $provider_id, $appointment_date,
                $end_time, $start_time,
                $end_time, $start_time,
                $start_time, $end_time
            ];
            $types = "isssssss";
            if ($exclude_appointment_id) {
                $query .= " AND appointment_id != ?";
                $params[] = $exclude_appointment_id;
                $types .= "i";
            }
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return isset($row['count']) && $row['count'] == 0;
        } catch (Exception $e) {
            error_log("Error checking slot availability: " . $e->getMessage());
            return false;
        }
    }

    public function getUpcomingAppointments($patient_id) {
        $appointments = [];
        $stmt = $this->db->prepare("
            SELECT 
                a.*,
                s.name AS service_name,
                provider.first_name AS provider_first_name,
                provider.last_name AS provider_last_name 
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.service_id
            LEFT JOIN users provider ON a.provider_id = provider.user_id
            WHERE a.patient_id = ?
            AND a.appointment_date >= CURDATE()
            ORDER BY a.appointment_date, a.start_time
        ");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
        return $appointments;
    }

    public function getProviderAppointments($providerId, $startDate = null, $endDate = null) {
        $query = "SELECT a.*, 
                    s.name as service_name, 
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    p.email as patient_email,
                    p.phone as patient_phone
                FROM appointments a
                JOIN users p ON a.patient_id = p.user_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.provider_id = ?";
        
        $params = [$providerId];
        $types = "i";
        
        if ($startDate) {
            $query .= " AND a.appointment_date >= ?";
            $params[] = $startDate;
            $types .= "s";
        }
        
        if ($endDate) {
            $query .= " AND a.appointment_date <= ?";
            $params[] = $endDate;
            $types .= "s";
        }
        
        $query .= " ORDER BY a.appointment_date ASC, a.start_time ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $appointments = [];
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        
        return $appointments;
    }

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

    public function getById($appointment_id) {
        $appointment = null;
        $stmt = $this->db->prepare("
            SELECT 
                a.*,
                s.name AS service_name,
                provider.first_name AS provider_first_name,
                provider.last_name AS provider_last_name,
                CONCAT(provider.first_name, ' ', provider.last_name) AS provider_name,
                patient.first_name AS patient_first_name,
                patient.last_name AS patient_last_name,
                CONCAT(patient.first_name, ' ', patient.last_name) AS patient_name,
                patient.email AS patient_email,
                pp.phone AS patient_phone,
                pp.date_of_birth AS patient_dob,
                pp.address AS patient_address,
                pp.emergency_contact,
                pp.emergency_contact_phone,
                pp.medical_conditions,
                pp.insurance_info
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.service_id
            LEFT JOIN users provider ON a.provider_id = provider.user_id
            LEFT JOIN users patient ON a.patient_id = patient.user_id
            LEFT JOIN patient_profiles pp ON patient.user_id = pp.user_id
            WHERE a.appointment_id = ?
        ");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $appointment = $result->fetch_assoc();
            if (!empty($appointment['insurance_info'])) {
                $insurance = json_decode($appointment['insurance_info'], true);
                $appointment['insurance_provider'] = $insurance['provider'] ?? '';
                $appointment['insurance_policy_number'] = $insurance['policy_number'] ?? '';
            }
        }
        return $appointment;
    }

public function getByProvider($provider_id) {
    try {
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   u.first_name AS patient_first_name,
                   u.last_name AS patient_last_name,
                   s.name AS service_name,
                   provider.first_name AS provider_first_name,
                   provider.last_name AS provider_last_name
            FROM appointments a
            JOIN users u ON a.patient_id = u.user_id
            JOIN services s ON a.service_id = s.service_id
            JOIN users provider ON a.provider_id = provider.user_id
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

    public function rescheduleAppointment($appointment_id, $new_date, $new_start_time, $new_end_time) {
        try {
            $stmt = $this->db->prepare("
                UPDATE appointments
                SET appointment_date = ?, 
                    start_time = ?, 
                    end_time = ?,
                    status = 'confirmed',
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
            $success = $stmt->execute();
            // Log system event only if the update was successful
            if ($success) {
                logSystemEvent('appointment_cancelled', 'An appointment was cancelled in the system', 'Appointment Cancelled');
            }
            return $success;
        } catch (Exception $e) {
            error_log("Error canceling appointment: " . $e->getMessage());
            return false;
        }
    }

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

    public function updateStatus($appointment_id, $status) {
        try {
            $sql = "UPDATE appointments SET status = ?, updated_at = NOW() ";
            if ($status == 'canceled') {
                $sql .= ", canceled_at = NOW() ";
            } else if ($status == 'confirmed') {
                $sql .= ", confirmed_at = NOW() ";
            }
            $sql .= "WHERE appointment_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $status, $appointment_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating appointment status: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM appointments");
        if ($stmt) {
            $result = $stmt->fetch_assoc();
            return $result['count'];
        }
        return 0;
    }

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
                    $groupFormat = '%Y-%u';
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

    public function findAvailableSlots($providerId, $date, $serviceId = null) {
        try {
            $availQuery = "
                SELECT 
                    availability_date, start_time, end_time
                FROM 
                    provider_availability
                WHERE 
                    provider_id = ? AND availability_date = ?
            ";
            $availStmt = $this->db->prepare($availQuery);
            $availStmt->bind_param("is", $providerId, $date);
            $availStmt->execute();
            $availResult = $availStmt->get_result();
            if ($availResult->num_rows === 0) {
                return [];
            }
            $availability = [];
            while ($row = $availResult->fetch_assoc()) {
                $availability[] = $row;
            }
            $slotDuration = 30;
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
            $availableSlots = [];
            foreach ($availability as $avail) {
                $startTime = strtotime($avail['start_time']);
                $endTime = strtotime($avail['end_time']);
                for ($time = $startTime; $time < $endTime; $time += $slotDuration * 60) {
                    $slotStart = date('H:i:s', $time);
                    $slotEnd = date('H:i:s', $time + $slotDuration * 60);
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

    public function getAvailableSlots($provider_id = null) {
        $slots = [];
        $query = "
            SELECT 
                pa.*,
                pa.availability_date AS available_date,
                u.first_name,
                u.last_name
            FROM provider_availability pa
            JOIN users u ON pa.provider_id = u.user_id
            WHERE pa.is_available = 1
                AND pa.availability_date >= CURDATE()
        ";
        if ($provider_id) {
            $query .= " AND pa.provider_id = ?";
        }
        $query .= " ORDER BY pa.availability_date, pa.start_time";
        if ($provider_id) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($query);
        }
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $slots[] = $row;
            }
        }
        return $slots;
    }

    public function bookAppointment($patientId, $availabilityId, $serviceId, $type = 'in_person', $notes = '', $reason = '') {
        $this->db->begin_transaction();
        try {
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
            $stmtService = $this->db->prepare("SELECT duration FROM services WHERE service_id = ?");
            $stmtService->bind_param("i", $serviceId);
            $stmtService->execute();
            $serviceResult = $stmtService->get_result();
            if (!$serviceResult || $serviceResult->num_rows === 0) {
                throw new Exception("Service not found");
            }
            $service = $serviceResult->fetch_assoc();
            $startTime = new DateTime($availability['start_time']);
            $endTime = clone $startTime;
            $endTime->add(new DateInterval('PT' . $service['duration'] . 'M'));
            $endTimeStr = $endTime->format('H:i:s');
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
            $updateStmt = $this->db->prepare("
                UPDATE provider_availability
                SET is_available = 0
                WHERE availability_id = ?
            ");
            $updateStmt->bind_param("i", $availabilityId);
            if (!$updateStmt->execute()) {
                throw new Exception("Failed to update availability");
            }
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

    public function generateBookableSlots($provider_id, $service_id, $date) {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(ps.custom_duration, s.duration) AS duration
            FROM services s
            LEFT JOIN provider_services ps ON ps.service_id = s.service_id AND ps.provider_id = ?
            WHERE s.service_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $provider_id, $service_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $duration = $row ? intval($row['duration']) : 30;

        $stmt = $this->db->prepare("
            SELECT start_time, end_time 
            FROM provider_availability 
            WHERE provider_id = ? 
              AND (availability_date = ? OR (is_recurring = 1 AND FIND_IN_SET(WEEKDAY(?)+1, weekdays)))
              AND is_available = 1
        ");
        $stmt->bind_param("iss", $provider_id, $date, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $availabilityBlocks = [];
        while ($row = $result->fetch_assoc()) {
            $availabilityBlocks[] = [
                'start' => $row['start_time'],
                'end'   => $row['end_time']
            ];
        }

        $stmt = $this->db->prepare("
            SELECT start_time, end_time 
            FROM appointments 
            WHERE provider_id = ? 
              AND appointment_date = ?
              AND status NOT IN ('canceled', 'no_show')
        ");
        $stmt->bind_param("is", $provider_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingAppointments = [];
        while ($row = $result->fetch_assoc()) {
            $existingAppointments[] = [
                'start' => $row['start_time'],
                'end'   => $row['end_time']
            ];
        }

        $slots = [];
        foreach ($availabilityBlocks as $block) {
            $blockStart = new DateTime("$date {$block['start']}");
            $blockEnd   = new DateTime("$date {$block['end']}");
            $slotStart  = clone $blockStart;

            while ($slotStart < $blockEnd) {
                $slotEnd = clone $slotStart;
                $slotEnd->modify("+{$duration} minutes");
                if ($slotEnd > $blockEnd) break;

                $overlap = false;
                foreach ($existingAppointments as $appt) {
                    $apptStart = new DateTime("$date {$appt['start']}");
                    $apptEnd   = new DateTime("$date {$appt['end']}");
                    if ($slotStart < $apptEnd && $slotEnd > $apptStart) {
                        $overlap = true;
                        break;
                    }
                }

                if (!$overlap) {
                    $slots[] = [
                        'start' => $slotStart->format('Y-m-d\TH:i:s'),
                        'end'   => $slotEnd->format('Y-m-d\TH:i:s'),
                    ];
                }
                $slotStart->modify("+{$duration} minutes");
            }
        }
        return $slots;
    }

    public function updateAppointment($appointment_id, $appointmentData) {
        try {
            $query = "UPDATE appointments SET ";
            $params = [];
            $types = "";
            
            $allowedFields = [
                'patient_id' => 'i',
                'provider_id' => 'i',
                'service_id' => 'i',
                'appointment_date' => 's',
                'start_time' => 's',
                'end_time' => 's',
                'status' => 's',
                'type' => 's',
                'notes' => 's',
                'reason' => 's'
            ];
            
            $updateParts = [];
            
            foreach ($allowedFields as $field => $paramType) {
                if (isset($appointmentData[$field])) {
                    $updateParts[] = "$field = ?";
                    $params[] = $appointmentData[$field];
                    $types .= $paramType;
                }
            }
            
            $updateParts[] = "updated_at = NOW()";
            
            if (empty($updateParts)) {
                error_log("No fields to update in appointment");
                return false;
            }
            
            $query .= implode(", ", $updateParts);
            $query .= " WHERE appointment_id = ?";
            $params[] = $appointment_id;
            $types .= "i";
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                error_log("SQL prepare error in updateAppointment: " . $this->db->error);
                return false;
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("SQL execution error in updateAppointment: " . $stmt->error);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Exception updating appointment: " . $e->getMessage());
            return false;
        }
    }

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

   public function getBookedSlotsCount() {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT appointment_id) as count
                FROM appointments
                WHERE status NOT IN ('canceled', 'no_show')
                AND appointment_date >= CURRENT_DATE()
            ");
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return (int)$row['count'];
            }
            
            return 0;
        } catch (Exception $e) {
            error_log("Database error in getBookedSlotsCount: " . $e->getMessage());
            return 0;
        }
    }
    /**
     * Get appointment data grouped by period for analytics
     *
     * @param string $period 'weekly', 'monthly', or 'yearly'
     * @param int $limit Number of periods to return
     * @return array Appointment data grouped by time period
     */
    public function getAppointmentsByPeriod($period = 'monthly', $limit = 12) {
        $result = [];
        
        switch ($period) {
            case 'weekly':
                // Get appointments for the last 7 days grouped by day
                $query = $this->db->query("
                    SELECT
                        DATE(appointment_date) as period_date,
                        DAYNAME(appointment_date) as period_name,
                        COUNT(*) as appointment_count
                    FROM
                        appointments
                    WHERE
                        appointment_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
                    GROUP BY
                        DATE(appointment_date), DAYNAME(appointment_date)
                    ORDER BY
                        DATE(appointment_date) ASC
                ");
                
                // Convert mysqli result to array
                $data = [];
                if ($query && $query instanceof mysqli_result) {
                    while ($row = $query->fetch_assoc()) {
                        $data[] = $row;
                    }
                }
                $result = $data;
                
                // Fill in any missing days with zero counts
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $filledData = [];
                foreach ($days as $day) {
                    $found = false;
                    foreach ($result as $row) {
                        if ($row['period_name'] == $day) {
                            $filledData[] = $row;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $filledData[] = [
                            'period_date' => null,
                            'period_name' => $day,
                            'appointment_count' => 0
                        ];
                    }
                }
                $result = $filledData;
                break;
                
            case 'yearly':
                // Get appointments for the last few years grouped by year
                $query = $this->db->query("
                    SELECT
                        YEAR(appointment_date) as period_name,
                        COUNT(*) as appointment_count
                    FROM
                        appointments
                    WHERE
                        appointment_date >= DATE_SUB(CURDATE(), INTERVAL {$limit} YEAR)
                    GROUP BY
                        YEAR(appointment_date)
                    ORDER BY
                        YEAR(appointment_date) ASC
                    LIMIT {$limit}
                ");
                
                // Convert mysqli result to array
                $data = [];
                if ($query && $query instanceof mysqli_result) {
                    while ($row = $query->fetch_assoc()) {
                        $data[] = $row;
                    }
                }
                $result = $data;
                break;
                
            case 'monthly':
            default:
                // Get appointments for the last 12 months grouped by month
                $query = $this->db->query("
                    SELECT
                        DATE_FORMAT(appointment_date, '%Y-%m') as period_date,
                        DATE_FORMAT(appointment_date, '%b') as period_name,
                        COUNT(*) as appointment_count
                    FROM
                        appointments
                    WHERE
                        appointment_date >= DATE_SUB(CURDATE(), INTERVAL {$limit} MONTH)
                    GROUP BY
                        DATE_FORMAT(appointment_date, '%Y-%m'),
                        DATE_FORMAT(appointment_date, '%b')
                    ORDER BY
                        DATE_FORMAT(appointment_date, '%Y-%m') ASC
                    LIMIT {$limit}
                ");
                
                // Convert mysqli result to array
                $data = [];
                if ($query && $query instanceof mysqli_result) {
                    while ($row = $query->fetch_assoc()) {
                        $data[] = $row;
                    }
                }
                $result = $data;
                
                // Fill in any missing months with zero counts
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $filledData = [];
                foreach ($months as $month) {
                    $found = false;
                    foreach ($result as $row) {
                        if ($row['period_name'] == $month) {
                            $filledData[] = $row;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $filledData[] = [
                            'period_date' => null,
                            'period_name' => $month,
                            'appointment_count' => 0
                        ];
                    }
                }
                $result = $filledData;
                break;
        }
        
        return $result;
    }

    /**
     * Get counts of appointments by status
     *
     * @return array Status counts
     */
    public function getAppointmentStatusCounts() {
        $statuses = ['confirmed', 'completed', 'canceled', 'no-show'];
        $result = [];
        
        foreach ($statuses as $status) {
            $query = $this->db->query("
                SELECT COUNT(*) as count 
                FROM appointments 
                WHERE status = '{$status}'
            ");
            
            if ($query && $query instanceof mysqli_result) {
                $row = $query->fetch_assoc();
                $result[$status] = (int)$row['count'];
            } else {
                $result[$status] = 0;
            }
        }
        
        return $result;
    }

    public function getPatientAppointmentAtTime($patient_id, $date, $time) {
        try {
            $stmt = $this->db->prepare("
                SELECT appointment_id 
                FROM appointments 
                WHERE patient_id = ? 
                AND appointment_date = ? 
                AND start_time = ?
                AND status NOT IN ('canceled', 'no_show')
            ");
            $stmt->bind_param("iss", $patient_id, $date, $time);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error checking patient appointment: " . $e->getMessage());
            return null;
        }
    }

    public function updateNotes($appointment_id, $notes) {
        try {
            $sql = "UPDATE appointments SET notes = ?, updated_at = NOW() WHERE appointment_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $notes, $appointment_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating appointment notes: " . $e->getMessage());
            return false;
        }
    }

    public function getAppointmentHistory($appointment_id) {
        try {
            $history = [];
            
            // 1. Get notifications data
            $query1 = "SELECT 
                        n.*, 
                        u.first_name, 
                        u.last_name, 
                        u.role,
                        n.created_at,
                        'notification' as source_type
                    FROM notifications n
                    LEFT JOIN users u ON n.user_id = u.user_id
                    WHERE n.appointment_id = ?
                    ORDER BY n.created_at ASC";
            
            // 2. Get activity log data for notes updates
            $query2 = "SELECT 
                        a.*, 
                        u.first_name, 
                        u.last_name, 
                        u.role,
                        a.created_at,
                        'activity_log' as source_type
                    FROM activity_log a
                    LEFT JOIN users u ON a.user_id = u.user_id
                    WHERE a.description LIKE ? 
                        AND a.description LIKE ?
                    ORDER BY a.created_at ASC";
            
            if ($this->db instanceof mysqli) {
                // Get notifications
                $stmt1 = $this->db->prepare($query1);
                $stmt1->bind_param("i", $appointment_id);
                $stmt1->execute();
                $result1 = $stmt1->get_result();
                $notificationsHistory = $result1->fetch_all(MYSQLI_ASSOC);
                
                // Get activity logs
                $stmt2 = $this->db->prepare($query2);
                $searchParam1 = "Appointment: notes_updated%";
                $searchParam2 = "%(ID: $appointment_id)%";
                $stmt2->bind_param("ss", $searchParam1, $searchParam2);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $activityHistory = $result2->fetch_all(MYSQLI_ASSOC);
            } else {
                // Get notifications
                $stmt1 = $this->db->prepare($query1);
                $stmt1->execute([$appointment_id]);
                $notificationsHistory = $stmt1->fetchAll(PDO::FETCH_ASSOC);
                
                // Get activity logs
                $stmt2 = $this->db->prepare($query2);
                $searchParam1 = "Appointment: notes_updated%";
                $searchParam2 = "%(ID: $appointment_id)%";
                $stmt2->execute([$searchParam1, $searchParam2]);
                $activityHistory = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Merge both results
            $history = array_merge($notificationsHistory, $activityHistory);
            
            // Sort by created_at
            usort($history, function($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });
            
            return $history;
        } catch (Exception $e) {
            error_log("Error getting appointment history: " . $e->getMessage());
            return [];
        }
    }

    public function getAppointmentLogs($appointment_id) {
        $logs = [];
        $history = $this->getAppointmentHistory($appointment_id);
        $deduplicated = [];
        
        // Deduplicate notifications based on action type and created_at timestamp (rounded to minutes)
        foreach ($history as $record) {
            $action = 'status_changed';
            
            if ($record['source_type'] === 'notification') {
                $subject = strtolower($record['subject'] ?? '');
                
                if (strpos($subject, 'confirmation') !== false || strpos($subject, 'new appointment') !== false) {
                    $action = 'created';
                } elseif (strpos($subject, 'cancelled') !== false || strpos($subject, 'cancellation') !== false) {
                    $action = 'canceled';
                } elseif (strpos($subject, 'rescheduled') !== false) {
                    $action = 'rescheduled';
                }
            } else if ($record['source_type'] === 'activity_log') {
                // Handle activity log entries
                if (strpos($record['description'], 'notes_updated') !== false) {
                    $action = 'notes_updated';
                }
            }
            
            // Create a key based on action and timestamp (to the minute)
            $timestampMinute = date('Y-m-d H:i', strtotime($record['created_at']));
            $key = $action . '_' . $timestampMinute;
            
            // Prioritize patient records over provider records for notifications
            $isPatient = ($record['user_id'] == 99); // Patient user ID
            
            if (!isset($deduplicated[$key]) || $isPatient) {
                $deduplicated[$key] = [
                    'record' => $record,
                    'action' => $action
                ];
            }
        }
        
        // Convert deduplicated records to logs
        foreach ($deduplicated as $item) {
            $record = $item['record'];
            $action = $item['action'];
            $details = [];
            $appointment = $this->getById($appointment_id);
            
            if ($appointment) {
                $details = [
                    'appointment_date' => $appointment['appointment_date'],
                    'start_time' => $appointment['start_time'],
                    'end_time' => $appointment['end_time'],
                    'cancellation_reason' => $appointment['reason'] ?? 'No reason provided',
                    'previous_status' => '',
                    'new_status' => $action === 'canceled' ? 'canceled' : ($appointment['status'] ?? ''),
                    'reason' => $appointment['reason'] ?? ''
                ];
            }
            
            $logs[] = [
                'details' => json_encode($details),
                'action' => $action,
                'created_at' => $record['created_at'],
                'user_name' => ($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''),
                'user_first_name' => $record['first_name'] ?? '',
                'user_last_name' => $record['last_name'] ?? '',
                'user_role' => $record['role'] ?? 'patient'
            ];
        }
        
        // Sort by created_at in ascending order (oldest first)
        usort($logs, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        
        return $logs;
    }

    public function getUpcomingAppointmentsByProvider($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.appointment_id as id,
                    a.appointment_date,
                    a.start_time,
                    a.end_time,
                    a.status,
                    a.notes,
                    a.reason,
                    CONCAT(u.first_name, ' ', u.last_name) as patient_name,
                    s.name as service_name
                FROM appointments a
                JOIN users u ON a.patient_id = u.user_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.provider_id = ?
                AND a.appointment_date >= CURDATE()
                AND a.status NOT IN ('canceled', 'no_show')
                ORDER BY a.appointment_date ASC, a.start_time ASC
                LIMIT 10
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
            return $appointments;
        } catch (Exception $e) {
            error_log("Error fetching upcoming provider appointments: " . $e->getMessage());
            return [];
        }
    }

    public function rateAppointment($appointment_id, $patient_id, $provider_id, $rating, $comment = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO appointment_ratings 
                (appointment_id, patient_id, provider_id, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("iiiis", $appointment_id, $patient_id, $provider_id, $rating, $comment);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error submitting rating: " . $e->getMessage());
            return false;
        }
    }
}
?>