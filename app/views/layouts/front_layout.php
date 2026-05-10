<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EduMatch — Module événements, webinaires et formations.">
    <title>Module Événement - Plateforme Éducation</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <?php if (!empty($data['extra_head'] ?? null)): ?>
    <?php echo $data['extra_head']; ?>
    <?php endif; ?>
</head>
<body class="template-front-body">
    <div id="navigation" class="site-navigation navbar-light bg-white shadow-sm">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row align-items-center py-2 g-2">
                <div class="col-auto">
                    <div class="site-logo">
                        <a href="<?php echo BASE_URL; ?>/Home/index" class="text-decoration-none d-flex align-items-center">
                            <img src="<?php echo BASE_URL; ?>/public/images/Logo_EduMatch2.png" alt="EduMatch" class="site-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                            <span class="logo-fallback text-gradient-brand fw-bold" style="display:none;font-size:1.35rem;">EduMatch</span>
                        </a>
                    </div>
                </div>
                <div class="col d-none d-lg-flex justify-content-center">
                    <nav id="main-menu" aria-label="Navigation principale">
                        <ul class="list-unstyled d-flex flex-wrap align-items-center gap-1 gap-xl-3 mb-0">
                            <li><a href="<?php echo BASE_URL; ?>/Home/index">Accueil</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/AdminEvenement/index">Administration</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-auto ms-auto d-none d-xl-flex align-items-center gap-2">
                    <a href="<?php echo BASE_URL; ?>/AdminEvenement/index" class="btn-one-header"><i class="fas fa-cog me-1"></i> Administration</a>
                </div>
                <div class="col-12 d-lg-none">
                    <button class="navbar-toggler-custom w-100 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-expanded="false" aria-controls="mobileNav">
                        <span class="small fw-semibold text-muted">Menu</span>
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="collapse mt-2" id="mobileNav">
                        <ul class="mobile-menu list-unstyled mb-0 py-2">
                            <li><a href="<?php echo BASE_URL; ?>/Home/index">Accueil</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/AdminEvenement/index">Administration</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <img src="<?php echo BASE_URL; ?>/public/images/Logo_EduMatch2.png" alt="EduMatch" class="footer-logo-lg mb-2" onerror="this.style.display='none';">
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
    <script src="<?php echo BASE_URL; ?>/public/js/validation.js"></script>
    <?php if (!empty($data['footer_scripts'] ?? null)): ?>
    <?php echo $data['footer_scripts']; ?>
    <?php endif; ?>
</body>
</html>
