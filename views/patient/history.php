<h4>Appointment History</h4>
<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Provider</th>
            <th>Service</th>
            <th>Status</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pastAppointments as $appointment) : ?>
            <tr>
                <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                <td>Dr. <?= htmlspecialchars($appointment['provider_name']) ?></td>
                <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                <td><?= htmlspecialchars($appointment['status']) ?></td>
                <td><?= htmlspecialchars($appointment['notes']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>