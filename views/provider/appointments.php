<h4>Appointments</h4>
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
                <td><?= htmlspecialchars($appointment['date']) ?></td>
                <td><?= htmlspecialchars($appointment['time']) ?></td>
                <td>
                    <a href="<?= base_url('index.php/provider/viewAppointment/' . $appointment['id']) ?>" class="btn btn-info">View</a>
                    <a href="<?= base_url('index.php/provider/rescheduleAppointment/' . $appointment['id']) ?>" class="btn btn-warning">Reschedule</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>