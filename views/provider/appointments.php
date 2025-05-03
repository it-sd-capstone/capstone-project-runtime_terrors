<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container provider-dashboard my-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body p-4">
                    <h2 class="text-primary mb-2">
                        <i class="fas fa-calendar-alt"></i> Appointments Overview
                    </h2>
                    <p class="text-muted">Manage scheduled appointments and track availability.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Calendar View (larger on this page) -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Appointment Calendar</h5>
                </div>
                <div class="card-body calendar-container p-2">
                    <div id="calendar"></div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-around">
                        <div><i class="fas fa-circle text-success"></i> <small>Confirmed Appointments</small></div>
                        <div><i class="fas fa-circle text-warning"></i> <small>Pending Appointments</small></div>
                        <div><i class="fas fa-circle text-info"></i> <small>Available Slots</small></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Appointments List (smaller sidebar on this page) -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 h5">Upcoming Appointments</h4>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                            Filter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                            <li><button class="dropdown-item filter-btn" data-filter="all">All</button></li>
                            <li><button class="dropdown-item filter-btn" data-filter="confirmed">Confirmed</button></li>
                            <li><button class="dropdown-item filter-btn" data-filter="pending">Pending</button></li>
                            <li><button class="dropdown-item filter-btn" data-filter="canceled">Canceled</button></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush appointment-list">
                        <?php if (!empty($appointments)) : ?>
                            <?php foreach ($appointments as $appointment) : 
                                $statusClass = match($appointment['status']) {
                                    'scheduled' => 'primary',
                                    'confirmed' => 'success',
                                    'canceled' => 'danger',
                                    'completed' => 'info',
                                    'no_show' => 'warning',
                                    default => 'secondary'
                                };
                            ?>
                                <a href="<?= base_url('index.php/provider/viewAppointment/' . ($appointment['id'] ?? $appointment['appointment_id'] ?? '')) ?>" 
                                   class="list-group-item list-group-item-action appointment-item" 
                                   data-status="<?= $appointment['status'] ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($appointment['patient_name']) ?></h6>
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($appointment['status']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-day"></i> <?= date('M d', strtotime($appointment['appointment_date'])) ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> <?= date('g:i A', strtotime($appointment['start_time'])) ?>
                                        </small>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($appointment['service_name'] ?? 'Consultation') ?>
                                        </small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="list-group-item py-4 text-center">
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="mb-1">No upcoming appointments</p>
                                    <small class="text-muted">Appointments will appear here once patients book with you</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('index.php/provider/schedule') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus-circle"></i> Add Availability
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Action Card -->
            <div class="card shadow mt-3">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('index.php/provider/appointments/export') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-file-export me-2"></i> Export Schedule
                        </a>
                        <button type="button" class="btn btn-outline-info btn-sm" id="refreshCalendar">
                            <i class="fas fa-sync-alt me-2"></i> Refresh Calendar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- This should ideally be in your header or footer partials -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: "auto",
        headerToolbar: {
            start: 'prev,next today',
            center: 'title',
            end: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        // Use controller methods instead of direct API files
        eventSources: [
            {
                url: '<?= base_url("index.php/provider/getAppointmentEvents") ?>',
                method: 'GET',
                failure: function() {
                    console.error("Error loading appointment events");
                    alert('There was an error loading appointments!');
                }
            },
            {
                url: '<?= base_url("index.php/provider/getAvailabilityEvents") ?>',
                method: 'GET',
                failure: function() {
                    console.error("Error loading availability events");
                    alert('There was an error loading availability!');
                }
            }
        ],
        eventClick: function(info) {
            console.log("Event clicked:", info.event);
            // Check if this is an availability slot or an appointment
            const eventType = info.event.extendedProps?.type || 'unknown';
            
            if(eventType === 'availability') {
                if (confirm("Do you want to remove this availability slot?")) {
                    const scheduleId = info.event.id;
                    if (scheduleId) {
                        window.location.href = "<?= base_url('index.php/provider/deleteSchedule/') ?>" + scheduleId;
                    } else {
                        alert("Cannot identify this availability slot.");
                    }
                }
            } else if(eventType === 'appointment') {
                // Navigate to appointment details
                const appointmentId = info.event.id;
                if (appointmentId) {
                    window.location.href = "<?= base_url('index.php/provider/viewAppointment/') ?>" + appointmentId;
                } else {
                    alert("Cannot identify this appointment.");
                }
            }
        },
        // Add loading indicator
        loading: function(isLoading) {
            if (isLoading) {
                // Show loading indicator
                document.getElementById('calendar').classList.add('loading');
            } else {
                // Hide loading indicator
                document.getElementById('calendar').classList.remove('loading');
            }
        },
        dayMaxEventRows: true, // Enable "more" link when too many events
        moreLinkClick: 'popover', // Show events in a popover when clicking "more" link
        eventMaxStack: 3, // Show 3 events max before showing "+more"
        
        // Make events with time more readable
        displayEventTime: true,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        
        // Improve event rendering
        eventDidMount: function(info) {
            // Add tooltip to show full details on hover
            const eventEl = info.el;
            const event = info.event;
            const title = event.title;
            const time = event.start ? event.start.toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'}) : '';
            const patient = event.extendedProps?.patient || '';
            const service = event.extendedProps?.service || '';
            const status = event.extendedProps?.status || '';
            
            // Create tooltip content
            let tooltipContent = `<strong>${title}</strong>`;
            if (time) tooltipContent += `<br>Time: ${time}`;
            if (service) tooltipContent += `<br>Service: ${service}`;
            if (status) tooltipContent += `<br>Status: ${status}`;
            
            // Add tooltip (if you have a tooltip library) or use title attribute
            eventEl.setAttribute('title', title + ' - ' + time);
            
            // You could add a custom tooltip library here if needed
        }
    });
    
    calendar.render();
    
    // Adjust calendar height based on view
    calendar.on('viewDidMount', function(info) {
        const view = info.view;
        const viewName = view.type;
        
        // Get the calendar container
        const calendarContainer = document.querySelector('.calendar-container');
        
        if (viewName.includes('timeGrid')) {
            // Week/Day views need more height
            calendarContainer.style.minHeight = '800px';
            calendar.setOption('height', 750);
        } else {
            // Month view can be shorter
            calendarContainer.style.minHeight = '600px';
            calendar.setOption('height', 'auto');
        }
    });

    // Make sure calendar resizes when window resizes
    window.addEventListener('resize', function() {
        calendar.updateSize();
    });
    
    // Add debugging to help troubleshoot
    console.log("Calendar initialized");
    
    // Add a manual refresh button for testing
    const refreshBtn = document.getElementById('refreshCalendar');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            calendar.refetchEvents();
            console.log("Calendar events manually refreshed");
        });
    }
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
