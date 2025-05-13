<?php 
// At the top of the file, add this for debugging
//print_r($appointment); // Uncomment to see the actual structure of your data

$appointment_id = $appointment['appointment_id'] ?? $appointment['id'] ?? null;

include VIEW_PATH . '/partials/header.php'; 
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm bg-light">
            <div class="card-body p-4">
                <h2 class="text-primary mb-2">
                    <i class="fas fa-calendar-check"></i> Appointment Details
                </h2>
                <p class="text-muted">Review and manage the selected appointment.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Appointment Information -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="h5 mb-0">Appointment Information</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Patient</p>
                        <h5><?= htmlspecialchars($appointment['patient_name']) ?></h5>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Service</p>
                        <h5><?= htmlspecialchars($appointment['service_name']) ?></h5>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Date</p>
                        <h5><?= date('F j, Y', strtotime($appointment['appointment_date'])) ?></h5>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Time</p>
                        <h5><?= date('g:i A', strtotime($appointment['start_time'])) ?></h5>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Status</p>
                        <span class="badge bg-<?= match($appointment['status']) {
                            'scheduled' => 'primary',
                            'confirmed' => 'success',
                            'canceled' => 'danger',
                            'completed' => 'info',
                            'no_show' => 'warning',
                            default => 'secondary'
                        } ?>">
                            <?= ucfirst(htmlspecialchars($appointment['status'])) ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Appointment ID</p>
                        <p>#<?= $appointment_id ?></p>
                    </div>
                </div>
                
                <?php if (!empty($appointment['notes'])): ?>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <p class="mb-1 text-muted">Notes</p>
                        <p class="p-3 bg-light rounded"><?= nl2br(htmlspecialchars($appointment['notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Appointment Actions -->
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h3 class="h5 mb-0">Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <?php if (in_array($appointment['status'], ['scheduled', 'pending'])): ?>
                    <!-- Confirm Button (POST for security) -->
                    <form action="<?= base_url('index.php/appointments/updateStatus') ?>" method="post" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Confirm
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if (!in_array($appointment['status'], ['completed', 'canceled', 'no_show'])): ?>
                    <!-- Complete Button -->
                    <form action="<?= base_url('index.php/appointments/updateStatus') ?>" method="post" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-clipboard-check me-1"></i> Mark Complete
                        </button>
                    </form>
                    
                    <!-- Cancel Button -->
                    <form action="<?= base_url('index.php/appointments/updateStatus') ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                        <input type="hidden" name="status" value="canceled">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                    </form>
                    
                    <!-- No Show Button -->
                    <form action="<?= base_url('index.php/appointments/updateStatus') ?>" method="post" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                        <input type="hidden" name="status" value="no_show">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-user-slash me-1"></i> No Show
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <!-- Add Notes Button (always available) -->
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#notesModal">
                        <i class="fas fa-sticky-note me-1"></i> Add Notes
                    </button>
                </div>
            </div>
        </div>

        <!-- Change Log / History Section -->
        <?php if (isset($logs) && !empty($logs)): ?>
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h3 class="h5 mb-0">Change History</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($logs as $log): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($log['action']) ?></strong>
                            by <?= htmlspecialchars($log['user_first_name'] . ' ' . $log['user_last_name']) ?>
                            on <?= date('F j, Y, g:i A', strtotime($log['created_at'])) ?>
                            <br>
                            <small><?= htmlspecialchars($log['details']) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <!-- Patient Information (if available) -->
        <div class="card shadow mb-4">
            <div class="card-header bg-secondary text-white">
                <h3 class="h5 mb-0">Patient Information</h3>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($appointment['patient_name'] ?? 'N/A'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($appointment['patient_email'] ?? 'N/A'); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['patient_phone'] ?? 'N/A'); ?></p>
                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($appointment['patient_dob'] ?? 'N/A'); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($appointment['patient_address'] ?? 'N/A'); ?></p>
                <p><strong>Emergency Contact:</strong> <?php echo htmlspecialchars($appointment['emergency_contact'] ?? 'N/A'); ?></p>
                <p><strong>Emergency Contact Phone:</strong> <?php echo htmlspecialchars($appointment['emergency_contact_phone'] ?? 'N/A'); ?></p>
                <p><strong>Medical Conditions:</strong> <?php echo htmlspecialchars($appointment['medical_conditions'] ?? 'None reported'); ?></p>
                <p><strong>Insurance Provider:</strong> <?php echo htmlspecialchars($appointment['insurance_provider'] ?? 'N/A'); ?></p>
                <p><strong>Insurance Policy Number:</strong> <?php echo htmlspecialchars($appointment['insurance_policy_number'] ?? 'N/A'); ?></p>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h3 class="h5 mb-0">Quick Links</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('index.php/provider/appointments') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-alt me-2"></i> Back to Appointments
                    </a>
                    <a href="<?= base_url('index.php/provider/index') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('index.php/appointments/update_notes') ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="notesModalLabel">Add/Edit Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Appointment Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="5"><?= htmlspecialchars($appointment['notes'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Notes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
