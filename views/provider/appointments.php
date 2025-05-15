
<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">My Appointments</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <!-- Upcoming Appointments -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Appointments</h5>
        </div>
        <div class="card-body">
            <?php 
            $upcomingAppointments = array_filter($appointments, function($app) {
                return $app['status'] !== 'completed' && $app['status'] !== 'canceled' && 
                       (strtotime($app['appointment_date']) >= strtotime(date('Y-m-d')) || 
                       (strtotime($app['appointment_date']) == strtotime(date('Y-m-d')) && 
                        strtotime($app['start_time']) > strtotime(date('H:i:s'))));
            });
            ?>
            
            <?php if (!empty($upcomingAppointments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
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
                            <?php foreach ($upcomingAppointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date']))) ?></td>
                                <td><?= htmlspecialchars(date('g:i A', strtotime($appointment['start_time']))) ?> - 
                                    <?= htmlspecialchars(date('g:i A', strtotime($appointment['end_time']))) ?></td>
                                <td><?= htmlspecialchars(($appointment['patient_first_name'] ?? '') . ' ' . ($appointment['patient_last_name'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($appointment['service_name'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusBadgeClass($appointment['status']) ?>">
                                        <?= htmlspecialchars(ucfirst($appointment['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('index.php/provider/viewAppointment/' . $appointment['appointment_id']) ?>" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($appointment['status'] === 'scheduled' || $appointment['status'] === 'confirmed'): ?>
                                            <a href="<?= base_url('index.php/provider/cancelAppointment/' . $appointment['appointment_id']) ?>" 
                                               class="btn btn-outline-danger" title="Cancel"
                                               onclick="return confirm('Are you sure you want to cancel this appointment?');">
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
            <?php else: ?>
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-calendar-plus fa-3x text-muted"></i>
                    </div>
                    <h5>No Upcoming Appointments</h5>
                    <p class="text-muted">You don't have any appointments scheduled.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Past & Canceled Appointments -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Past & Canceled Appointments</h5>
        </div>
        <div class="card-body">
            <?php 
            $pastAndCanceledAppointments = array_filter($appointments, function($app) {
                return $app['status'] === 'completed' || 
                    $app['status'] === 'canceled' || 
                    $app['status'] === 'no_show' ||
                    strtotime($app['appointment_date']) < strtotime(date('Y-m-d'));
            });
            ?>
            
            <?php if (!empty($pastAndCanceledAppointments)): ?>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastAndCanceledAppointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date']))) ?></td>
                                <td><?= htmlspecialchars(($appointment['patient_first_name'] ?? '') . ' ' . ($appointment['patient_last_name'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($appointment['service_name'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusBadgeClass($appointment['status']) ?>">
                                        <?= htmlspecialchars(ucfirst($appointment['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('index.php/provider/viewAppointment/' . $appointment['appointment_id']) ?>" 
                                    class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-info-circle me-1"></i> Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5>No Past or Canceled Appointments</h5>
                    <p class="text-muted">Your appointment history will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'scheduled':
            return 'primary';
        case 'confirmed':
            return 'info';
        case 'in_progress':
            return 'warning';
        case 'completed':
            return 'success';
        case 'canceled':
            return 'danger';
        case 'no_show':
            return 'secondary';
        default:
            return 'secondary';
    }
}
?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
