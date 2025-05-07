<?php include VIEW_PATH . '/partials/header.php'; ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm bg-light">
                <div class="card-body p-4">
                    <h2 class="text-primary mb-2">
                        <i class="fas fa-calendar-plus"></i> Book an Appointment
                    </h2>
                    <p class="text-muted">Select a provider, service, and your preferred appointment time.</p>
                </div>
            </div>
        </div>
    </div>
    
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
    
    <!-- Display alert messages -->
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5>Book Appointment <?= $selectedProviderName ? "with $selectedProviderName" : "" ?></h5>
        </div>
        <div class="card-body">
            <form id="bookForm" method="POST" action="<?= base_url('index.php/patient/processBooking') ?>" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                
                <!-- Provider Selection Dropdown -->
                <div class="mb-3">
                    <label for="provider_id" class="form-label">Select Provider:</label>
                    <select id="provider_id" name="provider_id" class="form-select" required onchange="updateCalendar()">
                        <option value="">-- Select a Provider --</option>
                        <?php foreach ($providers as $p) : ?>
                            <option value="<?= $p['user_id'] ?>" <?= ($selectedProviderId == $p['user_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> 
                                <?= !empty($p['specialization']) ? '(' . htmlspecialchars($p['specialization']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a provider.</div>
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
                        <div class="invalid-feedback">Please select a service.</div>
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
                    <select class="form-select" id="type" name="type" required>
                        <option value="in_person">In-Person</option>
                        <option value="telehealth">Telehealth</option>
                        <option value="phone">Phone Consultation</option>
                    </select>
                    <div class="invalid-feedback">Please select an appointment type.</div>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Visit:</label>
                    <textarea class="form-control" id="reason" name="reason" rows="2"></textarea>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Additional Notes (optional):</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= base_url('index.php/patient') ?>" class="btn btn-secondary me-md-2">Cancel</a>
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
// Enable form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bookForm');
    
    if (form) {
        // Handle form submission with validation
        form.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
            
            // Additional validation
            const providerSelect = document.getElementById('provider_id');
            const serviceSelect = document.getElementById('service_id');
            const dateInput = document.getElementById('appointment_date');
            const timeInput = document.getElementById('start_time');
            
            if (providerSelect.value && serviceSelect.value && dateInput.value && timeInput.value) {
                // Log for debugging
                console.log("Form submission with data:", {
                    provider_id: providerSelect.value,
                    service_id: serviceSelect.value,
                    appointment_date: dateInput.value,
                    start_time: timeInput.value,
                    type: document.getElementById("type").value,
                    reason: document.getElementById("reason").value,
                    notes: document.getElementById("notes").value
                });
            }
        });
    }
    
    // Initialize calendar if provider is selected
    if (document.getElementById('provider_id').value) {
        updateCalendar();
    } else {
        // Show a message in place of the calendar
        const calendarEl = document.getElementById('calendar');
        calendarEl.innerHTML = '<div class="alert alert-info text-center py-5"><i class="fas fa-info-circle me-2"></i>Select a provider to view available appointment slots.</div>';
    }
});

// Function to update the calendar when provider changes
function updateCalendar() {
    var calendarEl = document.getElementById('calendar');
    var providerId = document.getElementById('provider_id').value;
    document.getElementById("appointment_date").value = "";
    document.getElementById("start_time").value = "";
    
    if (!providerId) {
        calendarEl.innerHTML = '<div class="alert alert-info text-center py-5"><i class="fas fa-info-circle me-2"></i>Select a provider to view availability.</div>';
        return;
    }
    
    // Show loading indicator
    calendarEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading available slots...</p></div>';
    
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
        },
        eventDisplay: 'block',
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        eventDidMount: function(info) {
            // Add tooltips to events
            if (info.event.extendedProps.type === 'availability') {
                const title = info.event.title;
                const time = info.event.start ? new Date(info.event.start).toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'}) : '';
                
                const tooltipContent = `Available: ${time}`;
                
                // Add tooltip using title attribute
                info.el.setAttribute('title', tooltipContent);
            }
        }
    });
    
    calendar.render();
}
</script>
<?php include VIEW_PATH . '/partials/footer.php'; ?>