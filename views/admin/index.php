<?php include VIEW_PATH . '/partials/admin_header.php'; ?>

<div class="container admin-dashboard">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h4>Welcome to the Admin Dashboard</h4>
                <p>You have access to manage users, appointments, and services.</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Users Card -->
        <div class="col-md-4">
            <div class="card admin-card">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text">Manage system users including patients, providers, and administrators.</p>
                    <div class="d-grid">
                        <a href="<?= base_url('index.php/admin/users') ?>" class="btn btn-primary">Manage Users</a>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Total Users: <?= $stats['totalUsers'] ?? 0 ?></small>
                </div>
            </div>
        </div>
        
        <!-- Appointments Card -->
        <div class="col-md-4">
            <div class="card admin-card">
                <div class="card-body">
                    <h5 class="card-title">Appointments</h5>
                    <p class="card-text">View and manage all appointments in the system.</p>
                    <div class="d-grid">
                        <a href="<?= base_url('index.php/admin/appointments') ?>" class="btn btn-success">Manage Appointments</a>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Total Appointments: <?= $stats['totalAppointments'] ?? 0 ?></small>
                </div>
            </div>
        </div>
        
        <!-- Services Card -->
        <div class="col-md-4">
            <div class="card admin-card">
                <div class="card-body">
                    <h5 class="card-title">Services</h5>
                    <p class="card-text">Manage available services and their details.</p>
                    <div class="d-grid">
                        <a href="<?= base_url('index.php/admin/services') ?>" class="btn btn-info">Manage Services</a>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Total Services: <?= $stats['totalServices'] ?? 0 ?></small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Stats -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>System Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>User Distribution</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Patients
                                    <span class="badge bg-primary rounded-pill"><?= $stats['totalPatients'] ?? 0 ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Providers
                                    <span class="badge bg-success rounded-pill"><?= $stats['totalProviders'] ?? 0 ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Administrators
                                    <span class="badge bg-danger rounded-pill"><?= $stats['totalAdmins'] ?? 0 ?></span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Appointment Status</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Scheduled
                                    <span class="badge bg-info rounded-pill"><?= $scheduledAppointments ?? 0 ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Confirmed
                                    <span class="badge bg-warning rounded-pill"><?= $confirmedAppointments ?? 0 ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Completed
                                    <span class="badge bg-success rounded-pill"><?= $completedAppointments ?? 0 ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Canceled
                                    <span class="badge bg-danger rounded-pill"><?= $canceledAppointments ?? 0 ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    No Show
                                    <span class="badge bg-secondary rounded-pill"><?= $noShowAppointments ?? 0 ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- NEW SECTION: Service Usage and Provider Availability -->
    <div class="row mt-4">
        <!-- Service Usage Metrics -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Service Usage Metrics</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['topServices'])): ?>
                        <h6>Top Used Services</h6>
                        <ul class="list-group">
                            <?php foreach ($stats['topServices'] as $service): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($service['name']) ?>
                                    <span class="badge bg-info rounded-pill"><?= $service['usage_count'] ?> appointments</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No service usage data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Provider Availability Summary -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Provider Availability</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Booking Status</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $stats['availabilityRate'] ?? 0 ?>%;" 
                                 aria-valuenow="<?= $stats['availabilityRate'] ?? 0 ?>" 
                                 aria-valuemin="0" aria-valuemax="100">
                                <?= $stats['availabilityRate'] ?? 0 ?>%
                            </div>
                        </div>
                        <small class="text-muted">
                            <?= $stats['bookedSlots'] ?? 0 ?> booked out of <?= $stats['totalAvailableSlots'] ?? 0 ?> available slots
                        </small>
                    </div>
                    
                    <?php if (!empty($stats['topProviders'])): ?>
                        <h6>Top Providers by Appointments</h6>
                        <ul class="list-group">
                            <?php foreach ($stats['topProviders'] as $provider): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($provider['provider_name']) ?>
                                    <span class="badge bg-success rounded-pill">
                                        <?= $provider['appointment_count'] ?> appointments
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No provider booking data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['recentActivity'])): ?>
                        <p class="text-muted">No recent activity to display.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>User</th>
                                        <th>Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['recentActivity'] as $activity): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($activity['date']) ?></td>
                                        <td><?= htmlspecialchars($activity['user']) ?></td>
                                        <td><?= htmlspecialchars($activity['description']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4 mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('index.php/admin/users/create') ?>" class="btn btn-outline-primary">Add New User</a>
                        <a href="<?= base_url('index.php/admin/addProvider') ?>" class="btn btn-outline-success">Add New Provider</a>
                        <a href="<?= base_url('index.php/admin/services/create') ?>" class="btn btn-outline-info">Add New Service</a>
                        <a href="<?= base_url('index.php/admin/reports') ?>" class="btn btn-outline-secondary">Generate Reports</a>
                        <a href="<?= base_url('index.php/admin/settings') ?>" class="btn btn-outline-dark">System Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>