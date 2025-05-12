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
                    <?php if (empty($availability)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            This provider hasn't set up their availability schedule yet.
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
                                    
                                    foreach ($days as $index => $day): 
                                        // Convert day to numeric format (Monday=1, Tuesday=2, etc.)
                                        $dayNumeric = $index + 1;
                                    ?>
                                        <tr>
                                            <td><?= $day ?></td>
                                            <?php
                                            // Find this day in the availability array
                                            $dayData = null;
                                            foreach ($availability as $avail) {
                                                // Check if this availability applies to this day using numeric weekdays
                                                if (isset($avail['weekdays']) && !empty($avail['weekdays']) && $avail['is_recurring']) {
                                                    $weekdaysArray = explode(',', $avail['weekdays']);
                                                    if (in_array($dayNumeric, $weekdaysArray)) {
                                                        $dayData = $avail;
                                                        break;
                                                    }
                                                }
                                            }
                                            ?>
                                            
                                            <?php if ($dayData && $dayData['is_available']): ?>
                                                <td><span class="badge bg-success">Available</span></td>
                                                <td>
                                                    <?= date('g:i A', strtotime($dayData['start_time'])) ?> - 
                                                    <?= date('g:i A', strtotime($dayData['end_time'])) ?>
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
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Upcoming Appointments</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($appointments)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No upcoming appointments for this provider.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($appointments as $appointment): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1">
                                            <?= date('M j, Y', strtotime($appointment['appointment_date'])) ?> at 
                                            <?= date('g:i A', strtotime($appointment['start_time'])) ?>
                                        </h6>
                                        <span class="badge bg-<?= 
                                            $appointment['status'] === 'confirmed' ? 'success' : 
                                            ($appointment['status'] === 'pending' ? 'warning' : 'danger') 
                                        ?>"><?= ucfirst($appointment['status']) ?></span>
                                    </div>
                                    <p class="mb-1">Service: <?= htmlspecialchars($appointment['service_name']) ?></p>
                                    <small class="text-muted">
                                        Patient: <?= htmlspecialchars($appointment['patient_name']) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Special Date Exceptions -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Special Date Exceptions</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                This section would typically show dates where the provider has special hours or is unavailable.
            </div>
            
            <!-- This would be populated from a separate table in your database -->
            <p class="text-muted">No special date exceptions have been set up for this provider.</p>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
