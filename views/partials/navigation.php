<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
$userRole = $isLoggedIn ? $_SESSION['role'] : '';
$userName = $isLoggedIn ? $_SESSION['name'] : '';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/home">Appointment System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/home">Home</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/appointments">Appointments</a>
                </li>
                
                <?php if ($isLoggedIn): ?>
                    <?php if ($userRole === 'provider' || $userRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/provider">Provider Portal</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($userRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/admin">Admin Dashboard</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-<?php 
                                echo $userRole === 'admin' ? 'danger' : 
                                    ($userRole === 'provider' ? 'success' : 'primary'); 
                            ?>"><?= ucfirst($userRole) ?></span>
                            <?= htmlspecialchars($userName) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <?php if ($userRole === 'patient'): ?>
                                <li><a class="dropdown-item" href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/profile">My Profile</a></li>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <li>
                                    <a class="dropdown-item" href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth/logout">Logout</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/appointment-system/capstone-project-runtime_terrors/public_html/index.php/auth">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<!-- For Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
