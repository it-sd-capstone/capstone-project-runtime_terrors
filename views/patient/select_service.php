<?php include_once VIEW_PATH . '/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">What service are you looking for?</h3>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('index.php/patient/findProviders') ?>" method="GET">
                        <div class="mb-4">
                            <label for="service_id" class="form-label">Select a Service:</label>
                            <select id="service_id" name="service_id" class="form-select form-select-lg" required>
                                <option value="">-- Please select a service --</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['service_id'] ?>">
                                        <?= htmlspecialchars($service['name']) ?> 
                                        - <?= htmlspecialchars($service['description']) ?>
                                        ($<?= htmlspecialchars($service['price']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Find Providers <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once VIEW_PATH . '/partials/footer.php'; ?>