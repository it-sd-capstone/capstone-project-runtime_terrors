<?php include VIEW_PATH . '/partials/provider_header.php'; ?>

<div class="container provider-dashboard">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h4>Welcome to the Provider Dashboard</h4>
                <p>Manage appointments, availability, and services.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Manage Schedule -->
        <div class="col-md-4">
            <div class="card provider-card">
                <div class="card-body">
                    <h5 class="card-title">Schedule</h5>
                    <p class="card-text">Set and manage your availability.</p>
                    <div class="d-grid">
                        <a href="<?= base_url('index.php/provider/schedule') ?>" class="btn btn-primary">Manage Schedule</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Appointments -->
        <div class="col-md-4">
            <div class="card provider-card">
                <div class="card-body">
                    <h5 class="card-title">Appointments</h5>
                    <p class="card-text">View upcoming patient bookings.</p>
                    <div class="d-grid">
                        <a href="<?= base_url('index.php/provider/appointments') ?>" class="btn btn-success">View Appointments</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Services -->
        <div class="col-md-4">
            <div class="card provider-card">
                <div class="card-body">
                    <h5 class="card-title">Services</h5>
                    <p class="card-text">Update your offered services.</p>
                    <div class="d-grid">
                        <a href="<?= base_url('index.php/provider/services') ?>" class="btn btn-info">Manage Services</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>