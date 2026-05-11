<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?= htmlspecialchars((string) (substr($lesson['summary'] ?? '', 0, 160))) ?>">
    <meta name="keywords" content="lesson, learning path, education">
    <title><?= htmlspecialchars((string) $lesson['title']) ?> - <?= htmlspecialchars((string) $course['title']) ?></title>
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
        .lesson-header {
            background: linear-gradient(135deg, #0f172a 0%, #4338ca 100%);
            color: white;
            padding: 60px 0;
            margin-top: 80px;
        }
        .lesson-header h1 {
            font-size: 44px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
        }
        .lesson-header p {
            font-size: 16px;
            max-width: 800px;
            opacity: 0.92;
            line-height: 1.7;
        }
        .lesson-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        .lesson-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 14px;
            font-weight: 600;
        }
        .lesson-page {
            padding: 80px 0;
        }
        .lesson-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 28px;
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            color: #1e293b;
            font-weight: 700;
            transition: 0.3s;
        }
        .lesson-back:hover {
            background: #f8fafc;
            text-decoration: none;
            color: #1e293b;
        }
        .lesson-article {
            background: white;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            padding: 34px;
            margin-bottom: 26px;
        }
        .lesson-article h2 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
        }
        .lesson-content {
            font-size: 16px;
            line-height: 1.9;
            color: #475569;
        }
        .lesson-path-card,
        .lesson-tools,
        .quiz-cta {
            background: white;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            padding: 26px;
            margin-bottom: 24px;
        }
        .lesson-path-card h3,
        .lesson-tools h3,
        .quiz-cta h3 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .path-steps {
            display: grid;
            gap: 12px;
        }
        .path-step {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px;
            border-radius: 12px;
            background: #f8fafc;
        }
        .path-step.active {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
        }
        .path-step-number {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #1e293b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .path-step.active .path-step-number {
            background: #4338ca;
        }
        .path-step a {
            color: #0f172a;
            font-weight: 700;
            text-decoration: none;
        }
        .path-step p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }
        .tool-row {
            display: grid;
            gap: 12px;
        }
        .tool-item {
            padding: 14px 16px;
            border-radius: 12px;
            background: #f8fafc;
        }
        .tool-item strong {
            display: block;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .tool-item span {
            color: #64748b;
            font-size: 14px;
        }
        .nav-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 28px;
        }
        .lesson-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 22px;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
        }
        .lesson-btn-primary {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
        }
        .lesson-btn-primary:hover {
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        .lesson-btn-secondary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #0f172a;
        }
        .lesson-btn-secondary:hover {
            text-decoration: none;
            color: #0f172a;
            background: #f1f5f9;
        }
        @media (max-width: 991px) {
            .lesson-header h1 { font-size: 34px; }
            .lesson-article,
            .lesson-path-card,
            .lesson-tools,
            .quiz-cta { padding: 24px; }
        }
    </style>
</head>
<body data-spy="scroll" data-offset="80">
    <div class="preloaders"><span class="loader"></span></div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>

    <section class="lesson-header">
        <div class="container">
            <h1><?= htmlspecialchars((string) $lesson['title']) ?></h1>
            <p><?= htmlspecialchars((string) ($lesson['summary'] ?? '')) ?></p>
            <div class="lesson-meta">
                <span><i class="fas fa-book"></i> <?= htmlspecialchars((string) $course['title']) ?></span>
                <span><i class="fas fa-road"></i> Step <?= (int) ($lesson['lesson_order'] ?? 1) ?> of <?= count($lessons) ?></span>
                <span><i class="fas fa-clock"></i> <?= (int) ($lesson['duration_minutes'] ?? 10) ?> minutes</span>
            </div>
        </div>
    </section>

    <section class="lesson-page">
        <div class="container">
            <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => (int) $course['id']])); ?>" class="lesson-back">
                <i class="fas fa-arrow-left"></i> Back to Course
            </a>

            <div class="row">
                <div class="col-lg-8">
                    <article class="lesson-article">
                        <h2>Lesson Content</h2>
                        <div class="lesson-content">
                            <?= nl2br(htmlspecialchars((string) ($lesson['content'] ?? ''))) ?>
                        </div>

                        <div class="nav-actions">
                            <?php if ($previousLesson): ?>
                                <a href="<?= htmlspecialchars(frontofficeRoute('lessons', 'show', ['id' => (int) $previousLesson['id']])); ?>" class="lesson-btn lesson-btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Previous Lesson
                                </a>
                            <?php endif; ?>

                            <?php if ($nextLesson): ?>
                                <a href="<?= htmlspecialchars(frontofficeRoute('lessons', 'show', ['id' => (int) $nextLesson['id']])); ?>" class="lesson-btn lesson-btn-primary">
                                    Next Lesson <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php elseif (!empty($quizzes)): ?>
                                <a href="<?= htmlspecialchars(frontofficeRoute('quizzes', 'take', ['id' => (int) $quizzes[0]['id']])); ?>" class="lesson-btn lesson-btn-primary">
                                    Start Quiz <i class="fas fa-play"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>

                <div class="col-lg-4">
                    <aside class="lesson-path-card">
                        <h3>Learning Path</h3>
                        <div class="path-steps">
                            <?php foreach ($lessons as $courseLesson): ?>
                                <?php $isActiveLesson = (int) $courseLesson['id'] === (int) $lesson['id']; ?>
                                <div class="path-step<?= $isActiveLesson ? ' active' : '' ?>">
                                    <div class="path-step-number"><?= (int) ($courseLesson['lesson_order'] ?? 1) ?></div>
                                    <div>
                                        <a href="<?= htmlspecialchars(frontofficeRoute('lessons', 'show', ['id' => (int) $courseLesson['id']])); ?>">
                                            <?= htmlspecialchars((string) $courseLesson['title']) ?>
                                        </a>
                                        <p><?= htmlspecialchars((string) ($courseLesson['summary'] ?? '')) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </aside>

                    <aside class="lesson-tools">
                        <h3>Lesson Details</h3>
                        <div class="tool-row">
                            <div class="tool-item">
                                <strong>Course</strong>
                                <span><?= htmlspecialchars((string) $course['title']) ?></span>
                            </div>
                            <div class="tool-item">
                                <strong>Duration</strong>
                                <span><?= (int) ($lesson['duration_minutes'] ?? 10) ?> minutes</span>
                            </div>
                            <div class="tool-item">
                                <strong>Current Step</strong>
                                <span><?= (int) ($lesson['lesson_order'] ?? 1) ?> / <?= count($lessons) ?></span>
                            </div>
                        </div>
                    </aside>

                    <?php if (!empty($quizzes)): ?>
                        <aside class="quiz-cta">
                            <h3>After This Lesson</h3>
                            <p style="color: #64748b; line-height: 1.7; margin-bottom: 16px;">When you finish the lesson path, test your understanding with the course quiz.</p>
                            <a href="<?= htmlspecialchars(frontofficeRoute('quizzes', 'take', ['id' => (int) $quizzes[0]['id']])); ?>" class="lesson-btn lesson-btn-primary" style="width: 100%;">
                                <i class="fas fa-play"></i> Open Quiz
                            </a>
                        </aside>
                    <?php endif; ?>
                </div>
            </div>
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

    <script src="/gestion_users/assets/js/jquery-1.12.4.min.js"></script>
    <script src="/gestion_users/assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="/gestion_users/assets/js/wow.min.js"></script>
    <script src="/gestion_users/assets/js/scripts.js"></script>
    <script>
        new WOW().init();
    </script>
</body>
</html>
