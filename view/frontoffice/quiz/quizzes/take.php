<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Take <?= htmlspecialchars((string) $quiz['title']) ?> quiz">		
    <title>Quiz: <?= htmlspecialchars((string) $quiz['title']) ?> - Eduleb</title>			
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
        .quiz-header {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            padding: 60px 0;
            margin-top: 80px;
        }
        .quiz-header h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .quiz-meta {
            display: flex;
            gap: 30px;
            font-size: 16px;
        }
        .quiz-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .quiz-container {
            padding: 60px 0;
        }
        .quiz-content {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        .question-card {
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid #e0e0e0;
        }
        .question-card:last-child {
            border-bottom: none;
        }
        .question-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            border-radius: 50%;
            font-weight: 700;
            margin-right: 12px;
            font-size: 14px;
        }
        .question-text {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 20px 0 25px 0;
            line-height: 1.6;
        }
        .answer-option {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .answer-option:hover {
            border-color: #525fe1;
            background: #f0f4ff;
        }
        .answer-option input[type="radio"],
        .answer-option input[type="checkbox"] {
            margin-right: 12px;
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: #525fe1;
        }
        .answer-option input[type="radio"]:checked + label,
        .answer-option input[type="checkbox"]:checked + label {
            color: #525fe1;
            font-weight: 600;
        }
        .answer-option input[type="radio"]:checked ~ *,
        .answer-option input[type="checkbox"]:checked ~ * {
            color: #525fe1;
        }
        .answer-option.checked {
            background: #e3f2fd;
            border-color: #525fe1;
        }
        .form-check {
            display: flex;
            align-items: center;
        }
        .form-check-label {
            margin-bottom: 0;
            cursor: pointer;
            flex: 1;
            padding: 0;
        }
        .quiz-actions {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            padding-top: 40px;
            border-top: 2px solid #e0e0e0;
        }
        .btn-submit {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            border: none;
            padding: 14px 40px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: 0.3s;
            cursor: pointer;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(82, 95, 225, 0.3);
            color: white;
            text-decoration: none;
        }
        .btn-back-quiz {
            background: white;
            color: #1e293b;
            border: 2px solid #e0e0e0;
            padding: 14px 30px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-back-quiz:hover {
            background: #f8f9fa;
            border-color: #525fe1;
            color: #525fe1;
            text-decoration: none;
        }
        .no-questions {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
        }
        .no-questions i {
            font-size: 42px;
            color: #ff9800;
            margin-bottom: 15px;
        }
        .progress-info {
            background: #f0f4ff;
            border-left: 4px solid #525fe1;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .progress-info p {
            margin: 0;
            color: #333;
        }
    </style>
</head>

<body data-spy="scroll" data-offset="80">
    <div class="preloaders"><span class="loader"></span></div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?> 	  

    <section class="quiz-header">
        <div class="container">
            <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => (int) $course['id']])); ?>" class="btn-back-quiz">
                <i class="fas fa-arrow-left"></i> Back to Course
            </a>
            <h1><?= htmlspecialchars((string) $quiz['title']) ?></h1>
            <div class="quiz-meta">
                <span>
                    <i class="fas fa-clock"></i>
                    <?= (int) ($quiz['duration_minutes'] ?? 30) ?> minutes
                </span>
                <span>
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars((string) ($quiz['passing_score'] ?? 50)) ?>% to pass
                </span>
                <span>
                    <i class="fas fa-list-ul"></i>
                    <?= count($questions ?? []) ?> question<?= count($questions ?? []) !== 1 ? 's' : '' ?>
                </span>
            </div>
        </div>
    </section>

    <section class="quiz-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php if (!empty($quiz['description'])): ?>
                        <div class="progress-info">
                            <p><strong>About this quiz:</strong> <?= htmlspecialchars((string) $quiz['description']) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($questions)): ?>
                        <div class="no-questions">
                            <i class="fas fa-inbox"></i>
                            <h3 style="color: #ff9800; margin: 15px 0 10px 0;">No Questions Available</h3>
                            <p style="color: #666; margin: 0;">This quiz doesn't have any questions yet. Please try another quiz.</p>
                        </div>
                    <?php else: ?>
                        <div class="quiz-content">
                            <form method="post" action="<?= htmlspecialchars(frontofficeRoute('quizzes', 'submit')); ?>" id="quizForm">
                                <input type="hidden" name="quiz_id" value="<?= (int) $quiz['id'] ?>">

                                <?php foreach ($questions as $index => $q): ?>
                                    <div class="question-card">
                                        <div style="display: flex; align-items: start;">
                                            <span class="question-number"><?= ($index + 1) ?></span>
                                            <div class="question-text"><?= htmlspecialchars((string) $q['question_text']) ?></div>
                                        </div>

                                        <div style="margin-left: 47px;">
                                            <?php if (($q['question_type'] ?? 'single_choice') === 'multiple_choice'): ?>
                                                <?php foreach (($q['responses'] ?? []) as $r): ?>
                                                    <label class="answer-option">
                                                        <input type="checkbox" name="answers[<?= (int) $q['id'] ?>][]" value="<?= (int) $r['id'] ?>" class="form-check-input">
                                                        <label for="r<?= (int) $r['id'] ?>" style="margin: 0; flex: 1; padding-left: 0;" class="form-check-label">
                                                            <?= htmlspecialchars((string) $r['response_text']) ?>
                                                        </label>
                                                    </label>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?php foreach (($q['responses'] ?? []) as $r): ?>
                                                    <label class="answer-option">
                                                        <input type="radio" name="answers[<?= (int) $q['id'] ?>]" value="<?= (int) $r['id'] ?>" class="form-check-input">
                                                        <label for="r<?= (int) $r['id'] ?>" style="margin: 0; flex: 1; padding-left: 0;" class="form-check-label">
                                                            <?= htmlspecialchars((string) $r['response_text']) ?>
                                                        </label>
                                                    </label>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="quiz-actions">
                                    <button class="btn-submit" type="submit">
                                        <i class="fas fa-paper-plane"></i> Submit Quiz
                                    </button>
                                    <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => (int) $course['id']])); ?>" class="btn-back-quiz">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
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
