<?php include VIEW_PATH . '/partials/header.php'; ?>
<style>
    .availability-container {
        max-height: 300px;
        overflow-y: auto;
        border-radius: 0.25rem;
    }
    
    .provider-info-card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .provider-header {
        background: linear-gradient(to right, #4e73df, #224abe);
        color: white;
        padding: 1.5rem;
    }
    
    .provider-photo {
        border: 4px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* No hover styles for service items */
    
    .date-badge {
        background-color: #e3f2fd;
        color: #0d6efd;
        font-weight: 600;
        border-radius: 4px;
        padding: 0.25rem 0.5rem;
        margin-bottom: 0.5rem;
        display: inline-block;
    }
    
    .time-badge {
        font-weight: 600;
        color: #495057;
    }
    
    .scrollbar-custom::-webkit-scrollbar {
        width: 6px;
    }
    
    .scrollbar-custom::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .scrollbar-custom::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    .scrollbar-custom::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    .provider-details-section {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    
    .provider-details-section h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.75rem;
        border-bottom: 2px solid #4e73df;
        padding-bottom: 0.5rem;
        display: inline-block;
    }
    
    .contact-info {
        background-color: #e9ecef;
        border-radius: 0.5rem;
        padding: 1rem;
    }
    
    .book-btn {
        padding: 0.5rem 2rem;
        font-weight: 600;
        border-radius: 2rem;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.2s ease;
    }
    
    .book-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }
    
    .availability-item {
        border-left: 3px solid #4e73df;
        margin-bottom: 0.5rem;
    }
    
    .no-info {
        font-style: italic;
        color: #6c757d;
    }
</style>
<div class="container mt-4 mb-5">
    <!-- Page header with back button -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?= base_url('index.php/patient/search') ?>" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Search
            </a>
            <h2 class="mb-0">Provider Profile</h2>
        </div>
    </div>
<div class="row">
    <!-- Main provider info card -->
    <div class="col-lg-8 mb-4">
        <div class="card provider-info-card">
            <!-- Provider header with name and title -->
            <div class="provider-header">
                <h3 class="mb-1"><?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?></h3>
                <p class="mb-0 opacity-75"><?= htmlspecialchars($provider['title'] ?? 'Healthcare Provider') ?></p>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <!-- Provider photo -->
                    <div class="col-md-4 mb-4 mb-md-0 text-center">
                        <?php if (!empty($provider['profile_image'])): ?>
                            <img src="<?= base_url('uploads/' . $provider['profile_image']) ?>" 
                                 class="img-fluid rounded provider-photo" alt="Provider Photo">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center provider-photo" style="width: 150px; height: 150px;">
                                <i class="fas fa-user-md fa-4x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Book button for small screens -->
                        <div class="d-block d-md-none mt-3">
                            <a href="<?= base_url('index.php/patient/book?provider_id=' . $provider['user_id']) ?>" 
                               class="btn btn-primary book-btn w-100">
                               <i class="far fa-calendar-check me-2"></i> Book Appointment
                            </a>
                        </div>
                    </div>
                    
                    <!-- Provider details -->
                    <div class="col-md-8">
                        <!-- Specialization section -->
                        <div class="provider-details-section">
                            <h5><i class="fas fa-stethoscope me-2"></i>Specialization</h5>
                            <p class="mb-0"><?= htmlspecialchars($provider['specialization'] ?? 'General Practice') ?></p>
                        </div>
                        
                        <!-- About section -->
                        <div class="provider-details-section">
                            <h5><i class="fas fa-user me-2"></i>About</h5>
                            <p class="mb-0"><?= htmlspecialchars($provider['bio'] ?? 'No information available.') ?></p>
                        </div>
                        
                        <!-- Contact information section -->
                        <div class="provider-details-section">
                            <h5><i class="fas fa-address-card me-2"></i>Contact Information</h5>
                            <div class="contact-info">
                                <p class="mb-2">
                                    <i class="fas fa-envelope me-2 text-primary"></i>
                                    <strong>Email:</strong> <?= htmlspecialchars($provider['email']) ?>
                                </p>
                                <?php if (!empty($provider['phone'])): ?>
                                    <p class="mb-0">
                                        <i class="fas fa-phone me-2 text-primary"></i>
                                        <strong>Phone:</strong> <?= htmlspecialchars($provider['phone']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Book button for medium and larger screens -->
                        <div class="d-none d-md-block mt-4">
                            <a href="<?= base_url('index.php/patient/book?provider_id=' . $provider['user_id']) ?>" 
                               class="btn btn-primary book-btn">
                               <i class="far fa-calendar-check me-2"></i> Book Appointment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar with services and availability -->
    <div class="col-lg-4">
        <!-- Services card -->
        <div class="card provider-info-card mb-4">
            <div class="card-header bg-light">
                <h4 class="mb-0"><i class="fas fa-list-alt me-2"></i>Services Offered</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($services)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($services as $service): ?>
                            <div class="list-group-item service-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($service['name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($service['duration'] ?? '30') ?> min</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    $<?= htmlspecialchars(isset($service['custom_price']) && $service['custom_price'] > 0 ? 
                                        $service['custom_price'] : $service['price']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No services listed for this provider.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Availability card with scrollable area -->
        <div class="card provider-info-card">
            <div class="card-header bg-light">
                <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Availability</h4>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($availability)): ?>
                    <div class="availability-container scrollbar-custom p-3">
                        <?php 
                        $currentDate = '';
                        foreach ($availability as $slot): 
                            $slotDate = $slot['availability_date'] ?? $slot['available_date'];
                            $formattedDate = date('l, F j', strtotime($slotDate));
                            // Display date only once as a header
                            if ($formattedDate !== $currentDate):
                                $currentDate = $formattedDate;
                        ?>
                            <div class="date-badge">
                                <i class="far fa-calendar me-1"></i>
                                <?= $formattedDate ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="availability-item p-2 mb-2 bg-light rounded">
                            <div class="time-badge">
                                <i class="far fa-clock me-1"></i>
                                <?= date('g:i A', strtotime($slot['start_time'])) ?> - 
                                <?= date('g:i A', strtotime($slot['end_time'])) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle me-2"></i>
                        No availability information for this provider.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
<?php include VIEW_PATH . '/partials/footer.php'; ?>