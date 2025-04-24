<h4>Edit Profile</h4>
<form method="POST" action="<?= base_url('index.php/patient/updateProfile') ?>">
    <label>First Name:</label>
    <input type="text" name="first_name" value="<?= htmlspecialchars($patient['first_name']) ?>" required>

    <label>Last Name:</label>
    <input type="text" name="last_name" value="<?= htmlspecialchars($patient['last_name']) ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($patient['email']) ?>" readonly>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($patient['phone']) ?>" required>

    <label>Medical History:</label>
    <textarea name="medical_history"><?= htmlspecialchars($patient['medical_history']) ?></textarea>

    <button type="submit" class="btn btn-success">Save Changes</button>
</form>