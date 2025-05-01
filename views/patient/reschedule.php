<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Reschedule Appointment</h4>
            <a href="<?= base_url('index.php/patient') ?>" class="btn btn-light btn-sm">
                Back to Dashboard
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($appointment)): ?>
                <div class="mb-4">
                    <h5>Current Appointment Details</h5>
                    <div class="card bg-light">
                        <div class="card-body">
                            <p><strong>Provider:</strong> <?= htmlspecialchars($appointment['provider_name'] ?? 'Unknown') ?></p>
                            <p><strong>Service:</strong> <?= htmlspecialchars($appointment['service_name'] ?? 'Unknown') ?></p>
                            <p><strong>Date:</strong> 
                                <?= !empty($appointment['appointment_date']) ? 
                                    date('l, F j, Y', strtotime($appointment['appointment_date'])) : 
                                    'Not specified' ?>
                            </p>
                            <p><strong>Time:</strong> 
                                <?= !empty($appointment['start_time']) && !empty($appointment['end_time']) ? 
                                    date('g:i A', strtotime($appointment['start_time'])) . ' - ' . 
                                    date('g:i A', strtotime($appointment['end_time'])) : 
                                    'Not specified' ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="<?= base_url('index.php/patient/processReschedule') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                    <input type="hidden" name="provider_id" value="<?= $appointment['provider_id'] ?>">
                    <input type="hidden" name="service_id" value="<?= $appointment['service_id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_date" class="form-label">Select New Date</label>
                            <input type="date" class="form-control" id="new_date" name="new_date" required
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            <div class="form-text">Please select a date in the future.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="new_time" class="form-label">Select New Time</label>
                            <input type="time" class="form-control" id="new_time" name="new_time" required
                                   min="08:00" max="17:00">
                            <div class="form-text">Please select a time during business hours (8:00 AM - 5:00 PM).</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Rescheduling (Optional)</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <small>Please Note: Rescheduling an appointment less than 24 hours before the scheduled time may incur a fee.</small>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= base_url('index.php/patient/history') ?>" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Reschedule Appointment</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger">
                    <p>The appointment you're trying to reschedule doesn't exist or has been removed.</p>
                    <a href="<?= base_url('index.php/patient/history') ?>" class="btn btn-primary mt-3">Back to My Appointments</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>