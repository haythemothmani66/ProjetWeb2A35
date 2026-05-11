<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Your quiz result for <?= htmlspecialchars((string) $quiz['title']) ?>">		
    <title>Quiz Result - Eduleb</title>			
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
        body {
            background: #f8f9fa;
        }
        .result-header {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            padding: 60px 0;
            margin-top: 80px;
        }
        .result-header h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .result-container {
            padding: 60px 0;
        }
        .result-card {
            background: white;
            border-radius: 12px;
            padding: 60px 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .result-icon {
            font-size: 80px;
            margin-bottom: 30px;
        }
        .result-icon.passed {
            color: #4caf50;
        }
        .result-icon.failed {
            color: #f44336;
        }
        .result-message {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .result-message.passed {
            color: #4caf50;
        }
        .result-message.failed {
            color: #f44336;
        }
        .score-display {
            margin: 40px 0;
        }
        .score-large {
            font-size: 64px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
        }
        .score-label {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        .result-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            margin: 40px 0;
            text-align: left;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #1e293b;
        }
        .detail-value {
            font-size: 18px;
            font-weight: 700;
            color: #525fe1;
        }
        .result-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 14px 30px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
        }
        .btn-try-again {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
        }
        .btn-try-again:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(82, 95, 225, 0.3);
            color: white;
            text-decoration: none;
        }
        .btn-back {
            background: white;
            color: #1e293b;
            border: 2px solid #e0e0e0;
        }
        .btn-back:hover {
            background: #f8f9fa;
            border-color: #525fe1;
            color: #525fe1;
            text-decoration: none;
        }
        .btn-back-header {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            transition: 0.3s;
            margin-bottom: 30px;
        }
        .btn-back-header:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        /* Recommendation Section Styles */
        .recommendation-box {
            background: #fff;
            border: 1px solid #e0e7ff;
            border-radius: 16px;
            padding: 30px;
            margin-top: 50px;
            text-align: left;
            border-left: 6px solid #525fe1;
        }
        .recommendation-box h3 {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .recommendation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        .resource-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            transition: 0.3s;
            border: 1px solid #f1f5f9;
            text-decoration: none !important;
            display: block;
        }
        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            background: white;
            border-color: #525fe1;
        }
        .resource-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .resource-title {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
            display: block;
        }
        .resource-desc {
            font-size: 13px;
            color: #64748b;
        }
    </style>
</head>

