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
    <!-- Brand for all users -->
    <a class="navbar-brand" href="<?= base_url('index.php/home') ?>">
      <?php if ($userRole === 'admin'): ?>Admin Portal
      <?php elseif ($userRole === 'provider'): ?>Provider Portal
      <?php elseif ($userRole === 'patient'): ?>Patient Portal
      <?php else: ?>Appointment System<?php endif; ?>
    </a>
    
    <!-- Hamburger menu ONLY for guests -->
    <?php if (!$isLoggedIn): ?>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbar" aria-controls="guestNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <!-- Guest Navigation - collapsible on mobile -->
    <div class="collapse navbar-collapse" id="guestNavbar">
      <ul class="navbar-nav me-auto">
        <?php if (!$is_home_page): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/home') ?>">Home</a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/auth/register') ?>">Register</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/auth') ?>">Login</a>
        </li>
      </ul>
    </div>
    <?php else: ?>
    
    <!-- For logged-in users - Desktop Navigation -->
    <div class="desktop-nav">
      <ul class="navbar-nav me-auto">
        <?php if (!$is_home_page): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('index.php/home') ?>">Home</a>
        </li>
        <?php endif; ?>
        
        <?php if ($userRole === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/providers') ?>">Providers</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/services') ?>">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/appointments') ?>">Appointments</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/admin/users') ?>">Users</a>
          </li>
        <?php elseif ($userRole === 'provider'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/provider/services') ?>">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/provider/schedule') ?>">Schedule</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/provider/appointments') ?>">Appointments</a>
          </li>
        <?php elseif ($userRole === 'patient'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/patient/book') ?>">Book</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/appointments') ?>">My Appointments</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php/patient/search') ?>">Find Provider</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
    <?php endif; ?>
    
    <!-- Right side for all users -->
    <ul class="navbar-nav ms-auto">
      <?php if ($isLoggedIn): ?>
        <?php if (($userRole === 'patient' || $userRole === 'provider') && !isset($hideNotifications)): ?>
        <li class="nav-item notifications-icon">
          <a class="nav-link position-relative" href="<?= base_url('index.php/' . $userRole . '/notifications') ?>">
            <i class="fas fa-bell"></i>
            <?php
            // Get unread notification count
            $unreadCount = 0;
            if (isset($db) && $db instanceof mysqli && isset($_SESSION['user_id'])) {
                try {
                    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
                    $stmt = $db->prepare($query);
                    if ($stmt) {
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $unreadCount = $row['count'];
                        }
                    }
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
        
        <!-- User dropdown - This will contain ALL navigation on mobile -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="badge bg-<?= $userRole === 'admin' ? 'danger' : ($userRole === 'provider' ? 'success' : 'primary') ?>">
              <?= ucfirst($userRole) ?>
            </span>
            <?= htmlspecialchars($userName) ?>
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
      <?php else: ?>
        <li class="nav-item auth-links">
          <a class="nav-link" href="<?= base_url('index.php/auth/register') ?>">Register</a>
        </li>
        <li class="nav-item auth-links">
          <a class="nav-link" href="<?= base_url('index.php/auth') ?>">Login</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<style>
/* Custom responsive styling */
@media (max-width: 991px) {
  .desktop-nav {
    display: none !important;
  }
  
  .auth-links {
    display: none !important;
  }
  
  .mobile-only {
    display: block !important;
  }
  
  .dropdown-menu.show {
    display: block !important;
  }
}

@media (min-width: 992px) {
  .mobile-only {
    display: none !important;
  }
  
  .desktop-nav {
    display: flex !important;
  }
  
  .auth-links {
    display: block !important;
  }
}

/* Additional styles for dropdown */
.dropdown-menu {
  padding: 0.5rem;
}
.dropdown-item {
  border-radius: 4px;
  padding: 0.5rem 1rem;
}
.dropdown-item:hover, .dropdown-item:focus {
  background-color: rgba(0,0,0,0.05);
}
.dropdown-header {
  font-weight: bold;
  color: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Manual implementation of dropdown toggle
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
  
  // Handle hamburger menu for guests
  var hamburger = document.querySelector('.navbar-toggler');
  if (hamburger) {
    hamburger.addEventListener('click', function() {
      var target = document.querySelector(this.getAttribute('data-bs-target'));
      if (target) {
        target.classList.toggle('show');
      }
    });
  }
  
  // Handle window resize
  window.addEventListener('resize', function() {
    // Force update display classes when resizing between mobile and desktop
    if (window.innerWidth >= 992) {
      // Desktop mode
      document.querySelectorAll('.desktop-nav').forEach(function(el) {
        el.style.display = 'flex';
      });
      document.querySelectorAll('.mobile-only').forEach(function(el) {
        el.style.display = 'none';
      });
      document.querySelectorAll('.auth-links').forEach(function(el) {
        el.style.display = 'block';
      });
    } else {
      // Mobile mode
      document.querySelectorAll('.desktop-nav').forEach(function(el) {
        el.style.display = 'none';
      });
      document.querySelectorAll('.mobile-only').forEach(function(el) {
        el.style.display = 'block';
      });
      document.querySelectorAll('.auth-links').forEach(function(el) {
        el.style.display = 'none';
      });
      
      // Close any open dropdowns when switching to mobile
      document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
        menu.classList.remove('show');
      });
    }
  });
  
  // Initial check on page load
  if (window.innerWidth >= 992) {
    document.querySelectorAll('.desktop-nav').forEach(function(el) {
      el.style.display = 'flex';
    });
    document.querySelectorAll('.mobile-only').forEach(function(el) {
      el.style.display = 'none';
    });
  } else {
    document.querySelectorAll('.desktop-nav').forEach(function(el) {
      el.style.display = 'none';
    });
    document.querySelectorAll('.mobile-only').forEach(function(el) {
      el.style.display = 'block';
    });
  }
});
</script>

