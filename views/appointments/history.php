<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Appointment History</h1>
    
    <?php if (isset($appointment) && $appointment): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5>Appointment Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Appointment ID:</strong> #<?= htmlspecialchars($appointment['appointment_id']) ?></p>
                        <p><strong>Service:</strong> <?= htmlspecialchars($appointment['service_name']) ?></p>
                        <p><strong>Date:</strong> <?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date']))) ?></p>
                        <p><strong>Time:</strong> <?= htmlspecialchars(date('g:i A', strtotime($appointment['start_time']))) ?> - 
                            <?= htmlspecialchars(date('g:i A', strtotime($appointment['end_time']))) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Patient:</strong> <?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?></p>
                        <p><strong>Provider:</strong> <?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?></p>
                        <p><strong>Current Status:</strong> <span class="badge bg-<?= getStatusBadgeClass($appointment['status']) ?>"><?= htmlspecialchars(ucfirst($appointment['status'])) ?></span></p>
                        <p><strong>Type:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $appointment['type']))) ?></p>
                    </div>
                </div>
                <?php if (!empty($appointment['reason'])): ?>
                    <div class="mt-3">
                        <p><strong>Reason for Visit:</strong></p>
                        <p><?= nl2br(htmlspecialchars($appointment['reason'])) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($appointment['notes'])): ?>
                    <div class="mt-3">
                        <p><strong>Notes:</strong></p>
                        <p><?= nl2br(htmlspecialchars($appointment['notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Activity History</h5>
            </div>
            <div class="card-body">
                <?php if (isset($logs) && !empty($logs)): ?>
                    <div class="timeline">
                        <?php foreach ($logs as $log): ?>
                            <?php 
                                $details = json_decode($log['details'], true);
                                $actionClass = getActionClass($log['action']);
                            ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?= $actionClass ?>"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0"><?= htmlspecialchars(formatActionText($log['action'])) ?></h6>
                                    <p class="text-muted mb-1">
                                        <small>
                                            <?= htmlspecialchars(date('F j, Y g:i A', strtotime($log['created_at']))) ?> by 
                                            <?= htmlspecialchars($log['user_first_name'] . ' ' . $log['user_last_name']) ?> 
                                            (<?= htmlspecialchars(ucfirst($log['user_role'])) ?>)
                                        </small>
                                    </p>
                                    
                                    <?php if (!empty($details)): ?>
                                        <div class="details-card">
                                            <?php if ($log['action'] === 'created'): ?>
                                                <p><small>Booked an appointment for <strong><?= htmlspecialchars(date('F j, Y', strtotime($details['appointment_date']))) ?></strong> 
                                                   at <strong><?= htmlspecialchars(date('g:i A', strtotime($details['start_time']))) ?></strong>
                                                </small></p>
                                            <?php elseif ($log['action'] === 'canceled'): ?>
                                                <p><small>Canceled an appointment previously scheduled for <strong><?= htmlspecialchars(date('F j, Y', strtotime($details['appointment_date']))) ?></strong>
                                                   at <strong><?= htmlspecialchars(date('g:i A', strtotime($details['start_time']))) ?></strong></small></p>
                                                <p><small>Reason: <?= htmlspecialchars($details['cancellation_reason']) ?></small></p>
                                            <?php elseif ($log['action'] === 'status_changed'): ?>
                                                <p><small>Changed status from <strong><?= htmlspecialchars(ucfirst($details['previous_status'])) ?></strong> 
                                                   to <strong><?= htmlspecialchars(ucfirst($details['new_status'])) ?></strong></small></p>
                                                <p><small>Reason: <?= htmlspecialchars($details['reason']) ?></small></p>
                                            <?php else: ?>
                                                <pre class="small"><?= htmlspecialchars(json_encode($details, JSON_PRETTY_PRINT)) ?></pre>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No activity logs found for this appointment.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="<?= base_url('provider/appointments') ?>" class="btn btn-secondary">Back to Appointments</a>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Appointment not found or you don't have permission to view it.
        </div>
        <a href="<?= base_url('provider/appointments') ?>" class="btn btn-secondary">Back to Appointments</a>
    <?php endif; ?>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 25px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        top: 5px;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        left: -23px;
        width: 2px;
        height: 100%;
        background: #e9ecef;
    }
    
    .details-card {
        background-color: #f8f9fa;
        border-radius: 4px;
        padding: 10px;
        margin-top: 5px;
    }
</style>

<?php
// Helper functions for the view
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'scheduled':
            return 'primary';
        case 'completed':
            return 'success';
        case 'canceled':
            return 'danger';
        case 'no_show':
            return 'warning';
        case 'in_progress':
            return 'info';
        default:
            return 'secondary';
    }
}

function getActionClass($action) {
    switch ($action) {
        case 'created':
            return 'success';
        case 'canceled':
            return 'danger';
        case 'status_changed':
            return 'info';
        default:
            return 'secondary';
    }
}

function formatActionText($action) {
    switch ($action) {
        case 'created':
            return 'Appointment Created';
        case 'canceled':
            return 'Appointment Canceled';
        case 'status_changed':
            return 'Status Updated';
        default:
            return ucfirst(str_replace('_', ' ', $action));
    }
}
?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>