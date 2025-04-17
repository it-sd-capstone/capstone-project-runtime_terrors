<?php
// Debugging
if (!isset($available_slots)) {
    echo "<p>Error: \$available_slots is not set. Controller method may not be executing.</p>";
    $available_slots = []; // Prevent foreach error
}

// Debug the actual data
echo "<pre>Available slots: ";
var_dump($available_slots);
echo "</pre>";
// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
</head>
<body class="container mt-5">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="home">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="appointments">Appointments</a></li>
            <li class="nav-item"><a class="nav-link" href="auth/login">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="provider">Provider</a></li>
        </ul>
    </nav>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <h2>Book an Appointment</h2>

    <table class="table mt-3">
        <tr>
            <th>Provider</th>
            <th>Date</th>
            <th>Time</th>
            <th>Action</th>
        </tr>
        <?php foreach ($available_slots as $slot) : ?>
        <tr>
            <td><?= htmlspecialchars($slot['provider_name']) ?></td>
            <td><?= htmlspecialchars($slot['available_date']) ?></td>
            <td><?= htmlspecialchars($slot['start_time']) ?> - <?= htmlspecialchars($slot['end_time']) ?></td>
            <td>
                <form action="appointments/create" method="POST">
                    <input type="hidden" name="availability_id" value="<?= $slot['availability_id'] ?>">
                    <button type="submit" class="btn btn-primary">Book Now</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3 class="mt-4">Scheduled Appointments</h3>
    <div id="appointments-calendar" style="height: 500px; padding-bottom: 50px;"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Initializing appointments calendar...");
            var calendarEl = document.getElementById('appointments-calendar');
            
            if (!calendarEl) {
                console.error("Calendar element not found!");
                return;
            }
            
            var appointments = <?= json_encode($appointments ?? []) ?>;
            console.log("Appointment data:", appointments);
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 500,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                events: <?= json_encode(array_map(function($appt) {
                    return [
                        'title' => 'Appointment with ' . ($appt['provider_name'] ?? 'Provider'),
                        'start' => $appt['appointment_date'] . 'T' . $appt['start_time'],
                        'end' => $appt['appointment_date'] . 'T' . $appt['end_time'],
                        'color' => '#dc3545'
                    ];
                }, $appointments ?? [])) ?>
            });
            
            console.log("Rendering calendar...");
            calendar.render();
        });
    </script>
</body>
</html>