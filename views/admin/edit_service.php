
<?php include VIEW_PATH . '/partials/header.php'; ?>
<div class="container">
    <h2>Edit Service</h2>
    
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Service Details</h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('index.php/admin/services/edit/' . $service['service_id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($service['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($service['description']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price ($)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?= htmlspecialchars($service['price']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (minutes)</label>
                            <input type="number" min="1" class="form-control" id="duration" name="duration" value="<?= htmlspecialchars($service['duration'] ?? 30) ?>" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= (!isset($service['is_active']) || $service['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('index.php/admin/services') ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Service</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include VIEW_PATH . '/partials/footer.php'; ?>
