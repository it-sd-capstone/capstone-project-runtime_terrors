<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container my-4">
    <h2>Manage Availability</h2>

    <form action="<?= base_url('index.php/provider/updateAvailability') ?>" method="post">
        <div class="mb-3">
            <label for="availability_date" class="form-label">Date</label>
            <input type="date" class="form-control" id="availability_date" name="availability_date" required>
        </div>
        <div class="mb-3">
            <label for="start_time" class="form-label">Start Time</label>
            <input type="time" class="form-control" id="start_time" name="start_time" required>
        </div>
        <div class="mb-3">
            <label for="end_time" class="form-label">End Time</label>
            <input type="time" class="form-control" id="end_time" name="end_time" required>
        </div>
        <button type="submit" class="btn btn-success">Update Availability</button>
    </form>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>