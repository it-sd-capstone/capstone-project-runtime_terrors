<?php include VIEW_PATH . '/partials/header.php'; ?>
<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('index.php/admin') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('index.php/admin/providers') ?>">Providers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Provider Availability</li>
        </ol>
    </nav>
    
    <!-- Add this right after the breadcrumb section for debugging -->
    <!-- <div class="card mb-3">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Debug Information</h5>
        </div>
        <div class="card-body">
            <pre><?php print_r($availability); ?></pre>
        </div>
    </div> -->
    
    <div class="row mb-3">
        <div class="col-md-8">
            <h2>Availability Schedule for <?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($provider['title'] ?? '') ?> - <?= htmlspecialchars($provider['specialization'] ?? 'No specialization') ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= base_url('index.php/admin/providers') ?>" class="btn btn-secondary">
                Back to Providers
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Weekly Availability Schedule -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Weekly Schedule</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recurringSchedules)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            This provider hasn't set up their recurring availability schedule yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Status</th>
                                        <th>Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    $dayMap = [1, 2, 3, 4, 5, 6, 0]; // Map array index to day_of_week value
                                    
                                    foreach ($days as $index => $day):
                                        $dayOfWeek = $dayMap[$index];
                                        
                                        // Find schedule for this day
                                        $daySchedule = null;
                                        foreach ($recurringSchedules as $schedule) {
                                            if ($schedule['day_of_week'] == $dayOfWeek) {
                                                $daySchedule = $schedule;
                                                break;
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $day ?></td>
                                            <?php if ($daySchedule): ?>
                                                <td><span class="badge bg-success">Available</span></td>
                                                <td>
                                                    <?= date('g:i A', strtotime($daySchedule['start_time'])) ?> - 
                                                    <?= date('g:i A', strtotime($daySchedule['end_time'])) ?>
                                                </td>
                                            <?php else: ?>
                                                <td><span class="badge bg-secondary">Unavailable</span></td>
                                                <td>-</td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Appointments -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Upcoming Appointments</h5>
                    <?php if (!empty($appointments)): ?>
                    <span class="badge bg-primary"><?= count($appointments) ?> total</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php
                    // Filter appointments to only show confirmed ones
                    $confirmed_appointments = array_filter($appointments, function($appointment) {
                        return $appointment['status'] === 'confirmed';
                    });

                    if (empty($confirmed_appointments)): 
                    ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No confirmed upcoming appointments for this provider.
                        </div>
                    <?php else: ?>
                        <?php
                        // Pagination for appointments
                        $appts_per_page = 5; // Show 5 appointments per page
                        $current_appt_page = isset($_GET['appt_page']) ? (int)$_GET['appt_page'] : 1;
                        $current_appt_page = max(1, $current_appt_page);

                        $total_appts = count($confirmed_appointments);
                        $total_appt_pages = ceil($total_appts / $appts_per_page);

                        // Ensure current page doesn't exceed total pages
                        if ($current_appt_page > $total_appt_pages && $total_appt_pages > 0) {
                            $current_appt_page = $total_appt_pages;
                        }

                        $start_index = ($current_appt_page - 1) * $appts_per_page;
                        $paged_appointments = array_slice($confirmed_appointments, $start_index, $appts_per_page);
                        ?>

                        <div class="list-group">
                            <?php foreach ($paged_appointments as $appointment): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1">
                                            <?= date('M j, Y', strtotime($appointment['appointment_date'])) ?> at 
                                            <?= date('g:i A', strtotime($appointment['start_time'])) ?>
                                        </h6>
                                        <span class="badge bg-success">Confirmed</span>
                                    </div>
                                    <p class="mb-1">Service: <?= htmlspecialchars($appointment['service_name']) ?></p>
                                    <small class="text-muted">
                                        Patient: <?= htmlspecialchars($appointment['patient_name']) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination for appointments -->
                        <?php if ($total_appt_pages > 1): 
                            // Preserve any existing filter parameters
                            $query_params = $_GET;
                            unset($query_params['appt_page']); // Remove the page parameter to rebuild it
                            $query_params['id'] = $provider['user_id']; // Ensure provider ID is in the URL
                            $query_string = http_build_query($query_params);
                            $url = base_url('index.php/admin/viewAvailability') . "?$query_string&";
                        ?>
                        <nav aria-label="Appointment pagination" class="mt-3">
                            <ul class="pagination pagination-sm justify-content-center">
                                <!-- Previous page link -->
                                <li class="page-item <?= ($current_appt_page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $url ?>appt_page=<?= $current_appt_page - 1 ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>

                                <!-- Page number links -->
                                <?php for ($i = 1; $i <= $total_appt_pages; $i++): ?>
                                    <li class="page-item <?= ($i == $current_appt_page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $url ?>appt_page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next page link -->
                                <li class="page-item <?= ($current_appt_page >= $total_appt_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $url ?>appt_page=<?= $current_appt_page + 1 ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($confirmed_appointments) && $total_appt_pages > 0): ?>
                <div class="card-footer text-muted d-flex justify-content-between align-items-center py-2">
                    <small>Showing <?= count($paged_appointments) ?> of <?= $total_appts ?> confirmed appointments</small>
                    <?php if ($total_appt_pages > 1): ?>
                    <small>Page <?= $current_appt_page ?> of <?= $total_appt_pages ?></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <!-- Special Date Exceptions -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Special Date Exceptions</h5>
        </div>
        <div class="card-body">
            <?php
            // Collect all excluded dates from recurring schedules
            $allExcludedDates = [];
            foreach ($recurringSchedules as $schedule) {
                if (!empty($schedule['excluded_dates'])) {
                    foreach ($schedule['excluded_dates'] as $date) {
                        $allExcludedDates[] = [
                            'date' => $date,
                            'day_of_week' => $schedule['day_of_week']
                        ];
                    }
                }
            }
            ?>
            
            <?php if (empty($allExcludedDates)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    This section shows dates where the provider has special hours or is unavailable.
                    No special date exceptions have been set up for this provider.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Normal Day</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allExcludedDates as $exception): 
                                $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                $dayName = $dayNames[$exception['day_of_week']];
                            ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($exception['date'])) ?></td>
                                    <td><span class="badge bg-danger">Unavailable</span></td>
                                    <td><?= $dayName ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include VIEW_PATH . '/partials/footer.php'; ?>
