<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Candidature - Espace Candidat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --ink: #12202f;
            --muted: #587089;
            --primary: #0f6e8b;
            --primary-dark: #0a4f66;
            --accent: #f3a712;
            --bg: #f3f7fb;
            --card: rgba(255,255,255,.9);
            --shadow: 0 16px 40px rgba(16, 33, 52, .12);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 12% 12%, rgba(15,110,139,.18), transparent 28%),
                radial-gradient(circle at 88% 0%, rgba(243,167,18,.18), transparent 30%),
                linear-gradient(145deg, #eef4f9 0%, #f9fbfd 45%, #edf4fb 100%);
        }
        .site-navigation {
            background: rgba(10, 20, 30, .82);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .site-navigation a { color: #fff; text-decoration: none; }
        .site-logo { display:flex; align-items:center; gap:12px; padding: 18px 0; }
        .site-logo .brand-dot {
            width: 42px; height: 42px; border-radius: 14px;
            background: linear-gradient(135deg, var(--accent), #ff7a59);
            display:flex; align-items:center; justify-content:center;
            font-weight: 800; color: #111;
        }
        #main-menu ul { list-style:none; margin:0; padding:0; display:flex; gap:24px; align-items:center; }
        #main-menu a { color: rgba(255,255,255,.88); font-weight:600; }
        #main-menu a:hover { color: #fff; }
        .top-hero {
            padding: 78px 0 54px;
        }
        .top-hero .title-wrap {
            max-width: 860px;
            margin: 0 auto;
            text-align: center;
        }
        .eyebrow {
            display:inline-flex; align-items:center; gap:8px;
            padding: 8px 14px; border-radius: 999px;
            background: rgba(15,110,139,.10); color: var(--primary-dark);
            font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
            font-size: .75rem;
        }
        .top-hero h1 {
            margin: 18px 0 12px;
            font-family: 'Jost', sans-serif;
            font-weight: 800;
            font-size: clamp(2rem, 3vw + 1rem, 4rem);
            letter-spacing: -.02em;
        }
        .top-hero p { color: var(--muted); font-size: 1.05rem; max-width: 760px; margin: 0 auto; }
        .section-block { padding: 18px 0 72px; }
        .offer-card {
            height: 100%;
            border: 1px solid rgba(18, 32, 47, .08);
            border-radius: 24px;
            background: var(--card);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .offer-card:hover { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(16,33,52,.16); }
        .offer-head {
            padding: 22px 22px 0;
            display:flex; justify-content:space-between; align-items:flex-start; gap:14px;
        }
        .offer-icon {
            width: 54px; height: 54px; border-radius: 18px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color:#fff; display:flex; align-items:center; justify-content:center; flex: 0 0 auto;
            box-shadow: 0 12px 26px rgba(15,110,139,.22);
        }
        .offer-card h3 {
            margin: 0;
            font-family: 'Jost', sans-serif;
            font-size: 1.22rem;
            font-weight: 700;
        }
        .offer-meta { color: var(--muted); font-size: .95rem; margin-top: 8px; }
        .offer-body { padding: 18px 22px 22px; }
        .mini-row { display:flex; flex-wrap:wrap; gap:10px; margin: 16px 0 20px; }
        .mini-pill {
            display:inline-flex; align-items:center; gap:8px;
            padding: 8px 12px; border-radius: 999px; background: #eef4f8; color: #274055;
            font-size: .88rem; font-weight: 600;
        }
        .status-badge {
            display:inline-flex; align-items:center; padding: 6px 10px; border-radius: 999px;
            font-size: .72rem; font-weight: 800; letter-spacing:.05em; text-transform: uppercase;
        }
        .status-open { background: rgba(31,146,84,.14); color: #15784a; }
        .status-closed { background: rgba(194,59,69,.14); color: #9c2531; }
        .btn-job {
            border: 0; border-radius: 999px; padding: 12px 18px; font-weight: 700;
            text-decoration:none; display:inline-flex; align-items:center; justify-content:center;
        }
        .btn-primary-job { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color:#fff; }
        .btn-secondary-job { background: #e9eff4; color:#173042; }
        .alert-soft {
            border: 1px solid rgba(15,110,139,.14);
            background: rgba(255,255,255,.82);
            border-radius: 18px;
            padding: 20px;
            box-shadow: var(--shadow);
        }
        .footer-default {
            padding: 24px 0 36px;
            color: var(--muted);
        }
        @media (max-width: 991.98px) {
            #main-menu ul { gap: 14px; flex-wrap: wrap; justify-content: center; }
            .site-navigation .row { gap: 10px; }
        }
        @media (max-width: 767.98px) {
            .top-hero { padding-top: 56px; }
            .offer-head { padding: 18px 18px 0; }
            .offer-body { padding: 16px 18px 18px; }
        }
    </style>
</head>
<body>
    <div id="navigation" class="site-navigation">
        <div class="container-fluid px-4">
            <div class="row align-items-center py-2">
                <div class="col-lg-3 col-8">
                    <div class="site-logo">
                        <div class="brand-dot">PW</div>
                        <div>
                            <div class="text-white fw-bold">ProjetWeb2A35</div>
                            <small class="text-white-50">Candidature espace candidat</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block text-center">
                    <nav id="main-menu">
                        <ul>
                            <li><a href="index.php?espace=front&module=candidature&action=liste">Candidature</a></li>
                            <li><a href="index.php?espace=front&module=offreemploi&action=liste">Offres d'emploi</a></li>
                            <li><a href="index.php?espace=back&module=candidature&action=liste">Administration</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-lg-3 col-4 text-end">
                    <a href="index.php?espace=back&module=candidature&action=liste" class="btn-job btn-secondary-job">Administration</a>
                </div>
            </div>
        </div>
    </div>

    <section class="top-hero">
        <div class="container">
            <div class="title-wrap">
                <span class="eyebrow"><i class="fa-solid fa-paper-plane"></i> Postulez en un clic</span>
                <h1>Choisissez une offre ouverte et envoyez votre candidature</h1>
                <p>Parcourez les offres d'emploi actuellement ouvertes, consultez les informations essentielles et accedez directement au formulaire avec l'offre deja selectionnee.</p>
            </div>
        </div>
    </section>

    <section class="section-block">
        <div class="container">
            <?php if (empty($offres)): ?>
                <div class="alert-soft text-center">
                    <h3 class="h5 mb-2">Aucune offre ouverte pour le moment</h3>
                    <p class="mb-0">Aucune offre n'est actuellement ouverte, le formulaire de candidature ne peut pas etre demarre.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($offres as $offre): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="offer-card">
                                <div class="offer-head">
                                    <div>
                                        <span class="status-badge status-open">Ouverte</span>
                                        <h3 class="mt-3"><?= htmlspecialchars((string) $offre['titre']) ?></h3>
                                        <div class="offer-meta"><?= htmlspecialchars((string) $offre['lieu']) ?> · <?= htmlspecialchars((string) $offre['typecontrat']) ?></div>
                                    </div>
                                    <div class="offer-icon"><i class="fa-solid fa-briefcase"></i></div>
                                </div>
                                <div class="offer-body">
                                    <div class="mini-row">
                                        <span class="mini-pill"><i class="fa-regular fa-calendar"></i> Date limite: <?= htmlspecialchars((string) $offre['datelimite']) ?></span>
                                        <?php if (!empty($offre['salairemin']) || !empty($offre['salairemax'])): ?>
                                            <span class="mini-pill"><i class="fa-solid fa-sack-dollar"></i> Salaire</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a class="btn-job btn-primary-job" href="index.php?espace=front&module=candidature&action=ajouter&offreid=<?= (int) $offre['id'] ?>">Postuler maintenant</a>
                                        <a class="btn-job btn-secondary-job" href="index.php?espace=front&module=offreemploi&action=details&id=<?= (int) $offre['id'] ?>">Voir l'offre</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer-default">
        <div class="container text-center">
            <p class="mb-0">Module Candidature pour ProjetWeb2A35</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
