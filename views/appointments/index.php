<!DOCTYPE html>
<html lang="en">
<head>
    <title>Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
</head>
<body class="container mt-5">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="/index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="appointments">Appointments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="auth/login">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="provider">Provider</a>
            </li>
        </ul>
    </nav>

    <h2>Appointments</h2>

    <form action="/index.php?page=appointments&action=create" method="POST">
        <input type="text" name="patient_name" placeholder="Patient Name" required>
        <input type="text" name="provider_name" placeholder="Provider Name" required>
        <input type="datetime-local" name="appointment_date" required>
        <button type="submit" class="btn btn-success">Create Appointment</button>
    </form>

    <table class="table mt-3">
        <tr>
            <th>Patient</th>
            <th>Provider</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($appointments as $appointment) : ?>
        <tr>
            <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
            <td><?= htmlspecialchars($appointment['provider_name']) ?></td>
            <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
            <td><?= htmlspecialchars($appointment['status']) ?></td>
            <td>
                <form action="/index.php?page=appointments&action=update" method="POST">
                    <input type="hidden" name="id" value="<?= $appointment['id'] ?>">
                    <select name="status">
                        <option value="Scheduled">Scheduled</option>
                        <option value="Completed">Completed</option>
                        <option value="Canceled">Canceled</option>
                    </select>
                    <button type="submit" class="btn btn-warning">Update</button>
                </form>
                <form action="/index.php?page=appointments&action=delete" method="POST">
                    <input type="hidden" name="id" value="<?= $appointment['id'] ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>