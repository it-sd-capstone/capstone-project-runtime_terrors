<h4>Update Profile</h4>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2" style="font-size:1.5em;"></i>
        <div><?= $_SESSION['success'] ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= base_url('index.php/provider/processUpdateProfile') ?>">
    <?= csrf_field() ?>
    <label>First Name:</label>
    <input type="text" name="first_name" value="<?= htmlspecialchars($provider['first_name']) ?>" required>

    <label>Last Name:</label>
    <input type="text" name="last_name" value="<?= htmlspecialchars($provider['last_name']) ?>" required>

    <label>Specialty:</label>
    <input type="text" name="specialty" value="<?= htmlspecialchars($provider['specialty']) ?>" required>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($provider['phone']) ?>" required>

    <button type="submit" class="btn btn-success">Save Changes</button>
</form>