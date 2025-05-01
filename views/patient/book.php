<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container mt-4">
    <h4>Book an Appointment</h4>

    <?php
    $selectedProviderId = $_GET['provider_id'] ?? null;
    $selectedProviderName = $selectedProviderId ? htmlspecialchars($providers[$selectedProviderId]['name'] ?? 'Unknown Provider') : 'Select a provider';
    ?>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5>Book Appointment with <?= $selectedProviderName ?></h5>
        </div>
        <div class="card-body">
            <!-- Provider Selection Dropdown -->
            <form id="bookForm" method="POST" action="<?= base_url('index.php/patient/processPatientAction') ?>">
            <input type="hidden" name="action" value="book">
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

                <!-- CSRF Security Field -->
                <?= csrf_field() ?>

                <!-- If no provider is selected yet and we're not using the dropdown above -->
                <?php if (!$selectedProviderId && !isset($_GET['provider_id'])): ?>
                <div class="mb-3">
                    <label for="provider_select" class="form-label">Select Provider</label>
                    <select class="form-control" id="provider_id" name="provider_id" required onchange="updateCalendar()">
                        <option value="">-- Select a Provider --</option>
                        <?php foreach ($providers as $p): ?>
                            <option value="<?= $p['user_id'] ?>"
                                <?= (isset($provider) && $provider['user_id'] == $p['user_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                <!-- If provider is selected, store as hidden input -->
                <input type="hidden" name="provider_id" value="<?= $selectedProviderId ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="service_id" class="form-label">Select Service:</label>
                        <select id="service_id" name="service_id" class="form-select" required>
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

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

<script>
document.getElementById("bookForm").addEventListener("submit", function(event) {
    event.preventDefault();
    var selectedDate = document.getElementById("appointment_date").value;
    var selectedTime = document.getElementById("start_time").value;
    var providerId = document.getElementById("provider_id").value;
    
    if (!selectedDate || !selectedTime || !providerId) {
        alert("Please select a provider, date, and time.");
        return;
    }
    
    fetch("<?= base_url('index.php/patient/checkAvailability') ?>", {
        method: "POST",
        body: JSON.stringify({ provider_id: providerId, date: selectedDate, time: selectedTime }),
        headers: { "Content-Type": "application/json" }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.available) {
            alert("Selected time is unavailable. Please pick a different slot.");
        } else {
            alert("Booking confirmed!");
            document.getElementById("bookForm").submit();
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Server error. Try again later.");
    });
});

// Function to update the calendar when provider changes
function updateCalendar() {
    var calendarEl = document.getElementById('calendar');
    var providerId = document.getElementById('provider_id').value;

    if (!providerId) {
        calendarEl.innerHTML = '<p class="text-center text-muted">Select a provider to view availability.</p>';
        return;
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: "<?= base_url('index.php/scheduler/getAvailableSlots') ?>?provider_id=" + providerId,
        eventClick: function(info) {
            if (info.event.extendedProps.is_booked) {
                alert("This slot is already booked.");
                return;
            }
            document.getElementById("appointment_date").value = info.event.start.toISOString().split('T')[0];
            document.getElementById("start_time").value = info.event.start.toISOString().split('T')[1].substring(0, 5);
            document.getElementById("bookForm").scrollIntoView({ behavior: 'smooth' });
        }
    });
    
    calendar.render();
}

document.addEventListener("DOMContentLoaded", updateCalendar);
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>