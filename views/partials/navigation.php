<?php
// Check if session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Safely define all variables with defaults
$isLoggedIn = isset($isLoggedIn) ? $isLoggedIn : (isset($_SESSION['user_id']) && ($_SESSION['logged_in'] ?? false) === true);
$userRole = isset($userRole) ? $userRole : ($isLoggedIn ? ($_SESSION['role'] ?? 'guest') : 'guest');
$userName = isset($userName) ? $userName : ($isLoggedIn ? ($_SESSION['name'] ?? ($_SESSION['email'] ?? 'User')) : '');
$current_url = isset($current_url) ? $current_url : ($_SERVER['REQUEST_URI'] ?? '');
$is_home_page = isset($is_home_page) ? $is_home_page : (strpos($current_url, 'index.php/home') !== false || $current_url === '/' || $current_url === '/index.php');
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <!-- Removed brand completely - keeping this comment to maintain structure -->
    
    <?php if (!$isLoggedIn): ?>
    <!-- Hamburger menu for GUEST USERS -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbar" aria-controls="guestNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <!-- Guest Navigation - collapsible on mobile includes auth links -->
    <div class="collapse navbar-collapse" id="guestNavbar">
      <ul class="navbar-nav me-auto">
        <?php if (!$is_home_page): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/home') ?>">
            <i class="fas fa-home me-2 d-lg-none"></i>Home
          </a>
        </li>
        <?php endif; ?>
      </ul>
      <!-- Auth links for desktop and mobile -->
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/auth/register') ?>">
            <i class="fas fa-user-plus me-2 d-lg-none"></i>Register
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/auth') ?>">
            <i class="fas fa-sign-in-alt me-2 d-lg-none"></i>Login
          </a>
        </li>
      </ul>
    </div>
    <?php else: ?>
    <!-- For logged-in users -->
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#loggedInNavbar" aria-controls="loggedInNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <!-- Desktop Navigation for logged-in users -->
    <div class="collapse navbar-collapse" id="loggedInNavbar">
      <ul class="navbar-nav me-auto">
        <?php if (!$is_home_page): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/home') ?>">
            <i class="fas fa-home me-2 d-lg-none"></i>Home
          </a>
        </li>
        <?php endif; ?>
        
        <?php if ($userRole === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/providers') ?>">
              <i class="fas fa-user-md me-2 d-lg-none"></i>Providers
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/services') ?>">
              <i class="fas fa-clipboard-list me-2 d-lg-none"></i>Services
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/appointments') ?>">
              <i class="fas fa-calendar-check me-2 d-lg-none"></i>Appointments
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/users') ?>">
              <i class="fas fa-users me-2 d-lg-none"></i>Users
            </a>
          </li>
        <?php elseif ($userRole === 'provider'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/provider/services') ?>">
              <i class="fas fa-clipboard-list me-2 d-lg-none"></i>Services
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/provider/schedule') ?>">
              <i class="fas fa-calendar-alt me-2 d-lg-none"></i>Schedule
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/provider/appointments') ?>">
              <i class="fas fa-calendar-check me-2 d-lg-none"></i>Appointments
            </a>
          </li>
        <?php elseif ($userRole === 'patient'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/patient/book') ?>">
              <i class="fas fa-book-medical me-2 d-lg-none"></i>Book
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/appointments') ?>">
              <i class="fas fa-calendar-check me-2 d-lg-none"></i>My Appointments
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/patient/search') ?>">
              <i class="fas fa-search me-2 d-lg-none"></i>Find Provider
            </a>
          </li>
        <?php endif; ?>
      </ul>
      
      <!-- Right side for logged-in users -->
      <ul class="navbar-nav ms-auto">
        <?php if (($userRole === 'patient' || $userRole === 'provider') && !isset($hideNotifications)): ?>
        <li class="nav-item notifications-icon">
          <a class="nav-link position-relative" href="<?= base_url('index.php/' . $userRole . '/notifications') ?>">
            <i class="fas fa-bell"></i>
            <span class="d-lg-none ms-2">Notifications</span>
            <?php
            // Get unread notification count
            $unreadCount = 0;
            if (isset($_SESSION['user_id'])) {
                try {
                    // Use the Notification model to get the count
                    require_once APP_ROOT . '/models/Notification.php';
                    $notificationModel = new Notification(get_db());
                    $unreadCount = $notificationModel->getUnreadCountByUserId($_SESSION['user_id']);
                } catch (Exception $e) {
                    error_log("Error getting notification count: " . $e->getMessage());
                }
            }
            ?>
            <?php if ($unreadCount > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
              </span>
            <?php endif; ?>
          </a>
        </li>
        <?php endif; ?>
        
        <!-- User dropdown for logged-in users -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="badge bg-<?= $userRole === 'admin' ? 'danger' : ($userRole === 'provider' ? 'success' : 'primary') ?>">
              <?= ucfirst($userRole) ?>
            </span>
            <span class="text-truncate" style="max-width: 150px; display: inline-block; vertical-align: middle;"><?= htmlspecialchars($userName) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <!-- Mobile-only navigation items -->
            <li class="mobile-only"><h6 class="dropdown-header">Navigation</h6></li>
            
            <?php if (!$is_home_page): ?>
            <li class="mobile-only">
              <a class="dropdown-item" href="<?= base_url('index.php/home') ?>">
                <i class="fas fa-home"></i> Home
              </a>
            </li>
            <?php endif; ?>
            
            <?php if ($userRole === 'admin'): ?>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/admin/providers') ?>">
                  <i class="fas fa-user-md"></i> Providers
                </a>
              </li>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/admin/services') ?>">
                  <i class="fas fa-clipboard-list"></i> Services
                </a>
              </li>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/admin/appointments') ?>">
                  <i class="fas fa-calendar-check"></i> Appointments
                </a>
              </li>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/admin/users') ?>">
                  <i class="fas fa-users"></i> Users
                </a>
              </li>
            <?php elseif ($userRole === 'provider'): ?>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/provider/services') ?>">
                  <i class="fas fa-clipboard-list"></i> Services
                </a>
              </li>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/provider/schedule') ?>">
                  <i class="fas fa-calendar-alt"></i> Schedule
                </a>
              </li>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/provider/appointments') ?>">
                  <i class="fas fa-calendar-check"></i> Appointments
                </a>
              </li>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/provider/notifications') ?>">
                  <i class="fas fa-bell"></i> Notifications
                </a>
              </li>
            <?php elseif ($userRole === 'patient'): ?>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/patient/book') ?>">
                  <i class="fas fa-book-medical"></i> Book Appointment
                </a>
              </li>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/appointments') ?>">
                  <i class="fas fa-calendar-check"></i> My Appointments
                </a>
              </li>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/patient/search') ?>">
                  <i class="fas fa-search"></i> Find Provider
                </a>
              </li>
              <li class="mobile-only">
                <a class="dropdown-item" href="<?= base_url('index.php/patient/notifications') ?>">
                  <i class="fas fa-bell"></i> Notifications
                </a>
              </li>
            <?php endif; ?>
            
            <!-- Divider between mobile nav and profile items -->
            <li class="mobile-only"><hr class="dropdown-divider"></li>
            
            <!-- Profile items for all screen sizes -->
            <li><h6 class="dropdown-header">Account</h6></li>
            
            <?php if ($userRole === 'patient'): ?>
              <li>
                <a class="dropdown-item" href="<?= base_url('index.php/patient/viewProfile') ?>">
                  <i class="fas fa-user"></i> My Profile
                </a>
              </li>
            <?php elseif ($userRole === 'provider'): ?>
              <li>
                <a class="dropdown-item" href="<?= base_url('index.php/provider/viewProfile') ?>">
                  <i class="fas fa-user"></i> My Profile
                </a>
              </li>
            <?php endif; ?>
            
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item" href="<?= base_url('index.php/auth/logout') ?>">
                <i class="fas fa-sign-out-alt"></i> Logout
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

/* Ensure proper navbar functionality on mobile */
@media (max-width: 991.98px) {
  .navbar-collapse {
    background-color: white;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-top: 0.5rem;
    max-height: 80vh;
    overflow-y: auto;
  }
  
  .mobile-only {
    display: block !important;
  }
  
  .navbar-nav .nav-link {
    padding: 0.625rem 0.75rem;
    border-radius: 0.25rem;
    margin-bottom: 0.25rem;
  }
  
  .navbar-nav .nav-link:hover {
    background-color: rgba(0,0,0,0.05);
  }
  
  /* Position fixes for notification badge */
  .position-relative .badge {
    transform: translate(25%, -50%) !important;
  }
  
  /* Add extra space for items */
  .navbar-nav .nav-item {
    margin-bottom: 0.25rem;
  }
  
  /* Make links easier to tap */
  .navbar-nav a {
    display: block;
    width: 100%;
  }
}

/* Mobile-only nav items */
@media (min-width: 992px) {
  .mobile-only {
    display: none !important;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Use Bootstrap's built-in collapse functionality for navbar toggler buttons
  var navbarTogglers = document.querySelectorAll('.navbar-toggler');
  
  navbarTogglers.forEach(function(toggler) {
    toggler.addEventListener('click', function() {
      var targetId = this.getAttribute('data-bs-target');
      var targetElement = document.querySelector(targetId);
      
      if (targetElement) {
        targetElement.classList.toggle('show');
      }
    });
  });
  
  // Handle clicks on nav links to close menus on mobile
  var navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
  
  navLinks.forEach(function(link) {
    link.addEventListener('click', function() {
      var navbarCollapse = this.closest('.navbar-collapse');
      if (navbarCollapse && navbarCollapse.classList.contains('show')) {
        navbarCollapse.classList.remove('show');
      }
    });
  });
  
  // Handle clicks outside the navbar to close it
  document.addEventListener('click', function(event) {
    var openMenus = document.querySelectorAll('.navbar-collapse.show');
    
    openMenus.forEach(function(menu) {
      // Check if the click was outside the menu and its toggler
      var toggler = document.querySelector('[data-bs-target="#' + menu.id + '"]');
      
      if (!menu.contains(event.target) && (!toggler || !toggler.contains(event.target))) {
        menu.classList.remove('show');
      }
    });
  });
  
  // Handle dropdown toggle for user menu
  var userDropdown = document.getElementById('userDropdown');
  if (userDropdown) {
    userDropdown.addEventListener('click', function(e) {
      e.preventDefault();
      var dropdownMenu = this.nextElementSibling;
      if (dropdownMenu) {
        dropdownMenu.classList.toggle('show');
      }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!userDropdown.contains(e.target)) {
        var dropdownMenu = userDropdown.nextElementSibling;
        if (dropdownMenu && dropdownMenu.classList.contains('show')) {
          dropdownMenu.classList.remove('show');
        }
      }
    });
  }
});
</script>