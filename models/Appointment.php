<?php

class Appointment {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Schedule an appointment securely.
     * @return int|false Appointment ID on success, false on failure.
     */
    public function scheduleAppointment($patient_id, $provider_id, $service_id, $date, $start_time, $end_time, $type, $notes, $reason) {
        error_log("scheduleAppointment called with: patient=$patient_id, provider=$provider_id, service=$service_id, date=$date, start=$start_time, end=$end_time");
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO appointments (
                    patient_id, provider_id, service_id, appointment_date,
                    start_time, end_time, status, type, notes, reason, created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, 'scheduled', ?, ?, ?, NOW())
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

    /**
     * Check if a slot is available for a provider.
     * @return bool True if available, false otherwise.
     */
    public function isSlotAvailable($provider_id, $appointment_date, $start_time, $end_time, $exclude_appointment_id = null) {
        try {
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

    /**
     * Get upcoming appointments for a patient.
     * @return array
     */
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

    /**
     * Get past appointments for a patient.
     * @return array
     */
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

    /**
     * Retrieve an appointment by ID.
     * @return array|null
     */
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
                CONCAT(patient.first_name, ' ', patient.last_name) AS patient_name
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.service_id
            LEFT JOIN users provider ON a.provider_id = provider.user_id
            LEFT JOIN users patient ON a.patient_id = patient.user_id
            WHERE a.appointment_id = ?
        ");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $appointment = $result->fetch_assoc();
        }
        return $appointment;
    }

    /**
     * Get appointments for a provider.
     * @return array
     */
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

    /**
     * Reschedule an appointment securely.
     * @return bool
     */
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

    /**
     * Cancel an appointment securely.
     * @return bool
     */
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

    /**
     * Get all appointments with patient and provider details.
     * @return array
     */
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

    /**
     * Get count of appointments by status.
     * @return int
     */
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

    /**
     * Update appointment status.
     * @return bool
     */
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

    /**
     * Get the total count of appointments.
     * @return int
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
     * Get appointments by date range.
     * @return array
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
     * Get appointment statistics (daily, weekly, monthly).
     * @return array
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

    /**
     * Find available appointment slots for a provider.
     * @return array
     */
    public function findAvailableSlots($providerId, $date, $serviceId = null) {
        try {
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

    /**
     * Get available provider slots for appointments.
     * @return array
     */
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

    /**
     * Book an appointment with availability check in a single transaction.
     * @return array|false Array with appointment ID on success, false on failure.
     */
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

    /**
     * Restore availability slot when an appointment is canceled.
     * @return bool
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
     * Generate bookable slots for a provider, service, and date.
     * Returns an array of ['start' => 'YYYY-MM-DDTHH:MM:SS', 'end' => 'YYYY-MM-DDTHH:MM:SS']
     */
    public function generateBookableSlots($provider_id, $service_id, $date) {
        // 1. Get service duration (check for provider override)
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
        $duration = $row ? intval($row['duration']) : 30; // fallback to 30 min

        // 2. Get provider's available blocks for the date
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

        // 3. Get existing appointments for the provider on that date (exclude canceled)
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

        // 4. Generate slots
        $slots = [];
        foreach ($availabilityBlocks as $block) {
            $blockStart = new DateTime("$date {$block['start']}");
            $blockEnd   = new DateTime("$date {$block['end']}");
            $slotStart  = clone $blockStart;

            while ($slotStart < $blockEnd) {
                $slotEnd = clone $slotStart;
                $slotEnd->modify("+{$duration} minutes");
                if ($slotEnd > $blockEnd) break;

                // Check for overlap with existing appointments
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

    /**
     * Get appointment counts by type.
     * @return array
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
     * Get count of booked slots.
     * @return int
     */
    public function getBookedSlotsCount() {
        try {
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

    /**
     * Check if patient already has an appointment at the specified time.
     * @return array|null
     */
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

    /**
     * Update appointment notes.
     * @return bool
     */
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

    /**
     * Get history of an appointment's status changes and updates.
     * @return array
     */
    public function getAppointmentHistory($appointment_id) {
        $history = [];
        $stmt = $this->db->prepare("
            SELECT 
                ah.*,
                u.first_name,
                u.last_name,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM appointment_history ah
            LEFT JOIN users u ON ah.user_id = u.user_id
            WHERE ah.appointment_id = ?
            ORDER BY ah.created_at DESC
        ");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
        }
        if (empty($history)) {
            $appointmentData = $this->getById($appointment_id);
            if ($appointmentData) {
                $history[] = [
                    'history_id' => 0,
                    'appointment_id' => $appointment_id,
                    'user_id' => $appointmentData['patient_id'],
                    'status' => 'scheduled',
                    'notes' => 'Appointment created',
                    'created_at' => $appointmentData['created_at'],
                    'first_name' => $appointmentData['patient_first_name'],
                    'last_name' => $appointmentData['patient_last_name'],
                    'user_name' => $appointmentData['patient_first_name'] . ' ' . $appointmentData['patient_last_name']
                ];
            }
        }
        return $history;
    }

    /**
     * Get logs for an appointment in the format expected by the view.
     * @return array
     */
    public function getAppointmentLogs($appointment_id) {
        $logs = [];
        $history = $this->getAppointmentHistory($appointment_id);
        foreach ($history as $record) {
            $details = [];
            $appointment = $this->getById($appointment_id);
            if ($appointment) {
                $details = [
                    'appointment_date' => $appointment['appointment_date'],
                    'start_time' => $appointment['start_time'],
                    'end_time' => $appointment['end_time'],
                    'cancellation_reason' => $appointment['reason'] ?? 'No reason provided',
                    'previous_status' => '',
                    'new_status' => $record['status'] ?? '',
                    'reason' => $appointment['reason'] ?? ''
                ];
            }
            $action = 'status_changed';
            if (strpos(strtolower($record['notes'] ?? ''), 'created') !== false) {
                $action = 'created';
            } elseif (strpos(strtolower($record['notes'] ?? ''), 'cancel') !== false || 
                     $record['status'] === 'canceled') {
                $action = 'canceled';
            }
            $logs[] = [
                'details' => json_encode($details),
                'action' => $action,
                'created_at' => $record['created_at'],
                'user_first_name' => $record['first_name'] ?? '',
                'user_last_name' => $record['last_name'] ?? '',
                'user_role' => $record['role'] ?? 'patient'
            ];
        }
        return $logs;
    }

    /**
     * Get upcoming appointments for a provider.
     * @return array
     */
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
}
?>
