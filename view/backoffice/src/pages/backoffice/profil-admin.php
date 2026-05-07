<?php session_start();
require_once __DIR__ . '/../../../../../config/database.php';
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php'); exit;
}
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

$db = Config::getConnexion();
$stmt = $db->prepare("SELECT u.*, p.bio_text FROM user u LEFT JOIN profil p ON p.user_id=u.id WHERE u.id=? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch();

if ($userData) {
    $_SESSION['user_nom']    = $userData['nom'];
    $_SESSION['user_prenom'] = $userData['prenom'];
    $_SESSION['user_photo']  = $userData['photo'];
}

$BO = '/gestion_users/view/backoffice/src';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Mon Profil | EduMatch Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="<?= $BO ?>/assets/css/theme.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
  <style>
    .toggle-password { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; color: #6c757d; z-index: 5; line-height: 1; }
    .toggle-password:hover { color: #6366f1; }
    .profile-banner { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 16px; padding: 40px; color: white; position: relative; overflow: hidden; }
    .profile-banner::before { content: ''; position: absolute; top: -50%; right: -20%; width: 400px; height: 400px; background: rgba(255,255,255,0.05); border-radius: 50%; }
    .profile-banner img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,0.3); }
  </style>
  <script src="<?= $BO ?>/assets/js/vendors/color-modes.js"></script>
  <script>
    if(localStorage.getItem('sidebarExpanded')==='false'){document.documentElement.classList.add('collapsed');document.documentElement.classList.remove('expanded');}
    else{document.documentElement.classList.remove('collapsed');document.documentElement.classList.add('expanded');}
  </script>
</head>
<body>
  <div>
    <?php include __DIR__ . '/../../partials_php/sidebar.php'; ?>
    <div id="content" class="position-relative h-100">
      <?php include __DIR__ . '/../../partials_php/topbar.php'; ?>
      <div class="custom-container">

        <!-- Flash -->
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <ul class="mb-0"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="mb-4">
          <p class="text-uppercase text-secondary small mb-2">EduMatch BackOffice</p>
          <h1 class="mb-0">Mon Profil</h1>
        </div>

        <!-- Profile Banner -->
        <div class="profile-banner mb-4">
          <div class="d-flex flex-column flex-md-row align-items-center gap-4">
            <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($userData['photo'] ?? 'default.png') ?>" onerror="this.src='/gestion_users/uploads/photos/default.png';">
            <div class="text-center text-md-start">
              <h2 class="fw-bold mb-1"><?= htmlspecialchars($userData['prenom'].' '.$userData['nom']) ?></h2>
              <p class="mb-1 opacity-75"><?= htmlspecialchars($userData['email']) ?></p>
              <span class="badge bg-danger fs-6">Administrateur</span>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <!-- Edit Profile -->
          <div class="col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
              <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="fw-bold"><i class="ti ti-edit me-2 text-primary"></i>Modifier mes informations</h5>
              </div>
              <div class="card-body">
                <form id="formProfil" action="/gestion_users/profil/doEdit" method="POST" enctype="multipart/form-data">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Nom <span class="text-danger">*</span></label>
                      <input type="text" name="nom" id="profilNom" class="form-control" value="<?= htmlspecialchars($userData['nom'] ?? '') ?>">
                      <div class="text-danger small mt-1" id="err-nom"></div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Prenom <span class="text-danger">*</span></label>
                      <input type="text" name="prenom" id="profilPrenom" class="form-control" value="<?= htmlspecialchars($userData['prenom'] ?? '') ?>">
                      <div class="text-danger small mt-1" id="err-prenom"></div>
                    </div>
                    <div class="col-12">
                      <label class="form-label">Telephone</label>
                      <input type="text" name="telephone" id="profilTel" class="form-control" value="<?= htmlspecialchars($userData['telephone'] ?? '') ?>" placeholder="Ex: 12345678">
                      <div class="text-danger small mt-1" id="err-tel"></div>
                    </div>
                    <div class="col-12">
                      <label class="form-label">Photo de profil</label>
                      <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
                      <small class="text-secondary">JPG, PNG uniquement</small>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-dark mt-4"><i class="ti ti-device-floppy me-1"></i>Enregistrer</button>
                </form>
              </div>
            </div>
          </div>

          <!-- Change Password -->
          <div class="col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
              <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="fw-bold"><i class="ti ti-lock me-2 text-danger"></i>Changer le mot de passe</h5>
              </div>
              <div class="card-body">
                <form id="formChangePwd" action="/gestion_users/profil/changePassword" method="POST">
                  <div class="mb-3">
                    <label class="form-label">Mot de passe actuel</label>
                    <div class="position-relative">
                      <input type="password" name="current_password" id="currentPwd" class="form-control pe-5" placeholder="Mot de passe actuel">
                      <span class="toggle-password" data-target="currentPwd"><i class="ti ti-eye"></i></span>
                    </div>
                    <div class="text-danger small mt-1" id="err-current"></div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Nouveau mot de passe</label>
                    <div class="position-relative">
                      <input type="password" name="new_password" id="newPwd" class="form-control pe-5" placeholder="Nouveau mot de passe">
                      <span class="toggle-password" data-target="newPwd"><i class="ti ti-eye"></i></span>
                    </div>
                    <small class="text-muted d-block mt-1">Min. 8 caracteres, 1 majuscule, 1 minuscule, 1 chiffre, 1 special</small>
                    <div class="text-danger small mt-1" id="err-new"></div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <div class="position-relative">
                      <input type="password" name="confirm_password" id="confirmPwd" class="form-control pe-5" placeholder="Repeter le mot de passe">
                      <span class="toggle-password" data-target="confirmPwd"><i class="ti ti-eye"></i></span>
                    </div>
                    <div class="text-danger small mt-1" id="err-confirm"></div>
                  </div>
                  <button type="submit" class="btn btn-danger"><i class="ti ti-key me-1"></i>Changer</button>
                </form>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
  <script src="<?= $BO ?>/assets/js/main.js"></script>
  <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="/gestion_users/assets/js/validation.js"></script>
</body>
</html>
