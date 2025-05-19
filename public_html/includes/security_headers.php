<?php
// Basic security headers that won't break functionality
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Start with a very permissive CSP that won't break your site
// Comment this out if you're still having issues
header("Content-Security-Policy: default-src 'self' * data: 'unsafe-inline' 'unsafe-eval'; script-src 'self' * 'unsafe-inline' 'unsafe-eval'; style-src 'self' * 'unsafe-inline'; img-src 'self' * data:;");