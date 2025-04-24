<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
                                <a class="nav-link" href="<?= base_url('index.php/patient/appointments') ?>">My Appointments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/patient/view_services') ?>">Explore Services</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ms-auto">
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="patientNavDropdown" role="button" data-bs-toggle="dropdown">
                                        <span class="badge bg-primary">Patient</span>
                                        <?= htmlspecialchars($_SESSION['name'] ?? 'Patient') ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/auth/logout') ?>">Logout</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>