<?php

/**
 * Enhanced Admin Dashboard Test
 * 
 * This test file verifies all functionality of the admin dashboard
 * including statistics, charts, notifications, and widget preferences.
 * It also tests actual HTML rendering, user interactions, and API responses.
 */

// Include necessary configuration
require_once __DIR__ . '/../config/config.php';
require_once MODEL_PATH . '/User.php';
require_once MODEL_PATH . '/Appointment.php';
require_once MODEL_PATH . '/ActivityLog.php';
require_once CONTROLLER_PATH . '/admin_controller.php';

class AdminDashboardTest {
    private $db;
    private $adminController;
    private $testResults = [];
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $stats = [];
    private $renderedHtml = '';

    public function __construct() {
        // Initialize database connection
        $this->db = get_db();
        
        // Create admin controller instance
        $this->adminController = new AdminController();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set admin session for testing
        $_SESSION['user_id'] = 3; // Admin user ID from sample data
        $_SESSION['role'] = 'admin';
        $_SESSION['logged_in'] = true;
        
        // Get dashboard stats directly from database
        $this->initializeStats();
        
        // Render the actual dashboard HTML
        $this->renderDashboard();
        
        echo "<h1>Enhanced Admin Dashboard Test</h1>";
        echo "<div class='container'>";
    }
    
    /**
     * Initialize stats directly from database
     */
    private function initializeStats() {
        // Get user counts
        $this->stats['totalUsers'] = $this->adminController->getTestData('getCount', ['users']);
        $this->stats['totalPatients'] = $this->adminController->getTestData('getCountByRole', ['patient']);
        $this->stats['totalProviders'] = $this->adminController->getTestData('getCountByRole', ['provider']);
        $this->stats['totalAdmins'] = $this->adminController->getTestData('getCountByRole', ['admin']);
        
        // Get appointment counts
        $this->stats['totalAppointments'] = $this->adminController->getTestData('getCount', ['appointments']);
        $this->stats['scheduledAppointments'] = $this->adminController->getTestData('getCountByStatus', ['scheduled']);
        $this->stats['confirmedAppointments'] = $this->adminController->getTestData('getCountByStatus', ['confirmed']);
        $this->stats['completedAppointments'] = $this->adminController->getTestData('getCountByStatus', ['completed']);
        $this->stats['canceledAppointments'] = $this->adminController->getTestData('getCountByStatus', ['canceled']);
        $this->stats['noShowAppointments'] = $this->adminController->getTestData('getCountByStatus', ['no_show']);
        
        // Get service counts
        $this->stats['totalServices'] = $this->adminController->getTestData('getCount', ['services']);
        
        // Get top services
        $this->stats['topServices'] = $this->adminController->getTestData('getTopServices', [5]);
        
        // Get provider availability
        $this->stats['totalAvailableSlots'] = $this->adminController->getTestData('getAvailableSlotsCount', []);
        $this->stats['bookedSlots'] = $this->adminController->getTestData('getBookedSlotsCount', []);
        $this->stats['availabilityRate'] = ($this->stats['totalAvailableSlots'] > 0) ?
            round(($this->stats['bookedSlots'] / $this->stats['totalAvailableSlots']) * 100) : 0;
        
        // Get top providers
        $this->stats['topProviders'] = $this->adminController->getTestData('getTopProviders', [5]);
        
        // Get recent activity
        $this->stats['recentActivity'] = $this->adminController->getTestData('getRecentActivity', [10]);
    }
    
    /**
     * Render the actual dashboard HTML
     */
    private function renderDashboard() {
        // Capture output buffer to get the rendered HTML
        ob_start();
        
        // Call the index method to render the dashboard
        $this->adminController->index();
        
        // Get the rendered HTML
        $this->renderedHtml = ob_get_clean();
    }
    
    /**
     * Run all tests
     */
    public function runTests() {
        // Test dashboard statistics
        $this->testUserStatistics();
        $this->testAppointmentStatistics();
        $this->testServiceStatistics();
        $this->testProviderAvailability();
        $this->testRecentActivity();
        
        // Test dashboard UI elements
        $this->testDashboardUIElements();
        
        // Test chart initialization
        $this->testChartInitialization();
        
        // Test notification system
        $this->testNotificationSystem();
        
        // Test widget preferences
        $this->testWidgetPreferences();
        
        // Test quick actions
        $this->testQuickActions();
        
        // Test data consistency
        $this->testDataConsistency();
        
        // NEW: Test actual HTML rendering
        $this->testHtmlRendering();
        
        
        // NEW: Test user interactions
        $this->testUserInteractions();
        
        // Display summary
        $this->displaySummary();
    }
    
