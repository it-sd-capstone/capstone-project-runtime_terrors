<?php include VIEW_PATH . '/partials/header.php'; ?>

<!-- Modern Premium Styling -->
<style>
/* Modern Premium Styling */
:root {
    --primary: #4361ee;
    --secondary: #3f37c9;
    --success: #4cc9f0;
    --warning: #f72585;
    --danger: #ff4d6d;
    --light: #f8f9fa;
    --dark: #212529;
    --border-radius: 0.75rem;
    --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --transition: all 0.3s ease-in-out;
}

/* Premium Calendar Container */
.schedule-dashboard {
    background: var(--light);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.page-title {
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 1.5rem;
}

/* Calendar Styles */
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
.schedule-bottom-spacing {
    margin-bottom: 3rem; /* Adjust as needed for more/less space */
}

/* Calendar Header Enhancement */
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

.fc .fc-button-primary:hover {
    background-color: var(--secondary);
    border-color: var(--secondary);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.fc .fc-button-primary:focus:not(:active):not(.fc-button-active) {
    background-color: var(--primary);
    border-color: var(--primary);
}

/* Add navigation arrows to prev/next buttons */
.fc .fc-prev-button .fc-icon,
.fc .fc-next-button .fc-icon {
    font-size: 1.2em;
    font-weight: bold;
}

/* Premium Event Styling */
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

/* Calendar Day Styling */
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

/* Event Types */
.available-event, .regular-availability {
    background-color: #4cc9f0 !important;
    border-left: 4px solid #3a86ff !important;
}

.working-hours-event, .recurring-schedule {
    background-color: #4361ee !important;
    border-left: 4px solid #3f37c9 !important;
}

.consolidated-event {
    background-color: #4cc9f0 !important;
    border-left: 4px solid #3a86ff !important;
}

/* Action Panel Styling */
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
    background-color: var(--primary);
    color: white;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    padding: 1rem;
    border: none;
}

.nav-tabs .nav-link {
    color: rgba(255, 255, 255, 0.8);
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem 0.5rem 0 0;
    font-weight: 500;
    transition: var(--transition);
}

.nav-tabs .nav-link:hover {
    color: white;
    background-color: rgba(255, 255, 255, 0.1);
}

.nav-tabs .nav-link.active {
    color: var(--primary);
    background-color: white;
    font-weight: 600;
    border: none;
}

.tab-content {
    padding: 1.5rem;
}

.tab-pane h5, .tab-pane h6 {
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 1.5rem;
}

/* Modified padding-y class to provide more vertical space */
.py-2 {
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
}

/* Form Controls */
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

.input-group-text {
    border-radius: 0.5rem 0 0 0.5rem;
    background-color: rgba(67, 97, 238, 0.1);
    color: var(--primary);
    border: 1px solid #dee2e6;
    border-right: none;
}

.form-label {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

/* Button styling - make them smaller */
.btn {
    border-radius: 0.5rem;
    padding: 0.5rem 1rem; /* Smaller padding */
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.8rem; /* Smaller font */
    transition: var(--transition);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.btn-primary {
    background-color: var(--primary);
    border-color: var(--primary);
}

.btn-primary:hover, .btn-primary:focus {
    background-color: var(--secondary);
    border-color: var(--secondary);
}

.btn-success {
    background-color: var(--success);
    border-color: var(--success);
}

.btn-danger {
    background-color: var(--danger);
    border-color: var(--danger);
}

.btn-warning {
    background-color: var(--warning);
    border-color: var(--warning);
    color: white;
}

/* Special styling for clear day button */
#clearDayBtn {
    padding: 0.3rem 0.6rem;
    font-size: 0.75rem;
}

/* Card styling for management actions */
.action-card {
    background-color: white;
    border-radius: 0.75rem;
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
    transition: var(--transition);
    margin-bottom: 1.5rem;
    overflow: hidden;
    width: 100%; /* Ensure cards expand to full width */
}

.action-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.action-card .card-header {
    background-color: transparent;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1rem 1.5rem;
}

.action-card .card-title {
    font-weight: 600;
    margin-bottom: 0;
    color: var(--primary);
    display: flex;
    align-items: center;
}

.action-card .card-title i {
    margin-right: 0.5rem;
}

.action-card .card-body {
    padding: 1.5rem;
}

/* Legend styling */
.schedule-legend {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    padding: 0.75rem;
    background-color: rgba(248, 249, 250, 0.8);
    backdrop-filter: blur(10px);
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    margin: 0.25rem 1rem;
}

.legend-color {
    width: 1rem;
    height: 1rem;
    border-radius: 0.25rem;
    margin-right: 0.5rem;
}

.legend-label {
    font-size: 0.85rem;
    color: var(--dark);
}

/* Notification styling */
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
.fc-day-selected {
    background-color: rgba(0, 123, 255, 0.1) !important;
    border: 2px solid #007bff !important;
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

/* Loading animations */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(3px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    border-radius: var(--border-radius);
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(67, 97, 238, 0.1);
    border-radius: 50%;
    border-top-color: var(--primary);
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .calendar-container {
        min-height: 600px;
    }
    
    .action-panel {
        margin-top: 1.5rem;
    }
}

@media (max-width: 768px) {
    .fc .fc-toolbar.fc-header-toolbar {
        flex-direction: column;
        gap: 1rem;
    }
    
    .fc-toolbar-chunk {
        display: flex;
        justify-content: center;
    }
    
    .calendar-container {
        min-height: 500px;
        padding: 0.5rem;
    }
}
</style>

<div class="container-fluid mt-3 schedule-bottom-spacing">
    <!-- Introduction Card with Instructions -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm bg-white">
                <div class="card-body py-2">
                    <h5 class="text-primary mb-2">Schedule Management</h5>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Manage your availability in three simple steps:<br>
                        1. Set your recurring schedule for regular working hours,<br>
                        2. Generate availability slots based on your schedule<br>
                        3. Fine-tune individual slots as needed by clicking on dates in the calendar.<br>
                        <i class="fas fa-info-circle me-1"></i>
                        Use the tabs in the "Actions" panel to access these features.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content: Calendar + Action Panel -->
    <div class="row">
        <!-- Calendar View - Make it the main focus -->
        <div class="col-lg-8 calendar-column">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-calendar-alt me-1"></i> Your Schedule Calendar
                    </div>
                    <div>
                        <div class="form-check form-switch d-inline-block me-2">
                        <input class="form-check-input" type="checkbox" id="consolidatedView" checked>
                            <label class="form-check-label small text-white" for="consolidatedView">
                                Consolidated view
                            </label>
                        </div>
                        <div id="calendar-loading" class="spinner-border spinner-border-sm text-light d-none" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 p-sm-2">
                    <!-- Calendar Legend -->
                    <div class="d-flex flex-wrap justify-content-center bg-light p-2 border-bottom small">
                        <div class="me-3 mb-1">
                            <span class="badge bg-success p-1">&nbsp;</span>
                            <span>Available Slots</span>
                        </div>
                        <div class="me-3 mb-1">
                            <span class="badge bg-info p-1">&nbsp;</span>
                            <span>Working Hours</span>
                        </div>
                        <div>
                            <i class="fas fa-info-circle text-muted me-1"></i>
                            <span>Click to delete, drag to reschedule</span>
                        </div>
                    </div>
                    
                    <!-- Calendar will be rendered here -->
                    <div id="calendar" class="calendar-container"></div>
                </div>
            </div>
        </div>
        
        <!-- Action Panel - Tabbed interface for better organization -->
        <div class="col-lg-4 action-panel-column">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <ul class="nav nav-tabs card-header-tabs" id="actionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="recurring-tab" data-bs-toggle="tab" data-bs-target="#recurring-panel"
                                    type="button" role="tab" aria-selected="true">
                                <i class="fas fa-sync"></i> Recurring
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage-panel"
                                    type="button" role="tab" aria-selected="false">
                                <i class="fas fa-cog"></i> Manage
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Recurring Schedule Panel -->
                        <div class="tab-pane fade show active" id="recurring-panel" role="tabpanel" aria-labelledby="recurring-tab">
                            <h6 class="card-title"><i class="fas fa-sync me-1"></i> Set Recurring Hours</h6>
                            <form method="POST" action="<?= base_url('index.php/provider/processRecurringSchedule') ?>" class="row g-2">
                                <div class="col-md-12 mb-2">
                                    <label class="form-label small fw-bold">Day of Week:</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                        <select class="form-select" name="day_of_week" required>
                                            <option value="1">Monday</option>
                                            <option value="2">Tuesday</option>
                                            <option value="3">Wednesday</option>
                                            <option value="4">Thursday</option>
                                            <option value="5">Friday</option>
                                            <option value="6">Saturday</option>
                                            <option value="0">Sunday</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Start Time:</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-hourglass-start"></i></span>
                                        <input type="time" class="form-control" name="start_time" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">End Time:</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-hourglass-end"></i></span>
                                        <input type="time" class="form-control" name="end_time" required>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="repeat_weekly" name="repeat_weekly" checked>
                                        <label class="form-check-label small" for="repeat_weekly">
                                            Repeat weekly
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label for="repeat_until" class="form-label small fw-bold">Until:</label>
                                    <input type="date" class="form-control form-control-sm" id="repeat_until" name="repeat_until" min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-save me-1"></i>Save Schedule
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <!-- Add this right after the existing form in the recurring-panel div -->
                            <hr class="my-3">
                            <h6 class="card-title mt-3"><i class="fas fa-trash-alt me-1 text-danger"></i> Delete Work Schedule</h6>
                            <form id="deleteWorkScheduleForm" class="row g-2">
                                <div class="col-md-12 mb-2">
                                    <label class="form-label small fw-bold">Delete Type:</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-calendar-times"></i></span>
                                        <select class="form-select" id="delete_type" name="delete_type" required>
                                            <option value="" selected disabled>Select what to delete...</option>
                                            <option value="day">Single Day</option>
                                            <!-- <option value="month">Entire Month</option> -->
                                            <!-- <option value="custom">Custom Range</option> -->
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Single Day Selection (shows when day is selected) -->
                                <div class="col-md-12 mb-2 delete-option" id="day_option" style="display:none">
                                    <label class="form-label small fw-bold">Select Day:</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                        <input type="date" class="form-control" id="delete_day" name="delete_day">
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mb-2 delete-option" id="month_option" style="display:none">
                                    <label class="form-label small fw-bold">Select Month:</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" class="form-control" id="delete_month" name="delete_month" placeholder="Select month" readonly>
                                    </div>
                                </div>
                                
                                <!-- Custom Range (shows when custom is selected) -->
                                <div class="col-12 delete-option" id="custom_option" style="display:none">
                                    <div class="row g-2">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label small fw-bold">From:</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="fas fa-calendar-minus"></i></span>
                                                <input type="date" class="form-control" id="delete_start" name="delete_start">
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label small fw-bold">To:</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="fas fa-calendar-plus"></i></span>
                                                <input type="date" class="form-control" id="delete_end" name="delete_end">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="delete_confirm" name="delete_confirm" required>
                                        <label class="form-check-label small text-danger" for="delete_confirm">
                                            I understand this action cannot be undone
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mt-2">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-danger btn-sm" id="deleteWorkScheduleBtn" disabled>
                                            <i class="fas fa-trash-alt me-1"></i>Delete Schedule
                                        </button>
                                    </div>
                                    <div class="form-text text-muted small mt-1">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This will permanently delete selected work schedule slots.
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Management Panel -->
                        <div class="tab-pane fade" id="manage-panel" role="tabpanel" aria-labelledby="manage-tab">
                            <h6 class="card-title"><i class="fas fa-cog me-1"></i> Manage Schedule</h6>
                            
                            <!-- Clear Selected Day -->
                            <div class="card bg-light mb-3">
                                <div class="card-body py-2">
                                    <h6 class="card-title"><i class="fas fa-trash-alt me-1 text-danger"></i> Clear Selected Day</h6>
                                    <p class="card-text small mb-2">Delete all availability for a specific day.</p>
                                    <button id="clearDayBtn" class="btn btn-danger btn-sm w-100">
                                        <i class="fas fa-trash-alt me-1"></i>Clear Selected Day
                                    </button>
                                    <div id="selectedDayDisplay" class="text-muted small mt-1">
                                        Select a day from the calendar first
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mass Delete -->
                            <div class="card bg-light mb-3">
                                <div class="card-body py-2">
                                    <h6 class="card-title"><i class="fas fa-trash-alt me-1 text-danger"></i> Mass Delete</h6>
                                    <form id="deleteRangeForm" class="row g-2 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold mb-0">From:</label>
                                            <input type="date" class="form-control form-control-sm" id="delete_start_date" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold mb-0">To:</label>
                                            <input type="date" class="form-control form-control-sm" id="delete_end_date" required>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash-alt me-1"></i>Delete Range
                                                </button>
                                            </div>
                                            <div class="form-text text-danger small">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                This will permanently delete all availability in the range.
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Generate Slots -->
                            <div class="card bg-light mb-3">
                                <div class="card-body py-2">
                                    <h6 class="card-title"><i class="fas fa-magic me-1 text-warning"></i> Generate Slots</h6>
                                    <p class="card-text small mb-2">Create bookable slots for your services using your recurring schedule.</p>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Select Services to Generate:</label>
                                        <div class="service-selection">
                                            <?php if (!empty($provider_services)): ?>
                                                <?php foreach ($provider_services as $service): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input service-checkbox" type="checkbox"
                                                        value="<?= $service['service_id'] ?>"
                                                        id="service_<?= $service['service_id'] ?>"
                                                        data-duration="<?= $service['custom_duration'] ?: $service['duration'] ?>">
                                                    <label class="form-check-label" for="service_<?= $service['service_id'] ?>">
                                                        <?= htmlspecialchars($service['name']) ?>
                                                        (<?= $service['custom_duration'] ?: $service['duration'] ?> min)
                                                    </label>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="alert alert-warning py-2">
                                                    <small>You don't have any services set up. Please add services first.</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-2 g-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold mb-0">Distribution:</label>
                                            <select id="slotDistribution" class="form-select form-select-sm">
                                                <option value="alternate" selected>Alternate services</option>
                                                <option value="blocks">Blocks of same service</option>
                                                <option value="priority">Priority order</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold mb-0">Period:</label>
                                            <select id="generatePeriod" class="form-select form-select-sm">
                                                <option value="1">1 week</option>
                                                <option value="2" selected>2 weeks</option>
                                                <option value="4">4 weeks</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-text small mb-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Slots will be generated based on your recurring schedule. Make sure your recurring schedule is set up correctly.
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button id="generateSlotsBtn" class="btn btn-warning btn-sm">
                                            <i class="fas fa-magic me-1"></i>Generate Slots
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Required Libraries: Bootstrap, FullCalendar -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
    // Define base URL for AJAX calls
    var base_url = '<?= isset($base_url) ? $base_url : rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/' ?>';
    
    document.addEventListener('DOMContentLoaded', function() {

        
        // Show loading indicator
        document.getElementById('calendar-loading')?.classList.remove('d-none');
        
        var calendarEl = document.getElementById('calendar');
        var selectedDate = null; // Store the selected date
        
        // First create the calendar with basic configuration
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 700,
            dayMaxEventRows: 3,
            moreLinkClick: 'day',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            themeSystem: 'bootstrap5',
            aspectRatio: 1.35,
            contentHeight: "auto",
            editable: true,
            selectable: true,
            nowIndicator: true,
            dayMaxEvents: true,
            
            // Add these settings to improve week view
            slotMinWidth: 100, // Make columns wider in week view
            slotDuration: '00:30:00', // 30-minute slots for better granularity
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            },
            
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            },
            eventDisplay: 'auto',
            
            // Add this viewDidMount callback to refresh events when view changes
            viewDidMount: function(viewInfo) {
                // This will trigger a refetch with the current view type
                calendar.refetchEvents();
                
                // Log the current view - helpful for debugging
                console.log("Current view:", viewInfo.view.type);
                
                // Apply special styling to week view
                if (viewInfo.view.type === 'timeGridWeek') {
                    // Add some custom styling to make week view more readable
                    document.querySelectorAll('.fc-timegrid-col').forEach(col => {
                        col.style.minWidth = '120px'; // Force wider columns
                    });
                }
            },
            
            // Add eventDidMount to apply custom formatting if needed
            eventDidMount: function(info) {
                // For consolidated events in month view, add a tooltip
                if (info.event.extendedProps && 
                    (info.event.extendedProps.type === 'consolidated' || 
                     info.event.extendedProps.type === 'consolidated_recurring')) {
                    
                    $(info.el).addClass('tooltip-event');
                    
                    $(info.el).tooltip({
                        title: 'Click for detailed view',
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body',
                        delay: { show: 200, hide: 0 }
                    });
                            
                        // Add manual trigger to hide tooltip when mouse leaves
                        $(info.el).on('mouseleave', function() {
                            $(this).tooltip('hide');
                        });
                    }
                
                // Always show time in the title for recurring work hours (both in month and week views)
                if (info.event.extendedProps && 
                    (info.event.extendedProps.type === 'recurring' || 
                     info.event.extendedProps.title === 'Working Hours' ||
                     info.event.title === 'Working Hours')) {
                
                    const start = info.event.start;
                    const end = info.event.end;
                
                    if (start && end) {
                        const formattedStart = start.toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'});
                        const formattedEnd = end.toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'});
                    
                        // Always update title to include times for working hours
                        info.event.setProp('title', `Working Hours ${formattedStart}-${formattedEnd}`);
                    }
                }
            
                // If in day/week view and event doesn't have times in title, add them for other events too
                if (calendar.view.type !== 'dayGridMonth') {
                    const title = info.event.title;
                
                    // Only update if it doesn't already have time info
                    if (!title.includes('-') && info.event.start && info.event.end && 
                        !title.includes('Working Hours')) { // Skip working hours as we handled them above
                    
                        const start = info.event.start;
                        const end = info.event.end;
                    
                        if (start && end) {
                            const formattedStart = start.toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'});
                            const formattedEnd = end.toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'});
                        
                            if (title === 'Available') {
                                info.event.setProp('title', `Available ${formattedStart}-${formattedEnd}`);
                            }
                        }
                    }
                }
            },
            
            // Add event click handler
            eventClick: function(info) {
                   // Hide any active tooltips first
                    $('.tooltip-event').tooltip('hide');
                // For consolidated events in month view, switch to day view
                if (info.event.extendedProps && 
                    (info.event.extendedProps.type === 'consolidated' || 
                     info.event.extendedProps.type === 'consolidated_recurring')) {
                
                    // Navigate to day view for this date
                    calendar.gotoDate(info.event.start);
                    calendar.changeView('timeGridDay');
                
                    // Prevent the default action
                    info.jsEvent.preventDefault();
                }
            },
            
            // Add this event handler inside your calendar initialization
            dateClick: function(info) {
                // Hide any active tooltips
                $('.tooltip-event').tooltip('hide');
                $('.tooltip').tooltip('dispose');
                $('.tooltip').remove();
                
                // Always use an ISO string format YYYY-MM-DD for consistency
                selectedDate = info.dateStr || formatDateLocal(info.date);
                
                // Log for debugging
                console.log('Date selected:', selectedDate);
                
                // Update the display text
                document.getElementById('selectedDayDisplay').textContent =
                    'Selected: ' + info.date.toLocaleDateString();
                
                // Add visual feedback in month view
                if (calendar.view.type === 'dayGridMonth') {
                    // Remove previous highlighting
                    $('.fc-day-selected').removeClass('fc-day-selected');
                    // Add highlighting to the clicked day
                    $(info.dayEl).addClass('fc-day-selected');
                }
                
                // Check if element exists before updating
                const availInput = document.querySelector('input[name="availability_date"]');
                if (availInput) availInput.value = selectedDate;
            }

        });

        // AFTER calendar is initialized, add the event source
        calendar.addEventSource({
            url: "<?= base_url('index.php/provider/getProviderSchedules') ?>",
            method: "GET",
            extraParams: function() {
                return {
                    view: calendar.view.type, // Now this will work because calendar is initialized
                    consolidated: document.getElementById('consolidatedView').checked ? 1 : 0
                };
            },
            color: '#17a2b8',
            textColor: 'white',
            failure: function() {
                showNotification("Failed to load provider schedules", "danger");
            }
        });

        // Then render the calendar
        calendar.render();
        
        // Function to update availability
        function updateAvailability(event) {
            const eventId = event.id;
            const eventType = event.extendedProps?.type || 'regular';
            const startStr = event.start.toISOString();
            const endStr = event.end ? event.end.toISOString() :
                        new Date(event.start.getTime() + 30*60000).toISOString();
        
            const updatedData = {
                id: eventId,
                type: eventType,
                date: startStr.split('T')[0],
                start_time: startStr.split('T')[1].substring(0, 5),
                end_time: endStr.split('T')[1].substring(0, 5)
            };
        
            fetch("<?= base_url('index.php/provider/updateSchedule') ?>", {
                method: "POST",
                body: JSON.stringify(updatedData),
                headers: {
                    "Content-Type": "application/json",
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification("Updated successfully", "success");
                } else {
                    showNotification("Update failed: " + (data.message || "Unknown error"), "danger");
                    calendar.refetchEvents();
                }
            })
            .catch(error => {
                console.error("Error:", error);
                showNotification("Update error", "danger");
                calendar.refetchEvents();
            });
        }
        
        // Function to clear all availability for a specific day
        function clearDayAvailability(date) {
            fetch('<?= base_url('index.php/provider/clearDayAvailability') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    date: date
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message || 'All availability cleared for this date', 'success');
                    calendar.refetchEvents();
                } else {
                    showNotification(data.message || 'Failed to clear availability', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error clearing day availability', 'danger');
            });
        }
        
        // Function to show notification
        function showNotification(message, type = 'info') {
            // Create notification element
            const notificationHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        
            // Check if notification container exists, create if not
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
        
            // Add notification to container
            const notificationElement = document.createElement('div');
            notificationElement.innerHTML = notificationHtml;
            notificationContainer.appendChild(notificationElement);
        
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                notificationElement.querySelector('.alert').classList.remove('show');
                setTimeout(() => {
                    if (notificationElement.parentNode) {
                        notificationElement.parentNode.removeChild(notificationElement);
                    }
                }, 500);
            }, 5000);
        }
        
        // Toggle consolidated view
        document.getElementById('consolidatedView').addEventListener('change', function() {
            // Refresh the calendar to apply the new view mode
            calendar.refetchEvents();
        
            // Store preference in localStorage
            localStorage.setItem('consolidatedView', this.checked ? '1' : '0');
        });
        
        // Load preference on page load
        const savedPreference = localStorage.getItem('consolidatedView');
        if (savedPreference !== null) {
            document.getElementById('consolidatedView').checked = (savedPreference === '1');
        }
        
        // Clear Day button handler - This now gets the currently selected date from our tracking variable
        document.getElementById('clearDayBtn').addEventListener('click', function() {
            // Use the selected date or fall back to current displayed date
            const dateToUse = selectedDate || calendar.getDate().toISOString().split('T')[0];
            
            console.log('Attempting to clear date:', dateToUse, 'Selected date was:', selectedDate);
            
            if (!dateToUse) {
                showNotification('Please select a day first by clicking on the calendar', 'warning');
                return;
            }
            
            if (confirm(`Are you sure you want to delete ALL availability for ${dateToUse}?`)) {
                clearDayAvailability(dateToUse);
            }
        });

        // Form submission handlers
        document.querySelectorAll('form').forEach(form => {
            const formAction = form.getAttribute('action') || '';
            if (formAction.includes('processUpdateAvailability') || formAction.includes('processRecurringSchedule')) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const loadingBtn = this.querySelector('button[type="submit"]');
                    let originalBtnText = "";
                
                    if (loadingBtn) {
                        originalBtnText = loadingBtn.innerHTML;
                        loadingBtn.disabled = true;
                        loadingBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                    }
                
                    const formData = new FormData(this);
                
                    // Add recurring schedule options if relevant
                    if (formAction.includes('processRecurringSchedule')) {
                        const repeatWeekly = this.querySelector('#repeat_weekly')?.checked ? '1' : '0';
                        const repeatUntil = this.querySelector('#repeat_until')?.value || '';
                        formData.append('repeat_weekly', repeatWeekly);
                        formData.append('repeat_until', repeatUntil);
                    }
                
                    fetch(formAction, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message || 'Schedule updated successfully', 'success');
                        
                            // Reset form if desired
                            if (formAction.includes('processUpdateAvailability')) {
                                this.reset();
                            }
                        
                            // Refresh the calendar with new data
                            calendar.refetchEvents();
                        } else {
                            showNotification(data.message || 'Failed to update schedule', 'danger');
                        }
                    
                        // Restore button state
                        if (loadingBtn) {
                            loadingBtn.disabled = false;
                            loadingBtn.innerHTML = originalBtnText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error updating schedule', 'danger');
                    
                        // Restore button state
                        if (loadingBtn) {
                            loadingBtn.disabled = false;
                            loadingBtn.innerHTML = originalBtnText;
                        }
                    });
                });
            }
        });
        
        // Delete Range form handler
        document.getElementById('deleteRangeForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
        
            const startDate = document.getElementById('delete_start_date').value;
            const endDate = document.getElementById('delete_end_date').value;
        
            if (!startDate || !endDate) {
                showNotification('Please select both start and end dates', 'warning');
                return;
            }
        
            // Validate date range
            if (new Date(startDate) > new Date(endDate)) {
                showNotification('Start date must be before end date', 'warning');
                return;
            }
        
            if (confirm(`Are you sure you want to delete ALL availability between ${startDate} and ${endDate}? This cannot be undone.`)) {
                const loadingBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = loadingBtn.innerHTML;
                loadingBtn.disabled = true;
                loadingBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
            
                fetch('<?= base_url('index.php/provider/deleteAvailabilityRange') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        start_date: startDate,
                        end_date: endDate
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message || `Successfully deleted availability between ${startDate} and ${endDate}`, 'success');
                        calendar.refetchEvents();
                        this.reset();
                    } else {
                        showNotification(data.message || 'Failed to delete availability range', 'danger');
                    }
                
                    loadingBtn.disabled = false;
                    loadingBtn.innerHTML = originalBtnText;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error deleting availability range', 'danger');
                
                    loadingBtn.disabled = false;
                    loadingBtn.innerHTML = originalBtnText;
                });
            }
        });
        
        // Generate Slots button handler
        $('#generateSlotsBtn').on('click', function() {
            // Get selected services
            const selectedServices = [];
            $('.service-checkbox:checked').each(function() {
                selectedServices.push({
                    id: $(this).val(),
                    duration: $(this).data('duration')
                });
            });
        
            if (selectedServices.length === 0) {
                alert('Please select at least one service to generate slots for.');
                return;
            }
        
            const distribution = $('#slotDistribution').val();
            const period = $('#generatePeriod').val();
        
            // Show loading indicator
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...');
            $(this).prop('disabled', true);
        
            console.log('Sending request with:', {
                services: selectedServices,
                distribution: distribution,
                period: period
            });
        
            // Build a proper URL - get the base part of the current URL
            let baseUrl = window.location.href.split('/provider/')[0];
        
            // Call backend to generate slots
            $.ajax({
                url: baseUrl + '/provider/generateServiceSlots',
                type: 'POST',
                dataType: 'json',
                data: {
                    services: JSON.stringify(selectedServices),
                    distribution: distribution,
                    period: period
                },
                success: function(response) {
                    console.log('Response received:', response);
            
                    if (response && response.success === true) {
                        alert('Successfully generated ' + response.count + ' availability slots.');
                        // Refresh calendar
                        if (typeof calendar !== 'undefined') {
                            calendar.refetchEvents();
                        }
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Server returned an invalid response';
                        alert('Error: ' + errorMsg);
                        console.error('Error response:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    console.error('Response text:', xhr.responseText.substring(0, 500) + '...'); // Show just the start
            
                    alert('Error: Could not connect to server. Check console for details.');
                },
                complete: function() {
                    // Restore button
                    $('#generateSlotsBtn').html('<i class="fas fa-magic me-1"></i>Generate Slots');
                    $('#generateSlotsBtn').prop('disabled', false);
                }
            });
        });
        
        // Initialize with today's date in forms
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="availability_date"]').value = today;
        document.getElementById('delete_start_date').value = today;
        
        // Set end date to 2 weeks from now by default
        const twoWeeksFromNow = new Date();
        twoWeeksFromNow.setDate(twoWeeksFromNow.getDate() + 14);
        document.getElementById('delete_end_date').value = twoWeeksFromNow.toISOString().split('T')[0];
        
        // Tab switching handler to update form data based on selected tab
        document.querySelectorAll('#actionTabs button[data-bs-toggle="tab"]').forEach(function(button) {
            button.addEventListener('show.bs.tab', function(event) {
                // If switching to "Add" tab and a date is selected, set the date field
                if (event.target.id === 'add-tab' && selectedDate) {
                    document.querySelector('input[name="availability_date"]').value = selectedDate;
                }
            });
        });
        
        // Hide loading indicator when complete
        document.getElementById('calendar-loading')?.classList.add('d-none');
    });
