<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container patient-dashboard">
    <div class="row">
        <div class="col-md-12">
            <h2>Patient Dashboard</h2>
            <p>Welcome to your healthcare portal.</p>
        </div>
    </div>

    <!-- Profile Section -->
    <div class="row">
        <div class="col-md-3">
            <div class="card profile-card">
                <img src="<?= $patient['profile_picture'] ?? 'default-avatar.png' ?>" alt="Profile Picture">
                <h4><?= htmlspecialchars($patient['name'] ?? 'Patient') ?></h4>
                <p>Patient</p>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="col-md-9">
            <div class="card">
                <h4>Upcoming Appointments</h4>
                <?php if (!empty($appointments)) : ?>
                    <ul>
                        <?php foreach ($appointments as $appointment) : ?>
                            <li>
                                Dr. <?= htmlspecialchars($appointment['doctor_name']) ?> - <?= htmlspecialchars($appointment['specialty']) ?>
                                <br>
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
    </div>

    <!-- Appointment Statistics -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card stats-card">
                <h5>Appointment Statistics (Last 30 Days)</h5>
                <p>Appointments Scheduled: <?= $stats['appointments_scheduled'] ?? 0 ?> (+<?= $stats['increase'] ?? 0 ?>)</p>
                <p>Average Wait Time: <?= $stats['wait_time'] ?? 'N/A' ?> mins</p>
                <a href="<?= base_url('index.php/patient/stats') ?>" class="btn btn-info">View Details</a>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>