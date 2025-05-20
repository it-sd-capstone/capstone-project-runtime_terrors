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
                                    <?= htmlspecialchars($service['name']) ?> - 
                                    <?= htmlspecialchars($service['description']) ?>
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
                                    <?= '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="provider-bio" class="mt-2 text-muted" style="display:none;"></div>
                    </div>
                    
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
                        <input type="hidden" id="form_provider_id" name="provider_id">
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
// DOM elements
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

// Step 1: Service Selection
serviceSelect.addEventListener('change', function() {
    const selectedService = this.value;
    
    // Reset downstream steps
    providerSelect.value = "";
    providerBio.style.display = "none";
    providerSection.style.display = selectedService ? "block" : "none";
    calendarSection.style.display = "none";
    calendarError.style.display = "none";
    bookForm.style.display = "none";
    
    // --- FIX: Remove any previous 'No providers' alert ---
    const oldAlert = providerSection.querySelector('.alert.alert-info');
    if (oldAlert) oldAlert.remove();
    
    // Filter providers - FIXED VERSION
    let availableProviderCount = 0;
    
    // First hide all provider options
    Array.from(providerSelect.options).forEach(opt => {
        if (!opt.value) return; // skip placeholder
        opt.style.display = 'none';
    });
    
    // Only show providers that offer this specific service
    if (selectedService) {
        Array.from(providerSelect.options).forEach(opt => {
            if (!opt.value) return; // skip placeholder
            
            let services = [];
            try {
                const servicesData = opt.getAttribute('data-services');
                services = JSON.parse(servicesData || '[]');
            } catch (e) {
                console.error('Error parsing services data:', e);
                services = [];
            }
            
            // Convert both to strings to ensure comparison works
            const show = services.some(id => String(id) === String(selectedService));
            
            if (show) {
                opt.style.display = '';
                availableProviderCount++;
            }
        });
        
        console.log(`Found ${availableProviderCount} providers for service ${selectedService}`);
    }
    
    // If no providers for this service, show message
    if (selectedService && availableProviderCount === 0) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-info mt-2';
        alertDiv.textContent = 'No providers currently offer this service. Please select a different service or contact us for assistance.';
        providerSection.appendChild(alertDiv);
        providerSelect.disabled = true;
    } else {
        providerSelect.disabled = false;
    }
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

// Step 3: Calendar
function loadCalendar(providerId, serviceId) {
    calendarLoading.style.display = "block";
    calendarError.style.display = "none";
    calendarEl.innerHTML = "";
    
    if (calendar) calendar.destroy();
    
    // Build API URL - make sure to use consistent URL format
    const apiUrl = `<?= base_url('index.php/api/getAvailableSlots') ?>?provider_id=${providerId}&service_id=${serviceId}`;
    console.log("Loading calendar with API URL:", apiUrl);
    
    setTimeout(() => { // Simulate loading
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
                    start: new Date() // This sets the minimum selectable date to now
                },
                eventClick: function(info) {
                    // Check if event is in the past
                    if (info.event.start < new Date()) {
                        alert("Cannot book appointments in the past. Please select a future time slot.");
                        return;
                    }
                    
                    // Check if this is an available slot (check all possible prop locations)
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
                    
                    // Fill form hidden fields
                    document.getElementById("form_service_id").value = serviceSelect.value;
                    document.getElementById("form_provider_id").value = providerSelect.value;
                    document.getElementById("appointment_date").value = formattedDate;
                    document.getElementById("start_time").value = formattedTime;
                    
                    // Show summary - truncate long text for better display
                    const serviceText = serviceSelect.options[serviceSelect.selectedIndex].textContent;
                    const providerText = providerSelect.options[providerSelect.selectedIndex].textContent;
                    
                    document.getElementById("summary-service").textContent = 
                        serviceText.length > 30 ? serviceText.substring(0, 30) + '...' : serviceText;
                    document.getElementById("summary-provider").textContent = providerText;
                    document.getElementById("summary-date").textContent = formattedDate;
                    document.getElementById("summary-time").textContent = 
                        new Date(eventDate).toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'});
                    
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
                    // Gray out past events
                    if (info.event.start < new Date()) {
                        info.el.style.opacity = '0.6';
                        info.el.style.backgroundColor = '#e9ecef';
                        info.el.style.borderColor = '#dee2e6';
                        info.el.style.pointerEvents = 'none'; // Make past events unclickable
                    }
                    
                    // Always add a useful title tooltip
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
            
            // Additional fetch to handle empty slots case
            fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`API returned ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Available slots data:", data);
                
                if (!data || data.length === 0) {
                    // Show a message to the user
                    calendarEl.innerHTML = '<div class="alert alert-info">No available appointments found for this provider and service. Please try another provider or contact us.</div>';
                    calendarLoading.style.display = "none";
                }
            })
            .catch(error => {
                console.error("Error fetching slots:", error);
                calendarError.style.display = "block";
                calendarLoading.style.display = "none";
            });
            
        } catch (err) {
            console.error("Error setting up calendar:", err);
            calendarError.style.display = "block";
            calendarLoading.style.display = "none";
        }
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
    
    // Debug: Log provider service information
    const options = document.querySelectorAll('#provider_id option');
    options.forEach(opt => {
        if (opt.value) {
            console.log(
                'Provider:', opt.textContent.trim(),
                'Service IDs:', opt.getAttribute('data-services')
            );
        }
    });
    
    // Fix calendar rendering issues with Bootstrap tabs (if applicable)
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function() {
            if (calendar) {
                calendar.updateSize();
            }
        });
    });
    
    // Handle network issues by providing a retry button
    document.getElementById('calendar-error')?.addEventListener('click', function() {
        if (providerSelect.value && serviceSelect.value) {
            loadCalendar(providerSelect.value, serviceSelect.value);
        }
    });
    
    // Highlight the currently selected service
    serviceSelect.addEventListener('change', function() {
        document.querySelectorAll('.service-highlight').forEach(el => el.classList.remove('service-highlight'));
        if (this.value) {
            this.closest('.mb-4').classList.add('service-highlight');
        }
    });
});

// Helper function for API diagnostics
function diagnoseApiConnection(url) {
    console.log("Testing API connection to:", url);
    fetch(url, { method: 'HEAD' })
        .then(response => {
            console.log("API HEAD response status:", response.status);
            if (!response.ok) {
                console.error("API may not be accessible. Consider checking server logs.");
            }
        })
        .catch(error => {
            console.error("API connection error:", error);
        });
}

// Run API diagnostics for troubleshooting
diagnoseApiConnection("<?= base_url('index.php/api/test') ?>");
</script>

<style>
.service-highlight {
    border-left: 3px solid #0d6efd;
    padding-left: 10px;
}
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
