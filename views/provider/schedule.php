<?php include VIEW_PATH . '/partials/provider_header.php'; ?>
<style>
/* Calendar styles */
.calendar-container {
  padding: 1.5rem;
  min-height: 650px;
}

.fc-event {
  cursor: pointer;
  padding: 2px 4px;
}

.fc-event-title {
  font-weight: bold;
}

.fc-daygrid-day-number {
  font-weight: bold;
}

/* Make current day highlighted */
.fc-day-today {
  background-color: rgba(0, 123, 255, 0.1) !important;
}
</style>

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
                                <option value="7">Sunday</option>
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
    
    // Debug provider ID
    console.log("Provider ID being used:", <?= json_encode($provider_id ?? $_SESSION['user_id'] ?? 'undefined') ?>);

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 650,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: true,
        events: "<?= base_url('index.php/provider/getProviderSchedules') ?>",
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
        eventClick: function(info) {
            if (confirm("Do you want to remove this availability?")) {
                const eventId = info.event.id;
                const eventType = info.event.extendedProps?.type || 'regular';
                
                let endpoint = "<?= base_url('index.php/provider/deleteSchedule/') ?>";
                endpoint += eventId;
                
                fetch(endpoint, { 
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        type: eventType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        info.event.remove();
                        alert("Availability removed successfully!");
                    } else {
                        alert("Failed to remove availability: " + (data.message || "Unknown error"));
                    }
                })
                .catch(error => {
                    console.error("Error removing availability:", error);
                    alert("Error removing availability. Please try again.");
                });
            }
        },
        eventDrop: function(info) {
            updateAvailability(info.event);
        },
        eventResize: function(info) {
            updateAvailability(info.event);
        },
        loading: function(isLoading) {
            if (isLoading) {
                console.log("Calendar is loading events...");
            } else {
                console.log("Calendar finished loading events");
            }
        }
    });

    calendar.render();
    
    // Debug: Log event count after rendering
    setTimeout(function() {
        console.log("Total events displayed:", calendar.getEvents().length);
    }, 1000);

    // Function to update availability on drag/resize
    function updateAvailability(event) {
        const eventId = event.id;
        const eventType = event.extendedProps?.type || 'regular';
        
        // Format start and end times
        const startStr = event.start.toISOString();
        const endStr = event.end ? event.end.toISOString() : 
                      new Date(event.start.getTime() + 3600000).toISOString(); // Default 1 hour
        
        const updatedData = {
            id: eventId,
            type: eventType,
            date: startStr.split('T')[0],
            start_time: startStr.split('T')[1].substring(0, 5),
            end_time: endStr.split('T')[1].substring(0, 5)
        };
        
        console.log("Updating event:", updatedData);

        fetch("<?= base_url('index.php/provider/updateSchedule') ?>", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updatedData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Availability updated successfully");
            } else {
                alert("Failed to update availability: " + (data.message || "Unknown error"));
                calendar.refetchEvents(); // Reset to original state
            }
        })
        .catch(error => {
            console.error("Error updating availability:", error);
            alert("Error updating availability. Please try again.");
            calendar.refetchEvents(); // Reset to original state
        });
    }
    
    // Form submission handlers
    // Handle availability form submission
    const availabilityForm = document.querySelector('form[action*="processUpdateAvailability"]');
    if (availabilityForm) {
        availabilityForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Availability updated successfully!");
                    calendar.refetchEvents(); // Refresh calendar
                    this.reset(); // Reset form
                } else {
                    alert("Failed to update availability: " + (data.message || "Unknown error"));
                }
            })
            .catch(error => {
                console.error("Error updating availability:", error);
                alert("Error updating availability. Please try again.");
            });
        });
    }
    
    // Handle recurring schedule form submission
    const recurringForm = document.querySelector('form[action*="processRecurringSchedule"]');
    if (recurringForm) {
        recurringForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Recurring schedule created successfully!");
                    calendar.refetchEvents(); // Refresh calendar
                    this.reset(); // Reset form
                } else {
                    alert("Failed to create recurring schedule: " + (data.message || "Unknown error"));
                }
            })
            .catch(error => {
                console.error("Error creating recurring schedule:", error);
                alert("Error creating recurring schedule. Please try again.");
            });
        });
    }
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>