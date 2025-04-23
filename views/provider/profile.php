<?php include '../layout/partials/header.php'; ?>
<?php include '../layout/partials/navigation.php'; ?>

<div class="container mt-4">
    <h2>Provider Profile</h2>

    <!-- Profile Update Form -->
    <form id="profileForm" action="/provider/updateProfile" method="POST" enctype="multipart/form-data">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($provider['first_name']) ?>" required class="form-control">

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($provider['last_name']) ?>" required class="form-control">

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($provider['email']) ?>" readonly class="form-control">

        <label for="specialty">Specialty:</label>
        <input type="text" name="specialty" value="<?= htmlspecialchars($provider['specialty']) ?>" class="form-control">

        <label for="bio">Bio:</label>
        <textarea name="bio" class="form-control"><?= htmlspecialchars($provider['bio']) ?></textarea>

        <label for="profile_picture">Profile Picture:</label>
        <input type="file" name="profile_picture" accept="image/*" class="form-control">

        <button type="submit" class="btn btn-primary mt-3">Save Profile</button>
    </form>

    <p class="mt-3"><a href="/auth/changePassword">Change Password</a></p>
</div>

<script>
// Ensure validation before submitting
document.getElementById("profileForm").addEventListener("submit", function(event) {
    let firstName = document.querySelector("input[name='first_name']").value.trim();
    let lastName = document.querySelector("input[name='last_name']").value.trim();
    let specialty = document.querySelector("input[name='specialty']").value.trim();
    let bio = document.querySelector("textarea[name='bio']").value.trim();

    if (!firstName || !lastName || !specialty || !bio) {
        alert("All fields are required!");
        event.preventDefault();
    }
});
</script>

<?php include '../layout/partials/footer.php'; ?>