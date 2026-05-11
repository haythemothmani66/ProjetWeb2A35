<?php
$websiteName = $websiteName ?? 'EduMatch';
$pageTitle = $pageTitle ?? 'EduMatch - Espace Front';
$moduleLinks = $moduleLinks ?? [];
$moduleCardLinks = $moduleCardLinks ?? [];
$moduleDescriptions = $moduleDescriptions ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Page d'accueil de la plateforme educative EduMatch">
    <meta name="keywords" content="EduMatch, education, modules, plateforme etudiante, accueil">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="assets/owlcarousel/css/owl.carousel.css">
    <link rel="stylesheet" href="assets/owlcarousel/css/owl.theme.css">
    <link rel="stylesheet" href="assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-spy="scroll" data-offset="80">
    <div class="preloaders">
        <span class="loader"></span>
    </div>

    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <section class="home_bg hb_height" style="background-image: url(assets/img/bg/home-bg.jpg); background-size: cover; background-position: center center;">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-sm-12 col-xs-12">
                    <div class="hero-text ht_top">
                        <h1><span>Bienvenue sur</span> <?= htmlspecialchars($websiteName, ENT_QUOTES, 'UTF-8') ?></h1>
                        <p>
                            EduMatch est une plateforme educative qui regroupe les services etudiants,
                            les outils d'apprentissage, les opportunites, les quiz, les evenements,
                            les devoirs et bien plus encore au meme endroit.
                        </p>
                    </div>
                    <div class="home_sb">
                        <a href="#modules" class="btn_one">Explorer les modules</a>
                    </div>
                </div>
                <div class="col-lg-5 col-sm-12 col-xs-12">
                    <div class="hero-text-img">
                        <img src="assets/img/home-img2.png" class="img-fluid" alt="Accueil EduMatch" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="count_area counter_feature">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="single-counter">
                        <span class="ti-layout sc_one"></span>
                        <h2>Presentation de l'espace front commun</h2>
                        <p>
                            La plateforme aide les utilisateurs a acceder aux differents modules depuis
                            une interface partagee, pour faciliter l'integration de l'equipe tout en
                            gardant une experience moderne et claire.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="modules" class="top_cat__area section-padding" style="background-image: url(assets/img/bg/shape-1.png); background-size: cover; background-position: center center;">
        <div class="container">
            <div class="section-title text-center">
                <h2>Modules EduMatch</h2>
                <p>Chaque bloc ci-dessous represente un futur point d'entree dans le template front partage.</p>
            </div>
            <div class="row">
                <?php foreach ($moduleDescriptions as $moduleName => $moduleDescription): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.2s" data-wow-offset="0">
                        <div class="single_tp">
                            <h3><?= htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($moduleDescription, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php $cardUrl = $moduleCardLinks[$moduleName] ?? '#'; ?>
                            <a href="<?= htmlspecialchars($cardUrl, ENT_QUOTES, 'UTF-8') ?>" class="cta"><span>Ouvrir</span> <i class="fa fa-angle-right"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <button type="button" id="scroll-to-top" class="topcontrol" aria-label="Retour en haut">
        <i class="ti-arrow-up scrolltop"></i>
    </button>

    <script src="assets/js/jquery-1.12.4.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="assets/owlcarousel/js/owl.carousel.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/jquery.inview.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/scripts.js"></script>
    <script>
        (function () {
            const button = document.getElementById('scroll-to-top');
            if (!button) return;

            function getScrollTop() {
                return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
            }

            function toggleButton() {
                if (getScrollTop() > 120) {
                    button.style.opacity = '1';
                    button.style.pointerEvents = 'auto';
                } else {
                    button.style.opacity = '0';
                    button.style.pointerEvents = 'none';
                }
            }

            button.addEventListener('click', function (event) {
                event.preventDefault();
                document.documentElement.scrollTop = 0;
                document.body.scrollTop = 0;
                window.scrollTo(0, 0);
            });

            window.addEventListener('scroll', toggleButton, { passive: true });
            toggleButton();
        })();
    </script>
</body>
</html>
