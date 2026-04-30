<?php

// Load local credentials if available, otherwise use placeholders
$localConfig = __DIR__ . '/mail.local.php';
if (file_exists($localConfig)) {
    return require $localConfig;
}

// Fallback to placeholder configuration (for repo, CI/CD, etc.)
return [
    // SMTP server
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'secure' => 'tls',

    // Authentication (placeholders only)
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',

    // Sender
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'EduMatch - Recrutement',

    // Reply-to
    'reply_to' => 'your-email@gmail.com',

    // Debug
    'debug' => false,
];