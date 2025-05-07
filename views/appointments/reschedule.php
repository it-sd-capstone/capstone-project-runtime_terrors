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
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
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

                            <form action="<?= base_url('index.php/appointments/reschedule') ?>" method="post">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?? '' ?>">
                                
                                <h5 class="mb-3">Select New Date and Time</h5>
                                
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="new_date" class="form-label">New Date:</label>
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
    <script>
        // Add this JavaScript to fetch available time slots when a date is selected
        document.getElementById('new_date').addEventListener('change', function() {
            const selectedDate = this.value;
            const appointmentId = <?= json_encode($appointment['appointment_id'] ?? '') ?>;
            const providerId = <?= json_encode($appointment['provider_id'] ?? '') ?>;
            const serviceId = <?= json_encode($appointment['service_id'] ?? '') ?>;
            
            if (selectedDate) {
                // Clear previous time slots
                document.getElementById('time-slots').innerHTML = '<div class="col-12"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading available time slots...</div>';
                
                // Use controller API endpoint
                fetch(`<?= base_url('index.php/api/getAvailableSlots') ?>?date=${selectedDate}&provider_id=${providerId}&service_id=${serviceId}&appointment_id=${appointmentId}`)
                    .then(response => {
                        // Get a copy of the response to inspect
                        response.clone().text().then(rawText => {
                            console.log('Raw API response:', rawText);
                        });
                        
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Available slots:', data);
                        const timeSlotsContainer = document.getElementById('time-slots');
                        timeSlotsContainer.innerHTML = '';
                        
                        if (!data || data.length === 0) {
                            timeSlotsContainer.innerHTML = '<div class="col-12"><div class="alert alert-warning">No available time slots for this date. Please select another date.</div></div>';
                            return;
                        }
                        
                        data.forEach(slot => {
                            // Format the datetime strings from API to readable time format
                            const startTime = new Date(slot.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            const endTime = new Date(slot.end).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            
                            const timeSlot = document.createElement('div');
                            timeSlot.className = 'col-md-4 mb-2';
                            timeSlot.innerHTML = `
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="time_slot" 
                                        id="slot_${slot.id}" value="${slot.id}" required>
                                    <label class="form-check-label" for="slot_${slot.id}">
                                        ${startTime} - ${endTime}
                                    </label>
                                </div>
                            `;
                            timeSlotsContainer.appendChild(timeSlot);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching time slots:', error);
                        document.getElementById('time-slots').innerHTML = 
                            '<div class="col-12"><div class="alert alert-danger">Error loading time slots. Please try again.</div></div>';
                    });
            }
        });
    </script>
</body>
</html>