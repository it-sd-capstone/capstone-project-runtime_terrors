<?php
require_once __DIR__ . '/../helpers/system_notifications.php';
require_once MODEL_PATH . '/ActivityLog.php';
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/Services.php';
require_once MODEL_PATH . '/Notification.php';
class AppointmentsController {
    private $db;
    private $appointmentModel;
    private $activityLogModel;
    private $serviceModel;
    private $providerModel;
    private $notificationModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = get_db();
        $this->activityLogModel = new ActivityLog($this->db);
        $this->appointmentModel = new Appointment($this->db);
        $this->serviceModel = new Services($this->db);
        $this->providerModel = new Provider($this->db);
        $this->notificationModel = new Notification($this->db);
        $this->requireLogin();
    }

    private function requireLogin() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
    }

    public function index() {
        $userRole = $_SESSION['role'];
        $userId = $_SESSION['user_id'];
        $availableSlots = $this->appointmentModel->getAvailableSlots();
        $userAppointments = [];
        if ($userRole === 'patient') {
            $userAppointments = array_merge(
                $this->appointmentModel->getUpcomingAppointments($userId),
                $this->appointmentModel->getPastAppointments($userId)
            );
        } elseif ($userRole === 'provider') {
            $userAppointments = $this->appointmentModel->getByProvider($userId);
        } elseif ($userRole === 'admin') {
            $userAppointments = $this->appointmentModel->getAllAppointments();
        }
        
        $pastAppointments = $this->appointmentModel->getPastAppointments($userId);
        
        $completedUnratedAppointments = [];
        if ($userRole === 'patient') {
            foreach ($pastAppointments as $appointment) {
                if ($appointment['status'] === 'completed' && 
                    !$this->isAppointmentRated($appointment['appointment_id'], $userId)) {
                    $completedUnratedAppointments[] = $appointment;
                }
            }
        }
        
        include VIEW_PATH . '/appointments/index.php';
    }

    public function book() {
        if ($_SESSION['role'] !== 'patient' && $_SESSION['role'] !== 'admin') {
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        $availabilityId = $_GET['id'] ?? null;
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token()) {
                set_flash_message('error', 'Invalid security token.');
                header('Location: ' . base_url('index.php/appointments/book?id=' . urlencode($_POST['availability_id'] ?? '')));
                exit;
            }
            $availabilityId = $_POST['availability_id'] ?? null;
            $serviceId = $_POST['service_id'] ?? null;
            $type = $_POST['type'] ?? 'in_person';
            $notes = $_POST['notes'] ?? '';
            $reason = $_POST['reason'] ?? '';
            if (!$availabilityId || !$serviceId) {
                set_flash_message('error', "Missing required booking information");
            } else {
                $availableSlots = $this->appointmentModel->getAvailableSlots();
                $slotIsAvailable = false;
                foreach ($availableSlots as $slot) {
                    if ($slot['availability_id'] == $availabilityId) {
                        $slotIsAvailable = true;
                        break;
                    }
                }
                if (!$slotIsAvailable) {
                    set_flash_message('error', "Selected time slot is no longer available.");
                } else {
                    $bookingResult = $this->appointmentModel->bookAppointment(
                        $_SESSION['user_id'],
                        $availabilityId,
                        $serviceId,
                        $type,
                        $notes,
                        $reason
                    );
                    if ($bookingResult && isset($bookingResult['appointment_id'])) {
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
                        
                        // Format date/time for notifications
                        $formattedDate = date('F j, Y', strtotime($bookingResult['appointment_date']));
                        $formattedTime = date('g:i A', strtotime($bookingResult['start_time']));
                        
                        // Get service name for better notification detail
                        $serviceName = '';
                        try {
                            $serviceData = $this->serviceModel->getServiceById($serviceId);
                            $serviceName = $serviceData['name'] ?? '';
                        } catch (Exception $e) {
                            error_log("Error fetching service name: " . $e->getMessage());
                        }
                        
                        // Create notification for patient (booking user)
                        $this->notificationModel->addNotification([
                            'user_id' => $_SESSION['user_id'],
                            'subject' => 'Appointment Confirmation',
                            'message' => "Your appointment" . ($serviceName ? " for $serviceName" : "") . 
                                        " has been scheduled for $formattedDate at $formattedTime.",
                            'type' => 'appointment',
                            'appointment_id' => $bookingResult['appointment_id']
                        ]);
                        
                        // Create notification for provider
                        $this->notificationModel->addNotification([
                            'user_id' => $bookingResult['provider_id'],
                            'subject' => 'New Appointment Booking',
                            'message' => "A new appointment" . ($serviceName ? " for $serviceName" : "") . 
                                        " has been scheduled for $formattedDate at $formattedTime.",
                            'type' => 'appointment',
                            'appointment_id' => $bookingResult['appointment_id']
                        ]);
                        
                        // Create system notification for admin tracking
                        $notification = new Notification($this->db);
                        $notification->create([
                            'subject' => 'New Appointment Booked',
                            'message' => "Appointment ID: " . $bookingResult['appointment_id'] . " has been created",
                            'type' => 'appointment_created',
                            'is_system' => 1,
                            'audience' => 'admin'
                        ]);
                        
                        set_flash_message('success', "Appointment booked successfully!");
                        header('Location: ' . base_url('index.php/appointments?success=booked'));
                        exit;
                    } else {
                        set_flash_message('error', $bookingResult['message'] ?? "Failed to book appointment");
                    }
                }
            }
        }

        $availabilityDetails = null;
        if ($availabilityId) {
            $slots = $this->appointmentModel->getAvailableSlots();
            foreach ($slots as $slot) {
                if ($slot['availability_id'] == $availabilityId) {
                    $availabilityDetails = $slot;
                    break;
                }
            }
            if (!$availabilityDetails) {
                set_flash_message('error', "Selected time slot not found or no longer available");
            }
        } else {
            set_flash_message('error', "No time slot selected");
        }
        $services = $this->serviceModel->getAllServices();
        $services = array_filter($services, function($service) {
            return $service['is_active'] == 1;
        });
        include VIEW_PATH . '/appointments/book.php';
    }

    private function markTimeSlotAsBooked($provider_id, $date, $start_time, $end_time, $booked_service_id) {
        $conflictingAvailabilities = $this->providerModel->getConflictingAvailabilities(
            $provider_id, 
            $date, 
            $start_time, 
            $end_time,
            $booked_service_id
        );
        
        foreach ($conflictingAvailabilities as $availability) {
            $this->providerModel->updateAvailabilityStatus(
                $availability['id'], 
                0
            );
        }
    }

   public function cancel() {
        $appointmentId = $_GET['id'] ?? null;
        $reason = $_GET['reason'] ?? 'No reason provided';
        
        if (!$appointmentId) {
            set_flash_message('error', 'No appointment specified');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $appointment = $this->appointmentModel->getById($appointmentId);
        
        if ($appointment) {
            if (
                $_SESSION['user_id'] == $appointment['patient_id'] ||
                $_SESSION['user_id'] == $appointment['provider_id'] ||
                $_SESSION['role'] === 'admin'
            ) {
                if ($this->appointmentModel->cancelAppointment($appointmentId, $reason)) {
                    // Create system notification for admin
                    $notification = new Notification($this->db);
                    $notification->create([
                        'subject' => 'Appointment Cancelled',
                        'message' => "Appointment ID: " . $appointmentId . " has been cancelled",
                        'type' => 'appointment_cancelled',
                        'is_system' => 1,
                        'audience' => 'admin'
                    ]);
                    
                    // Format date/time for readability in notifications
                    $formattedDate = date('F j, Y', strtotime($appointment['appointment_date']));
                    $formattedTime = date('g:i A', strtotime($appointment['start_time']));
                    
                    // Add patient notification
                    $this->notificationModel->addNotification([
                        'user_id' => $appointment['patient_id'],
                        'subject' => 'Appointment Cancelled',
                        'message' => "Your appointment scheduled for $formattedDate at $formattedTime has been cancelled.",
                        'type' => 'appointment',
                        'appointment_id' => $appointmentId
                    ]);
                    
                    // Add provider notification
                    $this->notificationModel->addNotification([
                        'user_id' => $appointment['provider_id'],
                        'subject' => 'Appointment Cancelled',
                        'message' => "An appointment scheduled for $formattedDate at $formattedTime has been cancelled.",
                        'type' => 'appointment',
                        'appointment_id' => $appointmentId
                    ]);
                    
                    // Log the cancellation in activity log
                    $details = json_encode([
                        'previous_status' => $appointment['status'],
                        'cancellation_reason' => $reason,
                        'canceled_by_role' => $_SESSION['role'],
                        'appointment_date' => $appointment['appointment_date'],
                        'start_time' => $appointment['start_time']
                    ]);
                    
                    $this->activityLogModel->logAppointment(
                        $_SESSION['user_id'],
                        'canceled',
                        $appointmentId,
                        $details
                    );
                    
                    if (isset($appointment['availability_id'])) {
                        $this->appointmentModel->restoreAvailabilitySlot($appointment['availability_id']);
                    }
                    
                    set_flash_message('success', 'Appointment canceled successfully');
                    header('Location: ' . base_url('index.php/appointments'));
                    exit;
                }
            }
        }
        
        set_flash_message('error', 'Failed to cancel appointment');
        header('Location: ' . base_url('index.php/appointments'));
        exit;
    }


    public function updateStatus() {
        if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'provider') {
            set_flash_message('error', 'You do not have permission to update status');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            set_flash_message('error', 'Invalid request method');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        if (!verify_csrf_token()) {
            set_flash_message('error', 'Invalid security token.');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        $appointmentId = $_POST['appointment_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;
        if (!$appointmentId || !$newStatus) {
            set_flash_message('error', 'Missing data');
            header('Location: ' . base_url('index.php/appointments?error=missing_data'));
            exit;
        }
        $appointment = $this->appointmentModel->getById($appointmentId);
        if (!$appointment) {
            set_flash_message('error', 'Appointment not found');
            header('Location: ' . base_url('index.php/appointments?error=appointment_not_found'));
            exit;
        }
        $valid_statuses = ['scheduled', 'confirmed', 'canceled', 'completed', 'no_show', 'pending'];
        if (!in_array($newStatus, $valid_statuses)) {
            set_flash_message('error', 'Invalid status');
            header('Location: ' . base_url('index.php/appointments?error=invalid_status'));
            exit;
        }
        if ($this->appointmentModel->updateStatus($appointmentId, $newStatus)) {
            $details = json_encode([
                'previous_status' => $appointment['status'],
                'new_status' => $newStatus,
                'changed_by' => $_SESSION['user_id'],
                'changed_by_role' => $_SESSION['role'],
                'appointment_date' => $appointment['appointment_date'],
                'reason' => $_POST['reason'] ?? 'No reason provided'
            ]);
            $this->activityLogModel->logAppointment($_SESSION['user_id'], "status_changed", $appointmentId, $details);
            
            // Format date for notifications
            $formattedDate = date('F j, Y', strtotime($appointment['appointment_date']));
            $formattedTime = date('g:i A', strtotime($appointment['start_time']));
            
            // Create appropriate message based on new status
            $patientMessage = "Your appointment scheduled for $formattedDate at $formattedTime has been ";
            $providerMessage = "Appointment scheduled for $formattedDate at $formattedTime has been ";
            
            switch ($newStatus) {
                case 'confirmed':
                    $patientMessage .= "confirmed. Please arrive 10 minutes before your scheduled time.";
                    $providerMessage .= "confirmed.";
                    break;
                case 'canceled':
                    $patientMessage .= "canceled. " . ($_POST['reason'] ?? '');
                    $providerMessage .= "canceled. " . ($_POST['reason'] ?? '');
                    break;
                case 'completed':
                    $patientMessage .= "marked as completed. Thank you for your visit!";
                    $providerMessage .= "marked as completed.";
                    break;
                case 'no_show':
                    $patientMessage .= "marked as 'no show'. Please contact us to reschedule.";
                    $providerMessage .= "marked as 'no show'.";
                    break;
                default:
                    $patientMessage .= "updated to: $newStatus";
                    $providerMessage .= "updated to: $newStatus";
                    break;
            }
            
            // Notify patient about the status change
            $this->notificationModel->addNotification([
                'user_id' => $appointment['patient_id'],
                'subject' => 'Appointment Status Updated',
                'message' => $patientMessage,
                'type' => 'appointment_status',
                'appointment_id' => $appointmentId
            ]);
            
            // Notify provider if admin made the change
            if ($_SESSION['role'] === 'admin' && $appointment['provider_id'] != $_SESSION['user_id']) {
                $this->notificationModel->addNotification([
                    'user_id' => $appointment['provider_id'],
                    'subject' => 'Appointment Status Updated',
                    'message' => $providerMessage,
                    'type' => 'appointment_status',
                    'appointment_id' => $appointmentId
                ]);
            }
            
            // Add system notification for admins
            $this->notificationModel->create([
                'subject' => 'Appointment Status Changed',
                'message' => "Appointment ID: $appointmentId status changed from '{$appointment['status']}' to '$newStatus'",
                'type' => 'appointment_status_changed',
                'is_system' => 1,
                'audience' => 'admin'
            ]);
            
            set_flash_message('success', 'Appointment status updated successfully');
        } else {
            set_flash_message('error', 'Failed to update appointment status');
        }
        header('Location: ' . base_url('index.php/appointments/view?id=' . $appointmentId));
        exit;
    }


    public function update_notes() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            set_flash_message('error', 'Invalid request method');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        if (!verify_csrf_token()) {
            set_flash_message('error', 'Invalid security token.');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? '';
        $appointment_id = $_POST['appointment_id'] ?? null;
        $notes = $_POST['notes'] ?? '';
        
        if (!$appointment_id) {
            set_flash_message('error', 'No appointment specified');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $appointment = $this->appointmentModel->getById($appointment_id);
        if (!$appointment ||
            ($role == 'provider' && $appointment['provider_id'] != $user_id) &&
            $role != 'admin') {
            set_flash_message('error', 'You do not have permission to update appointment notes');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $result = $this->appointmentModel->updateNotes($appointment_id, $notes);
        if ($result) {
            $details = json_encode([
                'updated_by' => $user_id,
                'updated_by_role' => $role,
                'notes' => $notes
            ]);
            
            $this->activityLogModel->logAppointment($user_id, "notes_updated", $appointment_id, $details);
            
            // Format date for notifications
            $formattedDate = date('F j, Y', strtotime($appointment['appointment_date']));
            $formattedTime = date('g:i A', strtotime($appointment['start_time']));
            
            // Create notification for patient about notes update
            $patientMessage = "Notes have been updated for your appointment scheduled for $formattedDate at $formattedTime.";
            
            // Notify patient about the notes update
            $this->notificationModel->addNotification([
                'user_id' => $appointment['patient_id'],
                'subject' => 'Appointment Notes Updated',
                'message' => $patientMessage,
                'type' => 'appointment_notes',
                'appointment_id' => $appointment_id
            ]);
            
            // Notify provider if admin made the change
            if ($role === 'admin' && $appointment['provider_id'] != $user_id) {
                $providerMessage = "Notes have been updated for appointment scheduled on $formattedDate at $formattedTime.";
                
                $this->notificationModel->addNotification([
                    'user_id' => $appointment['provider_id'],
                    'subject' => 'Appointment Notes Updated',
                    'message' => $providerMessage,
                    'type' => 'appointment_notes',
                    'appointment_id' => $appointment_id
                ]);
            }
            
            // Add system notification for admins
            $this->notificationModel->create([
                'subject' => 'Appointment Notes Updated',
                'message' => "Notes updated for Appointment ID: $appointment_id by " . ($_SESSION['first_name'] ?? 'User') . " " . ($_SESSION['last_name'] ?? ''),
                'type' => 'appointment_notes_updated',
                'is_system' => 1,
                'audience' => 'admin'
            ]);
            
            set_flash_message('success', 'Appointment notes updated successfully');
        } else {
            set_flash_message('error', 'Failed to update appointment notes');
        }
        
        header('Location: ' . base_url('index.php/appointments/view?id=' . $appointment_id));
        exit;
    }


    public function history($id = null) {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth');
            return;
        }
        $appointment_id = $id ?? $_GET['id'] ?? null;
        if ($appointment_id) {
            $appointment = $this->appointmentModel->getById($appointment_id);
            if (!$appointment || !$this->canAccessAppointment($appointment)) {
                set_flash_message('error', 'You do not have permission to view this appointment.');
                redirect('appointments');
                return;
            }
            $logs = $this->appointmentModel->getAppointmentLogs($appointment_id);
            include VIEW_PATH . '/appointments/history.php';
            return;
        }
        $role = $_SESSION['role'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 0;
        if ($role === 'patient') {
            $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($user_id) ?? [];
            $pastAppointments = $this->appointmentModel->getPastAppointments($user_id) ?? [];
        } elseif ($role === 'provider') {
            $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments($user_id) ?? [];
            $pastAppointments = $this->appointmentModel->getPastAppointments($user_id) ?? [];
        } else {
            $upcomingAppointments = $this->appointmentModel->getUpcomingAppointments() ?? [];
            $pastAppointments = $this->appointmentModel->getPastAppointments() ?? [];
        }
        include VIEW_PATH . '/appointments/history.php';
    }

    private function canAccessAppointment($appointment) {
        if (!$appointment) return false;
        $role = $_SESSION['role'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 0;
        if ($role === 'admin') return true;
        if ($role === 'provider' && $appointment['provider_id'] == $user_id) return true;
        if ($role === 'patient' && $appointment['patient_id'] == $user_id) return true;
        return false;
    }

    public function reschedule() {

        // Set consistent timezone
        date_default_timezone_set('America/New_York'); // Update to your timezone
        
        if (!in_array($_SESSION['role'], ['patient', 'provider', 'admin'])) {
            set_flash_message('error', 'You do not have permission to reschedule');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $appointmentId = $_GET['id'] ?? $_POST['appointment_id'] ?? null;
        if (!$appointmentId) {
            set_flash_message('error', "Appointment ID is required to reschedule.");
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        $appointment = $this->appointmentModel->getById($appointmentId);
        if (!$appointment) {
            set_flash_message('error', "Appointment not found");
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        if (
            $_SESSION['user_id'] != $appointment['patient_id'] &&
            $_SESSION['user_id'] != $appointment['provider_id'] &&
            $_SESSION['role'] !== 'admin'
        ) {
            set_flash_message('error', "You don't have permission to reschedule this appointment");
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token()) {
                set_flash_message('error', 'Invalid security token.');
                header('Location: ' . base_url('index.php/appointments/reschedule?id=' . urlencode($appointmentId)));
                exit;
            }
            
            // Debug the incoming data
            error_log("Reschedule POST data: " . print_r($_POST, true));
            
            // Get the appointment ID
            $appointment_id = $_POST['appointment_id'] ?? null;
            $new_date = $_POST['new_date'] ?? null;
            $time_slot = $_POST['time_slot'] ?? null;
            $reschedule_reason = $_POST['reschedule_reason'] ?? '';
            
            error_log("Processing reschedule: ID=$appointment_id, Date=$new_date, Time=$time_slot");
            
            // Validate the data
            if (!$appointment_id || !$new_date || !$time_slot) {
                $error = "Missing required information. Please try again.";
                error_log("Reschedule failed: Missing data - ID=$appointment_id, Date=$new_date, Time=$time_slot");
                
                // Re-display the form with the error
                $appointment = $this->appointmentModel->getById($appointment_id);
                $availableSlotsPerDay = [];
                // Re-fetch slots
                // [your existing slot fetching code]
                $availableSlotsJson = json_encode($availableSlotsPerDay);
                include VIEW_PATH . '/appointments/reschedule.php';
                return;
            }
            
            // Support both naming conventions (original and new API-based)
            $newDate = $_POST['new_date'] ?? $_POST['appointment_date'] ?? '';
            $newTime = $_POST['time_slot'] ?? $_POST['appointment_time'] ?? '';
            
            error_log("Reschedule request - New date: $newDate, Time slot: $newTime");
            
            if (empty($newDate) || empty($newTime)) {
                set_flash_message('error', "New date and time are required");
            } else {
                $serviceId = $appointment['service_id'];
                
                // Process time_slot format from API if needed
                if (strpos($newTime, 'slot_') === 0) {
                    // Extract the actual time from the slot ID format (e.g., slot_123_0900)
                    $timeParts = explode('_', $newTime);
                    if (count($timeParts) >= 3) {
                        $timeValue = $timeParts[2];
                        // Format time from 0900 to 09:00
                        $formattedTime = substr($timeValue, 0, 2) . ':' . substr($timeValue, 2);
                        $newTime = $formattedTime;
                        error_log("Converted time slot '$newTime' from API format");
                    }
                }
                
                $availableSlots = $this->appointmentModel->findAvailableSlots(
                    $appointment['provider_id'],
                    $newDate,
                    $serviceId
                );
                
                error_log("Found " . count($availableSlots) . " available slots for date $newDate");
                
                $matchingSlot = null;
                foreach ($availableSlots as $slot) {
                    error_log("Checking slot: " . $slot['start_time'] . " against requested time: " . $newTime);
                    if ($slot['start_time'] == $newTime) {
                        $matchingSlot = $slot;
                        break;
                    }
                }
                
                if (!$matchingSlot) {
                    error_log("No matching slot found for time: $newTime");
                    set_flash_message('error', "Selected time slot is not available");
                } else {
                    $endTimeStr = $matchingSlot['end_time'];
                    
                    error_log("Rescheduling appointment $appointmentId to $newDate at $newTime-$endTimeStr");
                    
                    $result = $this->appointmentModel->rescheduleAppointment(
                        $appointmentId, $newDate, $newTime, $endTimeStr
                    );
                    
                    if ($result) {
                        error_log("Reschedule SUCCESS for appointment $appointmentId");
                        
                        // Add system notification here
                        $notification = new Notification($this->db);
                        $notification->create([
                            'subject' => 'Appointment Rescheduled',
                            'message' => "Appointment ID: " . $appointmentId . " has been rescheduled",
                            'type' => 'appointment_rescheduled',
                            'is_system' => 1,
                            'audience' => 'admin'
                        ]);
                        
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
                        
                        // Create notifications for both patient and provider
                        $formattedDate = date('F j, Y', strtotime($newDate));
                        $formattedTime = date('g:i A', strtotime($newTime));
                        
                        $this->notificationModel->addNotification([
                            'user_id' => $appointment['patient_id'],
                            'subject' => 'Appointment Rescheduled',
                            'message' => "Your appointment has been rescheduled to $formattedDate at $formattedTime",
                            'type' => 'appointment',
                            'appointment_id' => $appointmentId
                        ]);

                        $this->notificationModel->addNotification([
                            'user_id' => $appointment['provider_id'],
                            'subject' => 'Appointment Rescheduled',
                            'message' => "An appointment has been rescheduled to $formattedDate at $formattedTime",
                            'type' => 'appointment',
                            'appointment_id' => $appointmentId
                        ]);
                        
                        set_flash_message('success', "Appointment rescheduled successfully");
                        header('Location: ' . base_url('index.php/appointments/view?id=' . $appointmentId));
                        exit;
                    } else {
                        error_log("Reschedule FAILED for appointment $appointmentId");
                        set_flash_message('error', "Failed to reschedule appointment");
                    }
                }
            }
        }
        
        // When loading the page, get AVAILABLE dates and times, not existing appointments
        $providerId = $appointment['provider_id'];
        $serviceId = $appointment['service_id'];
        $availableDates = [];
        
        // Define the date range
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+14 days'));
        
        // Log what we're looking for
        error_log("Looking for available slots for Provider: $providerId, Service: $serviceId, Dates: $startDate to $endDate");
        
        // Get available slots for this provider and service combination
        // This should use the same method that your API endpoint uses
        $availableSlotsPerDay = [];
        for ($date = strtotime($startDate); $date <= strtotime($endDate); $date = strtotime('+1 day', $date)) {
            $currentDate = date('Y-m-d', $date);
            
            // Use the same method your API uses to find slots
            $slotsForDate = $this->appointmentModel->findAvailableSlots(
                $providerId,
                $currentDate,
                $serviceId
            );
            
            if (!empty($slotsForDate)) {
                $availableDates[] = $currentDate;
                $availableSlotsPerDay[$currentDate] = $slotsForDate;
                error_log("Found " . count($slotsForDate) . " slots for $currentDate");
            }
        }
        
        // If you need the data in the view
        $providerData = $this->providerModel->getById($providerId);
        $serviceData = $this->serviceModel->getServiceById($serviceId);
        
        // Include the available slots data for the JavaScript
        $availableSlotsJson = json_encode($availableSlotsPerDay);
        
        include VIEW_PATH . '/appointments/reschedule.php';
    }


    public function getTimeSlots() {
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
        $slots = $this->appointmentModel->findAvailableSlots($providerId, $date, $serviceId);
        header('Content-Type: application/json');
        echo json_encode(['slots' => $slots]);
        exit;
    }

    public function view() {
        $appointmentId = $_GET['id'] ?? null;
        if (!$appointmentId) {
            set_flash_message('error', "Appointment ID is required");
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        $appointment = $this->appointmentModel->getById($appointmentId);
        if (!$appointment) {
            set_flash_message('error', "Appointment not found");
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        if ($_SESSION['user_id'] != $appointment['patient_id'] &&
            $_SESSION['user_id'] != $appointment['provider_id'] &&
            $_SESSION['role'] !== 'admin') {
            set_flash_message('error', "You don't have permission to view this appointment");
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        $logs = $this->appointmentModel->getAppointmentLogs($appointmentId);
        include VIEW_PATH . '/appointments/view.php';
    }

    public function statistics() {
        if ($_SESSION['role'] !== 'provider' && $_SESSION['role'] !== 'admin') {
            set_flash_message('error', 'You do not have permission to view statistics');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        $period = $_GET['period'] ?? 'weekly';
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $limit = $_GET['limit'] ?? 8;
        $stats = $this->appointmentModel->getAppointmentStatistics($period, $startDate, $limit);
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            header('Content-Type: application/json');
            echo json_encode(['statistics' => $stats]);
            exit;
        }
        include VIEW_PATH . '/appointments/statistics.php';
    }

    public function export() {
        if ($_SESSION['role'] !== 'admin') {
            set_flash_message('error', 'You do not have permission to export data');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 month'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $appointments = $this->appointmentModel->getAppointmentsByDateRange($startDate, $endDate);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="appointments_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Appointment ID', 'Patient', 'Provider', 'Service',
            'Date', 'Start Time', 'End Time', 'Status',
            'Type', 'Reason', 'Notes', 'Created At'
        ]);
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
        fclose($output);
        exit;
    }

    public function submitRating() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . base_url('index.php/auth/login'));
            exit;
        }
        
        $appointment_id = $_POST['appointment_id'] ?? null;
        $provider_id = $_POST['provider_id'] ?? null;
        $rating = $_POST['rating'] ?? null;
        $comment = $_POST['comment'] ?? '';
        
        if (!$appointment_id || !$provider_id || !$rating) {
            set_flash_message('error', 'Missing required information');
            header('Location: ' . base_url('index.php/appointments?error=missing_data'));
            exit;
        }
        
        if (!isset($this->appointmentModel)) {
            require_once MODEL_PATH . '/Appointment.php';
            $this->appointmentModel = new Appointment($this->db);
        }
        
        $success = $this->appointmentModel->rateAppointment(
            $appointment_id,
            $_SESSION['user_id'],
            $provider_id,
            $rating,
            $comment
        );
        
        if ($success) {
            set_flash_message('success', 'Thank you for your feedback!');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        } else {
            set_flash_message('error', 'Failed to submit your rating');
            header('Location: ' . base_url('index.php/appointments?error=rating_failed'));
            exit;
        }
    }

    private function isAppointmentRated($appointmentId, $patientId) {
        $query = "SELECT rating_id 
                FROM appointment_ratings 
                WHERE appointment_id = ? AND patient_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $appointmentId, $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }    
}
?>