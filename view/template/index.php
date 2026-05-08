<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/check_blocked.php';
/* Auto-refresh session data if user_prenom is missing (old session) */
if (!empty($_SESSION['user_id']) && empty($_SESSION['user_prenom'])) {
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

    <?php include __DIR__ . '/_navbar.php'; ?>

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

    <!-- START OUR PARTNERS SECTION -->
    <section class="partners-section py-5" style="background: #f8f9fa;">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-7 col-md-12 mb-4 mb-lg-0">
            <h2 style="font-size: 2.5rem; font-weight: 700; color: #0b104a; margin-bottom: 1rem;">Nos Partenaires</h2>
            <p style="font-size: 1.1rem; color: #555; line-height: 1.8; margin-bottom: 1rem;">
              Chez <strong>EduMatch</strong>, nous croyons en la puissance de la collaboration pour creer des opportunites d'apprentissage significatives.
              Nous travaillons avec des entreprises, des universites, des startups et des ONG pour connecter les etudiants avec des ressources, des experiences et des connaissances precieuses.
            </p>
            <p style="font-size: 1.1rem; color: #555; line-height: 1.8; margin-bottom: 1.5rem;">
              En devenant partenaire, vous rejoignez un reseau croissant dedie a faconner l'avenir de l'education.
              Soumettez votre demande de partenariat et notre equipe examinera votre candidature dans les plus brefs delais.
            </p>

            <?php
              $userRole = $_SESSION['user_role'] ?? '';
              $isLoggedIn = !empty($_SESSION['user_id']);
            ?>

            <?php if ($isLoggedIn && $userRole === 'partenariat'): ?>
              <!-- Connecte + role partenariat : acces direct -->
              <a href="/gestion_users/view/frontoffice/partenariat.php" class="btn btn-lg" style="background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; border: none; padding: 1rem 2.5rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; display: inline-block;">
                <i class="fas fa-handshake me-2"></i>Apply as Partner
              </a>
            <?php elseif ($isLoggedIn): ?>
              <!-- Connecte mais pas le bon role -->
              <div class="d-inline-block" style="cursor: not-allowed;">
                <button disabled class="btn btn-lg" style="background: #ccc; color: #666; border: none; padding: 1rem 2.5rem; border-radius: 50px; font-weight: 600; pointer-events: none;">
                  <i class="fas fa-lock me-2"></i>Apply as Partner
                </button>
              </div>
              <p style="color: #e74c3c; font-size: 0.9rem; margin-top: 0.75rem;">
                <i class="fas fa-info-circle me-1"></i>Cette section est reservee aux comptes avec le role <strong>partenariat</strong>. Votre role actuel est <strong><?= htmlspecialchars($userRole) ?></strong>.
              </p>
            <?php else: ?>
              <!-- Pas connecte : rediriger vers connexion -->
              <a href="/gestion_users/view/template/sign-in.php" class="btn btn-lg" style="background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; border: none; padding: 1rem 2.5rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; display: inline-block;">
                <i class="fas fa-handshake me-2"></i>Apply as Partner
              </a>
              <p style="color: #888; font-size: 0.9rem; margin-top: 0.75rem;">
                <i class="fas fa-info-circle me-1"></i>Vous devez vous connecter avec un compte <strong>partenariat</strong> pour soumettre une demande.
              </p>
            <?php endif; ?>
          </div>
          <div class="col-lg-5 col-md-12 text-center">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 3rem; color: white;">
              <i class="fas fa-building" style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.9;"></i>
              <h3 style="font-weight: 700; margin-bottom: 1rem;">Pourquoi devenir partenaire ?</h3>
              <ul style="list-style: none; padding: 0; text-align: left; font-size: 1rem; line-height: 2;">
                <li><i class="fas fa-check-circle me-2" style="color: #00D4FF;"></i>Visibilite aupres de milliers d'etudiants</li>
                <li><i class="fas fa-check-circle me-2" style="color: #00D4FF;"></i>Badge "Nouveau" et "Populaire" automatiques</li>
                <li><i class="fas fa-check-circle me-2" style="color: #00D4FF;"></i>Recommandations IA personnalisees</li>
                <li><i class="fas fa-check-circle me-2" style="color: #00D4FF;"></i>Contrats et suivi en temps reel</li>
                <li><i class="fas fa-check-circle me-2" style="color: #00D4FF;"></i>Chatbot intelligent pour assistance</li>
              </ul>
            </div>
          </div>
        </div>
        <!-- Discover partners : visible pour tout le monde -->
        <div class="text-center mt-5">
          <a href="/gestion_users/view/frontoffice/partners_may_like.php" class="btn btn-lg" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: white; border: none; padding: 1rem 2.5rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; display: inline-block;">
            <i class="fas fa-brain me-2"></i>Discover Our Partners (AI Recommendations)
          </a>
        </div>
      </div>
    </section>
    <!-- END OUR PARTNERS SECTION -->

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
    <!-- User dropdown JS is in _navbar.php -->
  </body>
</html>
