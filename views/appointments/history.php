<?php include '../layout/partials/header.php'; ?>
<?php include '../layout/partials/navigation.php'; ?>

<div class="container mt-4">
    <h2>My Appointments</h2>
    
    <table class="table">
        <thead>
            <tr><th>Date</th><th>Time</th><th>Provider</th><th>Service</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $appointment): ?>
            <tr>
                <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                <td><?= htmlspecialchars($appointment['start_time']) ?></td>
                <td><?= htmlspecialchars($appointment['provider_name']) ?></td>
                <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                <td><?= ucfirst($appointment['status']) ?></td>
                <td>
                    <?php if ($appointment['status'] === 'scheduled'): ?>
                        <form action="/patient/cancelAppointment" method="POST" style="display:inline;">
                            <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                            <button type="submit" class="btn btn-danger">Cancel</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php include '../layout/partials/footer.php'; ?>