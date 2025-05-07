<?php include VIEW_PATH . '/partials/header.php'; ?>
<div class="container mt-4">
    <h4>Book an Appointment</h4>
    <?php
    $selectedProviderId = $_GET['provider_id'] ?? null;
    $selectedProviderName = '';
    if ($selectedProviderId) {
        foreach ($providers as $p) {
            if ($p['user_id'] == $selectedProviderId) {
                $selectedProviderName = htmlspecialchars($p['first_name'] . ' ' . $p['last_name']);
                break;
            }
        }
    }
    ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5>Book Appointment <?= $selectedProviderName ? "with $selectedProviderName" : "" ?></h5>
        </div>
        <div class="card-body">
            <form id="bookForm" method="POST" action="<?= base_url('index.php/patient/processBooking') ?>">
                <?= csrf_field() ?>
                
                <!-- Provider Selection Dropdown -->
                <div class="mb-3">
                    <label for="provider_id" class="form-label">Select Provider:</label>
                    <select id="provider_id" name="provider_id" class="form-select" required onchange="updateCalendar()">
                        <option value="">-- Select a Provider --</option>
                        <?php foreach ($providers as $p) : ?>
                            <option value="<?= $p['user_id'] ?>" <?= ($selectedProviderId == $p['user_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- FullCalendar for Available Slots -->
                <div id="calendar" class="mb-4"></div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="service_id" class="form-label">Select Service:</label>
                        <select id="service_id" name="service_id" class="form-select" required>
                            <option value="">-- Select a Service --</option>
                            <?php foreach ($services as $service) : ?>
                                <option value="<?= $service['service_id'] ?>" data-duration="<?= $service['duration'] ?? 30 ?>">
                                    <?= htmlspecialchars($service['name']) ?> ($<?= htmlspecialchars($service['price']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="appointment_date" class="form-label">Select Date:</label>
                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" required readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="start_time" class="form-label">Select Time:</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" required readonly>
                    </div>
                    </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Appointment Type:</label>
                    <select class="form-select" id="type" name="type">
                        <option value="in_person">In-Person</option>
                        <option value="telehealth">Telehealth</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (optional):</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">Book Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script>
// Debug form submission
document.getElementById("bookForm").addEventListener("submit", function(event) {
    console.log("Form submitted with data:", {
        provider_id: document.getElementById("provider_id").value,
        service_id: document.getElementById("service_id").value,
        appointment_date: document.getElementById("appointment_date").value,
        start_time: document.getElementById("start_time").value,
        type: document.getElementById("type").value,
        notes: document.getElementById("notes").value
    });
});

// Function to update the calendar when provider changes
function updateCalendar() {
    var calendarEl = document.getElementById('calendar');
    var providerId = document.getElementById('provider_id').value;
    document.getElementById("appointment_date").value = "";
    document.getElementById("start_time").value = "";
    
    if (!providerId) {
        calendarEl.innerHTML = '<p class="text-center text-muted">Select a provider to view availability.</p>';
        return;
    }
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        timeZone: 'local', // Use local timezone
        events: "<?= base_url('index.php/api/getAvailableSlots') ?>?provider_id=" + providerId,
        eventClick: function(info) {
            if (info.event.extendedProps.type !== 'availability') {
                alert("This slot is not available for booking.");
                return;
            }
            
            // Fix time zone issue by using the date object methods
            var eventDate = info.event.start;
            var year = eventDate.getFullYear();
            var month = (eventDate.getMonth() + 1).toString().padStart(2, '0');
            var day = eventDate.getDate().toString().padStart(2, '0');
            var hours = eventDate.getHours().toString().padStart(2, '0');
            var minutes = eventDate.getMinutes().toString().padStart(2, '0');
            
            // Format date as YYYY-MM-DD
            var formattedDate = `${year}-${month}-${day}`;
            // Format time as HH:MM
            var formattedTime = `${hours}:${minutes}`;
            
            console.log("Selected slot:", formattedDate, formattedTime);
            
            document.getElementById("appointment_date").value = formattedDate;
            document.getElementById("start_time").value = formattedTime;
            
            // Scroll to the form fields
            document.getElementById("service_id").scrollIntoView({ behavior: 'smooth' });
            
            // Focus on the service dropdown to prompt user for next input
            document.getElementById("service_id").focus();
        }
    });
    
    calendar.render();
}

document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById('provider_id').value) {
        updateCalendar();
    }
});
</script>
<?php include VIEW_PATH . '/partials/footer.php'; ?>
