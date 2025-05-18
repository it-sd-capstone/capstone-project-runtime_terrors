<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generate_csrf_token() ?>">
    <title>Patient Appointment System - Book Your Healthcare Appointments Online</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    
    <!-- Custom CSS -->
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
    
    <style>
        body {
            padding-top: 0;
            padding-bottom: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden; /* Prevent horizontal scroll */
        }
        
        /* Header container styles from header.php */
        .header-container {
            max-width: 1320px; 
            width: 95%;
            margin: 0 auto;
        }
        
        /* Card styles matching header.php */
        .card {
            margin-bottom: 20px;
            border-radius: 12px !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
            transition: none !important;
            border: none;
        }
        
        .card:hover {
            transform: none !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        }
        
        /* Hero section - Remove any spacing between navbar and hero */
        .hero-section {
            background-color: #0d6efd;
            color: white;
            padding: 4rem 0;
            border-radius: 0 0 2rem 2rem;
            margin-top: 0 !important; /* Remove any top margin */
        }
        
        /* Remove any bottom margin from navbar */
        #mainNavbar {
            margin-bottom: 0 !important;
            border-bottom: 0 !important;
        }
        
        /* Remove container spacing above hero section */
        .container.mt-3 {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        
        /* Specific fix for flash messages container */
        .flash-messages-container {
            margin-top: 0 !important;
            position: absolute;
            width: 100%;
            z-index: 1000;
        }
        
        /* Testimonial carousel adjustments to match card styling */
        #testimonialCarousel .card {
            height: auto !important;
            max-height: none !important;
        }

        #testimonialCarousel .carousel-item {
            height: auto !important;
        }
        
        /* Carousel controls */
        .carousel-control-prev-icon, .carousel-control-next-icon {
            background-color: rgba(13, 110, 253, 0.8);
            width: 3rem;
            height: 3rem;
            background-size: 1.5rem;
        }
        
        .carousel-indicators [data-bs-target] {
            background-color: #0d6efd;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
        }
        
        /* Footer styling */
        .footer {
            background-color: #212529;
            color: white;
            padding: 3rem 0;
            margin-top: 3rem;
        }
        
        .footer a {
            color: white;
            text-decoration: none;
        }
        
        .footer a:hover {
            color: #0d6efd;
        }
        
        /* Responsive title handling from header.php */
        @media (max-width: 767.98px) {
            .page-title-desktop {
                display: none;
            }
            
            .carousel-item {
                padding: 1rem;
            }
        }
        
        @media (min-width: 768px) {
            .page-title-mobile {
                display: none;
            }
            
            .carousel-item {
                padding: 1rem 4rem;
            }
        }
        
        /* Any specific card overrides needed */
        .call-to-action {
            height: auto !important;
        }
        
        /* Adjustments for features that MUST remain unique to home page */
        .service-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }
        
        /* Other card styles that need to be preserved but modified */
        .stat-card {
            padding: 1.5rem;
            text-align: center;
            border-radius: 0.5rem;
            color: white;
        }
        
        /* Fix navbar */
        .navbar-collapse.collapsing {
            height: auto;
            transition: height 0.35s ease;
        }
    </style>
    
    <!-- Load Bootstrap JS in the header to ensure it's available -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include_once APP_ROOT . '/views/partials/homeNav.php'; ?>
    
    <!-- Flash Messages Container - Now has position absolute to not create space -->
    <div class="flash-messages-container">
        <?php display_flash_messages(); ?>
    </div>