<?php 
  include VIEW_PATH . '/partials/provider_header.php'; 
?>

<div class="container provider-dashboard">
    <h2>Welcome, Dr. <?= htmlspecialchars($provider['first_name'] ?? 'Provider') ?>!</h2>
    <p>Your hub for seamless appointment management and patient interactions.</p>

    <nav class="nav nav-pills mb-3">
        <a class="nav-link active" href="<?= base_url('index.php/provider') ?>">Dashboard</a>
        <a class="nav-link" href="<?= base_url('index.php/provider/schedule') ?>">Schedule</a>
        <a class="nav-link" href="<?= base_url('index.php/provider/appointments/' . htmlspecialchars($provider_id)) ?>">Appointments</a>
        <a class="nav-link" href="<?= base_url('index.php/provider/services') ?>">Services</a>
    </nav>

        <!-- Upcoming Appointments -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">Upcoming Appointments</h3>
                    <a href="<?= base_url('index.php/provider/appointments/' . htmlspecialchars($provider_id)) ?>" class="btn btn-light btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcomingAppointments)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment) : ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                            <td><?= date('g:i A', strtotime($appointment['start_time'])) ?></td>
                                            <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $appointment['status'] == 'confirmed' ? 'success' : ($appointment['status'] == 'pending' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst(htmlspecialchars($appointment['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('index.php/provider/viewAppointment/' . $appointment['id']) ?>" class="btn btn-info btn-sm">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            <p class="mb-0">No upcoming appointments.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-3">
        <div class="col-md-6">
                <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h3 class="h5 mb-0">Quick Actions</h3>
                </div>
                    <div class="card-body d-flex justify-content-between">
                        <a href="<?= base_url('index.php/provider/profile') ?>" class="btn btn-outline-primary flex-fill">Edit Profile</a>
                        <a href="<?= base_url('index.php/provider/schedule') ?>" class="btn btn-outline-secondary flex-fill">Manage Availability</a>
                        <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-outline-info flex-fill">Manage Services</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php 
  include VIEW_PATH . '/partials/footer.php'; 
?>