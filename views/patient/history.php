<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
<div class="container mt-3">
    <div class="card">
        <div class="card-header bg-info text-white">
            Debug Information
        </div>
        <div class="card-body">
            <h5>Upcoming Appointments Data:</h5>
            <pre><?php print_r($upcomingAppointments); ?></pre>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Appointment History</h4>
                    <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-light btn-sm">Book New Appointment</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($pastAppointments) || !empty($upcomingAppointments)) : ?>
                        <ul class="nav nav-tabs mb-3" id="appointmentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="true">
                                    Upcoming Appointments
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab" aria-controls="past" aria-selected="false">
                                    Past Appointments
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="appointmentTabsContent">
                            <!-- Upcoming Appointments Tab -->
                            <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                                <?php if (!empty($upcomingAppointments)) : ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
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
                                                <?php foreach ($upcomingAppointments as $appointment) : ?>
                                                    <tr>
                                                        <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                                        <td><?= date('g:i A', strtotime($appointment['start_time'])) ?></td>
                                                        <td><?= htmlspecialchars($appointment['provider_name']) ?></td>
                                                        <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                                        <td>
                                                            <?php 
                                                            $statusClass = 'secondary';
                                                            switch($appointment['status']) {
                                                                case 'scheduled': $statusClass = 'primary'; break;
                                                                case 'confirmed': $statusClass = 'success'; break;
                                                                case 'canceled': $statusClass = 'danger'; break;
                                                                case 'no_show': $statusClass = 'warning'; break;
                                                            }
                                                            ?>
                                                            <span class="badge bg-<?= $statusClass ?>">
                                                                <?= ucfirst(htmlspecialchars($appointment['status'])) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if ($appointment['status'] != 'canceled'): ?>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="<?= base_url('index.php/patient/reschedule/' . $appointment['appointment_id']) ?>" class="btn btn-warning">Reschedule</a>
                                                                    <a href="<?= base_url('index.php/patient/cancel/' . $appointment['appointment_id']) ?>" class="btn btn-danger">Cancel</a>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted">No actions available</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <p class="mb-0">You have no upcoming appointments.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Past Appointments Tab -->
                            <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                                <?php if (!empty($pastAppointments)) : ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Provider</th>
                                                    <th>Service</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pastAppointments as $appointment) : ?>
                                                    <tr>
                                                        <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                                        <td><?= date('g:i A', strtotime($appointment['start_time'])) ?></td>
                                                        <td>Dr. <?= htmlspecialchars($appointment['provider_first_name'] . ' ' . $appointment['provider_last_name']) ?></td>
                                                        <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                                        <td>
                                                            <?php 
                                                            $statusClass = 'secondary';
                                                            $status = $appointment['status'] ?? 'completed';
                                                            switch($status) {
                                                                case 'completed': $statusClass = 'success'; break;
                                                                case 'canceled': $statusClass = 'danger'; break;
                                                                case 'no_show': $statusClass = 'warning'; break;
                                                            }
                                                            ?>
                                                            <span class="badge bg-<?= $statusClass ?>">
                                                                <?= ucfirst(htmlspecialchars($status)) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <p class="mb-0">You have no past appointments.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            <p class="mb-0">No appointment history available. Would you like to book your first appointment?</p>
                        </div>
                        <a href="<?= base_url('index.php/patient/book') ?>" class="btn btn-primary">Book Appointment</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>