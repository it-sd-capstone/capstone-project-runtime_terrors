<?php include '../layout/partials/header.php'; ?>
<?php include '../layout/partials/navigation.php'; ?>

<div class="container mt-4">
    <h2>Book an Appointment</h2>
    
    <form id="bookingForm" action="/patient/bookAppointment" method="POST">
        <label for="provider_id">Select Provider:</label>
        <select name="provider_id" required>
            <?php foreach ($availableProviders as $provider): ?>
                <option value="<?= $provider['provider_id'] ?>">
                    <?= $provider['specialization'] ?> (<?= $provider['availability_date'] ?> - <?= $provider['start_time'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="appointment_date">Appointment Date:</label>
        <input type="date" name="appointment_date" required>

        <label for="appointment_time">Time:</label>
        <input type="time" name="appointment_time" required>

        <button type="submit" class="btn btn-primary mt-3">Book Appointment</button>
    </form>

    <p class="mt-3"><a href="/appointments/history">View My Appointments</a></p>

</div>

<script>
document.getElementById("bookingForm").addEventListener("submit", function(event) {
    let date = document.querySelector("input[name='appointment_date']").value;
    let time = document.querySelector("input[name='appointment_time']").value;

    if (!date || !time) {
        alert("Please select a valid date and time!");
        event.preventDefault();
    }
});
</script>

<?php include '../layout/partials/footer.php'; ?>