<?php

class Provider {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get provider's profile details securely
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

    // Update provider profile securely with transactions
    public function updateProfile($provider_id, $profileData) {
        try {
            $this->db->begin_transaction();

            // If we have a full profile update with user data
            if (isset($profileData['first_name'])) {
                // Update users table
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, phone = ?
                    WHERE user_id = ? AND role = 'provider'
                ");
                $stmt->bind_param("sssi", 
                    $profileData['first_name'], 
                    $profileData['last_name'], 
                    $profileData['phone'], 
                    $provider_id
                );
                $success = $stmt->execute();

                if (!$success) {
                    throw new Exception("Profile update failed (users table).");
                }
            }

            // Update provider_profiles table
            $query = "UPDATE provider_profiles SET ";
            $params = [];
            $types = "";
            
            if (isset($profileData['specialization'])) {
                $query .= "specialization = ?, ";
                $params[] = $profileData['specialization'];
                $types .= "s";
            }
            
            if (isset($profileData['title'])) {
                $query .= "title = ?, ";
                $params[] = $profileData['title'];
                $types .= "s";
            }
            
            if (isset($profileData['bio'])) {
                $query .= "bio = ?, ";
                $params[] = $profileData['bio'];
                $types .= "s";
            }
            
            if (isset($profileData['accepting_new_patients'])) {
                $query .= "accepting_new_patients = ?, ";
                $params[] = $profileData['accepting_new_patients'];
                $types .= "i";
            }
            
            if (isset($profileData['max_patients_per_day'])) {
                $query .= "max_patients_per_day = ?, ";
                $params[] = $profileData['max_patients_per_day'];
                $types .= "i";
            }
            
            // Remove trailing comma and space
            $query = rtrim($query, ", ");
            
            $query .= " WHERE provider_id = ?";
            $params[] = $provider_id;
            $types .= "i";
            
            $stmt = $this->db->prepare($query);
            
            // Only bind parameters if we have fields to update
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
                $success = $stmt->execute();
                
                if (!$success) {
                    throw new Exception("Profile update failed (provider_profiles table).");
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error updating profile: " . $e->getMessage());
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

    // Get provider availability
    public function getAvailability($provider_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM provider_availability 
                WHERE provider_id = ? AND availability_date >= CURDATE()
                ORDER BY availability_date, start_time
            ");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching availability: " . $e->getMessage());
            return [];
        }
    }

    // Add provider availability
    public function addAvailability($availabilityData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO provider_availability 
                (provider_id, availability_date, start_time, end_time, max_appointments, is_available)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->bind_param(
                "isssi", 
                $availabilityData['provider_id'], 
                $availabilityData['availability_date'], 
                $availabilityData['start_time'], 
                $availabilityData['end_time'],
                $availabilityData['max_appointments']
            );
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding availability: " . $e->getMessage());
            return false;
        }
    }

    // Legacy method for backward compatibility
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
                SELECT ps.*, s.name, s.description, s.duration, s.is_active
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

