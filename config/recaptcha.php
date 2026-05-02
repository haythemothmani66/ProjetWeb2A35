<?php

return [
    'site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
    'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'],
    'verify_url' => $_ENV['RECAPTCHA_VERIFY_URL'] ?? 'https://www.google.com/recaptcha/api/siteverify',
    'timeout' => (int)($_ENV['RECAPTCHA_TIMEOUT'] ?? 5),
    'enabled' => filter_var($_ENV['RECAPTCHA_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN),

];