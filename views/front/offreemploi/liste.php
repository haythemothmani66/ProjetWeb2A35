<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Offres d'emploi - Front Office</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="../assets/owlcarousel/css/owl.carousel.css">
    <link rel="stylesheet" href="../assets/owlcarousel/css/owl.theme.css">
    <link rel="stylesheet" href="../assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="../assets/css/magnific-popup.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body data-spy="scroll" data-offset="80">
    <div class="preloaders">
        <span class="loader"></span>
    </div>

    <div id="navigation" class="navbar-light bg-faded site-navigation">
        <div class="container-fluid">
            <div class="row">
                <div class="col-20 align-self-center">
                    <div class="site-logo">
                        <a href="../view/template/index.html"><img src="../assets/img/logo.png" alt="logo"></a>
                    </div>
                </div>
                <div class="col-60 d-flex">
                    <nav id="main-menu">
                        <ul>
                            <li><a href="index.php?espace=front&module=offreemploi&action=liste">Offres d'emploi</a></li>
                            <li><a href="../view/template/about.html">About</a></li>
                            <li><a href="../view/template/contact.html">Contact</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-20 d-none d-xl-block text-end align-self-center">
                    <a href="index.php?espace=back&module=offreemploi&action=liste" class="btn_one">Back Office</a>
                </div>
                <ul class="mobile_menu">
                    <li><a href="index.php?espace=front&module=offreemploi&action=liste">Offres</a></li>
                    <li><a href="index.php?espace=back&module=offreemploi&action=liste">Back Office</a></li>
                </ul>
            </div>
        </div>
    </div>

    <section class="section-top">
        <div class="container">
            <div class="col-lg-10 offset-lg-1 text-center">
                <div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0">
                    <h1>Offres d'emploi disponibles</h1>
                    <ul>
                        <li><a href="index.php?espace=front&module=offreemploi&action=liste">Front Office</a></li>
                        <li> / Liste des offres</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="home_course section-padding">
        <div class="container">
            <div class="row">
                <?php if (empty($offres)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">Aucune offre disponible pour le moment.</div>
                    </div>
                <?php else: ?>
                    <?php
                    $images = [
                        '../assets/img/course/1.png',
                        '../assets/img/course/2.png',
                        '../assets/img/course/3.png',
                        '../assets/img/course/4.png',
                    ];
                    ?>
                    <?php foreach ($offres as $index => $offre): ?>
                        <div class="col-lg-4 col-sm-6 col-xs-12">
                            <div class="single_course">
                                <div class="single_c_img">
                                    <img src="<?= htmlspecialchars($images[$index % count($images)]) ?>" class="img-fluid" alt="offre-image" />
                                    <span class="badge <?= ($offre['statut'] === 'ouverte') ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars((string) $offre['statut']) ?></span>
                                </div>
                                <h4><a href="index.php?espace=front&module=offreemploi&action=details&id=<?= (int) $offre['id'] ?>"><?= htmlspecialchars((string) $offre['titre']) ?></a></h4>
                                <p><span class="ti-location-pin"></span> <?= htmlspecialchars((string) $offre['lieu']) ?></p>
                                <p><span class="ti-briefcase"></span> <?= htmlspecialchars((string) $offre['typecontrat']) ?></p>
                                <p><span class="ti-calendar"></span> Date limite: <?= htmlspecialchars((string) $offre['datelimite']) ?></p>
                                <div class="d-grid gap-2">
                                    <a href="index.php?espace=front&module=offreemploi&action=details&id=<?= (int) $offre['id'] ?>" class="btn_one">Voir details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="footer-default">
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center">
                        <p>&copy; 2026 Eduleb - Module Offres d'emploi.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="../assets/js/jquery-1.12.4.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="../assets/owlcarousel/js/owl.carousel.min.js"></script>
    <script src="../assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="../assets/js/wow.min.js"></script>
    <script src="../assets/js/jquery.inview.min.js"></script>
    <script src="../assets/js/jquery.magnific-popup.min.js"></script>
    <script src="../assets/js/modernizr-2.8.3.min.js"></script>
    <script src="../assets/js/scrolltopcontrol.js"></script>
    <script src="../assets/js/superMarquee.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>
