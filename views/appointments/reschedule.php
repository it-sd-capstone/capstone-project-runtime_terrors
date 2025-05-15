<?php
// Prevent direct access to view files
if (!defined('APP_ROOT')) {
    die("Direct access to views is not allowed");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Reschedule Appointment</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert alert"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                            <p><a href="<?= base_url('index.php/patient/appointments') ?>" class="btn btn-primary">Back to Appointments</a></p>
                        <?php else: ?>
                            <div class="appointment-details mb-4">
                                <h5>Current Appointment Details</h5>
                                <div class="card bg-light p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Provider:</strong> <?= htmlspecialchars($appointment['provider_name'] ?? 'Unknown Provider') ?></p>
                                            <p><strong>Service:</strong> <?= htmlspecialchars($appointment['service_name'] ?? 'Unknown Service') ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Date:</strong> <?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date'] ?? 'now'))) ?></p>
                                            <p><strong>Time:</strong> <?= htmlspecialchars(date('g:i A', strtotime($appointment['start_time'] ?? 'now'))) ?> - 
                                            <?= htmlspecialchars(date('g:i A', strtotime($appointment['end_time'] ?? 'now'))) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Next Available Dates Section -->
                            <div class="mb-3">
                                <h6>Quick Options: Next Available Dates</h6>
                                <div id="next-available-dates" class="d-flex flex-wrap gap-2 mb-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span>Loading next available dates...</span>
                                </div>
                            </div>
                            <form action="<?= base_url('index.php/appointments/reschedule') ?>" method="post">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?? '' ?>">
                                <!-- Add this hidden field to ensure the form includes all necessary data -->
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                
                                <h5 class="mb-3">Select New Date and Time</h5>
                                
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="new_date" class="form-label">New Date </label>
                                        <input type="date" class="form-control" id="new_date" name="new_date" required 
                                            min="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Available Time Slots:</label>
                                    <div id="time-slots" class="row">
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                Please select a date to see available time slots.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reschedule_reason" class="form-label">Reason for Rescheduling (Optional):</label>
                                    <textarea class="form-control" id="reschedule_reason" name="reschedule_reason" rows="2"></textarea>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="<?= base_url('index.php/appointments') ?>" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Reschedule Appointment</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add jQuery here -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Parse the PHP-provided available slots data with error handling
            let availableSlots = {};
            try {
                availableSlots = <?= isset($availableSlotsJson) ? $availableSlotsJson : '{}' ?>;
                console.log("Available slots loaded:", Object.keys(availableSlots).length, "dates found");
            } catch (e) {
                console.error("Error parsing available slots data:", e);
                availableSlots = {};
            }
            
            // Load the next available dates
            function loadNextAvailableDates() {
                const $nextDatesContainer = $('#next-available-dates');
                $nextDatesContainer.empty();
                
                if (Object.keys(availableSlots).length > 0) {
                    // Only show up to 5 dates
                    Object.keys(availableSlots).slice(0, 5).forEach(date => {
                        // Fix: Create date with timezone handling
                        // Add 'T00:00:00' to ensure it's interpreted as midnight in local time
                        const dateObj = new Date(`${date}T00:00:00`);
                        
                        const formattedDate = dateObj.toLocaleDateString('en-US', {
                            weekday: 'short',
                            month: 'short', 
                            day: 'numeric'
                        });
                        
                        const $btn = $('<button>', {
                            type: 'button',
                            class: 'btn btn-outline-primary m-1',
                            text: formattedDate,
                            click: function() {
                                $('#new_date').val(date).trigger('change');
                            }
                        });
                        
                        $nextDatesContainer.append($btn);
                    });
                } else {
                    $nextDatesContainer.html('<div class="alert alert-info">No available dates found.</div>');
                }
            }
            
            // Load time slots for a specific date
            function loadTimeSlotsForDate(date) {
                const $timeSlotsContainer = $('#time-slots');
                $timeSlotsContainer.empty();
                
                if (availableSlots[date] && availableSlots[date].length > 0) {
                    const $row = $('<div>', { class: 'row' });
                    
                    availableSlots[date].forEach(slot => {
                        const startTime = formatTimeForDisplay(slot.start_time);
                        
                        const $col = $('<div>', { class: 'col-md-3 col-6 mb-2' });
                        const $radioDiv = $('<div>', { class: 'form-check' });
                        
                        const $input = $('<input>', {
                            class: 'form-check-input',
                            type: 'radio',
                            name: 'time_slot',
                            id: `slot_${date}_${slot.start_time.replace(':', '')}`,
                            value: slot.start_time,
                            required: true
                        });
                        
                        const $label = $('<label>', {
                            class: 'form-check-label',
                            for: `slot_${date}_${slot.start_time.replace(':', '')}`,
                            text: startTime
                        });
                        
                        $radioDiv.append($input, $label);
                        $col.append($radioDiv);
                        $row.append($col);
                    });
                    
                    $timeSlotsContainer.append($row);
                } else {
                    $timeSlotsContainer.html('<div class="alert alert-warning">No available time slots for the selected date.</div>');
                }
            }
            
            // Format time to 12-hour format
            function formatTimeForDisplay(timeString) {
                if (!timeString) return '';
                const [hours, minutes] = timeString.split(':');
                let hour = parseInt(hours, 10);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                hour = hour % 12;
                hour = hour ? hour : 12; // Convert 0 to 12
                return `${hour}:${minutes} ${ampm}`;
            }
            
            // Event handler for date selection
            $('#new_date').on('change', function() {
                const selectedDate = $(this).val();
                loadTimeSlotsForDate(selectedDate);
            });
            
            // Initialize the UI
            loadNextAvailableDates();
        });
    </script>

</body>
</html>