<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Eduleb - Education Platform">
    <meta name="keywords" content="courses, education, learning">		
    <title>Our Courses - Eduleb</title>			
    <link rel="stylesheet" href="/gestion_users/assets/bootstrap/css/bootstrap.min.css">		
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/gestion_users/assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="/gestion_users/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="/gestion_users/assets/owlcarousel/css/owl.carousel.css">
    <link rel="stylesheet" href="/gestion_users/assets/owlcarousel/css/owl.theme.css">	
    <link rel="stylesheet" href="/gestion_users/assets/css/jquery-simple-mobilemenu.css">			
    <link rel="stylesheet" href="/gestion_users/assets/css/magnific-popup.css">		
    <link rel="stylesheet" href="/gestion_users/assets/css/animate.css">	
    <link rel="stylesheet" href="/gestion_users/assets/css/style.css">
    
    <style>
        .courses_section {
            padding: 80px 0;
        }
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-title h2 {
            font-size: 40px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1e293b;
        }
        .section-title p {
            font-size: 16px;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        .course-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        .course-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .course-card-img {
            height: 250px;
            object-fit: cover;
        }
        .course-card .card-body {
            padding: 25px;
        }
        .course-card .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1e293b;
            line-height: 1.4;
        }
        .course-card .card-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
            min-height: 60px;
        }
        .course-level {
            display: inline-block;
            padding: 6px 12px;
            background: #f0f4ff;
            color: #525fe1;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .btn-view-course {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view-course:hover {
            transform: translateX(5px);
            color: white;
            text-decoration: none;
        }
        .no-courses {
            text-align: center;
            padding: 60px 20px;
        }
        .no-courses h3 {
            color: #1e293b;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .no-courses p {
            color: #666;
        }
        .courses-toolbar {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: flex-end;
            margin: 0 0 22px;
            flex-wrap: wrap;
        }
        .courses-toolbar .search-wrap {
            flex: 1;
            max-width: 520px;
            min-width: 260px;
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
        }
        .courses-toolbar .search-input {
            border-radius: 10px;
            border: 1px solid rgba(30,41,59,0.15);
            padding: 10px 14px;
            width: 100%;
            height: 44px;
        }
        .courses-toolbar .btn-search {
            border-radius: 10px;
            border: 0;
            padding: 10px 14px;
            font-weight: 700;
            background: rgba(82, 95, 225, 0.12);
            color: #525fe1;
            transition: 0.2s ease;
            height: 44px;
            min-width: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }
        .courses-toolbar .btn-search:hover {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: #fff;
            transform: translateY(-1px);
        }
        .courses-toolbar label {
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            white-space: nowrap;
        }
        .courses-toolbar .form-select {
            max-width: 320px;
            border-radius: 10px;
            border: 1px solid rgba(30,41,59,0.15);
            padding: 10px 14px;
            height: 44px;
            min-width: 320px;
        }
        @media (max-width: 991.98px) {
            .courses-toolbar {
                justify-content: flex-start;
            }
            .courses-toolbar .form-select {
                min-width: 260px;
                max-width: 100%;
            }
        }
        @media (max-width: 575.98px) {
            .courses-toolbar {
                gap: 10px;
            }
            .courses-toolbar .search-wrap {
                max-width: 100%;
            }
        }
        .course-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .btn-course-pdf {
            background: #fff;
            border: 1px solid rgba(239,68,68,0.35);
            color: #ef4444;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 700;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-course-pdf:hover {
            transform: translateX(5px);
            color: #fff;
            background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%);
            text-decoration: none;
        }
    </style>
</head>

<body data-spy="scroll" data-offset="80">
    <div class="preloaders"><span class="loader"></span></div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>

    <section class="home_bg hb_height" style="background-image: url(/gestion_users/assets/img/bg/home-bg.jpg); background-size: cover; background-position: center center;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-sm-12 col-xs-12">
                    <div class="hero-text ht_top">
                        <h1><span>Explore</span> Our Courses & Start Learning</h1>
                        <p>Discover a wide range of courses designed to help you learn new skills and advance your career</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="courses_section">
        <div class="container">
            <div class="section-title">
                <h2>Featured Courses</h2>
                <p>Choose from our collection of carefully curated courses that will help you master new skills</p>
            </div>

            <form method="get" class="courses-toolbar">
                <input type="hidden" name="route" value="frontoffice/courses/index">
                <div class="search-wrap">
                    <input type="text" name="q" class="search-input" placeholder="Search by course name..." value="<?= htmlspecialchars((string) ($q ?? '')) ?>">
                    <button class="btn-search" type="submit" aria-label="Search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                    </button>
                </div>
                <label for="sort">Sort by</label>
                <select id="sort" name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="default" <?= ($sort ?? 'default') === 'default' ? 'selected' : '' ?>>Newest</option>
                    <option value="level" <?= ($sort ?? 'default') === 'level' ? 'selected' : '' ?>>Level (Beginner → Advanced) + Name (A → Z)</option>
                    <option value="name" <?= ($sort ?? 'default') === 'name' ? 'selected' : '' ?>>Course name (A → Z)</option>
                </select>
            </form>

            <?php if (!empty($courses)): ?>
                <div class="row">
                    <?php foreach ($courses as $c): ?>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4 wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.2s" data-wow-offset="0">
                            <div class="card course-card h-100">
                                <?php if (!empty($c['image'])): ?>
                                    <img src="<?= htmlspecialchars((string) $c['image']) ?>" class="card-img-top course-card-img" alt="<?= htmlspecialchars((string) $c['title']) ?>">
                                <?php else: ?>
                                    <div class="course-card-img" style="background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <?php
                                        $levelRaw = strtolower((string) ($c['level'] ?? 'beginner'));
                                        $levelLabel = match ($levelRaw) {
                                            'intermediate' => 'Intermediate',
                                            'advanced' => 'Advanced',
                                            default => 'Beginner',
                                        };
                                    ?>
                                    <span class="course-level"><?= htmlspecialchars($levelLabel) ?></span>
                                    <h5 class="card-title"><?= htmlspecialchars((string) $c['title']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars((string) (substr($c['description'] ?? '', 0, 100))) ?><?= strlen($c['description'] ?? '') > 100 ? '...' : '' ?></p>
                                    <div class="course-actions">
                                        <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => (int) $c['id']])); ?>" class="btn-view-course">
                                            <i class="fas fa-arrow-right"></i> View Course
                                        </a>
                                        <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'pdf', ['id' => (int) $c['id']])); ?>" class="btn-course-pdf">
                                            <i class="fa-regular fa-file-pdf"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <h3>No Courses Available</h3>
                    <p>Check back soon for new courses</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

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
	
    </div>

    <script src="/gestion_users/assets/js/jquery-1.12.4.min.js"></script>
    <script src="/gestion_users/assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="/gestion_users/assets/js/wow.min.js"></script>
    <script src="/gestion_users/assets/js/scripts.js"></script>

    <script>
        new WOW().init();
    </script>
</body>
</html>
