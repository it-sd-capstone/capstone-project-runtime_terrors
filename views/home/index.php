<?php
// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home | Appointment System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <?php include_once VIEW_PATH . '/partials/navigation.php'; ?>

    <h1 class="text-center">Welcome to the Appointment System</h1>
<!-- FullCalendar Integration -->
<h2 class="text-center">Upcoming Appointments</h2>
<div id="calendar"></div>
<!-- System Info -->
<h2>System Information:</h2>
<?php
    require_once __DIR__ . '/../../config/environment.php';
    ?>
<ul>
<li>Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></li>
<li>Environment: <?= Environment::detect() ?></li>
</ul>
<!-- Bootstrap & FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    { title: 'Test Event', start: '2025-04-20' } // Example event
                ]
            });
            calendar.render();
        });
</script>
</body>
</html>