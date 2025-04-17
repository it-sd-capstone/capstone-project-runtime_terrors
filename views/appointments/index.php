<!DOCTYPE html>
<html lang="en">
<head>
    <title>Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
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
    <div id="appointments-calendar"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('appointments-calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php foreach ($appointments as $appointment): ?>
                        {
                            title: "<?= htmlspecialchars($appointment['provider_name']) ?>",
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