<?php
$offres = $offres ?? [];
$filterState = $filterState ?? [
    'q' => '',
    'sort_by' => 'date_creation',
    'sort_dir' => 'desc',
    'sort_fields' => ['titre', 'lieu', 'type_contrat', 'date_creation', 'date_limite', 'statut'],
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

$moduleLinks = [
    "Offre d'emploi" => '/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste',
];

$activeCount = 0;
foreach ($offres as $offreItem) {
    if (($offreItem['statut'] ?? '') === 'ouverte') {
        $activeCount++;
    }
}

$images = [
    'assets/img/course/1.png',
    'assets/img/course/2.png',
    'assets/img/course/3.png',
    'assets/img/course/4.png',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Offres d'emploi - EduMatch</title>
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
    <style>
        .page-surface {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .84), rgba(255, 255, 255, .96)),
                url('assets/img/bg/section-top.jpg') center/cover no-repeat fixed;
        }

        .hero-offer {
            position: relative;
            padding: 95px 0 65px;
            overflow: hidden;
        }

        .hero-offer::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 18% 15%, rgba(82, 95, 225, .18), transparent 28%), radial-gradient(circle at 85% 20%, rgba(0, 214, 201, .14), transparent 24%);
            pointer-events: none;
        }

        .hero-panel {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, .82);
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 30px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .10);
            padding: 34px;
            backdrop-filter: blur(12px);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border-radius: 999px;
            background: rgba(82, 95, 225, .10);
            color: #4250d8;
            font-weight: 700;
            font-size: .9rem;
            margin-bottom: 18px;
        }

        .hero-title {
            font-family: 'Jost', sans-serif;
            font-size: clamp(1.7rem, 4.5vw, 3.5rem);
            line-height: .98;
            letter-spacing: -.04em;
            margin: 0 0 16px;
        }

        .hero-copy {
            max-width: 72ch;
            color: #5b6478;
            font-size: 1.05rem;
            line-height: 1.85;
            margin-bottom: 24px;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 22px;
        }

        .stat-card {
            border-radius: 22px;
            background: rgba(248, 250, 255, .9);
            border: 1px solid rgba(15, 23, 42, .06);
            padding: 16px;
        }

        .stat-card strong {
            display: block;
            font-family: 'Jost', sans-serif;
            font-size: 1.6rem;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-card span {
            color: #64748b;
            font-size: .95rem;
        }

        .filter-shell {
            margin-top: -24px;
            position: relative;
            z-index: 2;
        }

        .filter-card {
            border: 0;
            border-radius: 28px;
            box-shadow: 0 16px 50px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .filter-card .card-body {
            padding: 28px;
        }

        .filter-title {
            font-size: .82rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .radio-inline-wrap {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .toolbar-row {
            margin: 20px 0 8px;
            align-items: center;
        }

        .toolbar-row .badge {
            border-radius: 999px;
            padding: .55rem .9rem;
        }

        .offer-grid {
            margin-top: 18px;
        }

        .offer-grid .col-lg-4,
        .offer-grid .col-md-6,
        .offer-grid .col-sm-12 {
            display: flex;
        }

        .single_course.offer-card {
            width: 100%;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            border-radius: 26px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 14px 40px rgba(15, 23, 42, .08);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .single_course.offer-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 24px 50px rgba(15, 23, 42, .12);
        }

        .single_c_img {
            position: relative;
            overflow: hidden;
            height: 220px;
        }

        .single_c_img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .offer-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(2, 6, 23, 0) 20%, rgba(2, 6, 23, .6) 100%);
            z-index: 1;
        }

        .offer-body {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 22px;
            flex: 1;
        }

        .offer-title {
            font-family: 'Jost', sans-serif;
            font-size: 1.35rem;
            line-height: 1.2;
            margin: 0;
        }

        .offer-title a {
            color: #0b104a;
            text-decoration: none;
        }

        .offer-title a:hover {
            color: #525fe1;
        }

        .offer-meta {
            display: grid;
            gap: 8px;
            color: #5b6478;
            font-size: .96rem;
        }

        .offer-meta p {
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .offer-actions {
            margin-top: auto;
            display: grid;
            gap: 10px;
            padding-top: 8px;
        }

        .offer-actions .btn-outline-primary {
            border-color: rgba(82, 95, 225, .25);
            color: #4250d8;
        }

        .empty-state {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .07);
            padding: 24px;
        }

        @media (max-width: 991.98px) {
            .hero-offer {
                padding-top: 72px;
            }

            .hero-panel {
                padding: 26px;
            }

            .hero-stats {
                grid-template-columns: 1fr;
            }

            .filter-shell {
                margin-top: 0;
            }
        }

        @media (max-width: 767.98px) {
            .single_c_img {
                height: 200px;
            }

            .filter-card .card-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body data-spy="scroll" data-offset="80">
    <div class="preloaders">
        <span class="loader"></span>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>

    <section class="hero-offer page-surface">
        <div class="container">
            <div class="hero-panel">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="eyebrow"><i class="fa-solid fa-briefcase"></i> Offres d'emploi disponibles</div>
                        <h1 class="hero-title">Des opportunités claires, avec un parcours de candidature direct.</h1>
                        <p class="hero-copy">
                            Explorez les postes ouverts, comparez les détails importants et accédez en un clic au formulaire de candidature.
                            Le design met en avant les offres actives et garde les actions essentielles visibles sans surcharge.
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="#offres" class="btn_one">Voir les offres</a>
                        </div>
                        <div class="hero-stats">
                            <div class="stat-card">
                                <strong><?= count($offres) ?></strong>
                                <span>offres affichées</span>
                            </div>
                            <div class="stat-card">
                                <strong><?= (int) $activeCount ?></strong>
                                <span>offres ouvertes</span>
                            </div>
                            <div class="stat-card">
                                <strong>1 clic</strong>
                                <span>vers la candidature</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-none d-lg-block">
                        <div class="hero-panel" style="padding: 18px; background: rgba(255,255,255,.56); box-shadow:none; border-radius: 22px;">
                            <img src="assets/img/course/1.png" alt="Aperçu offre" class="img-fluid rounded-4 shadow-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="offres" class="section-padding filter-shell">
        <div class="container">
            <form method="get" action="index.php" class="mb-4">
                <input type="hidden" name="espace" value="front">
                <input type="hidden" name="module" value="offreemploi">
                <input type="hidden" name="action" value="liste">

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card filter-card h-100">
                            <div class="card-body">
                                <div class="filter-title">Recherche globale</div>
                                <label class="form-label" for="q">Texte à rechercher</label>
                                <input
                                    type="text"
                                    id="q"
                                    name="q"
                                    class="form-control form-control-lg"
                                    value="<?= htmlspecialchars((string) ($filterState['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="Titre, lieu, contrat, statut..."
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card filter-card h-100">
                            <div class="card-body">
                                <div class="filter-title">Tri</div>
                                <label class="form-label" for="sort_by">Trier par</label>
                                <select id="sort_by" name="sort_by" class="form-select form-select-lg mb-3">
                                    <?php $selectedSortBy = (string) ($filterState['sort_by'] ?? 'date_creation'); ?>
                                    <option value="titre" <?= $selectedSortBy === 'titre' ? 'selected' : '' ?>>Titre</option>
                                    <option value="lieu" <?= $selectedSortBy === 'lieu' ? 'selected' : '' ?>>Lieu</option>
                                    <option value="type_contrat" <?= $selectedSortBy === 'type_contrat' ? 'selected' : '' ?>>Type de contrat</option>
                                    <option value="datecreation" <?= $selectedSortBy === 'date_creation' ? 'selected' : '' ?>>Date de création</option>
                                    <option value="datelimite" <?= $selectedSortBy === 'date_limite' ? 'selected' : '' ?>>Date limite</option>
                                    <option value="statut" <?= $selectedSortBy === 'statut' ? 'selected' : '' ?>>Statut</option>
                                </select>

                                <?php $selectedSortDir = (string) ($filterState['sort_dir'] ?? 'desc'); ?>
                                <div class="radio-inline-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sort_dir" id="sort_dir_asc" value="asc" <?= $selectedSortDir === 'asc' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sort_dir_asc">Croissant</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sort_dir" id="sort_dir_desc" value="desc" <?= $selectedSortDir !== 'asc' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sort_dir_desc">Décroissant</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3 flex-wrap">
                    <button type="submit" class="btn btn-primary">Appliquer</button>
                    <a class="btn btn-outline-secondary" href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste">Réinitialiser</a>
                </div>
            </form>

            <div class="d-flex justify-content-end mb-4">
                <span id="offre-count-badge" class="badge bg-primary"><?= count($offres) ?> offre(s)</span>
            </div>

            <div class="row offer-grid">
                <?php if (empty($offres)): ?>
                    <div class="col-12">
                        <div class="empty-state text-center">
                            <h3 class="mb-2">Aucune offre disponible pour le moment</h3>
                            <p class="text-secondary mb-0">Revenez plus tard ou réinitialisez les filtres pour afficher les dernières opportunités.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($offres as $index => $offre): ?>
                        <div class="col-lg-4 col-md-6 col-sm-12 offre-card mb-4">
                            <article class="single_course offer-card">
                                <div class="single_c_img">
                                    <img src="<?= htmlspecialchars($images[$index % count($images)], ENT_QUOTES, 'UTF-8') ?>" class="img-fluid" alt="offre-image">
                                    <div class="offer-overlay"></div>
                                </div>
                                <div class="offer-body">
                                    <h3 class="offer-title">
                                        <a href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=details&id=<?= (int) $offre['id'] ?>">
                                            <?= htmlspecialchars((string) $offre['titre']) ?>
                                        </a>
                                    </h3>
                                    <div class="offer-meta">
                                        <p><span class="ti-location-pin"></span> <?= htmlspecialchars((string) $offre['lieu']) ?></p>
                                        <p><span class="ti-briefcase"></span> <?= htmlspecialchars((string) $offre['type_contrat']) ?></p>
                                        <p><span class="ti-calendar"></span> Date limite: <?= htmlspecialchars($formatDateTime($offre['date_limite'])) ?></p>
                                    </div>
                                    <div class="offer-actions">
                                        <a href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=details&id=<?= (int) $offre['id'] ?>" class="btn_one text-center">Voir détails</a>
                                        <?php if (($offre['statut'] ?? '') === 'ouverte'): ?>
                                            <a href="/gestion_users/controller/CandidatureController.php?espace=front&action=ajouter&offre=<?= (int) $offre['id'] ?>" class="btn btn-outline-primary">Candidater</a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary" disabled>Offre fermée</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
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

    <script src="assets/js/jquery-1.12.4.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/owlcarousel/js/owl.carousel.min.js"></script>
    <script src="assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/jquery.inview.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/modernizr-2.8.3.min.js"></script>
    <script src="assets/js/scrolltopcontrol.js"></script>
    <script src="assets/js/superMarquee.min.js"></script>
    <script src="assets/js/scripts.js"></script>
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

            function updateList() {
                const query = normalize(searchInput.value.trim());
                let visibleCount = 0;

                offerCards.forEach((card) => {
                    const text = normalize(card.textContent || '');
                    const visible = query === '' || text.includes(query);
                    card.style.display = visible ? '' : 'none';
                    if (visible) {
                        visibleCount += 1;
                    }
                });

                if (countBadge) {
                    countBadge.textContent = `${visibleCount} offre(s)`;
                }

                if (noResultAlert) {
                    noResultAlert.classList.toggle('d-none', visibleCount !== 0);
                }
            }

            searchInput.addEventListener('input', updateList);
            updateList();
        })();
    </script>
</body>
</html>
