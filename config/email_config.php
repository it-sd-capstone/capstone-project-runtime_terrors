<?php
/**
 * Email Configuration
 * 
 * Configure email settings for the application
 */
return [
    'mail_type' => 'smtp', // Use SMTP for reliable delivery
    'smtp' => [
        'host' => 'smtp.sendgrid.net',
        'port' => 587,
        'secure' => 'tls',
        'auth' => true,
        'username' => 'apikey', // Use literally "apikey" as the username
        'password' => getenv('SENDGRID_API_KEY') ?: 'SENDGRID_API_KEY_PLACEHOLDER', 

        'from_email' => 'admin@capstone.mommabearsweetz.com',
        'from_name' => 'Appointment System',
        'debug' => 2 // Set to 2 for testing, change to 0 for production
    ],
    'mail' => [
        'from_email' => 'admin@capstone.mommabearsweetz.com',
        'from_name' => 'Appointment System'
    ]
];
