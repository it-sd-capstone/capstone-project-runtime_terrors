<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container my-4 provider-dashboard">
    <h2>Welcome, <?= htmlspecialchars($provider['first_name']) ?>!</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Profile</h5>
                    <p class="card-text">Update your specialty, bio, and availability.</p>
                    <div class="d-grid">
                        <a href="<?= base_url('index.php/provider/profile') ?>" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Services</h5>
                    <p class="card-text">Manage services offered and pricing.</p>
                    <div class="d-grid">
                        <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-info">Manage Services</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Availability</h5>
                    <p class="card-text">Set your weekly schedule and recurring availability.</p>
                    <div class="d-grid">
                        <a href="<?= base_url('index.php/provider/schedule') ?>" class="btn btn-success">Manage Availability</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar Integration -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Upcoming Appointments</h5>
                </div>
                <div class="card-body">
                    <div id="providerCalendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    $("#providerCalendar").fullCalendar({
        events: "/provider/api/getAppointments.php",
        defaultView: "month",
        editable: false
    });
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>