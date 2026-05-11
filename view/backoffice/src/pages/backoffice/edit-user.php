<?php session_start();
require_once __DIR__ . '/../../../../../config/database.php';
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php'); exit;
}
$errors = $_SESSION['errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['form_data']);

$db = Config::getConnexion();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM user WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { $_SESSION['errors'] = ["Utilisateur introuvable."]; header('Location: users.php'); exit; }

// Recuperer specialite + adresse actuelles depuis profil (utile pour encadrants)
$pStmt = $db->prepare("SELECT specialite, adresse FROM profil WHERE user_id = ? LIMIT 1");
$pStmt->execute([$id]);
$profilRow = $pStmt->fetch(PDO::FETCH_ASSOC) ?: [];
$currentSpecialite = (string) ($profilRow['specialite'] ?? '');
$currentAdresse    = (string) ($profilRow['adresse'] ?? '');

$nom = $formData['nom'] ?? $user['nom'];
$prenom = $formData['prenom'] ?? $user['prenom'];
$email = $formData['email'] ?? $user['email'];
$telephone = $formData['telephone'] ?? $user['telephone'];
$role = $formData['role'] ?? $user['role'];
$verifStudent = isset($formData['verification_student']) ? (int)$formData['verification_student'] : (int)$user['verification_student'];
$statutCompte = isset($formData['statut']) ? (int)$formData['statut'] : (int)$user['statut'];
$specialite = $formData['specialite'] ?? $currentSpecialite;
$adresse    = $formData['adresse']    ?? $currentAdresse;
$BO = '/gestion_users/view/backoffice/src';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Modifier Utilisateur | EduMatch Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="<?= $BO ?>/assets/css/theme.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
  <style>
    [data-bs-theme="light"] .form-control, [data-bs-theme="light"] .form-select { color: #000 !important; }
    .toggle-password { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; color: #6c757d; z-index: 5; line-height: 1; }
    .toggle-password:hover { color: #6366f1; }
  </style>
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
            <h1 class="mb-2">Modifier <?= htmlspecialchars($user['nom'].' '.$user['prenom']) ?></h1>
            <p class="mb-0 text-secondary"><?= htmlspecialchars($user['email']) ?></p>
          </div>
          <div class="col-lg-4 col-12 text-lg-end d-flex justify-content-lg-end gap-2">
            <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($user['photo']) ?>" class="rounded-circle" width="50" height="50" style="object-fit:cover;">
            <a href="users.php" class="btn btn-white align-self-center"><i class="ti ti-arrow-left me-1"></i>Retour</a>
          </div>
        </div>

        <div class="card card-lg mb-6">
          <div class="card-body">
            <form id="formEditUser" action="/gestion_users/user/doEdit" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <div class="row g-4">
                <div class="col-md-6">
                  <label class="form-label">Nom <span class="text-danger">*</span></label>
                  <input type="text" name="nom" id="editNom" class="form-control" value="<?= htmlspecialchars($nom) ?>" placeholder="Ex: Ben Ali">
                  <div class="text-danger small mt-1" id="err-nom"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Prenom <span class="text-danger">*</span></label>
                  <input type="text" name="prenom" id="editPrenom" class="form-control" value="<?= htmlspecialchars($prenom) ?>" placeholder="Ex: Ahmed">
                  <div class="text-danger small mt-1" id="err-prenom"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="text" name="email" id="editEmail" class="form-control" value="<?= htmlspecialchars($email) ?>" placeholder="exemple@email.com">
                  <div class="text-danger small mt-1" id="err-email"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Telephone</label>
                  <input type="text" name="telephone" id="editTel" class="form-control" value="<?= htmlspecialchars($telephone) ?>" placeholder="Ex: 12345678">
                  <div class="text-danger small mt-1" id="err-tel"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Role <span class="text-danger">*</span></label>
                  <select name="role" class="form-select">
                    <option value="etudiant" <?= $role==='etudiant'?'selected':'' ?>>Etudiant</option>
                    <option value="encadrant" <?= $role==='encadrant'?'selected':'' ?>>Encadrant</option>
                    <option value="partenariat" <?= $role==='partenariat'?'selected':'' ?>>Partenariat</option>
                    <option value="admin" <?= $role==='admin'?'selected':'' ?>>Admin</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Nouvelle photo</label>
                  <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
                  <small class="text-secondary">Laisser vide pour garder l'actuelle</small>
                </div>

                <!-- Verification etudiant (manuel par admin) -->
                <div class="col-md-6">
                  <label class="form-label">Verification <span class="text-secondary small">(etudiant)</span></label>
                  <select name="verification_student" class="form-select">
                    <option value="0" <?= $verifStudent === 0 ? 'selected' : '' ?>>Non verifie</option>
                    <option value="1" <?= $verifStudent === 1 ? 'selected' : '' ?>>Verifie</option>
                  </select>
                  <small class="text-secondary">Verifier manuellement un etudiant active aussi son compte.</small>
                </div>

                <!-- Statut du compte (Actif / Bloque) -->
                <div class="col-md-6">
                  <label class="form-label">Statut du compte</label>
                  <select name="statut" class="form-select">
                    <option value="1" <?= $statutCompte === 1 ? 'selected' : '' ?>>Actif</option>
                    <option value="0" <?= $statutCompte === 0 ? 'selected' : '' ?>>Bloque</option>
                  </select>
                  <small class="text-secondary">Bloquer empeche la connexion de l'utilisateur.</small>
                </div>

                <!-- Specialite (encadrants uniquement) -->
                <div class="col-md-6 encadrant-only" id="specialiteWrapper" style="display: <?= $role === 'encadrant' ? 'block' : 'none' ?>;">
                  <label class="form-label">Specialite <span class="text-secondary small">(encadrants)</span></label>
                  <select name="specialite" id="editSpecialite" class="form-select">
                    <option value="">-- Selectionnez une specialite --</option>
                    <?php
                    $specialitesList = [
                        'Mathematiques','Physique','Chimie','Informatique','Programmation',
                        'Algorithmique','Bases de donnees','Reseaux','Cybersecurite',
                        'Intelligence Artificielle','Data Science','Developpement Web',
                        'Developpement Mobile','Genie Logiciel','Anglais','Francais',
                        'Arabe','Espagnol','Economie','Gestion','Comptabilite','Autre',
                    ];
                    foreach ($specialitesList as $sp): ?>
                      <option value="<?= htmlspecialchars($sp) ?>" <?= $specialite === $sp ? 'selected' : '' ?>><?= htmlspecialchars($sp) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <small class="text-secondary">Utilise dans la recherche d'encadrants.</small>
                </div>

                <!-- Adresse (encadrants uniquement, pour seances en presentiel) -->
                <div class="col-md-6 encadrant-only" id="adresseWrapper" style="display: <?= $role === 'encadrant' ? 'block' : 'none' ?>;">
                  <label class="form-label">Adresse <span class="text-secondary small">(encadrants - presentiel)</span></label>
                  <input type="text" name="adresse" id="editAdresse" class="form-control" value="<?= htmlspecialchars($adresse) ?>" placeholder="Ex : 12 rue de la Liberte, Tunis">
                  <small class="text-secondary">Visible par les etudiants reservant en presentiel.</small>
                </div>
              </div>
              <div class="d-flex gap-3 mt-5">
                <button type="submit" class="btn btn-dark"><i class="ti ti-device-floppy me-1"></i>Enregistrer</button>
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

  <!-- Toggle specialite + adresse selon role -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var roleSelect = document.querySelector('select[name="role"]');
    var specialiteWrapper = document.getElementById('specialiteWrapper');
    var specialiteSelect = document.getElementById('editSpecialite');
    var adresseWrapper = document.getElementById('adresseWrapper');
    var adresseInput = document.getElementById('editAdresse');

    function toggleEncadrantFields() {
      if (!roleSelect) return;
      var isEnc = roleSelect.value === 'encadrant';
      if (specialiteWrapper) specialiteWrapper.style.display = isEnc ? 'block' : 'none';
      if (adresseWrapper)    adresseWrapper.style.display    = isEnc ? 'block' : 'none';
      if (!isEnc) {
        if (specialiteSelect) specialiteSelect.value = '';
        if (adresseInput) adresseInput.value = '';
      }
    }

    if (roleSelect) {
      roleSelect.addEventListener('change', toggleEncadrantFields);
      toggleEncadrantFields();
    }
  });
  </script>
</body>
</html>
