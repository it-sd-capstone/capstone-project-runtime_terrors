<!DOCTYPE html>
<html lang="en">
<head>
    <title>Provider Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="/index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="/index.php?page=appointments">Appointments</a></li>
            <li class="nav-item"><a class="nav-link" href="/index.php?page=auth/login">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="/index.php?page=provider">Provider</a></li>
        </ul>
    </nav>

    <h2 class="text-center">Provider Dashboard</h2>
    
    <h3>Upload Availability</h3>
    <form action="/index.php?page=provider&action=upload_availability" method="POST">
        <label>Available Date:</label>
        <input type="date" name="available_date" required class="form-control">
        
        <label>Start Time:</label>
        <input type="time" name="start_time" required class="form-control">
        
        <label>End Time:</label>
        <input type="time" name="end_time" required class="form-control">
        
        <button type="submit" class="btn btn-success mt-3">Upload Availability</button>
    </form>
</body>
</html>