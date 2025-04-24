<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
$userRole = $isLoggedIn ? $_SESSION['role'] : '';
$userName = $isLoggedIn ? $_SESSION['name'] : '';

// Determine current page by looking at the URL
$current_url = $_SERVER['REQUEST_URI'];
$is_home_page = (strpos($current_url, 'index.php/home') !== false || $current_url === '/' || $current_url === '/index.php');
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <!-- Changed from link to span (no link) -->
        <span class="navbar-brand">Appointment System</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (!$is_home_page): ?>
                <!-- Only show Home link if not on the home page -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('index.php/home') ?>">Home</a>
                </li>
                <?php endif; ?>
                
                <?php if ($isLoggedIn): ?>
                <!-- Only show Appointments link if logged in -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('index.php/appointments') ?>">Appointments</a>
                </li>
                
                    <?php if ($userRole === 'provider' || $userRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('index.php/provider') ?>">Provider Portal</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($userRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('index.php/admin') ?>">Admin Dashboard</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-<?php 
                                echo $userRole === 'admin' ? 'danger' : 
                                    ($userRole === 'provider' ? 'success' : 'primary'); 
                            ?>"><?= ucfirst($userRole) ?></span>
                            <?= htmlspecialchars($userName) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <?php if ($userRole === 'patient'): ?>
                                <li><a class="dropdown-item" href="<?= base_url('index.php/profile') ?>">My Profile</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= base_url('index.php/auth/logout') ?>">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('index.php/auth') ?>">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Make sure Bootstrap JS is loaded properly -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

<!-- Custom JavaScript for dropdown functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all dropdowns
        var dropdowns = document.querySelectorAll('.dropdown-toggle');
        if (dropdowns.length > 0) {
            dropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('click', function(event) {
                    event.preventDefault();
                    var dropdownMenu = this.nextElementSibling;
                    if (dropdownMenu.classList.contains('show')) {
                        dropdownMenu.classList.remove('show');
                    } else {
                        // Close any open dropdowns first
                        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                            menu.classList.remove('show');
                        });
                        dropdownMenu.classList.add('show');
                    }
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                        menu.classList.remove('show');
                    });
                }
            });
        }
    });
</script>

<!-- Add CSS to ensure dropdown is visible -->
<style>
    .dropdown-menu.show {
        display: block !important;
        position: absolute !important;
        top: 100% !important;
        right: 0 !important;
        left: auto !important;
        margin-top: 0.125rem !important;
        z-index: 1000 !important;
    }
</style>