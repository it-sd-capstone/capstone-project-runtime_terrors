<?php include '../layout/partials/header.php'; ?>
<?php include '../layout/partials/navigation.php'; ?>

<div class="container mt-4">
    <h2>Provider Profile</h2>
    
    <!-- Profile Update Form -->
    <form id="profileForm" action="/provider/updateProfile" method="POST">
        <label for="name">Full Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($provider['name']) ?>" required class="form-control">

        <label for="specialty">Specialty:</label>
        <input type="text" name="specialty" value="<?= htmlspecialchars($provider['specialty']) ?>" class="form-control">

        <label for="bio">Bio:</label>
        <textarea name="bio" class="form-control"><?= htmlspecialchars($provider['bio']) ?></textarea>

        <button type="submit" class="btn btn-primary mt-3">Save Profile</button>
    </form>

    <!-- Redirect to Password Change -->
    <p class="mt-3"><a href="/auth/changePassword">Change Password</a></p>

</div>

<script>
document.getElementById("profileForm").addEventListener("submit", function(event) {
    let name = document.querySelector("input[name='name']").value.trim();
    let specialty = document.querySelector("input[name='specialty']").value.trim();
    let bio = document.querySelector("textarea[name='bio']").value.trim();

    if (!name || !specialty || !bio) {
        alert("All fields are required!");
        event.preventDefault();
    }
});
</script>

<?php include '../layout/partials/footer.php'; ?>