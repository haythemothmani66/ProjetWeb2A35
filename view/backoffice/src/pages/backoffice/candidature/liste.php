<?php
$candidatures = $candidatures ?? [];
$contextOffre = $contextOffre ?? null;
$filterState = $filterState ?? [
    'q' => '',
    'sort_by' => 'date_candidature',
    'sort_dir' => 'desc',
    'sort_fields' => ['offre_titre', 'nom', 'prenom', 'email', 'statut', 'date_candidature', 'date_reponse'],
];
$formatDateTime = static function ($value): string {
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('d/m/Y H:i');
    } catch (Throwable $exception) {
        return $value;
    }
};

$BO = '/gestion_users/view/backoffice/src';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Back Office - Candidatures | EduMatch Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="<?= $BO ?>/assets/css/theme.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
  <script src="<?= $BO ?>/assets/js/vendors/color-modes.js"></script>
  <script>
    if(localStorage.getItem('sidebarExpanded')==='false'){document.documentElement.classList.add('collapsed');document.documentElement.classList.remove('expanded');}
    else{document.documentElement.classList.remove('collapsed');document.documentElement.classList.add('expanded');}
  </script>
  <style>
    .metric-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
    .filter-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
    .filter-title { font-size: .82rem; letter-spacing: .04em; text-transform: uppercase; color: #64748b; font-weight: 700; }
    .radio-inline-wrap { display: flex; gap: 1.25rem; flex-wrap: wrap; }
    .table thead th { font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; color: #64748b; }
    .search-row-hide { display: none; }
    .action-group { display: inline-flex; gap: .5rem; flex-wrap: wrap; }
  </style>
</head>
<body>
  <div>
    <?php include __DIR__ . '/../../../partials_php/sidebar.php'; ?>
    <div id="content" class="position-relative h-100">
      <?php include __DIR__ . '/../../../partials_php/topbar.php'; ?>
      <div class="custom-container">

        <div class="row mb-6 g-6 align-items-end">
          <div class="col-lg-8">
            <p class="text-uppercase text-secondary small mb-2">Module Candidatures</p>
            <h1 class="mb-0"><?= $contextOffre ? 'Candidatures de l\'offre' : 'Gestion des candidatures' ?></h1>
            <?php if ($contextOffre): ?>
              <p class="mb-0 text-secondary small">
                <?= htmlspecialchars((string) $contextOffre['titre']) ?> · <?= htmlspecialchars((string) $contextOffre['lieu']) ?>
              </p>
            <?php endif; ?>
          </div>
          <div class="col-lg-4 text-lg-end">
            <?php if ($contextOffre): ?>
              <a class="btn btn-outline-secondary me-2" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=details&id=<?= (int) $contextOffre['id'] ?>">Retour à l'offre</a>
            <?php endif; ?>
            <a class="btn btn-outline-secondary me-2" href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste">Voir l'espace candidat</a>
            <a class="btn btn-outline-primary" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=liste">Administration offres</a>
          </div>
        </div>

                <?php if (!$contextOffre): ?>
                <form method="get" action="/gestion_users/controller/CandidatureController.php" class="mb-4" id="candidature-filter-form">
                    <input type="hidden" name="espace" value="back">
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
                                        <?php $selectedSortBy = (string) ($filterState['sort_by'] ?? 'date_candidature'); ?>
                                        <option value="offre_titre" <?= $selectedSortBy === 'offre_titre' ? 'selected' : '' ?>>offre</option>
                                        <option value="nom" <?= $selectedSortBy === 'nom' ? 'selected' : '' ?>>nom</option>
                                        <option value="prenom" <?= $selectedSortBy === 'prenom' ? 'selected' : '' ?>>prenom</option>
                                        <option value="email" <?= $selectedSortBy === 'email' ? 'selected' : '' ?>>email</option>
                                        <option value="statut" <?= $selectedSortBy === 'statut' ? 'selected' : '' ?>>statut</option>
                                        <option value="match_score" <?= $selectedSortBy === 'match_score' ? 'selected' : '' ?>>score IA</option>
                                        <option value="datecandidature" <?= $selectedSortBy === 'date_candidature' ? 'selected' : '' ?>>date de depot</option>
                                        <option value="datereponse" <?= $selectedSortBy === 'date_reponse' ? 'selected' : '' ?>>date de reponse</option>
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
                        <a class="btn btn-outline-secondary" href="/gestion_users/controller/CandidatureController.php?espace=back&action=liste">Reinitialiser</a>
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
                                        <th>Score IA</th>
                                        <th>Date de depot</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="candidatures-table-body">
                                    <?php if (empty($candidatures)): ?>
                                        <tr id="server-empty-row">
                                            <td colspan="7" class="text-center text-secondary py-4">Aucune candidature pour le moment.</td>
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
                                                    <span class="badge <?= ($candidature['statut'] === 'en_attente') ? 'text-bg-warning' : (($candidature['statut'] === 'acceptée' || $candidature['statut'] === 'acceptee') ? 'text-bg-success' : 'text-bg-danger') ?>">
                                                        <?= htmlspecialchars((string) $candidature['statut']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php $matchScore = isset($candidature['match_score']) && $candidature['match_score'] !== null && $candidature['match_score'] !== '' ? (float) $candidature['match_score'] : null; ?>
                                                    <?php if ($matchScore !== null): ?>
                                                        <span class="badge <?= $matchScore >= 80 ? 'text-bg-success' : ($matchScore >= 60 ? 'text-bg-warning' : 'text-bg-secondary') ?>">
                                                            <?= number_format($matchScore, 0) ?>/100
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-secondary">En attente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($formatDateTime((string) $candidature['date_candidature'])) ?></td>
                                                <td>
                                                    <div class="action-group">
                                                    <a class="btn btn-sm btn-outline-primary" href="/gestion_users/controller/CandidatureController.php?espace=back&action=details&id=<?= (int) $candidature['id'] ?>">Details</a>
                                                    <a class="btn btn-sm btn-outline-danger" href="/gestion_users/controller/CandidatureController.php?espace=back&action=supprimer&id=<?= (int) $candidature['id'] ?>" onclick="return confirm('Supprimer cette candidature ?');">Supprimer</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr id="client-no-result-row" class="search-row-hide">
                                            <td colspan="7" class="text-center text-secondary py-4">Aucun resultat pour cette recherche.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
  <script src="<?= $BO ?>/assets/js/main.js"></script>
  <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>
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
                            .slice(0, 6)
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
