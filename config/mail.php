<?php

return [
    'host' => $_ENV['MAIL_HOST'],
    'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
    'secure' => $_ENV['MAIL_SECURE'] ?? 'tls',
    'username' => $_ENV['MAIL_USERNAME'],
    'password' => $_ENV['MAIL_PASSWORD'],
    'from_email' => $_ENV['MAIL_FROM_EMAIL'],
    'from_name' => $_ENV['MAIL_FROM_NAME'],
    'reply_to' => $_ENV['MAIL_REPLY_TO'],
    'debug' => filter_var($_ENV['MAIL_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
];
