<h4>Appointment History</h4>
<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Provider</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pastAppointments as $appointment) : ?>
            <tr>
                <td><?= htmlspecialchars($appointment['date']) ?></td>
                <td>Dr. <?= htmlspecialchars($appointment['doctor_name']) ?></td>
                <td><?= htmlspecialchars($appointment['notes']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>