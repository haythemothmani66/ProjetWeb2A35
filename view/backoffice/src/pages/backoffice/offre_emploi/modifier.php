<?php
$offre = $offre ?? [];
$fieldErrors = $fieldErrors ?? [];
$error = $error ?? '';

$BO = '/gestion_users/view/backoffice/src';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Back Office - Modifier offre | EduMatch Admin</title>
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
    .field-error { border: 2px solid #dc3545 !important; background-color: #fff5f5 !important; }
    .field-error-text { color: #dc3545; font-size: .875rem; margin-top: .35rem; display: none; }
    .field-error-text.visible { display: block; }
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
            <h1 class="mb-0">Modifier l'offre</h1>
          </div>
          <div class="col-lg-4 text-lg-end">
            <a class="btn btn-outline-secondary" href="/gestion_users/controller/OffreEmploiController.php?espace=back&action=liste">Retour liste</a>
          </div>
        </div>

        <div class="card metric-card">
                    <div class="card-body p-4">
                        <?php
                        $fieldErrors = $fieldErrors ?? [];
                        $errorFor = static function (string $key) use ($fieldErrors): string {
                            return htmlspecialchars((string) ($fieldErrors[$key][0] ?? ''), ENT_QUOTES, 'UTF-8');
                        };
                        ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="post" class="row g-3" novalidate id="offre-form">
                            <div class="col-md-6">
                                <label for="titre" class="form-label">Titre *</label>
                                <input type="text" id="titre" name="titre" class="form-control<?= !empty($fieldErrors['titre']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) $offre['titre']) ?>">
                                <div class="field-error-text<?= !empty($fieldErrors['titre']) ? ' visible' : '' ?>" data-error-for="titre"><?= $errorFor('titre') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label for="lieu" class="form-label">Lieu *</label>
                                <input type="text" id="lieu" name="lieu" class="form-control<?= !empty($fieldErrors['lieu']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) $offre['lieu']) ?>">
                                <div class="field-error-text<?= !empty($fieldErrors['lieu']) ? ' visible' : '' ?>" data-error-for="lieu"><?= $errorFor('lieu') ?></div>
                            </div>

                            <div class="col-md-6">
                                <label for="type_contrat" class="form-label">Type contrat *</label>
                                <input type="text" id="type_contrat" name="type_contrat" class="form-control<?= !empty($fieldErrors['type_contrat']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) $offre['type_contrat']) ?>">
                                <div class="field-error-text<?= !empty($fieldErrors['type_contrat']) ? ' visible' : '' ?>" data-error-for="type_contrat"><?= $errorFor('type_contrat') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label for="date_limite" class="form-label">Date limite *</label>
                                <?php
                                  $rawDate = (string) ($offre['date_limite'] ?? '');
                                  $dateValue = '';
                                  if ($rawDate !== '') {
                                      $ts = strtotime($rawDate);
                                      if ($ts !== false) { $dateValue = date('Y-m-d\TH:i', $ts); }
                                  }
                                ?>
                                <input type="datetime-local" id="date_limite" name="date_limite" class="form-control<?= !empty($fieldErrors['date_limite']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars($dateValue) ?>">
                                <div class="field-error-text<?= !empty($fieldErrors['date_limite']) ? ' visible' : '' ?>" data-error-for="date_limite"><?= $errorFor('date_limite') ?></div>
                            </div>

                            <div class="col-md-6">
                                <label for="salaire_min" class="form-label">Salaire min</label>
                                <input type="number" step="0.01" id="salaire_min" name="salaire_min" class="form-control<?= !empty($fieldErrors['salaire_min']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) ($offre['salaire_min'] ?? '')) ?>">
                                <div class="field-error-text<?= !empty($fieldErrors['salaire_min']) ? ' visible' : '' ?>" data-error-for="salaire_min"><?= $errorFor('salaire_min') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label for="salaire_max" class="form-label">Salaire max</label>
                                <input type="number" step="0.01" id="salaire_max" name="salaire_max" class="form-control<?= !empty($fieldErrors['salaire_max']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) ($offre['salaire_max'] ?? '')) ?>">
                                <div class="field-error-text<?= !empty($fieldErrors['salaire_max']) ? ' visible' : '' ?>" data-error-for="salaire_max"><?= $errorFor('salaire_max') ?></div>
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Description *</label>
                                <textarea id="description" name="description" rows="5" class="form-control<?= !empty($fieldErrors['description']) ? ' field-error' : '' ?>"><?= htmlspecialchars((string) $offre['description']) ?></textarea>
                                <div class="field-error-text<?= !empty($fieldErrors['description']) ? ' visible' : '' ?>" data-error-for="description"><?= $errorFor('description') ?></div>
                            </div>

                            <div class="col-12">
                                <label for="competences_requises" class="form-label">Competences requises</label>
                                <textarea id="competences_requises" name="competences_requises" rows="4" class="form-control"><?= htmlspecialchars((string) ($offre['competences_requises'] ?? '')) ?></textarea>
                            </div>

                            <div class="col-md-4">
                                <label for="statut" class="form-label">Statut</label>
                                <select id="statut" name="statut" class="form-select<?= !empty($fieldErrors['statut']) ? ' field-error' : '' ?>">
                                    <option value="ouverte" <?= ($offre['statut'] === 'ouverte') ? 'selected' : '' ?>>ouverte</option>
                                    <option value="fermee" <?= ($offre['statut'] === 'fermee') ? 'selected' : '' ?>>fermee</option>
                                </select>
                                <div class="field-error-text<?= !empty($fieldErrors['statut']) ? ' visible' : '' ?>" data-error-for="statut"><?= $errorFor('statut') ?></div>
                            </div>

                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Mettre a jour l'offre</button>
                            </div>
                        </form>
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
            const form = document.getElementById('offre-form');
            if (!form) return;

            const requiredFields = {
                titre: 'Le titre est obligatoire.',
                description: 'La description est obligatoire.',
                lieu: 'Le lieu est obligatoire.',
                type_contrat: 'Le type de contrat est obligatoire.',
                date_limite: 'La date limite est obligatoire.'
            };

            const textLikePattern = /^(?=.*[A-Za-zÀ-ÿ])[A-Za-zÀ-ÿ0-9\s\-'.\/,]{2,100}$/;

            function getField(name) {
                return form.querySelector('[name="' + name + '"]');
            }

            function getErrorBox(name) {
                return form.querySelector('[data-error-for="' + name + '"]');
            }

            function setFieldError(name, message) {
                const field = getField(name);
                const errorBox = getErrorBox(name);
                if (field) field.classList.add('field-error');
                if (errorBox) {
                    errorBox.textContent = message;
                    errorBox.classList.add('visible');
                }
            }

            function clearFieldError(name) {
                const field = getField(name);
                const errorBox = getErrorBox(name);
                if (field) field.classList.remove('field-error');
                if (errorBox) {
                    errorBox.textContent = '';
                    errorBox.classList.remove('visible');
                }
            }

            function validateField(name) {
                const field = getField(name);
                if (!field) return true;

                clearFieldError(name);
                const value = field.value.trim();

                if (requiredFields[name] && value === '') {
                    setFieldError(name, requiredFields[name]);
                    return false;
                }

                if (name === 'date_limite' && value !== '' && !/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(value)) {
                    setFieldError(name, 'La date limite est invalide.');
                    return false;
                }

                if (name === 'date_limite' && value !== '') {
                    const inputDate = new Date(value);
                    const now = new Date();
                    if (inputDate <= now) {
                        setFieldError(name, 'La date limite doit etre strictement posterieure a la date du jour.');
                        return false;
                    }
                }

                if ((name === 'lieu' || name === 'type_contrat') && value !== '' && !textLikePattern.test(value)) {
                    setFieldError(name, (name === 'lieu'
                        ? 'Le lieu doit contenir du texte valide (pas uniquement des chiffres).'
                        : 'Le type de contrat doit contenir du texte valide (pas uniquement des chiffres).'));
                    return false;
                }

                if ((name === 'salaire_min' || name === 'salaire_max') && value !== '' && Number(value) < 0) {
                    setFieldError(name, 'La valeur doit etre positive.');
                    return false;
                }

                return true;
            }

            form.addEventListener('submit', function (event) {
                let hasError = false;

                Object.keys(requiredFields).forEach(function (name) {
                    if (!validateField(name)) hasError = true;
                });

                const salaireMinValue = getField('salaire_min') ? getField('salaire_min').value.trim() : '';
                const salaireMaxValue = getField('salaire_max') ? getField('salaire_max').value.trim() : '';

                if (salaireMinValue !== '' && !validateField('salaire_min')) hasError = true;
                if (salaireMaxValue !== '' && !validateField('salaire_max')) hasError = true;

                if (salaireMinValue !== '' && salaireMaxValue !== '' && Number(salaireMaxValue) < Number(salaireMinValue)) {
                    setFieldError('salaire_max', 'Le salaire maximum doit etre superieur ou egal au salaire minimum.');
                    hasError = true;
                }

                if (hasError) {
                    event.preventDefault();
                }
            });

            Object.keys(requiredFields).concat(['salaire_min', 'salaire_max']).forEach(function (name) {
                const field = getField(name);
                if (!field) return;
                field.addEventListener('input', function () {
                    clearFieldError(name);
                });
                field.addEventListener('change', function () {
                    clearFieldError(name);
                });
            });
        })();
    </script>
</body>
</html>
