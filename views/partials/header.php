<?php
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session validation
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && isset($_SESSION['login_timestamp'])) {
    $session_login_time = $_SESSION['login_timestamp'];
    
    require_once APP_ROOT . '/models/User.php';
    $userModel = new User(get_db());
    
    $latest_login = $userModel->getLastLoginTime($_SESSION['user_id']);
    
    if ($latest_login && $session_login_time != strtotime($latest_login)) {
        $_SESSION = array();
        session_destroy();
        
        if (strpos($_SERVER['REQUEST_URI'], 'index.php/auth') === false) {
            set_flash_message('error', 'Your session has expired. Please log in again.', 'auth_login');
            header('Location: ' . base_url('index.php/auth'));
            exit;
        }
    }
}

$isLoggedIn = isset($_SESSION['user_id']) && ($_SESSION['logged_in'] ?? false) === true;
$userRole = $isLoggedIn ? ($_SESSION['role'] ?? 'guest') : 'guest';
$userName = $isLoggedIn ? ($_SESSION['name'] ?? $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?? $_SESSION['email'] ?? 'User') : '';
$current_url = $_SERVER['REQUEST_URI'] ?? '';
$pageTitle = $pageTitle ?? 'Appointment System';

if (strpos($current_url, 'dashboard') !== false || strpos($current_url, $userRole) !== false) {
    if ($userRole === 'admin') {
        $pageTitle = 'Admin Dashboard - Appointment System';
    } elseif ($userRole === 'provider') {
        $pageTitle = 'Provider Dashboard - Appointment System';
    } elseif ($userRole === 'patient') {
        $pageTitle = 'Patient Dashboard - Appointment System';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generate_csrf_token() ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <?php if ($userRole === 'provider'): ?>
    <!-- FullCalendar for provider role -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <?php endif; ?>
    
    <!-- Custom CSS -->
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
    
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-dashboard, .provider-dashboard, .patient-dashboard {
            margin-top: 20px;
        }
        
        .card {
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
            transition: none !important;
        }
        
        .dashboard-cards .card-body .d-grid {
            margin-top: auto;
            margin-bottom: 15px;
        }
        .dashboard-cards .card-body {
            display: flex;
            flex-direction: column;
            height: calc(100% - 1px);
        }
        
        .dashboard-cards .card {
            position: relative;
            padding-bottom: 50px;
            min-height: 250px;
        }
        .dashboard-cards .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }
        .card:hover {
            transform: none !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        }
        
        .bg-role-admin { background-color: #dc3545; }
        .bg-role-provider { background-color: #28a745; }
        .bg-role-patient { background-color: #0d6efd; }
        
        .calendar-container {
            padding: 1.5rem;
            min-height: 650px;
        }
        
        .header-container {
            max-width: 1320px; 
            width: 95%;
            margin: 0 auto;
        }
        
        /* Responsive title handling */
        @media (max-width: 767.98px) {
            .page-title-desktop {
                display: none;
            }
        }
        
        @media (min-width: 768px) {
            .page-title-mobile {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header-container">
        <header class="mb-4">
            <div class="page-title-desktop">
                <?php if ($userRole === 'admin'): ?>
                    <h1 class="display-5">Appointment System Administration</h1>
                <?php elseif ($userRole === 'provider'): ?>
                    <h1 class="display-5">Provider Dashboard</h1>
                <?php elseif ($userRole === 'patient'): ?>
                               <h1 class="display-5">Patient Dashboard</h1>
                <?php else: ?>
                    <h1 class="display-5">Appointment System</h1>
                <?php endif; ?>
            </div>
            
            <?php include_once APP_ROOT . '/views/partials/navigation.php'; ?>
        </header>
    </div>
    
    <div class="container mt-3">
        <?php 
        // Get the current page context based on the URL
        $currentUri = $_SERVER['REQUEST_URI'];
        $contextMap = [
            // Admin routes
            'admin/appointments' => 'admin_appointments',
            'admin/appointments/edit' => 'admin_appointment_edit',
            'admin/providers' => 'admin_providers',
            'admin/services' => 'admin_services',
            'admin/users' => 'admin_users',
            'admin/user_view' => 'admin_user_view',
            'admin/user_edit' => 'admin_user_edit',
            'admin/provider_services' => 'admin_provider_services',
            'admin/provider_avaliability' => 'admin_provider_availability',
            
            // Provider routes
            'provider/profile' => 'provider_profile',
            'provider/appointments' => 'provider_appointments',
            'provider/schedule' => 'provider_schedule',
            'provider/services' => 'provider_services',
            'provider/view_appointment' => 'provider_view_appointment',
            'provider/updateProfile' => 'provider_update_profile',
            'provider/notifications' => 'provider_notifications',
            
            // Patient routes
            'patient/book' => 'patient_book',
            'patient/profile' => 'patient_profile',
            'patient/notifications' => 'patient_notifications',
            'patient/view_provider' => 'patient_view_provider',
            'patient/search' => 'patient_search',
            
            // Auth routes
            'auth' => 'auth_login',
            'auth/register' => 'auth_register',
            'auth/forgot_password' => 'auth_forgot_password',
            'auth/reset_password' => 'auth_reset_password',
            'auth/verify' => 'auth_verify',
            'auth/change_password' => 'auth_change_password',
            
            // Appointment routes
            'appointments/create' => 'appointments_create',
            'appointments/view' => 'appointments_view',
            'appointments/reschedule' => 'appointments_reschedule',
            'appointments/history' => 'appointments_history',
            
            // Home
            'home' => 'home'
        ];
        
        // Determine the context from the URL
        $messageContext = 'global';
        foreach ($contextMap as $urlPart => $context) {
            if (strpos($currentUri, $urlPart) !== false) {
                $messageContext = $context;
                break;
            }
        }
        
        // Display messages
        $contextMessages = get_flash_messages($messageContext);
        $globalMessages = get_flash_messages('global');
        $allMessages = array_merge($contextMessages, $globalMessages);
        
        foreach ($allMessages as $flashMessage): 
        ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="container">
        <?php display_flash_messages(); ?>
        