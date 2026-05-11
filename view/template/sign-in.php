<?php session_start();
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['form_data']);
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Connexion | EduMatch</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="../../assets/fonts/font-awesome.min.css" />
    <link rel="stylesheet" href="../../assets/fonts/themify-icons.css" />
    <link rel="stylesheet" href="../../assets/owlcarousel/css/owl.carousel.css" />
    <link rel="stylesheet" href="../../assets/owlcarousel/css/owl.theme.css" />
    <link rel="stylesheet" href="../../assets/css/jquery-simple-mobilemenu.css" />
    <link rel="stylesheet" href="../../assets/css/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/css/animate.css" />
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <style>
      
      
      
      
      
      .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
      .auth-section { min-height: 70vh; display: flex; align-items: center; padding: 80px 0 60px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
      .auth-card { background: white; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); padding: 40px; max-width: 480px; margin: 0 auto; }
      .auth-card h2 { font-weight: 700; margin-bottom: 10px; }
      .auth-card .form-control { border-radius: 10px; padding: 12px 16px; }
      .auth-card .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); border: none; border-radius: 10px; padding: 12px; font-weight: 600; width: 100%; }
      .auth-card .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
      .auth-card a { color: #525fe1; }
      .auth-card a:hover { color: #764ba2; }
    </style>
  </head>

  <body>
    <!-- PRELOADER -->
    <div class="preloaders"><span class="loader"></span></div>

    <?php include __DIR__ . '/_navbar.php'; ?>

    <!-- SIGN IN FORM -->
    <section class="auth-section">
      <div class="container">
        <div class="auth-card">
          <h2 class="text-center"><i class="fas fa-sign-in-alt me-2"></i>Connexion</h2>
          <p class="text-muted text-center mb-4">Accédez à votre compte EduMatch</p>

          <?php if ($success): ?>
          <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
          <?php endif; ?>
          <?php if ($errors): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
          <?php endif; ?>

          <form id="formLogin" action="/gestion_users/auth/doLogin" method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Email</label>
              <input type="text" class="form-control" id="loginEmail" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" placeholder="exemple@email.com">
              <div class="text-danger small mt-1" id="err-email"></div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Mot de passe</label>
              <div class="position-relative">
                <input type="password" class="form-control pe-5" id="loginPassword" name="password" placeholder="Votre mot de passe">
                <span class="toggle-password" data-target="loginPassword"><i class="fas fa-eye"></i></span>
              </div>
              <div class="text-danger small mt-1" id="err-password"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
              <a href="forget-password.php" class="text-decoration-none small">Mot de passe oublié ?</a>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt me-2"></i>Se connecter</button>
          </form>
          <p class="text-center mt-4 mb-0">Pas encore de compte ? <a href="sign-up.php" class="fw-semibold text-decoration-none">Créer un compte</a></p>
        </div>
      </div>
    </section>
    <!-- END SIGN IN -->

    <!-- FOOTER -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <a href="index.php" class="text-decoration-none"><img src="../../assets/img/logo.png" alt="EduMatch" class="mb-3" style="height: 50px;"></a>
            <p class="mt-3 text-light opacity-75">Plateforme intelligente connectant les etudiants avec des professeurs experts pour un apprentissage personnalise.</p>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Navigation</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php" class="text-light text-decoration-none">Accueil</a></li>
              <li class="mb-2"><a href="sign-in.php" class="text-light text-decoration-none">Connexion</a></li>
              <li class="mb-2"><a href="sign-up.php" class="text-light text-decoration-none">Inscription</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Contact</h5>
            <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Tunisia, Tunis</p>
            <p class="mb-2"><i class="fas fa-phone me-2"></i>+216 90 549 254</p>
            <p class="mb-2"><i class="fas fa-envelope me-2"></i>edumatch@gmail.com</p>
          </div>
        </div>
        <hr class="my-4 opacity-25">
        <p class="text-center text-light opacity-75 mb-0">&copy; 2026 EduMatch. Tous droits reserves.</p>
      </div>
    </footer>
    <!-- END FOOTER -->

    <script src="../../assets/js/jquery-1.12.4.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../assets/js/modernizr-2.8.3.min.js"></script>
    <script src="../../assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="../../assets/owlcarousel/js/owl.carousel.min.js"></script>
    <script src="../../assets/js/jquery.magnific-popup.min.js"></script>
    <script src="../../assets/js/jquery.inview.min.js"></script>
    <script src="../../assets/js/scrolltopcontrol.js"></script>
    <script src="../../assets/js/wow.min.js"></script>
    <script src="../../assets/js/scripts.js"></script>
    <script src="../../assets/js/validation.js"></script>
  </body>
</html>
