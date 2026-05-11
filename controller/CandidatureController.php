<?php
/**
 * Routeur Candidature
 *
 * Front (postuler) :
 *   ?espace=front&action=ajouter&offre=X     → form de candidature (encadrant + admin)
 *   ?espace=front&action=merci&id=X          → page apres soumission
 *
 * Back (admin) :
 *   ?espace=back&action=liste
 *   ?espace=back&action=details&id=X
 *   ?espace=back&action=modifier&id=X        → accepter / refuser
 *   ?espace=back&action=supprimer&id=X
 */
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/OffreEmploi.php';
require_once __DIR__ . '/../model/Candidature.php';
require_once __DIR__ . '/../api/CvMatchService.php';
require_once __DIR__ . '/../api/Mailer.php';

$pdo = Config::getConnexion();

$espace = $_GET['espace'] ?? 'front';
$action = $_GET['action'] ?? 'ajouter';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($espace === 'back') {
    // Backoffice : admin only
    if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }

    require_once __DIR__ . '/offre_emploi/BackCandidatureController.php';
    $controller = new CandidatureController($pdo);

    switch ($action) {
        case 'liste':       $controller->liste(); break;
        case 'details':     $id > 0 ? $controller->details($id) : $controller->liste(); break;
        case 'modifier':    $id > 0 ? $controller->modifier($id) : $controller->liste(); break;
        case 'supprimer':   $id > 0 ? $controller->supprimer($id) : $controller->liste(); break;
        default:            $controller->liste();
    }
    exit;
}

// Front : postuler (encadrant + admin uniquement)
if ($action === 'ajouter' || $action === 'merci') {
    if (empty($_SESSION['user_id'])) {
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }
    $role = $_SESSION['user_role'] ?? '';
    if (!in_array($role, ['encadrant', 'admin'], true)) {
        header('Location: /gestion_users/controller/OffreEmploiController.php?espace=front&action=liste&error=role');
        exit;
    }
}

require_once __DIR__ . '/offre_emploi/FrontCandidatureController.php';
$controller = new CandidatureController($pdo);

if ($action === 'merci' && $id > 0) {
    $controller->merci($id);
} else {
    $offreId = isset($_GET['offre']) ? (int) $_GET['offre'] : 0;
    if ($offreId <= 0) {
        header('Location: /gestion_users/controller/OffreEmploiController.php?espace=front&action=liste');
        exit;
    }
    $controller->ajouter($offreId);
}
