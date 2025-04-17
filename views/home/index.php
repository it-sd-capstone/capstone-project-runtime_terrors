<!DOCTYPE html>
<html lang="en">
<head>
<title>Appointment System</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<!-- Bootstrap & FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
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
<h1 class="text-center">Welcome to the Appointment System</h1>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3">
<div class="container-fluid">
<a class="navbar-brand" href="/index.php">Appointment System</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav">
<li class="nav-item">
<a class="nav-link" href="home">Home</a>
</li>
<li class="nav-item">
<a class="nav-link" href="appointments">Appointments</a>
</li>
<li class="nav-item">
<a class="nav-link" href="auth/login">Login</a>
</li>
</ul>
</div>
</div>
</nav>
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