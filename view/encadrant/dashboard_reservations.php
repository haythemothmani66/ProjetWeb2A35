<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/ReservationController.php';
require_once __DIR__ . '/../../api/MailHelper.php';

$baseUrl = '/gestion_users';

// Protection : seuls encadrants/admin
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

// Traitement actions accept/refuse depuis le dashboard
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $idRes = (int)($_POST['id_reservation'] ?? 0);
    $notes = trim((string)($_POST['notes_encadrant'] ?? ''));

    if ($idRes > 0 && in_array($action, ['accept', 'refuse'], true)) {
        $newStatut = $action === 'accept' ? 'acceptee' : 'refusee';

        // Verifier que c'est bien une reservation de cet encadrant
        $pdo = Config::getConnexion();
        $check = $pdo->prepare("
            SELECT r.*, us.email AS etudiant_email, us.nom AS etudiant_nom, us.prenom AS etudiant_prenom,
                   ue.nom AS encadrant_nom, ue.prenom AS encadrant_prenom,
                   pe.adresse AS encadrant_adresse
            FROM reservations r
            INNER JOIN user us ON us.id = r.id_etudiant
            INNER JOIN user ue ON ue.id = r.id_encadrant
            LEFT JOIN profil pe ON pe.user_id = ue.id
            WHERE r.id_reservation = ? AND r.id_encadrant = ?
        ");
        $check->execute([$idRes, $userId]);
        $res = $check->fetch(PDO::FETCH_ASSOC);

        if ($res && $res['statut'] === 'en_attente') {
            $result = $ctrl->updateStatus($idRes, $newStatut, $notes ?: null);
            if ($result['success']) {
                // Envoyer mail a l'etudiant (avec adresse si presentiel + acceptee)
                MailHelper::sendReservationStatusToEtudiant(
                    $res['etudiant_email'],
                    trim($res['etudiant_prenom'] . ' ' . $res['etudiant_nom']),
                    trim($res['encadrant_prenom'] . ' ' . $res['encadrant_nom']),
                    $res['date_reservation'],
                    substr($res['heure_debut'], 0, 5),
                    substr($res['heure_fin'], 0, 5),
                    $res['matiere'],
                    $newStatut,
                    $res['mode'] ?? null,
                    $res['encadrant_adresse'] ?? null
                );
                $flash = ['success' => true, 'message' => 'Reservation ' . ($newStatut === 'acceptee' ? 'acceptee' : 'refusee') . ' et email envoye a l\'etudiant.'];
            } else {
                $flash = $result;
            }
        } else {
            $flash = ['success' => false, 'message' => 'Reservation introuvable ou deja traitee.'];
        }
    }
}

$reservations = $ctrl->getReservationsByEncadrant($userId);

