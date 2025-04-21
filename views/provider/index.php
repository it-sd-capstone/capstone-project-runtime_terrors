<?php
// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Provider Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
</head>
<body class="container mt-5">
    <?php include_once VIEW_PATH . '/partials/navigation.php'; ?>

    <h2 class="text-center">Provider Dashboard</h2>

    <h3>Upload Availability</h3>
    <form action="<?= base_url('index.php/provider/upload_availability') ?>" method="POST">
        <label>Available Date:</label>
        <input type="date" name="available_date" required class="form-control">
        
        <label>Start Time:</label>
        <input type="time" name="start_time" required class="form-control">
        
        <label>End Time:</label>
        <input type="time" name="end_time" required class="form-control">
        
        <button type="submit" class="btn btn-success mt-3">Upload Availability</button>
    </form>

    <h3 class="mt-4">Availability & Appointments Calendar</h3>
    <div id="provider-calendar" style="padding-bottom: 50px;"></div>
    <!-- <div class="mt-4">
        <h4>Debug Data</h4>
        <strong>Provider Availability:</strong>
        <pre><?php print_r($provider_availability); ?></pre>
        
        <strong>Appointments:</strong>
        <pre><?php print_r($appointments); ?></pre>
    </div> -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('provider-calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php foreach ($provider_availability as $availability): ?>
                        {
                            title: "Available",
                            start: "<?= htmlspecialchars($availability['available_date']) ?>T<?= htmlspecialchars($availability['start_time']) ?>",
                            end: "<?= htmlspecialchars($availability['available_date']) ?>T<?= htmlspecialchars($availability['end_time']) ?>",
                            backgroundColor: "#28a745"
                        },
                    <?php endforeach; ?>
                    <?php foreach ($appointments as $appointment): ?>
                        {
                            title: "Booked: <?= htmlspecialchars($appointment['patient_name'] ?? 'Patient') ?>",
                            start: "<?= htmlspecialchars($appointment['appointment_date']) ?>T<?= htmlspecialchars($appointment['start_time']) ?>",
                            end: "<?= htmlspecialchars($appointment['appointment_date']) ?>T<?= htmlspecialchars($appointment['end_time']) ?>",
                            backgroundColor: "#dc3545"
                        },
                    <?php endforeach; ?>
                ]
            });
            calendar.render();
        });
    </script>
</body>
</html>