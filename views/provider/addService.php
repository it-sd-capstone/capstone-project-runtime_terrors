<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container my-4">
    <h2>Add New Service</h2>

    <form action="<?= base_url('index.php/provider/addService') ?>" method="post">
        <div class="mb-3">
            <label for="name" class="form-label">Service Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price ($)</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Service</button>
    </form>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>