<?php 
  include VIEW_PATH . '/partials/header.php'; 
?>

<div class="container provider-dashboard my-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body p-4">
                    <h2 class="text-primary mb-2">Welcome<?= !empty($providerData['first_name']) || !empty($providerData['last_name']) ? ', ' . htmlspecialchars($providerData['first_name'] ?? '') . ' ' . htmlspecialchars($providerData['last_name'] ?? '') : '' ?>!</h2>
                    <p class="text-muted">Your hub for seamless appointment management and patient interactions.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Upcoming Appointments -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">Upcoming Appointments</h3>
                    <a href="<?= base_url('index.php/provider/appointments/' . htmlspecialchars($provider_id)) ?>" class="btn btn-light btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($appointments)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
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
                                                <a href="<?= base_url('index.php/provider/viewAppointment/' . $appointment['id']) ?>" class="btn btn-info btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            <div class="empty-state py-4">
                                <i class="fa fa-calendar-check mb-3"></i>
                                <p class="mb-0">No upcoming appointments scheduled.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats and Actions -->
        <div class="col-md-4">
            <!-- Provider Stats -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h3 class="h5 mb-0">Your Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="stat-label">Rating</div>
                        <div class="stat-value"><strong>4.8</strong> <small class="text-muted">(125 reviews)</small></div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="stat-label">Appointments</div>
                        <div class="stat-value"><strong><?= count($appointments ?? []) ?></strong> <small class="text-muted">upcoming</small></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="stat-label">Completion Rate</div>
                        <div class="stat-value"><strong>98%</strong></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h3 class="h5 mb-0">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('index.php/provider/profile') ?>" class="btn btn-outline-primary">
                            <i class="fa fa-user-md me-2"></i> Edit Profile
                        </a>
                        <a href="<?= base_url('index.php/provider/schedule') ?>" class="btn btn-outline-secondary">
                            <i class="fa fa-calendar-alt me-2"></i> Manage Availability
                        </a>
                        <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-outline-info">
                            <i class="fa fa-stethoscope me-2"></i> Manage Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
  include VIEW_PATH . '/partials/footer.php'; 
?>