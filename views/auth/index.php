<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login | Appointment System</title>
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

    <h2 class="text-center">Login</h2>

    <form action="/index.php?page=auth/login" method="post">
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</body>
</html>