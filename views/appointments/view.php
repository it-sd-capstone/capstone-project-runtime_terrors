<?php
// Make sure this file isn't accessed directly
// We check if the appointment variable is set to determine if we're included from the controller
if (!isset($appointment)) {
    // If we're accessed directly, we redirect to the appointments listing
    header('Location: ../../../index.php/appointments');
    exit;
}
?>
<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
            <?= $_SESSION['flash_message'] ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-calendar-check"></i> Appointment Details</h1>
        <a href="<?= base_url('index.php/appointments') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Appointments
        </a>
    </div>

    <?php if (isset($appointment) && $appointment): ?>
        <!-- Appointment Status Banner -->
        <div class="card mb-4 border-<?= getStatusBadgeClass($appointment['status']) ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-<?= getStatusBadgeClass($appointment['status']) ?> fs-6 mb-2">
                            <?= ucfirst($appointment['status']) ?>
                        </span>
                        <h4 class="card-title mb-0"><?= htmlspecialchars($appointment['service_name']) ?></h4>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars(date('l, F j, Y', strtotime($appointment['appointment_date']))) ?>
                            at <?= htmlspecialchars(date('g:i A', strtotime($appointment['start_time']))) ?> - 
                            <?= htmlspecialchars(date('g:i A', strtotime($appointment['end_time']))) ?>
                        </p>
                    </div>
                    <?php if ($appointment['status'] === 'scheduled' || $appointment['status'] === 'confirmed'): ?>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog me-1"></i> Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                <?php if ($_SESSION['role'] === 'provider' || $_SESSION['role'] === 'admin'): ?>
                                    <li>
                                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#statusModal"
                                                data-appointment-id="<?= $appointment['appointment_id'] ?>"
                                                data-current-status="<?= $appointment['status'] ?>">
                                            <i class="fas fa-exchange-alt me-1"></i> Update Status
                                        </button>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('index.php/appointments/reschedule?id=' . $appointment['appointment_id']) ?>">
                                        <i class="fas fa-calendar-alt me-1"></i> Reschedule
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('index.php/appointments/cancel?id=' . $appointment['appointment_id']) ?>"
                                       onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Appointment Information -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Appointment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Service</h6>
                                <p class="fs-5"><?= htmlspecialchars($appointment['service_name']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Type</h6>
                                <p class="fs-5"><?= ucfirst(str_replace('_', ' ', $appointment['type'])) ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Date</h6>
                                <p class="fs-5"><?= htmlspecialchars(date('l, F j, Y', strtotime($appointment['appointment_date']))) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Time</h6>
                                <p class="fs-5">
                                    <?= htmlspecialchars(date('g:i A', strtotime($appointment['start_time']))) ?> - 
                                    <?= htmlspecialchars(date('g:i A', strtotime($appointment['end_time']))) ?>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Provider</h6>
                                <p class="fs-5">
                                    <?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Patient</h6>
                                <p class="fs-5">
                                    <?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Reason for Appointment -->
                        <?php if (!empty($appointment['reason'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted">Reason for Appointment</h6>
                            <p><?= nl2br(htmlspecialchars($appointment['reason'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Notes Section -->
                        <div class="mb-3">
                            <h6 class="text-muted">Notes</h6>
                            <?php if ($_SESSION['role'] === 'provider' || $_SESSION['role'] === 'admin'): ?>
                                <form action="<?= base_url('index.php/appointments/update_notes') ?>" method="POST">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                    <div class="mb-3">
                                        <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($appointment['notes'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Notes</button>
                                </form>
                            <?php else: ?>
                                <?php if (!empty($appointment['notes'])): ?>
                                    <p><?= nl2br(htmlspecialchars($appointment['notes'])) ?></p>
                                <?php else: ?>
                                    <p class="text-muted">No notes available</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Appointment History -->
                <?php if (!empty($logs)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Appointment History</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($logs as $index => $log): ?>
                                <?php 
                                    $details = json_decode($log['details'], true) ?? [];
                                    $actionClass = '';
                                    $actionIcon = '';
                                    
                                    switch ($log['action']) {
                                        case 'created':
                                            $actionClass = 'success';
                                            $actionIcon = 'fa-plus-circle';
                                            break;
                                        case 'canceled':
                                            $actionClass = 'danger';
                                            $actionIcon = 'fa-times-circle';
                                            break;
                                        case 'rescheduled':
                                            $actionClass = 'warning';
                                            $actionIcon = 'fa-calendar-alt';
                                            break;
                                        case 'status_changed':
                                            $actionClass = 'info';
                                            $actionIcon = 'fa-exchange-alt';
                                            break;
                                        case 'notes_updated':
                                            $actionClass = 'primary';
                                            $actionIcon = 'fa-edit';
                                            break;
                                        default:
                                            $actionClass = 'secondary';
                                            $actionIcon = 'fa-info-circle';
                                    }
                                ?>
                                <div class="timeline-item">
                                    <div class="timeline-icon bg-<?= $actionClass ?>">
                                        <i class="fas <?= $actionIcon ?>"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">
                                            <?php
                                                switch ($log['action']) {
                                                    case 'created':
                                                        echo 'Appointment Created';
                                                        break;
                                                    case 'canceled':
                                                        echo 'Appointment Canceled';
                                                        break;
                                                    case 'rescheduled':
                                                        echo 'Appointment Rescheduled';
                                                        break;
                                                    case 'status_changed':
                                                        echo 'Status Updated to ' . ucfirst($details['new_status'] ?? '');
                                                        break;
                                                    case 'notes_updated':
                                                        echo 'Notes Updated';
                                                        break;
                                                    default:
                                                        echo ucfirst(str_replace('_', ' ', $log['action']));
                                                }
                                            ?>
                                        </h6>
                                        <p class="text-muted mb-1">
                                            <?= date('F j, Y g:i A', strtotime($log['created_at'])) ?> 
                                            by <?= htmlspecialchars($log['user_first_name'] . ' ' . $log['user_last_name']) ?>
                                        </p>
                                        
                                        <?php if ($log['action'] === 'rescheduled' && isset($details['previous_date'])): ?>
                                            <p class="mb-0">
                                                Changed from: 
                                                <span class="text-decoration-line-through">
                                                    <?= date('F j, Y', strtotime($details['previous_date'])) ?> 
                                                    at <?= date('g:i A', strtotime($details['previous_time'])) ?>
                                                </span>
                                                <br>
                                                To: <?= date('F j, Y', strtotime($details['new_date'])) ?> 
                                                at <?= date('g:i A', strtotime($details['new_time'])) ?>
                                            </p>
                                        <?php elseif ($log['action'] === 'canceled' && isset($details['cancellation_reason'])): ?>
                                            <p class="mb-0">
                                                Reason: <?= htmlspecialchars($details['cancellation_reason']) ?>
                                            </p>
                                        <?php elseif ($log['action'] === 'status_changed' && isset($details['previous_status'])): ?>
                                            <p class="mb-0">
                                                Changed from 
                                                <span class="badge bg-<?= getStatusBadgeClass($details['previous_status']) ?>">
                                                    <?= ucfirst($details['previous_status']) ?>
                                                </span>
                                                to 
                                                <span class="badge bg-<?= getStatusBadgeClass($details['new_status']) ?>">
                                                    <?= ucfirst($details['new_status']) ?>
                                                </span>
                                                <?php if (!empty($details['reason'])): ?>
                                                    <br>Reason: <?= htmlspecialchars($details['reason']) ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Patient Information -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Patient Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted">Name</h6>
                            <p class="fs-5"><?= htmlspecialchars($appointment['patient_name']) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-muted">Email</h6>
                            <p><?= htmlspecialchars($appointment['patient_email']) ?></p>
                        </div>
                        <?php if (!empty($appointment['patient_phone'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted">Phone</h6>
                            <p><?= htmlspecialchars($appointment['patient_phone']) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($appointment['patient_dob'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted">Date of Birth</h6>
                            <p><?= htmlspecialchars(date('F j, Y', strtotime($appointment['patient_dob']))) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($appointment['patient_address'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted">Address</h6>
                            <p><?= nl2br(htmlspecialchars($appointment['patient_address'])) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($appointment['emergency_contact'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted">Emergency Contact</h6>
                            <p>
                                <?= htmlspecialchars($appointment['emergency_contact']) ?><br>
                                <?= htmlspecialchars($appointment['emergency_contact_phone']) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($appointment['medical_conditions'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted">Medical Conditions</h6>
                            <p><?= nl2br(htmlspecialchars($appointment['medical_conditions'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($appointment['insurance_provider'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted">Insurance Information</h6>
                            <p>
                                Provider: <?= htmlspecialchars($appointment['insurance_provider']) ?><br>
                                Policy: <?= htmlspecialchars($appointment['insurance_policy_number']) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i> Appointment not found or you don't have permission to view it.
        </div>
    <?php endif; ?>
</div>

<!-- Status Update Modal -->
<?php if (isset($appointment) && ($_SESSION['role'] === 'provider' || $_SESSION['role'] === 'admin')): ?>
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="statusModalLabel">Update Appointment Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('index.php/appointments/updateStatus') ?>" method="POST">
                <?= csrf_token_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">New Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="scheduled" <?= $appointment['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="confirmed" <?= $appointment['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="in_progress" <?= $appointment['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= $appointment['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="no_show" <?= $appointment['status'] === 'no_show' ? 'selected' : '' ?>>No Show</option>
                            <option value="canceled" <?= $appointment['status'] === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Change (Optional)</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline:before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: 20px;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-icon {
    position: absolute;
    left: 10px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    text-align: center;
    color: white;
    transform: translateX(-50%);
    z-index: 1;
}

.timeline-icon i {
    font-size: 12px;
    line-height: 24px;
}

.timeline-content {
    margin-left: 40px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

@media (min-width: 768px) {
    .timeline:before {
        left: 50px;
    }
    
    .timeline-icon {
        left: 50px;
    }
    
    .timeline-content {
        margin-left: 70px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var statusModal = document.getElementById('statusModal');
    if (statusModal) {
        statusModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var appointmentId = button.getAttribute('data-appointment-id');
            var currentStatus = button.getAttribute('data-current-status');
            
            var statusSelect = document.getElementById('status');
            statusSelect.value = currentStatus;
        });
    }
});
</script>

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