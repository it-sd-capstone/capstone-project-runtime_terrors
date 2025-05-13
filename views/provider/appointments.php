<?php include VIEW_PATH . '/partials/header.php'; ?>

<style>
/* Modern Premium Styling */
:root {
    --primary: #4361ee;
    --secondary: #3f37c9;
    --success: #4cc9f0;
    --warning: #f72585;
    --danger: #ff4d6d;
    --light: #f8f9fa;
    --white: #ffffff;
    --dark: #212529;
    --border-radius: 0.75rem;
    --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --transition: all 0.3s ease-in-out;
}

.provider-dashboard {
    background: var(--white);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.page-title {
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 1.5rem;
}

.calendar-wrapper {
    position: relative;
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: var(--transition);
    background-color: #fff;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.calendar-wrapper:hover {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

.calendar-container {
    padding: 1rem;
    min-height: 700px;
}

.fc .fc-toolbar.fc-header-toolbar {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background-color: rgba(67, 97, 238, 0.05);
    border-radius: var(--border-radius);
}

.fc .fc-toolbar-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.fc .fc-button-primary {
    background-color: var(--primary);
    border-color: var(--primary);
    border-radius: 0.5rem;
    transition: var(--transition);
    text-transform: uppercase;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
}

.fc .fc-prev-button,
.fc .fc-next-button {
    position: relative;
    padding: 0.5rem 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
}

.fc .fc-prev-button .fc-icon,
.fc .fc-next-button .fc-icon {
    font-size: 1.2rem;
}

.fc .fc-button-primary:hover {
    background-color: var(--secondary);
    border-color: var(--secondary);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    background-color: var(--secondary);
    border-color: var(--secondary);
}

.fc .fc-today-button {
    background-color: var(--primary);
    border-color: var(--primary);
}

.fc-event {
    cursor: pointer;
    border-radius: 0.5rem;
    padding: 0.25rem 0.5rem;
    font-size: 0.85rem;
    border: none !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.fc-event:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.fc-event-title {
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fc-daygrid-day {
    transition: var(--transition);
}

.fc-daygrid-day:hover {
    background-color: rgba(67, 97, 238, 0.05);
}

.fc-day-today {
    background-color: rgba(67, 97, 238, 0.1) !important;
}

.fc-daygrid-day-number {
    font-weight: 600;
    color: var(--dark);
    padding: 0.5rem !important;
}

.action-panel {
    background-color: #fff;
    border-radius: var(--border-radius);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: var(--transition);
    height: 100%;
}

.action-panel:hover {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

.action-panel .card-header {
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    padding: 1rem;
    border: none;
}

.py-2 {
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
}

.form-control, .form-select {
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    border: 1px solid #dee2e6;
    font-size: 0.9rem;
    transition: var(--transition);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
}

.btn {
    border-radius: 0.5rem;
    padding: 0.5rem 1rem; 
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.8rem; 
    transition: var(--transition);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.btn-outline-primary {
    color: var(--primary);
    border-color: var(--primary);
}

.btn-outline-primary:hover {
    background-color: var(--primary);
    border-color: var(--primary);
    color: #fff;
}

.btn-outline-secondary {
    color: var(--secondary);
    border-color: var(--secondary);
}

.btn-outline-secondary:hover {
    background-color: var(--secondary);
    border-color: var(--secondary);
    color: #fff;
}

.appointment-list {
    max-height: 500px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.appointment-list::-webkit-scrollbar {
    width: 6px;
}

.appointment-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.appointment-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.appointment-item {
    transition: var(--transition);
    border-left: 3px solid transparent;
}

.appointment-item:hover {
    transform: translateY(-2px);
    border-left-color: var(--primary);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

.appointment-item .badge {
    transition: var(--transition);
}

#notification-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 350px;
}

.notification {
    background-color: white;
    border-left: 4px solid var(--primary);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    animation: slide-in 0.3s ease-out forwards;
}

.notification.success {
    border-left-color: var(--success);
}

.notification.danger {
    border-left-color: var(--danger);
}

.notification.warning {
    border-left-color: var(--warning);
}

@keyframes slide-in {
    0% {
        transform: translateX(100%);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slide-out {
    0% {
        transform: translateX(0);
        opacity: 1;
    }
    100% {
        transform: translateX(100%);
        opacity: 0;
    }
}

.card-header.bg-info {
    background-color: #0dcaf0 !important;
}

.card-header.bg-secondary {
    background-color: var(--secondary) !important;
}

.appointment-list {
    max-height: none !important;
    height: 100%;
}

.action-panel {
    height: auto !important;
}

.col-lg-4.mb-4 {
    display: flex;
    flex-direction: column;
}

.card.shadow.mt-3.action-panel {
    margin-top: 1rem !important;
    margin-bottom: 1rem !important;
}

.calendar-container {
    min-height: 600px !important;
}

.provider-dashboard {
    padding-bottom: 2rem;
}

body {
    overflow-y: auto;
}

.container.provider-dashboard {
    padding-bottom: 2rem;
}

.card-header.bg-dark {
    color: white !important;
}

.action-panel {
    height: auto !important;
    margin-bottom: 1rem;
}

.provider-dashboard {
    padding-bottom: 3rem;
}

.calendar-container {
    min-height: 600px !important;
}

.appointments-container {
    height: 600px;
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.quick-actions-container {
    margin-top: 0;
}

.appointments-container .card-body {
    flex: 1;
    overflow: hidden;
    padding: 0 !important;
}

.appointments-container .appointment-list {
    height: 100%;
    overflow-y: auto;
    scrollbar-width: thin;
}

.appointments-container .appointment-list::-webkit-scrollbar {
    width: 6px;
}

.appointments-container .appointment-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.appointments-container .appointment-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.card.shadow-sm.bg-light {
    background-color: var(--white) !important;
}
</style>

<div class="container provider-dashboard my-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm bg-white">
                <div class="card-body py-2">
                    <h5 class="text-primary mb-2">Appointments Overview</h5>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Manage scheduled appointments and track availability.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Appointment Calendar</h5>
                    <div>
                        <div id="calendar-loading" class="spinner-border spinner-border-sm text-light d-none" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="card-body calendar-container p-2">
                    <div class="d-flex flex-wrap justify-content-center bg-light p-2 border-bottom small mb-2">
                        <div class="me-3 mb-1">
                            <span class="badge bg-success p-1">&nbsp;</span>
                            <span>Confirmed</span>
                        </div>
                        <div class="me-3 mb-1">
                            <span class="badge bg-warning p-1">&nbsp;</span>
                            <span>Pending</span>
                        </div>
                        <div class="me-3 mb-1">
                            <span class="badge bg-info p-1">&nbsp;</span>
                            <span>Available Slots</span>
                        </div>
                        <div class="me-3 mb-1">
                            <span class="badge bg-danger p-1">&nbsp;</span>
                            <span>Canceled</span>
                        </div>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow appointments-container">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
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
                <div class="card-body">
                    <div class="list-group list-group-flush appointment-list">
                        <?php if (!empty($appointments)) : ?>
                            <?php foreach ($appointments as $appointment) : 
                                $statusClass = match($appointment['status']) {
                                    'scheduled', 'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'canceled' => 'danger',
                                    'completed' => 'info',
                                    'no_show' => 'secondary',
                                    default => 'secondary'
                                };
                            ?>
                                <a href="<?= base_url('index.php/provider/viewAppointment/' . ($appointment['id'] ?? $appointment['appointment_id'] ?? '')) ?>" 
                                class="list-group-item list-group-item-action appointment-item" 
                                data-status="<?= $appointment['status'] ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($appointment['patient_name'] ?? $appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?></h6>
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
            </div>
            
            <div class="card shadow quick-actions-container">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 text-white">Quick Actions</h5>
                </div>
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between flex-wrap">
                        <a href="<?= base_url('index.php/provider/appointments/export') ?>" class="btn btn-outline-secondary btn-sm mb-2 me-2">
                            <i class="fas fa-file-export me-1"></i> Export Schedule
                        </a>
                        <button type="button" class="btn btn-outline-info btn-sm mb-2 me-2" id="refreshCalendar">
                            <i class="fas fa-sync-alt me-1"></i> Refresh Calendar
                        </button>
                        <a href="<?= base_url('index.php/provider/schedule') ?>" class="btn btn-outline-primary btn-sm mb-2">
                            <i class="fas fa-plus-circle me-1"></i> Manage Availability
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('calendar-loading')?.classList.remove('d-none');
    
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: "auto",
        headerToolbar: {
            start: 'prev,next today',
            center: 'title',
            end: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventSources: [
            {
                url: '<?= base_url("index.php/provider/getAppointmentEvents") ?>',
                method: 'GET',
                failure: function() {
                    console.error("Error loading appointment events");
                    showNotification('There was an error loading appointments!', 'danger');
                }
            },
            {
                url: '<?= base_url("index.php/provider/getAvailabilityEvents") ?>',
                method: 'GET',
                failure: function() {
                    console.error("Error loading availability events");
                    showNotification('There was an error loading availability!', 'danger');
                }
            }
        ],
        eventClick: function(info) {
            console.log("Event clicked:", info.event);
            const eventType = info.event.extendedProps?.type || 'unknown';
            
            if(eventType === 'availability') {
                if (confirm("Do you want to remove this availability slot?")) {
                    const scheduleId = info.event.id;
                    if (scheduleId) {
                        window.location.href = "<?= base_url('index.php/provider/deleteSchedule/') ?>" + scheduleId;
                    } else {
                        showNotification("Cannot identify this availability slot.", "warning");
                    }
                }
            } else if(eventType === 'appointment') {
                const appointmentId = info.event.id;
                if (appointmentId) {
                    window.location.href = "<?= base_url('index.php/provider/viewAppointment/') ?>" + appointmentId;
                } else {
                    showNotification("Cannot identify this appointment.", "warning");
                }
            }
        },
        loading: function(isLoading) {
            if (isLoading) {
                document.getElementById('calendar-loading').classList.remove('d-none');
            } else {
                document.getElementById('calendar-loading').classList.add('d-none');
            }
        },
        dayMaxEventRows: true,
        moreLinkClick: 'popover',
        eventMaxStack: 3,
        
        displayEventTime: true,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        
        eventDidMount: function(info) {
            const eventEl = info.el;
            const event = info.event;
            const title = event.title;
            const time = event.start ? event.start.toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'}) : '';
            const patient = event.extendedProps?.patient || '';
            const service = event.extendedProps?.service || '';
            const status = event.extendedProps?.status || '';
            
            let tooltipContent = `<strong>${title}</strong>`;
            if (time) tooltipContent += `<br>Time: ${time}`;
            if (service) tooltipContent += `<br>Service: ${service}`;
            if (status) tooltipContent += `<br>Status: ${status}`;
            
            eventEl.setAttribute('title', title + ' - ' + time);
        }
    });
    
    calendar.render();
    
    calendar.on('viewDidMount', function(info) {
        const view = info.view;
        const viewName = view.type;
        
        const calendarContainer = document.querySelector('.calendar-container');
        
        if (viewName.includes('timeGrid')) {
            calendarContainer.style.minHeight = '800px';
            calendar.setOption('height', 750);
        } else {
            calendarContainer.style.minHeight = '600px';
            calendar.setOption('height', 'auto');
        }
    });

    window.addEventListener('resize', function() {
        calendar.updateSize();
    });
    
    function showNotification(message, type = 'info') {
        const notificationHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    
        let notificationContainer = document.getElementById('notification-container');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'notification-container';
            notificationContainer.style.position = 'fixed';
            notificationContainer.style.top = '20px';
            notificationContainer.style.right = '20px';
            notificationContainer.style.zIndex = '9999';
            notificationContainer.style.maxWidth = '350px';
            document.body.appendChild(notificationContainer);
        }
    
        const notificationElement = document.createElement('div');
        notificationElement.innerHTML = notificationHtml;
        notificationContainer.appendChild(notificationElement);
    
        setTimeout(() => {
            notificationElement.querySelector('.alert').classList.remove('show');
            setTimeout(() => {
                if (notificationElement.parentNode) {
                    notificationElement.parentNode.removeChild(notificationElement);
                }
            }, 500);
        }, 5000);
    }
    
    const refreshBtn = document.getElementById('refreshCalendar');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            calendar.refetchEvents();
            showNotification("Calendar refreshed", "info");
        });
    }

    const filterButtons = document.querySelectorAll('.filter-btn');
    const appointmentItems = document.querySelectorAll('.appointment-item');
    const dropdownToggle = document.getElementById('filterDropdown');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');

            appointmentItems.forEach(item => {
                const status = item.getAttribute('data-status');

                if (filter === 'all' || status === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });

            dropdownToggle.textContent = this.textContent;

            const dropdownElement = dropdownToggle.closest('.dropdown');
            const bsDropdown = bootstrap.Dropdown.getOrCreateInstance(dropdownToggle);
            bsDropdown.hide();
        });
    });
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>