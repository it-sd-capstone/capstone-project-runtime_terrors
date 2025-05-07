
<?php
class AppointmentsController {
    private $db;
    private $appointmentModel;
    private $activityLogModel;
    private $serviceModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = get_db();
        require_once MODEL_PATH . '/ActivityLog.php';
        require_once MODEL_PATH . '/Appointment.php';
        require_once MODEL_PATH . '/Services.php';
        $this->activityLogModel = new ActivityLog($this->db);
        $this->appointmentModel = new Appointment($this->db);
        $this->serviceModel = new Service($this->db);
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
            $userAppointments = $this->appointmentModel->getUpcomingAppointments($userId);
        } elseif ($userRole === 'provider') {
            $userAppointments = $this->appointmentModel->getByProvider($userId);
        } elseif ($userRole === 'admin') {
            $userAppointments = $this->appointmentModel->getAllAppointments();
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
                    header('Location: ' . base_url('index.php/appointments?success=canceled'));
                    exit;
                }
            }
        }
        set_flash_message('error', 'Failed to cancel appointment');
        header('Location: ' . base_url('index.php/appointments?error=cancel_failed'));
        exit;
    }

    /**
     * Unified status update method (POST only)
     */
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
            set_flash_message('success', 'Appointment status updated successfully');
        } else {
            set_flash_message('error', 'Failed to update appointment status');
        }
        header('Location: ' . base_url('index.php/appointments/view?id=' . $appointmentId));
        exit;
    }

    /**
     * Update appointment notes (POST only)
     */
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
            $upcomingAppointments = $this->appointmentModel->getProviderUpcomingAppointments($user_id) ?? [];
            $pastAppointments = $this->appointmentModel->getProviderPastAppointments($user_id) ?? [];
        } else { // admin
            $upcomingAppointments = $this->appointmentModel->getAllUpcomingAppointments() ?? [];
            $pastAppointments = $this->appointmentModel->getAllPastAppointments() ?? [];
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
        if (!in_array($_SESSION['role'], ['patient', 'provider', 'admin'])) {
            set_flash_message('error', 'You do not have permission to reschedule');
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
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
            $newDate = $_POST['appointment_date'] ?? '';
            $newTime = $_POST['appointment_time'] ?? '';
            if (empty($newDate) || empty($newTime)) {
                set_flash_message('error', "New date and time are required");
            } else {
                $serviceId = $appointment['service_id'];
                $availableSlots = $this->appointmentModel->findAvailableSlots(
                    $appointment['provider_id'],
                    $newDate,
                    $serviceId
                );
                $matchingSlot = null;
                foreach ($availableSlots as $slot) {
                    if ($slot['start_time'] == $newTime) {
                        $matchingSlot = $slot;
                        break;
                    }
                }
                if (!$matchingSlot) {
                    set_flash_message('error', "Selected time slot is not available");
                } else {
                    $endTimeStr = $matchingSlot['end_time'];
                    $result = $this->appointmentModel->rescheduleAppointment(
                        $appointmentId, $newDate, $newTime, $endTimeStr
                    );
                    if ($result) {
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
                        set_flash_message('success', "Appointment rescheduled successfully");
                        header('Location: ' . base_url('index.php/appointments/view?id=' . $appointmentId));
                        exit;
                    } else {
                        set_flash_message('error', "Failed to reschedule appointment");
                    }
                }
            }
        }
        $providerId = $appointment['provider_id'];
        $availableDates = [];
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+14 days'));
        $availableSlots = $this->appointmentModel->getAppointmentsByDateRange($startDate, $endDate, $providerId);
        if (!empty($availableSlots)) {
            foreach ($availableSlots as $slot) {
                if (!in_array($slot['appointment_date'], $availableDates)) {
                    $availableDates[] = $slot['appointment_date'];
                }
            }
        }
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
        $logs = $this->activityLogModel->getAppointmentLogs($appointmentId);
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
}
?>
