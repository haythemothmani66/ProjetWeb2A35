<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Back Office - Modifier offre</title>
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
        .emploi-brand { display: flex; align-items: center; gap: .75rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,.08); }
        .emploi-brand img { width: 36px; height: 36px; }
        .emploi-nav { padding: 1rem; display: flex; flex-direction: column; gap: .35rem; }
        .emploi-nav a { display: flex; align-items: center; gap: .75rem; padding: .8rem 1rem; border-radius: .75rem; }
        .emploi-nav a.active, .emploi-nav a:hover { background: rgba(255,255,255,.08); }
        .emploi-main { flex: 1; min-width: 0; }
        .emploi-topbar { background: #fff; border-bottom: 1px solid #e5e7eb; }
        .metric-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        @media (max-width: 991.98px) { .emploi-sidebar { width: 100%; } }
    </style>
</head>
<body>
    <div class="emploi-shell d-flex">
        <aside class="emploi-sidebar d-none d-lg-flex flex-column">
            <div class="emploi-brand">
                <img src="../dasher-1.0.0/src/assets/images/brand/logo/logo-icon.svg" alt="Dasher" />
                <div>
                    <div class="fw-bold fs-5">Dasher</div>
                    <small class="text-white-50">Offres Emploi Admin</small>
                </div>
            </div>
            <nav class="emploi-nav">
                <a href="index.php?espace=back&module=offreemploi&action=liste">Lister les offres</a>
                <a class="active" href="index.php?espace=back&module=offreemploi&action=modifier&id=<?= (int) $offre['id'] ?>">Modifier l'offre</a>
                <a href="index.php?espace=front&module=offreemploi&action=liste">Front Office</a>
            </nav>
        </aside>

        <div class="emploi-main">
            <header class="emploi-topbar px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-secondary small">Module Offres d'emploi</p>
                    <h1 class="h4 mb-0">Modifier l'offre #<?= (int) $offre['id'] ?></h1>
                </div>
                <a class="btn btn-outline-secondary" href="index.php?espace=back&module=offreemploi&action=details&id=<?= (int) $offre['id'] ?>">Retour details</a>
            </header>

            <main class="container-fluid p-4 p-lg-5">
                <div class="card metric-card">
                    <div class="card-body p-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="post" class="row g-3">
                            <div class="col-md-6">
                                <label for="titre" class="form-label">Titre *</label>
                                <input type="text" id="titre" name="titre" class="form-control" required value="<?= htmlspecialchars((string) $offre['titre']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="lieu" class="form-label">Lieu *</label>
                                <input type="text" id="lieu" name="lieu" class="form-control" required value="<?= htmlspecialchars((string) $offre['lieu']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="typecontrat" class="form-label">Type contrat *</label>
                                <input type="text" id="typecontrat" name="typecontrat" class="form-control" required value="<?= htmlspecialchars((string) $offre['typecontrat']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="datelimite" class="form-label">Date limite *</label>
                                <input type="date" id="datelimite" name="datelimite" class="form-control" required value="<?= htmlspecialchars((string) $offre['datelimite']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="salairemin" class="form-label">Salaire min</label>
                                <input type="number" step="0.01" id="salairemin" name="salairemin" class="form-control" value="<?= htmlspecialchars((string) ($offre['salairemin'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="salairemax" class="form-label">Salaire max</label>
                                <input type="number" step="0.01" id="salairemax" name="salairemax" class="form-control" value="<?= htmlspecialchars((string) ($offre['salairemax'] ?? '')) ?>">
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Description *</label>
                                <textarea id="description" name="description" rows="5" class="form-control" required><?= htmlspecialchars((string) $offre['description']) ?></textarea>
                            </div>

                            <div class="col-12">
                                <label for="competencesrequises" class="form-label">Competences requises</label>
                                <textarea id="competencesrequises" name="competencesrequises" rows="4" class="form-control"><?= htmlspecialchars((string) ($offre['competencesrequises'] ?? '')) ?></textarea>
                            </div>

                            <div class="col-md-4">
                                <label for="statut" class="form-label">Statut</label>
                                <select id="statut" name="statut" class="form-select">
                                    <option value="ouverte" <?= ($offre['statut'] === 'ouverte') ? 'selected' : '' ?>>ouverte</option>
                                    <option value="fermee" <?= ($offre['statut'] === 'fermee') ? 'selected' : '' ?>>fermee</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Mettre a jour l'offre</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../dasher-1.0.0/src/assets/js/main.js"></script>
</body>
</html>
