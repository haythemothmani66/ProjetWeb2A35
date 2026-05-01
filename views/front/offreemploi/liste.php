<?php
$offres = $offres ?? [];
$filterState = $filterState ?? [
    'q' => '',
    'sort_by' => 'datecreation',
    'sort_dir' => 'desc',
    'sort_fields' => ['titre', 'lieu', 'typecontrat', 'datecreation', 'datelimite', 'statut'],
];
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
?>
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
    <style>
        .search-row-hide {
            display: none !important;
        }

        .filter-card {
            border: 0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
            border-radius: 18px;
        }

        .filter-title {
            font-size: .82rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
        }

        .radio-inline-wrap {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
        }

        .home_course .row > [class*="col-"] {
            display: flex;
        }

        .home_course .single_course {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
            min-height: 390px;
        }

        .home_course .single_c_img {
            height: 200px;
            overflow: hidden;
        }

        .home_course .single_c_img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .home_course .single_course h4 {
            margin: 14px 16px 8px;
            min-height: 2.6em;
            line-height: 1.3;
            display: -webkit-box;
            line-clamp: 2;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .home_course .single_course h4 a {
            display: inline-block;
            line-height: 1.3;
        }

        .home_course .single_course p {
            min-height: 0;
            margin: 0 16px 6px;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .home_course .offre-card-actions {
            margin-top: auto;
            margin-left: 16px;
            margin-right: 16px;
            margin-bottom: 12px;
            min-height: 78px;
        }

        @media (max-width: 768px) {
            .home_course .single_course {
                min-height: 380px;
            }

            .home_course .single_c_img {
                height: 190px;
            }
        }
    </style>
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
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <strong>Candidature active.</strong> Une offre ouverte peut maintenant recevoir une candidature directement depuis cette page.
                        </div>
                        <a href="index.php?espace=front&module=candidature&action=liste" class="btn btn-sm btn-primary">Acceder au formulaire</a>
                    </div>
                </div>
            </div>

            <form method="get" action="index.php" class="mb-4">
                <input type="hidden" name="espace" value="front">
                <input type="hidden" name="module" value="offreemploi">
                <input type="hidden" name="action" value="liste">

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card filter-card h-100">
                            <div class="card-body p-4">
                                <div class="filter-title mb-2">Recherche globale</div>
                                <label class="form-label" for="q">Texte à rechercher</label>
                                <input
                                    type="text"
                                    id="q"
                                    name="q"
                                    class="form-control"
                                    value="<?= htmlspecialchars((string) ($filterState['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="Rechercher..."
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card filter-card h-100">
                            <div class="card-body p-4">
                                <div class="filter-title mb-2">Zone de tri</div>
                                <label class="form-label" for="sort_by">Trier par</label>
                                <select id="sort_by" name="sort_by" class="form-select mb-3">
                                    <?php $selectedSortBy = (string) ($filterState['sort_by'] ?? 'datecreation'); ?>
                                    <option value="titre" <?= $selectedSortBy === 'titre' ? 'selected' : '' ?>>titre</option>
                                    <option value="lieu" <?= $selectedSortBy === 'lieu' ? 'selected' : '' ?>>lieu</option>
                                    <option value="typecontrat" <?= $selectedSortBy === 'typecontrat' ? 'selected' : '' ?>>type de contrat</option>
                                    <option value="datecreation" <?= $selectedSortBy === 'datecreation' ? 'selected' : '' ?>>date de creation</option>
                                    <option value="datelimite" <?= $selectedSortBy === 'datelimite' ? 'selected' : '' ?>>date limite</option>
                                    <option value="statut" <?= $selectedSortBy === 'statut' ? 'selected' : '' ?>>statut</option>
                                </select>

                                <?php $selectedSortDir = (string) ($filterState['sort_dir'] ?? 'desc'); ?>
                                <div class="radio-inline-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sort_dir" id="sort_dir_asc" value="asc" <?= $selectedSortDir === 'asc' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sort_dir_asc">ascending</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sort_dir" id="sort_dir_desc" value="desc" <?= $selectedSortDir !== 'asc' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sort_dir_desc">descending</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3 flex-wrap">
                    <button type="submit" class="btn btn-primary">Appliquer</button>
                    <a class="btn btn-outline-secondary" href="index.php?espace=front&module=offreemploi&action=liste">Réinitialiser</a>
                </div>
            </form>

            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-secondary small">Les résultats se filtrent en direct pendant la saisie.</div>
                    <span id="offre-count-badge" class="badge bg-primary"><?= count($offres) ?> offre(s)</span>
                </div>
            </div>

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
                        <div class="col-lg-4 col-sm-6 col-xs-12 offre-card">
                            <div class="single_course">
                                <div class="single_c_img">
                                    <img src="<?= htmlspecialchars($images[$index % count($images)]) ?>" class="img-fluid" alt="offre-image" />
                                    <span class="badge <?= ($offre['statut'] === 'ouverte') ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars((string) $offre['statut']) ?></span>
                                </div>
                                <h4><a href="index.php?espace=front&module=offreemploi&action=details&id=<?= (int) $offre['id'] ?>"><?= htmlspecialchars((string) $offre['titre']) ?></a></h4>
                                <p><span class="ti-location-pin"></span> <?= htmlspecialchars((string) $offre['lieu']) ?></p>
                                <p><span class="ti-briefcase"></span> <?= htmlspecialchars((string) $offre['typecontrat']) ?></p>
                                <p><span class="ti-calendar"></span> Date limite: <?= htmlspecialchars($formatDateTime($offre['datelimite'])) ?></p>
                                <div class="d-grid gap-2 offre-card-actions">
                                    <a href="index.php?espace=front&module=offreemploi&action=details&id=<?= (int) $offre['id'] ?>" class="btn_one">Voir details</a>
                                    <?php if (($offre['statut'] ?? '') === 'ouverte'): ?>
                                        <a href="index.php?espace=front&module=candidature&action=ajouter&offreid=<?= (int) $offre['id'] ?>" class="btn btn-outline-primary">Candidater</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="col-12">
                        <div id="no-result-alert" class="alert alert-info d-none">Aucune offre ne correspond à votre recherche.</div>
                    </div>
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
    <script>
        (function () {
            const searchInput = document.getElementById('q');
            const offerCards = Array.from(document.querySelectorAll('.offre-card'));
            const noResultAlert = document.getElementById('no-result-alert');
            const countBadge = document.getElementById('offre-count-badge');

            if (!searchInput || offerCards.length === 0) {
                return;
            }

            function normalize(value) {
                return value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            }

            function updateCount(visibleCount) {
                if (countBadge) {
                    countBadge.textContent = visibleCount + ' offre(s)';
                }
            }

            function filterOffers() {
                const query = normalize(searchInput.value.trim());
                let visibleCount = 0;

                offerCards.forEach(function (card) {
                    const text = normalize(card.textContent || '');
                    const isMatch = query === '' || text.includes(query);
                    card.classList.toggle('search-row-hide', !isMatch);
                    if (isMatch) {
                        visibleCount += 1;
                    }
                });

                if (noResultAlert) {
                    noResultAlert.classList.toggle('d-none', visibleCount !== 0);
                }

                updateCount(visibleCount);
            }

            searchInput.addEventListener('input', filterOffers);
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });

            filterOffers();
        })();
    </script>
</body>
</html>
