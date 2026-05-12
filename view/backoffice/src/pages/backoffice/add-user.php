<?php session_start();
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php'); exit;
}
$errors = $_SESSION['errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['form_data']);
$BO = '/gestion_users/view/backoffice/src';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Ajouter Utilisateur | EduMatch Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="<?= $BO ?>/assets/css/theme.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
  <style>.toggle-password { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; color: #6c757d; z-index: 5; line-height: 1; } .toggle-password:hover { color: #525fe1; }</style>
  <script src="<?= $BO ?>/assets/js/vendors/color-modes.js"></script>
  <script>
    if (localStorage.getItem('sidebarExpanded') === 'false') { document.documentElement.classList.add('collapsed'); document.documentElement.classList.remove('expanded'); }
    else { document.documentElement.classList.remove('collapsed'); document.documentElement.classList.add('expanded'); }
  </script>
</head>
<body>
  <div>
    <?php include __DIR__ . '/../../partials_php/sidebar.php'; ?>

    <div id="content" class="position-relative h-100">
      <?php include __DIR__ . '/../../partials_php/topbar.php'; ?>

      <div class="custom-container">
        <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <ul class="mb-0"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row mb-6 g-6 align-items-center">
          <div class="col-lg-8 col-12">
            <p class="text-uppercase text-secondary small mb-2">EduMatch BackOffice</p>
            <h1 class="mb-2">Ajouter un utilisateur</h1>
          </div>
          <div class="col-lg-4 col-12 text-lg-end">
            <a href="users.php" class="btn btn-white"><i class="ti ti-arrow-left me-1"></i>Retour</a>
          </div>
        </div>

        <div class="card card-lg mb-6">
          <div class="card-body">
            <form id="formAddUser" action="/gestion_users/user/doAdd" method="POST" enctype="multipart/form-data">
              <div class="row g-4">
                <div class="col-md-6">
                  <label class="form-label">Nom <span class="text-danger">*</span></label>
                  <input type="text" name="nom" id="addNom" class="form-control" value="<?= htmlspecialchars($formData['nom'] ?? '') ?>" placeholder="Ex: Ben Ali">
                  <div class="text-danger small mt-1" id="err-nom"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Prenom <span class="text-danger">*</span></label>
                  <input type="text" name="prenom" id="addPrenom" class="form-control" value="<?= htmlspecialchars($formData['prenom'] ?? '') ?>" placeholder="Ex: Ahmed">
                  <div class="text-danger small mt-1" id="err-prenom"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="text" name="email" id="addEmail" class="form-control" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" placeholder="exemple@email.com">
                  <div class="text-danger small mt-1" id="err-email"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Telephone</label>
                  <input type="text" name="telephone" id="addTel" class="form-control" value="<?= htmlspecialchars($formData['telephone'] ?? '') ?>" placeholder="Ex: 12345678">
                  <div class="text-danger small mt-1" id="err-tel"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Role <span class="text-danger">*</span></label>
                  <select name="role" class="form-select">
                    <option value="etudiant" <?= ($formData['role']??'')==='etudiant'?'selected':'' ?>>Etudiant</option>
                    <option value="encadrant" <?= ($formData['role']??'')==='encadrant'?'selected':'' ?>>Encadrant</option>
                    <option value="partenariat" <?= ($formData['role']??'')==='partenariat'?'selected':'' ?>>Partenariat</option>
                    <option value="admin" <?= ($formData['role']??'')==='admin'?'selected':'' ?>>Admin</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Photo</label>
                  <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                  <div class="position-relative">
                    <input type="password" name="password" id="addPassword" class="form-control pe-5" placeholder="Mot de passe">
                    <span class="toggle-password" data-target="addPassword"><i class="ti ti-eye"></i></span>
                  </div>
                  <small class="text-muted d-block mt-1">Min. 8 caracteres, 1 majuscule, 1 minuscule, 1 chiffre, 1 special</small>
                  <div class="text-danger small mt-1" id="err-password"></div>
                </div>
              </div>
              <div class="d-flex gap-3 mt-5">
                <button type="submit" class="btn btn-dark"><i class="ti ti-user-plus me-1"></i>Ajouter</button>
                <a href="users.php" class="btn btn-white">Annuler</a>
              </div>
            </form>
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