</script>

<script>
// Optional: Add real-time validation for time fields
document.querySelectorAll('input[type="time"]').forEach(function(input) {
    input.addEventListener('change', function() {
        // If this is start time and we also have an end time input as next sibling
        if (this.name === 'start_time' && this.form.querySelector('input[name="end_time"]')) {
            const startTime = this.value;
            const endTimeInput = this.form.querySelector('input[name="end_time"]');
            const endTime = endTimeInput.value;
            
            // If end time is set and is before start time
            if (endTime && endTime <= startTime) {
                // Calculate a default end time (1 hour later)
                const startParts = startTime.split(':');
                let endHour = parseInt(startParts[0]) + 1;
                if (endHour > 23) endHour = 23;
                
                const newEndTime = endHour.toString().padStart(2, '0') + ':' + startParts[1];
                endTimeInput.value = newEndTime;
                
                showNotification('End time automatically adjusted to be after start time', 'info');
            }
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Function to replace the icons
  function replaceCalendarIcons() {
    const prevButtons = document.querySelectorAll('.fc-prev-button');
    const nextButtons = document.querySelectorAll('.fc-next-button');
    
    prevButtons.forEach(button => {
      button.innerHTML = '<i class="fas fa-chevron-left"></i>';
    });
    
    nextButtons.forEach(button => {
      button.innerHTML = '<i class="fas fa-chevron-right"></i>';
    });
  }
  
  replaceCalendarIcons();
  
  setTimeout(replaceCalendarIcons, 500);
  
  document.addEventListener('click', function(e) {
    if (e.target.closest('.fc-button')) {
      setTimeout(replaceCalendarIcons, 100);
    }
  });
});
</script>
<script>
// Handle showing/hiding the appropriate date inputs based on selection
document.addEventListener('DOMContentLoaded', function() {
    // Get references to form elements
    const deleteTypeSelect = document.getElementById('delete_type');
    const deleteOptions = document.querySelectorAll('.delete-option');
    const deleteConfirm = document.getElementById('delete_confirm');
    const deleteButton = document.getElementById('deleteWorkScheduleBtn');
    const deleteForm = document.getElementById('deleteWorkScheduleForm');
    
    // Initialize month picker with dropdown selectors
    setupMonthPicker();
    
    // Show/hide appropriate fields based on delete type selection
    deleteTypeSelect.addEventListener('change', function() {
        // Hide all options first
        deleteOptions.forEach(option => {
            option.style.display = 'none';
        });
        
        // Show the selected option
        const selectedOption = this.value;
        if (selectedOption) {
            document.getElementById(selectedOption + '_option').style.display = 'block';
        }
        
        // Set today's date as default for single day
        if (selectedOption === 'day') {
            document.getElementById('delete_day').valueAsDate = new Date();
        }
        
        // Set current month for month selection
        if (selectedOption === 'month') {
            // The month will be set by the setupMonthPicker function
            updateMonthSelectorsToCurrentMonth();
        }
        
        // Set reasonable defaults for custom range
        if (selectedOption === 'custom') {
            const today = new Date();
            document.getElementById('delete_start').valueAsDate = today;
            
            const nextWeek = new Date();
            nextWeek.setDate(today.getDate() + 7);
            document.getElementById('delete_end').valueAsDate = nextWeek;
        }
    });
    
    // Enable/disable delete button based on confirmation checkbox
    deleteConfirm.addEventListener('change', function() {
        deleteButton.disabled = !this.checked;
    });
    
    // Handle form submission
    deleteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get the selected delete type
        const deleteType = deleteTypeSelect.value;
        let startDate, endDate;
        
        // Calculate the appropriate date range based on selection
        if (deleteType === 'day') {
            startDate = document.getElementById('delete_day').value;
            endDate = startDate; // Same day for single day deletion
        } else if (deleteType === 'month') {
            // Get values from month and year selectors
            const monthSelect = document.querySelector('.month-select');
            const yearSelect = document.querySelector('.year-select');
            
            if (monthSelect && yearSelect) {
                const year = yearSelect.value;
                const month = parseInt(monthSelect.value) + 1; // JavaScript months are 0-indexed
                
                // Calculate the last day of the selected month
                const lastDay = new Date(year, month, 0).getDate();
                
                // Format dates
                const monthStr = month < 10 ? '0' + month : month;
                startDate = `${year}-${monthStr}-01`; // First day of month
                endDate = `${year}-${monthStr}-${lastDay}`; // Last day of month
            } else {
                // Fallback to the hidden input if selectors aren't found
                const monthValue = document.getElementById('delete_month').value; // Format: YYYY-MM
                if (monthValue) {
                    const year = monthValue.split('-')[0];
                    const month = monthValue.split('-')[1];
                    const lastDay = new Date(year, month, 0).getDate();
                    
                    startDate = `${monthValue}-01`; // First day of month
                    endDate = `${monthValue}-${lastDay}`; // Last day of month
                }
            }
        } else if (deleteType === 'custom') {
            startDate = document.getElementById('delete_start').value;
            endDate = document.getElementById('delete_end').value;
        }
        
        // Validate dates
        if (!startDate || !endDate) {
            showAlert('danger', 'Please select valid dates');
            return;
        }
        
        // Send to backend
        deleteWorkSchedule(startDate, endDate);
    });
    
    // Function to send delete request to backend
    function deleteWorkSchedule(startDate, endDate) {
        // Show loading indicator
        deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
        deleteButton.disabled = true;
        
        // Make AJAX request to delete the schedule
        fetch(base_url + 'index.php/provider/deleteWorkSchedule', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', 'Successfully deleted work schedule slots in the selected range');
                
                // Refresh the calendar to show changes
                calendar.refetchEvents();
                
                // Reset form
                deleteForm.reset();
                deleteOptions.forEach(option => {
                    option.style.display = 'none';
                });
            } else {
                // Show error message
                showAlert('danger', data.message || 'Failed to delete work schedule slots');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while deleting work schedule slots');
        })
        .finally(() => {
            // Reset button state
            deleteButton.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Delete Schedule';
            deleteButton.disabled = false;
            deleteConfirm.checked = false;
        });
    }
    
    // Helper function to show alerts
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert alert before the form
        deleteForm.parentNode.insertBefore(alertDiv, deleteForm);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
    }
    
    // Function to set up the month picker with dropdowns
    function setupMonthPicker() {
        const monthInput = document.getElementById('delete_month');
        const monthContainer = document.getElementById('month_option');
        
        if (monthInput && monthContainer) {
            // Create month and year selectors
            const monthSelect = document.createElement('select');
            monthSelect.className = 'form-select form-select-sm month-select me-2';
            monthSelect.style.width = '140px';
            
            const yearSelect = document.createElement('select');
            yearSelect.className = 'form-select form-select-sm year-select';
            yearSelect.style.width = '100px';
            
            // Create wrapper with proper styling
            const selectWrapper = document.createElement('div');
            selectWrapper.className = 'd-flex align-items-center mt-1';
            
            // Add icon for better UI
            const iconSpan = document.createElement('span');
            iconSpan.className = 'me-2 text-primary';
            iconSpan.innerHTML = '<i class="fas fa-calendar-alt fa-lg"></i>';
            
            selectWrapper.appendChild(iconSpan);
            selectWrapper.appendChild(monthSelect);
            selectWrapper.appendChild(yearSelect);
            
            // Replace the input with our selectors
            monthInput.style.display = 'none';
            monthContainer.querySelector('.input-group').style.display = 'none';
            monthContainer.appendChild(selectWrapper);
            
            // Populate months
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
            months.forEach((month, index) => {
                const option = document.createElement('option');
                option.value = index;
                option.textContent = month;
                monthSelect.appendChild(option);
            });
            
            // Populate years (current year - 2 to current year + 3)
            const currentYear = new Date().getFullYear();
            for (let year = currentYear - 2; year <= currentYear + 3; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }
            
            // Set defaults to current month/year
            const now = new Date();
            monthSelect.value = now.getMonth();
            yearSelect.value = now.getFullYear();
            
            // Update hidden input when selections change
            function updateMonthInput() {
                const year = yearSelect.value;
                const month = parseInt(monthSelect.value) + 1;
                const formattedMonth = month < 10 ? '0' + month : month;
                monthInput.value = `${year}-${formattedMonth}`;
            }
            
            monthSelect.addEventListener('change', updateMonthInput);
            yearSelect.addEventListener('change', updateMonthInput);
            
            // Initialize
            updateMonthInput();
        }
    }
    
    // Function to update the month selectors to the current month
    function updateMonthSelectorsToCurrentMonth() {
        const monthSelect = document.querySelector('.month-select');
        const yearSelect = document.querySelector('.year-select');
        
        if (monthSelect && yearSelect) {
            const now = new Date();
            monthSelect.value = now.getMonth();
            yearSelect.value = now.getFullYear();
            
            // Also update the hidden input
            const monthInput = document.getElementById('delete_month');
            if (monthInput) {
                const month = now.getMonth() + 1;
                const formattedMonth = month < 10 ? '0' + month : month;
                monthInput.value = `${now.getFullYear()}-${formattedMonth}`;
            }
        }
    }
});
</script>
<?php include VIEW_PATH . '/partials/footer.php'; ?>
     