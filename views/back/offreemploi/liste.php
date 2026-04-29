<?php
$offres = $offres ?? [];
$filterState = $filterState ?? [
    'q' => '',
    'sort_by' => 'datecreation',
    'sort_dir' => 'desc',
    'sort_fields' => ['titre', 'lieu', 'typecontrat', 'salairemin', 'salairemax', 'datecreation', 'datelimite', 'statut'],
];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Back Office - Offres d'emploi</title>
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
        .filter-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .filter-title { font-size: .82rem; letter-spacing: .04em; text-transform: uppercase; color: #64748b; font-weight: 700; }
        .radio-inline-wrap { display: flex; gap: 1.25rem; flex-wrap: wrap; }
        .table thead th { font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; color: #64748b; }
        .search-row-hide { display: none; }
        .flash-success {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition: opacity .45s ease, transform .45s ease, visibility 0s linear .45s;
        }
        .flash-success.hide {
            opacity: 0;
            transform: translateY(-6px);
            visibility: hidden;
            pointer-events: none;
        }
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
                    <h1 class="h4 mb-0">Gestion des offres</h1>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="index.php?espace=front&module=offreemploi&action=liste">Voir le front office</a>
                    <a class="btn btn-primary" href="index.php?espace=back&module=offreemploi&action=ajouter">Nouvelle offre</a>
                </div>
            </header>

            <main class="container-fluid p-4 p-lg-5">
                <?php if (($_GET['success'] ?? '') === 'modifiee'): ?>
                    <div id="flash-success" class="alert alert-success flash-success">L'offre a ete modifiee avec succes.</div>
                <?php elseif (($_GET['success'] ?? '') === 'ajoutee'): ?>
                    <div id="flash-success" class="alert alert-success flash-success">L'offre a ete ajoutee avec succes.</div>
                <?php endif; ?>

                <form method="get" action="index.php" class="mb-4" id="offre-filter-form">
                    <input type="hidden" name="espace" value="back">
                    <input type="hidden" name="module" value="offreemploi">
                    <input type="hidden" name="action" value="liste">

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="card filter-card h-100">
                                <div class="card-body p-4">
                                    <div class="filter-title mb-2">Recherche globale</div>
                                    <label class="form-label" for="q">Texte a rechercher dans le tableau</label>
                                    <input
                                        type="text"
                                        id="q"
                                        name="q"
                                        class="form-control"
                                        value="<?= htmlspecialchars((string) ($filterState['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Titre, lieu, contrat, statut, salaire, dates..."
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card filter-card h-100">
                                <div class="card-body p-4">
                                    <div class="filter-title mb-2">Zone de tri</div>
                                    <label class="form-label" for="sort_by">Trier par</label>
                                    <select id="sort_by" name="sort_by" class="form-select mb-3">
                                        <?php $selectedSortBy = (string) ($filterState['sort_by'] ?? 'datecreation'); ?>
                                        <option value="titre" <?= $selectedSortBy === 'titre' ? 'selected' : '' ?>>titre</option>
                                        <option value="lieu" <?= $selectedSortBy === 'lieu' ? 'selected' : '' ?>>lieu</option>
                                        <option value="typecontrat" <?= $selectedSortBy === 'typecontrat' ? 'selected' : '' ?>>typecontrat</option>
                                        <option value="salairemin" <?= $selectedSortBy === 'salairemin' ? 'selected' : '' ?>>salairemin</option>
                                        <option value="salairemax" <?= $selectedSortBy === 'salairemax' ? 'selected' : '' ?>>salairemax</option>
                                        <option value="datecreation" <?= $selectedSortBy === 'datecreation' ? 'selected' : '' ?>>datecreation</option>
                                        <option value="datelimite" <?= $selectedSortBy === 'datelimite' ? 'selected' : '' ?>>datelimite</option>
                                        <option value="statut" <?= $selectedSortBy === 'statut' ? 'selected' : '' ?>>statut</option>
                                    </select>

                                    <?php $selectedSortDir = (string) ($filterState['sort_dir'] ?? 'desc'); ?>
                                    <div class="radio-inline-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="sort_dir" id="sort_dir_asc" value="asc" <?= $selectedSortDir === 'asc' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="sort_dir_asc">ascending</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="sort_dir" id="sort_dir_desc" value="desc" <?= $selectedSortDir !== 'asc' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="sort_dir_desc">descending</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button type="submit" class="btn btn-primary">Appliquer</button>
                        <a class="btn btn-outline-secondary" href="index.php?espace=back&module=offreemploi&action=liste">Reinitialiser</a>
                    </div>
                </form>

                <div class="card metric-card">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0">Liste des offres d'emploi</h3>
                        <span id="offre-count-badge" class="badge text-bg-primary\"><?= count($offres) ?> offre(s)</span>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table align-middle" id="offres-table">
                                <thead>
                                    <tr>
                                        <th>Titre</th>
                                        <th>Lieu</th>
                                        <th>Type contrat</th>
                                        <th>Date limite</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="offres-table-body">
                                    <?php if (empty($offres)): ?>
                                        <tr id="server-empty-row">
                                            <td colspan="6" class="text-center text-secondary py-4">Aucune offre disponible.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($offres as $offre): ?>
                                            <tr class="offre-row">
                                                <td><?= htmlspecialchars((string) $offre['titre']) ?></td>
                                                <td><?= htmlspecialchars((string) $offre['lieu']) ?></td>
                                                <td><?= htmlspecialchars((string) $offre['typecontrat']) ?></td>
                                                <td><?= htmlspecialchars((string) $offre['datelimite']) ?></td>
                                                <td>
                                                    <span class="badge <?= ($offre['statut'] === 'ouverte') ? 'text-bg-success' : 'text-bg-danger' ?>">
                                                        <?= htmlspecialchars((string) $offre['statut']) ?>
                                                    </span>
                                                </td>
                                                <td class="d-flex gap-2">
                                                    <a class="btn btn-sm btn-outline-primary" href="index.php?espace=back&module=offreemploi&action=details&id=<?= (int) $offre['id'] ?>">Details</a>
                                                    <a class="btn btn-sm btn-outline-secondary" href="index.php?espace=back&module=offreemploi&action=modifier&id=<?= (int) $offre['id'] ?>">Modifier</a>
                                                    <a class="btn btn-sm btn-outline-danger" href="index.php?espace=back&module=offreemploi&action=supprimer&id=<?= (int) $offre['id'] ?>" onclick="return confirm('Supprimer cette offre ?');">Supprimer</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr id="client-no-result-row" class="search-row-hide">
                                            <td colspan="6" class="text-center text-secondary py-4">Aucun resultat pour cette recherche.</td>
                                        </tr>
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
    <script src="../dasher-1.0.0/src/assets/js/main.js"></script>
    <script>
        (function () {
            const flash = document.getElementById('flash-success');
            if (!flash) return;

            window.setTimeout(function () {
                flash.classList.add('hide');
            }, 4500);
        })();

        (function () {
            const form = document.getElementById('offre-filter-form');
            const searchInput = document.getElementById('q');
            const rows = Array.from(document.querySelectorAll('#offres-table-body .offre-row'));
            const countBadge = document.getElementById('offre-count-badge');
            const noResultRow = document.getElementById('client-no-result-row');
            if (!form || !searchInput || rows.length === 0) return;

            function normalize(value) {
                return value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            }

            function updateCount(visibleCount) {
                if (!countBadge) return;
                countBadge.textContent = visibleCount + ' offre(s)';
            }

            function filterRows() {
                const query = normalize(searchInput.value.trim());
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const searchableText = normalize(
                        Array.from(row.querySelectorAll('td'))
                            .slice(0, 5)
                            .map(function (cell) { return cell.textContent || ''; })
                            .join(' ')
                    );

                    const isMatch = query === '' || searchableText.includes(query);
                    row.classList.toggle('search-row-hide', !isMatch);
                    if (isMatch) {
                        visibleCount += 1;
                    }
                });

                if (noResultRow) {
                    noResultRow.classList.toggle('search-row-hide', visibleCount !== 0);
                }

                updateCount(visibleCount);
            }

            searchInput.addEventListener('input', filterRows);
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });

            filterRows();
        })();
    </script>
</body>
</html>
