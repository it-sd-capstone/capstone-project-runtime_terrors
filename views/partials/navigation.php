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
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light" id="dashMainNavbar">
  <div class="container-fluid">
    <!-- Dashboard branding - optional -->
    <a class="navbar-brand d-lg-none" href="<?= base_url('index.php/' . $userRole) ?>">
      <span class="badge bg-<?= $userRole === 'admin' ? 'danger' : ($userRole === 'provider' ? 'success' : 'primary') ?>">
        <?= ucfirst($userRole) ?> Dashboard
      </span>
    </a>
    
    <button class="navbar-toggler ms-auto" type="button" id="dashboardNavbarToggle">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="dashboardNavbar">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/home') ?>">
            <i class="fas fa-home me-2"></i>Home
          </a>
        </li>
        
        <?php if ($userRole === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/providers') ?>">
              <i class="fas fa-user-md me-2"></i>Providers
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/services') ?>">
              <i class="fas fa-clipboard-list me-2"></i>Services
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/appointments') ?>">
              <i class="fas fa-calendar-check me-2"></i>Appointments
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/users') ?>">
              <i class="fas fa-users me-2"></i>Users
            </a>
          </li>
        <?php elseif ($userRole === 'provider'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/provider/services') ?>">
              <i class="fas fa-clipboard-list me-2"></i>Services
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/provider/schedule') ?>">
              <i class="fas fa-calendar-alt me-2"></i>Schedule
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/provider/appointments') ?>">
              <i class="fas fa-calendar-check me-2"></i>Appointments
            </a>
          </li>
        <?php elseif ($userRole === 'patient'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/patient/book') ?>">
              <i class="fas fa-book-medical me-2"></i>Book
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/appointments') ?>">
              <i class="fas fa-calendar-check me-2"></i>My Appointments
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/patient/search') ?>">
              <i class="fas fa-search me-2"></i>Find Provider
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
          <a class="nav-link dropdown-toggle" href="#" id="userDashboardDropdown" role="button">
            <span class="badge bg-<?= $userRole === 'admin' ? 'danger' : ($userRole === 'provider' ? 'success' : 'primary') ?>">
              <?= ucfirst($userRole) ?>
            </span>
            <span class="text-truncate" style="max-width: 150px; display: inline-block; vertical-align: middle;">
              <?= htmlspecialchars($userName) ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" id="dashUserDropdownMenu">
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
  </div>
</nav>

<!-- Custom dashboard navigation script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Prevent the script from running twice
  if (window.dashNavInitialized) return;
  window.dashNavInitialized = true;
  
  // Force any existing navigation to close first
  const openMenus = document.querySelectorAll('.navbar-collapse.show');
  openMenus.forEach(menu => menu.classList.remove('show'));
  
  // Custom toggle for dashboard navigation
  const dashToggle = document.getElementById('dashboardNavbarToggle');
  const dashMenu = document.getElementById('dashboardNavbar');
  
  if (dashToggle && dashMenu) {
    dashToggle.addEventListener('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      
      console.log('Dashboard toggle clicked');
      dashMenu.classList.toggle('show');
      return false;
    });
  }
  
  // User dropdown toggle
  const userDropdownToggle = document.getElementById('userDashboardDropdown');
  const userDropdownMenu = document.getElementById('dashUserDropdownMenu');
  
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
    if (document.querySelector('#dashMainNavbar').contains(event.target)) {
      return;
    }
    
    // Close any open navbars when clicking outside
    dashMenu?.classList.remove('show');
  });
});
</script>

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
  
  /* Position fixes for notification badge */
  .position-relative .badge {
    transform: translate(25%, -50%) !important;
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

/* Remove underlines from all navigation links */
.navbar a,
.navbar .nav-link,
.navbar .dropdown-item,
.navbar .navbar-brand {
  text-decoration: none !important;
}

/* Optionally add underline only on hover if desired */
.navbar a:hover {
  text-decoration: none !important;
}

/* Fix for specific link underlines that might be coming from other CSS */
#dashboardNavbar .nav-link,
#dashUserDropdownMenu .dropdown-item,
.navbar-brand {
  text-decoration: none !important;
  border-bottom: none !important;
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
