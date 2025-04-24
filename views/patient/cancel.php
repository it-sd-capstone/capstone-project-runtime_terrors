<h4>Cancel Appointment</h4>
<p>Are you sure you want to cancel your appointment with Dr. <?= htmlspecialchars($appointment['provider_name']) ?>?</p>

<table class="table">
    <tr>
        <th>Service:</th>
        <td><?= htmlspecialchars($appointment['service_name']) ?></td>
    </tr>
    <tr>
        <th>Date:</th>
        <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
    </tr>
    <tr>
        <th>Time:</th>
        <td><?= htmlspecialchars($appointment['start_time']) ?></td>
    </tr>
    <tr>
        <th>Notes:</th>
        <td><?= htmlspecialchars($appointment['notes'] ?? 'N/A') ?></td>
    </tr>
</table>

<!-- Confirmation Form -->
<form method="POST" action="<?= base_url('index.php/patient/processCancel') ?>">
    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">

    <label>Reason for Cancellation:</label>
    <textarea name="reason" required></textarea>

    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
    <a href="<?= base_url('index.php/patient/index') ?>" class="btn btn-secondary">Go Back</a>
</form>