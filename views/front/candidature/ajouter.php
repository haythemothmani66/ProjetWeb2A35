<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deposer une candidature</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
<body>
    <div class="shell">
        <div class="topbar py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-dot">PW</div>
                    <div>
                        <div class="fw-bold">ProjetWeb2A35</div>
                        <small class="text-white-50">Formulaire de candidature</small>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="index.php?espace=front&module=candidature&action=liste" class="btn-secondary-job">Offres ouvertes</a>
                    <a href="index.php?espace=back&module=candidature&action=liste" class="btn-secondary-job">Administration</a>
                </div>
            </div>
        </div>

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
                                <div class="small text-white-50"><?= htmlspecialchars((string) $selectedOffer['lieu']) ?> · <?= htmlspecialchars((string) $selectedOffer['typecontrat']) ?></div>
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

                        <form method="post" class="row g-3" novalidate autocomplete="off" id="candidature-form">
                            <input type="hidden" name="offreid" value="<?= htmlspecialchars((string) $formData['offreid']) ?>">
                            <?php if (!empty($fieldErrors['offreid'])): ?>
                                <div class="col-12">
                                    <div class="alert alert-warning mb-0"><?= htmlspecialchars((string) $fieldErrors['offreid']) ?></div>
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
                                <label class="form-label" for="cvurl">Lien du CV *</label>
                                <input type="url" id="cvurl" name="cvurl" class="form-control<?= !empty($fieldErrors['cvurl']) ? ' field-error' : '' ?>" placeholder="https://..." value="<?= htmlspecialchars((string) $formData['cvurl']) ?>" autocomplete="new-password">
                                <?php if (!empty($fieldErrors['cvurl'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['cvurl']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="lettremotivation">Lettre de motivation *</label>
                                <textarea id="lettremotivation" name="lettremotivation" class="form-control<?= !empty($fieldErrors['lettremotivation']) ? ' field-error' : '' ?>" rows="8" autocomplete="new-password"><?= htmlspecialchars((string) $formData['lettremotivation']) ?></textarea>
                                <?php if (!empty($fieldErrors['lettremotivation'])): ?>
                                    <div class="field-error-text"><?= htmlspecialchars((string) $fieldErrors['lettremotivation']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                                <button type="submit" class="btn-primary-job">Envoyer la candidature</button>
                                <a href="index.php?espace=front&module=candidature&action=liste" class="btn-secondary-job">Retour aux offres</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

                if (name === 'lettremotivation') {
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
                ['nom', 'prenom', 'email', 'cvurl', 'lettremotivation'].forEach(function (name) {
                    if (!validateField(name)) {
                        hasError = true;
                    }
                });

                const offreid = getField('offreid');
                if (!offreid || offreid.value.trim() === '') {
                    hasError = true;
                }

                if (hasError) {
                    event.preventDefault();
                }
            });

            ['nom', 'prenom', 'email', 'cvurl', 'lettremotivation'].forEach(function (name) {
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
