<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container provider-dashboard">
    <h2>Welcome, Dr. <?= htmlspecialchars($provider['first_name'] ?? 'Provider') ?>!</h2>
    <p>Manage appointments, availability, and patient interactions.</p>

    <div class="nav nav-tabs">
        <a class="nav-link active" href="<?= base_url('index.php/provider') ?>">Dashboard</a>
        <a class="nav-link" href="<?= base_url('index.php/provider/schedule') ?>">Schedule</a>
        <a class="nav-link" href="<?= base_url('index.php/provider/appointments') ?>">Appointments</a>
        <a class="nav-link" href="<?= base_url('index.php/provider/services') ?>">Services</a>
    </div>

    <!-- Upcoming Appointments -->
    <div class="card mt-3">
        <h4>Upcoming Appointments</h4>
        <?php foreach ($appointments as $appointment) : ?>
            <p>
                <?= htmlspecialchars($appointment['patient_name']) ?> - <?= htmlspecialchars($appointment['date']) ?> at <?= htmlspecialchars($appointment['time']) ?>
                <br>
                <a href="<?= base_url('index.php/provider/appointment/' . $appointment['id']) ?>" class="btn btn-info">View Details</a>
            </p>
        <?php endforeach; ?>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card">
            <h4>Quick Actions</h4>
            <a href="<?= base_url('index.php/provider/profile') ?>" class="btn btn-info">Edit Profile</a>
            <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-secondary">Manage Services</a>
            <a href="<?= base_url('index.php/provider/reports') ?>" class="btn btn-warning">View Reports</a>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>