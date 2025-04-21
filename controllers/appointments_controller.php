<?php
class AppointmentsController {
    private $db;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
        
        // Debug message to check connection type
        error_log("Using MySQLi connection in AppointmentsController");
    }
    
    public function index() {
        error_log("APPOINTMENT controller index method called - this should appear if routing is correct");
        
        // Check if user is logged in
        $isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
        $userRole = $isLoggedIn ? $_SESSION['role'] : '';
        $userId = $isLoggedIn ? $_SESSION['user_id'] : null;
        
        // Get available appointment slots
        $availableSlots = $this->getAvailableSlots();
        error_log("Successfully retrieved available slots: " . count($availableSlots));
        error_log("Available slots: " . count($availableSlots));
        
        // Get user's appointments if logged in
        $userAppointments = [];
        if ($isLoggedIn) {
            if ($userRole === 'patient') {
                $userAppointments = $this->getUserAppointments($userId);
            } elseif ($userRole === 'provider') {
                $userAppointments = $this->getProviderAppointments($userId);
            } elseif ($userRole === 'admin') {
                $userAppointments = $this->getAllAppointments();
            }
        }
        error_log("Appointments: " . count($userAppointments));
        
        // Pass data to view
        include VIEW_PATH . '/appointments/index.php';
    }
    
    public function book() {
        // Require login for booking
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['logged_in'] !== true) {
            // Redirect to login
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
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
            // Process booking form
            $availabilityId = $_POST['availability_id'] ?? null;
            $serviceId = $_POST['service_id'] ?? null;
            
            if (!$availabilityId || !$serviceId) {
                $error = "Missing required booking information";
            } else {
                // Get availability details
                $stmt = $this->db->prepare("SELECT provider_id, available_date, start_time, end_time 
                                           FROM provider_availability 
                                           WHERE availability_id = ? AND is_available = 1");
                $stmt->bind_param("i", $availabilityId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $availability = $result->fetch_assoc();
                    
                    // Get service duration
                    $stmtService = $this->db->prepare("SELECT duration FROM services WHERE service_id = ?");
                    $stmtService->bind_param("i", $serviceId);
                    $stmtService->execute();
                    $serviceResult = $stmtService->get_result();
                    $service = $serviceResult->fetch_assoc();
                    
                    // Calculate end time based on service duration
                    $startTime = new DateTime($availability['start_time']);
                    $endTime = clone $startTime;
                    $endTime->add(new DateInterval('PT' . $service['duration'] . 'M'));
                    $endTimeStr = $endTime->format('H:i:s');
                    
                    // Create the appointment
                    $stmt = $this->db->prepare("INSERT INTO appointments 
                                              (patient_id, provider_id, service_id, availability_id, 
                                               appointment_date, start_time, end_time, status) 
                                              VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                    
                    $patientId = $_SESSION['user_id'];
                    $providerId = $availability['provider_id'];
                    $appointmentDate = $availability['available_date'];
                    $startTimeStr = $availability['start_time'];
                    
                    $stmt->bind_param("iiissss", 
                        $patientId, 
                        $providerId, 
                        $serviceId, 
                        $availabilityId, 
                        $appointmentDate, 
                        $startTimeStr, 
                        $endTimeStr
                    );
                    
                    if ($stmt->execute()) {
                        // Mark the availability as no longer available
                        $updateStmt = $this->db->prepare("UPDATE provider_availability 
                                                         SET is_available = 0 
                                                         WHERE availability_id = ?");
                        $updateStmt->bind_param("i", $availabilityId);
                        $updateStmt->execute();
                        
                        $success = "Appointment booked successfully!";
                        // Redirect to appointments list
                        header('Location: ' . base_url('index.php/appointments?success=booked'));
                        exit;
                    } else {
                        $error = "Failed to book appointment: " . $this->db->error;
                    }
                } else {
                    $error = "Selected time slot is no longer available";
                }
            }
        }
        
        // Get available slot details for the form
        $availabilityDetails = null;
        if ($availabilityId) {
            $stmt = $this->db->prepare("
                SELECT pa.*, u.first_name, u.last_name 
                FROM provider_availability pa
                JOIN users u ON pa.provider_id = u.user_id
                WHERE availability_id = ? AND is_available = 1
            ");
            $stmt->bind_param("i", $availabilityId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $availabilityDetails = $result->fetch_assoc();
            } else {
                $error = "Selected time slot not found or no longer available";
            }
        } else {
            $error = "No time slot selected";
        }
        
        // Get all services for dropdown
        $services = [];
        $stmt = $this->db->query("SELECT service_id, name, description, duration FROM services WHERE is_active = 1");
        if ($stmt) {
            while ($row = $stmt->fetch_assoc()) {
                $services[] = $row;
            }
        }
        
        // Load booking form view
        include VIEW_PATH . '/appointments/book.php';
    }
    
    public function cancel() {
        // Require login for canceling
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
        
        $appointmentId = $_GET['id'] ?? null;
        
        if (!$appointmentId) {
            header('Location: ' . base_url('index.php/appointments'));
            exit;
        }
        
        // Check appointment belongs to user or user is admin/provider
        $stmt = $this->db->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
        $stmt->bind_param("i", $appointmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $appointment = $result->fetch_assoc();
            
            // Only the patient who booked it, the provider assigned to it, or an admin can cancel
            if ($_SESSION['user_id'] == $appointment['patient_id'] || 
                $_SESSION['user_id'] == $appointment['provider_id'] || 
                $_SESSION['role'] === 'admin') {
                
                // Update appointment status
                $updateStmt = $this->db->prepare("UPDATE appointments SET status = 'canceled' WHERE appointment_id = ?");
                $updateStmt->bind_param("i", $appointmentId);
                
                if ($updateStmt->execute()) {
                    // Free up the availability slot again
                    if ($appointment['availability_id']) {
                        $freeSlot = $this->db->prepare("UPDATE provider_availability SET is_available = 1 WHERE availability_id = ?");
                        $freeSlot->bind_param("i", $appointment['availability_id']);
                        $freeSlot->execute();
                    }
                    
                    header('Location: ' . base_url('index.php/appointments?success=canceled'));
                    exit;
                }
            }
        }
        
        // If we get here, something went wrong
        header('Location: ' . base_url('index.php/appointments?error=cancel_failed'));
        exit;
    }
    
    private function getAvailableSlots() {
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
    
    private function getUserAppointments($userId) {
        $appointments = [];
        
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   u_provider.first_name as provider_first_name, 
                   u_provider.last_name as provider_last_name,
                   s.name as service_name, 
                   s.duration as service_duration
            FROM appointments a
            JOIN users u_provider ON a.provider_id = u_provider.user_id
            JOIN services s ON a.service_id = s.service_id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date, a.start_time
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
        
        return $appointments;
    }
    
    private function getProviderAppointments($userId) {
        $appointments = [];
        
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   u_patient.first_name as patient_first_name, 
                   u_patient.last_name as patient_last_name,
                   s.name as service_name, 
                   s.duration as service_duration
            FROM appointments a
            JOIN users u_patient ON a.patient_id = u_patient.user_id
            JOIN services s ON a.service_id = s.service_id
            WHERE a.provider_id = ?
            ORDER BY a.appointment_date, a.start_time
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
        
        return $appointments;
    }
    
    private function getAllAppointments() {
        $appointments = [];
        
        $stmt = $this->db->query("
            SELECT a.*, 
                   u_patient.first_name as patient_first_name, 
                   u_patient.last_name as patient_last_name,
                   u_provider.first_name as provider_first_name, 
                   u_provider.last_name as provider_last_name,
                   s.name as service_name, 
                   s.duration as service_duration
            FROM appointments a
            JOIN users u_patient ON a.patient_id = u_patient.user_id
            JOIN users u_provider ON a.provider_id = u_provider.user_id
            JOIN services s ON a.service_id = s.service_id
            ORDER BY a.appointment_date, a.start_time
        ");
        
        if ($stmt) {
            while ($row = $stmt->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
        
        return $appointments;
    }
}
?>
