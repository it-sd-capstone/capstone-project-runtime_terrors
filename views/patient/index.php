<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container patient-dashboard">
    <h2>Welcome, <?= htmlspecialchars($patient['first_name'] ?? 'Patient') ?>!</h2>
    <p>Your healthcare portal.</p>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <h4>Upcoming Appointments</h4>
                <?php if (!empty($appointments)) : ?>
                    <ul>
                        <?php foreach ($appointments as $appointment) : ?>
                            <li>
                                Dr. <?= htmlspecialchars($appointment['doctor_name']) ?> - <?= htmlspecialchars($appointment['specialty']) ?><br>
                                <?= htmlspecialchars($appointment['date']) ?> at <?= htmlspecialchars($appointment['time']) ?>
                                <br>
                                <a href="<?= base_url('index.php/patient/cancelAppointment/' . $appointment['id']) ?>" class="btn btn-danger">Cancel</a>
                                <a href="<?= base_url('index.php/patient/rescheduleAppointment/' . $appointment['id']) ?>" class="btn btn-warning">Reschedule</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>No upcoming appointments.</p>
                    <a href="<?= base_url('index.php/patient/bookAppointment') ?>" class="btn btn-primary">Book an Appointment</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <h4>Quick Actions</h4>
                <a href="<?= base_url('index.php/patient/profile') ?>" class="btn btn-info">Edit Profile</a>
                <a href="<?= base_url('index.php/patient/history') ?>" class="btn btn-secondary">View History</a>
                <a href="<?= base_url('index.php/patient/notifications') ?>" class="btn btn-warning">Notifications</a>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>