<?php
$offre = $offre ?? [];
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
  <title>Back Office - Details offre | EduMatch Admin</title>
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
            <h1 class="mb-0">Details de l'offre</h1>
          </div>
          <div class="col-lg-4 text-lg-end">
            <a class="btn btn-outline-secondary me-2" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=liste">Retour liste</a>
            <a class="btn btn-primary" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=modifier&id=<?= (int) $offre['id'] ?>">Modifier</a>
          </div>
        </div>

        <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card metric-card">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h3 class="h5 mb-0"><?= htmlspecialchars((string) $offre['titre']) ?></h3>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <p class="text-secondary mb-3"><?= nl2br(htmlspecialchars((string) $offre['description'])) ?></p>
                                <h4 class="h6">Competences requises</h4>
                                <p class="mb-0"><?= nl2br(htmlspecialchars((string) ($offre['competences_requises'] ?? 'Non specifiees'))) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card metric-card h-100">
                            <div class="card-body d-flex flex-column gap-2">
                                <p class="mb-1"><strong>Lieu:</strong> <?= htmlspecialchars((string) $offre['lieu']) ?></p>
                                <p class="mb-1"><strong>Type contrat:</strong> <?= htmlspecialchars((string) $offre['type_contrat']) ?></p>
                                <p class="mb-1"><strong>Salaire min:</strong> <?= htmlspecialchars((string) ($offre['salaire_min'] ?? '-')) ?></p>
                                <p class="mb-1"><strong>Salaire max:</strong> <?= htmlspecialchars((string) ($offre['salaire_max'] ?? '-')) ?></p>
                                <p class="mb-1"><strong>Date creation:</strong> <?= htmlspecialchars((string) $offre['date_creation']) ?></p>
                                <p class="mb-1"><strong>Date limite:</strong> <?= htmlspecialchars($formatDateTime($offre['date_limite'])) ?></p>
                                <p class="mb-3"><strong>Statut:</strong> <span class="badge <?= ($offre['statut'] === 'ouverte') ? 'text-bg-success' : 'text-bg-danger' ?>"><?= htmlspecialchars((string) $offre['statut']) ?></span></p>
                                <a class="btn btn-outline-danger" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=supprimer&id=<?= (int) $offre['id'] ?>" onclick="return confirm('Supprimer cette offre ?');">Supprimer</a>
                            </div>
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
</body>
</html>
