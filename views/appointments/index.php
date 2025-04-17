<!DOCTYPE html>
<html lang="en">
<head>
    <title>Appointments</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">

    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="container mt-5">
    <h1 class="text-center">Manage Your Appointments</h1>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3">
        <a class="navbar-brand" href="appointments">Appointment System</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="auth/login">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="home">Home</a></li>
            </ul>
        </div>
    </nav>

    <!-- FullCalendar -->
    <h2>Upcoming Appointments</h2>
    <div id="calendar"></div>

    <!-- Appointments Table -->
    <h2>Appointment List</h2>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Appointment ID</th>
                <th>Patient</th>
                <th>Provider</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Assuming you retrieve appointments from a database
            require_once '../../models/Database.php';
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->query("SELECT * FROM appointments");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $row['appointment_id']; ?></td>
                    <td><?= $row['patient_id']; ?></td>
                    <td><?= $row['provider_id']; ?></td>
                    <td><?= $row['appointment_date'] . ' @ ' . $row['start_time']; ?></td>
                    <td><?= ucfirst($row['status']); ?></td>
                    <td>
                        <a href="/index.php?page=appointments/edit&id=<?= $row['appointment_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="/index.php?page=appointments/cancel&id=<?= $row['appointment_id']; ?>" class="btn btn-danger btn-sm">Cancel</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Bootstrap & FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php
                    $stmt = $conn->query("SELECT * FROM appointments");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "{ title: 'Appointment " . $row['appointment_id'] . "', start: '" . $row['appointment_date'] . "T" . $row['start_time'] . "' },";
                    }
                    ?>
                ]
            });
            calendar.render();
        });
    </script>
</body>
</html>