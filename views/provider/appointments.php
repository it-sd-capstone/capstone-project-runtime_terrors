<?php
// Assuming this is at the top of your file where PHP processing happens
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments</title>
<!-- Include Bootstrap & FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
</head>
<body>
<h1>Appointments</h1>
<table class="table">
<thead>
<tr>
<th>Patient</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php foreach ($appointments as $appointment) : ?>
<tr>
<td><?= htmlspecialchars($appointment['patient_name']) ?></td>
<td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
<td><?= htmlspecialchars($appointment['start_time']) ?></td>
<td><?= htmlspecialchars($appointment['status']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
 
    <!--Calendar View (Added Below the Schedule Forms) -->
<h4>Calendar</h4>
<div id="calendar"></div>
 
    <!-- FullCalendar Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: "auto", // Adjust height dynamically
            contentHeight: 200, // Limit calendar height
            events: '<?= base_url("index.php/provider/getProviderSchedules") ?>', // Fetch availability dynamically
            editable: true,
            eventClick: function(info) {
                if (confirm("Do you want to remove this availability?")) {
                    window.location.href = "<?= base_url('index.php/provider/deleteSchedule/') ?>" + info.event.id;
                }
            }
        });
        calendar.render();
    });
</script>
</body>
</html>