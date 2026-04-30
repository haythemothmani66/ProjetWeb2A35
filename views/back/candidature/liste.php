<?php
$candidatures = $candidatures ?? [];
$contextOffre = $contextOffre ?? null;
$filterState = $filterState ?? [
    'q' => '',
    'sort_by' => 'datecandidature',
    'sort_dir' => 'desc',
    'sort_fields' => ['offre_titre', 'nom', 'prenom', 'email', 'statut', 'datecandidature', 'datereponse'],
];
?>
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
        .main { flex: 1; min-width: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e5e7eb; }
        .metric-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .filter-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .filter-title { font-size: .82rem; letter-spacing: .04em; text-transform: uppercase; color: #64748b; font-weight: 700; }
        .radio-inline-wrap { display: flex; gap: 1.25rem; flex-wrap: wrap; }
        .table thead th { font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; color: #64748b; }
        .search-row-hide { display: none; }
        @media (max-width: 991.98px) { .sidebar { width: 100%; } }
    </style>
</head>
<body>
    <div class="shell d-flex">
        <aside class="sidebar d-none d-lg-flex flex-column">
            <?php include __DIR__ . '/../partials/brand.php'; ?>
            <?php $activeTab = 'candidatures'; include __DIR__ . '/../partials/nav.php'; ?>
        </aside>

        <div class="main">
            <header class="topbar px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-secondary small">Module Candidature</p>
                    <h1 class="h4 mb-0"><?= $contextOffre ? 'Candidatures de l\'offre' : 'Gestion des candidatures' ?></h1>
                    <?php if ($contextOffre): ?>
                        <p class="mb-0 text-secondary small">
                            <?= htmlspecialchars((string) $contextOffre['titre']) ?> · <?= htmlspecialchars((string) $contextOffre['lieu']) ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($contextOffre): ?>
                        <a class="btn btn-outline-secondary" href="index.php?espace=back&module=offreemploi&action=details&id=<?= (int) $contextOffre['id'] ?>">Retour à l'offre</a>
                    <?php endif; ?>
                    <a class="btn btn-outline-secondary" href="index.php?espace=front&module=candidature&action=liste">Voir l'espace candidat</a>
                    <a class="btn btn-outline-primary" href="index.php?espace=back&module=offreemploi&action=liste">Administration offres</a>
                </div>
            </header>

            <main class="container-fluid p-4 p-lg-5">
                <?php if (!$contextOffre): ?>
                <form method="get" action="index.php" class="mb-4" id="candidature-filter-form">
                    <input type="hidden" name="espace" value="back">
                    <input type="hidden" name="module" value="candidature">
                    <input type="hidden" name="action" value="liste">

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="card filter-card h-100">
                                <div class="card-body p-4">
                                    <div class="filter-title mb-2">Recherche globale</div>
                                    <label class="form-label" for="q">Texte à rechercher dans le tableau</label>
                                    <input
                                        type="text"
                                        id="q"
                                        name="q"
                                        class="form-control"
                                        value="<?= htmlspecialchars((string) ($filterState['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Offre, candidat, email, statut, dates..."
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
                                        <?php $selectedSortBy = (string) ($filterState['sort_by'] ?? 'datecandidature'); ?>
                                        <option value="offre_titre" <?= $selectedSortBy === 'offre_titre' ? 'selected' : '' ?>>offre</option>
                                        <option value="nom" <?= $selectedSortBy === 'nom' ? 'selected' : '' ?>>nom</option>
                                        <option value="prenom" <?= $selectedSortBy === 'prenom' ? 'selected' : '' ?>>prenom</option>
                                        <option value="email" <?= $selectedSortBy === 'email' ? 'selected' : '' ?>>email</option>
                                        <option value="statut" <?= $selectedSortBy === 'statut' ? 'selected' : '' ?>>statut</option>
                                        <option value="datecandidature" <?= $selectedSortBy === 'datecandidature' ? 'selected' : '' ?>>date de depot</option>
                                        <option value="datereponse" <?= $selectedSortBy === 'datereponse' ? 'selected' : '' ?>>date de reponse</option>
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
                        <a class="btn btn-outline-secondary" href="index.php?espace=back&module=candidature&action=liste">Reinitialiser</a>
                    </div>
                </form>
                <?php endif; ?>

                <div class="card metric-card">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0">Liste des candidatures</h3>
                        <span id="candidature-count-badge" class="badge text-bg-primary"><?= count($candidatures) ?> candidature(s)</span>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table align-middle" id="candidatures-table">
                                <thead>
                                    <tr>
                                        <th>Offre</th>
                                        <th>Candidat</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Date de depot</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="candidatures-table-body">
                                    <?php if (empty($candidatures)): ?>
                                        <tr id="server-empty-row">
                                            <td colspan="6" class="text-center text-secondary py-4">Aucune candidature pour le moment.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($candidatures as $candidature): ?>
                                            <tr class="candidature-row">
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
    <script>
        (function () {
            const form = document.getElementById('candidature-filter-form');
            const searchInput = document.getElementById('q');
            const rows = Array.from(document.querySelectorAll('#candidatures-table-body .candidature-row'));
            const countBadge = document.getElementById('candidature-count-badge');
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
                countBadge.textContent = visibleCount + ' candidature(s)';
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
