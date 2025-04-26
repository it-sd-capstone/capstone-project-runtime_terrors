<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">Cancel Appointment</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <p class="mb-0">Are you sure you want to cancel your appointment with Dr. <?= htmlspecialchars($appointment['provider_name']) ?>?</p>
                    </div>

                    <table class="table table-striped">
                        <tr>
                            <th style="width: 30%">Service:</th>
                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td><?= date('F d, Y', strtotime($appointment['appointment_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Time:</th>
                            <td><?= date('g:i A', strtotime($appointment['start_time'])) ?></td>
                        </tr>
                        <tr>
                            <th>Notes:</th>
                            <td><?= htmlspecialchars($appointment['notes'] ?? 'N/A') ?></td>
                        </tr>
                    </table>

                    <!-- Confirmation Form -->
                    <form method="POST" action="<?= base_url('index.php/patient/processCancel') ?>">
                        <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Cancellation:</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('index.php/patient/history') ?>" class="btn btn-secondary">Go Back</a>
                            <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>