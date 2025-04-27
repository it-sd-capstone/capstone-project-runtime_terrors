<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <style>
        body {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .provider-dashboard {
            margin-top: 20px;
        }
        .provider-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="mb-4">
            <h1 class="display-4">Provider Dashboard</h1>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#providerNavbar">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="providerNavbar">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/provider') ?>">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/provider/services') ?>">Manage Services</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/provider/manage_availability') ?>">Manage Availability</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/provider/appointments') ?>">View Appointments</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ms-auto">
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="providerNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="badge bg-success">Provider</span>
                                        <?= htmlspecialchars($_SESSION['name'] ?? 'Provider') ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <!-- Add Home and Profile links -->
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/home') ?>">Home</a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/provider/profile') ?>">My Profile</a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/provider/notifications') ?>">Notifications</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/auth/logout') ?>">Logout</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>