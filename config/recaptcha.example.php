<?php
/**
 * Config reCAPTCHA v2 pour EduMatch (formulaire candidature)
 *
 * INSTRUCTIONS POUR COEQUIPIERS :
 * 1. Copier ce fichier en `config/recaptcha.php`
 * 2. Aller sur https://www.google.com/recaptcha/admin/create
 * 3. Creer un site reCAPTCHA v2 "Je ne suis pas un robot"
 *    - Domaine : localhost
 * 4. Remplacer les valeurs ci-dessous par tes cles
 *
 * Si tu veux desactiver le reCAPTCHA : 'enabled' => false
 */
return [
    'enabled'    => false, // mettre true apres avoir mis tes cles
    'site_key'   => 'YOUR_SITE_KEY_HERE',
    'secret_key' => 'YOUR_SECRET_KEY_HERE',
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    'timeout'    => 5,
];
