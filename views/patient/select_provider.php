<?php include_once VIEW_PATH . '/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('index.php/patient/selectService') ?>">Select Service</a></li>
                    <li class="breadcrumb-item active">Select Provider</li>
                </ol>
            </nav>
            
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Select a Provider for <?= htmlspecialchars($service['name']) ?></h3>
                </div>
                <div class="card-body">
                    <?php if (empty($providers)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No providers are currently available for this service. Please select a different service or try again later.
                        </div>
                        <a href="<?= base_url('index.php/patient/selectService') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Service Selection
                        </a>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            <?php foreach ($providers as $provider): ?>
                                <div class="col">
                                    <div class="card h-100 provider-card">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                Dr. <?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?>
                                            </h5>
                                            <?php if (!empty($provider['specialization'])): ?>
                                                <h6 class="card-subtitle mb-2 text-muted">
                                                    <?= htmlspecialchars($provider['specialization']) ?>
                                                </h6>
                                            <?php endif; ?>
                                            <?php if (!empty($provider['bio'])): ?>
                                                <p class="card-text">
                                                    <?= htmlspecialchars(substr($provider['bio'], 0, 150)) ?>...
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-white border-top-0">
                                            <a href="<?= base_url('index.php/patient/book?provider_id=' . $provider['user_id'] . '&service_id=' . $service['service_id']) ?>" 
                                               class="btn btn-primary w-100">
                                               Select & Book
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once VIEW_PATH . '/partials/footer.php'; ?>