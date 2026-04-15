<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Administration - Candidatures</title>
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
        .table thead th { font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; color: #64748b; }
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
                <a class="active" href="index.php?espace=back&module=candidature&action=liste">Candidatures</a>
                <a href="index.php?espace=front&module=candidature&action=liste">Espace candidat</a>
                <a href="index.php?espace=back&module=offreemploi&action=liste">Administration offres</a>
            </nav>
        </aside>

        <div class="main">
            <header class="topbar px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-secondary small">Module Candidature</p>
                    <h1 class="h4 mb-0">Gestion des candidatures</h1>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-outline-secondary" href="index.php?espace=front&module=candidature&action=liste">Voir l'espace candidat</a>
                    <a class="btn btn-outline-primary" href="index.php?espace=back&module=offreemploi&action=liste">Administration offres</a>
                </div>
            </header>

            <main class="container-fluid p-4 p-lg-5">
                <div class="card metric-card">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0">Liste des candidatures</h3>
                        <span class="badge text-bg-primary"><?= count($candidatures) ?> candidature(s)</span>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Offre</th>
                                        <th>Candidat</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Date de depot</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($candidatures)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-secondary py-4">Aucune candidature pour le moment.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($candidatures as $candidature): ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string) $candidature['id']) ?></td>
                                                <td><?= htmlspecialchars((string) ($candidature['offre_titre'] ?? 'Offre inconnue')) ?></td>
                                                <td>
                                                    <?= htmlspecialchars(trim((string) ($candidature['prenom'] ?? '') . ' ' . (string) ($candidature['nom'] ?? ''))) ?>
                                                </td>
                                                <td><?= htmlspecialchars((string) $candidature['email']) ?></td>
                                                <td>
                                                    <span class="badge <?= ($candidature['statut'] === 'enattente') ? 'text-bg-warning' : (($candidature['statut'] === 'acceptée' || $candidature['statut'] === 'acceptee') ? 'text-bg-success' : 'text-bg-danger') ?>">
                                                        <?= htmlspecialchars((string) $candidature['statut']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars((string) $candidature['datecandidature']) ?></td>
                                                <td class="d-flex gap-2 flex-wrap">
                                                    <a class="btn btn-sm btn-outline-primary" href="index.php?espace=back&module=candidature&action=details&id=<?= (int) $candidature['id'] ?>">Details</a>
                                                    <a class="btn btn-sm btn-outline-secondary" href="index.php?espace=back&module=candidature&action=modifier&id=<?= (int) $candidature['id'] ?>">Modifier</a>
                                                    <a class="btn btn-sm btn-outline-danger" href="index.php?espace=back&module=candidature&action=supprimer&id=<?= (int) $candidature['id'] ?>" onclick="return confirm('Supprimer cette candidature ?');">Supprimer</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