<body data-spy="scroll" data-offset="80">
    <div class="preloaders"><span class="loader"></span></div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?> 	  

    <?php $percent = (float) ($result['percent'] ?? 0); ?>
    <?php $passed = (bool) ($result['passed'] ?? false); ?>

    <section class="result-header">
        <div class="container">
            <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => (int) $course['id']])); ?>" class="btn-back-header">
                <i class="fas fa-arrow-left"></i> Back to Course
            </a>
            <h1>Quiz Results</h1>
            <p><?= htmlspecialchars((string) $quiz['title']) ?></p>
        </div>
    </section>

    <section class="result-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="result-card">
                        <div class="result-icon <?= $passed ? 'passed' : 'failed' ?>">
                            <?php if ($passed): ?>
                                <i class="fas fa-check-circle"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i>
                            <?php endif; ?>
                        </div>

                        <div class="result-message <?= $passed ? 'passed' : 'failed' ?>">
                            <?= $passed ? 'Congratulations!' : 'Keep Learning' ?>
                        </div>

                        <div class="score-label">
                            <?= $passed ? 'You passed the quiz!' : 'You didn\'t pass this time. Try again!' ?>
                        </div>

                        <div class="score-display">
                            <div class="score-large">
                                <?= number_format($percent, 1) ?>%
                            </div>
                            <div class="score-label">
                                Your Score
                            </div>
                        </div>

                        <div class="result-details">
                            <div class="detail-item">
                                <span class="detail-label">
                                    <i class="fas fa-star" style="color: #ffc107;"></i> Total Score
                                </span>
                                <span class="detail-value">
                                    <?= number_format((float) ($result['earned'] ?? 0), 2) ?> / <?= number_format((float) ($result['total'] ?? 0), 2) ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">
                                    <i class="fas fa-bullseye" style="color: #2196f3;"></i> Passing Score
                                </span>
                                <span class="detail-value">
                                    <?= htmlspecialchars((string) ($quiz['passing_score'] ?? 50)) ?>%
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">
                                    <i class="fas fa-flag" style="color: #ff9800;"></i> Status
                                </span>
                                <span class="detail-value">
                                    <?= $passed ? 'ÃƒÂ¢Ã…â€œÃ¢â‚¬Å“ Passed' : 'ÃƒÂ¢Ã…â€œÃ¢â‚¬â€ Failed' ?>
                                </span>
                            </div>
                        </div>

                        <div class="result-actions">
                            <a href="<?= htmlspecialchars(frontofficeRoute('quizzes', 'take', ['id' => (int) $quiz['id']])); ?>" class="btn-action btn-try-again">
                                <i class="fas fa-redo"></i> Try Again
                            </a>
                            <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => (int) $course['id']])); ?>" class="btn-action btn-back">
                                <i class="fas fa-book"></i> Back to Course
                            </a>
                        </div>

                        <?php if ($passed): ?>
                        <div class="mt-5 p-4" style="background: #e8f5e9; border-radius: 8px; border-left: 4px solid #4caf50;">
                            <h4 style="color: #2e7d32; margin-bottom: 15px;"><i class="fas fa-award"></i> Claim Your Certificate</h4>
                            <p style="color: #1b5e20;">You've successfully passed the quiz! Enter your name below to generate your certificate.</p>
                            <form action="<?= htmlspecialchars(frontofficeRoute('certificates', 'generate')); ?>" method="POST" class="d-flex align-items-center justify-content-center gap-2 mt-3" style="flex-wrap: wrap;">
                                <input type="hidden" name="quiz_id" value="<?= (int) $quiz['id']; ?>">
                                <input type="text" name="student_name" placeholder="Enter your full name" required class="form-control" style="max-width: 300px; padding: 12px; border-radius: 6px; border: 1px solid #c8e6c9;">
                                <button type="submit" class="btn-action" style="background: #4caf50; color: white; border: none;">
                                    <i class="fas fa-certificate"></i> Generate Certificate
                                </button>
                            </form>
                        </div>
                        <?php elseif ($percent < 50): ?>
                        <!-- Recommendation System for Score < 50% -->
                        <?php 
                            $query = urlencode($course['title'] ?? $quiz['title']);
                            $wikiUrl = "https://en.wikipedia.org/wiki/Special:Search?search=" . $query;
                            $youtubeUrl = "https://www.youtube.com/results?search_query=" . $query . "+tutorial";
                            $docsUrl = "https://www.google.com/search?q=" . $query . "+documentation+tutorial";
                        ?>
                        <div class="recommendation-box animate__animated animate__fadeInUp">
                            <h3><i class="fas fa-lightbulb" style="color: #f59e0b;"></i> Recommended Resources for You</h3>
                            <p class="text-muted">Don't worry! Everyone starts somewhere. We've gathered these resources to help you master <strong><?= htmlspecialchars($course['title'] ?? 'this topic') ?></strong>:</p>
                            
                            <div class="recommendation-grid">
                                <a href="<?= $youtubeUrl ?>" target="_blank" class="resource-card">
                                    <div class="resource-icon" style="background: #fee2e2; color: #ef4444;">
                                        <i class="fab fa-youtube"></i>
                                    </div>
                                    <span class="resource-title">YouTube Tutorials</span>
                                    <span class="resource-desc">Visual lessons and deep-dives into the subject.</span>
                                </a>

                                <a href="<?= $wikiUrl ?>" target="_blank" class="resource-card">
                                    <div class="resource-icon" style="background: #f1f5f9; color: #1e293b;">
                                        <i class="fab fa-wikipedia-w"></i>
                                    </div>
                                    <span class="resource-title">Wikipedia Guide</span>
                                    <span class="resource-desc">Comprehensive theoretical background and definitions.</span>
                                </a>

                                <a href="<?= $docsUrl ?>" target="_blank" class="resource-card">
                                    <div class="resource-icon" style="background: #e0f2fe; color: #0284c7;">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <span class="resource-title">Web Resources</span>
                                    <span class="resource-desc">Search for top-rated articles and documentation.</span>
                                </a>
                            </div>
                            
                            <div class="mt-4 p-3 bg-light rounded" style="border-left: 3px solid #6366f1;">
                                <small><strong>Pro Tip:</strong> Re-watching the course lessons or trying the "Battle vs AI" mode can also help strengthen your knowledge!</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
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