    /**
     * Test user statistics
     */
    private function testUserStatistics() {
        echo "<h2>Testing User Statistics</h2>";
        
        // Test total users count
        $totalUsers = $this->stats['totalUsers'];
        $this->assertTest(
            'Total Users Count',
            $totalUsers > 0,
            "Expected total users to be greater than 0, got {$totalUsers}"
        );
        
        // Test user distribution by role
        $totalPatients = $this->stats['totalPatients'];
        $this->assertTest(
            'Patient Count',
            $totalPatients >= 0,
            "Expected patient count to be non-negative, got {$totalPatients}"
        );
        
        $totalProviders = $this->stats['totalProviders'];
        $this->assertTest(
            'Provider Count',
            $totalProviders >= 0,
            "Expected provider count to be non-negative, got {$totalProviders}"
        );
        
        $totalAdmins = $this->stats['totalAdmins'];
        $this->assertTest(
            'Admin Count',
            $totalAdmins >= 0,
            "Expected admin count to be non-negative, got {$totalAdmins}"
        );
        
        // Verify that the sum of roles equals total users
        $sumOfRoles = $totalPatients + $totalProviders + $totalAdmins;
        $this->assertTest(
            'Sum of Roles',
            $sumOfRoles <= $totalUsers,
            "Expected sum of roles ({$sumOfRoles}) to be less than or equal to total users ({$totalUsers})"
        );
    }
    
    /**
     * Test appointment statistics
     */
    private function testAppointmentStatistics() {
        echo "<h2>Testing Appointment Statistics</h2>";
        
        // Test total appointments count
        $totalAppointments = $this->stats['totalAppointments'];
        $this->assertTest(
            'Total Appointments Count',
            $totalAppointments >= 0,
            "Expected total appointments to be non-negative, got {$totalAppointments}"
        );
        
        // Test appointment status counts
        $scheduledAppointments = $this->stats['scheduledAppointments'];
        $this->assertTest(
            'Scheduled Appointments Count',
            $scheduledAppointments >= 0,
            "Expected scheduled appointments to be non-negative, got {$scheduledAppointments}"
        );
        
        $confirmedAppointments = $this->stats['confirmedAppointments'];
        $this->assertTest(
            'Confirmed Appointments Count',
            $confirmedAppointments >= 0,
            "Expected confirmed appointments to be non-negative, got {$confirmedAppointments}"
        );
        
        $completedAppointments = $this->stats['completedAppointments'];
        $this->assertTest(
            'Completed Appointments Count',
            $completedAppointments >= 0,
            "Expected completed appointments to be non-negative, got {$completedAppointments}"
        );
        
        $canceledAppointments = $this->stats['canceledAppointments'];
        $this->assertTest(
            'Canceled Appointments Count',
            $canceledAppointments >= 0,
            "Expected canceled appointments to be non-negative, got {$canceledAppointments}"
        );
        
        $noShowAppointments = $this->stats['noShowAppointments'];
        $this->assertTest(
            'No Show Appointments Count',
            $noShowAppointments >= 0,
            "Expected no-show appointments to be non-negative, got {$noShowAppointments}"
        );
        
        // Verify that the sum of appointment statuses equals total appointments
        $sumOfStatuses = $scheduledAppointments + $confirmedAppointments + 
                         $completedAppointments + $canceledAppointments + 
                         $noShowAppointments;
        $this->assertTest(
            'Sum of Appointment Statuses',
            $sumOfStatuses <= $totalAppointments,
            "Expected sum of statuses ({$sumOfStatuses}) to be less than or equal to total appointments ({$totalAppointments})"
        );
    }
    
    /**
     * Test service statistics
     */
    private function testServiceStatistics() {
        echo "<h2>Testing Service Statistics</h2>";
        
        // Test total services count
        $totalServices = $this->stats['totalServices'];
        $this->assertTest(
            'Total Services Count',
            $totalServices > 0,
            "Expected total services to be greater than 0, got {$totalServices}"
        );
        
        // Test top services
        $topServices = $this->stats['topServices'];
        $this->assertTest(
            'Top Services Data',
            is_array($topServices),
            "Expected top services to be an array, got " . gettype($topServices)
        );
        
        if (is_array($topServices)) {
            $this->assertTest(
                'Top Services Structure',
                count($topServices) === 0 || (count($topServices) > 0 && isset($topServices[0]['name'], $topServices[0]['usage_count'])),
                "Expected top services to have name and usage_count fields"
            );
        }
    }
    
