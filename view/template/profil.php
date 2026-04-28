<?php session_start();
require_once __DIR__ . '/../../config/database.php';
if (empty($_SESSION['user_id'])) { header('Location: /gestion_users/view/template/sign-in.php'); exit; }
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

$db = Config::getConnexion();
$stmt = $db->prepare("SELECT u.*, p.bio_text, p.niveau, p.specialite FROM user u LEFT JOIN profil p ON p.user_id = u.id WHERE u.id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch();

/* Keep session in sync with DB */
if ($userData) {
    $_SESSION['user_nom']    = $userData['nom'];
    $_SESSION['user_prenom'] = $userData['prenom'];
    $_SESSION['user_photo']  = $userData['photo'];
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Mon Profil | EduMatch</title>
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
      
      
      .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; text-align: center; border: none; cursor: pointer; }
      .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
      .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
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

  <body>
    <div class="preloaders"><span class="loader"></span></div>

    <!-- NAVBAR -->
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
          </div>
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
            <li><a href="profil.php">Mon Profil</a></li>
            <li><a href="/gestion_users/auth/logout">Deconnexion</a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- END NAVBAR -->

    <section class="py-5" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 80vh; padding-top: 80px !important;">
      <div class="container">

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <ul class="mb-0"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-4">
          <!-- Carte Profil -->
          <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center p-4" style="border-radius:16px;">
              <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($userData['photo'] ?? 'default.png') ?>"
                   class="rounded-circle mx-auto mb-3 border border-3 border-primary"
                   width="120" height="120" style="object-fit:cover;">
              <h5 class="fw-bold mb-0"><?= htmlspecialchars($userData['nom'] . ' ' . $userData['prenom']) ?></h5>
              <span class="badge bg-primary mt-2 text-capitalize"><?= htmlspecialchars($userData['role'] ?? '') ?></span>
              <p class="text-muted small mt-2 mb-0"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($userData['email'] ?? '') ?></p>
              <?php if (!empty($userData['telephone'])): ?>
              <p class="text-muted small mt-1 mb-0"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($userData['telephone']) ?></p>
              <?php endif; ?>
              <?php if (!empty($userData['bio_text'])): ?>
              <hr>
              <p class="text-muted small"><?= htmlspecialchars($userData['bio_text']) ?></p>
              <?php endif; ?>
              <hr>
              <p class="text-muted small mb-0">Membre depuis le <?= date('d/m/Y', strtotime($userData['created_at'] ?? 'now')) ?></p>
            </div>
          </div>

          <!-- Modifier Profil -->
          <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius:16px;">
              <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold"><i class="fas fa-pen-to-square me-2 text-primary"></i>Modifier le profil</h5>
              </div>
              <div class="card-body p-4">
                <form id="formProfil" action="/gestion_users/profil/doEdit" method="POST" enctype="multipart/form-data">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                      <input type="text" name="nom" id="profilNom" class="form-control" value="<?= htmlspecialchars($userData['nom'] ?? '') ?>">
                      <div class="text-danger small mt-1" id="err-nom"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Prenom <span class="text-danger">*</span></label>
                      <input type="text" name="prenom" id="profilPrenom" class="form-control" value="<?= htmlspecialchars($userData['prenom'] ?? '') ?>">
                      <div class="text-danger small mt-1" id="err-prenom"></div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Telephone</label>
                    <input type="text" name="telephone" id="profilTel" class="form-control" value="<?= htmlspecialchars($userData['telephone'] ?? '') ?>" placeholder="Ex: 12345678">
                    <div class="text-danger small mt-1" id="err-tel"></div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Bio</label>
                    <textarea name="bio_text" class="form-control" rows="3" placeholder="Parlez de vous..."><?= htmlspecialchars($userData['bio_text'] ?? '') ?></textarea>
                  </div>
                  <?php if (($userData['role'] ?? '') === 'etudiant'): ?>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Niveau</label>
                    <input type="text" name="niveau" class="form-control" value="<?= htmlspecialchars($userData['niveau'] ?? '') ?>" placeholder="Ex: 2eme annee">
                  </div>
                  <?php endif; ?>
                  <?php if (($userData['role'] ?? '') === 'encadrant'): ?>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Specialite</label>
                    <input type="text" name="specialite" class="form-control" value="<?= htmlspecialchars($userData['specialite'] ?? '') ?>" placeholder="Ex: Developpement Web">
                  </div>
                  <?php endif; ?>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Photo de profil</label>
                    <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
                    <small class="text-muted">JPG, PNG uniquement</small>
                  </div>
                  <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-floppy-disk me-2"></i>Enregistrer</button>
                </form>
              </div>
            </div>

            <!-- Changer mot de passe -->
            <div class="card shadow-sm border-0" style="border-radius:16px;">
              <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold"><i class="fas fa-lock me-2 text-danger"></i>Changer le mot de passe</h5>
              </div>
              <div class="card-body p-4">
                <form id="formChangePwd" action="/gestion_users/profil/changePassword" method="POST">
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Mot de passe actuel</label>
                    <div class="position-relative">
                      <input type="password" name="current_password" id="currentPwd" class="form-control pe-5" placeholder="Mot de passe actuel">
                      <span class="toggle-password" data-target="currentPwd"><i class="fas fa-eye"></i></span>
                    </div>
                    <div class="text-danger small mt-1" id="err-current"></div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Nouveau mot de passe</label>
                    <div class="position-relative">
                      <input type="password" name="new_password" id="newPwd" class="form-control pe-5" placeholder="Nouveau mot de passe">
                      <span class="toggle-password" data-target="newPwd"><i class="fas fa-eye"></i></span>
                    </div>
                    <small class="text-muted d-block mt-1">Min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre, 1 spécial</small>
                    <div class="text-danger small mt-1" id="err-new"></div>
                  </div>
                  <div class="mb-4">
                    <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                    <div class="position-relative">
                      <input type="password" name="confirm_password" id="confirmPwd" class="form-control pe-5" placeholder="Répéter le mot de passe">
                      <span class="toggle-password" data-target="confirmPwd"><i class="fas fa-eye"></i></span>
                    </div>
                    <div class="text-danger small mt-1" id="err-confirm"></div>
                  </div>
                  <button type="submit" class="btn btn-danger fw-semibold"><i class="fas fa-key me-2"></i>Changer</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <a href="index.php" class="text-decoration-none"><img src="../../assets/img/logo.png" alt="EduMatch" class="mb-3" style="height: 50px;"></a>
            <p class="mt-3 text-light opacity-75">Smart matching platform connecting students with expert professors for personalized learning.</p>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Navigation</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php" class="text-light text-decoration-none">Home</a></li>
              <li class="mb-2"><a href="profil.php" class="text-light text-decoration-none">Mon Profil</a></li>
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
        <p class="text-center text-light opacity-75 mb-0">&copy; 2026 EduMatch. All rights reserved.</p>
      </div>
    </footer>

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
    <script>
    (function(){
      var dd = document.getElementById('userDropdown');
      var menu = document.getElementById('userDropdownMenu');
      if (dd && menu) {
        dd.addEventListener('click', function(e) { e.stopPropagation(); menu.classList.toggle('show'); });
        document.addEventListener('click', function() { menu.classList.remove('show'); });
      }
    })();
    </script>
  </body>
</html>
