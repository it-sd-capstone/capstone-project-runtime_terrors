<?php
// Check if session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Safely define all variables with defaults
$isLoggedIn = isset($isLoggedIn) ? $isLoggedIn : (isset($_SESSION['user_id']) && ($_SESSION['logged_in'] ?? false) === true);
$userRole = isset($userRole) ? $userRole : ($isLoggedIn ? ($_SESSION['role'] ?? 'guest') : 'guest');
$userName = isset($userName) ? $userName : ($isLoggedIn ? ($_SESSION['name'] ?? ($_SESSION['email'] ?? 'User')) : '');
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light" id="mainNavbar">
  <div class="container-fluid">
    <!-- Home-specific brand/logo as non-clickable text -->
    <span class="navbar-brand">
      <strong>Appointment System</strong>
    </span>
    
    <button class="navbar-toggler ms-auto" type="button" id="navbarToggleGuest">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <?php if (!$isLoggedIn): ?>
    <!-- Guest Navigation -->
    <div class="collapse navbar-collapse" id="homeGuestNavbar">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/auth/register') ?>">
            <i class="fas fa-user-plus me-2"></i>Register
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/auth') ?>">
            <i class="fas fa-sign-in-alt me-2"></i>Login
          </a>
        </li>
      </ul>
    </div>
    <?php else: ?>
    <!-- Logged-in Navigation -->
    <div class="collapse navbar-collapse" id="homeLoggedInNavbar">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/' . $userRole . '/dashboard') ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
          </a>
        </li>
        
        <!-- User dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userHomeDropdown" role="button">
            <span class="badge bg-<?= $userRole === 'admin' ? 'danger' : ($userRole === 'provider' ? 'success' : 'primary') ?>">
              <?= ucfirst($userRole) ?>
            </span>
            <span class="text-truncate" style="max-width: 150px; display: inline-block; vertical-align: middle;">
              <?= htmlspecialchars($userName) ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" id="userDropdownMenu">
            <li><h6 class="dropdown-header">Account</h6></li>
            
            <?php if ($userRole === 'patient'): ?>
            <li>
              <a class="dropdown-item" href="<?= base_url('index.php/patient/viewProfile') ?>">
                <i class="fas fa-user me-2"></i> My Profile
              </a>
            </li>
            <?php elseif ($userRole === 'provider'): ?>
            <li>
              <a class="dropdown-item" href="<?= base_url('index.php/provider/viewProfile') ?>">
                <i class="fas fa-user me-2"></i> My Profile
              </a>
            </li>
            <?php endif; ?>
            
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item" href="<?= base_url('index.php/auth/logout') ?>">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</nav>

<style>
/* Navbar styles - matching the navigation.php styles exactly */
.navbar {
  padding: 0.5rem 1rem;
}

/* Additional styles for dropdown */
.dropdown-menu {
  padding: 0.5rem;
  border-radius: 4px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  border: 1px solid rgba(0,0,0,0.08);
}

.dropdown-item {
  border-radius: 4px;
  padding: 0.5rem 1rem;
  margin-bottom: 2px;
}

.dropdown-item:hover, .dropdown-item:focus {
  background-color: rgba(0,0,0,0.05);
}

.dropdown-header {
  font-weight: bold;
  color: #555;
  padding: 0.5rem 1rem;
}

/* Navbar styles for dashboard */
@media (max-width: 991.98px) {
  .navbar-collapse {
    position: absolute;
    top: 100%;
    right: 0;
    left: 0;
    z-index: 1000;
    background-color: white;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-top: 0.5rem;
    max-height: 0;
    overflow: hidden;
    display: none !important; /* Hide by default in mobile */
  }
  
  /* Only show when the 'show' class is applied */
  .navbar-collapse.show {
    max-height: 80vh;
    overflow-y: auto;
    display: block !important;
  }
  
  /* Hide mobile menu items initially */
  .navbar-nav {
    display: none;
  }
  
  /* Show them when the menu is expanded */
  .navbar-collapse.show .navbar-nav {
    display: block;
  }
  
  .navbar-nav .nav-link {
    padding: 0.625rem 0.75rem;
    border-radius: 0.25rem;
    margin-bottom: 0.25rem;
  }
  
  .navbar-nav .nav-link:hover {
    background-color: rgba(0,0,0,0.05);
  }
  
  /* Add extra space for items */
  .navbar-nav .nav-item {
    margin-bottom: 0.25rem;
  }
}

/* Force the navigation menu to be displayed when it has the 'show' class */
.navbar-collapse.show {
  height: auto !important;
  visibility: visible !important;
  overflow: visible !important;
}

.dropdown-menu.show {
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
}

/* Add these new rules to ensure hero section text remains white */
.hero-section {
  color: white !important;
}
.hero-section h1, 
.hero-section p, 
.hero-section .display-4, 
.hero-section .lead {
  color: white !important;
}
  @media (max-width: 991.98px) {
  /* Position the collapsed navbar directly behind the toggle button */
  .navbar-collapse {
    position: absolute;
    top: 0;
    right: 0;
    margin-top: -100%; /* Start offscreen/hidden */
    transition: margin-top 0.3s ease;
    width: 100%;
    z-index: -1; /* Place behind the button */
  }
  
  /* When the menu is expanded/shown */
  .navbar-collapse.show {
    margin-top: 50px; /* Adjust this value based on your button position */
    z-index: 1000; /* Bring it to the front when shown */
  }
  
  /* Container position to allow absolute positioning */
  .navbar .container-fluid {
    position: relative;
  }
}

</style>

<!-- Custom navigation script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Prevent the script from running twice
  if (window.navInitialized) return;
  window.navInitialized = true;
  
  // Force any existing navigation to close first
  const openMenus = document.querySelectorAll('.navbar-collapse.show');
  openMenus.forEach(menu => menu.classList.remove('show'));
  
  // Custom toggle for guest/user navigation
  const navbarToggle = document.getElementById('navbarToggleGuest');
  const guestMenu = document.getElementById('homeGuestNavbar');
  const userMenu = document.getElementById('homeLoggedInNavbar');
  
  if (navbarToggle) {
    navbarToggle.addEventListener('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      
      // Toggle the appropriate menu
      if (guestMenu) guestMenu.classList.toggle('show');
      if (userMenu) userMenu.classList.toggle('show');
      return false;
    });
  }
  
  // User dropdown toggle
  const userDropdownToggle = document.getElementById('userHomeDropdown');
  const userDropdownMenu = document.getElementById('userDropdownMenu');
  
  if (userDropdownToggle && userDropdownMenu) {
    userDropdownToggle.addEventListener('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      
      userDropdownMenu.classList.toggle('show');
      return false;
    });
  }
  
  // Close when clicking outside
  document.addEventListener('click', function(event) {
    // Close any open dropdowns when clicking outside
    if (!userDropdownToggle?.contains(event.target) && !userDropdownMenu?.contains(event.target)) {
      userDropdownMenu?.classList.remove('show');
    }
    
    // Don't close the navbar when clicking inside it
    if (document.querySelector('#mainNavbar').contains(event.target)) {
      return;
    }
    
    // Close any open navbars when clicking outside
    guestMenu?.classList.remove('show');
    userMenu?.classList.remove('show');
  });
});
</script>