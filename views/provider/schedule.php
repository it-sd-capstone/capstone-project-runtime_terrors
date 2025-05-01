<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container mt-4">
    <!-- Title Section -->
    <div class="alert alert-info text-center">
        <h2 class="h4 mb-0">
            <i class="fas fa-calendar-alt text-primary"></i> Manage Your Schedule
        </h2>
        <p class="text-muted">Set availability and view upcoming appointments.</p>
    </div>

    <!-- Availability Update Form -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5>Update Availability</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('index.php/provider/processUpdateAvailability') ?>">
                        <div class="mb-3">
                            <label>Select Date:</label>
                            <input type="date" class="form-control" name="availability_date" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Start Time:</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>End Time:</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Availability:</label>
                            <select class="form-select" name="is_available">
                                <option value="1">Available</option>
                                <option value="0">Unavailable</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Availability</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recurring Schedule Form -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5>Set Recurring Availability</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('index.php/provider/processRecurringSchedule') ?>">
                        <div class="mb-3">
                            <label>Day of Week:</label>
                            <select class="form-select" name="day_of_week" required>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                                <option value="0">Sunday</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Start Time:</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>End Time:</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Active:</label>
                            <select class="form-select" name="is_active">
                                <option value="1">Available</option>
                                <option value="0">Unavailable</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Save Recurring Schedule</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5>View Your Availability</h5>
            </div>
            <div class="card-body calendar-container">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap & FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var selectedDuration = 30; // Default service duration

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 650,
        aspectRatio: 1.35,
        contentHeight: "auto",
        editable: true,
        eventSources: [
            {
                url: "<?= base_url('index.php/provider/getProviderSchedules') ?>",
                method: "GET",
                failure: function() {
                    alert("Failed to load provider schedules.");
                }
            }
        ],
        eventResize: function(info) { 
            updateAvailability(info.event);
        },
        eventDrop: function(info) { 
            updateAvailability(info.event);
        },
        eventClick: function(info) { 
            if (confirm("Do you want to remove this availability?")) {
                fetch("<?= base_url('index.php/provider/deleteSchedule/') ?>" + info.event.id, { method: "POST" })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            info.event.remove();
                        } else {
                            alert("Failed to remove availability.");
                        }
                    });
            }
        }
    });

    // Debug provider availability fetching
    fetch("<?= base_url('index.php/provider/getProviderSchedules') ?>")
    .then(response => response.json())
    .then(data => {
        console.log("Provider Schedules Data:", data);
        if (data.length === 0) {
            console.warn("No provider schedules found! Check backend response.");
        }
        calendar.addEventSource(data); // Ensure event source loads correctly
    })
    .catch(error => console.error("Error fetching provider schedules:", error));
    
    // Debug available appointments fetching
    fetch("<?= base_url('index.php/provider/getAvailableSlots') ?>?provider_id=<?= $provider_id ?>&service_duration=" + selectedDuration)
    .then(response => response.json())
    .then(data => {
        console.log("Filtered Available Slots:", data);
        if (data.length === 0) {
            console.warn("No available slots found! Check backend response.");
        }
    })
    .catch(error => console.error("Error fetching available slots:", error));
    calendar.render();

    function updateAvailability(event) {
        var updatedData = {
            id: event.id,
            date: event.start.toISOString().split('T')[0],
            start_time: event.start.toISOString().split('T')[1].substring(0, 5),
            end_time: event.end ? event.end.toISOString().split('T')[1].substring(0, 5) : event.start.toISOString().split('T')[1].substring(0, 5)
        };

        fetch("<?= base_url('index.php/provider/updateSchedule') ?>", {
            method: "POST",
            body: JSON.stringify(updatedData),
            headers: { "Content-Type": "application/json" }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert("Failed to update availability.");
            }
        });
    }
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>