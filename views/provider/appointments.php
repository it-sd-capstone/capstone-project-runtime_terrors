<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container my-4">
    <h2>Upcoming Appointments</h2>

    <div class="card">
        <div class="card-header bg-light">
            <h5>Appointment List</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($appointment['start_time']) ?> - <?= htmlspecialchars($appointment['end_time']) ?></td>
                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['status']) ?></td>
                            <td>
                                <a href="<?= base_url('index.php/provider/editAppointment?id=' . $appointment['appointment_id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                                <form method="post" action="<?= base_url('index.php/provider/cancelAppointment') ?>" onsubmit="return confirm('Cancel this appointment?')">
                                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>