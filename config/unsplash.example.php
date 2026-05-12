<?php
/**
 * Config Unsplash API - Service d'auto-image pour les cours du module Quiz
 *
 * INSTRUCTIONS POUR COEQUIPIERS :
 *
 * 1. Aller sur https://unsplash.com/oauth/applications
 * 2. Se connecter (compte gratuit, confirmer l'email)
 * 3. Accepter les API Terms (1ere fois uniquement)
 * 4. Cliquer "+ New Application"
 *    - Application name : EduMatch
 *    - Description : Plateforme educative qui matche cours avec images pedagogiques
 *    - Cocher les Terms + "Create application"
 * 5. Copier l'Access Key (PAS la Secret Key)
 * 6. Ajouter dans le fichier .env a la racine du projet :
 *    UNSPLASH_ACCESS_KEY=ta_cle_ici
 *
 * Quota gratuit : 50 requetes/heure en mode dev (suffisant pour creations occasionnelles).
 *
 * Ce fichier est juste un template. Le service api/UnsplashImageService.php
 * lit la cle directement depuis $_ENV['UNSPLASH_ACCESS_KEY'].
 */
declare(strict_types=1);

return [
    'access_key' => $_ENV['UNSPLASH_ACCESS_KEY'] ?? getenv('UNSPLASH_ACCESS_KEY') ?: '',
    'api_url'    => $_ENV['UNSPLASH_API_URL'] ?? 'https://api.unsplash.com',
    'timeout'    => 10,
    'image_size' => 'regular', // 'thumb', 'small', 'regular', 'full', 'raw'
];
