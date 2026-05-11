<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/ReservationController.php';

$baseUrl = '/gestion_users';

if (empty($_SESSION['user_id'])) {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}

$ctrl = new ReservationController();
$userId = (int) $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? '';

// Traitement annulation
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $idRes = (int)($_POST['id_reservation'] ?? 0);
    if ($idRes > 0) {
        $flash = $ctrl->cancelReservation($idRes, $userId);
    }
}

$reservations = $ctrl->getReservationsByEtudiant($userId);

$photoPlaceholder = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#8B5CF6"/></linearGradient></defs><rect width="100" height="100" fill="url(#g)"/><text x="50" y="68" font-size="50" fill="white" text-anchor="middle" font-weight="700">👤</text></svg>');

$statusConfig = [
    'en_attente' => ['label' => 'En attente', 'color' => '#f59e0b', 'bg' => '#fef3c7', 'icon' => 'fa-clock'],
    'acceptee'   => ['label' => 'Acceptee',   'color' => '#10b981', 'bg' => '#d1fae5', 'icon' => 'fa-check-circle'],
    'refusee'    => ['label' => 'Refusee',    'color' => '#ef4444', 'bg' => '#fee2e2', 'icon' => 'fa-times-circle'],
    'annulee'    => ['label' => 'Annulee',    'color' => '#6b7280', 'bg' => '#f3f4f6', 'icon' => 'fa-ban'],
    'terminee'   => ['label' => 'Terminee',   'color' => '#3b82f6', 'bg' => '#dbeafe', 'icon' => 'fa-flag-checkered'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mes reservations - EduMatch</title>
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
        .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; }
        .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
        .user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .user-dropdown .user-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #525fe1; }
        .user-dropdown .user-name { font-weight: 600; font-size: 14px; color: #0b104a; white-space: nowrap; }
        .user-dropdown .dropdown-caret { font-size: 10px; color: #6c757d; transition: transform 0.2s; }
        .user-dropdown:hover .dropdown-caret { transform: rotate(180deg); }
        .user-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background: white; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); min-width: 200px; padding: 8px 0; z-index: 1000; margin-top: 8px; }
        .user-dropdown-menu.show { display: block; }
        .user-dropdown-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: #333; text-decoration: none; font-size: 14px; font-weight: 500; transition: background 0.2s; }
        .user-dropdown-menu a:hover { background: #f5f7fa; color: #525fe1; }
        .user-dropdown-menu a i { width: 18px; text-align: center; }
        .user-dropdown-menu hr { margin: 6px 0; border-color: #eee; }

        body { background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); min-height: 100vh; }
        .page-header { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 3rem 0; margin-bottom: 2rem; }
        .reservation-card { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 15px rgba(15,23,42,0.06); margin-bottom: 1rem; transition: all 0.3s ease; }
        .reservation-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(99,102,241,0.12); }
        .status-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.85rem; border-radius: 50px; font-size: 0.78rem; font-weight: 700; }
        .encadrant-avatar { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd6fe; }
        .info-line { color: #6b7280; font-size: 0.9rem; margin-bottom: 0.3rem; }
        .info-line i { color: #8B5CF6; width: 18px; }
        .btn-cancel { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; }
        .btn-cancel:hover { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../template/_navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="fw-bold mb-2"><i class="fas fa-calendar-check me-3"></i>Mes reservations</h1>
            <p class="mb-0 opacity-75">Suivez l'etat de vos demandes de seances avec les encadrants.</p>
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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="mb-0 text-muted"><strong><?= count($reservations) ?></strong> reservation<?= count($reservations) > 1 ? 's' : '' ?> au total</p>
            <a href="<?= $baseUrl ?>/view/frontoffice/encadrants_list.php" class="btn" style="background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; font-weight: 600;">
                <i class="fas fa-plus me-2"></i>Nouvelle reservation
            </a>
        </div>

        <?php if (empty($reservations)): ?>
            <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                <i class="fas fa-calendar-times" style="font-size: 4rem; color: #d1d5db;"></i>
                <h4 class="mt-3 fw-bold text-muted">Aucune reservation</h4>
                <p class="text-muted">Vous n'avez encore reserve aucune seance.</p>
                <a href="<?= $baseUrl ?>/view/frontoffice/encadrants_list.php" class="btn btn-primary mt-2" style="background: linear-gradient(135deg, #6366f1, #8B5CF6); border: none;">
                    Reserver maintenant
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($reservations as $r):
                $statut = $statusConfig[$r['statut']] ?? $statusConfig['en_attente'];
                $photoEnc = !empty($r['encadrant_photo']) && $r['encadrant_photo'] !== 'default.png'
                    ? $baseUrl . '/uploads/photos/' . htmlspecialchars($r['encadrant_photo'])
                    : $photoPlaceholder;
                $encNom = trim($r['encadrant_prenom'] . ' ' . $r['encadrant_nom']);

                // Verifier si on peut encore annuler (24h avant)
                $canCancel = false;
                if (in_array($r['statut'], ['en_attente', 'acceptee'], true)) {
                    $resDate = new DateTime($r['date_reservation'] . ' ' . $r['heure_debut']);
                    $now = new DateTime('now');
                    $hoursAhead = ($resDate->getTimestamp() - $now->getTimestamp()) / 3600;
                    $canCancel = ($hoursAhead >= 24);
                }
            ?>
                <div class="reservation-card">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <img src="<?= $photoEnc ?>" alt="<?= htmlspecialchars($encNom) ?>" class="encadrant-avatar" onerror="this.onerror=null;this.src='<?= htmlspecialchars($photoPlaceholder) ?>';">
                        </div>
                        <div class="col-md">
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($encNom) ?></h5>
                            <?php if (!empty($r['encadrant_specialite'])): ?>
                                <p class="text-primary fw-semibold small mb-2"><i class="fas fa-star me-1"></i><?= htmlspecialchars($r['encadrant_specialite']) ?></p>
                            <?php endif; ?>
                            <div class="info-line"><i class="fas fa-book"></i> <strong>Matiere :</strong> <?= htmlspecialchars($r['matiere']) ?></div>
                            <div class="info-line"><i class="fas fa-calendar"></i> <?= htmlspecialchars($r['date_reservation']) ?> a <?= substr($r['heure_debut'], 0, 5) ?> - <?= substr($r['heure_fin'], 0, 5) ?></div>
                            <div class="info-line"><i class="fas fa-<?= $r['mode'] === 'en_ligne' ? 'video' : 'map-marker-alt' ?>"></i> <?= $r['mode'] === 'en_ligne' ? 'En ligne' : 'Presentiel' ?></div>
                            <?php if (!empty($r['sujet'])): ?>
                                <div class="info-line"><i class="fas fa-comment"></i> <em><?= htmlspecialchars(mb_substr($r['sujet'], 0, 80)) ?><?= mb_strlen($r['sujet']) > 80 ? '...' : '' ?></em></div>
                            <?php endif; ?>
                            <?php if (!empty($r['notes_encadrant']) && $r['statut'] === 'refusee'): ?>
                                <div class="info-line mt-2 p-2" style="background: #fee2e2; border-radius: 0.5rem;">
                                    <i class="fas fa-info-circle"></i> <strong>Note de l'encadrant :</strong> <?= htmlspecialchars($r['notes_encadrant']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-auto text-md-end">
                            <span class="status-badge mb-2" style="background: <?= $statut['bg'] ?>; color: <?= $statut['color'] ?>;">
                                <i class="fas <?= $statut['icon'] ?>"></i> <?= $statut['label'] ?>
                            </span>
                            <?php if ($canCancel): ?>
                                <div class="mt-2">
                                    <form method="POST" onsubmit="return confirm('Voulez-vous vraiment annuler cette reservation ?');" class="d-inline">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="id_reservation" value="<?= (int)$r['id_reservation'] ?>">
                                        <button type="submit" class="btn-cancel">
                                            <i class="fas fa-times me-1"></i>Annuler
                                        </button>
                                    </form>
                                </div>
                            <?php elseif (in_array($r['statut'], ['en_attente', 'acceptee'], true)): ?>
                                <small class="d-block text-muted mt-1">Annulation impossible (< 24h)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/jquery-1.12.4.min.js"></script>
    <script src="<?= $baseUrl ?>/assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
