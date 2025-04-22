<?php include '../layout/partials/header.php'; ?>
<?php include '../layout/partials/navigation.php'; ?>

<div class="container mt-4">
    <h2>Manage Your Availability</h2>

    <!-- Add Recurring Schedule Form -->
    <form id="scheduleForm" action="/provider/addRecurringSchedule" method="POST">
        <label for="day_of_week">Day of Week:</label>
        <select name="day_of_week" required>
            <option value="0">Sunday</option>
            <option value="1">Monday</option>
            <option value="2">Tuesday</option>
            <option value="3">Wednesday</option>
            <option value="4">Thursday</option>
            <option value="5">Friday</option>
            <option value="6">Saturday</option>
        </select>

        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" required>

        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" required>

        <button type="submit" class="btn btn-success mt-3">Add Schedule</button>
    </form>

    <hr>

    <!-- Display Existing Schedules -->
    <h3>Current Availability</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Day</th><th>Start Time</th><th>End Time</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody id="scheduleList">
            <?php foreach ($recurringSchedules as $schedule) : ?>
                <tr>
                    <td><?= htmlspecialchars($schedule['day_of_week']) ?></td>
                    <td><?= htmlspecialchars($schedule['start_time']) ?></td>
                    <td><?= htmlspecialchars($schedule['end_time']) ?></td>
                    <td><?= $schedule['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td>
                        <button class="btn btn-danger deleteSchedule" data-id="<?= $schedule['schedule_id'] ?>">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- FullCalendar Integration -->
    <div id="calendar"></div>
</div>

<script>
// Validate time selection before submitting
document.getElementById("scheduleForm").addEventListener("submit", function(event) {
    let startTime = document.querySelector("input[name='start_time']").value;
    let endTime = document.querySelector("input[name='end_time']").value;

    if (endTime <= startTime) {
        alert("End time must be later than start time!");
        event.preventDefault();
    }
});

// AJAX to handle schedule deletion
document.querySelectorAll(".deleteSchedule").forEach(button => {
    button.addEventListener("click", function() {
        let scheduleId = this.getAttribute("data-id");

        fetch("/provider/deleteRecurringSchedule", {
            method: "POST",
            body: JSON.stringify({ schedule_id: scheduleId }),
            headers: { "Content-Type": "application/json" }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.closest("tr").remove();
            } else {
                alert("Error deleting schedule!");
            }
        });
    });
});

// Initialize FullCalendar.js
document.addEventListener("DOMContentLoaded", function() {
    var calendarEl = document.getElementById("calendar");

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "timeGridWeek",
        events: "/api/getProviderSchedules",
        selectable: true,
        select: function(info) {
            let confirmed = confirm(`Do you want to set availability on ${info.startStr}?`);
            if (confirmed) {
                window.location.href = `/provider/addRecurringSchedule?date=${info.startStr}`;
            }
        }
    });

    calendar.render();
});
</script>

<?php include '../layout/partials/footer.php'; ?>