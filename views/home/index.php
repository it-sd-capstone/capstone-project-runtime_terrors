<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment System</title>
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
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        ul {
            padding-left: 25px;
        }
        li {
            margin-bottom: 8px;
        }
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Appointment System</h1>
        <p>This is the home page of the application.</p>
        
        <h2>Available Routes:</h2>
        <ul>
            <li><a href="bootstrap_test.php">Bootstrap Test</a></li>
            <li><a href="test/env">Environment Test</a></li>
            <li><a href="appointments">Appointments</a></li>
            <li><a href="auth/login">Login</a></li>
        </ul>
        
        <h2>System Information:</h2>
        <ul>
            <li>Environment: <?= get_environment() ?></li>
            <li>Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></li>
            <li>View Structure: Using subdirectory organization</li>
        </ul>
    </div>
</body>
</html>
