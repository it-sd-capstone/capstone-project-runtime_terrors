<h2>Welcome, Dr. <?= htmlspecialchars($provider['first_name'] ?? 'Provider') ?>!</h2>

<div class="nav nav-tabs">
    <a class="nav-link active" href="<?= base_url('index.php/provider') ?>">Dashboard</a>
    <a class="nav-link" href="<?= base_url('index.php/provider/schedule') ?>">Manage Schedule</a>
    <a class="nav-link" href="<?= base_url('index.php/provider/appointments') ?>">View Appointments</a>
    <a class="nav-link" href="<?= base_url('index.php/provider/services') ?>">Manage Services</a>
</div>

<!-- Upcoming Appointments -->
<h4>Upcoming Appointments</h4>
<table class="table">
    <thead>
        <tr>
            <th>Patient</th>
            <th>Date</th>
            <th>Time</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($appointments as $appointment) : ?>
            <tr>
                <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                <td><?= htmlspecialchars($appointment['start_time']) ?></td>
                <td>
                    <a href="<?= base_url('index.php/provider/appointment/' . $appointment['id']) ?>" class="btn btn-info">View</a>
                    <a href="<?= base_url('index.php/provider/reschedule/' . $appointment['id']) ?>" class="btn btn-warning">Reschedule</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>