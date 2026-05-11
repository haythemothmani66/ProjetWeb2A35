<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EduMatch — Module événements, webinaires et formations.">
    <title>EduMatch - Evenements</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- CSS du template eduleb (pour la navbar unifiee _navbar.php) -->
    <link rel="stylesheet" href="<?php echo PROJECT_URL; ?>/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo PROJECT_URL; ?>/assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo PROJECT_URL; ?>/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="<?php echo PROJECT_URL; ?>/assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="<?php echo PROJECT_URL; ?>/assets/css/animate.css">
    <link rel="stylesheet" href="<?php echo PROJECT_URL; ?>/assets/css/style.css">

    <!-- CSS du module evenement -->
    <link rel="stylesheet" href="<?php echo PROJECT_URL; ?>/public/css/style.css">

    <style>
      /* ===== Navbar dropdown styles (necessaire pour _navbar.php) ===== */
      .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
      .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; text-align: center; border: none; cursor: pointer; }
      .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
      .user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; }
      .user-dropdown .user-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #525fe1; }
      .user-dropdown .user-name { font-weight: 600; font-size: 14px; color: #0b104a; white-space: nowrap; }
      .user-dropdown .dropdown-caret { font-size: 10px; color: #6c757d; transition: transform 0.2s; }
      .user-dropdown:hover .dropdown-caret { transform: rotate(180deg); }
      .user-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background: white; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); min-width: 200px; padding: 8px 0; z-index: 1000; margin-top: 8px; }
      .user-dropdown-menu.show { display: block; }
      .user-dropdown-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: #333; text-decoration: none; font-size: 14px; font-weight: 500; transition: background 0.2s; }
      .user-dropdown-menu a:hover { background: #f5f7fa; color: #525fe1; }
      .user-dropdown-menu a i { width: 18px; text-align: center; }
      .user-dropdown-menu hr { margin: 6px 0; border-color: #eee; }

      /* Espacement apres la navbar pour eviter le chevauchement avec le contenu */
      body { padding-top: 0; }
    </style>

    <?php if (!empty($data['extra_head'] ?? null)): ?>
    <?php echo $data['extra_head']; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar unifiee EduMatch -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>

    <main class="template-main py-4 py-lg-5">
        <div class="container">
            <?php echo $content; ?>
        </div>
    </main>

    <footer class="modern-footer text-white">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <a href="<?php echo BASE_URL; ?>/Home/index" class="text-decoration-none d-inline-block mb-3">
                        <img src="<?php echo PROJECT_URL; ?>/public/images/Logo_EduMatch2.png" alt="EduMatch" class="footer-logo-lg mb-2" onerror="this.style.display='none';">
                        <h3 class="text-white fw-bold h5 mb-0 logo-fallback footer-fallback" style="display:none;">EduMatch</h3>
                    </a>
                    <p class="mt-2 text-white-50 small mb-0">
                        EduMatch connecte les élèves et les professeurs de cours particuliers via des événements éducatifs utiles, pratiques et accessibles.
                    </p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-white me-3" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="fw-bold mb-3">Plateforme</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/Home/index" class="text-white-50 text-decoration-none">Accueil</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/AdminEvenement/index" class="text-white-50 text-decoration-none">Administration</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="fw-bold mb-3">Module</h5>
                    <ul class="list-unstyled small text-white-50">
                        <li class="mb-2">Événements</li>
                        <li class="mb-2">Inscriptions</li>
                        <li class="mb-2">Calendrier</li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="fw-bold mb-3">Contact</h5>
                    <p class="mb-2 small text-white-50"><i class="fas fa-map-marker-alt me-2 text-primary-soft"></i>Lac 2</p>
                    <p class="mb-2 small text-white-50"><i class="fas fa-phone me-2 text-primary-soft"></i>54 861 248</p>
                    <p class="mb-0 small text-white-50"><i class="fas fa-globe me-2 text-primary-soft"></i>www.EduMatch.tn</p>
                </div>
            </div>
            <hr class="my-4 border-secondary opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6 small text-white-50">&copy; <?php echo date('Y'); ?> EduMatch. Tous droits réservés.</div>
                <div class="col-md-6 text-md-end small mt-2 mt-md-0">
                    <span class="text-white-50">Module événements</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo PROJECT_URL; ?>/public/js/validation.js"></script>
    <?php if (!empty($data['footer_scripts'] ?? null)): ?>
    <?php echo $data['footer_scripts']; ?>
    <?php endif; ?>
</body>
</html>
