<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease-in-out;
        }
        .card:hover {
            transform: scale(1.02);
        }
        .btn-primary, .btn-outline-primary, .btn-outline-success, .btn-outline-info, .btn-outline-dark {
            transition: all 0.3s ease-in-out;
            font-weight: 500;
        }
        .btn-primary:hover, .btn-outline-primary:hover, .btn-outline-success:hover, .btn-outline-info:hover, .btn-outline-dark:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .icon-badge {
            font-size: 0.85rem;
            padding: 6px 10px;
            border-radius: 20px;
        }
        .empty-state {
            text-align: center;
            padding: 20px;
        }
        .empty-state i {
            font-size: 3rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <!-- Welcome Section -->
    <div class="alert alert-info text-center">
        <h2 class="h4 mb-0">
            <i class="fas fa-user-md text-primary"></i> Welcome back, <?= htmlspecialchars($patient['first_name']) ?>!
        </h2>
        <p class="text-muted">Stay on track with your upcoming appointments and healthcare reminders.</p>
    </div>

    <div class="row">
        <!-- Appointment Overview -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Appointment Overview</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Total Appointments:</span>
                        <span class="badge bg-primary icon-badge"><?= count($pastAppointments) + count($upcomingAppointments) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Upcoming:</span>
                        <span class="badge bg-info icon-badge"><?= count($upcomingAppointments) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Completed:</span>
                        <span class="badge bg-success icon-badge"><?= count($pastAppointments) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5>Upcoming Appointments</h5>
                    <a href="<?= base_url('index.php/appointments/') ?>" class="btn btn-light btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcomingAppointments)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Provider</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingAppointments as $appointment) : ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                            <td><?= date('g:i A', strtotime($appointment['start_time'])) ?></td>
                                            <td><?= htmlspecialchars($appointment['provider_first_name'] ?? '') . ' ' . htmlspecialchars($appointment['provider_last_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                            <td>
                                                <?php 
                                                    $statusClass = match($appointment['status']) {
                                                        'scheduled' => 'primary',
                                                        'confirmed' => 'success',
                                                        'canceled' => 'danger',
                                                        'no_show' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst(htmlspecialchars($appointment['status'])) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($appointment['status'] !== 'canceled'): ?>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?= base_url('index.php/appointments/reschedule?id=' . $appointment['appointment_id']) ?>" class="btn btn-warning">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </a>
                                                        <a href="<?= base_url('index.php/appointments/cancel?id=' . $appointment['appointment_id']) ?>" class="btn btn-danger">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No actions</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-alt"></i>
                            <p class="mt-2 text-muted">You have no upcoming appointments.</p>
                            <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary">Book Your First Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('index.php/patient/search') ?>" class="btn btn-outline-primary">Find a Provider</a>
                        <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-outline-success">Book Appointment</a>
                        <a href="<?= base_url('index.php/patient/profile') ?>" class="btn btn-outline-info">Update Profile</a>
                        <a href="<?= base_url('index.php/notification/settings') ?>" class="btn btn-outline-dark">Notification Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>