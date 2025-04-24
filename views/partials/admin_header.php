<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment System Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Additional CSS -->
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .admin-dashboard {
            margin-top: 20px;
        }
        .admin-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="mb-4">
            <h1 class="display-4">Appointment System Administration</h1>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="adminNavbar">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/admin') ?>">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/admin/providers') ?>">Manage Providers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/admin/services') ?>">Manage Services</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/admin/appointments') ?>">Manage Appointments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/admin/users') ?>">Manage Users</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ms-auto">
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="adminNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="badge bg-danger">Admin</span>
                                        <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminNavDropdown">
                                        <li><a class="dropdown-item" href="<?= base_url('index.php/auth/logout') ?>">Logout</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>