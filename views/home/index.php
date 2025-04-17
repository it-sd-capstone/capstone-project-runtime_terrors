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

    <h1 class="text-center">Welcome to the Appointment System</h1>
</body>
</html>