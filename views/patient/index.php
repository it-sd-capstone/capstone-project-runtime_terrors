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
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .card-header {
            font-weight: 600;
            border-radius: 12px 12px 0 0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            transition: all 0.3s ease-in-out;
        }
        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
        .icon-badge {
            font-size: 0.8rem;
            padding: 6px 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <!-- Welcome Section -->
    <div class="alert alert-info text-center">
        <h2 class="h4 mb-0">
            <i class="fas fa-user-md text-primary"></i> Welcome back, <?= htmlspecialchars($patient['first_name']) ?>!
        </h2>
    </div>

    <!-- Stats & Appointments -->
    <div class="row">
        <!-- Appointment Stats Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Your Appointment Stats</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Total Appointments:</span>
                        <span class="badge bg-primary icon-badge"><?= count($upcomingAppointments) + count($pastAppointments) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Completed:</span>
                        <span class="badge bg-success icon-badge"><?= count($pastAppointments) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Upcoming:</span>
                        <span class="badge bg-info icon-badge"><?= count($upcomingAppointments) ?></span>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary w-100">Book New Appointment</a>
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h3 class="h5 mb-0">Upcoming Appointments</h3>
                    <a href="<?= base_url('index.php/patient/history') ?>" class="btn btn-light btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcomingAppointments)) : ?>
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
                                        <td><?= htmlspecialchars($appointment['provider_name']) ?></td>
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
                                                    <a href="<?= base_url('index.php/patient/reschedule/' . $appointment['appointment_id']) ?>" class="btn btn-warning">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </a>
                                                    <a href="<?= base_url('index.php/patient/cancel/' . $appointment['appointment_id']) ?>" class="btn btn-danger">
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
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <p class="mb-0">You have no upcoming appointments.</p>
                        </div>
                        <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary">Book Your First Appointment</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>