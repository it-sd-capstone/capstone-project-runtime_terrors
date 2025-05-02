<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container mt-4">
    <div class="alert alert-info text-center">
        <h2 class="h4 mb-0">
            <i class="fas fa-calendar-alt text-primary"></i> Appointments Overview
        </h2>
        <p class="text-muted">Manage scheduled appointments and track availability.</p>
    </div>

    <!-- Appointments Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Upcoming Appointments</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($appointments)) : ?>
                            <?php foreach ($appointments as $appointment) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                    <td><?= date('g:i A', strtotime($appointment['start_time'])) ?></td>
                                    <td>
                                        <?php 
                                            $statusClass = match($appointment['status']) {
                                                'scheduled' => 'primary',
                                                'confirmed' => 'success',
                                                'canceled' => 'danger',
                                                'completed' => 'info',
                                                'no_show' => 'warning',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucfirst(htmlspecialchars($appointment['status'])) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <p class="mt-3"><i class="fas fa-exclamation-circle"></i> No upcoming appointments.</p>
                                    <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary mt-2">Schedule an Appointment</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5>Appointment Calendar</h5>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap & FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: "auto",
        events: '<?= base_url("index.php/provider/getProviderSchedules") ?>',
        editable: false,
        eventClick: function(info) {
            if (confirm("Do you want to remove this availability?")) {
                window.location.href = "<?= base_url('index.php/provider/deleteSchedule/') ?>" + info.event.id;
            }
        }
    });

    calendar.render();
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>