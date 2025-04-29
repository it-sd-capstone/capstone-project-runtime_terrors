<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            
            <?php
            $selectedProviderId = isset($provider) && isset($provider['user_id']) ? $provider['user_id'] : null;
            $selectedProviderName = isset($provider) ? 
                htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) : 
                'Select a provider';
            ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0">Book Appointment with <?= $selectedProviderName ?></h2>
                </div>
                <div class="card-body">
                    <!-- Calendar for Available Slots -->
                    <div id="calendar" class="mb-4"></div>

                    <!-- Appointment Booking Form -->
                    <form id="bookForm" method="POST" action="<?= base_url('index.php/patient/processBooking') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="provider_id" value="<?= $selectedProviderId ?>">
                        
                        <!-- If no provider is selected, show a dropdown to select one -->
                        <?php if (!$selectedProviderId): ?>
                        <div class="mb-3">
                            <label for="provider_select" class="form-label">Select Provider</label>
                            <select class="form-control" id="provider_id" name="provider_id" required>
                                <option value="">-- Select a Provider --</option>
                                <?php foreach ($providers as $p): ?>
                                    <option value="<?= $p['user_id'] ?>" 
                                        <?= (isset($provider) && $provider['user_id'] == $p['user_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="service_id" class="form-label">Select Service:</label>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">-- Select a Service --</option>
                                    <?php foreach ($services as $service) : ?>
                                        <option value="<?= $service['service_id'] ?>">
                                            <?= htmlspecialchars($service['name']) ?> ($<?= htmlspecialchars($service['price']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="appointment_date" class="form-label">Select Date:</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="start_time" class="form-label">Select Time:</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">Confirm Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

<script>
document.getElementById("bookForm").addEventListener("submit", function(event) {
    event.preventDefault();
    var selectedDate = document.getElementById("appointment_date").value;
    var selectedTime = document.getElementById("start_time").value;
    var selectedProvider = document.querySelector('input[name="provider_id"]').value;
    
    if (!selectedDate || !selectedTime) {
        alert("Please select both date and time.");
        return;
    }
    
    fetch("<?= base_url('index.php/patient/checkAvailability') ?>", {
        method: "POST",
        body: JSON.stringify({ 
            provider_id: selectedProvider, 
            date: selectedDate, 
            time: selectedTime 
        }),
        headers: { "Content-Type": "application/json" }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.available) {
            alert("Selected time is unavailable. Please pick a different slot.");
        } else {
            alert("Booking confirmed! Proceeding...");
            document.getElementById("bookForm").submit();
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Server error. Try again later.");
    });
});

// FullCalendar Initialization
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var providerId = document.querySelector('input[name="provider_id"]').value;
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: "<?= base_url('index.php/scheduler/getAvailableSlots') ?>?provider_id=" + providerId,
        editable: false,
        eventClick: function(info) {
            if (info.event.extendedProps.is_booked) {
                alert("This slot is already booked.");
                return;
            }
            
            // Automatically fill the booking form with selected event details
            document.getElementById("appointment_date").value = info.event.start.toISOString().split('T')[0];
            document.getElementById("start_time").value = info.event.start.toISOString().split('T')[1].substring(0, 5);
            
            // Scroll to the form
            document.getElementById("bookForm").scrollIntoView({ behavior: 'smooth' });
            
            // Optional: Ask for confirmation
            if (confirm("Would you like to book this appointment?")) {
                // You can either submit the form here or let the user review the details first
                // window.location.href = "<?= base_url('index.php/patient/bookAppointment/') ?>" + info.event.id;
            }
        },
        eventContent: function(info) {
            return {
                html: '<div class="fc-event-time">' + 
                      (info.timeText || '') + 
                      '</div><div class="fc-event-title">' + 
                      (info.event.extendedProps.is_booked ? 
                      '<span class="badge bg-danger">Booked</span>' : 
                      '<span class="badge bg-success">Available</span>') + 
                      '</div>'
            };
        }
    });
    
    calendar.render();
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>