<?php
$offre = $offre ?? [];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Back Office - Details offre</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../dasher-1.0.0/src/assets/css/theme.css" />
    <script src="../dasher-1.0.0/src/assets/js/vendors/color-modes.js"></script>
    <style>
        body { background: #f6f8fc; font-family: 'Public Sans', sans-serif; }
        .emploi-shell { min-height: 100vh; }
        .emploi-sidebar { width: 280px; background: #0f172a; color: #fff; }
        .emploi-sidebar a { color: rgba(255,255,255,.8); text-decoration: none; }
        .emploi-sidebar a:hover, .emploi-sidebar .active { color: #fff; }
        .emploi-main { flex: 1; min-width: 0; }
        .emploi-topbar { background: #fff; border-bottom: 1px solid #e5e7eb; }
        .metric-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        @media (max-width: 991.98px) { .emploi-sidebar { width: 100%; } }
    </style>
</head>
<body>
    <div class="emploi-shell d-flex">
        <aside class="emploi-sidebar d-none d-lg-flex flex-column">
            <?php include __DIR__ . '/../partials/brand.php'; ?>
            <?php $activeTab = 'offres'; include __DIR__ . '/../partials/nav.php'; ?>
        </aside>

        <div class="emploi-main">
            <header class="emploi-topbar px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-secondary small">Module Offres d'emploi</p>
                    <h1 class="h4 mb-0">Details de l'offre</h1>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="index.php?espace=back&module=offreemploi&action=liste">Retour liste</a>
                    <a class="btn btn-primary" href="index.php?espace=back&module=offreemploi&action=modifier&id=<?= (int) $offre['id'] ?>">Modifier</a>
                </div>
            </header>

            <main class="container-fluid p-4 p-lg-5">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card metric-card">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h3 class="h5 mb-0"><?= htmlspecialchars((string) $offre['titre']) ?></h3>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <p class="text-secondary mb-3"><?= nl2br(htmlspecialchars((string) $offre['description'])) ?></p>
                                <h4 class="h6">Competences requises</h4>
                                <p class="mb-0"><?= nl2br(htmlspecialchars((string) ($offre['competencesrequises'] ?? 'Non specifiees'))) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card metric-card h-100">
                            <div class="card-body d-flex flex-column gap-2">
                                <p class="mb-1"><strong>Lieu:</strong> <?= htmlspecialchars((string) $offre['lieu']) ?></p>
                                <p class="mb-1"><strong>Type contrat:</strong> <?= htmlspecialchars((string) $offre['typecontrat']) ?></p>
                                <p class="mb-1"><strong>Salaire min:</strong> <?= htmlspecialchars((string) ($offre['salairemin'] ?? '-')) ?></p>
                                <p class="mb-1"><strong>Salaire max:</strong> <?= htmlspecialchars((string) ($offre['salairemax'] ?? '-')) ?></p>
                                <p class="mb-1"><strong>Date creation:</strong> <?= htmlspecialchars((string) $offre['datecreation']) ?></p>
                                <p class="mb-1"><strong>Date limite:</strong> <?= htmlspecialchars((string) $offre['datelimite']) ?></p>
                                <p class="mb-3"><strong>Statut:</strong> <span class="badge <?= ($offre['statut'] === 'ouverte') ? 'text-bg-success' : 'text-bg-danger' ?>"><?= htmlspecialchars((string) $offre['statut']) ?></span></p>
                                <a class="btn btn-outline-danger" href="index.php?espace=back&module=offreemploi&action=supprimer&id=<?= (int) $offre['id'] ?>" onclick="return confirm('Supprimer cette offre ?');">Supprimer</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../dasher-1.0.0/src/assets/js/main.js"></script>
</body>
</html>
