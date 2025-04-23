<?php include '../layout/partials/header.php'; ?>
<?php include '../layout/partials/navigation.php'; ?>

<div class="container mt-4">
    <h2>Patient Profile</h2>

    <!-- Profile Update Form -->
    <form id="profileForm" action="/patient/updateProfile" method="POST">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($patient['first_name']) ?>" required class="form-control">

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($patient['last_name']) ?>" required class="form-control">

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($patient['email']) ?>" readonly class="form-control">

        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($patient['phone']) ?>" class="form-control">

        <label for="age">Age:</label>
        <input type="number" name="age" value="<?= htmlspecialchars($patient['age']) ?>" class="form-control">

        <button type="submit" class="btn btn-primary mt-3">Save Profile</button>
    </form>

    <p class="mt-3"><a href="/auth/changePassword">Change Password</a></p>
</div>

<script>
document.getElementById("profileForm").addEventListener("submit", function(event) {
    let firstName = document.querySelector("input[name='first_name']").value.trim();
    let lastName = document.querySelector("input[name='last_name']").value.trim();
    let phone = document.querySelector("input[name='phone']").value.trim();
    let age = document.querySelector("input[name='age']").value.trim();

    if (!firstName || !lastName || !phone || !age) {
        alert("All fields are required!");
        event.preventDefault();
    }
});
</script>

<?php include '../layout/partials/footer.php'; ?>