<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Événement - Plateforme Éducation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg shadow-sm edumatch-navbar">
        <div class="container">
            <a class="navbar-brand logo-wrap" href="<?php echo BASE_URL; ?>/Home/index">
                <img src="<?php echo BASE_URL; ?>/public/images/Logo_EduMatch2.png" alt="EduMatch" class="site-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                <span class="logo-fallback" style="display:none;">EduMatch</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/Home/index">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminEvenement/index">Administration</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php echo $content; ?>
    </div>

    <footer class="site-footer py-4 mt-5 border-top">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-md-4 text-center text-md-start">
                    <img src="<?php echo BASE_URL; ?>/public/images/Logo_EduMatch2.png" alt="EduMatch" class="footer-logo-lg" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                    <span class="logo-fallback footer-fallback" style="display:none;">EduMatch</span>
                </div>
                <div class="col-md-8">
                    <h5 class="mb-2">About</h5>
                    <p class="text-muted mb-3">
                        EduMatch connecte les eleves et les professeurs de cours particuliers via des evenements educatifs
                        utiles, pratiques et accessibles.
                    </p>
                    <h5 class="mb-2">Contact</h5>
                    <p class="mb-1"><strong>Num:</strong> 54 861 248</p>
                    <p class="mb-1"><strong>Mail:</strong> www.EduMatch.tn</p>
                    <p class="mb-0"><strong>Lieu:</strong> Lac 2</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/js/validation.js"></script>
</body>
</html>
