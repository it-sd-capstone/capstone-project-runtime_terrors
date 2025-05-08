
<?php include_once VIEW_PATH . '/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Book an Appointment</h3>
                </div>
                <div class="card-body">

                    <!-- Step 1: Select Service -->
                    <div class="mb-4">
                        <label for="service_id" class="form-label fw-bold">1. What service do you need?</label>
                        <select id="service_id" name="service_id" class="form-select form-select-lg" required>
                            <option value="">-- Please select a service --</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service['service_id'] ?>" data-duration="<?= $service['duration'] ?? 30 ?>">
                                    <?= htmlspecialchars($service['name']) ?> 
                                    - <?= htmlspecialchars($service['description']) ?>
                                    ($<?= htmlspecialchars($service['price']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Step 2: Select Provider (hidden until service selected) -->
                    <div class="mb-4" id="provider-section" style="display:none;">
                        <label for="provider_id" class="form-label fw-bold">2. Choose a provider:</label>
                        <select id="provider_id" name="provider_id" class="form-select" required>
                            <option value="">-- Select a provider --</option>
                            <?php foreach ($providers as $p): ?>
                                <option value="<?= $p['user_id'] ?>"
                                    data-services='<?= json_encode($p['service_ids'] ?? []) ?>'>
                                    <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                                    <?= !empty($p['specialization']) ? '(' . htmlspecialchars($p['specialization']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="provider-bio" class="mt-2 text-muted" style="display:none;"></div>
                    </div>

                    <!-- Step 3: Calendar (hidden until provider selected) -->
                    <div class="mb-4" id="calendar-section" style="display:none;">
                        <label class="form-label fw-bold">3. Pick an available time slot:</label>
                        <div id="calendar"></div>
                        <div id="calendar-loading" class="text-center py-4" style="display:none;">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Loading available slots...</p>
                        </div>
                    </div>

                    <!-- Step 4: Booking Form (hidden until slot picked) -->
                    <form id="bookForm" method="POST" action="<?= base_url('index.php/patient/processBooking') ?>" class="needs-validation" novalidate style="display:none;">
                        <?= csrf_field() ?>
                        <input type="hidden" id="form_service_id" name="service_id">
                        <input type="hidden" id="form_provider_id" name="provider_id">
                        <input type="hidden" id="appointment_date" name="appointment_date">
                        <input type="hidden" id="start_time" name="start_time">

                        <div class="mb-3">
                            <label class="form-label fw-bold">4. Confirm your appointment details:</label>
                            <div>
                                <span class="badge bg-info text-dark" id="summary-service"></span>
                                <span class="badge bg-secondary" id="summary-provider"></span>
                                <span class="badge bg-success" id="summary-date"></span>
                                <span class="badge bg-success" id="summary-time"></span>
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
    </div>
</div>

<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

<script>
const serviceSelect = document.getElementById('service_id');
const providerSection = document.getElementById('provider-section');
const providerSelect = document.getElementById('provider_id');
const providerBio = document.getElementById('provider-bio');
const calendarSection = document.getElementById('calendar-section');
const calendarEl = document.getElementById('calendar');
const calendarLoading = document.getElementById('calendar-loading');
const bookForm = document.getElementById('bookForm');

let calendar = null;

// Step 1: Service Selection
serviceSelect.addEventListener('change', function() {
    const selectedService = this.value;
    // Reset downstream steps
    providerSelect.value = "";
    providerBio.style.display = "none";
    providerSection.style.display = selectedService ? "block" : "none";
    calendarSection.style.display = "none";
    bookForm.style.display = "none";
    // Filter providers
    Array.from(providerSelect.options).forEach(opt => {
        if (!opt.value) return; // skip placeholder
        const services = JSON.parse(opt.getAttribute('data-services'));
        opt.style.display = services.includes(selectedService) ? '' : 'none';
    });
});

// Step 2: Provider Selection
providerSelect.addEventListener('change', function() {
    const selectedProvider = this.value;
    // Show bio if available
    let bio = "";
    if (selectedProvider) {
        const opt = providerSelect.options[providerSelect.selectedIndex];
        const providerName = opt.textContent;
        <?php // Output provider bios as a JS object ?>
        const providerBios = <?=
            json_encode(array_column($providers, 'bio', 'user_id'))
        ?>;
        bio = providerBios[selectedProvider] || "";
    }
    providerBio.textContent = bio ? bio.substring(0, 200) + (bio.length > 200 ? "..." : "") : "";
    providerBio.style.display = bio ? "block" : "none";
    // Reset downstream
    calendarSection.style.display = selectedProvider ? "block" : "none";
    bookForm.style.display = "none";
    if (selectedProvider && serviceSelect.value) {
        loadCalendar(selectedProvider, serviceSelect.value);
    } else {
        if (calendar) calendar.destroy();
        calendarEl.innerHTML = '';
    }
});

// Step 3: Calendar
function loadCalendar(providerId, serviceId) {
    calendarLoading.style.display = "block";
    calendarEl.innerHTML = "";
    if (calendar) calendar.destroy();
    setTimeout(() => { // Simulate loading
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,dayGridMonth'
            },
            timeZone: 'local',
            events: "<?= base_url('index.php/api/getAvailableSlots') ?>?provider_id=" + providerId + "&service_id=" + serviceId,
            eventClick: function(info) {
                if (info.event.extendedProps.type !== 'availability') {
                    alert("This slot is not available for booking.");
                    return;
                }
                const eventDate = info.event.start;
                const year = eventDate.getFullYear();
                const month = (eventDate.getMonth() + 1).toString().padStart(2, '0');
                const day = eventDate.getDate().toString().padStart(2, '0');
                const hours = eventDate.getHours().toString().padStart(2, '0');
                const minutes = eventDate.getMinutes().toString().padStart(2, '0');
                const formattedDate = `${year}-${month}-${day}`;
                const formattedTime = `${hours}:${minutes}`;
                // Fill form hidden fields
                document.getElementById("form_service_id").value = serviceSelect.value;
                document.getElementById("form_provider_id").value = providerSelect.value;
                document.getElementById("appointment_date").value = formattedDate;
                document.getElementById("start_time").value = formattedTime;
                // Show summary
                document.getElementById("summary-service").textContent = serviceSelect.options[serviceSelect.selectedIndex].textContent;
                document.getElementById("summary-provider").textContent = providerSelect.options[providerSelect.selectedIndex].textContent;
                document.getElementById("summary-date").textContent = formattedDate;
                document.getElementById("summary-time").textContent = formattedTime;
                // Show form
                bookForm.style.display = "block";
                bookForm.scrollIntoView({behavior: "smooth"});
            },
            eventDisplay: 'block',
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            eventDidMount: function(info) {
                if (info.event.extendedProps.type === 'availability') {
                    const time = info.event.start ? new Date(info.event.start).toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'}) : '';
                    info.el.setAttribute('title', `Available: ${time}`);
                }
            }
        });
        calendar.render();
        calendarLoading.style.display = "none";
    }, 400); // Simulate loading delay
}

// Step 4: Form Validation
document.addEventListener('DOMContentLoaded', function() {
    if (bookForm) {
        bookForm.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
});
</script>

<?php include_once VIEW_PATH . '/partials/footer.php'; ?>
