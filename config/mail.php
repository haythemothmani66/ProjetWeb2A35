<?php
/**
 * Config SMTP utilisee par api/Mailer.php (module offre_emploi)
 * Aligne avec les credentials utilises dans api/MailHelper.php
 */
return [
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'secure'     => 'tls',
    'username'   => 'chahdtissaoui29@gmail.com',
    'password'   => 'lnainfkoigwpvggw',
    'from_email' => 'chahdtissaoui29@gmail.com',
    'from_name'  => 'EduMatch Team',
    'reply_to'   => 'chahdtissaoui29@gmail.com',
    'debug'      => false,
];
