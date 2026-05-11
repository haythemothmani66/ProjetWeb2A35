<?php
$offres = $offres ?? [];
$filterState = $filterState ?? [
    'q' => '',
    'sort_by' => 'date_creation',
    'sort_dir' => 'desc',
    'sort_fields' => ['titre', 'lieu', 'type_contrat', 'salaire_min', 'salaire_max', 'date_creation', 'date_limite', 'statut'],
];
$formatDateTime = static function ($value): string {
    $value = trim((string) $value);
    if ($value === '') { return ''; }
    try { return (new DateTimeImmutable($value))->format('d/m/Y H:i'); }
    catch (Throwable $e) { return $value; }
};

$BO = '/gestion_users/view/backoffice/src';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Back Office - Offres d'emploi | EduMatch Admin</title>
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
    .filter-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
    .filter-title { font-size: .82rem; letter-spacing: .04em; text-transform: uppercase; color: #64748b; font-weight: 700; }
    .radio-inline-wrap { display: flex; gap: 1.25rem; flex-wrap: wrap; }
    .table thead th { font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; color: #64748b; }
    .search-row-hide { display: none; }
    .flash-success { opacity: 1; visibility: visible; transform: translateY(0); transition: opacity .45s ease, transform .45s ease, visibility 0s linear .45s; }
    .flash-success.hide { opacity: 0; transform: translateY(-6px); visibility: hidden; pointer-events: none; }
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
            <p class="text-uppercase text-secondary small mb-2">Module Offres d'emploi</p>
            <h1 class="mb-0">Gestion des offres</h1>
          </div>
          <div class="col-lg-4 text-lg-end">
            <a class="btn btn-outline-secondary me-2" href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste">
              <i class="ti ti-external-link me-1"></i> Voir le front office
            </a>
            <a class="btn btn-primary" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=ajouter">
              <i class="ti ti-plus me-1"></i> Nouvelle offre
            </a>
          </div>
        </div>

        <?php if (($_GET['success'] ?? '') === 'modifiee'): ?>
          <div id="flash-success" class="alert alert-success flash-success">L'offre a ete modifiee avec succes.</div>
        <?php elseif (($_GET['success'] ?? '') === 'ajoutee'): ?>
          <div id="flash-success" class="alert alert-success flash-success">L'offre a ete ajoutee avec succes.</div>
        <?php elseif (($_GET['success'] ?? '') === 'supprimee'): ?>
          <div id="flash-success" class="alert alert-success flash-success">L'offre a ete supprimee avec succes.</div>
        <?php endif; ?>

        <form method="get" action="/gestion_users/controller/OffreEmploiController.php" class="mb-4" id="offre-filter-form">
          <input type="hidden" name="espace" value="back">
          <input type="hidden" name="action" value="liste">

          <div class="row g-3">
            <div class="col-lg-6">
              <div class="card filter-card h-100">
                <div class="card-body p-4">
                  <div class="filter-title mb-2">Recherche globale</div>
                  <label class="form-label" for="q">Texte a rechercher dans le tableau</label>
                  <input type="text" id="q" name="q" class="form-control"
                    value="<?= htmlspecialchars((string) ($filterState['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Titre, lieu, contrat, statut, salaire, dates..." autocomplete="off">
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="card filter-card h-100">
                <div class="card-body p-4">
                  <div class="filter-title mb-2">Zone de tri</div>
                  <label class="form-label" for="sort_by">Trier par</label>
                  <select id="sort_by" name="sort_by" class="form-select mb-3">
                    <?php $selectedSortBy = (string) ($filterState['sort_by'] ?? 'date_creation'); ?>
                    <option value="titre" <?= $selectedSortBy === 'titre' ? 'selected' : '' ?>>Titre</option>
                    <option value="lieu" <?= $selectedSortBy === 'lieu' ? 'selected' : '' ?>>Lieu</option>
                    <option value="type_contrat" <?= $selectedSortBy === 'type_contrat' ? 'selected' : '' ?>>Type de contrat</option>
                    <option value="salaire_min" <?= $selectedSortBy === 'salaire_min' ? 'selected' : '' ?>>Salaire min</option>
                    <option value="salaire_max" <?= $selectedSortBy === 'salaire_max' ? 'selected' : '' ?>>Salaire max</option>
                    <option value="date_creation" <?= $selectedSortBy === 'date_creation' ? 'selected' : '' ?>>Date de creation</option>
                    <option value="date_limite" <?= $selectedSortBy === 'date_limite' ? 'selected' : '' ?>>Date limite</option>
                    <option value="statut" <?= $selectedSortBy === 'statut' ? 'selected' : '' ?>>Statut</option>
                  </select>

                  <?php $selectedSortDir = (string) ($filterState['sort_dir'] ?? 'desc'); ?>
                  <div class="radio-inline-wrap">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="sort_dir" id="sort_dir_asc" value="asc" <?= $selectedSortDir === 'asc' ? 'checked' : '' ?>>
                      <label class="form-check-label" for="sort_dir_asc">Croissant</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="sort_dir" id="sort_dir_desc" value="desc" <?= $selectedSortDir !== 'asc' ? 'checked' : '' ?>>
                      <label class="form-check-label" for="sort_dir_desc">Decroissant</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2 mt-3 flex-wrap">
            <button type="submit" class="btn btn-primary">Appliquer</button>
            <a class="btn btn-outline-secondary" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=liste">Reinitialiser</a>
          </div>
        </form>

        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0">Liste des offres d'emploi</h3>
            <span id="offre-count-badge" class="badge text-bg-primary"><?= count($offres) ?> offre(s)</span>
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
                        <td><?= htmlspecialchars((string) $offre['type_contrat']) ?></td>
                        <td><?= htmlspecialchars($formatDateTime($offre['date_limite'])) ?></td>
                        <td>
                          <span class="badge <?= ($offre['statut'] === 'ouverte') ? 'text-bg-success' : 'text-bg-danger' ?>">
                            <?= htmlspecialchars((string) $offre['statut']) ?>
                          </span>
                        </td>
                        <td>
                          <div class="d-flex gap-1 flex-wrap">
                            <a class="btn btn-sm btn-outline-warning" href="/gestion_users/controller/CandidatureController.php?espace=back&action=parOffre&id=<?= (int) $offre['id'] ?>"><i class="ti ti-file-text"></i> Candidatures</a>
                            <a class="btn btn-sm btn-outline-primary" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=details&id=<?= (int) $offre['id'] ?>"><i class="ti ti-eye"></i> Details</a>
                            <a class="btn btn-sm btn-outline-secondary" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=modifier&id=<?= (int) $offre['id'] ?>"><i class="ti ti-edit"></i> Modifier</a>
                            <a class="btn btn-sm btn-outline-danger" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=supprimer&id=<?= (int) $offre['id'] ?>" onclick="return confirm('Supprimer cette offre ?');"><i class="ti ti-trash"></i> Supprimer</a>
                          </div>
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

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
  <script src="<?= $BO ?>/assets/js/main.js"></script>
  <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>
  <script>
    (function () {
      const flash = document.getElementById('flash-success');
      if (!flash) return;
      window.setTimeout(function () { flash.classList.add('hide'); }, 4500);
    })();

    (function () {
      const form = document.getElementById('offre-filter-form');
      const searchInput = document.getElementById('q');
      const rows = Array.from(document.querySelectorAll('#offres-table-body .offre-row'));
      const countBadge = document.getElementById('offre-count-badge');
      const noResultRow = document.getElementById('client-no-result-row');
      if (!form || !searchInput || rows.length === 0) return;

      function normalize(value) {
        return value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      }

      function updateCount(visibleCount) {
        if (countBadge) countBadge.textContent = visibleCount + ' offre(s)';
      }

      function filterRows() {
        const query = normalize(searchInput.value.trim());
        let visibleCount = 0;
        rows.forEach(function (row) {
          const searchableText = normalize(
            Array.from(row.querySelectorAll('td')).slice(0, 5)
              .map(function (cell) { return cell.textContent || ''; }).join(' ')
          );
          const isMatch = query === '' || searchableText.includes(query);
          row.classList.toggle('search-row-hide', !isMatch);
          if (isMatch) visibleCount += 1;
        });
        if (noResultRow) noResultRow.classList.toggle('search-row-hide', visibleCount !== 0);
        updateCount(visibleCount);
      }

      searchInput.addEventListener('input', filterRows);
      searchInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
      filterRows();
    })();
  </script>
</body>
</html>
