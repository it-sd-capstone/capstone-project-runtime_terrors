<!DOCTYPE html>
<html lang="en">
<head>
    <title>Frontend Test Page</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">

    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="container mt-5">
    <h1 class="text-center">Bootstrap & FullCalendar Test</h1>

    <!-- Test Bootstrap Button -->
    <button class="btn btn-primary mb-3" onclick="alert('Bootstrap works!')">Test Bootstrap Button</button>

    <!-- FullCalendar Example -->
    <h2>Calendar Test</h2>
    <div id="calendar"></div>

    <!-- Bootstrap & FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    { title: 'Sample Event', start: '2025-04-20' }
                ]
            });
            calendar.render();
        });
    </script>
</body>
</html>