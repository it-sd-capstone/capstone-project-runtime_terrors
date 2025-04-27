<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h2 class="h4 mb-0">Welcome back, <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>!</h2>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Appointment Stats Card -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Your Appointment Stats</h3>
                </div>
                <div class="card-body" id="appointment-stats">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Appointments:</span>
                        <span class="badge bg-primary rounded-pill"><?= count($upcomingAppointments) + count($pastAppointments) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Completed:</span>
                        <span class="badge bg-success rounded-pill"><?= count($pastAppointments) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Upcoming:</span>
                        <span class="badge bg-info rounded-pill"><?= count($upcomingAppointments) ?></span>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary btn-sm w-100">Book New Appointment</a>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Appointments Card -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">Upcoming Appointments</h3>
                    <a href="<?= base_url('index.php/patient/history') ?>" class="btn btn-light btn-sm">View All</a>
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
                                            <td><?= htmlspecialchars($appointment['provider_name']) ?></td>
                                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = 'secondary';
                                                switch($appointment['status']) {
                                                    case 'scheduled': $statusClass = 'primary'; break;
                                                    case 'confirmed': $statusClass = 'success'; break;
                                                    case 'canceled': $statusClass = 'danger'; break;
                                                    case 'no_show': $statusClass = 'warning'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?= ucfirst(htmlspecialchars($appointment['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($appointment['status'] != 'canceled'): ?>
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
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p class="mb-0">You have no upcoming appointments.</p>
                        </div>
                        <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary">Book Your First Appointment</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Dashboard Widgets Row -->
    <div class="row">
        <!-- Health Reminders Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h3 class="h5 mb-0">Health Reminders</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Annual check-up
                            <span class="badge bg-primary rounded-pill">Due in 3 months</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Dental cleaning
                            <span class="badge bg-warning rounded-pill">Overdue</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Prescription refill
                            <span class="badge bg-success rounded-pill">Up to date</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Quick Links Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h3 class="h5 mb-0">Quick Links</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('index.php/patient/search') ?>" class="btn btn-outline-primary">Find a Provider</a>
                        <a href="<?= base_url('index.php/patient/profile') ?>" class="btn btn-outline-secondary">Update Profile</a>
                        <a href="<?= base_url('index.php/notification/settings') ?>" class="btn btn-outline-info">Notification Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Real-Time Updates -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to update appointment stats
    function updateAppointmentStats() {
        fetch("<?= base_url('index.php/patient/fetchAppointments') ?>")
            .then(response => {
                if (!response.ok) {
                    throw new Error("Failed to fetch appointment data.");
                }
                return response.json();
            })
            .then(data => {
                if (data && data.stats) {
                    const statsHtml = `
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Appointments:</span>
                            <span class="badge bg-primary rounded-pill">${data.stats.total}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Completed:</span>
                            <span class="badge bg-success rounded-pill">${data.stats.completed}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Upcoming:</span>
                            <span class="badge bg-info rounded-pill">${data.stats.upcoming}</span>
                        </div>
                    `;
                    document.getElementById("appointment-stats").innerHTML = statsHtml;
                }
            })
            .catch(error => console.error("Error updating stats:", error.message));
    }
    
    // Update stats every 60 seconds
    setInterval(updateAppointmentStats, 60000);
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