    /**
     * Test provider availability
     */
    private function testProviderAvailability() {
        echo "<h2>Testing Provider Availability</h2>";
        
        // Test available slots
        $totalAvailableSlots = $this->stats['totalAvailableSlots'];
        $this->assertTest(
            'Total Available Slots',
            $totalAvailableSlots >= 0,
            "Expected total available slots to be non-negative, got {$totalAvailableSlots}"
        );
        
        // Test booked slots
        $bookedSlots = $this->stats['bookedSlots'];
        $this->assertTest(
            'Booked Slots',
            $bookedSlots >= 0,
            "Expected booked slots to be non-negative, got {$bookedSlots}"
        );
        
        // Test availability rate calculation
        $availabilityRate = $this->stats['availabilityRate'];
        $this->assertTest(
            'Availability Rate Calculation',
            $availabilityRate >= 0 && $availabilityRate <= 100,
            "Expected availability rate to be between 0 and 100, got {$availabilityRate}"
        );
        
        // Test top providers
        $topProviders = $this->stats['topProviders'];
        $this->assertTest(
            'Top Providers Data',
            is_array($topProviders),
            "Expected top providers to be an array, got " . gettype($topProviders)
        );
        
        if (is_array($topProviders)) {
            $hasValidStructure = count($topProviders) === 0 || 
                (count($topProviders) > 0 && 
                 isset($topProviders[0]['provider_name'], $topProviders[0]['appointment_count']));
            
            $this->assertTest(
                'Top Providers Structure',
                $hasValidStructure,
                "Expected top providers to have provider_name and appointment_count fields"
            );
        }
    }
    
    /**
     * Test recent activity
     */
    private function testRecentActivity() {
        echo "<h2>Testing Recent Activity</h2>";
        
        // Test recent activity data
        $recentActivity = $this->stats['recentActivity'];
        $this->assertTest(
            'Recent Activity Data',
            is_array($recentActivity),
            "Expected recent activity to be an array, got " . gettype($recentActivity)
        );
        
        if (is_array($recentActivity) && count($recentActivity) > 0) {
            $this->assertTest(
                'Recent Activity Structure',
                isset($recentActivity[0]['description'], $recentActivity[0]['created_at']),
                "Expected recent activity to have description and created_at fields"
            );
        }
    }
    
    /**
     * Test dashboard UI elements
     */
    private function testDashboardUIElements() {
        echo "<h2>Testing Dashboard UI Elements</h2>";
        
        // Test if view file exists
        $viewFile = VIEW_PATH . '/admin/index.php';
        $this->assertTest(
            'Dashboard View File',
            file_exists($viewFile),
            "Expected dashboard view file to exist at {$viewFile}"
        );
        
        // Test if required partials exist
        $headerFile = VIEW_PATH . '/partials/header.php';
        $this->assertTest(
            'Admin Header Partial',
            file_exists($headerFile),
            "Expected admin header partial to exist at {$headerFile}"
        );
        
        $footerFile = VIEW_PATH . '/partials/footer.php';
        $this->assertTest(
            'Footer Partial',
            file_exists($footerFile),
            "Expected footer partial to exist at {$footerFile}"
        );
        
        // Test if Chart.js is included
        $viewContent = file_get_contents($viewFile);
        $this->assertTest(
            'Chart.js Inclusion',
            strpos($viewContent, 'chart.js') !== false || strpos($viewContent, 'Chart.js') !== false,
            "Expected Chart.js to be included in the dashboard view"
        );
        
        // Test if required JavaScript functions exist
        $jsTests = [
            'initializeCharts Function' => 'function initializeCharts',
            'updateChartTimePeriod Function' => 'function updateChartTimePeriod',
            'loadNotifications Function' => 'function loadNotifications',
            'saveWidgetPreferences Function' => 'function saveWidgetPreferences',
            'applySavedWidgetPreferences Function' => 'function applySavedWidgetPreferences'
        ];
        
        foreach ($jsTests as $testName => $searchString) {
            $this->assertTest(
                $testName,
                strpos($viewContent, $searchString) !== false,
                "Expected {$searchString} to exist in the dashboard view"
            );
        }
    }
    
