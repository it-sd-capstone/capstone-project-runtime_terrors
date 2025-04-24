<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container patient-dashboard">
    <h2>Welcome, <?= htmlspecialchars($patient['first_name'] ?? 'Patient') ?>!</h2>
    <p>Manage your appointments and healthcare records.</p>

    <div class="row">
        <!-- Upcoming Appointments -->
        <div class="col-md-6">
            <div class="card">
                <h4>Upcoming Appointments</h4>
                <?php if (!empty($appointments)) : ?>
                    <ul>
                        <?php foreach ($appointments as $appointment) : ?>
                            <li>
                                Dr. <?= htmlspecialchars($appointment['provider_name']) ?> - <?= htmlspecialchars($appointment['service_name']) ?><br>
                                <?= htmlspecialchars($appointment['appointment_date']) ?> at <?= htmlspecialchars($appointment['start_time']) ?>
                                <br>
                                <a href="<?= base_url('index.php/patient/reschedule/' . $appointment['appointment_id']) ?>" class="btn btn-warning">Reschedule</a>
                                <a href="<?= base_url('index.php/patient/cancel/' . $appointment['appointment_id']) ?>" class="btn btn-danger">Cancel</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>No upcoming appointments.</p>
                    <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary">Book an Appointment</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card">
                <h4>Quick Actions</h4>
                <a href="<?= base_url('index.php/patient/profile') ?>" class="btn btn-info">Edit Profile</a>
                <a href="<?= base_url('index.php/patient/history') ?>" class="btn btn-secondary">View History</a>
                <a href="<?= base_url('index.php/patient/notifications') ?>" class="btn btn-warning">View Notifications</a>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>