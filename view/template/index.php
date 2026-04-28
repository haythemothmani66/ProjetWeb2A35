<?php
session_start();
/* Auto-refresh session data if user_prenom is missing (old session) */
if (!empty($_SESSION['user_id']) && empty($_SESSION['user_prenom'])) {
    require_once __DIR__ . '/../../config/database.php';
    $stmt = Config::getConnexion()->prepare("SELECT nom, prenom, photo FROM user WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['user_nom']    = $u['nom'];
        $_SESSION['user_prenom'] = $u['prenom'];
        $_SESSION['user_photo']  = $u['photo'];
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>EduMatch - Education Platform</title>
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
      .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; text-align: center; border: none; cursor: pointer; }
      .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
      /* User dropdown navbar */
      .user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; }
      .user-dropdown .user-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #525fe1; }
      .user-dropdown .user-name { font-weight: 600; font-size: 14px; color: #0b104a; white-space: nowrap; }
      .user-dropdown .dropdown-caret { font-size: 10px; color: #6c757d; transition: transform 0.2s; }
      .user-dropdown:hover .dropdown-caret { transform: rotate(180deg); }
      .user-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background: white; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); min-width: 200px; padding: 8px 0; z-index: 1000; margin-top: 8px; }
      .user-dropdown-menu.show { display: block; }
      .user-dropdown-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: #333; text-decoration: none; font-size: 14px; font-weight: 500; transition: background 0.2s; }
      .user-dropdown-menu a:hover { background: #f5f7fa; color: #525fe1; }
      .user-dropdown-menu a i { width: 18px; text-align: center; }
      .user-dropdown-menu hr { margin: 6px 0; border-color: #eee; }
    </style>
  </head>

  <body data-spy="scroll" data-offset="80">
    <!-- START PRELOADER -->
    <div class="preloaders"><span class="loader"></span></div>
    <!-- END PRELOADER -->

    <!-- START NAVBAR -->
    <div id="navigation" class="navbar-light bg-faded site-navigation">
      <div class="container-fluid">
        <div class="row">
          <div class="col-20 align-self-center">
            <div class="site-logo">
              <a href="index.php"><img src="../../assets/img/logo.png" alt="EduMatch" style="height:50px;" /></a>
            </div>
          </div>

          <div class="col-60 d-flex">
            <nav id="main-menu">
              <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="#">About</a></li>
                <li class="menu-item-has-children">
                  <a href="#">Edufeed</a>
                  <ul>
                    <li><a href="#">Submit Assignment</a></li>
                    <li><a href="#">Learning Feed</a></li>
                  </ul>
                </li>
                <li><a href="#">Partenariat</a></li>
                <li><a href="#">Evenement</a></li>
                <li><a href="#">Quiz</a></li>
                <li><a href="#">Offre d'emploi</a></li>
              </ul>
            </nav>
          </div>

          <div class="col-20 d-none d-xl-block text-end align-self-center">
            <?php if (!empty($_SESSION['user_id'])): ?>
            <div class="header-group" style="justify-content: flex-end;">
              <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
              <a href="/gestion_users/view/backoffice/src/pages/backoffice/users.php" class="btn-backoffice"><i class="fas fa-tachometer-alt"></i> Backoffice</a>
              <?php endif; ?>
              <div class="user-dropdown" id="userDropdown">
                <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($_SESSION['user_photo'] ?? 'default.png') ?>" alt="Photo" class="user-avatar" onerror="this.src='/gestion_users/uploads/photos/default.png';">
                <span class="user-name"><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></span>
                <i class="fas fa-chevron-down dropdown-caret"></i>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                  <a href="profil.php"><i class="fas fa-user"></i> Mon Profil</a>
                  <hr>
                  <a href="/gestion_users/auth/logout"><i class="fas fa-sign-out-alt"></i> Deconnexion</a>
                </div>
              </div>
            </div>
            <?php else: ?>
            <div class="header-group">
              <a href="sign-in.php" class="header-btn">Connexion</a>
              <a href="sign-up.php" class="btn_one">Inscription</a>
            </div>
            <?php endif; ?>
          </div>

          <!-- Mobile menu -->
          <ul class="mobile_menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="#">About</a></li>
            <li>
              <a href="#">Edufeed</a>
              <ul class="sub-menu">
                <li><a href="#">Submit Assignment</a></li>
                <li><a href="#">Learning Feed</a></li>
              </ul>
            </li>
            <li><a href="#">Partenariat</a></li>
            <li><a href="#">Evenement</a></li>
            <li><a href="#">Quiz</a></li>
            <li><a href="#">Offre d'emploi</a></li>
            <?php if (!empty($_SESSION['user_id'])): ?>
            <li><a href="profil.php">Mon Profil</a></li>
            <li><a href="/gestion_users/auth/logout">Deconnexion</a></li>
            <?php else: ?>
            <li><a href="sign-in.php">Connexion</a></li>
            <li><a href="sign-up.php">Inscription</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
    <!-- END NAVBAR -->

    <!-- START HERO -->
    <section class="hero-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center;">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 col-md-12">
            <div class="hero-content">
              <h1 class="hero-title" style="font-size: 3.5rem; font-weight: 700; color: white; margin-bottom: 1.5rem; line-height: 1.2;">
                EduMatch – <span style="color: #00D4FF;">Connecting Students</span><br>with Expert Professors
              </h1>
              <p class="hero-subtitle" style="font-size: 1.25rem; color: rgba(255,255,255,0.9); margin-bottom: 2rem; line-height: 1.6;">
                Find the perfect professor match for your academic needs. Personalized connections across all subjects for better understanding and academic success.
              </p>
              <div class="hero-ctas">
                <?php if (empty($_SESSION['user_id'])): ?>
                <a href="sign-up.php" class="btn btn-primary btn-lg me-3" style="background: linear-gradient(45deg, #00D4FF, #6366f1); border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; color: white; transition: all 0.3s ease;">
                  Get Started <i class="fas fa-rocket ms-2"></i>
                </a>
                <a href="sign-in.php" class="btn btn-outline-light btn-lg" style="border: 2px solid white; color: white; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
                  Connexion <i class="fas fa-sign-in-alt ms-2"></i>
                </a>
                <?php else: ?>
                <a href="profil.php" class="btn btn-primary btn-lg me-3" style="background: linear-gradient(45deg, #00D4FF, #6366f1); border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; color: white; transition: all 0.3s ease;">
                  Mon Profil <i class="fas fa-user ms-2"></i>
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-12 text-center">
            <div class="hero-image">
              <img src="../../assets/img/home-img2.png" alt="EduMatch Platform" class="img-fluid" style="max-width: 80%; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.2));">
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- END HERO -->

    <!-- START CALL TO ACTION -->
    <section class="cta-section py-5" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
      <div class="container text-center">
        <h2 class="cta-title" style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Ready to Find Your Perfect Professor Match?</h2>
        <p class="cta-subtitle" style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9;">Join thousands of students who have found their ideal professor connections across all academic subjects. Start your journey to academic excellence today.</p>
        <div class="cta-buttons">
          <?php if (empty($_SESSION['user_id'])): ?>
          <a href="sign-up.php" class="btn btn-light btn-lg me-3" style="background: white; color: #f5576c; border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
            Join EduMatch Today <i class="fas fa-rocket ms-2"></i>
          </a>
          <a href="sign-in.php" class="btn btn-outline-light btn-lg" style="border: 2px solid white; color: white; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
            Already a Member? <i class="fas fa-sign-in-alt ms-2"></i>
          </a>
          <?php else: ?>
          <a href="profil.php" class="btn btn-light btn-lg" style="background: white; color: #f5576c; border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
            Mon Profil <i class="fas fa-user ms-2"></i>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <!-- END CALL TO ACTION -->

    <!-- START MODERN FOOTER -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <a href="index.php" class="text-decoration-none">
              <img src="../../assets/img/logo.png" alt="EduMatch Logo" class="mb-3" style="height: 50px;">
            </a>
            <p class="mt-3 text-light opacity-75">Smart matching platform connecting students with expert professors across all academic subjects for personalized learning experiences.</p>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Platform</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php" class="text-light text-decoration-none">Home</a></li>
              <?php if (empty($_SESSION['user_id'])): ?>
              <li class="mb-2"><a href="sign-in.php" class="text-light text-decoration-none">Connexion</a></li>
              <li class="mb-2"><a href="sign-up.php" class="text-light text-decoration-none">Inscription</a></li>
              <?php else: ?>
              <li class="mb-2"><a href="profil.php" class="text-light text-decoration-none">Mon Profil</a></li>
              <li class="mb-2"><a href="/gestion_users/auth/logout" class="text-light text-decoration-none">Deconnexion</a></li>
              <?php endif; ?>
            </ul>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Academic Subjects</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Mathematics</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Coding</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Algorithm</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Languages</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Humanities</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Contact Info</h5>
            <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Tunisia, Tunis</p>
            <p class="mb-2"><i class="fas fa-phone me-2"></i>+216 90 549 254</p>
            <p class="mb-2"><i class="fas fa-envelope me-2"></i>edumatch@gmail.com</p>
          </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="row align-items-center">
          <div class="col-md-6">
            <p class="mb-0 text-light opacity-75">&copy; 2026 EduMatch. All rights reserved.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <a href="#" class="text-light text-decoration-none me-3">Privacy Policy</a>
            <a href="#" class="text-light text-decoration-none me-3">Terms of Service</a>
            <a href="#" class="text-light text-decoration-none">Support</a>
          </div>
        </div>
      </div>
    </footer>
    <!-- END MODERN FOOTER -->

    <!-- Latest jQuery -->
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
    <script>
    /* User dropdown toggle */
    (function(){
      var dd = document.getElementById('userDropdown');
      var menu = document.getElementById('userDropdownMenu');
      if (dd && menu) {
        dd.addEventListener('click', function(e) {
          e.stopPropagation();
          menu.classList.toggle('show');
        });
        document.addEventListener('click', function() {
          menu.classList.remove('show');
        });
      }
    })();
    </script>
  </body>
</html>
