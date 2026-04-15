<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Administration - Details candidature</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background: #f6f8fc; font-family: 'Public Sans', sans-serif; }
        .shell { min-height: 100vh; }
        .sidebar { width: 280px; background: #0f172a; color: #fff; }
        .sidebar a { color: rgba(255,255,255,.82); text-decoration: none; }
        .sidebar a:hover, .sidebar .active { color: #fff; }
        .brand { display:flex; align-items:center; gap:.75rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,.08); }
        .brand-badge {
            width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, #f3a712, #ff7a59);
            display:flex; align-items:center; justify-content:center; font-weight:800; color:#111;
        }
        .nav-box { padding: 1rem; display:flex; flex-direction:column; gap:.35rem; }
        .nav-box a { display:flex; align-items:center; gap:.75rem; padding: .8rem 1rem; border-radius: .75rem; }
        .nav-box a.active, .nav-box a:hover { background: rgba(255,255,255,.08); }
        .main { flex: 1; min-width: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e5e7eb; }
        .metric-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        @media (max-width: 991.98px) { .sidebar { width: 100%; } }
    </style>
</head>
<body>
    <div class="shell d-flex">
        <aside class="sidebar d-none d-lg-flex flex-column">
            <div class="brand">
                <div class="brand-badge">PW</div>
                <div>
                    <div class="fw-bold fs-5">ProjetWeb2A35</div>
                    <small class="text-white-50">Administration candidature</small>
                </div>
            </div>
            <nav class="nav-box">
                <a href="index.php?espace=back&module=candidature&action=liste">Candidatures</a>
                <a href="index.php?espace=front&module=candidature&action=liste">Espace candidat</a>
                <a href="index.php?espace=back&module=offreemploi&action=liste">Administration offres</a>
            </nav>
        </aside>

        <div class="main">
            <header class="topbar px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-secondary small">Module Candidature</p>
                    <h1 class="h4 mb-0">Candidature #<?= (int) $candidature['id'] ?></h1>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-outline-secondary" href="index.php?espace=back&module=candidature&action=liste">Retour a la liste</a>
                    <a class="btn btn-primary" href="index.php?espace=back&module=candidature&action=modifier&id=<?= (int) $candidature['id'] ?>">Modifier</a>
                </div>
            </header>

            <main class="container-fluid p-4 p-lg-5">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card metric-card">
                            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <h3 class="h5 mb-1"><?= htmlspecialchars((string) ($candidature['offre_titre'] ?? 'Offre inconnue')) ?></h3>
                                    <p class="mb-0 text-secondary">Candidat: <?= htmlspecialchars(trim((string) ($candidature['prenom'] ?? '') . ' ' . (string) ($candidature['nom'] ?? ''))) ?></p>
                                </div>
                                <span class="badge <?= ($candidature['statut'] === 'enattente') ? 'text-bg-warning' : (($candidature['statut'] === 'acceptée' || $candidature['statut'] === 'acceptee') ? 'text-bg-success' : 'text-bg-danger') ?>">
                                    <?= htmlspecialchars((string) $candidature['statut']) ?>
                                </span>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="row g-3">
                                    <div class="col-md-6"><strong>Nom:</strong> <?= htmlspecialchars((string) ($candidature['nom'] ?? '')) ?></div>
                                    <div class="col-md-6"><strong>Prenom:</strong> <?= htmlspecialchars((string) ($candidature['prenom'] ?? '')) ?></div>
                                    <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars((string) $candidature['email']) ?></div>
                                    <div class="col-md-6"><strong>CV:</strong> <a href="<?= htmlspecialchars((string) $candidature['cvurl']) ?>" target="_blank" rel="noopener">Ouvrir le CV</a></div>
                                    <div class="col-md-6"><strong>Date de depot:</strong> <?= htmlspecialchars((string) $candidature['datecandidature']) ?></div>
                                    <div class="col-md-6"><strong>Date de reponse:</strong> <?= htmlspecialchars((string) ($candidature['datereponse'] ?? 'En attente')) ?></div>
                                </div>

                                <div class="mt-4">
                                    <h4 class="h6">Lettre de motivation</h4>
                                    <p class="text-secondary mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars((string) $candidature['lettremotivation']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card metric-card h-100">
                            <div class="card-body d-flex flex-column gap-2">
                                <h4 class="h6 mb-3">Offre liee</h4>
                                <p class="mb-1"><strong>Titre:</strong> <?= htmlspecialchars((string) ($candidature['offre_titre'] ?? 'N/A')) ?></p>
                                <p class="mb-1"><strong>Lieu:</strong> <?= htmlspecialchars((string) ($candidature['offre_lieu'] ?? 'N/A')) ?></p>
                                <p class="mb-1"><strong>Contrat:</strong> <?= htmlspecialchars((string) ($candidature['offre_typecontrat'] ?? 'N/A')) ?></p>
                                <p class="mb-3"><strong>ID candidature:</strong> <?= (int) $candidature['id'] ?></p>
                                <a class="btn btn-outline-primary" href="index.php?espace=back&module=candidature&action=modifier&id=<?= (int) $candidature['id'] ?>">Modifier cette candidature</a>
                                <a class="btn btn-outline-danger" href="index.php?espace=back&module=candidature&action=supprimer&id=<?= (int) $candidature['id'] ?>" onclick="return confirm('Supprimer cette candidature ?');">Supprimer</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
