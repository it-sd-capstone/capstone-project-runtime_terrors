<?php include VIEW_PATH . '/partials/provider_header.php'; ?> <!--Ensures styles and navigation are included -->

<div class="container">
    <h2>Welcome, Dr. <?= htmlspecialchars($provider['first_name'] ?? 'Provider') ?>!</h2>

    <!-- Navigation Tabs -->
    <div class="nav nav-tabs">
        <a class="nav-link active" href="<?= base_url('index.php/provider') ?>">Dashboard</a>
        <a class="nav-link" href="<?= base_url('index.php/provider/schedule') ?>">Manage Schedule</a>
        <a class="nav-link" href="<?= base_url('index.php/provider/appointments') ?>">View Appointments</a>
        <a class="nav-link" href="<?= base_url('index.php/provider/services') ?>">Manage Services</a>
    </div>

    <!-- Upcoming Appointments -->
    <h4>Upcoming Appointments</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Patient</th>
                <th>Date</th>
                <th>Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($appointments)) : ?>
                <?php foreach ($appointments as $appointment) : ?>
                    <tr>
                        <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($appointment['start_time']) ?></td>
                        <td>
                            <a href="<?= base_url('index.php/provider/viewAppointment/' . htmlspecialchars($appointment['appointment_id'] ?? '')) ?>" class="btn btn-info">View</a>
                            <a href="<?= base_url('index.php/provider/rescheduleAppointment/' . htmlspecialchars($appointment['appointment_id'] ?? '')) ?>" class="btn btn-warning">Reschedule</a>
                            <form action="<?= base_url('index.php/provider/cancelAppointment') ?>" method="POST" style="display:inline;">
                                <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($appointment['appointment_id'] ?? '') ?>">
                                <button type="submit" class="btn btn-danger">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4">No upcoming appointments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include VIEW_PATH . '/partials/provider_footer.php'; ?> <!-- Includes footer for clean page layout -->