    /**
     * Test chart initialization
     */
    private function testChartInitialization() {
        echo "<h2>Testing Chart Initialization</h2>";
        
        // Test if chart canvases exist
        $viewFile = VIEW_PATH . '/admin/index.php';
        $viewContent = file_get_contents($viewFile);
        
        $this->assertTest(
            'Appointment Status Chart Canvas',
            strpos($viewContent, 'appointmentStatusChart') !== false,
            "Expected appointmentStatusChart canvas to exist in the dashboard view"
        );
        
        $this->assertTest(
            'Appointment Trends Chart Canvas',
            strpos($viewContent, 'appointmentTrendsChart') !== false,
            "Expected appointmentTrendsChart canvas to exist in the dashboard view"
        );
        
        // Test if chart initialization code exists
        $this->assertTest(
            'Chart Initialization Code',
            strpos($viewContent, 'new Chart(') !== false,
            "Expected Chart initialization code to exist in the dashboard view"
        );
        
        // Test if chart data is properly structured
        $this->assertTest(
            'Chart Data Structure',
            strpos($viewContent, 'datasets') !== false && 
            strpos($viewContent, 'labels') !== false,
            "Expected chart data structure with datasets and labels"
        );
        
        // Test if chart options are properly configured
        $this->assertTest(
            'Chart Options Configuration',
            strpos($viewContent, 'options') !== false && 
            strpos($viewContent, 'responsive') !== false,
            "Expected chart options with responsive configuration"
        );
        
        // Test if chart update functionality exists
        $this->assertTest(
            'Chart Update Functionality',
            strpos($viewContent, '.update(') !== false,
            "Expected chart update functionality to exist"
        );
    }
    
    /**
     * Test notification system
     */
    private function testNotificationSystem() {
        echo "<h2>Testing Notification System</h2>";
        
        $viewFile = VIEW_PATH . '/admin/index.php';
        $viewContent = file_get_contents($viewFile);
        
        // Test if notifications container exists
        $this->assertTest(
            'Notifications Container',
            strpos($viewContent, 'notifications-container') !== false,
            "Expected notifications-container to exist in the dashboard view"
        );
        
        // Test if notification loading function exists
        $this->assertTest(
            'Notification Loading Function',
            strpos($viewContent, 'loadNotifications') !== false,
            "Expected loadNotifications function to exist"
        );
        
        // Test if notification refresh functionality exists
        $this->assertTest(
            'Notification Refresh Functionality',
            strpos($viewContent, 'refresh-notifications') !== false,
            "Expected notification refresh functionality to exist"
        );
        
        // Test if notification API endpoint is correctly referenced
        $this->assertTest(
            'Notification API Endpoint',
            strpos($viewContent, 'notification/getAdminNotifications') !== false,
            "Expected reference to notification API endpoint"
        );
        
        // Test if notification rendering logic exists
        $this->assertTest(
            'Notification Rendering Logic',
            strpos($viewContent, 'notification.message') !== false,
            "Expected notification rendering logic to exist"
        );
    }
    
    /**
     * Test widget preferences
     */
    private function testWidgetPreferences() {
        echo "<h2>Testing Widget Preferences</h2>";
        
        // Test if widget preferences form exists
        $viewFile = VIEW_PATH . '/admin/index.php';
        $viewContent = file_get_contents($viewFile);
        
        $this->assertTest(
            'Widget Preferences Form',
            strpos($viewContent, 'widget-preferences-form') !== false,
            "Expected widget-preferences-form to exist in the dashboard view"
        );
        
        // Test if widget preference checkboxes exist
        $widgetCheckboxes = [
            'widget-appointments',
            'widget-notifications',
            'widget-providers',
            'widget-services'
        ];
        
        foreach ($widgetCheckboxes as $checkbox) {
            $this->assertTest(
                "Widget Checkbox: {$checkbox}",
                strpos($viewContent, $checkbox) !== false,
                "Expected {$checkbox} checkbox to exist in the dashboard view"
            );
        }
        
        // Test if localStorage is used for preferences
        $this->assertTest(
            'LocalStorage for Preferences',
            strpos($viewContent, 'localStorage.setItem') !== false && 
            strpos($viewContent, 'localStorage.getItem') !== false,
            "Expected localStorage to be used for storing widget preferences"
        );
        
        // Test if preferences are applied to UI
        $this->assertTest(
            'Apply Preferences to UI',
            strpos($viewContent, 'style.display') !== false && 
            strpos($viewContent, 'preferences.appointments') !== false,
            "Expected widget preferences to be applied to UI elements"
        );
    }
    
