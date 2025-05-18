<!DOCTYPE html>
<html lang="en">
<head>
    <title>Patient Appointment System - Book Your Healthcare Appointments Online</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generate_csrf_token() ?>">
    
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
        /* Fix for carousel card height */
        #testimonialCarousel .card {
        height: auto !important; /* Override any height settings */
        max-height: none !important;
        }

        #testimonialCarousel .carousel-item {
        height: auto !important;
        }

        /* Ensure testimonial cards don't grow unnecessarily tall */
        #testimonialCarousel .card-body {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        }

        /* Limit the height of the testimonial quote */
        #testimonialCarousel .lead {
        max-height: 150px;
        overflow-y: auto;
        margin-bottom: 1.5rem;
        }

        /* Make sure avatar sizing is consistent */
        #testimonialCarousel .rounded-circle {
        min-width: 50px;
        min-height: 50px;
        max-width: 50px;
        max-height: 50px;
        margin-right: 1rem;
        }
        
        /* Override for the specific call-to-action card */
        section.py-5 > .container > .card.text-center.bg-primary {
        height: auto !important;
        }

        section.py-5 > .container > .card.text-center.bg-primary > .card-body.py-5 {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
        }

        /* Reset any hover transformations for this specific card */
        section.py-5 > .container > .card.text-center.bg-primary:hover {
        transform: none !important;
        }
        

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero-section {
            background-color: #0d6efd;
            color: white;
            padding: 4rem 0;
            border-radius: 0 0 2rem 2rem;
        }
        .card {
            border-radius: 0.5rem;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .service-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }
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
        .user-dashboard {
            background-color: #f8f9fa;
            border-radius: 1rem;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .appointment-card {
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card {
            padding: 1.5rem;
            text-align: center;
            border-radius: 0.5rem;
            color: white;
        }
        .stat-card.blue {
            background-color: #0d6efd;
        }
        .stat-card.green {
            background-color: #198754;
        }
        .stat-card.red {
            background-color: #dc3545;
        }
        .stat-card.yellow {
            background-color: #ffc107;
            color: #212529;
        }
        .stat-card h2 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .stat-card p {
            margin-bottom: 0;
            opacity: 0.8;
        }
        .availability-slot {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background-color: white;
            border-radius: 0.5rem;
            border-left: 4px solid #28a745;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        /* Testimonial carousel styles */
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
        .carousel-item {
            padding: 1rem 4rem;
        }
        .carousel-item .card {
            box-shadow: 0 6px 10px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s ease;
        }
        .carousel-item .card:hover {
            transform: translateY(-5px);
        }
        @media (max-width: 767px) {
            .carousel-item {
                padding: 1rem;
            }
        }
        
        /* Make sure navbar toggle works */
        .navbar-collapse.collapsing {
            height: auto;
            -webkit-transition: height 0.35s ease;
            transition: height 0.35s ease;
        }
    </style>
    
    <!-- Load Bootstrap JS in the header to ensure it's available -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include_once APP_ROOT . '/views/partials/homeNav.php'; ?>
    
    <!-- Flash Messages Container -->
    <div class="container mt-3">
        <?php display_flash_messages(); ?>
    </div>
