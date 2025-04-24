<h4>Appointments</h4>
<table class="table">
    <thead>
        <tr>
            <th>Patient</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($appointments as $appointment) : ?>
            <tr>
                <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                <td><?= htmlspecialchars($appointment['start_time']) ?></td>
                <td><?= htmlspecialchars($appointment['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>