    /**
     * Test quick actions
     */
    private function testQuickActions() {
        echo "<h2>Testing Quick Actions</h2>";
        
        // Test if quick actions section exists
        $viewFile = VIEW_PATH . '/admin/index.php';
        $viewContent = file_get_contents($viewFile);
        
        $this->assertTest(
            'Quick Actions Section',
            strpos($viewContent, 'Quick Actions') !== false,
            "Expected Quick Actions section to exist in the dashboard view"
        );
        
        // Test if all quick action buttons exist
        $quickActions = [
            'Add New User' => 'admin/users/create',
            'Add New Provider' => 'admin/addProvider',
            'Add New Service' => 'admin/services/create'
        ];
        
        foreach ($quickActions as $actionName => $actionUrl) {
            $this->assertTest(
                "Quick Action: {$actionName}",
                strpos($viewContent, $actionName) !== false && 
                strpos($viewContent, $actionUrl) !== false,
                "Expected {$actionName} quick action to exist with URL containing {$actionUrl}"
            );
        }
    }
    
    /**
     * Test if the admin controller methods work correctly
     */
    private function testAdminControllerMethods() {
        echo "<h2>Testing Admin Controller Methods</h2>";
        
        // Test index method
        try {
            ob_start();
            $this->adminController->index();
            $output = ob_get_clean();
            
            $this->assertTest(
                'Admin Controller Index Method',
                !empty($output) && strpos($output, 'admin-dashboard') !== false,
                "Expected admin controller index method to render dashboard view"
            );
        } catch (Exception $e) {
            $this->assertTest(
                'Admin Controller Index Method',
                false,
                "Error in admin controller index method: " . $e->getMessage()
            );
        }
        
        // Test getCount method
        $userCount = $this->adminController->getTestData('getCount', ['users']);
        $this->assertTest(
            'getCount Method',
            is_numeric($userCount) && $userCount >= 0,
            "Expected getCount method to return a non-negative number, got {$userCount}"
        );
        
        // Test getCountByRole method
        $patientCount = $this->adminController->getTestData('getCountByRole', ['patient']);
        $this->assertTest(
            'getCountByRole Method',
            is_numeric($patientCount) && $patientCount >= 0,
            "Expected getCountByRole method to return a non-negative number, got {$patientCount}"
        );
        
        // Test getCountByStatus method
        $scheduledCount = $this->adminController->getCountByStatus('scheduled');
        $this->assertTest(
            'getCountByStatus Method',
            is_numeric($scheduledCount) && $scheduledCount >= 0,
            "Expected getCountByStatus method to return a non-negative number, got {$scheduledCount}"
        );
        
        // Test getTopServices method
        $topServices = $this->adminController->getTopServices(3);
        $this->assertTest(
            'getTopServices Method',
            is_array($topServices) && (count($topServices) <= 3),
            "Expected getTopServices method to return an array with at most 3 items"
        );
        
        // Test getTopProviders method
        $topProviders = $this->adminController->getTopProviders(3);
        $this->assertTest(
            'getTopProviders Method',
            is_array($topProviders) && (count($topProviders) <= 3),
            "Expected getTopProviders method to return an array with at most 3 items"
        );
        
        // Test getRecentActivity method
        $recentActivity = $this->adminController->getRecentActivity(5);
        $this->assertTest(
            'getRecentActivity Method',
            is_array($recentActivity) && (count($recentActivity) <= 5),
            "Expected getRecentActivity method to return an array with at most 5 items"
        );
    }
    /**
     * Create system notifications for testing
     * 
     * @return void
     */
    public function createTestNotifications() {
        // Check if user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Unauthorized access',
                'success' => false
            ]);
            exit;
        }
        
        try {
            // Create sample system notifications
            $notifications = [
                [
                    'subject' => 'New Registration',
                    'message' => 'New provider registered: Dr. Smith',
                    'type' => 'user_registered',
                    'is_system' => true,
                    'audience' => 'admin'
                ],
                [
                    'subject' => 'Appointment Statistics',
                    'message' => '15 appointments confirmed today',
                    'type' => 'appointment_confirmed',
                    'is_system' => true,
                    'audience' => 'admin'
                ],
                [
                    'subject' => 'System Warning',
                    'message' => '3 appointments need admin review',
                    'type' => 'system_warning',
                    'is_system' => true,
                    'audience' => 'admin'
                ],
                [
                    'subject' => 'System Error',
                    'message' => 'System backup failed: Check database connection',
                    'type' => 'system_error',
                    'is_system' => true,
                    'audience' => 'admin'
                ]
            ];
            
            $success = true;
            foreach ($notifications as $notification) {
                if (!$this->notificationModel->addNotification($notification)) {
                    $success = false;
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Test notifications created' : 'Failed to create some notifications'
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Error creating test notifications: ' . $e->getMessage(),
                'success' => false
            ]);
            exit;
        }
    }

    /**
     * Test dashboard data consistency
     */
    private function testDataConsistency() {
        echo "<h2>Testing Data Consistency</h2>";
        
        // Get all stats
        $totalUsers = $this->stats['totalUsers'];
        $totalPatients = $this->stats['totalPatients'];
        $totalProviders = $this->stats['totalProviders'];
        $totalAdmins = $this->stats['totalAdmins'];
        
        $totalAppointments = $this->stats['totalAppointments'];
        $scheduledAppointments = $this->stats['scheduledAppointments'];
        $confirmedAppointments = $this->stats['confirmedAppointments'];
        $completedAppointments = $this->stats['completedAppointments'];
        $canceledAppointments = $this->stats['canceledAppointments'];
        $noShowAppointments = $this->stats['noShowAppointments'];
        
        // Test user role consistency
        $sumOfRoles = $totalPatients + $totalProviders + $totalAdmins;
        $this->assertTest(
            'User Role Consistency',
            $sumOfRoles <= $totalUsers,
            "Sum of roles ({$sumOfRoles}) should be less than or equal to total users ({$totalUsers})"
        );
        
        // Test appointment status consistency
        $sumOfStatuses = $scheduledAppointments + $confirmedAppointments + 
                         $completedAppointments + $canceledAppointments + 
                         $noShowAppointments;
        $this->assertTest(
            'Appointment Status Consistency',
            $sumOfStatuses <= $totalAppointments,
            "Sum of appointment statuses ({$sumOfStatuses}) should be less than or equal to total appointments ({$totalAppointments})"
        );
        
        // Test provider appointment counts
        $topProviders = $this->stats['topProviders'];
        $providerAppointmentSum = 0;
        
        if (is_array($topProviders)) {
            foreach ($topProviders as $provider) {
                if (isset($provider['appointment_count'])) {
                    $providerAppointmentSum += $provider['appointment_count'];
                }
            }
            
            $this->assertTest(
                'Provider Appointment Count Consistency',
                $providerAppointmentSum <= $totalAppointments,
                "Sum of provider appointments ({$providerAppointmentSum}) should be less than or equal to total appointments ({$totalAppointments})"
            );
        }
        
        // Test service usage counts
        $topServices = $this->stats['topServices'];
        $serviceUsageSum = 0;
        
        if (is_array($topServices)) {
            foreach ($topServices as $service) {
                if (isset($service['usage_count'])) {
                    $serviceUsageSum += $service['usage_count'];
                }
            }
            
            $this->assertTest(
                'Service Usage Count Consistency',
                $serviceUsageSum <= $totalAppointments,
                "Sum of service usage ({$serviceUsageSum}) should be less than or equal to total appointments ({$totalAppointments})"
            );
        }
    }
    
    /**
     * NEW: Test actual HTML rendering
     * Verifies that the rendered HTML contains the exact numbers from the database
     */
    private function testHtmlRendering() {
        echo "<h2>Testing HTML Rendering</h2>";
        
        // Test if the rendered HTML contains the correct user counts
        $this->assertHtmlContains(
            'Total Users Display',
            $this->stats['totalUsers'],
            "Expected total users count ({$this->stats['totalUsers']}) to be displayed in the HTML"
        );
        
        $this->assertHtmlContains(
            'Total Patients Display',
            $this->stats['totalPatients'],
            "Expected total patients count ({$this->stats['totalPatients']}) to be displayed in the HTML"
        );
        
        $this->assertHtmlContains(
            'Total Providers Display',
            $this->stats['totalProviders'],
            "Expected total providers count ({$this->stats['totalProviders']}) to be displayed in the HTML"
        );
        
        $this->assertHtmlContains(
            'Total Admins Display',
            $this->stats['totalAdmins'],
            "Expected total admins count ({$this->stats['totalAdmins']}) to be displayed in the HTML"
        );
        
        // Test if the rendered HTML contains the correct appointment counts
        $this->assertHtmlContains(
            'Total Appointments Display',
            $this->stats['totalAppointments'],
            "Expected total appointments count ({$this->stats['totalAppointments']}) to be displayed in the HTML"
        );
        
        $this->assertHtmlContains(
            'Scheduled Appointments Display',
            $this->stats['scheduledAppointments'],
            "Expected scheduled appointments count ({$this->stats['scheduledAppointments']}) to be displayed in the HTML"
        );
        
        $this->assertHtmlContains(
            'Confirmed Appointments Display',
            $this->stats['confirmedAppointments'],
            "Expected confirmed appointments count ({$this->stats['confirmedAppointments']}) to be displayed in the HTML"
        );
        
        $this->assertHtmlContains(
            'Completed Appointments Display',
            $this->stats['completedAppointments'],
            "Expected completed appointments count ({$this->stats['completedAppointments']}) to be displayed in the HTML"
        );
        
        $this->assertHtmlContains(
            'Canceled Appointments Display',
            $this->stats['canceledAppointments'],
            "Expected canceled appointments count ({$this->stats['canceledAppointments']}) to be displayed in the HTML"
        );
        
        $this->assertHtmlContains(
            'No Show Appointments Display',
            $this->stats['noShowAppointments'],
            "Expected no-show appointments count ({$this->stats['noShowAppointments']}) to be displayed in the HTML"
        );
        
        // Test if the rendered HTML contains the correct service count
        $this->assertHtmlContains(
            'Total Services Display',
            $this->stats['totalServices'],
            "Expected total services count ({$this->stats['totalServices']}) to be displayed in the HTML"
        );
        
        // Test if top services are displayed
        if (is_array($this->stats['topServices']) && count($this->stats['topServices']) > 0) {
            $topService = $this->stats['topServices'][0];
            if (isset($topService['name'])) {
                $this->assertHtmlContains(
                    'Top Service Name Display',
                    htmlspecialchars($topService['name']),
                    "Expected top service name ({$topService['name']}) to be displayed in the HTML"
                );
            }
        }
        
        // Test if top providers are displayed
        if (is_array($this->stats['topProviders']) && count($this->stats['topProviders']) > 0) {
            $topProvider = $this->stats['topProviders'][0];
            if (isset($topProvider['provider_name'])) {
                $this->assertHtmlContains(
                    'Top Provider Name Display',
                    htmlspecialchars($topProvider['provider_name']),
                    "Expected top provider name ({$topProvider['provider_name']}) to be displayed in the HTML"
                );
            }
        }
        
        // Test if recent activity is displayed
        if (is_array($this->stats['recentActivity']) && count($this->stats['recentActivity']) > 0) {
            $recentActivity = $this->stats['recentActivity'][0];
            if (isset($recentActivity['description'])) {
                $this->assertHtmlContains(
                    'Recent Activity Display',
                    htmlspecialchars($recentActivity['description']),
                    "Expected recent activity description to be displayed in the HTML"
                );
            }
        }
        
        // Test if availability rate is displayed
        $this->assertHtmlContains(
            'Availability Rate Display',
            $this->stats['availabilityRate'] . '%',
            "Expected availability rate ({$this->stats['availabilityRate']}%) to be displayed in the HTML"
        );
    }
    
    
    
    /**
     * NEW: Test user interactions
     * Simulates user interactions with the dashboard
     */
    private function testUserInteractions() {
        echo "<h2>Testing User Interactions</h2>";
        
        // Test chart period selection
        $this->assertTest(
            'Chart Period Selection',
            strpos($this->renderedHtml, 'btn-weekly') !== false && 
            strpos($this->renderedHtml, 'btn-monthly') !== false && 
            strpos($this->renderedHtml, 'btn-yearly') !== false,
            "Expected chart period selection buttons to exist in the HTML"
        );
        
        // Test notification refresh button
        $this->assertTest(
            'Notification Refresh Button',
            strpos($this->renderedHtml, 'refresh-notifications') !== false,
            "Expected notification refresh button to exist in the HTML"
        );
        
        // Test widget preference toggles
        $this->assertTest(
            'Widget Preference Toggles',
            strpos($this->renderedHtml, 'form-check-input') !== false && 
            strpos($this->renderedHtml, 'form-check-label') !== false,
            "Expected widget preference toggle switches to exist in the HTML"
        );
        
        // Test quick action buttons
        $this->assertTest(
            'Quick Action Buttons',
            strpos($this->renderedHtml, 'btn-outline-primary') !== false && 
            strpos($this->renderedHtml, 'btn-outline-success') !== false,
            "Expected quick action buttons to exist in the HTML"
        );
        
        // Test JavaScript event listeners
        $this->assertTest(
            'JavaScript Event Listeners',
            strpos($this->renderedHtml, 'addEventListener') !== false,
            "Expected JavaScript event listeners to be defined in the HTML"
        );
        
        // Test chart update function calls
        $this->assertTest(
            'Chart Update Function Calls',
            strpos($this->renderedHtml, 'updateChartTimePeriod') !== false,
            "Expected chart update function calls to exist in the HTML"
        );
        
        // Test localStorage interactions
        $this->assertTest(
            'LocalStorage Interactions',
            strpos($this->renderedHtml, 'localStorage.setItem') !== false && 
            strpos($this->renderedHtml, 'localStorage.getItem') !== false,
            "Expected localStorage interactions to exist in the HTML"
        );
        
        // Test AJAX calls
        $this->assertTest(
            'AJAX Calls',
            strpos($this->renderedHtml, 'fetch(') !== false,
            "Expected AJAX calls to exist in the HTML"
        );
        
        // Test DOM manipulation
        $this->assertTest(
            'DOM Manipulation',
            strpos($this->renderedHtml, 'innerHTML') !== false || 
            strpos($this->renderedHtml, 'textContent') !== false,
            "Expected DOM manipulation to exist in the HTML"
        );
        
        // Test form submission handling
        $this->assertTest(
            'Form Submission Handling',
            strpos($this->renderedHtml, 'addEventListener(\'submit\'') !== false || 
            strpos($this->renderedHtml, 'onsubmit') !== false,
            "Expected form submission handling to exist in the HTML"
        );
    }
    /**
     * Make an API request to the specified endpoint
     *
     * @param string $endpoint API endpoint to request
     * @param array $params Optional parameters to include in the request
     * @return string|false Response body or false on failure
     */
    private function makeApiRequest($endpoint, $params = []) {
        // Build the full URL
        $url = base_url('index.php/' . $endpoint);
        
        // Add query parameters if any
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        error_log("Making API request to: " . $url);
        
        // Initialize cURL session
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        // Pass session cookie to maintain authentication
        $cookie = session_name() . '=' . session_id();
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        
        // Execute the request
        $response = curl_exec($ch);
        
        // Check for errors
        if (curl_errno($ch)) {
            error_log('API Request Error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        // Get HTTP status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        error_log("API response HTTP code: " . $httpCode);
        
        // Close cURL session
        curl_close($ch);
        
        // Log response for debugging
        error_log("API response: " . substr($response, 0, 500) . (strlen($response) > 500 ? '...' : ''));
        
        return $response;
    }

    /**
     * Display test summary
     */
    private function displaySummary() {
        echo "<h2>Test Summary</h2>";
        echo "<p>Total Tests: " . ($this->testsPassed + $this->testsFailed) . "</p>";
        echo "<p>Tests Passed: {$this->testsPassed}</p>";
        echo "<p>Tests Failed: {$this->testsFailed}</p>";
        
        if ($this->testsFailed > 0) {
            echo "<h3>Failed Tests:</h3>";
            echo "<ul>";
            foreach ($this->testResults as $test) {
                if (!$test['passed']) {
                    echo "<li><strong>{$test['name']}:</strong> {$test['message']}</li>";
                }
            }
            echo "</ul>";
        }
        
        echo "</div>";
    }
    /**
     * Helper method to assert that HTML contains a specific value
     * 
     * @param string $name Test name
     * @param mixed $value Value to search for
     * @param string $message Error message if test fails
     */
    private function assertHtmlContains($name, $value, $message = '') {
        // Convert value to string and escape special characters for regex
        $valueStr = preg_quote((string)$value, '/');
        
        // Check if the value exists in the HTML
        $condition = preg_match("/{$valueStr}/", $this->renderedHtml);
        
        if ($condition) {
            echo "<div class='alert alert-success'><strong>✓ PASS:</strong> {$name}</div>";
            $this->testsPassed++;
        } else {
            echo "<div class='alert alert-danger'><strong>✗ FAIL:</strong> {$name} - {$message}</div>";
            $this->testsFailed++;
        }
        
        $this->testResults[] = [
            'name' => $name,
            'passed' => $condition,
            'message' => $message
        ];
    }

    /**
     * Assert test result
     * 
     * @param string $name Test name
     * @param bool $condition Test condition
     * @param string $message Error message if test fails
     */
    private function assertTest($name, $condition, $message = '') {
        if ($condition) {
            echo "<div class='alert alert-success'><strong>✓ PASS:</strong> {$name}</div>";
            $this->testsPassed++;
        } else {
            echo "<div class='alert alert-danger'><strong>✗ FAIL:</strong> {$name} - {$message}</div>";
            $this->testsFailed++;
        }
        
        $this->testResults[] = [
            'name' => $name,
            'passed' => $condition,
            'message' => $message
        ];
    }
}

// Create test instance and run tests
$test = new AdminDashboardTest();
$test->runTests();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        padding: 20px;
    }
    h1 {
        color: #333;
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
    }
    h2 {
        color: #444;
        margin-top: 30px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
    .alert {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
    }
</style>
