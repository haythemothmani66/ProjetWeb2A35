<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/ReservationController.php';

$baseUrl = '/gestion_users';

if (empty($_SESSION['user_id'])) {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}
$userRole = $_SESSION['user_role'] ?? '';
if (!in_array($userRole, ['encadrant', 'admin'], true)) {
    header('Location: /gestion_users/view/template/index.php');
    exit;
}

$ctrl = new ReservationController();
$userId = (int) $_SESSION['user_id'];

$flash = null;

// Add disponibilite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $jour = trim((string)($_POST['jour_semaine'] ?? ''));
    $hDebut = trim((string)($_POST['heure_debut'] ?? ''));
    $hFin = trim((string)($_POST['heure_fin'] ?? ''));

    $jours = ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'];
    if (!in_array($jour, $jours, true)) {
        $flash = ['success' => false, 'message' => 'Jour invalide.'];
    } elseif (empty($hDebut) || empty($hFin) || $hDebut >= $hFin) {
        $flash = ['success' => false, 'message' => 'Horaires invalides.'];
    } else {
        $ok = $ctrl->addDisponibilite($userId, $jour, $hDebut . ':00', $hFin . ':00');
        $flash = $ok
            ? ['success' => true, 'message' => 'Creneau ajoute avec succes.']
            : ['success' => false, 'message' => 'Erreur lors de l\'ajout.'];
    }
}

// Delete disponibilite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $idDispo = (int)($_POST['id_disponibilite'] ?? 0);
    if ($idDispo > 0) {
        $ok = $ctrl->deleteDisponibilite($idDispo, $userId);
        $flash = $ok
            ? ['success' => true, 'message' => 'Creneau supprime.']
            : ['success' => false, 'message' => 'Erreur lors de la suppression.'];
    }
}

$dispos = $ctrl->getDisponibilitesByEncadrant($userId);

// Grouper par jour
$dispoByJour = [];
foreach ($dispos as $d) {
    $dispoByJour[$d['jour_semaine']][] = $d;
}

$jours = [
    'lundi'    => 'Lundi',
    'mardi'    => 'Mardi',
    'mercredi' => 'Mercredi',
    'jeudi'    => 'Jeudi',
    'vendredi' => 'Vendredi',
    'samedi'   => 'Samedi',
    'dimanche' => 'Dimanche',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mes disponibilites - EduMatch</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/animate.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
    <style>
        .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
        .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; display: inline-block; }
        .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
        .user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .user-dropdown .user-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #525fe1; }
        .user-dropdown .user-name { font-weight: 600; font-size: 14px; color: #0b104a; white-space: nowrap; }
        .user-dropdown .dropdown-caret { font-size: 10px; color: #6c757d; transition: transform 0.2s; }
        .user-dropdown:hover .dropdown-caret { transform: rotate(180deg); }
        .user-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background: white; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); min-width: 200px; padding: 8px 0; z-index: 1000; margin-top: 8px; }
        .user-dropdown-menu.show { display: block; }
        .user-dropdown-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: #333; text-decoration: none; font-size: 14px; font-weight: 500; }
        .user-dropdown-menu a:hover { background: #f5f7fa; color: #525fe1; }
        .user-dropdown-menu a i { width: 18px; text-align: center; }
        .user-dropdown-menu hr { margin: 6px 0; border-color: #eee; }

        body { background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); min-height: 100vh; }
        .page-header { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 3rem 0; margin-bottom: 2rem; }
        .day-card { background: white; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 12px rgba(15,23,42,0.06); margin-bottom: 1rem; }
        .slot-pill { display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #ddd6fe, #c7d2fe); color: #4338ca; padding: 0.4rem 0.85rem; border-radius: 50px; font-weight: 600; font-size: 0.88rem; margin: 0.25rem; }
        .slot-pill .btn-del { background: transparent; border: none; color: #ef4444; padding: 0; margin-left: 0.25rem; cursor: pointer; }
        .slot-pill .btn-del:hover { color: #b91c1c; }
        .add-form-card { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 12px rgba(15,23,42,0.06); margin-bottom: 2rem; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../template/_navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="fw-bold mb-2"><i class="fas fa-clock me-3"></i>Mes disponibilites</h1>
            <p class="mb-0 opacity-75">Definissez vos creneaux horaires pour permettre aux etudiants de reserver.</p>
        </div>
    </div>

    <div class="container pb-5">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['success'] ? 'success' : 'danger' ?> alert-dismissible fade show">
                <i class="fas fa-<?= $flash['success'] ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <a href="<?= $baseUrl ?>/view/encadrant/dashboard_reservations.php" class="btn btn-light mb-3">
            <i class="fas fa-arrow-left me-2"></i>Retour aux reservations
        </a>

        <!-- Add form -->
        <div class="add-form-card">
            <h5 class="fw-bold mb-3"><i class="fas fa-plus-circle me-2 text-primary"></i>Ajouter un creneau</h5>
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="add">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jour de la semaine</label>
                    <select name="jour_semaine" class="form-control" required>
                        <?php foreach ($jours as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Heure debut</label>
                    <input type="time" name="heure_debut" class="form-control" required step="3600">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Heure fin</label>
                    <input type="time" name="heure_fin" class="form-control" required step="3600">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; font-weight: 700;">
                        <i class="fas fa-plus me-1"></i>Ajouter
                    </button>
                </div>
            </form>
        </div>

        <!-- List by day -->
        <h4 class="fw-bold mb-3"><i class="fas fa-calendar-week me-2 text-primary"></i>Creneaux actuels</h4>

        <?php foreach ($jours as $k => $jourLabel): ?>
            <div class="day-card">
                <h6 class="fw-bold mb-2 text-uppercase" style="color: #6366f1; letter-spacing: 1px;"><?= $jourLabel ?></h6>
                <?php if (empty($dispoByJour[$k])): ?>
                    <p class="text-muted mb-0 fst-italic small">Aucun creneau ce jour</p>
                <?php else: ?>
                    <div>
                        <?php foreach ($dispoByJour[$k] as $d): ?>
                            <span class="slot-pill">
                                <i class="fas fa-clock"></i>
                                <?= substr($d['heure_debut'], 0, 5) ?> - <?= substr($d['heure_fin'], 0, 5) ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce creneau ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_disponibilite" value="<?= (int)$d['id_disponibilite'] ?>">
                                    <button type="submit" class="btn-del" title="Supprimer"><i class="fas fa-times-circle"></i></button>
                                </form>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/jquery-1.12.4.min.js"></script>
    <script src="<?= $baseUrl ?>/assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
