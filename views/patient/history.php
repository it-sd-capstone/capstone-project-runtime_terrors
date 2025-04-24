<h4>Appointment History</h4>

<?php if (!empty($pastAppointments) || !empty($upcomingAppointments)) : ?>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Provider</th>
                <th>Service</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($upcomingAppointments as $appointment) : ?>
                <tr>
                    <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                    <td>Dr. <?= htmlspecialchars($appointment['provider_name']) ?></td>
                    <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                    <td><?= htmlspecialchars($appointment['status']) ?></td>
                    <td>
                        <a href="<?= base_url('index.php/patient/reschedule/' . $appointment['appointment_id']) ?>" class="btn btn-warning">Reschedule</a>
                        <a href="<?= base_url('index.php/patient/cancel/' . $appointment['appointment_id']) ?>" class="btn btn-danger">Cancel</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($pastAppointments as $appointment) : ?>
                <tr>
                    <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                    <td>Dr. <?= htmlspecialchars($appointment['provider_name']) ?></td>
                    <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                    <td>Completed</td>
                    <td>-</td> <!-- No actions for completed appointments -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>No appointment history available.</p>
<?php endif; ?>