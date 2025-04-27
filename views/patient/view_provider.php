<?php include VIEW_PATH . '/partials/header.php'; ?>
<?php include VIEW_PATH . '/partials/patient_header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?></h3>
                    <p class="text-muted"><?= htmlspecialchars($provider['title'] ?? 'Healthcare Provider') ?></p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if (!empty($provider['profile_image'])): ?>
                                <img src="<?= base_url('uploads/' . $provider['profile_image']) ?>" 
                                     class="img-fluid rounded" alt="Provider Photo">
                            <?php else: ?>
                                <img src="<?= base_url('assets/images/default-provider.jpg') ?>" 
                                     class="img-fluid rounded" alt="Default Provider Photo">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5>Specialization</h5>
                            <p><?= htmlspecialchars($provider['specialization'] ?? 'General Practice') ?></p>
                            
                            <h5>About</h5>
                            <p><?= htmlspecialchars($provider['bio'] ?? 'No information available.') ?></p>
                            
                            <h5>Contact Information</h5>
                            <p><strong>Email:</strong> <?= htmlspecialchars($provider['email']) ?></p>
                            <?php if (!empty($provider['phone'])): ?>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($provider['phone']) ?></p>
                            <?php endif; ?>
                            
                            <a href="<?= base_url('index.php/patient/book/' . $provider['user_id']) ?>" 
                               class="btn btn-primary">Book Appointment</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Services Offered</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($services)): ?>
                        <ul class="list-group">
                            <?php foreach ($services as $service): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($service['name']) ?>
                                    <span class="badge bg-primary rounded-pill">
                                        $<?= htmlspecialchars(isset($service['custom_price']) && $service['custom_price'] > 0 ? 
                                            $service['custom_price'] : $service['price']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No services listed for this provider.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>Availability</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($availability)): ?>
                        <ul class="list-group">
                            <?php foreach ($availability as $slot): ?>
                                <li class="list-group-item">
                                    <?= date('l, F j', strtotime($slot['availability_date'] ?? $slot['available_date'])) ?>
                                    <br>
                                    <?= date('g:i A', strtotime($slot['start_time'])) ?> - 
                                    <?= date('g:i A', strtotime($slot['end_time'])) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No availability information for this provider.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
