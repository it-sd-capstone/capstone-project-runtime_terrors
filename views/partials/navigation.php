<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
$userRole = $isLoggedIn ? $_SESSION['role'] : '';
$userName = $isLoggedIn ? ($_SESSION['name'] ?? ($_SESSION['email'] ?? 'User')) : '';
$current_url = $_SERVER['REQUEST_URI'];
$is_home_page = (strpos($current_url, 'index.php/home') !== false || $current_url === '/' || $current_url === '/index.php');
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <span class="navbar-brand">Appointment System</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (!$is_home_page): ?>
                <li class="nav-item">
                    <!-- <a class="nav-link" href="<?= base_url('index.php/home') ?>">Home</a> -->
                </li>
                <?php endif; ?>
                
                <?php if ($isLoggedIn): ?>
                    <?php if ($is_home_page && $userRole === 'admin'): ?>
                    <?php elseif ($userRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('index.php/admin') ?>">Admin Dashboard</a>
                        </li>
                    <?php else: ?>
                        <?php if (!$is_home_page): ?>
                            <li class="nav-item">
                                <?php if ($userRole === 'provider'): ?>
                                    <a class="nav-link" href="<?= base_url('index.php/provider/appointments') ?>">Appointments</a>
                                <?php elseif ($userRole === 'patient'): ?>
                                    <a class="nav-link" href="<?= base_url('index.php/patient/history') ?>">Appointments</a>
                                <?php else: ?>
                                    <a class="nav-link" href="<?= base_url('index.php/appointments') ?>">Appointments</a>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($userRole === 'provider'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/provider') ?>">Provider Portal</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($userRole === 'patient'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('index.php/patient') ?>">Patient Portal</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-<?php 
                                echo $userRole === 'admin' ? 'danger' : 
                                    ($userRole === 'provider' ? 'success' : 'primary'); 
                            ?>"><?= ucfirst($userRole) ?></span>
                            <?= htmlspecialchars($userName) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($userRole === 'patient'): ?>
                                <li><a class="dropdown-item" href="<?= base_url('index.php/patient/profile') ?>">My Profile</a></li>
                            <?php endif; ?>
                            <?php if ($userRole === 'provider'): ?>
                                <li><a class="dropdown-item" href="<?= base_url('index.php/provider/profile') ?>">My Profile</a></li>
                            <?php endif; ?>
                            <!-- <li><a class="dropdown-item" href="<?= base_url('index.php/home') ?>">Home</a></li> -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript for dropdown functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var dropdowns = document.querySelectorAll('.dropdown-toggle');
        if (dropdowns.length > 0) {
            dropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('click', function(event) {
                    event.preventDefault();
                    var dropdownMenu = this.nextElementSibling;
                    if (dropdownMenu.classList.contains('show')) {
                        dropdownMenu.classList.remove('show');
                    } else {
                        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                            menu.classList.remove('show');
                        });
                        dropdownMenu.classList.add('show');
                    }
                });
            });

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