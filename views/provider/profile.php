<h4>Update Profile</h4>
<form method="POST" action="<?= base_url('index.php/provider/processUpdateProfile') ?>">
    <label>First Name:</label>
    <input type="text" name="first_name" value="<?= htmlspecialchars($provider['first_name'] ?? '') ?>" required>

    <label>Last Name:</label>
    <input type="text" name="last_name" value="<?= htmlspecialchars($provider['last_name']) ?>" required>

    <label>Specialty:</label>
    <input type="text" name="specialty" value="<?= htmlspecialchars($provider['specialty']) ?>" required>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($provider['phone']) ?>" required>

    <button type="submit" class="btn btn-success">Save Changes</button>
</form>