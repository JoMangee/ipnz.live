<?php
/**
 * SMTP Configuration Example
 * 
 * Copy this file to smtp.config.php and fill in your SMTP provider details.
 * This file should be in the root of the ipnz.live directory (same level as www/).
 * 
 * IMPORTANT: This file contains sensitive credentials. Add it to .gitignore!
 */

// Example configurations for popular email providers:

// ============================================
// MAILGUN (Recommended for production)
// ============================================
return [
    'host' => 'smtp.mailgun.org',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'postmaster@mail.ipnz.live',     // Your Mailgun domain
    'password' => 'your-mailgun-smtp-password',    // Get from Mailgun dashboard
    'from_email' => 'noreply@mail.ipnz.live',
    'from_name' => 'IPnz.live'
];

// ============================================
// AWS SES (Simple Email Service)
// ============================================
/*
return [
    'host' => 'email-smtp.us-east-1.amazonaws.com',  // Change region as needed
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'your-ses-smtp-username',
    'password' => 'your-ses-smtp-password',
    'from_email' => 'noreply@ipnz.live',
    'from_name' => 'IPnz.live'
];
*/

// ============================================
// SENDGRID
// ============================================
/*
return [
    'host' => 'smtp.sendgrid.net',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'apikey',
    'password' => 'SG.your-sendgrid-api-key',
    'from_email' => 'noreply@ipnz.live',
    'from_name' => 'IPnz.live'
];
*/

// ============================================
// GMAIL/Google Workspace
// ============================================
/*
return [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',  // Generate app-specific password
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'IPnz.live'
];
*/

// ============================================
// Custom/Local SMTP Server
// ============================================
/*
return [
    'host' => 'mail.yourdomain.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'mail@yourdomain.com',
    'password' => 'your-email-password',
    'from_email' => 'noreply@ipnz.live',
    'from_name' => 'IPnz.live'
];
*/

// ============================================
// ENVIRONMENT VARIABLES (Alternative method)
// ============================================
/*
Instead of this config file, you can set environment variables:

In Docker: Add to docker-compose.yml under ipnz-web environment:
    - SMTP_HOST=smtp.mailgun.org
    - SMTP_PORT=587
    - SMTP_ENCRYPTION=tls
    - SMTP_USERNAME=postmaster@mail.ipnz.live
    - SMTP_PASSWORD=your-mailgun-password
    - MAIL_FROM_EMAIL=noreply@mail.ipnz.live
    - MAIL_FROM_NAME=IPnz.live

In .env file:
    SMTP_HOST=smtp.mailgun.org
    SMTP_PORT=587
    SMTP_ENCRYPTION=tls
    SMTP_USERNAME=postmaster@mail.ipnz.live
    SMTP_PASSWORD=your-mailgun-password
    MAIL_FROM_EMAIL=noreply@mail.ipnz.live
    MAIL_FROM_NAME=IPnz.live
*/

?>
