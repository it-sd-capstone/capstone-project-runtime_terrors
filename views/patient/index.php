<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body p-4">
                    <h2 class="text-primary mb-2">
                        <i class="fas fa-user-circle"></i> Welcome, <?= htmlspecialchars($patient['first_name'] ?? 'Patient') ?>!
                    </h2>
                    <p class="text-muted">Manage your appointments and health information all in one place.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Display alert messages -->
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Appointment Overview -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Appointment Overview</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Appointments:</span>
                        <span class="badge bg-primary rounded-pill"><?= count($pastAppointments) + count($upcomingAppointments) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Upcoming:</span>
                        <span class="badge bg-info rounded-pill"><?= count($upcomingAppointments) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Completed:</span>
                        <span class="badge bg-success rounded-pill"><?= count($pastAppointments) ?></span>
                    </div>
                    
                    <div class="d-grid mt-3">
                        <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i>Book New Appointment
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('index.php/patient/profile') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-user-edit me-2"></i>Update Profile
                        </a>
                        <a href="<?= base_url('index.php/patient/search') ?>" class="btn btn-outline-success">
                            <i class="fas fa-search me-2"></i>Find Provider
                        </a>
                        <a href="<?= base_url('index.php/auth/change_password') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </a>
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
                            <table class="table table-striped table-hover">
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
                                            <td><?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?></td>
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
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?= ucfirst(htmlspecialchars($appointment['status'])) ?>
                                                </span>
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
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                            <h5>No Upcoming Appointments</h5>
                            <p class="text-muted">You don't have any appointments scheduled.</p>
                            <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary mt-2">
                                Book Your First Appointment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>