// Stats
$stats = ['total' => 0, 'en_attente' => 0, 'acceptee' => 0, 'refusee' => 0, 'annulee' => 0];
foreach ($reservations as $r) {
    $stats['total']++;
    if (isset($stats[$r['statut']])) $stats[$r['statut']]++;
}

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
    <title>Mes seances - EduMatch</title>
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
        .stat-card { background: white; border-radius: 1rem; padding: 1.25rem; text-align: center; box-shadow: 0 4px 12px rgba(15,23,42,0.06); }
        .stat-card .icon { font-size: 1.75rem; margin-bottom: 0.5rem; }
        .stat-card .number { font-size: 2rem; font-weight: 800; color: #0b104a; line-height: 1; }
        .stat-card .label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-top: 0.5rem; }
        .reservation-card { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 15px rgba(15,23,42,0.06); margin-bottom: 1rem; }
        .status-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.85rem; border-radius: 50px; font-size: 0.78rem; font-weight: 700; }
        .etudiant-avatar { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd6fe; }
        .info-line { color: #6b7280; font-size: 0.9rem; margin-bottom: 0.3rem; }
        .info-line i { color: #8B5CF6; width: 18px; }
        .btn-accept { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; }
        .btn-accept:hover { background: linear-gradient(135deg, #059669, #047857); color: white; }
        .btn-refuse { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; }
        .btn-refuse:hover { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../template/_navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="fw-bold mb-2"><i class="fas fa-chalkboard-teacher me-3"></i>Mes seances</h1>
            <p class="mb-0 opacity-75">Gerez les demandes de reservation de vos etudiants.</p>
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

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-6">
                <div class="stat-card"><div class="icon" style="color: #6366f1;"><i class="fas fa-list"></i></div>
                    <div class="number"><?= $stats['total'] ?></div><div class="label">Total</div></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card"><div class="icon" style="color: #f59e0b;"><i class="fas fa-clock"></i></div>
                    <div class="number"><?= $stats['en_attente'] ?></div><div class="label">En attente</div></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card"><div class="icon" style="color: #10b981;"><i class="fas fa-check-circle"></i></div>
                    <div class="number"><?= $stats['acceptee'] ?></div><div class="label">Acceptees</div></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card"><div class="icon" style="color: #ef4444;"><i class="fas fa-times-circle"></i></div>
                    <div class="number"><?= $stats['refusee'] ?></div><div class="label">Refusees</div></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-card"><div class="icon" style="color: #6b7280;"><i class="fas fa-ban"></i></div>
                    <div class="number"><?= $stats['annulee'] ?></div><div class="label">Annulees</div></div>
            </div>
            <div class="col-md-2 col-6">
                <a href="<?= $baseUrl ?>/view/encadrant/disponibilites.php" class="btn w-100 h-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; font-weight: 600; border-radius: 1rem;">
                    <i class="fas fa-calendar-alt me-2"></i>Mes disponibilites
                </a>
            </div>
        </div>

        <?php if (empty($reservations)): ?>
            <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                <i class="fas fa-inbox" style="font-size: 4rem; color: #d1d5db;"></i>
                <h4 class="mt-3 fw-bold text-muted">Aucune reservation recue</h4>
                <p class="text-muted">Les demandes de seances de vos etudiants apparaitront ici.</p>
            </div>
        <?php else: ?>
            <?php foreach ($reservations as $r):
                $statut = $statusConfig[$r['statut']] ?? $statusConfig['en_attente'];
                $photoEt = !empty($r['etudiant_photo']) && $r['etudiant_photo'] !== 'default.png'
                    ? $baseUrl . '/uploads/photos/' . htmlspecialchars($r['etudiant_photo'])
                    : $photoPlaceholder;
                $nomEt = trim($r['etudiant_prenom'] . ' ' . $r['etudiant_nom']);
            ?>
                <div class="reservation-card">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <img src="<?= $photoEt ?>" alt="<?= htmlspecialchars($nomEt) ?>" class="etudiant-avatar" onerror="this.onerror=null;this.src='<?= htmlspecialchars($photoPlaceholder) ?>';">
                        </div>
                        <div class="col-md">
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($nomEt) ?></h5>
                            <p class="text-muted small mb-2"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($r['etudiant_email']) ?></p>
                            <div class="info-line"><i class="fas fa-book"></i> <strong>Matiere :</strong> <?= htmlspecialchars($r['matiere']) ?></div>
                            <div class="info-line"><i class="fas fa-calendar"></i> <?= htmlspecialchars($r['date_reservation']) ?> a <?= substr($r['heure_debut'], 0, 5) ?> - <?= substr($r['heure_fin'], 0, 5) ?></div>
                            <div class="info-line"><i class="fas fa-<?= $r['mode'] === 'en_ligne' ? 'video' : 'map-marker-alt' ?>"></i> <?= $r['mode'] === 'en_ligne' ? 'En ligne' : 'Presentiel' ?></div>
                            <?php if (!empty($r['sujet'])): ?>
                                <div class="info-line"><i class="fas fa-comment"></i> <strong>Sujet :</strong> <?= htmlspecialchars($r['sujet']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($r['notes_etudiant'])): ?>
                                <div class="info-line"><i class="fas fa-sticky-note"></i> <em><?= htmlspecialchars($r['notes_etudiant']) ?></em></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-auto text-md-end">
                            <span class="status-badge mb-2" style="background: <?= $statut['bg'] ?>; color: <?= $statut['color'] ?>;">
                                <i class="fas <?= $statut['icon'] ?>"></i> <?= $statut['label'] ?>
                            </span>
                            <?php if ($r['statut'] === 'en_attente'): ?>
                                <div class="d-flex gap-2 mt-2">
                                    <form method="POST" onsubmit="return confirm('Accepter cette reservation ?');" class="d-inline">
                                        <input type="hidden" name="action" value="accept">
                                        <input type="hidden" name="id_reservation" value="<?= (int)$r['id_reservation'] ?>">
                                        <button type="submit" class="btn-accept"><i class="fas fa-check me-1"></i>Accepter</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Refuser cette reservation ?');" class="d-inline">
                                        <input type="hidden" name="action" value="refuse">
                                        <input type="hidden" name="id_reservation" value="<?= (int)$r['id_reservation'] ?>">
                                        <button type="submit" class="btn-refuse"><i class="fas fa-times me-1"></i>Refuser</button>
                                    </form>
                                </div>
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
