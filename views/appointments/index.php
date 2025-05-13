<?php
// Commenting out debugging code
/*
if (!isset($availableSlots)) {
    echo "<p>Error: \$availableSlots is not set. Controller method may not be executing.</p>";
    $availableSlots = []; // Prevent foreach error
}
// Debug the actual data
echo "<pre>Available slots: ";
var_dump($availableSlots);
echo "</pre>";
*/

// Just keep this part to ensure $availableSlots is set
if (!isset($availableSlots)) {
    $availableSlots = []; // Prevent foreach error
}

// Modify the security check to allow controller access
if (!defined('BASE_PATH') && !isset($availableSlots)) {
    die("Direct access to views is not allowed");
}
?>
<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php 
                $message = '';
                switch ($_GET['success']) {
                    case 'booked':
                        $message = 'Appointment successfully booked!';
                        break;
                    case 'canceled':
                        $message = 'Appointment has been canceled.';
                        break;
                    case 'updated':
                        $message = 'Appointment status has been updated.';
                        break;
                    default:
                        $message = 'Operation completed successfully.';
                }
                echo $message;
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php 
                $message = '';
                switch ($_GET['error']) {
                    case 'cancel_failed':
                        $message = 'Failed to cancel appointment.';
                        break;
                    case 'missing_data':
                        $message = 'Missing required information.';
                        break;
                    case 'update_failed':
                        $message = 'Failed to update appointment status.';
                        break;
                    default:
                        $message = 'An error occurred.';
                }
                echo $message;
            ?>
        </div>
    <?php endif; ?>

    <h1 class="mb-4">Appointment Management</h1>

    
    <!-- User's Appointments -->
    <div class="card">
        <div class="card-header">
            <h5>Your Appointments</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($userAppointments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <?php if ($_SESSION['role'] === 'patient'): ?>
                                    <th>Provider</th>
                                <?php elseif ($_SESSION['role'] === 'provider'): ?>
                                    <th>Patient</th>
                                <?php else: ?>
                                    <th>Provider</th>
                                    <th>Patient</th>
                                <?php endif; ?>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userAppointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date']))) ?></td>
                                <td><?= htmlspecialchars(date('g:i A', strtotime($appointment['start_time']))) ?> - 
                                    <?= htmlspecialchars(date('g:i A', strtotime($appointment['end_time']))) ?></td>
                                
                                <?php if ($_SESSION['role'] === 'patient'): ?>
                                    <td><?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?></td>
                                <?php elseif ($_SESSION['role'] === 'provider'): ?>
                                    <td><?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?></td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?></td>
                                    <td><?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?></td>
                                <?php endif; ?>
                                
                                <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusBadgeClass($appointment['status']) ?>">
                                        <?= htmlspecialchars(ucfirst($appointment['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <?php if ($appointment['status'] === 'scheduled' && ($appointment['patient_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'admin')): ?>
                                            <a href="<?= base_url('index.php/appointments/cancel?id=' . $appointment['appointment_id']) ?>" 
                                               class="btn btn-danger btn-sm me-2">Cancel</a>
                                        <?php endif; ?>
                                        
                                        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'provider'): ?>
                                            <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" 
                                                    data-bs-target="#statusModal" 
                                                    data-appointment-id="<?= $appointment['appointment_id'] ?>" 
                                                    data-current-status="<?= $appointment['status'] ?>">
                                                Update Status
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="<?= base_url('index.php/appointments/history?id=' . $appointment['appointment_id']) ?>" 
                                           class="btn btn-info btn-sm">History</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="mb-0">No appointments found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Appointment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('index.php/appointments/updateStatus') ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="modal-appointment-id">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="no_show">No Show</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Change (Optional)</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize the modal with appointment data
    document.addEventListener('DOMContentLoaded', function() {
        var statusModal = document.getElementById('statusModal');
        if (statusModal) {
            statusModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var appointmentId = button.getAttribute('data-appointment-id');
                var currentStatus = button.getAttribute('data-current-status');
                
                var modalAppointmentId = document.getElementById('modal-appointment-id');
                var statusSelect = document.getElementById('status');
                
                modalAppointmentId.value = appointmentId;
                statusSelect.value = currentStatus;
            });
        }
    });
</script>

<?php
// Helper function for status badge color
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