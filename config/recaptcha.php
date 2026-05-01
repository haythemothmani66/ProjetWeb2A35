<?php

// Load local credentials if available, otherwise use placeholders.
$localConfig = __DIR__ . '/recaptcha.local.php';
if (file_exists($localConfig)) {
    return require $localConfig;
}

return [
    'site_key' => 'your-site-key',
    'secret_key' => 'your-secret-key',
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    'timeout' => 5,
    'enabled' => true,
];