    // Get all providers
    public function getAll() {
        try {
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.first_name, u.last_name, 
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

    // Get recurring schedules
    public function getRecurringSchedules($provider_id) {
        try {
            $query = "SELECT * FROM recurring_schedules
                    WHERE provider_id = ?
                    ORDER BY day_of_week, start_time";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $schedules = [];
            
            while ($row = $result->fetch_assoc()) {
                $schedules[] = $row;
            }
            
            return $schedules;
        } catch (Exception $e) {
            error_log("Error fetching recurring schedules: " . $e->getMessage());
            return [];
        }
    }

    // Get booked appointments for a provider
    public function getBookedAppointments($provider_id) {
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
     * Add a new provider with profile
     * @param array $userData User data
     * @param array $profileData Profile data
     * @return int|bool New provider ID or false on failure
     */
    public function createProvider($userData, $profileData) {
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
     * Search for providers by various criteria
     * @param array $criteria Search criteria
     * @return array Matching providers
     */
    public function searchProviders($criteria = []) {
        try {
            $query = "
                SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                       pp.specialization, pp.title, pp.bio, pp.accepting_new_patients,
                       pp.profile_image
                FROM users u
                JOIN provider_profiles pp ON u.user_id = pp.provider_id
                WHERE u.role = 'provider' AND u.is_active = 1
            ";
            
            $params = [];
            $types = "";
            
            // Add search conditions
            if (!empty($criteria['specialization'])) {
                $query .= " AND pp.specialization LIKE ?";
                $params[] = "%" . $criteria['specialization'] . "%";
                $types .= "s";
            }
            
            if (!empty($criteria['name'])) {
                $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ?)";
                $params[] = "%" . $criteria['name'] . "%";
                $params[] = "%" . $criteria['name'] . "%";
                $types .= "ss";
            }
            
            if (isset($criteria['accepting_new_patients']) && $criteria['accepting_new_patients']) {
                $query .= " AND pp.accepting_new_patients = 1";
            }
            
            // Add ordering
            $query .= " ORDER BY u.last_name, u.first_name";
            
            $stmt = $this->db->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error searching providers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get provider's upcoming appointments
     * @param int $provider_id Provider ID
     * @param string $startDate Optional start date filter (defaults to today)
     * @param string $endDate Optional end date filter
     * @return array Upcoming appointments
     */
    public function getUpcomingAppointments($provider_id, $startDate = null, $endDate = null) {
        try {
            $query = "
                SELECT a.*,
                       u.first_name AS patient_first_name,
                       u.last_name AS patient_last_name,
                       s.name AS service_name
                FROM appointments a
                JOIN users u ON a.patient_id = u.user_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.provider_id = ?
                AND a.status NOT IN ('canceled', 'no_show', 'completed')
            ";
            
            $params = [$provider_id];
            $types = "i";
            
            if ($startDate) {
                $query .= " AND a.appointment_date >= ?";
                $params[] = $startDate;
                $types .= "s";
            } else {
                $query .= " AND a.appointment_date >= CURDATE()";
            }
            
            if ($endDate) {
                $query .= " AND a.appointment_date <= ?";
                $params[] = $endDate;
                $types .= "s";
            }
            
            $query .= " ORDER BY a.appointment_date, a.start_time";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error fetching upcoming appointments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all providers with their complete details for admin management
     * Includes appointment counts, service counts, and profile details
     * 
     * @return array All providers with details
     */
    public function getAllProvidersWithDetails() {
        try {
            $query = "
                SELECT 
                    u.user_id, u.first_name, u.last_name, u.email, u.phone, 
                    u.created_at, u.is_active,
                    pp.specialization, pp.title, pp.bio, pp.accepting_new_patients,
                    pp.max_patients_per_day, pp.profile_image,
                    (SELECT COUNT(*) FROM provider_services WHERE provider_id = u.user_id) AS service_count,
                    (SELECT COUNT(*) FROM appointments 
                     WHERE provider_id = u.user_id AND status NOT IN ('canceled', 'no_show')) AS appointment_count,
                    (SELECT COUNT(*) FROM appointments 
                     WHERE provider_id = u.user_id AND appointment_date >= CURDATE() 
                     AND status NOT IN ('canceled', 'no_show')) AS upcoming_count,
                    (SELECT COUNT(*) FROM provider_availability 
                     WHERE provider_id = u.user_id AND available_date >= CURDATE()) AS availability_count
                FROM 
                    users u
                LEFT JOIN 
                    provider_profiles pp ON u.user_id = pp.provider_id
                WHERE 
                    u.role = 'provider'
                ORDER BY 
                    u.last_name, u.first_name
            ";
            
            $result = $this->db->query($query);
            
            $providers = [];
            while ($row = $result->fetch_assoc()) {
                $providers[] = $row;
            }
            
            return $providers;
        } catch (Exception $e) {
            error_log("Error fetching providers with details: " . $e->getMessage());
            return [];
        }
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
                    available_date BETWEEN ? AND ?
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
                    pa.available_date,
                    SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(pa.end_time, pa.start_time)))) as total_hours,
                    COUNT(DISTINCT a.appointment_id) as booked_appointments
                FROM 
                    provider_availability pa
                LEFT JOIN 
                    appointments a ON 
                    pa.provider_id = a.provider_id AND 
                    pa.available_date = a.appointment_date AND
                    a.status NOT IN ('canceled', 'no_show')
                WHERE 
                    pa.provider_id = ? AND
                    pa.available_date BETWEEN ? AND ?
                GROUP BY 
                    pa.available_date
                ORDER BY 
                    pa.available_date
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
                    s.service_id, s.duration
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
}
?>
