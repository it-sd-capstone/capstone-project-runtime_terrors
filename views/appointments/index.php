<?php
// Simple check to ensure $availableSlots is set
if (!isset($availableSlots)) {
    $availableSlots = []; 
}

// Security check
if (!defined('BASE_PATH') && !isset($availableSlots)) {
    die("Direct access to views is not allowed");
}
?>
<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container mt-4">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php 
                $message = '';
                switch ($_GET['success']) {
                    case 'booked':
                        $message = 'Appointment successfully booked!';
                        break;
                    case 'canceled':
                        $message = 'Appointment has been canceled.';
                        break;
                    case 'updated':
                        $message = 'Appointment status has been updated.';
                        break;
                    case 'rated':
                        $message = 'Thank you for rating your provider!';
                        break;
                    default:
                        $message = 'Operation completed successfully.';
                }
                echo $message;
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php 
                $message = '';
                switch ($_GET['error']) {
                    case 'cancel_failed':
                        $message = 'Failed to cancel appointment.';
                        break;
                    case 'missing_data':
                        $message = 'Missing required information.';
                        break;
                    case 'update_failed':
                        $message = 'Failed to update appointment status.';
                        break;
                    case 'rating_failed':
                        $message = 'Failed to submit your rating. Please try again.';
                        break;
                    default:
                        $message = 'An error occurred.';
                }
                echo $message;
            ?>
        </div>
    <?php endif; ?>

    <h1 class="mb-4">My Appointments</h1>

    
    <!-- Upcoming Appointments -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Appointments</h5>
            <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-light btn-sm">
                <i class="fas fa-plus me-1"></i> Book New
            </a>
        </div>
        <div class="card-body">
            <?php 
            $upcomingAppointments = array_filter($userAppointments, function($app) {
                return $app['status'] !== 'completed' && $app['status'] !== 'canceled' && 
                       (strtotime($app['appointment_date']) >= strtotime(date('Y-m-d')) || 
                       (strtotime($app['appointment_date']) == strtotime(date('Y-m-d')) && 
                        strtotime($app['start_time']) > strtotime(date('H:i:s'))));
            });
            ?>
            
            <?php if (!empty($upcomingAppointments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Provider</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingAppointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date']))) ?></td>
                                <td><?= htmlspecialchars(date('g:i A', strtotime($appointment['start_time']))) ?> - 
                                    <?= htmlspecialchars(date('g:i A', strtotime($appointment['end_time']))) ?></td>
                                <td><?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?></td>
                                <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusBadgeClass($appointment['status']) ?>">
                                        <?= htmlspecialchars(ucfirst($appointment['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('index.php/appointments/view?id=' . $appointment['appointment_id']) ?>" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($appointment['status'] === 'scheduled' || $appointment['status'] === 'confirmed'): ?>
                                            <a href="<?= base_url('index.php/appointments/reschedule?id=' . $appointment['appointment_id']) ?>" 
                                               class="btn btn-outline-warning" title="Reschedule">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                            
                                            <a href="<?= base_url('index.php/appointments/cancel?id=' . $appointment['appointment_id']) ?>" 
                                               class="btn btn-outline-danger" title="Cancel"
                                               onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-calendar-plus fa-3x text-muted"></i>
                    </div>
                    <h5>No Upcoming Appointments</h5>
                    <p class="text-muted">You don't have any appointments scheduled.</p>
                    <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary mt-2">
                        Book an Appointment
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>


    <!-- Past & Canceled Appointments -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Past & Canceled Appointments</h5>
        </div>
        <div class="card-body">
            <?php 
            $pastAndCanceledAppointments = array_filter($userAppointments, function($app) {
                return $app['status'] === 'completed' || 
                    $app['status'] === 'canceled' || 
                    $app['status'] === 'no_show' ||
                    strtotime($app['appointment_date']) < strtotime(date('Y-m-d'));
            });
            ?>
            
            <?php if (!empty($pastAndCanceledAppointments)): ?>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th>Provider</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastAndCanceledAppointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date']))) ?></td>
                                <td><?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?></td>
                                <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusBadgeClass($appointment['status']) ?>">
                                        <?= htmlspecialchars(ucfirst($appointment['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('index.php/appointments/history?id=' . $appointment['appointment_id']) ?>" 
                                    class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-info-circle me-1"></i> Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5>No Past or Canceled Appointments</h5>
                    <p class="text-muted">Your appointment history will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Rate Your Provider Section -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Rate Your Provider</h5>
        </div>
        <div class="card-body">
            <?php 
            $completedUnratedAppointments = $completedUnratedAppointments ?? [];
            ?>
            
            <?php if (!empty($completedUnratedAppointments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Provider</th>
                                <th>Service</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completedUnratedAppointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date']))) ?></td>
                                <td><?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?></td>
                                <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#ratingModal" 
                                            data-appointment-id="<?= $appointment['appointment_id'] ?>"
                                            data-provider-id="<?= $appointment['provider_id'] ?>"
                                            data-provider-name="<?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?>"
                                            data-service-name="<?= htmlspecialchars($appointment['service_name']) ?>"
                                            data-appointment-date="<?= date('F j, Y', strtotime($appointment['appointment_date'])) ?>">
                                        <i class="fas fa-star me-1"></i> Rate Now
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h5>All Caught Up!</h5>
                    <p class="text-muted">You've rated all your completed appointments. Thank you for your feedback!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="ratingModalLabel">Rate Your Provider</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('index.php/appointments/submitRating') ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="modal-appointment-id">
                    <input type="hidden" name="provider_id" id="modal-provider-id">
                    
                    <div class="text-center mb-4">
                        <p class="mb-1">How was your experience with</p>
                        <h5 id="modal-provider-name" class="mb-0"></h5>
                        <p class="text-muted small mb-1" id="modal-appointment-info"></p>
                    </div>
                    
                    <div class="mb-4 text-center">
                        <div class="star-rating">
                            <div class="d-flex justify-content-center mb-2">
                                <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="Outstanding"><i class="fas fa-star fa-2x"></i></label>
                                <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="Very Good"><i class="fas fa-star fa-2x"></i></label>
                                <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="Good"><i class="fas fa-star fa-2x"></i></label>
                                <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="Fair"><i class="fas fa-star fa-2x"></i></label>
                                <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="Poor"><i class="fas fa-star fa-2x"></i></label>
                            </div>
                            <div class="rating-text text-center mb-3">Select a rating</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comment" class="form-label">Comments (Optional)</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Share your experience with this provider..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submit-rating" disabled>Submit Rating</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Appointment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('index.php/appointments/updateStatus') ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="modal-appointment-id">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="no_show">No Show</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Change (Optional)</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.star-rating {
    direction: rtl;
    display: inline-block;
}

.star-rating input[type="radio"] {
    display: none;
}

.star-rating label {
    color: #ccc;
    cursor: pointer;
    padding: 0 0.2em;
    transition: all 0.3s ease;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input[type="radio"]:checked ~ label {
    color: #FFD700;
}

.rating-text {
    min-height: 24px;
    font-weight: bold;
    color: #666;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var statusModal = document.getElementById('statusModal');
        if (statusModal) {
            statusModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var appointmentId = button.getAttribute('data-appointment-id');
                var currentStatus = button.getAttribute('data-current-status');
                
                var modalAppointmentId = document.getElementById('modal-appointment-id');
                var statusSelect = document.getElementById('status');
                
                modalAppointmentId.value = appointmentId;
                statusSelect.value = currentStatus;
            });
        }
        
        var ratingModal = document.getElementById('ratingModal');
        if (ratingModal) {
            ratingModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var appointmentId = button.getAttribute('data-appointment-id');
                var providerId = button.getAttribute('data-provider-id');
                var providerName = button.getAttribute('data-provider-name');
                var serviceName = button.getAttribute('data-service-name');
                var appointmentDate = button.getAttribute('data-appointment-date');
                
                document.getElementById('modal-appointment-id').value = appointmentId;
                document.getElementById('modal-provider-id').value = providerId;
                document.getElementById('modal-provider-name').textContent = providerName;
                document.getElementById('modal-appointment-info').textContent = 
                    serviceName + ' - ' + appointmentDate;
                
                // Reset form
                document.querySelector('form').reset();
                document.querySelector('.rating-text').textContent = 'Select a rating';
                document.getElementById('submit-rating').disabled = true;
            });
        }
        
        const ratingLabels = {
            1: "Poor",
            2: "Fair",
            3: "Good",
            4: "Very Good",
            5: "Outstanding"
        };
        
        document.querySelectorAll('.star-rating input[type="radio"]').forEach(input => {
            input.addEventListener('change', function() {
                const value = this.value;
                document.querySelector('.rating-text').textContent = ratingLabels[value] || 'Select a rating';
                document.getElementById('submit-rating').disabled = false;
            });
        });
    });
</script>

<?php
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'scheduled':
            return 'primary';
        case 'confirmed':
            return 'info';
        case 'in_progress':
            return 'warning';
        case 'completed':
            return 'success';
        case 'canceled':
            return 'danger';
        case 'no_show':
            return 'secondary';
        default:
            return 'secondary';
    }
}
?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>