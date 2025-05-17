<?php include VIEW_PATH . '/partials/header.php'; ?>

<?php
// Get preselected provider_id from URL if present
$selectedProviderId = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : null;

// If provider is preselected, filter services to only those offered by this provider
if ($selectedProviderId) {
    $providerServices = $this->providerModel->getProviderServices($selectedProviderId);
    $serviceIds = array_column($providerServices, 'service_id');
    $services = array_filter($services, function($s) use ($serviceIds) {
        return in_array($s['service_id'], $serviceIds);
    });
    // Re-index for easier use in JS
    $services = array_values($services);
}
?>

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
                                    <?= htmlspecialchars($service['name']) ?> - 
                                    <?= htmlspecialchars($service['description']) ?>
                                    ($<?= htmlspecialchars(number_format((float)$service['price'], 2)) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Step 2: Select Provider (hidden or locked if preselected) -->
                    <div class="mb-4" id="provider-section" <?= $selectedProviderId ? 'style="display:none;"' : '' ?>>
                        <label for="provider_id" class="form-label fw-bold">2. Choose a provider:</label>
                        <select id="provider_id" name="provider_id" class="form-select" required <?= $selectedProviderId ? 'disabled' : '' ?>>
                            <option value="">-- Select a provider --</option>
                            <?php foreach ($providers as $p): ?>
                                <option value="<?= $p['user_id'] ?>"
                                    data-services='<?= json_encode($p['service_ids'] ?? []) ?>'
                                    <?= ($selectedProviderId && $selectedProviderId == $p['user_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                                    <?= !empty($p['specialization']) ? '(' . htmlspecialchars($p['specialization']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="provider-bio" class="mt-2 text-muted" style="display:none;"></div>
                    </div>
                    
                    <!-- If provider is preselected, show their name -->
                    <?php if ($selectedProviderId): ?>
                        <div class="mb-3">
                            <strong>Provider:</strong>
                            <?php
                            $provider = array_filter($providers, fn($p) => $p['user_id'] == $selectedProviderId);
                            $provider = reset($provider);
                            echo htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']);
                            ?>
                            <input type="hidden" name="provider_id" id="form_provider_id" value="<?= $selectedProviderId ?>">
                        </div>
                    <?php endif; ?>
                    
                    <!-- Step 3: Calendar (hidden until provider selected) -->
                    <div class="mb-4" id="calendar-section" style="display:none;">
                        <label class="form-label fw-bold">3. Pick an available time slot:</label>
                        <div id="calendar-error" class="alert alert-warning" style="display:none;">
                            Unable to load availability. Please try again later or contact support.
                        </div>
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
                        <?php if (!$selectedProviderId): ?>
                            <input type="hidden" id="form_provider_id" name="provider_id">
                        <?php endif; ?>
                        <input type="hidden" id="appointment_date" name="appointment_date">
                        <input type="hidden" id="start_time" name="start_time">
                        <div class="mb-3">
                            <label class="form-label fw-bold">4. Confirm your appointment details:</label>
                            <div class="mb-2">
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
                            <textarea class="form-control" id="reason" name="reason" rows="2" required></textarea>
                            <div class="invalid-feedback">Please provide a reason for your visit.</div>
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
const calendarError = document.getElementById('calendar-error');
const bookForm = document.getElementById('bookForm');
let calendar = null;

const selectedProviderId = <?= json_encode($selectedProviderId ?? null) ?>;

// Step 1: Service Selection
serviceSelect.addEventListener('change', function() {
    const selectedService = this.value;

    // Reset downstream steps
    if (providerSelect) providerSelect.value = "";
    if (providerBio) providerBio.style.display = "none";
    if (providerSection) providerSection.style.display = selectedService && !selectedProviderId ? "block" : "none";
    calendarSection.style.display = "none";
    calendarError.style.display = "none";
    bookForm.style.display = "none";

    // Filter providers if not preselected
    if (!selectedProviderId && providerSelect) {
        let availableProviderCount = 0;
        Array.from(providerSelect.options).forEach(opt => {
            if (!opt.value) return;
            let services = [];
            try {
                const servicesData = opt.getAttribute('data-services');
                services = JSON.parse(servicesData || '[]');
            } catch (e) {
                services = [];
            }
            const show = services.some(id => String(id) === String(selectedService));
            opt.style.display = show ? '' : 'none';
            if (show) availableProviderCount++;
        });
        providerSelect.disabled = availableProviderCount === 0;
    }

    // If provider is preselected, auto-load calendar if service is selected
    if (selectedProviderId && selectedService) {
        calendarSection.style.display = "block";
        calendarError.style.display = "none";
        bookForm.style.display = "none";
        loadCalendar(selectedProviderId, selectedService);
    }
});

// Step 2: Provider Selection (if not preselected)
if (providerSelect) {
    providerSelect.addEventListener('change', function() {
        const selectedProvider = this.value;

        // Show bio if available
        let bio = "";
        if (selectedProvider) {
            const opt = providerSelect.options[providerSelect.selectedIndex];
            const providerBios = <?= json_encode(array_column($providers, 'bio', 'user_id')) ?>;
            bio = providerBios[selectedProvider] || "";
        }
        providerBio.textContent = bio ? bio.substring(0, 200) + (bio.length > 200 ? "..." : "") : "";
        providerBio.style.display = bio ? "block" : "none";

        // Reset downstream
        calendarSection.style.display = selectedProvider ? "block" : "none";
        calendarError.style.display = "none";
        bookForm.style.display = "none";

        if (selectedProvider && serviceSelect.value) {
            loadCalendar(selectedProvider, serviceSelect.value);
        } else {
            if (calendar) calendar.destroy();
            calendarEl.innerHTML = '';
        }
    });
}

// Step 3: Calendar
function loadCalendar(providerId, serviceId) {
    calendarLoading.style.display = "block";
    calendarError.style.display = "none";
    calendarEl.innerHTML = "";

    if (calendar) calendar.destroy();

    const apiUrl = `<?= base_url('index.php/api/getAvailableSlots') ?>?provider_id=${providerId}&service_id=${serviceId}`;

    setTimeout(() => {
        try {
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                timeZone: 'local',
                events: apiUrl,
                validRange: {
                    start: new Date()
                },
                eventClick: function(info) {
                    if (info.event.start < new Date()) {
                        alert("Cannot book appointments in the past. Please select a future time slot.");
                        return;
                    }
                    const isAvailable = 
                        info.event.extendedProps.type === 'availability' || 
                        info.event.title === 'Available' ||
                        (info.event.extendedProps && info.event.backgroundColor === '#28a745');
                    if (!isAvailable) {
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

                    document.getElementById("form_service_id").value = serviceSelect.value;
                    if (!selectedProviderId) {
                        document.getElementById("form_provider_id").value = providerSelect.value;
                    }
                    document.getElementById("appointment_date").value = formattedDate;
                    document.getElementById("start_time").value = formattedTime;

                    const serviceText = serviceSelect.options[serviceSelect.selectedIndex].textContent;
                    let providerText = "";
                    if (selectedProviderId) {
                        providerText = <?= json_encode($provider['first_name'] . ' ' . $provider['last_name']) ?>;
                    } else {
                        providerText = providerSelect.options[providerSelect.selectedIndex].textContent;
                    }

                    document.getElementById("summary-service").textContent = 
                        serviceText.length > 30 ? serviceText.substring(0, 30) + '...' : serviceText;
                    document.getElementById("summary-provider").textContent = providerText;
                    document.getElementById("summary-date").textContent = formattedDate;
                    document.getElementById("summary-time").textContent = 
                        new Date(eventDate).toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'});

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
                    if (info.event.start < new Date()) {
                        info.el.style.opacity = '0.6';
                        info.el.style.backgroundColor = '#e9ecef';
                        info.el.style.borderColor = '#dee2e6';
                        info.el.style.pointerEvents = 'none';
                    }
                    const time = info.event.start ? 
                        new Date(info.event.start).toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'}) : '';
                    info.el.setAttribute('title', `Available: ${time}`);
                },
                loading: function(isLoading) {
                    calendarLoading.style.display = isLoading ? "block" : "none";
                },
                eventSourceFailure: function(error) {
                    console.error("Calendar failed to load events:", error);
                    calendarError.style.display = "block";
                }
            });
            calendar.render();
            fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`API returned ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data || data.length === 0) {
                    calendarEl.innerHTML = '<div class="alert alert-info">No available appointments found for this provider and service. Please try another provider or contact us.</div>';
                }
            })
            .catch(error => {
                calendarError.style.display = "block";
            });
        } catch (err) {
            calendarError.style.display = "block";
            calendarLoading.style.display = "none";
        }
    }, 400);
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

    // If provider is preselected and only one service, preselect it
    if (selectedProviderId && serviceSelect.options.length === 2) {
        serviceSelect.selectedIndex = 1;
        // Trigger calendar load
        calendarSection.style.display = "block";
        calendarError.style.display = "none";
        bookForm.style.display = "none";
        loadCalendar(selectedProviderId, serviceSelect.value);
    }
});
</script>

<style>
.fc-event {
    cursor: pointer;
}
#calendar-error {
    cursor: pointer;
}
#calendar-error:hover:after {
    content: " (Click to retry)";
}
</style>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
