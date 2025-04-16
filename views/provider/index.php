<!DOCTYPE html>
<html lang="en">
<head>
    <title>Provider Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
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

    <h2 class="text-center">Provider Dashboard</h2>

    <!-- Upload Availability Form -->
    <h3>Upload Availability</h3>
    <form action="provider/upload_availability" method="POST">
        <label>Available Date:</label>
        <input type="date" name="available_date" required class="form-control">
        
        <label>Start Time:</label>
        <input type="time" name="start_time" required class="form-control">
        
        <label>End Time:</label>
        <input type="time" name="end_time" required class="form-control">
        
        <button type="submit" class="btn btn-success mt-3">Upload Availability</button>
    </form>

    <!-- FullCalendar for Providers -->
    <h3 class="mt-4">Availability & Appointments Calendar</h3>
    <div id="provider-calendar"></div>

    <!-- FullCalendar Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('provider-calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php foreach ($provider_availability as $availability): ?>
                        {
                            title: "Available",
                            start: "<?= htmlspecialchars($availability['available_date']) ?>",
                            description: "<?= htmlspecialchars($availability['start_time']) ?> - <?= htmlspecialchars($availability['end_time']) ?>",
                            backgroundColor: "#28a745" // Green for available
                        },
                    <?php endforeach; ?>
                    <?php foreach ($appointments as $appointment): ?>
                        {
                            title: "Booked: <?= htmlspecialchars($appointment['patient_name']) ?>",
                            start: "<?= htmlspecialchars($appointment['appointment_date']) ?>",
                            backgroundColor: "#dc3545" // Red for booked
                        },
                    <?php endforeach; ?>
                ]
            });
            calendar.render();
        });
    </script>
</body>
</html>