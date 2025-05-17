<?php

require_once __DIR__ . '/../helpers/system_notifications.php';
class Provider {
    private $providerModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->providerModel = $this;
    }

    // Get provider's profile details securely
    // This replaces getProviderData with the method name your controller expects

    public function getById($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, pp.*
                FROM users u
                JOIN provider_profiles pp ON u.user_id = pp.provider_id
                WHERE u.user_id = ? AND u.role = 'provider'
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc() ?: [];
        } catch (Exception $e) {
            error_log("Error fetching provider data: " . $e->getMessage());
            return [];
        }
    }

    // Alias to maintain compatibility
    public function getProviderData($provider_id) {
        return $this->getById($provider_id);
    }
    // Get provider with complete profile
    public function getProviderWithProfile($provider_id) {
        return $this->getById($provider_id);
    }
   /**
     * Check if a provider offers a specific service
     *
     * @param int $provider_id The provider ID
     * @param int $service_id The service ID
     * @return bool True if the provider offers the service, false otherwise
     */
    public function checkProviderService($provider_id, $service_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM provider_services
            WHERE provider_id = ? AND service_id = ?
        ");
        $stmt->bind_param("ii", $provider_id, $service_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
            
        return $row['count'] > 0;
    }

    /**
     * Add a service to provider's offerings
     *
     * @param int $providerId Provider ID
     * @param int $serviceId Service ID
     * @param int|null $customDuration Custom duration in minutes (optional)
     * @param string|null $customNotes Custom notes for this service (optional)
     * @return bool Success or failure
     */
    public function addServiceToProvider($providerId, $serviceId, $customDuration = null, $customNotes = null) {
        try {
            // Begin transaction
            $this->db->begin_transaction();
            
            // Insert into provider_services table
            $stmt = $this->db->prepare(
                "INSERT INTO provider_services 
                (provider_id, service_id, custom_duration, custom_notes) 
                VALUES (?, ?, ?, ?)"
            );
            
            $stmt->bind_param("iiis", $providerId, $serviceId, $customDuration, $customNotes);
            $result = $stmt->execute();
            
            if (!$result) {
                $this->db->rollback();
                error_log("Failed to add service to provider: " . $stmt->error);
                return false;
            }
            
            // Commit transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Exception in addServiceToProvider: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Update provider profile
     * 
     * @param int $provider_id Provider ID
     * @param array $data Profile data to update
     * @return bool True on success, false on failure
     */

public function updateProfile($provider_id, $data) {
    try {
        // --- SPECIALIZATION VALIDATION ---
        if (empty($data['specialization'])) {
            error_log("Provider specialization is required.");
            return false;
        }
        // First check if a record exists for this provider
        $checkSql = "SELECT provider_id FROM provider_profiles WHERE provider_id = ?";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->bind_param('i', $provider_id);
        $checkStmt->execute();
        $checkStmt->store_result();

        // If no record exists, insert one instead of updating
        if ($checkStmt->num_rows == 0) {
            $insertSql = "INSERT INTO provider_profiles (provider_id, specialization, title, bio, 
                        accepting_new_patients, max_patients_per_day, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $insertStmt = $this->db->prepare($insertSql);
            $insertStmt->bind_param(
                'issiii',
                $provider_id,
                $data['specialization'],
                $data['title'],
                $data['bio'],
                $data['accepting_new_patients'],
                $data['max_patients_per_day']
            );

            return $insertStmt->execute();
        }

        // Record exists, proceed with update
        $sql = "UPDATE provider_profiles SET
                specialization = ?,
                title = ?,
                bio = ?,
                accepting_new_patients = ?,
                max_patients_per_day = ?,
                updated_at = NOW()
                WHERE provider_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'sssiii',
            $data['specialization'],
            $data['title'],
            $data['bio'],
            $data['accepting_new_patients'],
            $data['max_patients_per_day'],
            $provider_id
        );

        // Execute and ALWAYS return true if no DB error
        if ($stmt->execute()) {
            return true; // Success if query executed without errors, regardless of affected rows
        } else {
            error_log("Database error in updateProfile: " . $stmt->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception in updateProfile: " . $e->getMessage());
        return false;
    }
}

    // Legacy method for backward compatibility
    public function updateProfile_old($provider_id, $first_name, $last_name, $specialization, $phone, $bio) {
        $profileData = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'specialization' => $specialization,
            'phone' => $phone,
            'bio' => $bio
        ];
        return $this->updateProfile($provider_id, $profileData);
    }
    // Update profile image
    public function updateProfileImage($provider_id, $image_path) {
        try {
            $stmt = $this->db->prepare("
                UPDATE provider_profiles 
                SET profile_image = ?
                WHERE provider_id = ?
            ");
            $stmt->bind_param("si", $image_path, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating profile image: " . $e->getMessage());
            return false;
        }
    }

    // Change provider password securely
    public function changePassword($provider_id, $current_password, $new_password) {
        try {
            // Verify current password
            $stmt = $this->db->prepare("
                SELECT password_hash 
                FROM users 
                WHERE user_id = ? AND role = 'provider'
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result || !password_verify($current_password, $result['password_hash'])) {
                return false;
            }

            // Update to new password (hashed)
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password_hash = ? 
                WHERE user_id = ? AND role = 'provider'
            ");
            $stmt->bind_param("si", $new_password_hash, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }

   /**
     * Get provider availability with optional service filtering
     *  
     * @param int $providerId Provider ID
     * @param int|null $serviceId Optional service ID to filter availability
     * @param bool $includeRecurring Whether to include recurring availability
     * @return array Array of availability slots
     */
    public function getAvailability($providerId, $serviceId = null, $includeRecurring = true) {
        try {
            $params = [$providerId];
            $types = "i";
            
            $query = "SELECT
                availability_id as id,
                provider_id,
                availability_date,
                start_time,
                end_time,
                is_available,
                is_recurring,
                IFNULL(weekdays, '') as weekdays
            FROM
                provider_availability
            WHERE
                provider_id = ?
                AND availability_date >= CURDATE()
                AND is_available = 1";
                
            // Add service filter if provided
            if ($serviceId) {
                $query .= " AND (service_id = ? OR service_id IS NULL)";
                $params[] = $serviceId;
                $types .= "i";
            }
            
            // Exclude recurring if needed
            if (!$includeRecurring) {
                $query .= " AND is_recurring = 0";
            }
            
            $query .= " ORDER BY availability_date, start_time";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $availability = [];
            while ($row = $result->fetch_assoc()) {
                $availability[] = $row;
            }
            
            return $availability;
        } catch (Exception $e) {
            error_log("Error in getAvailability: " . $e->getMessage());
            return [];
        }
    }

    
   /**
     * Add provider availability
     *
     * @param array $availabilityData Availability data including provider_id, dates, times, and optional service_id
     * @return bool Success or failure indicator
     */
    public function addAvailability($availabilityData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO provider_availability
                (provider_id, availability_date, start_time, end_time, service_id, max_appointments, is_available)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Handle service_id more explicitly to ensure NULL is properly stored
            // This avoids issues with empty strings or 0 values
            $service_id = isset($availabilityData['service_id']) && $availabilityData['service_id'] ? 
                intval($availabilityData['service_id']) : null;
            
            // Default max_appointments to 1 if not provided
            $max_appointments = $availabilityData['max_appointments'] ?? 1;
            
            // Default is_available to 1 if not provided
            $is_available = $availabilityData['is_available'] ?? 1;
            
            $stmt->bind_param(
                "isssiii",
                $availabilityData['provider_id'],
                $availabilityData['availability_date'],
                $availabilityData['start_time'],
                $availabilityData['end_time'],
                $service_id,
                $max_appointments,
                $is_available
            );
            
            $success = $stmt->execute();
            if (!$success) {
                error_log("addAvailability SQL Error: " . $stmt->error);
            }
            
            return $success;
        } catch (Exception $e) {
            error_log("Error adding availability: " . $e->getMessage());
            return false;
        }
    }


    
    // Provider Services Management
    public function addService($serviceData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO provider_services
                (provider_id, service_id, custom_duration, notes)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "iiis",
                $serviceData['provider_id'],
                $serviceData['service_id'],
                $serviceData['custom_duration'],
                $serviceData['notes']
            );
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding service: " . $e->getMessage());
            return false;
        }
    }

    // Legacy method for backward compatibility
    public function addService_old($provider_id, $service_id, $custom_duration = null, $custom_notes = null) {
        $serviceData = [
            'provider_id' => $provider_id,
            'service_id' => $service_id,
            'custom_duration' => $custom_duration,
            'notes' => $custom_notes
        ];
        return $this->addService($serviceData);
    }

    // Legacy method for backward compatibility
    public function getSchedulesByProvider($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT availability_id AS id, availability_date AS date, 
                       start_time, end_time, max_appointments, 
                       (CASE WHEN max_appointments = 0 THEN 1 ELSE 0 END) AS is_booked
                FROM provider_availability
                WHERE provider_id = ?
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching availability: " . $e->getMessage());
            return [];
        }
    }
    
    public function addAvailability_old($provider_id, $date, $start_time, $end_time) {
        $availabilityData = [
            'provider_id' => $provider_id,
            'availability_date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'max_appointments' => 1
        ];
        return $this->addAvailability($availabilityData);
    }

    // Delete availability
    public function deleteAvailability($availability_id, $provider_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM provider_availability
                WHERE availability_id = ? AND provider_id = ?
            ");
            $stmt->bind_param("ii", $availability_id, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting availability: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Delete a recurring schedule
     * 
     * @param int $schedule_id The ID of the recurring schedule
     * @param int $provider_id The provider ID for security validation
     * @return bool Whether the deletion was successful
     */
    public function deleteRecurringSchedule($schedule_id, $provider_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM recurring_schedules
                WHERE schedule_id = ? AND provider_id = ?
            ");
            $stmt->bind_param("ii", $schedule_id, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting recurring schedule: " . $e->getMessage());
            return false;
        }
    }
    // In the Provider model or a Settings model
    public function getDefaultSlotDuration() {
        return 30; // Default 30-minute increments
    }
    public function generateAvailabilityFromSchedule() {
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request';
            redirect('provider/schedule');
            return;
        }
        
        // Get selected services from the form
        $selectedServices = $_POST['services'] ?? [];
        if (empty($selectedServices)) {
            $_SESSION['error'] = 'Please select at least one service';
            redirect('provider/schedule');
            return;
        }
        
        // Get distribution method
        $distributionMethod = $_POST['distribution'] ?? 'alternate';
        
        // Get period (in weeks)
        $period = intval($_POST['period'] ?? 1);
        $endDate = date('Y-m-d', strtotime("+{$period} weeks"));
        
        $provider_id = $_SESSION['user_id'];
        $recurringSchedules = $this->providerModel->getRecurringSchedules($provider_id);
        
        // Track how many slots were created
        $slotsCreated = 0;
        $serviceIndex = 0; // Used for alternating services
        
        // For each recurring schedule
        foreach ($recurringSchedules as $schedule) {
            // Get day of week (0 = Sunday, 6 = Saturday)
            $dayOfWeek = $schedule['day_of_week'];
            $dayName = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$dayOfWeek];
            
            // Calculate all dates in the period that match this day of week
            $currentDate = date('Y-m-d', strtotime("next $dayName"));
            while (strtotime($currentDate) <= strtotime($endDate)) {
                // Generate slots based on slot duration
                $slotDuration = 30; // Default duration in minutes
                $startTime = strtotime($schedule['start_time']);
                $endTime = strtotime($schedule['end_time']);
                
                // Create availability slots
                for ($time = $startTime; $time < $endTime; $time += ($slotDuration * 60)) {
                    $slotStart = date('H:i:s', $time);
                    $slotEnd = date('H:i:s', $time + ($slotDuration * 60));
                    
                    // Determine which service ID to use based on distribution method
                    $serviceId = null;
                    switch ($distributionMethod) {
                        case 'alternate':
                            // Alternate between selected services
                            $serviceId = $selectedServices[$serviceIndex % count($selectedServices)];
                            $serviceIndex++;
                            break;
                            
                        case 'blocks':
                            // Create blocks of the same service
                            // Each day uses the same service, rotate by day
                            $dayIndex = array_search($currentDate, array_unique([$currentDate]));
                            $serviceId = $selectedServices[$dayIndex % count($selectedServices)];
                            break;
                            
                        case 'priority':
                            // Just use the first service (highest priority)
                            $serviceId = $selectedServices[0];
                            break;
                    }
                    
                    // Add availability slot WITH SERVICE ID
                    $result = $this->providerModel->addAvailability([
                        'provider_id' => $provider_id,
                        'availability_date' => $currentDate,
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'is_available' => 1,
                        'service_id' => $serviceId // This is the critical addition!
                    ]);
                    
                    if ($result) {
                        $slotsCreated++;
                    }
                }
                
                // Move to the next occurrence of this day
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 week'));
            }
        }
        
        if ($slotsCreated > 0) {
            $_SESSION['success'] = "$slotsCreated availability slots generated from your schedule";
        } else {
            $_SESSION['error'] = 'No slots were generated. Please check your recurring schedule.';
        }
        
        redirect('provider/schedule');
    }

    /**
     * Delete all availability slots within a date range
     * 
     * @param int $provider_id The provider ID
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return int|bool Number of deleted slots or false on failure
     */
    public function deleteAvailabilityRange($provider_id, $start_date, $end_date) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM provider_availability 
                WHERE provider_id = ? 
                AND availability_date BETWEEN ? AND ?
            ");
            
            $stmt->bind_param("iss", $provider_id, $start_date, $end_date);
            $stmt->execute();
            
            return $stmt->affected_rows;
        } catch (Exception $e) {
            error_log("Error deleting availability range: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all availability for a specific day
     * 
     * @param int $provider_id The provider ID
     * @param string $date The date (YYYY-MM-DD)
     * @return int|bool Number of deleted slots or false on failure
     */
    public function clearDayAvailability($provider_id, $date) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM provider_availability 
                WHERE provider_id = ? 
                AND availability_date = ?
            ");
            
            $stmt->bind_param("is", $provider_id, $date);
            $stmt->execute();
            
            return $stmt->affected_rows;
        } catch (Exception $e) {
            error_log("Error clearing day availability: " . $e->getMessage());
            return false;
        }
    }
    public function addRecurringSchedule($provider_id, $day_of_week, $start_time, $end_time, $is_active) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO recurring_schedules (provider_id, day_of_week, start_time, end_time, is_active) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssi", $provider_id, $day_of_week, $start_time, $end_time, $is_active);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding recurring schedule: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Delete recurring schedules for a specific day of the week
     * 
     * @param int $provider_id Provider ID
     * @param int $day_of_week Day of week (0=Sunday, 1=Monday, etc.)
     * @return int Number of schedules deleted
     */
    public function deleteRecurringSchedulesByDay($provider_id, $day_of_week, $specific_date = null) {
        try {
            if ($specific_date) {
                // For a specific date: Create a deletion marker
                $stmt = $this->db->prepare("
                    INSERT INTO recurring_schedules 
                    (provider_id, day_of_week, specific_date, start_time, end_time, is_active)
                    VALUES (?, ?, ?, '00:00:00', '00:00:00', 0)
                ");
                $stmt->bind_param("iis", $provider_id, $day_of_week, $specific_date);
                $stmt->execute();
                return $stmt->affected_rows > 0 ? 1 : 0;
            } else {
                // Delete the general pattern (existing functionality)
                $stmt = $this->db->prepare("
                    DELETE FROM recurring_schedules 
                    WHERE provider_id = ? AND day_of_week = ? AND specific_date IS NULL
                ");
                $stmt->bind_param("ii", $provider_id, $day_of_week);
                $stmt->execute();
                return $stmt->affected_rows;
            }
        } catch (Exception $e) {
            error_log("Error managing recurring schedules: " . $e->getMessage());
            return 0;
        }
    }


    /**
     * Delete all recurring schedules for a provider
     * 
     * @param int $provider_id Provider ID
     * @return int Number of schedules deleted
     */
    public function deleteAllRecurringSchedules($provider_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM recurring_schedules 
                WHERE provider_id = ?
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            
            return $stmt->affected_rows;
        } catch (Exception $e) {
            error_log("Error deleting all recurring schedules: " . $e->getMessage());
            return 0;
        }
    }
    /**
     * Delete recurring schedules for days of week within a date range
     * 
     * @param int $provider_id Provider ID
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return int Number of schedules deleted
     */
    public function deleteRecurringSchedulesInRange($provider_id, $start_date, $end_date) {
        try {
            // First identify which days of the week fall within this date range
            $days_in_range = [];
            $current = strtotime($start_date);
            $end = strtotime($end_date);
            
            while ($current <= $end) {
                $day_of_week = date('w', $current); // 0 (Sunday) through 6 (Saturday)
                $days_in_range[] = $day_of_week;
                $current = strtotime('+1 day', $current);
            }
            
            // Remove duplicates
            $days_in_range = array_unique($days_in_range);
            
            if (empty($days_in_range)) {
                return 0;
            }
            
            // Create placeholders for the IN clause
            $placeholders = implode(',', array_fill(0, count($days_in_range), '?'));
            
            // Prepare the statement with the dynamic placeholders
            $sql = "DELETE FROM recurring_schedules 
                    WHERE provider_id = ? 
                    AND day_of_week IN ($placeholders)";
            
            $stmt = $this->db->prepare($sql);
            
            // Build the parameter list
            $types = 'i' . str_repeat('i', count($days_in_range));
            $params = array($provider_id);
            foreach ($days_in_range as $day) {
                $params[] = $day;
            }
            
            // Bind parameters using call_user_func_array
            call_user_func_array([$stmt, 'bind_param'], $this->refValues(array_merge([$types], $params)));
            
            $stmt->execute();
            return $stmt->affected_rows;
            
        } catch (Exception $e) {
            error_log("Error deleting recurring schedules in range: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper function to pass parameters by reference for bind_param
     * 
     * @param array $params Array of parameters
     * @return array Array of references to parameters
     */
    private function refValues($params) {
        $refs = [];
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        return $refs;
    }

    public function getRecurringSchedulesByProvider($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT provider_id, day_of_week, start_time, end_time, is_active, schedule_id
                FROM recurring_schedules
                WHERE provider_id = ? 
                AND is_active = 1
                AND specific_date IS NULL
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching recurring schedules: " . $e->getMessage());
            return [];
        }
    }


    // Check for overlapping availability
    public function hasOverlappingAvailability($provider_id, $date, $start_time, $end_time) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM provider_availability
                WHERE provider_id = ? AND availability_date = ? AND
                ((start_time <= ? AND end_time > ?) OR
                 (start_time < ? AND end_time >= ?) OR
                 (start_time >= ? AND end_time <= ?))
            ");
            $stmt->bind_param("isssssss",
                $provider_id, $date,
                $end_time, $start_time,
                $end_time, $start_time,
                $start_time, $end_time
            );
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error checking overlapping availability: " . $e->getMessage());
            return true; // Assume overlap on error to be safe
        }
    }
    public function getAvailabilityForDate($provider_id, $date, $service_id = null) {
        $slots = [];
        $day_of_week = date('w', strtotime($date)); // 0 (Sunday) to 6 (Saturday)
        
        try {
            // If no specific service requested, get all availability
            if (!$service_id) {
                // 1. Get all one-time availability for this specific date
                $sql = "
                    SELECT
                        availability_id,
                        provider_id,
                        availability_date,
                        start_time,
                        end_time,
                        service_id,
                        0 as is_recurring
                    FROM
                        provider_availability
                    WHERE
                        provider_id = ?
                        AND availability_date = ?
                        AND is_available = 1
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("is", $provider_id, $date);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $slots[] = $row;
                }
                
                // 2. Get recurring availability for this day of week
                $sql = "
                    SELECT
                        availability_id,
                        provider_id,
                        ? as availability_date,
                        start_time,
                        end_time,
                        service_id,
                        1 as is_recurring
                    FROM
                        provider_availability
                    WHERE
                        provider_id = ?
                        AND is_available = 1
                        AND is_recurring = 1
                        AND FIND_IN_SET(?, weekdays) > 0
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("sis", $date, $provider_id, $day_of_week);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $slots[] = $row;
                }
            } 
            else {
                // When a specific service is requested
                
                // 1. First try to get service-specific one-time availability
                $sql = "
                    SELECT
                        availability_id,
                        provider_id,
                        availability_date,
                        start_time,
                        end_time,
                        service_id,
                        0 as is_recurring
                    FROM
                        provider_availability
                    WHERE
                        provider_id = ?
                        AND availability_date = ?
                        AND is_available = 1
                        AND service_id = ?
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("isi", $provider_id, $date, $service_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $serviceSpecificSlots = [];
                
                while ($row = $result->fetch_assoc()) {
                    $serviceSpecificSlots[] = $row;
                }
                
                // 2. Get service-specific recurring availability
                $sql = "
                    SELECT
                        availability_id,
                        provider_id,
                        ? as availability_date,
                        start_time,
                        end_time,
                        service_id,
                        1 as is_recurring
                    FROM
                        provider_availability
                    WHERE
                        provider_id = ?
                        AND is_available = 1
                        AND is_recurring = 1
                        AND FIND_IN_SET(?, weekdays) > 0
                        AND service_id = ?
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("sisi", $date, $provider_id, $day_of_week, $service_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $serviceSpecificSlots[] = $row;
                }
                
                // If we found service-specific slots, use only those
                if (!empty($serviceSpecificSlots)) {
                    error_log("Found " . count($serviceSpecificSlots) . " service-specific slots for service_id: $service_id");
                    return $serviceSpecificSlots;
                }
                
                // If no service-specific slots, fall back to general availability (NULL service_id)
                error_log("No service-specific slots found for service_id: $service_id, using general availability");
                
                // 3. Get general one-time availability (NULL service_id)
                $sql = "
                    SELECT
                        availability_id,
                        provider_id,
                        availability_date,
                        start_time,
                        end_time,
                        service_id,
                        0 as is_recurring
                    FROM
                        provider_availability
                    WHERE
                        provider_id = ?
                        AND availability_date = ?
                        AND is_available = 1
                        AND service_id IS NULL
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("is", $provider_id, $date);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $slots[] = $row;
                }
                
                // 4. Get general recurring availability (NULL service_id)
                $sql = "
                    SELECT
                        availability_id,
                        provider_id,
                        ? as availability_date,
                        start_time,
                        end_time,
                        service_id,
                        1 as is_recurring
                    FROM
                        provider_availability
                    WHERE
                        provider_id = ?
                        AND is_available = 1
                        AND is_recurring = 1
                        AND FIND_IN_SET(?, weekdays) > 0
                        AND service_id IS NULL
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("sis", $date, $provider_id, $day_of_week);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $slots[] = $row;
                }
            }
            
            error_log("getAvailabilityForDate returning " . count($slots) . " slots for provider $provider_id, date $date" . 
                    ($service_id ? ", service $service_id" : ""));
            
            return $slots;
            
        } catch (Exception $e) {
            error_log("Error in getAvailabilityForDate: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }


    public function getAvailableSlots($provider_id, $date, $service_duration, $service_id = null, $exclude_appointment_id = null) {
        try {
            $slots = [];
            
            // Convert the date parameter to the right format
            $requestedDate = date('Y-m-d', strtotime($date));
            
            error_log("Getting slots for provider_id=$provider_id, date=$requestedDate, service_duration=$service_duration, service_id=" . ($service_id ?? 'NULL'));
            
            // First check if this provider offers the requested service (if service_id is provided)
            if ($service_id) {
                $checkServiceSql = "
                    SELECT COUNT(*) as count
                    FROM provider_services
                    WHERE provider_id = ? AND service_id = ? AND is_active = 1
                ";
                $stmt = $this->db->prepare($checkServiceSql);
                $stmt->bind_param("ii", $provider_id, $service_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['count'] == 0) {
                    error_log("Provider $provider_id does not offer service $service_id");
                    return []; // Return empty array if provider doesn't offer this service
                }
                
                error_log("Provider $provider_id offers service $service_id - proceeding with availability check");
            }
            
            // 1. Get one-time availability (filtered by the requested date)
            $sql = "
                SELECT
                    availability_id,
                    availability_date,
                    start_time,
                    end_time,
                    service_id
                FROM
                    provider_availability
                WHERE
                    provider_id = ?
                    AND availability_date = ?
                    AND is_available = 1
            ";
            
            // Add service_id filtering if specified - only show slots specifically for this service or generic slots
            $params = [$provider_id, $requestedDate];
            $types = "is";
            
            if ($service_id) {
                $sql .= " AND (service_id = ? OR service_id IS NULL)";
                $params[] = $service_id;
                $types .= "i";
            }
            
            $sql .= " ORDER BY start_time";
            
            error_log("One-time availability SQL: " . $sql);
            error_log("Params: " . implode(", ", $params));
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $oneTimeSlots = 0; // For logging
            
            while ($row = $result->fetch_assoc()) {
                $start = strtotime($row['start_time']);
                $end = strtotime($row['end_time']);
                
                error_log("Processing availability: " . $requestedDate . " " .
                    $row['start_time'] . "-" . $row['end_time'] .
                    " (service_id=" . ($row['service_id'] ?? 'NULL') . ")");
                
                while ($start + ($service_duration * 60) <= $end) {
                    $slot_end_time = date("H:i:s", $start + ($service_duration * 60));
                    $slots[] = [
                        'id' => 'slot_' . $row['availability_id'] . '_' . date("His", $start),
                        'start' => $requestedDate . 'T' . date("H:i:s", $start),
                        'end' => $requestedDate . 'T' . $slot_end_time,
                        'end_time' => $slot_end_time, // Add this specific field for patient controller
                        'title' => 'Available',
                        'color' => '#28a745',
                        'extendedProps' => [
                            'availability_id' => $row['availability_id'],
                            'service_id' => $row['service_id'] ?? null
                        ]
                    ];
                    $start += ($service_duration * 60);
                    $oneTimeSlots++;
                }
            }
            
            error_log("Found $oneTimeSlots one-time availability slots");
            
            // 2. Get recurring availability patterns (only for the requested date)
            $dayOfWeek = date('w', strtotime($requestedDate)); // 0 (Sun) to 6 (Sat)
            
            // Improved query for recurring availability
            $sql = "
                SELECT
                    availability_id,
                    weekdays,
                    start_time,
                    end_time,
                    service_id
                FROM
                    provider_availability
                WHERE
                    provider_id = ?
                    AND is_available = 1
                    AND is_recurring = 1
            ";
            
            // Add service_id filtering for recurring slots too - only show slots specifically for this service or generic slots
            $params = [$provider_id];
            $types = "i";
            
            if ($service_id) {
                $sql .= " AND (service_id = ? OR service_id IS NULL)";
                $params[] = $service_id;
                $types .= "i";
            }
            
            error_log("Recurring availability SQL: " . $sql);
            error_log("Params: " . implode(", ", $params));
            error_log("Looking for day of week: " . $dayOfWeek);
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $recurringSlots = 0; // For logging
            $recurringCount = 0;
            
            while ($row = $result->fetch_assoc()) {
                $recurringCount++;
                // Fix for null weekdays value - use empty string if null
                $weekdaysStr = $row['weekdays'] ?? '';
                
                // Debug the weekdays field
                error_log("Recurring pattern " . $recurringCount . ": weekdays=[" . $weekdaysStr .
                    "], type=" . gettype($weekdaysStr));
                
                // Convert weekdays to array and ensure they're integers
                $weekdays = [];
                if (!empty($weekdaysStr)) {
                    // Handle both comma-separated and CSV formats
                    $weekdaysArray = explode(',', $weekdaysStr);
                    foreach ($weekdaysArray as $day) {
                        $trimmedDay = trim($day);
                        if (is_numeric($trimmedDay)) {
                            $weekdays[] = (int)$trimmedDay;
                        }
                    }
                }
                
                error_log("Parsed weekdays: [" . implode(',', $weekdays) . "]");
                error_log("Checking if day $dayOfWeek is in weekdays array");
                error_log("Service ID: " . ($row['service_id'] ?? 'NULL'));
                
                // Only process if this date matches a recurring weekday pattern
                if (in_array((int)$dayOfWeek, $weekdays)) {
                    $start = strtotime($row['start_time']);
                    $end = strtotime($row['end_time']);
                    
                    error_log("MATCH! Recurring pattern for day $dayOfWeek: " . $row['start_time'] . "-" . $row['end_time']);
                    
                    while ($start + ($service_duration * 60) <= $end) {
                        $slot_end_time = date("H:i:s", $start + ($service_duration * 60));
                        $slots[] = [
                            'id' => 'slot_' . $row['availability_id'] . '_' . date("His", $start),
                            'start' => $requestedDate . 'T' . date("H:i:s", $start),
                            'end' => $requestedDate . 'T' . $slot_end_time,
                            'end_time' => $slot_end_time, // Add this specific field for patient controller
                            'title' => 'Available (Recurring)',
                            'color' => '#28a745',
                            'extendedProps' => [
                                'availability_id' => $row['availability_id'],
                                'service_id' => $row['service_id'] ?? null,
                                'is_recurring' => true
                            ]
                        ];
                        $start += ($service_duration * 60);
                        $recurringSlots++;
                    }
                } else {
                    error_log("Day $dayOfWeek not found in weekdays [" . implode(',', $weekdays) . "]");
                }
            }
            
            error_log("Found $recurringSlots recurring availability slots from $recurringCount patterns");
            
            // 3. Remove slots that conflict with existing appointments
            if (!empty($slots)) {
                // Modified to get booked appointments for the specific date only
                $bookedAppointments = $this->getBookedAppointmentsForDate($provider_id, $requestedDate);
                
                error_log("Found " . count($bookedAppointments) . " booked appointments to check for conflicts");
                
                if (!empty($bookedAppointments)) {
                    $originalCount = count($slots);
                    foreach ($bookedAppointments as $booked) {
                        // Skip canceled appointments and the excluded appointment
                        if ($booked['status'] === 'canceled' ||
                            ($exclude_appointment_id && $booked['appointment_id'] == $exclude_appointment_id)) {
                            error_log("Skipping appointment ID " . $booked['appointment_id'] . " (status=" . $booked['status'] . ")");
                            continue;
                        }
                        
                        $bookedDate = $booked['appointment_date'];
                        $bookedStart = strtotime($bookedDate . ' ' . $booked['start_time']);
                        $bookedEnd = strtotime($bookedDate . ' ' . $booked['end_time']);
                        
                        error_log("Checking conflicts with appointment: " . $bookedDate . " " .
                            $booked['start_time'] . "-" . $booked['end_time'] .
                            " (status=" . $booked['status'] . ")");
                        
                        // Remove slots that overlap with booked appointments
                        $slots = array_filter($slots, function($slot) use ($bookedStart, $bookedEnd) {
                            $slotStart = strtotime(substr($slot['start'], 0, 10) . ' ' . substr($slot['start'], 11));
                            $slotEnd = strtotime(substr($slot['end'], 0, 10) . ' ' . substr($slot['end'], 11));
                            
                            // No overlap if slot ends before booked starts or starts after booked ends
                            $noOverlap = ($slotEnd <= $bookedStart || $slotStart >= $bookedEnd);
                            
                            if (!$noOverlap) {
                                error_log("Conflict found: Slot " . date('H:i', $slotStart) . "-" . date('H:i', $slotEnd) .
                                    " overlaps with appointment " . date('H:i', $bookedStart) . "-" . date('H:i', $bookedEnd));
                            }
                            
                            return $noOverlap;
                        });
                    }
                    
                    // Re-index array after filtering
                    $slots = array_values($slots);
                    error_log("After conflict removal: " . count($slots) . " slots remain (removed " .
                        ($originalCount - count($slots)) . " conflicting slots)");
                }
            }
            
            if (empty($slots)) {
                error_log("No available slots found after all processing");
                
                // DEBUG: Just to test if we can see any slots at all - REMOVE FOR PRODUCTION
                if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
                    error_log("Adding test slot for debugging");
                    $slots[] = [
                        'id' => 'debug_slot',
                        'start' => $requestedDate . 'T09:00:00',
                        'end' => $requestedDate . 'T09:30:00',
                        'end_time' => '09:30:00', // Add this for the patient controller
                        'title' => 'TEST SLOT',
                        'color' => '#ff0000',
                    ];
                }
            } else {
                error_log("Returning " . count($slots) . " total available slots");
            }
            
            return $slots;
        } catch (Exception $e) {
            error_log("Error fetching available appointments: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }


    // Helper method to get booked appointments for a specific date
    private function getBookedAppointmentsForDate($provider_id, $date) {
        try {
            $sql = "
                SELECT
                    appointment_id,
                    appointment_date,
                    start_time,
                    end_time,
                    status
                FROM
                    appointments
                WHERE
                    provider_id = ?
                    AND appointment_date = ?
            ";
            
            error_log("Booked appointments SQL: " . $sql);
            error_log("Params: $provider_id, $date");
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("is", $provider_id, $date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
            
            return $appointments;
        } catch (Exception $e) {
            error_log("Error fetching booked appointments: " . $e->getMessage());
            return [];
        }
    }
    // Check if provider has a specific service
    public function hasService($provider_id, $service_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM provider_services
                WHERE provider_id = ? AND service_id = ?
            ");
            $stmt->bind_param("ii", $provider_id, $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error checking if provider has service: " . $e->getMessage());
            return false;
        }
    }

    // Get provider services
    public function getProviderServices($provider_id) {
        return $this->getServices($provider_id);
    }

    // Get services offered by a provider
    public function getServices($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT ps.*, s.name, s.description, s.duration, s.is_active, s.price
                FROM provider_services ps
                JOIN services s ON ps.service_id = s.service_id
                WHERE ps.provider_id = ?
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching services: " . $e->getMessage());
            return [];
        }
    }


    // Update provider service
    public function updateService($provider_service_id, $provider_id, $custom_duration, $custom_notes) {
        try {
            $stmt = $this->db->prepare("
                UPDATE provider_services 
                SET custom_duration = ?, custom_notes = ?
                WHERE provider_service_id = ? AND provider_id = ?
            ");
            $stmt->bind_param("isii", $custom_duration, $custom_notes, $provider_service_id, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating service: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Get a specific availability slot by date and time
     * 
     * @param int $provider_id The provider ID
     * @param string $date The appointment date (YYYY-MM-DD)
     * @param string $time The appointment start time (HH:MM:SS)
     * @return array|null The slot data including end_time or null if not found
     */
    public function getSlotByDateTime($provider_id, $date, $time) {
        // Format the date consistently
        $formattedDate = date('Y-m-d', strtotime($date));
        
        error_log("Looking for slot: provider=$provider_id, date=$formattedDate, time=$time");
        
        // First check one-time availability
        $sql = "
            SELECT
                availability_id,
                availability_date,
                start_time,
                end_time,
                service_id
            FROM
                provider_availability
            WHERE
                provider_id = ?
                AND availability_date = ?
                AND start_time <= ?
                AND end_time > ?
                AND is_available = 1
                AND is_recurring = 0
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isss", $provider_id, $formattedDate, $time, $time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $slot = $result->fetch_assoc();
            error_log("Found one-time slot: " . json_encode($slot));
            return $slot;
        }
        
        // If not found, check recurring availability
        $dayOfWeek = date('w', strtotime($formattedDate)); // 0 (Sun) to 6 (Sat)
        
        $sql = "
            SELECT
                availability_id,
                weekdays,
                start_time,
                end_time,
                service_id
            FROM
                provider_availability
            WHERE
                provider_id = ?
                AND is_available = 1
                AND is_recurring = 1
                AND start_time <= ?
                AND end_time > ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iss", $provider_id, $time, $time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Parse weekdays
            $weekdaysStr = $row['weekdays'] ?? '';
            $weekdays = [];
            
            if (!empty($weekdaysStr)) {
                $weekdaysArray = explode(',', $weekdaysStr);
                foreach ($weekdaysArray as $day) {
                    $trimmedDay = trim($day);
                    if (is_numeric($trimmedDay)) {
                        $weekdays[] = (int)$trimmedDay;
                    }
                }
            }
            
            // Check if this recurring slot applies to the current day of week
            if (in_array((int)$dayOfWeek, $weekdays)) {
                error_log("Found recurring slot for day $dayOfWeek: " . json_encode($row));
                return $row;
            }
        }
        
        error_log("No slot found for provider=$provider_id, date=$formattedDate, time=$time");
        return null;
    }


    // Remove service from provider
    public function removeService($provider_service_id, $provider_id) {
        return $this->deleteService($provider_service_id, $provider_id);
    }

    // Delete provider service
    public function deleteService($provider_service_id, $provider_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM provider_services 
                WHERE provider_service_id = ? AND provider_id = ?
            ");
            $stmt->bind_param("ii", $provider_service_id, $provider_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting service: " . $e->getMessage());
            return false;
        }
    }


    // Get all providers (needed for listing providers)
    public function getAll() {
        try {
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.first_name, u.last_name, u.email, u.is_active, 
                       pp.specialization, pp.title, pp.bio, pp.accepting_new_patients
                FROM users u
                JOIN provider_profiles pp ON u.user_id = pp.provider_id
                WHERE u.role = 'provider' AND u.is_active = 1
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching all providers: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Get provider availability filtered by service
     */
    public function getServiceAvailability($provider_id, $service_id = null, $date = null) {
        // Base query
        $sql = "
            SELECT * FROM provider_availability
            WHERE provider_id = ? AND is_available = 1
        ";
        
        $params = [$provider_id];
        $types = "i";
        
        // Add service filter - include slots with NULL service_id (available for all)
        // or slots matching the requested service_id
        if ($service_id) {
            $sql .= " AND (service_id = ? OR service_id IS NULL)";
            $params[] = $service_id;
            $types .= "i";
            // Debug this specific condition
            error_log("Using service_id filter: $service_id or NULL");
        } else {
            error_log("No service_id filter applied");
        }
        
        // Add date filter - FIXED: use availability_date instead of date
        if ($date) {
            $sql .= " AND availability_date = ?";
            $params[] = $date;
            $types .= "s";
        } else {
            // Only future dates if no specific date provided
            $sql .= " AND availability_date >= CURDATE()";
        }
        
        $sql .= " ORDER BY availability_date ASC, start_time ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    public function getRecurringSchedules($providerId) {
        try {
            // First get all specific date exceptions
            $exceptionQuery = "SELECT specific_date, day_of_week 
                            FROM recurring_schedules
                            WHERE provider_id = ? 
                            AND is_active = 0
                            AND specific_date IS NOT NULL";
            $exceptionStmt = $this->db->prepare($exceptionQuery);
            $exceptionStmt->bind_param("i", $providerId);
            $exceptionStmt->execute();
            $exceptionResult = $exceptionStmt->get_result();
            
            $excludedDates = [];
            while ($row = $exceptionResult->fetch_assoc()) {
                $excludedDates[$row['specific_date']] = $row['day_of_week'];
            }
            
            // Then get the regular recurring schedules
            $query = "SELECT * FROM recurring_schedules
                    WHERE provider_id = ? AND is_active = 1
                    AND specific_date IS NULL
                    ORDER BY day_of_week, start_time";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $providerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $schedules = [];
            while ($row = $result->fetch_assoc()) {
                // Add excluded dates information to each schedule
                $row['excluded_dates'] = [];
                foreach ($excludedDates as $date => $day) {
                    if ($day == $row['day_of_week']) {
                        $row['excluded_dates'][] = $date;
                    }
                }
                $schedules[] = $row;
            }
            
            return $schedules;
        } catch (Exception $e) {
            error_log("Error in getRecurringSchedules: " . $e->getMessage());
            return [];
        }
    }

    
    public function getBookedAppointments($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*,
                       u.first_name AS patient_first_name,
                       u.last_name AS patient_last_name,
                       CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
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
     * Get top providers by appointment count
     * @param int $limit Number of providers to return
     * @return array Top providers
     */
    public function getTopProviders($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.provider_id, 
                    CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                    COUNT(a.appointment_id) as appointment_count
                FROM 
                    appointments a
                    JOIN users u ON a.provider_id = u.user_id
                WHERE 
                    a.status NOT IN ('canceled', 'no_show')
                GROUP BY 
                    a.provider_id, provider_name
                ORDER BY 
                    appointment_count DESC
                LIMIT ?
            ");
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting top providers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available slots count
     * @return int Count of available slots
     */
    public function getAvailableSlotsCount() {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM provider_availability
                WHERE is_available = 1
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return $row['count'];
            }
            return 0;
        } catch (Exception $e) {
            error_log("Error counting available slots: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create a new provider profile
     * @param array $profileData Provider profile data
     * @return bool Success status
     */
 public function createProfile($profileData) {
    try {
        // --- SPECIALIZATION VALIDATION ---
        if (empty($profileData['specialization'])) {
            error_log("Provider specialization is required.");
            return false;
        }
        $stmt = $this->db->prepare("
            INSERT INTO provider_profiles
            (provider_id, specialization, title, bio, accepting_new_patients)
            VALUES (?, ?, ?, ?, ?)
        ");

        $acceptingPatients = $profileData['accepting_new_patients'] ?? 1;

        $stmt->bind_param("isssi",
            $profileData['provider_id'],
            $profileData['specialization'],
            $profileData['title'],
            $profileData['bio'],
            $acceptingPatients
        );

        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error creating provider profile: " . $e->getMessage());
        return false;
    }
}

   /**
     * Create a provider profile
     *
     * @param int $providerId The user ID of the provider
     * @param array $profileData Profile data (specialization, title, bio, etc.)
     * @return bool True on success, false on failure
     */
    public function createProviderProfile($providerId, $profileData) {
        try {
            // Removed specialization validation to make it optional
            
            // Prepare default values
            $specialization = $profileData['specialization'] ?? '';
            $title = $profileData['title'] ?? '';
            $bio = $profileData['bio'] ?? '';
            $acceptingNewPatients = isset($profileData['accepting_new_patients']) ? 1 : 0;
            $maxPatientsPerDay = $profileData['max_patients_per_day'] ?? 20;
            
            // Create the SQL query - note we're not including user_id column
            $query = "INSERT INTO provider_profiles
                    (provider_id, specialization, title, bio, accepting_new_patients, max_patients_per_day)
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                error_log("Provider profile prepare error: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("isssii",
                $providerId,
                $specialization,
                $title,
                $bio,
                $acceptingNewPatients,
                $maxPatientsPerDay
            );
            
            $result = $stmt->execute();
            if (!$result) {
                error_log("Provider profile execute error: " . $stmt->error);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Provider profile creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a new provider with profile
     * @param array $userData User data
     * @param array $profileData Profile data
     * @return int|bool New provider ID or false on failure
     */
    public function createProvider($userData, $profileData) {
    // Log system event
    if ($success || $provider_id) {
        logSystemEvent('provider_added', 'A new healthcare provider was added to the system', 'New Provider Added');
    }

        try {
            $this->db->begin_transaction();
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users
                (email, password_hash, first_name, last_name, phone, role, is_active, password_change_required)
                VALUES (?, ?, ?, ?, ?, 'provider', 1, 1)
            ");
            $stmt->bind_param("sssss",
                $userData['email'],
                $userData['password_hash'],
                $userData['first_name'],
                $userData['last_name'],
                $userData['phone']
            );
            $stmt->execute();
            
            $userId = $this->db->insert_id;
            
            // Create provider profile
            $profileData['provider_id'] = $userId;
            $this->createProfile($profileData);
            
            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error creating provider: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search for providers based on various criteria
     * 
     * @param array $params Search parameters (specialty, location, date, gender, language, insurance)
     * @return array List of providers matching the criteria
     */
    public function searchProviders($params) {
        // Modified query to match actual database structure
        $query = "
            SELECT 
                u.user_id AS provider_id,
                CONCAT(u.first_name, ' ', u.last_name) AS name,
                pp.specialization AS specialty,
                u.phone, 
                pp.title,
                pp.bio,
                pp.profile_image,
                pp.accepting_new_patients,
                (
                    SELECT MIN(availability_date) 
                    FROM provider_availability 
                    WHERE provider_id = u.user_id 
                    AND is_available = 1 
                    AND availability_date >= CURDATE()
                ) AS next_available_date
            FROM 
                users u
            JOIN 
                provider_profiles pp ON u.user_id = pp.provider_id
            WHERE 
                u.role = 'provider'
        ";
        
        $conditions = [];
        $params_array = [];
        $types = "";
        
        // Add search conditions based on provided parameters
        if (!empty($params['specialty'])) {
            $conditions[] = "pp.specialization = ?";
            $params_array[] = $params['specialty'];
            $types .= "s";
        }
        
        // Your database doesn't have location columns, so we'll search across name and phone instead
        if (!empty($params['location'])) {
            $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.phone LIKE ?)";
            $params_array[] = "%{$params['location']}%";
            $params_array[] = "%{$params['location']}%";
            $params_array[] = "%{$params['location']}%";
            $types .= "sss";
        }
        
        // No gender column in your schema, so we'll skip this filter
        // if (!empty($params['gender'])) {
        //     $conditions[] = "u.gender = ?";
        //     $params_array[] = $params['gender'];
        //     $types .= "s";
        // }
        
        // No languages column in your schema, so we'll skip this filter
        // if (!empty($params['language'])) {
        //     $conditions[] = "pp.languages LIKE ?";
        //     $params_array[] = "%{$params['language']}%";
        //     $types .= "s";
        // }
        
        // No insurance_accepted column in your schema, so we'll skip this filter
        // if (!empty($params['insurance'])) {
        //     $conditions[] = "pp.insurance_accepted LIKE ?";
        //     $params_array[] = "%{$params['insurance']}%";
        //     $types .= "s";
        // }
        
        if (!empty($params['date'])) {
            $conditions[] = "EXISTS (
                SELECT 1 FROM provider_availability 
                WHERE provider_id = u.user_id 
                AND availability_date = ? 
                AND is_available = 1
            )";
            $params_array[] = $params['date'];
            $types .= "s";
        }
        
        // Add conditions to query if any exist
        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
        }
        
        // Removed rating from ORDER BY since it doesn't exist in your schema
         $query .= " ORDER BY next_available_date ASC, u.first_name ASC";
         
        $providers = [];
        
        $stmt = $this->db->prepare($query);
        
        if (!empty($params_array)) {
            $stmt->bind_param($types, ...$params_array);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Add a location field for display purposes
                $row['location'] = 'Not specified'; // Since location isn't in your DB
                $providers[] = $row;
            }
        }
        
        return $providers;
    }

    /**
     * Get suggested providers when no search results are found
     * 
     * @return array List of suggested providers
     */

public function getSuggestedProviders() {
    // Only suggest providers with a non-empty, non-null specialty
    $query = "
        SELECT
            u.user_id AS provider_id,
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            pp.specialization AS specialty,
            pp.bio,
            pp.profile_image
        FROM
            users u
        JOIN
            provider_profiles pp ON u.user_id = pp.provider_id
        WHERE
            u.role = 'provider'
            AND pp.accepting_new_patients = 1
            AND pp.specialization IS NOT NULL
            AND TRIM(pp.specialization) != ''
        ORDER BY
            RAND()
        LIMIT 3
    ";
    
    $suggested_providers = [];
    $result = $this->db->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $suggested_providers[] = $row;
        }
    }

    
    return $suggested_providers;
}
    /**
     * Remove a service from a provider
     *
     * @param int $providerId Provider ID
     * @param int $serviceId Service ID
     * @return bool Success or failure
     */
    public function removeServiceFromProvider($providerId, $serviceId) {
        // Prepare the SQL query to delete the provider-service relationship
        $query = "DELETE FROM provider_services 
                WHERE provider_id = ? AND service_id = ?";
        
        // Prepare and execute the statement
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $providerId, $serviceId);
        $result = $stmt->execute();
        
        // Return true if successful, false otherwise
        return $result && ($stmt->affected_rows > 0);
    }
    /**
     * Get all available specializations for the filter dropdown
     * 
     * @return array List of distinct specializations
     */
    public function getDistinctSpecializations() {
        $specialties = [];
        $query = "SELECT DISTINCT specialization FROM provider_profiles WHERE specialization IS NOT NULL ORDER BY specialization";
        $result = $this->db->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $specialties[] = $row;
            }
        }
        
        return $specialties;
    }

    /**
     * Get provider utilization metrics
     * 
     * @param int $providerId Provider ID
     * @param string $startDate Optional start date (defaults to 30 days ago)
     * @param string $endDate Optional end date (defaults to today)
     * @return array Provider utilization metrics
     */
    public function getProviderUtilization($providerId, $startDate = null, $endDate = null) {
        try {
            // Set default date range if not provided
            if (!$startDate) {
                $startDate = date('Y-m-d', strtotime('-30 days'));
            }
            if (!$endDate) {
                $endDate = date('Y-m-d');
            }
            
            // Get total available slots
            $availQuery = "
                SELECT 
                    SUM(
                        TIME_TO_SEC(TIMEDIFF(end_time, start_time)) / 1800
                    ) as total_slots
                FROM 
                    provider_availability
                WHERE 
                    provider_id = ? AND
                    availability_date BETWEEN ? AND ?
            ";
            
            $availStmt = $this->db->prepare($availQuery);
            $availStmt->bind_param("iss", $providerId, $startDate, $endDate);
            $availStmt->execute();
            $availResult = $availStmt->get_result();
            $availRow = $availResult->fetch_assoc();
            $totalSlots = $availRow['total_slots'] ?: 0;
            
            // Get booked appointments
            $apptQuery = "
                SELECT 
                    COUNT(*) as total_appointments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_shows,
                    SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as cancellations
                FROM 
                    appointments
                WHERE 
                    provider_id = ? AND
                    appointment_date BETWEEN ? AND ?
            ";
            
            $apptStmt = $this->db->prepare($apptQuery);
            $apptStmt->bind_param("iss", $providerId, $startDate, $endDate);
            $apptStmt->execute();
            $apptResult = $apptStmt->get_result();
            $apptRow = $apptResult->fetch_assoc();
            
            // Calculate utilization metrics
            $bookedAppointments = $apptRow['total_appointments'] ?: 0;
            $utilization = ($totalSlots > 0) ? ($bookedAppointments / $totalSlots) * 100 : 0;
            $noShowRate = ($bookedAppointments > 0) ? ($apptRow['no_shows'] / $bookedAppointments) * 100 : 0;
            $cancellationRate = ($bookedAppointments > 0) ? ($apptRow['cancellations'] / $bookedAppointments) * 100 : 0;
            $completionRate = ($bookedAppointments > 0) ? ($apptRow['completed'] / $bookedAppointments) * 100 : 0;
            
            return [
                'provider_id' => $providerId,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'total_slots' => $totalSlots,
                'booked_appointments' => $bookedAppointments,
                'utilization_rate' => round($utilization, 2),
                'no_show_rate' => round($noShowRate, 2),
                'cancellation_rate' => round($cancellationRate, 2),
                'completion_rate' => round($completionRate, 2)
            ];
        } catch (Exception $e) {
            error_log("Error calculating provider utilization: " . $e->getMessage());
            return [
                'provider_id' => $providerId,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all provider specializations with count
     * Useful for admin dashboards and filters
     * 
     * @return array Specializations with provider counts
     */
    public function getAllSpecializations() {
        try {
            $query = "
                SELECT 
                    specialization, 
                    COUNT(*) as provider_count
                FROM 
                    provider_profiles
                JOIN
                    users ON provider_profiles.provider_id = users.user_id
                WHERE
                    users.is_active = 1
                GROUP BY 
                    specialization
                ORDER BY 
                    provider_count DESC, specialization
            ";
            
            $result = $this->db->query($query);
            
            $specializations = [];
            while ($row = $result->fetch_assoc()) {
                $specializations[] = $row;
            }
            
            return $specializations;
        } catch (Exception $e) {
            error_log("Error getting specializations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get provider availability summary
     * 
     * @param int $providerId Provider ID
     * @param int $daysForward Number of days to look forward (default 30)
     * @return array Availability summary
     */
    public function getAvailabilitySummary($providerId, $daysForward = 30) {
        try {
            // Calculate end date
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime("+{$daysForward} days"));
            
            $query = "
                SELECT 
                    pa.availability_date,
                    SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(pa.end_time, pa.start_time)))) as total_hours,
                    COUNT(DISTINCT a.appointment_id) as booked_appointments
                FROM 
                    provider_availability pa
                LEFT JOIN 
                    appointments a ON 
                    pa.provider_id = a.provider_id AND 
                    pa.availability_date = a.appointment_date AND
                    a.status NOT IN ('canceled', 'no_show')
                WHERE 
                    pa.provider_id = ? AND
                    pa.availability_date BETWEEN ? AND ?
                GROUP BY 
                    pa.availability_date
                ORDER BY 
                    pa.availability_date
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iss", $providerId, $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $availability = [];
            while ($row = $result->fetch_assoc()) {
                $availability[] = $row;
            }
            
            // Calculate summary statistics
            $totalDays = 0;
            $totalHours = 0;
            $totalBookings = 0;
            
            foreach ($availability as $day) {
                $totalDays++;
                $hoursArray = explode(':', $day['total_hours']);
                $totalHours += $hoursArray[0] + ($hoursArray[1] / 60);
                $totalBookings += $day['booked_appointments'];
            }
            
            return [
                'provider_id' => $providerId,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'total_days_available' => $totalDays,
                'total_hours_available' => $totalHours,
                'average_hours_per_day' => $totalDays > 0 ? round($totalHours / $totalDays, 2) : 0,
                'total_bookings' => $totalBookings,
                'average_bookings_per_day' => $totalDays > 0 ? round($totalBookings / $totalDays, 2) : 0,
                'daily_availability' => $availability
            ];
        } catch (Exception $e) {
            error_log("Error getting availability summary: " . $e->getMessage());
            return [
                'provider_id' => $providerId,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get provider service statistics
     * 
     * @param int $providerId Provider ID
     * @return array Service statistics
     */
    public function getServiceStatistics($providerId) {
        try {
            $query = "
                SELECT 
                    s.service_id, s.name as service_name,
                    COUNT(a.appointment_id) as appointment_count,
                    AVG(s.duration) as avg_duration,
                    SUM(s.price) as total_revenue
                FROM 
                    provider_services ps
                JOIN 
                    services s ON ps.service_id = s.service_id
                LEFT JOIN 
                    appointments a ON 
                    a.provider_id = ps.provider_id AND 
                    a.service_id = ps.service_id AND
                    a.status = 'completed'
                WHERE 
                    ps.provider_id = ?
                GROUP BY 
                    s.service_id, s.name
                ORDER BY 
                    appointment_count DESC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $providerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
            
            return $services;
        } catch (Exception $e) {
            error_log("Error getting service statistics: " . $e->getMessage());
            return [];
        }
    }

    public function setActiveStatus($providerId, $isActive) {
    try {
        $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE user_id = ? AND role = 'provider'");
        $stmt->bind_param("ii", $isActive, $providerId);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error updating provider active status: " . $e->getMessage());
        return false;
    }
}

public function setAcceptingNewPatients($providerId, $accepting) {
    try {
        $stmt = $this->db->prepare("UPDATE provider_profiles SET accepting_new_patients = ? WHERE provider_id = ?");
        $stmt->bind_param("ii", $accepting, $providerId);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error updating accepting_new_patients: " . $e->getMessage());
        return false;
    }
}
    /**
     * Get provider comparison metrics for admin analysis
     * 
     * @param int $limit Number of providers to return (default 10)
     * @return array Provider comparison data
     */
    public function getProviderComparisonMetrics($limit = 10) {
        try {
            $query = "
                SELECT 
                    u.user_id, CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                    pp.specialization,
                    COUNT(a.appointment_id) as total_appointments,
                    SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                    SUM(CASE WHEN a.status = 'no_show' THEN 1 ELSE 0 END) as no_shows,
                    SUM(CASE WHEN a.status = 'canceled' THEN 1 ELSE 0 END) as cancellations,
                    ROUND(
                        (SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) / 
                        IF(COUNT(a.appointment_id) = 0, 1, COUNT(a.appointment_id)) * 100), 
                        2
                    ) as completion_rate
                FROM 
                    users u
                JOIN 
                    provider_profiles pp ON u.user_id = pp.provider_id
                LEFT JOIN 
                    appointments a ON u.user_id = a.provider_id
                WHERE 
                    u.role = 'provider' AND
                    u.is_active = 1
                GROUP BY 
                    u.user_id, provider_name, pp.specialization
                ORDER BY 
                    total_appointments DESC
                LIMIT ?
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $providers = [];
            while ($row = $result->fetch_assoc()) {
                $providers[] = $row;
            }
            
            return $providers;
        } catch (Exception $e) {
            error_log("Error getting provider comparison metrics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get provider workload distribution for specified period
     * 
     * @param int $providerId Provider ID
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @return array Workload distribution by day of week and time
     */
    public function getWorkloadDistribution($providerId, $startDate, $endDate) {
        try {
            // Get distribution by day of week
            $dowQuery = "
                SELECT 
                    DAYNAME(appointment_date) as day_of_week,
                    COUNT(*) as appointment_count
                FROM 
                    appointments
                WHERE 
                    provider_id = ? AND
                    appointment_date BETWEEN ? AND ? AND
                    status NOT IN ('canceled', 'no_show')
                GROUP BY 
                    day_of_week
                ORDER BY 
                    FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
            ";
            
            $dowStmt = $this->db->prepare($dowQuery);
            $dowStmt->bind_param("iss", $providerId, $startDate, $endDate);
            $dowStmt->execute();
            $dowResult = $dowStmt->get_result();
            
            $dayDistribution = [];
            while ($row = $dowResult->fetch_assoc()) {
                $dayDistribution[$row['day_of_week']] = $row['appointment_count'];
            }
            
            // Get distribution by time of day
            $timeQuery = "
                SELECT 
                    CASE
                        WHEN HOUR(start_time) < 9 THEN 'Early Morning (before 9am)'
                        WHEN HOUR(start_time) < 12 THEN 'Morning (9am-12pm)'
                        WHEN HOUR(start_time) < 15 THEN 'Early Afternoon (12pm-3pm)'
                        WHEN HOUR(start_time) < 18 THEN 'Late Afternoon (3pm-6pm)'
                        ELSE 'Evening (after 6pm)'
                    END as time_slot,
                    COUNT(*) as appointment_count
                FROM 
                    appointments
                WHERE 
                    provider_id = ? AND
                    appointment_date BETWEEN ? AND ? AND
                    status NOT IN ('canceled', 'no_show')
                GROUP BY 
                    time_slot
                ORDER BY 
                    MIN(HOUR(start_time))
            ";
            
            $timeStmt = $this->db->prepare($timeQuery);
            $timeStmt->bind_param("iss", $providerId, $startDate, $endDate);
            $timeStmt->execute();
            $timeResult = $timeStmt->get_result();
            
            $timeDistribution = [];
            while ($row = $timeResult->fetch_assoc()) {
                $timeDistribution[$row['time_slot']] = $row['appointment_count'];
            }
            
            return [
                'provider_id' => $providerId,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'by_day_of_week' => $dayDistribution,
                'by_time_of_day' => $timeDistribution
            ];
        } catch (Exception $e) {
            error_log("Error getting workload distribution: " . $e->getMessage());
            return [
                'provider_id' => $providerId,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get an admin overview of inactive providers
     * 
     * @param int $days Number of days to look back for activity
     * @return array Inactive providers
     */
    public function getInactiveProviders($days = 30) {
        try {
            $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
            
            $query = "
                SELECT 
                    u.user_id, u.first_name, u.last_name, u.email, u.phone,
                    u.created_at, u.last_login,
                    pp.specialization, pp.title,
                    (SELECT MAX(appointment_date) FROM appointments 
                     WHERE provider_id = u.user_id) as last_appointment,
                    (SELECT COUNT(*) FROM appointments 
                     WHERE provider_id = u.user_id) as total_appointments
                FROM 
                    users u
                JOIN 
                    provider_profiles pp ON u.user_id = pp.provider_id
                WHERE 
                    u.role = 'provider' AND
                    u.is_active = 1 AND
                    (u.last_login IS NULL OR u.last_login < ?) AND
                    NOT EXISTS (
                        SELECT 1 FROM appointments 
                        WHERE provider_id = u.user_id AND appointment_date >= ?
                    )
                ORDER BY 
                    u.last_login ASC, last_appointment ASC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ss", $cutoffDate, $cutoffDate);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $providers = [];
            while ($row = $result->fetch_assoc()) {
                $providers[] = $row;
            }
            
            return $providers;
        } catch (Exception $e) {
            error_log("Error getting inactive providers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Bulk update provider status (active/inactive)
     * 
     * @param array $providerIds Array of provider IDs
     * @param int $isActive Active status (1 = active, 0 = inactive)
     * @return bool Success status
     */
    public function bulkUpdateStatus($providerIds, $isActive) {
        if (empty($providerIds)) {
            return false;
        }
        
        try {
            $this->db->begin_transaction();
            
            $placeholders = str_repeat('?,', count($providerIds) - 1) . '?';
            $query = "UPDATE users SET is_active = ? WHERE user_id IN ({$placeholders}) AND role = 'provider'";
            
            $types = 'i' . str_repeat('i', count($providerIds));
            $params = array_merge([$isActive], $providerIds);
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute();
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error bulk updating provider status: " . $e->getMessage());
            return false;
        }
    }
     /**
     * Get provider's availability schedule
     *
     * @param int $providerId Provider ID
     * @return array Availability schedule
     */
    public function getProviderAvailability($providerId) {
        $stmt = $this->db->prepare(
            "SELECT *
            FROM provider_availability
            WHERE provider_id = ?"
        );
        
        $stmt->bind_param("i", $providerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        return [];
    }
    /**
     * Get provider efficiency metrics
     * 
     * @param int $providerId Provider ID
     * @return array Provider efficiency metrics
     */
    public function getProviderEfficiency($providerId) {
        try {
            // Get average appointment duration
            $durationQuery = "
                SELECT
                    a.service_id,
                    s.name as service_name,
                    AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) as avg_duration_seconds,
                    s.duration as expected_duration_minutes
                FROM
                    appointments a
                JOIN
                    services s ON a.service_id = s.service_id
                WHERE
                    a.provider_id = ? AND
                    a.status = 'completed'
                GROUP BY
                    s.service_id, s.name, s.duration
            ";
            
            $durationStmt = $this->db->prepare($durationQuery);
            $durationStmt->bind_param("i", $providerId);
            $durationStmt->execute();
            $durationResult = $durationStmt->get_result();
            
            $serviceEfficiency = [];
            $totalActualSeconds = 0;
            $totalExpectedSeconds = 0;
            
            while ($row = $durationResult->fetch_assoc()) {
                $actualMinutes = $row['avg_duration_seconds'] / 60;
                $expectedMinutes = $row['expected_duration_minutes'];
                $efficiency = $expectedMinutes > 0 ? ($expectedMinutes / $actualMinutes) * 100 : 0;
                
                $serviceEfficiency[] = [
                    'service_id' => $row['service_id'] ?? 0,
                    'service_name' => $row['service_name'] ?? 'Unknown',
                    'actual_minutes' => round($actualMinutes, 2),
                    'expected_minutes' => $expectedMinutes,
                    'efficiency_percentage' => round($efficiency, 2)
                ];
                
                $totalActualSeconds += $row['avg_duration_seconds'];
                $totalExpectedSeconds += $expectedMinutes * 60;
            }
            
            // Calculate overall efficiency
            $overallEfficiency = $totalActualSeconds > 0 ? 
                ($totalExpectedSeconds / $totalActualSeconds) * 100 : 0;
            
            return [
                'provider_id' => $providerId,
                'service_efficiency' => $serviceEfficiency,
                'overall_efficiency' => round($overallEfficiency, 2),
                'is_efficient' => $overallEfficiency >= 90
            ];
        } catch (Exception $e) {
            error_log("Error calculating provider efficiency: " . $e->getMessage());
            return [
                'provider_id' => $providerId,
                'error' => $e->getMessage()
            ];
        }
    }
    public function updateAvailabilitySlot($availabilityId, $date, $startTime, $endTime) {
        try {
            $query = "UPDATE provider_availability 
                      SET availability_date = ?, start_time = ?, end_time = ? 
                      WHERE availability_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssi", $date, $startTime, $endTime, $availabilityId);
            $success = $stmt->execute();
            
            return $success;
        } catch (Exception $e) {
            error_log("Error in updateAvailabilitySlot: " . $e->getMessage());
            return false;
        }
    }

    public function updateRecurringSchedule($scheduleId, $startTime, $endTime) {
        try {
            $query = "UPDATE recurring_schedules 
                      SET start_time = ?, end_time = ? 
                      WHERE schedule_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssi", $startTime, $endTime, $scheduleId);
            $success = $stmt->execute();
            
            return $success;
        } catch (Exception $e) {
            error_log("Error in updateRecurringSchedule: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get provider by ID
     *
     * @param int $provider_id The provider ID
     * @return array|false Provider data or false if not found
     */
    public function getProviderById($provider_id) {
        try {
            // Join users table to get complete provider information
            $sql = "SELECT 
                    pp.*, 
                    u.email, 
                    u.first_name, 
                    u.last_name, 
                    u.phone,
                    u.is_active
                FROM provider_profiles pp
                JOIN users u ON pp.provider_id = u.user_id
                WHERE pp.provider_id = ?";
        
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $provider_id);
            $stmt->execute();
        
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        
            return false;
        } catch (Exception $e) {
            error_log("Error fetching provider data: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get providers who offer a specific service
     * 
     * @param int $service_id The ID of the service
     * @return array Array of providers
     */
    public function getProvidersByService($service_id) {
        try {
            $query = "
                SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                       pp.specialization, pp.bio, pp.accepting_new_patients
                FROM users u
                JOIN provider_profiles pp ON u.user_id = pp.provider_id
                JOIN provider_services ps ON u.user_id = ps.provider_id
                WHERE ps.service_id = ? AND u.role = 'provider' AND u.is_active = 1
                AND pp.accepting_new_patients = 1
                ORDER BY u.last_name, u.first_name
            ";
        
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
        
            $providers = [];
            while ($row = $result->fetch_assoc()) {
                $providers[] = $row;
            }
        
            return $providers;
        } catch (Exception $e) {
            error_log("Error getting providers by service: " . $e->getMessage());
            return [];
        }
    }
}
?>