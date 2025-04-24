<?php
// Enhanced controller for your home page with role-specific content

class HomeController {
    private $db;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get database connection
        $this->db = get_db();
    }
    
    public function index() {
        // Determine user role
        $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
        $userRole = $isLoggedIn ? $_SESSION['role'] : 'guest';
        $userId = $isLoggedIn ? $_SESSION['user_id'] : null;
        
        // Default data for all users
        $featuredServices = [
            [
                'service_id' => 1,
                'name' => 'Regular Checkup',
                'description' => 'Comprehensive health evaluation with our experienced physicians.',
                'duration' => 30,
                'icon' => 'stethoscope'
            ],
            [
                'service_id' => 2,
                'name' => 'Therapy Session',
                'description' => 'One-on-one counseling sessions with our licensed therapists.',
                'duration' => 60,
                'icon' => 'brain'
            ],
            [
                'service_id' => 3,
                'name' => 'Cardiac Evaluation',
                'description' => 'Complete cardiovascular assessment with our heart specialists.',
                'duration' => 45,
                'icon' => 'heartbeat'
            ]
        ];
        
        $featuredProviders = [
            [
                'user_id' => 1,
                'first_name' => 'Dr. Smith',
                'last_name' => 'MD',
                'specialization' => 'Family Medicine',
                'bio' => 'With over 15 years of experience in family medicine, Dr. Smith provides comprehensive care for patients of all ages.'
            ],
            [
                'user_id' => 2,
                'first_name' => 'Dr. Johnson',
                'last_name' => '',
                'specialization' => 'Cardiology',
                'bio' => 'Dr. Johnson is a board-certified cardiologist specializing in preventive cardiology and heart health management.'
            ],
            [
                'user_id' => 3,
                'first_name' => 'Dr. Williams',
                'last_name' => '',
                'specialization' => 'Mental Health',
                'bio' => 'As a licensed therapist, Dr. Williams offers compassionate mental health services and counseling.'
            ]
        ];
        
        $testimonials = [
            [
                'name' => 'Jennifer L.',
                'text' => 'The online scheduling system made booking my appointments so easy. No more waiting on hold for 20 minutes!',
                'date' => '2023-08-15',
                'patient_since' => '2023'
            ],
            [
                'name' => 'Michael T.',
                'text' => 'I love the appointment reminders! I haven\'t missed a single appointment since signing up with this clinic.',
                'date' => '2024-01-10',
                'patient_since' => '2024'
            ],
            [
                'name' => 'Sarah K.',
                'text' => 'Being able to see all available slots made finding an appointment that works with my busy schedule so much easier.',
                'date' => '2022-11-05',
                'patient_since' => '2022'
            ]
        ];
        
        // User-specific data
        $upcomingAppointments = [];
        $availabilityData = [];
        $dashboardStats = [];
        
        // Fetch user-specific data based on role
        if ($isLoggedIn) {
            if ($userRole === 'patient') {
                // Get upcoming appointments for patient
                $upcomingAppointments = $this->getPatientAppointments($userId);
            } elseif ($userRole === 'provider') {
                // Get today's schedule and availability for provider
                $upcomingAppointments = $this->getProviderAppointments($userId);
                $availabilityData = $this->getProviderAvailability($userId);
            } elseif ($userRole === 'admin') {
                // Get system-wide stats for admin
                $dashboardStats = $this->getAdminStats();
            }
        }
        
        // Include the view file with the data
        include VIEW_PATH . '/home/index.php';
    }
    
    /**
     * Get upcoming appointments for a patient
     */
    private function getPatientAppointments($patientId) {
        $appointments = [];
        
        try {
            // Sample query - adjust based on your database schema
            $query = "
                SELECT a.*, 
                       u.first_name as provider_first_name, 
                       u.last_name as provider_last_name,
                       s.name as service_name
                FROM appointments a
                JOIN users u ON a.provider_id = u.user_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.patient_id = ? 
                AND a.appointment_date >= CURDATE()
                AND a.status != 'canceled'
                ORDER BY a.appointment_date, a.start_time
                LIMIT 3
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $appointments[] = $row;
                }
            }
        } catch (Exception $e) {
            error_log("Error fetching patient appointments: " . $e->getMessage());
        }
        
        // If no appointments found, return empty array
        return $appointments;
    }
    
    /**
     * Get upcoming appointments for a provider
     */
    private function getProviderAppointments($providerId) {
        $appointments = [];
        
        try {
            // Sample query - adjust based on your database schema
            $query = "
                SELECT a.*, 
                       u.first_name as patient_first_name, 
                       u.last_name as patient_last_name,
                       s.name as service_name
                FROM appointments a
                JOIN users u ON a.patient_id = u.user_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.provider_id = ? 
                AND a.appointment_date = CURDATE()
                AND a.status != 'canceled'
                ORDER BY a.start_time
                LIMIT 5
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $providerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $appointments[] = $row;
                }
            }
        } catch (Exception $e) {
            error_log("Error fetching provider appointments: " . $e->getMessage());
        }
        
        // If no appointments found, return empty array
        return $appointments;
    }
    
    /**
     * Get availability data for a provider
     */
    private function getProviderAvailability($providerId) {
        $availability = [];
        
        try {
            // Sample query - adjust based on your database schema
            $query = "
                SELECT *
                FROM provider_availability
                WHERE provider_id = ? 
                AND available_date >= CURDATE()
                AND is_available = 1
                ORDER BY available_date, start_time
                LIMIT 5
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $providerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $availability[] = $row;
                }
            }
        } catch (Exception $e) {
            error_log("Error fetching provider availability: " . $e->getMessage());
        }
        
        // If no availability found, return empty array
        return $availability;
    }
    
    /**
     * Get admin dashboard stats
     */
    private function getAdminStats() {
        $stats = [
            'total_appointments' => 0,
            'appointments_today' => 0,
            'active_patients' => 0,
            'active_providers' => 0
        ];
        
        try {
            // Get total appointments
            $result = $this->db->query("SELECT COUNT(*) as count FROM appointments");
            if ($result && $row = $result->fetch_assoc()) {
                $stats['total_appointments'] = $row['count'];
            }
            
            // Get appointments today
            $today = date('Y-m-d');
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ?");
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $stats['appointments_today'] = $row['count'];
            }
            
            // Get active patients
            $result = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'patient' AND is_active = 1");
            if ($result && $row = $result->fetch_assoc()) {
                $stats['active_patients'] = $row['count'];
            }
            
            // Get active providers
            $result = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'provider' AND is_active = 1");
            if ($result && $row = $result->fetch_assoc()) {
                $stats['active_providers'] = $row['count'];
            }
        } catch (Exception $e) {
            error_log("Error fetching admin stats: " . $e->getMessage());
        }
        
        return $stats;
    }
}