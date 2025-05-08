<?php
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']) && ($_SESSION['logged_in'] ?? false) === true;
$userRole = $isLoggedIn ? ($_SESSION['role'] ?? 'guest') : 'guest';
$userName = $isLoggedIn ? ($_SESSION['name'] ?? $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?? $_SESSION['email'] ?? 'User') : '';

$current_url = $_SERVER['REQUEST_URI'];
$is_home_page = (strpos($current_url, 'index.php/home') !== false || $current_url === '/' || $current_url === '/index.php');

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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <?php if ($userRole === 'provider'): ?>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <?php endif; ?>
    
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
        
        .card:hover {
            transform: none !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        }
        
        .bg-role-admin {
            background-color: #dc3545;
        }
        
        .bg-role-provider {
            background-color: #28a745;
        }
        
        .bg-role-patient {
            background-color: #0d6efd;
        }
        
        .calendar-container {
            padding: 1.5rem;
            min-height: 650px;
        }
        
        .dropdown-menu.show {
            display: block !important;
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            margin-top: 0.125rem !important;
            z-index: 1000 !important;
        }
        
        .header-container {
            max-width: 1320px; 
            width: 95%;
            margin: 0 auto;
        }
        
        .navbar-nav .nav-link {
            padding-right: 0.8rem;
            padding-left: 0.8rem;
            white-space: nowrap;
        }
        
        @media (max-width: 1200px) {
            .navbar-nav .nav-link {
                padding-right: 0.5rem;
                padding-left: 0.5rem;
                font-size: 0.9rem;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 991px) {
            .navbar-collapse {
                padding-top: 1rem;
            }
            
            .navbar-nav .nav-link {
                padding: 0.5rem 0;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php if ($userRole !== 'guest' || !$is_home_page): ?>
    <div class="header-container">
        <header class="mb-4">
            <?php if ($userRole === 'admin'): ?>
                <h1 class="display-5">Appointment System Administration</h1>
            <?php elseif ($userRole === 'provider'): ?>
                <h1 class="display-5">Provider Dashboard</h1>
            <?php elseif ($userRole === 'patient'): ?>
                <h1 class="display-5">Patient Dashboard</h1>
            <?php else: ?>
                <h1 class="display-5">Appointment System</h1>
            <?php endif; ?>
            
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid px-0">
                    <!-- Made the brand a bit simpler -->
                    <a class="navbar-brand d-lg-none" href="<?= base_url('index.php/home') ?>">Menu</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#navbarNav" aria-controls="navbarNav" 
                            aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <!-- Left side navigation items -->
                        <ul class="navbar-nav me-auto">
                            <?php if (!$is_home_page): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/home') ?>">
                                    <i class="fas fa-home"></i> Home
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if ($userRole === 'admin'): ?>
                                <!-- Admin Navigation -->
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/admin') ?>">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/admin/providers') ?>">Providers</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/admin/services') ?>">Services</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/admin/appointments') ?>">Appointments</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/admin/users') ?>">Users</a>
                                </li>
                            <?php elseif ($userRole === 'provider'): ?>
                                <!-- Provider Navigation -->
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/provider') ?>">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/provider/services') ?>">Services</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/provider/schedule') ?>">Schedule</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/provider/appointments') ?>">Appointments</a>
                                </li>
                            <?php elseif ($userRole === 'patient'): ?>
                                <!-- Patient Navigation -->
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/patient') ?>">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/patient/book') ?>">Book</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/appointments') ?>">My Appointments</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/patient/search') ?>">Find Provider</a>
                                </li>
                            <?php else: ?>
                                <!-- Guest Navigation -->
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/appointments') ?>">Appointments</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/auth/register') ?>">Register</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        
                        <ul class="navbar-nav">
                            <?php if ($isLoggedIn): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                       data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="badge bg-<?php 
                                            echo $userRole === 'admin' ? 'danger' : 
                                                ($userRole === 'provider' ? 'success' : 'primary'); 
                                        ?>"><?= ucfirst($userRole) ?></span>
                                        <?= htmlspecialchars($userName) ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                        <?php if ($userRole === 'patient'): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('index.php/patient/profile') ?>">
                                                    <i class="fas fa-user"></i> My Profile
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('index.php/notification/notifications/' . $_SESSION['user_id']) ?>">
                                                    <i class="fas fa-bell"></i> Notifications
                                                </a>
                                            </li>
                                        <?php elseif ($userRole === 'provider'): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('index.php/provider/profile') ?>">
                                                    <i class="fas fa-user"></i> My Profile
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('index.php/provider/notifications') ?>">
                                                    <i class="fas fa-bell"></i> Notifications
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('index.php/home') ?>">
                                                <i class="fas fa-home"></i> Home
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('index.php/auth/logout') ?>">
                                                <i class="fas fa-sign-out-alt"></i> Logout
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('index.php/auth') ?>">
                                        <i class="fas fa-sign-in-alt"></i> Login
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
    </div>
    
    <div class="container">
        <?php display_flash_messages(); ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var dropdowns = document.querySelectorAll('.dropdown-toggle');
        if (dropdowns.length > 0) {
            dropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('click', function(event) {
                    event.preventDefault();
                    var dropdownMenu = this.nextElementSibling;
                    if (dropdownMenu.classList.contains('show')) {
                        dropdownMenu.classList.remove('show');
                    } else {
                        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                            menu.classList.remove('show');
                        });
                        dropdownMenu.classList.add('show');
                    }
                });
            });

            document.addEventListener('click', function(event) {
                if (!event.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                        menu.classList.remove('show');
                    });
                }
            });
        }
    });
    </script>