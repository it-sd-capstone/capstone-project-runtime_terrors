<!-- Patient Dashboard Content -->
<h2>Welcome back, <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>!</h2>

<div class="dashboard">
    <div class="appointment-section">
        <h3>Upcoming Appointments</h3>
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
            </tbody>
        </table>
    </div>

    <div class="appointment-stats">
        <h3>Your Appointment Stats</h3>
        <p>**Total Appointments:** <?= count($upcomingAppointments) + count($pastAppointments) ?></p>
        <p>**Completed:** <?= count($pastAppointments) ?></p>
        <p>**Upcoming:** <?= count($upcomingAppointments) ?></p>
    </div>
</div>

<!-- JavaScript for Real-Time Updates -->
<script>
setInterval(() => {
    fetch("<?= base_url('index.php/patient/fetchAppointments') ?>")
        .then(response => response.json())
        .then(data => {
            document.querySelector(".appointment-stats").innerHTML = `
                <h3>Your Appointment Stats</h3>
                <p>**Total Appointments:** ${data.total}</p>
                <p>**Completed:** ${data.completed}</p>
                <p>**Upcoming:** ${data.upcoming}</p>
            `;
        });
}, 60000); // Refresh every 60 seconds
</script>