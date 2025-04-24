<h4>Manage Your Schedule</h4>
<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
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

<form id="scheduleForm">
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