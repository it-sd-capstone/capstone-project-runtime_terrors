<h4>Edit Profile</h4>
<form method="POST" action="<?= base_url('index.php/provider/updateProfile') ?>">
    <label>First Name:</label>
    <input type="text" name="first_name" value="<?= htmlspecialchars($provider['first_name']) ?>" required>

    <label>Last Name:</label>
    <input type="text" name="last_name" value="<?= htmlspecialchars($provider['last_name']) ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($provider['email']) ?>" readonly>

    <label>Specialty:</label>
    <input type="text" name="specialty" value="<?= htmlspecialchars($provider['specialty']) ?>" required>

    <button type="submit" class="btn btn-success">Save Changes</button>
</form>