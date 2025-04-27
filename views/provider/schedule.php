
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Schedule</title>

    <!-- Include Bootstrap & FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
</head>
<body>

<!-- Availability Form -->
<h4>Update Availability</h4>
<form method="POST" action="<?= base_url('index.php/provider/processUpdateAvailability') ?>">
    <label>Select Date:</label>
    <input type="date" name="availability_date" required>

    <label>Start Time:</label>
    <input type="time" name="start_time" required>

    <label>End Time:</label>
    <input type="time" name="end_time" required>

    <label>Available:</label>
    <select name="is_available">
        <option value="1">Available</option>
        <option value="0">Unavailable</option>
    </select>

    <button type="submit" class="btn btn-success">Update Availability</button>
</form>
<h4>Set Recurring Availability</h4>

< id="scheduleForm">
    <label>Day of Week:</label>
    <select name="day_of_week" required>
        <option value="1">Monday</option>
        <option value="2">Tuesday</option>
        <option value="3">Wednesday</option>
        <option value="4">Thursday</option>
        <option value="5">Friday</option>
        <option value="6">Saturday</option>
        <option value="7">Sunday</option>
    </select>

    <label>Start Time:</label>
    <input type="time" name="start_time" required>

    <label>End Time:</label>
    <input type="time" name="end_time" required>

    <label>Active:</label>
    <select name="is_active">
        <option value="1">Available</option>
        <option value="0">Unavailable</option>
    </select>

    <button type="submit" class="btn btn-success">Save Recurring Schedule</button>
</form>

<!--Calendar View (Added Below the Schedule Forms) -->
<h4>View Your Availability</h4>
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
