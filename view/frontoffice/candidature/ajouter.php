<?php
$formData = $formData ?? [
    'offre_id' => '',
    'nom' => '',
    'prenom' => '',
    'email' => '',
    'cvurl' => '',
    'lettre_motivation' => '',
];
$fieldErrors = $fieldErrors ?? [];
$errors = $errors ?? [];
$error = $error ?? '';
$selectedOffer = $selectedOffer ?? null;
$recaptchaSiteKey = $recaptchaSiteKey ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deposer une candidature</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS eduleb (pour navbar EduMatch + footer) -->
    <link rel="stylesheet" href="/gestion_users/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/gestion_users/assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="/gestion_users/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="/gestion_users/assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="/gestion_users/assets/css/style.css">
    <!-- Bootstrap 5.3 (Pour le formulaire candidature) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #12202f;
            --muted: #587089;
            --primary: #0f6e8b;
            --primary-dark: #0a4f66;
            --bg: #f3f7fb;
            --card: rgba(255,255,255,.92);
            --shadow: 0 16px 40px rgba(16, 33, 52, .12);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 12% 12%, rgba(15,110,139,.18), transparent 28%),
                linear-gradient(145deg, #eef4f9 0%, #f9fbfd 45%, #edf4fb 100%);
        }
        .shell {
            min-height: 100vh;
        }
        .topbar {
            background: rgba(10, 20, 30, .82);
            backdrop-filter: blur(10px);
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .brand-dot {
            width: 42px; height: 42px; border-radius: 14px;
            background: linear-gradient(135deg, #f3a712, #ff7a59);
            display:flex; align-items:center; justify-content:center;
            font-weight: 800; color: #111;
        }
        .page-title {
            font-family: 'Jost', sans-serif;
            font-weight: 800;
            letter-spacing: -.02em;
        }
        .hero-box {
            border-radius: 24px;
            background: var(--card);
            box-shadow: var(--shadow);
            border: 1px solid rgba(18, 32, 47, .08);
            padding: 28px;
        }
        .info-panel {
            border-radius: 22px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            box-shadow: 0 20px 48px rgba(15,110,139,.22);
        }
        .info-panel .small { color: rgba(255,255,255,.8) !important; }
        .offer-chip {
            display:inline-flex; align-items:center; gap:8px;
            padding: 8px 12px; border-radius: 999px;
            background: rgba(255,255,255,.14); color: #fff;
            font-weight: 600;
            font-size: .9rem;
        }
        .form-control, .form-select {
            border-radius: 14px;
            padding: 12px 14px;
            border: 1px solid #c8d7e2;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15,110,139,.14);
        }
        .btn-primary-job {
            border: 0;
            border-radius: 999px;
            padding: 12px 18px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            font-weight: 700;
        }
        .btn-secondary-job {
            border: 0;
            border-radius: 999px;
            padding: 12px 18px;
            background: #e9eff4;
            color: #173042;
            font-weight: 700;
            text-decoration: none;
        }
        .muted { color: var(--muted); }
        .alert-soft {
            border-radius: 18px;
            border: 1px solid rgba(15,110,139,.16);
            background: rgba(255,255,255,.9);
        }
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
<body data-spy="scroll" data-offset="80">
    <div class="preloaders"><span class="loader"></span></div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>

    <div class="shell">
        <main class="container py-5">
            <div class="row g-4 align-items-start">
                <div class="col-lg-5">
                    <div class="info-panel p-4 p-xl-5">
                        <span class="offer-chip mb-3"><i class="fa-solid fa-paper-plane"></i> Envoyer une candidature</span>
                        <h1 class="page-title h2 mb-3">Postuler a une offre d'emploi</h1>
                        <p class="mb-4">Renseignez votre nom, votre prenom, votre email, le lien de votre CV et votre lettre de motivation. L'offre est deja selectionnee depuis la liste des offres disponibles.</p>

                        <?php if (!empty($selectedOffer)): ?>
                            <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10">
                                <div class="small text-uppercase fw-bold mb-1">Offre selectionnee</div>
                                <div class="h5 mb-1"><?= htmlspecialchars((string) $selectedOffer['titre']) ?></div>
                                <div class="small text-white-50"><?= htmlspecialchars((string) $selectedOffer['lieu']) ?> · <?= htmlspecialchars((string) $selectedOffer['type_contrat']) ?></div>
                            </div>
                        <?php else: ?>
                            <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10">
                                <div class="small text-uppercase fw-bold mb-1">Aucune offre selectionnee</div>
                                <div class="small text-white-50">Choisissez une offre ouverte dans la liste a droite.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="hero-box">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-soft">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $message): ?>
                                        <li><?= htmlspecialchars((string) $message) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger alert-soft"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="post" class="row g-3" novalidate autocomplete="off" enctype="multipart/form-data" id="candidature-form">
                            <input type="hidden" name="offre_id" value="<?= htmlspecialchars((string) $formData['offre_id']) ?>">
                            <?php if (!empty($fieldErrors['offre_id'])): ?>
                                <div class="col-12">
                                    <div class="alert alert-warning mb-0"><?= htmlspecialchars((string) $fieldErrors['offre_id']) ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="col-md-6">
                                <label class="form-label" for="nom">Nom *</label>
                                <input type="text" id="nom" name="nom" class="form-control<?= !empty($fieldErrors['nom']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) $formData['nom']) ?>" autocomplete="new-password">
                                <?php if (!empty($fieldErrors['nom'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['nom']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="prenom">Prenom *</label>
                                <input type="text" id="prenom" name="prenom" class="form-control<?= !empty($fieldErrors['prenom']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) $formData['prenom']) ?>" autocomplete="new-password">
                                <?php if (!empty($fieldErrors['prenom'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['prenom']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control<?= !empty($fieldErrors['email']) ? ' field-error' : '' ?>" value="<?= htmlspecialchars((string) $formData['email']) ?>" autocomplete="new-password">
                                <?php if (!empty($fieldErrors['email'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['email']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="cvfile">CV *</label>
                                <input type="file" id="cvfile" name="cvfile" accept=".pdf,.doc,.docx" class="form-control<?= !empty($fieldErrors['cvurl']) ? ' field-error' : '' ?>">
                                <small class="form-text text-muted">Formats acceptes : PDF, DOC, DOCX (max 5 Mo)</small>
                                <?php if (!empty($fieldErrors['cvurl'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['cvurl']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="lettre_motivation">Lettre de motivation *</label>
                                <textarea id="lettre_motivation" name="lettre_motivation" class="form-control<?= !empty($fieldErrors['lettre_motivation']) ? ' field-error' : '' ?>" rows="8" autocomplete="new-password"><?= htmlspecialchars((string) $formData['lettre_motivation']) ?></textarea>
                                <?php if (!empty($fieldErrors['lettre_motivation'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['lettre_motivation']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Verification anti-robot *</label>
                                <?php if (!empty($recaptchaSiteKey) && $recaptchaSiteKey !== 'your-site-key'): ?>
                                    <div class="g-recaptcha<?= !empty($fieldErrors['recaptcha']) ? ' field-error' : '' ?>" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey) ?>"></div>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">Le reCAPTCHA n'est pas encore configure localement.</div>
                                <?php endif; ?>
                                <?php if (!empty($fieldErrors['recaptcha'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['recaptcha']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                                <button type="submit" class="btn-primary-job">Envoyer la candidature</button>
                                <a href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste" class="btn-secondary-job">Retour aux offres</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- FOOTER EduMatch -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <a href="/gestion_users/view/template/index.php" class="text-decoration-none">
              <img src="/gestion_users/assets/img/logo.png" alt="EduMatch Logo" class="mb-3" style="height: 50px;">
            </a>
            <p class="mt-3 text-light opacity-75">Plateforme intelligente de mise en relation des etudiants avec des professeurs experts dans toutes les matieres academiques pour des experiences d'apprentissage personnalisees.</p>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Plateforme</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="/gestion_users/view/template/index.php" class="text-light text-decoration-none">Accueil</a></li>
              <?php if (empty($_SESSION['user_id'])): ?>
              <li class="mb-2"><a href="/gestion_users/view/template/sign-in.php" class="text-light text-decoration-none">Connexion</a></li>
              <li class="mb-2"><a href="/gestion_users/view/template/sign-up.php" class="text-light text-decoration-none">Inscription</a></li>
              <?php else: ?>
              <li class="mb-2"><a href="/gestion_users/view/template/profil.php" class="text-light text-decoration-none">Mon Profil</a></li>
              <li class="mb-2"><a href="/gestion_users/auth/logout" class="text-light text-decoration-none">Deconnexion</a></li>
              <?php endif; ?>
            </ul>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Matieres academiques</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Mathematiques</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Programmation</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Algorithmique</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Langues</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences humaines</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Coordonnees</h5>
            <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Tunis, Tunisie</p>
            <p class="mb-2"><i class="fas fa-phone me-2"></i>+216 90 549 254</p>
            <p class="mb-2"><i class="fas fa-envelope me-2"></i>edumatch@gmail.com</p>
          </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="row align-items-center">
          <div class="col-md-6">
            <p class="mb-0 text-light opacity-75">&copy; 2026 EduMatch. Tous droits reserves.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <a href="#" class="text-light text-decoration-none me-3">Politique de confidentialite</a>
            <a href="#" class="text-light text-decoration-none me-3">Conditions d'utilisation</a>
            <a href="#" class="text-light text-decoration-none">Assistance</a>
          </div>
        </div>
      </div>
    </footer>
    <!-- END FOOTER -->

    <!-- JS eduleb (pour navbar mobile + dropdown user) -->
    <script src="/gestion_users/assets/js/jquery-1.12.4.min.js"></script>
    <script src="/gestion_users/assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="/gestion_users/assets/js/scripts.js"></script>
    <!-- Bootstrap 5.3 (Pour le formulaire) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($recaptchaSiteKey) && $recaptchaSiteKey !== 'your-site-key'): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
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

                return true;
            }

            form.addEventListener('submit', function (event) {
                let hasError = false;
                ['nom', 'prenom', 'email', 'cvurl', 'lettre_motivation'].forEach(function (name) {
                    if (!validateField(name)) {
                        hasError = true;
                    }
                });

                const offreid = getField('offre_id');
                if (!offreid || offreid.value.trim() === '') {
                    hasError = true;
                }

                if (hasError) {
                    event.preventDefault();
                }
            });

            ['nom', 'prenom', 'email', 'cvurl', 'lettre_motivation'].forEach(function (name) {
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
