<?php
class AppointmentsController {
    private $db;
    private $appointmentModel;
    private $activityLogModel;
    private $serviceModel;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Debug message to check connection type
        error_log("Using MySQLi connection in AppointmentsController");
        
        // Initialize models
        require_once MODEL_PATH . '/ActivityLog.php';
        require_once MODEL_PATH . '/Appointment.php';
        require_once MODEL_PATH . '/Services.php';
        
        $this->activityLogModel = new ActivityLog($this->db);
        $this->appointmentModel = new Appointment($this->db);
        $this->serviceModel = new Service($this->db);
        // Check if user is logged in - add this authentication check
        $this->requireLogin();
    }
    
    /**
     * Require user to be logged in
     */
    private function requireLogin() {
        // If not logged in, redirect to auth page
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            // Store the requested URL so we can redirect back after login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            
            // Redirect to login page
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
    }
    
    public function index() {
        error_log("APPOINTMENT controller index method called - this should appear if routing is correct");
        
        // User is guaranteed to be logged in at this point
        $isLoggedIn = true;
        $userRole = $_SESSION['role'];
        $userId = $_SESSION['user_id'];
        
        // Get available appointment slots using the model method
        $availableSlots = $this->appointmentModel->getAvailableSlots();
        error_log("Successfully retrieved available slots: " . count($availableSlots));
        error_log("Available slots: " . count($availableSlots));
        
        // Get user's appointments if logged in using the model methods
        $userAppointments = [];
        if ($userRole === 'patient') {
            $userAppointments = $this->appointmentModel->getUpcomingAppointments($userId);
        } elseif ($userRole === 'provider') {
            $userAppointments = $this->appointmentModel->getByProvider($userId);
        } elseif ($userRole === 'admin') {
            $userAppointments = $this->appointmentModel->getAllAppointments();
        }
        error_log("Appointments: " . count($userAppointments));
        
        // Pass data to view
        include VIEW_PATH . '/appointments/index.php';
    }
    
    public function book() {
        // User is already authenticated by the constructor
        
        // Only patients can book appointments
        if ($_SESSION['role'] !== 'patient' && $_SESSION['role'] !== 'admin') {
            // Redirect to appointments view
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $availabilityId = $_GET['id'] ?? null;
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!verify_csrf_token()) {
                return;
            }
            
            // Process booking form
            $availabilityId = $_POST['availability_id'] ?? null;
            $serviceId = $_POST['service_id'] ?? null;
            $type = $_POST['type'] ?? 'in_person';
            $notes = $_POST['notes'] ?? '';
            $reason = $_POST['reason'] ?? '';
            
            if (!$availabilityId || !$serviceId) {
                $error = "Missing required booking information";
            } else {
                // Use the model method to book the appointment
                $bookingResult = $this->appointmentModel->bookAppointment(
                    $_SESSION['user_id'],     // patientId
                    $availabilityId,          // availabilityId
                    $serviceId,               // serviceId
                    $type,                    // type
                    $notes,                   // notes
                    $reason                   // reason
                );
                
                if ($bookingResult && isset($bookingResult['appointment_id'])) {
                    // Log appointment creation with detailed information
                    $details = json_encode([
                        'service_id' => $serviceId,
                        'appointment_date' => $bookingResult['appointment_date'],
                        'start_time' => $bookingResult['start_time'],
                        'end_time' => $bookingResult['end_time'],
                        'provider_id' => $bookingResult['provider_id']
                    ]);
                    
                    $this->activityLogModel->logAppointment(
                        $_SESSION['user_id'], 
                        'created', 
                        $bookingResult['appointment_id'],
                        $details
                    );
                    
                    // Mark the availability as no longer available
                    $updateStmt = $this->db->prepare("UPDATE provider_availability 
                                                      SET is_available = 0 
                                                      WHERE availability_id = ?");
                    $updateStmt->bind_param("i", $availabilityId);
                    $updateStmt->execute();
                    
                    $_SESSION['success'] = "Appointment booked successfully!";
                    header('Location: ' . base_url('index.php/appointments?success=booked'));
                    exit;
                } else {
                    $error = $bookingResult['message'] ?? "Failed to book appointment";
                }
            }
        }
        
        // Get available slot details for the form
        $availabilityDetails = null;
        if ($availabilityId) {
            // Using the model to get availability details
            $slots = $this->appointmentModel->getAvailableSlots();
            
            foreach ($slots as $slot) {
                if ($slot['availability_id'] == $availabilityId) {
                    $availabilityDetails = $slot;
                    break;
                }
            }
            
            if (!$availabilityDetails) {
                $error = "Selected time slot not found or no longer available";
            }
        } else {
            $error = "No time slot selected";
        }
        
        // Get all services for dropdown
        // This would need a Service model, but we can use a simplified approach for now
        // In a complete implementation, this should use a ServiceModel
        $services = $this->serviceModel->getAllServices();
        // Or if you need only active services:
        $services = array_filter($services, function($service) {
            return $service['is_active'] == 1;
        });
        
        // Load booking form view
        include VIEW_PATH . '/appointments/book.php';
    }
    
    public function cancel() {
        // User is already authenticated by the constructor
        
        $appointmentId = $_GET['id'] ?? null;
        $reason = $_GET['reason'] ?? 'No reason provided';
        
        if (!$appointmentId) {
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        // Check appointment belongs to user or user is admin/provider
        $appointment = $this->appointmentModel->getAppointmentById($appointmentId);
        
        if ($appointment) {
            // Only the patient who booked it, the provider assigned to it, or an admin can cancel
            if ($_SESSION['user_id'] == $appointment['patient_id'] ||
                $_SESSION['user_id'] == $appointment['provider_id'] ||
                $_SESSION['role'] === 'admin') {
                
                // Use the model to cancel the appointment
                if ($this->appointmentModel->cancelAppointment($appointmentId, $reason)) {
                    // Log appointment cancellation with details
                    $details = json_encode([
                        'previous_status' => $appointment['status'],
                        'cancellation_reason' => $reason,
                        'canceled_by_role' => $_SESSION['role'],
                        'appointment_date' => $appointment['appointment_date'],
                        'start_time' => $appointment['start_time']
                    ]);
                    $this->activityLogModel->logAppointment($_SESSION['user_id'], 'canceled', $appointmentId, $details);
                    
                    // Restore availability using model method
                    $this->appointmentModel->restoreAvailabilitySlot($appointmentId);
                    
                    header('Location: ' . base_url('index.php/appointments?success=canceled'));
                    exit;
                }
            }
        }
        
        // If we get here, something went wrong
        header('Location: ' . base_url('index.php/appointments?error=cancel_failed'));
        exit;
    }
    
    // Add a new method for updating appointment status that includes logging
    public function updateStatus() {
        // Only admin and providers can update appointment status
        if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'provider') {
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $appointmentId = $_POST['appointment_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;
        
        if (!$appointmentId || !$newStatus) {
            header('Location: ' . base_url('index.php/appointments?error=missing_data'));
            exit;
        }
        
        // Get current appointment details
        $appointment = $this->appointmentModel->getAppointmentById($appointmentId);
        
        if (!$appointment) {
            header('Location: ' . base_url('index.php/appointments?error=appointment_not_found'));
            exit;
        }
        
        // Use the model's updateStatus method
        if ($this->appointmentModel->updateStatus($appointmentId, $newStatus)) {
            // Log the status change with detailed information
            $details = json_encode([
                'previous_status' => $appointment['status'],
                'new_status' => $newStatus,
                'changed_by' => $_SESSION['user_id'],
                'changed_by_role' => $_SESSION['role'],
                'appointment_date' => $appointment['appointment_date'],
                'reason' => $_POST['reason'] ?? 'No reason provided'
            ]);
            $this->activityLogModel->logAppointment($_SESSION['user_id'], "status_changed", $appointmentId, $details);
            
            header('Location: ' . base_url('index.php/appointments?success=updated'));
        } else {
            header('Location: ' . base_url('index.php/appointments?error=update_failed'));
        }
        exit;
    }
    
    // View appointment history with detailed logs
    /**
     * View Appointment History
     * @param int $id Appointment ID (optional)
     */
    public function history($id = null) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('auth');
            return;
        }
        
        // Get appointment ID from URL parameter or query string
        $appointment_id = $id ?? $_GET['id'] ?? null;
        
        if ($appointment_id) {
            // Get appointment details - make sure this query includes ALL fields
            $appointment = $this->appointmentModel->getById($appointment_id);
            
            // For debugging, you could add:
            // echo '<pre>'; print_r($appointment); echo '</pre>';
            
            if (!$appointment || !$this->canAccessAppointment($appointment)) {
                set_flash_message('error', 'You do not have permission to view this appointment.');
                redirect('appointments');
                return;
            }
            
            // Get appointment logs (instead of history)
            $logs = $this->appointmentModel->getAppointmentLogs($appointment_id);
            
            include VIEW_PATH . '/appointments/history.php';
            return;
        }
        
        // Otherwise show user's appointment history
        if ($role === 'patient') {
            $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($user_id) ?? [];
            $pastAppointments = $this->appointmentModel->getPastAppointments($user_id) ?? [];
        } elseif ($role === 'provider') {
            $upcomingAppointments = $this->appointmentModel->getProviderUpcomingAppointments($user_id) ?? [];
            $pastAppointments = $this->appointmentModel->getProviderPastAppointments($user_id) ?? [];
        } else { // admin
            $upcomingAppointments = $this->appointmentModel->getAllUpcomingAppointments() ?? [];
            $pastAppointments = $this->appointmentModel->getAllPastAppointments() ?? [];
        }
        
        include VIEW_PATH . '/appointments/history.php';
    }
    
    /**
     * Check if current user can access an appointment
     * @param array $appointment The appointment data
     * @return bool True if user can access, false otherwise
     */
    private function canAccessAppointment($appointment) {
        if (!$appointment) {
            return false;
        }
        
        $role = $_SESSION['role'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 0;
        
        if ($role === 'admin') {
            return true;
        }
        
        if ($role === 'provider' && $appointment['provider_id'] == $user_id) {
            return true;
        }
        
        if ($role === 'patient' && $appointment['patient_id'] == $user_id) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Add a method for rescheduling appointments
     */
    public function reschedule() {
        // Only the patient, provider, or admin can reschedule
        if (!in_array($_SESSION['role'], ['patient', 'provider', 'admin'])) {
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $appointmentId = $_GET['id'] ?? null;
        
        if (!$appointmentId) {
            $_SESSION['error'] = "Appointment ID is required";
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        // Get the current appointment details
        $appointment = $this->appointmentModel->getAppointmentById($appointmentId);
        
        if (!$appointment) {
            $_SESSION['error'] = "Appointment not found";
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        // Check permissions - only patient who booked, provider assigned, or admin can reschedule
        if ($_SESSION['user_id'] != $appointment['patient_id'] &&
            $_SESSION['user_id'] != $appointment['provider_id'] &&
            $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = "You don't have permission to reschedule this appointment";
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newDate = $_POST['appointment_date'] ?? '';
            $newTime = $_POST['appointment_time'] ?? '';
            
            if (empty($newDate) || empty($newTime)) {
                $_SESSION['error'] = "New date and time are required";
            } else {
                // Calculate end time based on service duration
                $startTime = new DateTime($newTime);
                $endTime = clone $startTime;
                $serviceId = $appointment['service_id'];
                
                // Find available slots - this will calculate end time based on service duration
                $availableSlots = $this->appointmentModel->findAvailableSlots(
                    $appointment['provider_id'], 
                    $newDate,
                    $serviceId
                );
                
                // Find an available slot that matches the requested time
                $matchingSlot = null;
                foreach ($availableSlots as $slot) {
                    if ($slot['start_time'] == $newTime) {
                        $matchingSlot = $slot;
                        break;
                    }
                }
                
                if (!$matchingSlot) {
                    $_SESSION['error'] = "Selected time slot is not available";
                } else {
                    // Extract end time from the matching slot
                    $endTimeStr = $matchingSlot['end_time'];
                    
                    // Use the model to reschedule the appointment
                    $result = $this->appointmentModel->rescheduleAppointment(
                        $appointmentId, $newDate, $newTime, $endTimeStr
                    );
                    
                    if ($result) {
                        // Log the rescheduling
                        $details = json_encode([
                            'previous_date' => $appointment['appointment_date'],
                            'previous_time' => $appointment['start_time'],
                            'new_date' => $newDate,
                            'new_time' => $newTime,
                            'rescheduled_by' => $_SESSION['user_id'],
                            'rescheduled_by_role' => $_SESSION['role']
                        ]);
                        
                        $this->activityLogModel->logAppointment(
                            $_SESSION['user_id'], 'rescheduled', $appointmentId, $details
                        );
                        
                        $_SESSION['success'] = "Appointment rescheduled successfully";
                        header('Location: ' . base_url('index.php/appointments'));
                        exit;
                    } else {
                        $_SESSION['error'] = "Failed to reschedule appointment";
                    }
                }
            }
        }
        
        // Get available dates for the provider
        $providerId = $appointment['provider_id'];
        $availableDates = [];
        
        // Use the model to get available dates
        $nextTwoWeeks = [];
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+14 days'));
        
        // Get available slots for the next 14 days
        $availableSlots = $this->appointmentModel->getAppointmentsByDateRange($startDate, $endDate, $providerId);
        
        // Extract unique dates from available slots
        if (!empty($availableSlots)) {
            foreach ($availableSlots as $slot) {
                if (!in_array($slot['appointment_date'], $availableDates)) {
                    $availableDates[] = $slot['appointment_date'];
                }
            }
        }
        
        // Include the reschedule form
        include VIEW_PATH . '/appointments/reschedule.php';
    }
    
    /**
     * AJAX endpoint to get available time slots for a date
     */
    public function getTimeSlots() {
        // This should be an AJAX request
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }
        
        $providerId = $_GET['provider_id'] ?? null;
        $date = $_GET['date'] ?? null;
        $serviceId = $_GET['service_id'] ?? null;
        
        if (!$providerId || !$date) {
            http_response_code(400);
            echo json_encode(['error' => 'Provider ID and date are required']);
            exit;
        }
        
        // Use the model method to find available slots
        $slots = $this->appointmentModel->findAvailableSlots($providerId, $date, $serviceId);
        
        header('Content-Type: application/json');
        echo json_encode(['slots' => $slots]);
        exit;
    }
    
    /**
     * Method to view detailed appointment information
     */
    public function view() {
        $appointmentId = $_GET['id'] ?? null;
        
        if (!$appointmentId) {
            $_SESSION['error'] = "Appointment ID is required";
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        // Get appointment details using the model
        $appointment = $this->appointmentModel->getAppointmentById($appointmentId);
        
        if (!$appointment) {
            $_SESSION['error'] = "Appointment not found";
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        // Check permissions - only related parties can view details
        if ($_SESSION['user_id'] != $appointment['patient_id'] && 
            $_SESSION['user_id'] != $appointment['provider_id'] && 
            $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = "You don't have permission to view this appointment";
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        // Get appointment history/logs
        $logs = $this->activityLogModel->getAppointmentLogs($appointmentId);
        
        // Include view
        include VIEW_PATH . '/appointments/view.php';
    }
    
    /**
     * Get statistics for appointments (for provider or admin dashboards)
     */
    public function statistics() {
        // Only providers and admins can see statistics
        if ($_SESSION['role'] !== 'provider' && $_SESSION['role'] !== 'admin') {
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $period = $_GET['period'] ?? 'weekly';
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $limit = $_GET['limit'] ?? 8;
        
        // Use the model to get statistics
        $stats = $this->appointmentModel->getAppointmentStatistics($period, $startDate, $limit);
        
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            header('Content-Type: application/json');
            echo json_encode(['statistics' => $stats]);
            exit;
        }
        
        // Include view
        include VIEW_PATH . '/appointments/statistics.php';
    }
    
    /**
     * Export appointments to CSV
     */
    public function export() {
        // Only admins can export data
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 month'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        // Get appointments using the model
        $appointments = $this->appointmentModel->getAppointmentsByDateRange($startDate, $endDate);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="appointments_' . date('Y-m-d') . '.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, [
            'Appointment ID', 'Patient', 'Provider', 'Service', 
            'Date', 'Start Time', 'End Time', 'Status', 
            'Type', 'Reason', 'Notes', 'Created At'
        ]);
        
        // Add appointment data
        foreach ($appointments as $appointment) {
            fputcsv($output, [
                $appointment['appointment_id'],
                $appointment['patient_name'] ?? 'Unknown',
                $appointment['provider_name'] ?? 'Unknown',
                $appointment['service_name'] ?? 'Unknown',
                $appointment['appointment_date'],
                $appointment['start_time'],
                $appointment['end_time'],
                $appointment['status'],
                $appointment['type'],
                $appointment['reason'],
                $appointment['notes'],
                $appointment['created_at']
            ]);
        }
        
        // Close the output stream
        fclose($output);
        exit;
    }
    
    /**
     * Update appointment status
     * 
     * @param int $appointment_id The ID of the appointment
     * @param string $status The new status
     */
    public function update_status($appointment_id, $status) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? '';
        
        // Get appointment details to verify ownership
        $appointment = $this->appointmentModel->getById($appointment_id);
        
        // Validate permission (only provider, patient who owns it, or admin can update)
        if (!$appointment || 
            ($role == 'provider' && $appointment['provider_id'] != $user_id) || 
            ($role == 'patient' && $appointment['patient_id'] != $user_id) && 
            $role != 'admin') {
            set_flash_message('error', 'You do not have permission to update this appointment');
            redirect($role . '/index');
            return;
        }
        
        // List of valid statuses
        $valid_statuses = ['scheduled', 'confirmed', 'canceled', 'completed', 'no_show', 'pending'];
        if (!in_array($status, $valid_statuses)) {
            set_flash_message('error', 'Invalid status');
            redirect($role . '/viewAppointment/' . $appointment_id);
            return;
        }
        
        // Update the appointment status
        $result = $this->appointmentModel->updateStatus($appointment_id, $status);
        
        if ($result) {
            // Record the action in logs if needed
            // $this->appointmentModel->logAction($appointment_id, 'status_changed', $user_id, $status);
            
            set_flash_message('success', 'Appointment status updated successfully');
        } else {
            set_flash_message('error', 'Failed to update appointment status');
        }
        
        // Redirect back to the appointment view
        redirect($role . '/viewAppointment/' . $appointment_id);
    }

    /**
     * Update appointment notes
     */
    public function update_notes() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? '';
        
        // Get form data
        $appointment_id = $_POST['appointment_id'] ?? null;
        $notes = $_POST['notes'] ?? '';
        
        if (!$appointment_id) {
            set_flash_message('error', 'No appointment specified');
            redirect($role . '/index');
            return;
        }
        
        // Get appointment details to verify ownership
        $appointment = $this->appointmentModel->getById($appointment_id);
        
        // Validate permission (only provider or admin can update notes)
        if (!$appointment || 
            ($role == 'provider' && $appointment['provider_id'] != $user_id) && 
            $role != 'admin') {
            set_flash_message('error', 'You do not have permission to update appointment notes');
            redirect($role . '/index');
            return;
        }
        
        // Update the appointment notes
        $result = $this->appointmentModel->updateNotes($appointment_id, $notes);
        
        if ($result) {
            set_flash_message('success', 'Appointment notes updated successfully');
        } else {
            set_flash_message('error', 'Failed to update appointment notes');
        }
        
        // Redirect back to the appointment view
        redirect($role . '/viewAppointment/' . $appointment_id);
    }
}
?>
