<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/ReservationController.php';

// Protection : etudiant / admin / encadrant (encadrant peut voir mais pas reserver)
if (empty($_SESSION['user_id'])) {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}

$baseUrl = '/gestion_users';
$ctrl = new ReservationController();

$search = trim((string)($_GET['search'] ?? ''));
$specialite = trim((string)($_GET['specialite'] ?? ''));

$encadrants = $ctrl->getAllEncadrants($search ?: null, $specialite ?: null);
$specialites = $ctrl->getAllSpecialites();

$userRole = $_SESSION['user_role'] ?? '';
$canReserve = in_array($userRole, ['etudiant', 'admin'], true);

// Placeholder photo
$photoPlaceholder = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#8B5CF6"/></linearGradient></defs><rect width="200" height="200" fill="url(#g)"/><text x="100" y="135" font-size="100" fill="white" text-anchor="middle" font-weight="700">👤</text></svg>');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reserver une seance - EduMatch</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/animate.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
    <style>
        /* Navbar dropdown (necessaire pour _navbar.php) */
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

        /* Page */
        body { background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); min-height: 100vh; }
        .page-hero { background: linear-gradient(135deg, #6366f1 0%, #8B5CF6 50%, #c026d3 100%); color: white; padding: 4rem 0 5rem; position: relative; overflow: hidden; margin-bottom: -3rem; }
        .page-hero::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%; }
        .page-hero h1 { font-size: 3rem; font-weight: 800; margin-bottom: 0.75rem; position: relative; }
        .page-hero p { font-size: 1.15rem; opacity: 0.9; position: relative; }

        .filters-card { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 8px 30px rgba(15,23,42,0.08); position: relative; z-index: 10; }

        .encadrant-card { background: white; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 4px 20px rgba(15,23,42,0.06); transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275); height: 100%; display: flex; flex-direction: column; }
        .encadrant-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(99,102,241,0.15); }
        .encadrant-card .photo-wrap { position: relative; height: 200px; overflow: hidden; background: linear-gradient(135deg, #6366f1, #8B5CF6); }
        .encadrant-card .photo-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .encadrant-card:hover .photo-wrap img { transform: scale(1.1); }
        .encadrant-card .photo-wrap::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 60px; background: linear-gradient(to top, rgba(0,0,0,0.5), transparent); }
        .encadrant-card .role-badge { position: absolute; top: 1rem; right: 1rem; background: rgba(255,255,255,0.95); color: #6366f1; padding: 0.35rem 0.85rem; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .encadrant-card .card-body { padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column; }
        .encadrant-card h3 { font-size: 1.2rem; font-weight: 700; color: #0b104a; margin-bottom: 0.25rem; }
        .encadrant-card .specialite { color: #6366f1; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.75rem; }
        .encadrant-card .bio { color: #6b7280; font-size: 0.88rem; line-height: 1.5; margin-bottom: 1rem; flex-grow: 1; }
        .encadrant-card .info-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: #6b7280; margin-bottom: 0.4rem; }
        .encadrant-card .info-row i { color: #8B5CF6; width: 16px; }
        .btn-reserve { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 700; text-decoration: none; transition: all 0.3s ease; display: block; text-align: center; margin-top: 1rem; box-shadow: 0 4px 15px rgba(99,102,241,0.3); }
        .btn-reserve:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
        .btn-reserve.disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; box-shadow: none; }
        .btn-reserve.disabled:hover { background: #e5e7eb; transform: none; box-shadow: none; }

        .empty-state { text-align: center; padding: 4rem 2rem; color: #6b7280; }
        .empty-state i { font-size: 4rem; opacity: 0.3; }
    </style>
</head>
<body data-spy="scroll" data-offset="80">
    <?php include __DIR__ . '/../template/_navbar.php'; ?>

    <!-- HERO -->
    <section class="page-hero">
        <div class="container text-center">
            <h1><i class="fas fa-calendar-check me-3"></i>Reserver une seance</h1>
            <p>Choisissez l'encadrant qui correspond a vos besoins et reservez en quelques clics.</p>
        </div>
    </section>

    <!-- FILTERS -->
    <div class="container py-4 position-relative">
        <div class="filters-card mb-4">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rechercher</label>
                    <input type="text" name="search" class="form-control form-control-lg" placeholder="Nom, prenom, specialite..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Specialite</label>
                    <select name="specialite" class="form-control form-control-lg">
                        <option value="">Toutes les specialites</option>
                        <?php foreach ($specialites as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $specialite === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-lg w-100" style="background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; font-weight: 700;">
                        <i class="fas fa-search me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>

        <!-- COUNT -->
        <p class="text-muted mb-4">
            <strong><?= count($encadrants) ?></strong> encadrant<?= count($encadrants) > 1 ? 's' : '' ?> trouve<?= count($encadrants) > 1 ? 's' : '' ?>
            <?php if ($search || $specialite): ?>
                pour vos criteres <a href="encadrants_list.php" class="ms-2 small">(reinitialiser)</a>
            <?php endif; ?>
        </p>

        <!-- GRID -->
        <?php if (empty($encadrants)): ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <h4 class="mt-3 fw-bold">Aucun encadrant trouve</h4>
                <p>Essayez de modifier vos criteres de recherche.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($encadrants as $e):
                    $photoSrc = !empty($e['photo']) && $e['photo'] !== 'default.png'
                        ? $baseUrl . '/uploads/photos/' . htmlspecialchars($e['photo'])
                        : $photoPlaceholder;
                    $nomComplet = trim(($e['prenom'] ?? '') . ' ' . ($e['nom'] ?? ''));
                ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="encadrant-card">
                            <div class="photo-wrap">
                                <img src="<?= $photoSrc ?>" alt="<?= htmlspecialchars($nomComplet) ?>" onerror="this.onerror=null;this.src='<?= htmlspecialchars($photoPlaceholder) ?>';">
                                <span class="role-badge"><i class="fas fa-chalkboard-teacher me-1"></i>Encadrant</span>
                            </div>
                            <div class="card-body">
                                <h3><?= htmlspecialchars($nomComplet) ?></h3>
                                <?php if (!empty($e['specialite'])): ?>
                                    <div class="specialite">
                                        <i class="fas fa-star me-1"></i><?= htmlspecialchars($e['specialite']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($e['bio_text'])): ?>
                                    <p class="bio"><?= htmlspecialchars(mb_substr($e['bio_text'], 0, 110)) ?><?= mb_strlen($e['bio_text']) > 110 ? '...' : '' ?></p>
                                <?php else: ?>
                                    <p class="bio fst-italic">Aucune biographie renseignee.</p>
                                <?php endif; ?>

                                <?php if (!empty($e['niveau'])): ?>
                                    <div class="info-row"><i class="fas fa-graduation-cap"></i> <span><?= htmlspecialchars($e['niveau']) ?></span></div>
                                <?php endif; ?>
                                <?php if (!empty($e['etablissement_ecole'])): ?>
                                    <div class="info-row"><i class="fas fa-university"></i> <span><?= htmlspecialchars($e['etablissement_ecole']) ?></span></div>
                                <?php endif; ?>
                                <?php if (!empty($e['ville']) || !empty($e['pays'])): ?>
                                    <div class="info-row"><i class="fas fa-map-marker-alt"></i> <span><?= htmlspecialchars(trim($e['ville'] . ' ' . $e['pays'])) ?></span></div>
                                <?php endif; ?>
                                <div class="info-row"><i class="fas fa-envelope"></i> <span><?= htmlspecialchars($e['email']) ?></span></div>

                                <?php if ($canReserve): ?>
                                    <a href="<?= $baseUrl ?>/view/frontoffice/reservation_calendar.php?encadrant=<?= (int)$e['id'] ?>" class="btn-reserve">
                                        <i class="fas fa-calendar-plus me-2"></i>Reserver une seance
                                    </a>
                                <?php else: ?>
                                    <span class="btn-reserve disabled">
                                        <i class="fas fa-lock me-2"></i>Reserve aux etudiants
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/jquery-1.12.4.min.js"></script>
    <script src="<?= $baseUrl ?>/assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
