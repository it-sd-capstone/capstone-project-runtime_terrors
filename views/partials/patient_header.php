<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generate_csrf_token() ?>">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .patient-dashboard {
            margin-top: 20px;
        }
        .patient-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="mb-4">
            <h1 class="display-4">Patient Dashboard</h1>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#patientNavbar">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="patientNavbar">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/patient') ?>">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/patient/book') ?>">Book Appointment</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/patient/history') ?>">My Appointments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/patient/search') ?>">Explore Services</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ms-auto">
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="patientNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="badge bg-primary">Patient</span>
                                        <?= htmlspecialchars($_SESSION['name'] ?? 'Patient') ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <!-- Add profile link here -->
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/home') ?>"><i class="fas fa-home"></i> Home</a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/patient/profile') ?>"><i class="fas fa-user"></i> My Profile</a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/notification/notifications/' . $_SESSION['user_id']) ?>"><i class="fas fa-bell"></i> Notifications</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/auth/logout') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
