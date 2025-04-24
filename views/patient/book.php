<h4>Book an Appointment</h4>
<form method="POST" action="<?= base_url('index.php/patient/confirmBooking') ?>">
    <label>Select Provider:</label>
    <select name="provider_id" required>
        <?php foreach ($providers as $provider) : ?>
            <option value="<?= $provider['provider_id'] ?>">
                <?= htmlspecialchars($provider['provider_name']) ?> - <?= htmlspecialchars($provider['specialization']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Select Service:</label>
    <select name="service_id" required>
        <?php foreach ($services as $service) : ?>
            <option value="<?= $service['service_id'] ?>">
                <?= htmlspecialchars($service['name']) ?> ($<?= htmlspecialchars($service['price']) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label>Select Date:</label>
    <input type="date" name="appointment_date" required>

    <label>Select Time:</label>
    <input type="time" name="start_time" required>

    <button type="submit" class="btn btn-success">Confirm Booking</button>
</form>
    <script>
    document.getElementById("bookForm").addEventListener("submit", function(event) {
        event.preventDefault();
        var selectedDate = document.getElementById("appointment_date").value;
        var selectedTime = document.getElementById("appointment_time").value;

        if (!selectedDate || !selectedTime) {
            alert("Please select both date and time.");
            return;
        }

        fetch("<?= base_url('index.php/patient/checkAvailability') ?>", {
            method: "POST",
            body: JSON.stringify({ date: selectedDate, time: selectedTime }),
            headers: { "Content-Type": "application/json" }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.available) {
                alert("Selected time is unavailable. Please pick a different slot.");
            } else {
                document.getElementById("bookForm").submit();
            }
        });
    });
</script>