<?php
/**
 * Routeur Offre d'emploi
 * Dispatch entre les actions front (public) et back (admin)
 *
 * URLs :
 *   /gestion_users/controller/OffreEmploiController.php?espace=front&action=liste
 *   /gestion_users/controller/OffreEmploiController.php?espace=front&action=details&id=X
 *   /gestion_users/controller/OffreEmploiController.php?espace=back&action=liste     (admin)
 *   /gestion_users/controller/OffreEmploiController.php?espace=back&action=ajouter
 *   /gestion_users/controller/OffreEmploiController.php?espace=back&action=modifier&id=X
 *   /gestion_users/controller/OffreEmploiController.php?espace=back&action=details&id=X
 *   /gestion_users/controller/OffreEmploiController.php?espace=back&action=supprimer&id=X
 *   /gestion_users/controller/OffreEmploiController.php?espace=back&action=stats
 *   /gestion_users/controller/OffreEmploiController.php?espace=back&action=exportStatsPdf
 */
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/OffreEmploi.php';
require_once __DIR__ . '/../model/Candidature.php';
require_once __DIR__ . '/../api/CvMatchService.php';

$pdo = Config::getConnexion();

$espace = $_GET['espace'] ?? 'front';
$action = $_GET['action'] ?? 'liste';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($espace === 'back') {
    // Backoffice : admin only
    if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }

    require_once __DIR__ . '/offre_emploi/BackOffreEmploiController.php';
    $controller = new OffreEmploiController($pdo);

    switch ($action) {
        case 'liste':         $controller->liste(); break;
        case 'stats':         $controller->stats(); break;
        case 'exportStatsPdf': $controller->exportStatsPdf(); break;
        case 'details':       $id > 0 ? $controller->details($id) : $controller->liste(); break;
        case 'ajouter':       $controller->ajouter(); break;
        case 'modifier':      $id > 0 ? $controller->modifier($id) : $controller->liste(); break;
        case 'supprimer':     $id > 0 ? $controller->supprimer($id) : $controller->liste(); break;
        default:              $controller->liste();
    }
    exit;
}

// Front : public access (lecture libre)
require_once __DIR__ . '/offre_emploi/FrontOffreEmploiController.php';
$controller = new OffreEmploiController($pdo);

if ($action === 'details' && $id > 0) {
    $controller->details($id);
} else {
    $controller->liste();
}
