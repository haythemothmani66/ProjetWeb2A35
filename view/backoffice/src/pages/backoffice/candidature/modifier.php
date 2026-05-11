<?php
$candidature = $candidature ?? [];
$offres = $offres ?? [];
$fieldErrors = $fieldErrors ?? [];
$errors = $errors ?? [];
$error = $error ?? '';

$BO = '/gestion_users/view/backoffice/src';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Back Office - Modifier candidature | EduMatch Admin</title>
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
    .form-control, .form-select { border-radius: 14px; padding: 12px 14px; }
    .form-control:focus, .form-select:focus { box-shadow: 0 0 0 3px rgba(15,110,139,.14); border-color: #0f6e8b; }
    .field-error {
      border-color: #dc3545 !important;
      box-shadow: 0 0 0 3px rgba(220,53,69,.12) !important;
    }
    .field-error-text {
      color: #b42318;
      font-size: .86rem;
      margin-top: 6px;
      font-weight: 600;
    }
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
            <h1 class="mb-0">Modifier la candidature</h1>
          </div>
          <div class="col-lg-4 text-lg-end">
            <a class="btn btn-outline-secondary" href="/gestion_users/controller/CandidatureController.php?espace=back&action=details&id=<?= (int) $candidature['id'] ?>">Retour aux details</a>
          </div>
        </div>

        <div class="card metric-card">
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $message): ?>
                                        <li><?= htmlspecialchars((string) $message) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="post" class="row g-3" novalidate autocomplete="off" id="candidature-form">
                            <div class="col-md-6">
                                <label class="form-label" for="offreid">Offre *</label>
                                <select id="offreid" name="offreid" class="form-select<?= !empty($fieldErrors['offre_id']) ? ' field-error' : '' ?>">
                                    <option value="">Choisir une offre</option>
                                    <?php foreach ($offres as $offre): ?>
                                        <option value="<?= (int) $offre['id'] ?>" <?= ((string) $candidature['offre_id'] === (string) $offre['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string) $offre['titre']) ?> · <?= htmlspecialchars((string) $offre['lieu']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!empty($fieldErrors['offre_id'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['offre_id']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="nom">Nom *</label>
                                <input type="text" id="nom" name="nom" class="form-control<?= !empty($fieldErrors['nom']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) ($candidature['nom'] ?? '')) ?>" autocomplete="new-password">
                                <?php if (!empty($fieldErrors['nom'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['nom']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="prenom">Prenom *</label>
                                <input type="text" id="prenom" name="prenom" class="form-control<?= !empty($fieldErrors['prenom']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) ($candidature['prenom'] ?? '')) ?>" autocomplete="new-password">
                                <?php if (!empty($fieldErrors['prenom'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['prenom']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control<?= !empty($fieldErrors['email']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) $candidature['email']) ?>" autocomplete="new-password">
                                <?php if (!empty($fieldErrors['email'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['email']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="statut">Statut *</label>
                                <select id="statut" name="statut" class="form-select<?= !empty($fieldErrors['statut']) ? ' field-error' : '' ?>">
                                    <option value="enattente" <?= ($candidature['statut'] === 'en_attente') ? 'selected' : '' ?>>enattente</option>
                                    <option value="acceptee" <?= ($candidature['statut'] === 'acceptee' || $candidature['statut'] === 'acceptée') ? 'selected' : '' ?>>acceptee</option>
                                    <option value="refusee" <?= ($candidature['statut'] === 'refusee' || $candidature['statut'] === 'refusée') ? 'selected' : '' ?>>refusee</option>
                                </select>
                                <?php if (!empty($fieldErrors['statut'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['statut']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="cvurl">Lien du CV *</label>
                                <input type="url" id="cvurl" name="cvurl" class="form-control<?= !empty($fieldErrors['cvurl']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) $candidature['cvurl']) ?>" autocomplete="new-password">
                                <?php if (!empty($fieldErrors['cvurl'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['cvurl']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="datereponse">Date de reponse</label>
                                <input type="date" id="datereponse" name="datereponse" class="form-control<?= !empty($fieldErrors['date_reponse']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) ($candidature['date_reponse'] ?? '')) ?>" autocomplete="new-password">
                                <?php if (!empty($fieldErrors['date_reponse'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['date_reponse']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="lettremotivation">Lettre de motivation *</label>
                                <textarea id="lettremotivation" name="lettremotivation" class="form-control<?= !empty($fieldErrors['lettre_motivation']) ? ' field-error' : '' ?>" rows="8" autocomplete="new-password"><?= htmlspecialchars((string) $candidature['lettre_motivation']) ?></textarea>
                                <?php if (!empty($fieldErrors['lettre_motivation'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['lettre_motivation']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
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
            const form = document.getElementById('candidature-form');
            if (!form) return;

            const patterns = {
                name: /^(?=.*[A-Za-zÀ-ÿ])[A-Za-zÀ-ÿ\s\-']{2,60}$/,
                email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/i
            };

            function getField(name) {
                return form.querySelector('[name="' + name + '"]');
            }

            function ensureErrorBox(field) {
                if (!field) return null;
                let errorBox = field.parentElement.querySelector('.field-error-text.js-inline-error');
                if (!errorBox) {
                    errorBox = document.createElement('div');
                    errorBox.className = 'field-error-text js-inline-error';
                    field.insertAdjacentElement('afterend', errorBox);
                }
                return errorBox;
            }

            function setError(name, message) {
                const field = getField(name);
                if (!field) return;
                field.classList.add('field-error');
                const errorBox = ensureErrorBox(field);
                if (errorBox) {
                    errorBox.textContent = message;
                }
            }

            function clearError(name) {
                const field = getField(name);
                if (!field) return;
                field.classList.remove('field-error');
                const errorBox = field.parentElement.querySelector('.field-error-text.js-inline-error');
                if (errorBox) {
                    errorBox.textContent = '';
                }
            }

            function validateField(name) {
                const field = getField(name);
                if (!field) return true;

                clearError(name);
                const value = field.value.trim();

                if (name === 'offre_id') {
                    if (value === '') {
                        setError(name, 'L\'offre est obligatoire.');
                        return false;
                    }
                }

                if (name === 'nom' || name === 'prenom') {
                    if (value === '') {
                        setError(name, (name === 'nom' ? 'Le nom est obligatoire.' : 'Le prenom est obligatoire.'));
                        return false;
                    }
                    if (!patterns.name.test(value)) {
                        setError(name, (name === 'nom'
                            ? 'Le nom doit contenir uniquement des lettres, espaces, apostrophes ou tirets.'
                            : 'Le prenom doit contenir uniquement des lettres, espaces, apostrophes ou tirets.'));
                        return false;
                    }
                }

                if (name === 'email') {
                    if (value === '') {
                        setError(name, 'L\'email est obligatoire.');
                        return false;
                    }
                    if (!patterns.email.test(value)) {
                        setError(name, 'Le format de l\'email est invalide.');
                        return false;
                    }
                }

                if (name === 'cvurl') {
                    if (value === '') {
                        setError(name, 'Le lien du CV est obligatoire.');
                        return false;
                    }
                    try {
                        const parsed = new URL(value);
                        if (!['http:', 'https:'].includes(parsed.protocol)) {
                            setError(name, 'Le lien du CV doit etre une URL valide en http:// ou https://.');
                            return false;
                        }
                    } catch (e) {
                        setError(name, 'Le lien du CV doit etre une URL valide en http:// ou https://.');
                        return false;
                    }
                }

                if (name === 'lettre_motivation') {
                    if (value === '') {
                        setError(name, 'La lettre de motivation est obligatoire.');
                        return false;
                    }
                    if (value.length < 30) {
                        setError(name, 'La lettre de motivation doit contenir au moins 30 caracteres.');
                        return false;
                    }
                }

                if (name === 'date_reponse' && value !== '' && !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                    setError(name, 'La date de reponse est invalide.');
                    return false;
                }

                return true;
            }

            form.addEventListener('submit', function (event) {
                let hasError = false;
                ['offre_id', 'nom', 'prenom', 'email', 'cvurl', 'lettre_motivation', 'date_reponse'].forEach(function (name) {
                    if (!validateField(name)) {
                        hasError = true;
                    }
                });

                if (hasError) {
                    event.preventDefault();
                }
            });

            ['offre_id', 'nom', 'prenom', 'email', 'cvurl', 'lettre_motivation', 'date_reponse'].forEach(function (name) {
                const field = getField(name);
                if (!field) return;
                field.addEventListener('input', function () {
                    clearError(name);
                });
                field.addEventListener('change', function () {
                    clearError(name);
                });
            });
        })();
    </script>
</body>
</html>
