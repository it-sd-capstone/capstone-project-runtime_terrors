<h4>Update Profile</h4>

<form method="POST" action="<?= base_url('index.php/provider/processUpdateProfile') ?>">
    <label>First Name:</label>
    <input type="text" name="first_name" value="<?= htmlspecialchars($provider['first_name'] ?? '') ?>" required>

    <label>Last Name:</label>
    <input type="text" name="last_name" value="<?= htmlspecialchars($provider['last_name'] ?? '') ?>" required>

    <label>Specialty:</label>
    <input type="text" name="specialty" value="<?= htmlspecialchars($provider['specialty'] ?? '') ?>" required>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($provider['phone'] ?? '') ?>" required>

    <label>Bio:</label>
    <textarea name="bio"><?= htmlspecialchars($provider['bio'] ?? '') ?></textarea>

    <button type="submit" class="btn btn-success">Save Changes</button>
</form>

<h4>Change Password</h4>
<form method="POST" action="<?= base_url('index.php/provider/processPasswordChange') ?>">
    <label>Current Password:</label>
    <input type="password" name="current_password" required>

    <label>New Password:</label>
    <input type="password" name="new_password" required>

    <label>Confirm New Password:</label>
    <input type="password" name="confirm_password" required>

    <button type="submit" class="btn btn-warning">Change Password</button>
</form>

<!-- AJAX Script for Real-Time Updates -->
<script>
document.getElementById("profileForm").addEventListener("submit", function(event) {
    event.preventDefault();
    var formData = new FormData(this);

    fetch("<?= base_url('index.php/provider/processUpdateProfile') ?>", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById("statusMessage").innerHTML = "<p class='success'>Profile updated successfully!</p>";
        } else {
            document.getElementById("statusMessage").innerHTML = "<p class='error'>Update failed.</p>";
        }
    })
    .catch(error => console.error("Error:", error));
});
</script>
