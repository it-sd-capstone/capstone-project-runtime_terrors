<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container">
    <h2>Manage Appointments</h2>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>Appointment List</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
                        Add New Appointment
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Provider</th>
                                    <th>Service</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Pagination logic
                                $items_per_page = 10; // Number of items to display per page
                                $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $current_page = max(1, $current_page); // Ensure page is at least 1
                                
                                $total_items = count($appointments);
                                $total_pages = ceil($total_items / $items_per_page);
                                
                                // Ensure current page doesn't exceed total pages
                                if ($current_page > $total_pages && $total_pages > 0) {
                                    $current_page = $total_pages;
                                }
                                
                                $start_index = ($current_page - 1) * $items_per_page;
                                $paged_appointments = array_slice($appointments, $start_index, $items_per_page);
                                
                                if(!empty($paged_appointments)): 
                                    foreach ($paged_appointments as $appointment): 
                                ?>
                                <tr>
                                    <td><?= $appointment['appointment_id'] ?></td>
                                    <td><?= htmlspecialchars($appointment['patient_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($appointment['provider_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($appointment['service_name'] ?? 'N/A') ?></td>
                                    <td><?= date('F j, Y g:i A', strtotime($appointment['appointment_date'] . ' ' . $appointment['start_time'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $appointment['status'] === 'confirmed' ? 'success' :
                                            ($appointment['status'] === 'pending' ? 'warning' :
                                            ($appointment['status'] === 'canceled' ? 'danger' : 'secondary'))
                                        ?>">
                                            <?= ucfirst($appointment['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('index.php/admin/appointments/edit/' . $appointment['appointment_id']) ?>" class="btn btn-outline-primary">Edit</a>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAppointmentModal<?= $appointment['appointment_id'] ?>">Cancel</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No appointments found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination controls -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Appointment pagination">
                        <ul class="pagination justify-content-center mt-4">
                            <!-- Previous page link -->
                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $current_page - 1 ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <!-- Page number links -->
                            <?php 
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            // Show first page if not included in the range
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Display page links in the calculated range -->
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Show last page if not included in the range -->
                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $total_pages ?>"><?= $total_pages ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Next page link -->
                            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('index.php/admin/appointments/add') ?>" method="post">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="patient_id" class="form-label">Patient</label>
                            <select class="form-select" id="patient_id" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['user_id'] ?>"><?= htmlspecialchars($patient['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="provider_id" class="form-label">Provider</label>
                            <select class="form-select" id="provider_id" name="provider_id" required>
                                <option value="">Select Provider</option>
                                <?php foreach ($providers as $provider): ?>
                                <option value="<?= $provider['user_id'] ?>"><?= htmlspecialchars($provider['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="service_id" class="form-label">Service</label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="">Select Service</option>
                                <?php foreach ($services as $service): ?>
                                <option value="<?= $service['service_id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment_date" class="form-label">Appointment Date</label>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="appointment_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <!-- <option value="scheduled" selected>Scheduled</option> -->
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="canceled">Canceled</option>
                                <option value="no_show">No Show</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label">Appointment Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="in_person" selected>In Person</option>
                                <option value="virtual">Virtual</option>
                                <option value="phone">Phone</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Visit</label>
                        <textarea class="form-control" id="reason" name="reason" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Appointment Modals -->
<?php if(!empty($appointments)): ?>
    <?php foreach ($appointments as $appointment): ?>
    <div class="modal fade" id="deleteAppointmentModal<?= $appointment['appointment_id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this appointment for: <strong><?= htmlspecialchars($appointment['patient_name'] ?? 'Unknown') ?></strong> with <strong><?= htmlspecialchars($appointment['provider_name'] ?? 'Unknown') ?></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="<?= base_url('index.php/admin/appointments/cancel/' . $appointment['appointment_id']) ?>" class="btn btn-danger">Cancel Appointment</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
