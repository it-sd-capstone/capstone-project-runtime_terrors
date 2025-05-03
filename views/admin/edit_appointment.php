<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="<?= base_url('index.php/admin/appointments') ?>" class="btn btn-secondary">« Back to Appointments</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Appointment</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error'] ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success'] ?>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('index.php/admin/appointments/edit/' . $appointment['appointment_id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label">Patient</label>
                                <select class="form-select" id="patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['user_id'] ?>" <?= $appointment['patient_id'] == $patient['user_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($patient['full_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="provider_id" class="form-label">Provider</label>
                                <select class="form-select" id="provider_id" name="provider_id" required>
                                    <option value="">Select Provider</option>
                                    <?php foreach ($providers as $provider): ?>
                                    <option value="<?= $provider['user_id'] ?>" <?= $appointment['provider_id'] == $provider['user_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($provider['full_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="service_id" class="form-label">Service</label>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">Select Service</option>
                                    <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['service_id'] ?>" <?= (isset($appointment) && isset($appointment['service_id']) && $appointment['service_id'] == $service['service_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($service['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="appointment_date" class="form-label">Appointment Date</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                       value="<?= $appointment['appointment_date'] ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="appointment_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="appointment_time" name="appointment_time" 
                                       value="<?= substr($appointment['start_time'], 0, 5) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="scheduled" <?= $appointment['status'] == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                    <option value="confirmed" <?= $appointment['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="completed" <?= $appointment['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="canceled" <?= $appointment['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                    <option value="no_show" <?= $appointment['status'] == 'no_show' ? 'selected' : '' ?>>No Show</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label">Appointment Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="in_person" <?= $appointment['type'] == 'in_person' ? 'selected' : '' ?>>In Person</option>
                                    <option value="virtual" <?= $appointment['type'] == 'virtual' ? 'selected' : '' ?>>Virtual</option>
                                    <option value="phone" <?= $appointment['type'] == 'phone' ? 'selected' : '' ?>>Phone</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Visit</label>
                            <textarea class="form-control" id="reason" name="reason" rows="2"><?= htmlspecialchars($appointment['reason'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($appointment['notes'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('index.php/admin/appointments') ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>