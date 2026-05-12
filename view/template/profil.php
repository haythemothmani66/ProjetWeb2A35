<?php session_start();
require_once __DIR__ . '/../../config/database.php';
if (empty($_SESSION['user_id'])) { header('Location: /gestion_users/view/template/sign-in.php'); exit; }
require_once __DIR__ . '/../../config/check_blocked.php';
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

$db = Config::getConnexion();
$stmt = $db->prepare("SELECT u.*, p.bio_text, p.niveau, p.specialite, p.classe, p.email_universitaire, p.card_image, p.adresse, p.etablissement_ecole, p.identifiant_card, p.annee_universitaire FROM user u LEFT JOIN profil p ON p.user_id = u.id WHERE u.id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch();

/* Keep session in sync with DB */
if ($userData) {
    $_SESSION['user_nom']    = $userData['nom'];
    $_SESSION['user_prenom'] = $userData['prenom'];
    $_SESSION['user_photo']  = $userData['photo'];
}

/* Calculate trial remaining for students */
$isStudentVerified = ($userData['role'] === 'etudiant' && (int)($userData['verification_student'] ?? 0) === 1);
$trialRemaining = null;
if ($userData['role'] === 'etudiant' && !$isStudentVerified) {
    $trialDays = (int)($userData['trial_days'] ?: 7);
    $created = new DateTime($userData['created_at']);
    $now = new DateTime();
    $hoursElapsed = ($now->getTimestamp() - $created->getTimestamp()) / 3600;
    $daysPassed = (int)floor($hoursElapsed / 24);
    $trialRemaining = max(0, $trialDays - $daysPassed);
}
?>
<!doctype html>
<html lang="fr">
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

      /* Blinking green bullet */
      @keyframes pulse-green {
        0%   { opacity: 1; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
        50%  { opacity: 0.6; box-shadow: 0 0 8px 4px rgba(16, 185, 129, 0.25); }
        100% { opacity: 1; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
      }
      .status-bullet {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #10b981;
        animation: pulse-green 1.8s ease-in-out infinite;
        vertical-align: middle;
      }
      .status-label {
        color: #10b981;
        font-weight: 600;
        font-size: 14px;
        vertical-align: middle;
      }

      /* Photo upload redesign */
      .photo-upload-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px 0 10px;
      }
      .photo-upload-circle {
        position: relative;
        width: 110px;
        height: 110px;
        cursor: pointer;
      }
      .photo-upload-circle img {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e2e8f0;
        transition: border-color 0.3s, filter 0.3s;
      }
      .photo-upload-circle:hover img {
        border-color: #525fe1;
        filter: brightness(0.85);
      }
      .photo-upload-circle .camera-badge {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #525fe1;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2.5px solid #fff;
        transition: background 0.3s, transform 0.2s;
      }
      .photo-upload-circle:hover .camera-badge {
        background: #4338ca;
        transform: scale(1.1);
      }
      .camera-badge i {
        color: #fff;
        font-size: 13px;
      }
      .photo-upload-hint {
        margin-top: 8px;
        font-size: 12.5px;
        color: #94a3b8;
      }

      /* ===== Carte Etudiant Section ===== */
      .card-upload-zone {
        border: 2.5px dashed #cbd5e1;
        border-radius: 14px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.3s, background 0.3s;
        background: #f8fafc;
      }
      .card-upload-zone:hover {
        border-color: #525fe1;
        background: #eef2ff;
      }
      .card-upload-zone .upload-icon {
        font-size: 40px;
        color: #94a3b8;
        margin-bottom: 12px;
        transition: color 0.3s;
      }
      .card-upload-zone:hover .upload-icon { color: #525fe1; }
      .card-upload-zone .upload-text {
        font-size: 15px;
        font-weight: 600;
        color: #475569;
      }
      .card-upload-zone .upload-hint {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
      }

      .card-preview-frame {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #f8fafc;
        text-align: center;
        position: relative;
      }
      .card-preview-frame img {
        width: 100%;
        max-height: 220px;
        object-fit: contain;
        display: block;
        margin: 0 auto;
      }
      .card-preview-frame .change-overlay {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(255,255,255,0.92);
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #525fe1;
        cursor: pointer;
        border: 1px solid #e2e8f0;
        transition: background 0.2s, color 0.2s;
      }
      .card-preview-frame .change-overlay:hover {
        background: #525fe1;
        color: #fff;
      }

      .warning-card {
        background: #fffbeb;
        border: 1px solid #fbbf24;
        border-radius: 10px;
        padding: 12px 16px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
      }
      .warning-card i { color: #f59e0b; font-size: 18px; margin-top: 2px; }
      .warning-card p { margin: 0; font-size: 13px; color: #92400e; line-height: 1.5; }

      .success-card {
        background: #ecfdf5;
        border: 1px solid #6ee7b7;
        border-radius: 10px;
        padding: 12px 16px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
      }
      .success-card i { color: #10b981; font-size: 18px; margin-top: 2px; }
      .success-card p { margin: 0; font-size: 13px; color: #065f46; line-height: 1.5; }

      /* ===== IA Generate Button ===== */
      @keyframes ai-pulse {
        0%   { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.5); }
        70%  { box-shadow: 0 0 0 10px rgba(99, 102, 241, 0); }
        100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
      }
      @keyframes ai-sparkle {
        0%, 100% { transform: rotate(0deg) scale(1); }
        25%  { transform: rotate(-8deg) scale(1.15); }
        75%  { transform: rotate(8deg) scale(1.15); }
      }
      .btn-ia-generate {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 12px 28px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        animation: ai-pulse 2.5s infinite;
      }
      .btn-ia-generate:hover {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        transform: translateY(-2px);
        color: #fff;
      }
      .btn-ia-generate:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        animation: none;
        transform: none;
      }
      .btn-ia-generate .ai-icon {
        font-size: 18px;
        animation: ai-sparkle 2s ease-in-out infinite;
      }

      /* ===== IA Loading ===== */
      @keyframes ai-brain-spin {
        0%   { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      @keyframes ai-dots {
        0%, 20%  { content: '.'; }
        40%      { content: '..'; }
        60%, 100% { content: '...'; }
      }
      .ia-loading-box {
        display: none;
        background: linear-gradient(135deg, #eef2ff, #faf5ff);
        border: 1.5px solid #c7d2fe;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
      }
      .ia-loading-box .brain-spinner {
        width: 48px;
        height: 48px;
        border: 3px solid #e0e7ff;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: ai-brain-spin 1s linear infinite;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .ia-loading-box .brain-spinner i {
        animation: none;
        font-size: 18px;
        color: #6366f1;
      }
      .ia-loading-box .loading-text {
        font-weight: 600;
        color: #4338ca;
        font-size: 14px;
      }
      .ia-loading-box .loading-sub {
        font-size: 12px;
        color: #6366f1;
        margin-top: 4px;
      }

      /* ===== Pre-fill highlight ===== */
      @keyframes field-glow {
        0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        50%  { box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
      }
      .field-prefilled {
        border-color: #10b981 !important;
        background-color: #f0fdf4 !important;
        animation: field-glow 1.5s ease-out;
        transition: border-color 3s, background-color 3s;
      }
    </style>
  </head>

  <body>
    <div class="preloaders"><span class="loader"></span></div>

    <?php include __DIR__ . '/_navbar.php'; ?>

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

            <!-- Changer mot de passe (under sidebar card) -->
            <div class="card shadow-sm border-0 mt-4" style="border-radius:16px;">
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
                    <small class="text-muted d-block mt-1">Min. 8 car., 1 maj, 1 min, 1 chiffre, 1 special</small>
                    <div class="text-danger small mt-1" id="err-new"></div>
                  </div>
                  <div class="mb-4">
                    <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                    <div class="position-relative">
                      <input type="password" name="confirm_password" id="confirmPwd" class="form-control pe-5" placeholder="Repeter le mot de passe">
                      <span class="toggle-password" data-target="confirmPwd"><i class="fas fa-eye"></i></span>
                    </div>
                    <div class="text-danger small mt-1" id="err-confirm"></div>
                  </div>
                  <button type="submit" class="btn btn-danger fw-semibold"><i class="fas fa-key me-2"></i>Changer</button>
                </form>
              </div>
            </div>
          </div>

          <!-- Modifier Profil -->
          <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius:16px;">
              <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="fas fa-pen-to-square me-2 text-primary"></i>Modifier le profil</h5>
                <span><span class="status-bullet me-1"></span> <span class="status-label">Connecte</span></span>
              </div>
              <div class="card-body p-4">
                <form id="formProfil" action="/gestion_users/profil/doEdit" method="POST" enctype="multipart/form-data">
                  <!-- Photo + Nom/Prenom/Tel inline -->
                  <div class="d-flex gap-3 mb-3 align-items-start">
                    <div class="photo-upload-box flex-shrink-0 text-center" style="min-width:100px;">
                      <div class="photo-upload-circle" id="photoUploadWrapper">
                        <img id="photoPreview"
                             src="/gestion_users/uploads/photos/<?= htmlspecialchars($userData['photo'] ?? 'default.png') ?>"
                             alt="Photo de profil"
                             onerror="this.src='/gestion_users/uploads/photos/default.png';">
                        <div class="camera-badge"><i class="fas fa-camera"></i></div>
                        <input type="file" name="photo" id="photoFileInput" accept=".jpg,.jpeg,.png" style="display:none;">
                      </div>
                      <span class="photo-upload-hint" style="font-size:11px;">Modifier</span>
                    </div>
                    <div class="flex-grow-1">
                      <div class="row">
                        <div class="col-md-6 mb-2">
                          <label class="form-label fw-semibold mb-1">Nom <span class="text-danger">*</span></label>
                          <input type="text" name="nom" id="profilNom" class="form-control" value="<?= htmlspecialchars($userData['nom'] ?? '') ?>">
                          <div class="text-danger small mt-1" id="err-nom"></div>
                        </div>
                        <div class="col-md-6 mb-2">
                          <label class="form-label fw-semibold mb-1">Prenom <span class="text-danger">*</span></label>
                          <input type="text" name="prenom" id="profilPrenom" class="form-control" value="<?= htmlspecialchars($userData['prenom'] ?? '') ?>">
                          <div class="text-danger small mt-1" id="err-prenom"></div>
                        </div>
                      </div>
                      <div>
                        <label class="form-label fw-semibold mb-1">Telephone</label>
                        <input type="text" name="telephone" id="profilTel" class="form-control" value="<?= htmlspecialchars($userData['telephone'] ?? '') ?>" placeholder="Ex: 12345678">
                        <div class="text-danger small mt-1" id="err-tel"></div>
                      </div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Bio</label>
                    <textarea name="bio_text" id="profilBio" class="form-control" rows="3" placeholder="Parlez de vous..."><?= htmlspecialchars($userData['bio_text'] ?? '') ?></textarea>
                    <button type="button" id="btnGenBio" class="btn btn-sm btn-outline-primary mt-2">
                      <i class="fas fa-magic me-1"></i>Generer bio par IA
                    </button>
                    <span id="bioLoading" class="text-primary small ms-2" style="display:none;"><i class="fas fa-spinner fa-spin me-1"></i>Generation...</span>
                    <div id="bioError" class="text-danger small mt-1" style="display:none;"></div>
                  </div>
                  <?php if (($userData['role'] ?? '') === 'etudiant'): ?>
                  <!-- ============ CARTE ETUDIANT SECTION ============ -->
                  <hr>
                  <h6 class="fw-bold mb-3" style="color:#334155;"><i class="fas fa-id-card me-2 text-primary"></i>Carte Etudiant</h6>

                  <!-- Status banner -->
                  <?php if ($isStudentVerified): ?>
                  <div class="success-card mb-3">
                    <i class="fas fa-check-circle"></i>
                    <p>Votre statut etudiant a ete verifie par l'administrateur !</p>
                  </div>
                  <?php else: ?>
                  <div class="warning-card mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Vous devez ajouter votre carte etudiant pour que l'administrateur puisse verifier votre compte. Sans verification, votre acces sera limite apres la periode d'essai (vous avez <strong><?= $trialRemaining ?> jour(s)</strong> encore).</p>
                  </div>
                  <?php endif; ?>

                  <?php if ($isStudentVerified): ?>
                  <!-- Verified: show card read-only (no upload / no IA) -->
                  <?php if (!empty($userData['card_image'])): ?>
                  <div class="card-preview-frame mb-3" style="pointer-events:none;">
                    <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($userData['card_image']) ?>" alt="Carte etudiant" class="img-fluid" onerror="this.outerHTML='<span class=\'text-muted\'>— Image introuvable —</span>';">
                  </div>
                  <?php else: ?>
                  <p class="text-muted mb-3">— Aucune carte ajoutee —</p>
                  <?php endif; ?>
                  <?php else: ?>
                  <!-- Not verified: upload zone / Preview + IA -->
                  <input type="file" name="card_image" id="cardImageInput" accept=".jpg,.jpeg,.png" style="display:none;">

                  <?php if (!empty($userData['card_image'])): ?>
                  <!-- Card already uploaded: show preview -->
                  <div id="cardPreviewContainer" class="card-preview-frame mb-3">
                    <img id="cardPreviewImg" src="/gestion_users/uploads/photos/<?= htmlspecialchars($userData['card_image']) ?>" alt="Carte etudiant" onerror="this.closest('.card-preview-frame').style.display='none'; document.getElementById('cardDropZone').style.display='block';">
                    <div class="change-overlay" id="cardChangeBtn"><i class="fas fa-pen me-1"></i>Changer</div>
                  </div>
                  <div id="cardDropZone" class="card-upload-zone mb-3" style="display:none;">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">Ajouter votre carte etudiant</div>
                    <div class="upload-hint">JPG, PNG — Cliquez ou glissez votre carte ici</div>
                  </div>
                  <?php else: ?>
                  <!-- No card: show drop zone -->
                  <div id="cardPreviewContainer" class="card-preview-frame mb-3" style="display:none;">
                    <img id="cardPreviewImg" src="" alt="Carte etudiant">
                    <div class="change-overlay" id="cardChangeBtn"><i class="fas fa-pen me-1"></i>Changer</div>
                  </div>
                  <div id="cardDropZone" class="card-upload-zone mb-3">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">Ajouter votre carte etudiant</div>
                    <div class="upload-hint">JPG, PNG — Cliquez ou glissez votre carte ici</div>
                  </div>
                  <?php endif; ?>

                  <!-- IA Generate Button (below card) -->
                  <div class="text-center mb-3" id="iaButtonContainer" style="<?= empty($userData['card_image']) ? 'display:none;' : '' ?>">
                    <button type="button" id="btnOcrIA" class="btn-ia-generate">
                      <i class="fas fa-wand-magic-sparkles ai-icon"></i>
                      <span>Generer les informations par IA</span>
                    </button>
                  </div>

                  <!-- IA Loading -->
                  <div class="ia-loading-box mb-3" id="ocrLoading">
                    <div class="brain-spinner"><i class="fas fa-brain"></i></div>
                    <div class="loading-text">L'IA analyse votre carte etudiant</div>
                    <div class="loading-sub">Extraction OCR + Analyse intelligente en cours</div>
                  </div>

                  <!-- IA Results -->
                  <div id="ocrResult" class="alert alert-success small mb-3" style="display:none; border-radius:10px;"><i class="fas fa-check-circle me-2"></i><span></span></div>
                  <div id="ocrError" class="alert alert-danger small mb-3" style="display:none; border-radius:10px;"><i class="fas fa-times-circle me-2"></i><span></span></div>
                  <?php endif; ?>

                  <!-- ============ INFORMATIONS ETUDIANT ============ -->
                  <hr>
                  <h6 class="fw-bold mb-3" style="color:#334155;"><i class="fas fa-graduation-cap me-2 text-primary"></i>Informations Etudiant <small class="fw-normal text-muted ms-2">(auto-remplies par IA)</small></h6>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Niveau</label>
                      <input type="text" name="niveau" id="profilNiveau" class="form-control ia-field" value="<?= htmlspecialchars($userData['niveau'] ?? '') ?>" placeholder="Ex: 2eme annee">
                      <div class="text-danger small mt-1" id="err-niveau"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Classe</label>
                      <input type="text" name="classe" id="profilClasse" class="form-control ia-field" value="<?= htmlspecialchars($userData['classe'] ?? '') ?>" placeholder="Ex: 2A35">
                      <div class="text-danger small mt-1" id="err-classe"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Email universitaire</label>
                      <input type="text" name="email_universitaire" id="profilEmailUniv" class="form-control ia-field" value="<?= htmlspecialchars($userData['email_universitaire'] ?? '') ?>" placeholder="prenom.nom@esprit.tn">
                      <div class="text-danger small mt-1" id="err-email-univ"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Etablissement / Ecole</label>
                      <input type="text" name="etablissement_ecole" id="profilEtablissement" class="form-control ia-field" value="<?= htmlspecialchars($userData['etablissement_ecole'] ?? '') ?>" placeholder="Ex: ESPRIT">
                      <div class="text-danger small mt-1" id="err-etablissement"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Identifiant carte</label>
                      <input type="text" name="identifiant_card" id="profilIdCard" class="form-control ia-field" value="<?= htmlspecialchars($userData['identifiant_card'] ?? '') ?>" placeholder="Ex: 2A35-12345">
                      <div class="text-danger small mt-1" id="err-idcard"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Annee universitaire</label>
                      <input type="text" name="annee_universitaire" id="profilAnneeUniv" class="form-control ia-field" value="<?= htmlspecialchars($userData['annee_universitaire'] ?? '') ?>" placeholder="Ex: 2025-2026">
                      <div class="text-danger small mt-1" id="err-annee"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Specialite</label>
                      <input type="text" name="specialite" id="profilSpecialite" class="form-control ia-field" value="<?= htmlspecialchars($userData['specialite'] ?? '') ?>" placeholder="Ex: Informatique">
                      <div class="text-danger small mt-1" id="err-specialite"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Adresse</label>
                      <input type="text" name="adresse" id="profilAdresse" class="form-control ia-field" value="<?= htmlspecialchars($userData['adresse'] ?? '') ?>" placeholder="Ex: Tunis, Ariana">
                      <div class="text-danger small mt-1" id="err-adresse"></div>
                    </div>
                  </div>
                  <?php endif; ?>

                  <?php if (($userData['role'] ?? '') === 'encadrant'): ?>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Specialite</label>
                    <input type="text" name="specialite" class="form-control" value="<?= htmlspecialchars($userData['specialite'] ?? '') ?>" placeholder="Ex: Developpement Web">
                  </div>
                  <?php endif; ?>
                  <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-floppy-disk me-2"></i>Enregistrer</button>
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
            <p class="mt-3 text-light opacity-75">Plateforme intelligente connectant les etudiants avec des professeurs experts pour un apprentissage personnalise.</p>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Navigation</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php" class="text-light text-decoration-none">Accueil</a></li>
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
        <p class="text-center text-light opacity-75 mb-0">&copy; 2026 EduMatch. Tous droits reserves.</p>
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

    /* ========== Photo de profil : click to change + live preview ========== */
    var photoWrapper = document.getElementById('photoUploadWrapper');
    var photoInput = document.getElementById('photoFileInput');
    var photoPreview = document.getElementById('photoPreview');

    if (photoWrapper && photoInput) {
      photoWrapper.addEventListener('click', function(e) {
        if (e.target !== photoInput) photoInput.click();
      });
      photoInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
          var reader = new FileReader();
          reader.onload = function(e) { photoPreview.src = e.target.result; };
          reader.readAsDataURL(this.files[0]);
        }
      });
    }

    /* ========== Groq API helper (via proxy serveur api/groq_profile_assist.php) ==========
       Remplace l'ancien appel Ollama (qui necessitait une installation locale et la dependance
       externe ollama.com en panne). Le proxy serveur charge la cle GROQ_API_KEY depuis .env. */
    var GROQ_PROXY_URL = '/gestion_users/api/groq_profile_assist.php';

    function callGroq(promptText, task) {
      task = task || 'bio_generate'; // 'ocr_extract' | 'bio_generate'
      return fetch(GROQ_PROXY_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task: task, prompt: promptText })
      })
      .then(function(r) {
        if (!r.ok) {
          return r.text().then(function(txt) {
            var msg;
            try { var obj = JSON.parse(txt); msg = obj.error || txt; } catch(e) { msg = txt; }
            throw new Error('Erreur IA: ' + msg);
          });
        }
        return r.json();
      })
      .then(function(data) {
        if (!data || !data.success || !data.response) {
          throw new Error(data && data.error ? data.error : 'Reponse IA vide.');
        }
        return data.response;
      });
    }

    /* Alias retro-compatible : conserve le nom callOllama pour ne pas casser le reste du code */
    function callOllama(promptText) {
      return callGroq(promptText, 'bio_generate');
    }

    /* ========== Carte Etudiant: drop zone + preview + file input ========== */
    var cardInput    = document.getElementById('cardImageInput');
    var cardDropZone = document.getElementById('cardDropZone');
    var cardPreview  = document.getElementById('cardPreviewContainer');
    var cardImg      = document.getElementById('cardPreviewImg');
    var cardChangeBtn = document.getElementById('cardChangeBtn');
    var iaContainer  = document.getElementById('iaButtonContainer');
    var btnOcr       = document.getElementById('btnOcrIA');

    if (cardDropZone && cardInput) {
      /* Click drop zone -> open file picker */
      cardDropZone.addEventListener('click', function() { cardInput.click(); });

      /* Click "Changer" overlay -> open file picker */
      if (cardChangeBtn) {
        cardChangeBtn.addEventListener('click', function(e) { e.stopPropagation(); cardInput.click(); });
      }

      /* Drag & drop support */
      cardDropZone.addEventListener('dragover', function(e) { e.preventDefault(); this.style.borderColor = '#525fe1'; this.style.background = '#eef2ff'; });
      cardDropZone.addEventListener('dragleave', function() { this.style.borderColor = ''; this.style.background = ''; });
      cardDropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = ''; this.style.background = '';
        if (e.dataTransfer.files.length > 0) {
          cardInput.files = e.dataTransfer.files;
          cardInput.dispatchEvent(new Event('change'));
        }
      });

      /* When file selected: show preview + IA button */
      cardInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
          var reader = new FileReader();
          reader.onload = function(ev) {
            cardImg.src = ev.target.result;
            cardDropZone.style.display = 'none';
            cardPreview.style.display = 'block';
            if (iaContainer) iaContainer.style.display = 'block';
          };
          reader.readAsDataURL(this.files[0]);
        }
      });
    }

    /* ========== OCR + Gemini : extract data from student card ========== */
    if (btnOcr && cardInput) {
      btnOcr.addEventListener('click', function() {
        var file = cardInput.files[0];
        /* If no new file selected but card_image exists in DB, use the existing image via URL */
        var useExisting = !file && cardImg && cardImg.src && cardImg.src.indexOf('data:') === -1 && cardImg.src.indexOf('default.png') === -1;

        if (!file && !useExisting) return;

        var loading   = document.getElementById('ocrLoading');
        var resultDiv = document.getElementById('ocrResult');
        var errDiv    = document.getElementById('ocrError');
        loading.style.display = 'block';
        resultDiv.style.display = 'none';
        errDiv.style.display = 'none';
        btnOcr.disabled = true;

        var ocrPromise;

        if (file) {
          /* OCR from uploaded file */
          var formData = new FormData();
          formData.append('file', file);
          formData.append('apikey', 'helloworld');
          formData.append('language', 'fre');
          formData.append('isOverlayRequired', 'false');
          ocrPromise = fetch('https://api.ocr.space/parse/image', { method: 'POST', body: formData });
        } else {
          /* OCR from existing image URL */
          var imgUrl = window.location.origin + cardImg.getAttribute('src');
          var formData2 = new FormData();
          formData2.append('url', imgUrl);
          formData2.append('apikey', 'helloworld');
          formData2.append('language', 'fre');
          formData2.append('isOverlayRequired', 'false');
          ocrPromise = fetch('https://api.ocr.space/parse/image', { method: 'POST', body: formData2 });
        }

        ocrPromise
          .then(function(r) { return r.json(); })
          .then(function(ocrData) {
            if (!ocrData.ParsedResults || ocrData.ParsedResults.length === 0 || ocrData.IsErroredOnProcessing) {
              throw new Error(ocrData.ErrorMessage || 'OCR a echoue.');
            }
            var ocrText = ocrData.ParsedResults[0].ParsedText;
            if (!ocrText || ocrText.trim().length < 5) {
              throw new Error('Aucun texte detecte sur la carte.');
            }

            var prompt = "/no_think\nVoici le texte extrait par OCR d'une carte etudiant :\n\n" + ocrText +
              "\n\nAnalyse ce texte et extrais les informations suivantes au format JSON strict (sans markdown, sans ```json) :" +
              "\n{\"nom\": \"\", \"prenom\": \"\", \"classe\": \"\", \"email_universitaire\": \"\", \"etablissement_ecole\": \"\", \"identifiant_card\": \"\", \"annee_universitaire\": \"\", \"specialite\": \"\", \"adresse\": \"\", \"niveau\": \"\"}" +
              "\n\nRegles :" +
              "\n- Remplis uniquement les champs que tu peux identifier avec certitude dans le texte." +
              "\n- Laisse vide (\"\") les champs non trouvables." +
              "\n- Reponds UNIQUEMENT avec le JSON, rien d'autre.";

            return callGroq(prompt, 'ocr_extract');
          })
          .then(function(rawText) {
            /* Clean: remove <think> blocks, markdown wrappers */
            var text = rawText.replace(/<think>[\s\S]*?<\/think>/g, '').replace(/```json\s*/g, '').replace(/```\s*/g, '').trim();
            /* Extract first JSON object if there's extra text */
            var jsonMatch = text.match(/\{[\s\S]*\}/);
            if (!jsonMatch) throw new Error('Aucun JSON valide dans la reponse IA.');
            var data = JSON.parse(jsonMatch[0]);

            /* Pre-fill fields with highlight animation */
            var filled = [];
            var fieldMap = {
              'profilNom': data.nom,
              'profilPrenom': data.prenom,
              'profilClasse': data.classe,
              'profilEmailUniv': data.email_universitaire,
              'profilEtablissement': data.etablissement_ecole,
              'profilIdCard': data.identifiant_card,
              'profilAnneeUniv': data.annee_universitaire,
              'profilAdresse': data.adresse,
              'profilSpecialite': data.specialite,
              'profilNiveau': data.niveau
            };

            var delay = 0;
            for (var id in fieldMap) {
              var el = document.getElementById(id);
              if (el && fieldMap[id]) {
                (function(element, value, d) {
                  setTimeout(function() {
                    element.value = value;
                    element.classList.add('field-prefilled');
                    setTimeout(function() { element.classList.remove('field-prefilled'); }, 4000);
                  }, d);
                })(el, fieldMap[id], delay);
                filled.push(id);
                delay += 150; /* stagger the highlight animation */
              }
            }

            loading.style.display = 'none';
            resultDiv.style.display = 'block';
            resultDiv.querySelector('span').textContent = filled.length > 0
              ? 'IA a pre-rempli ' + filled.length + ' champ(s). Verifiez et cliquez Enregistrer.'
              : 'Aucun nouveau champ a remplir (tous deja remplis).';

            /* Scroll to the fields */
            if (filled.length > 0) {
              var firstField = document.getElementById(filled[0]);
              if (firstField) firstField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          })
          .catch(function(err) {
            loading.style.display = 'none';
            errDiv.style.display = 'block';
            errDiv.querySelector('span').textContent = 'Erreur: ' + err.message;
          })
          .finally(function() {
            btnOcr.disabled = false;
          });
      });
    }

    /* ========== Suggestion bio IA (Gemini) ========== */
    var btnBio = document.getElementById('btnGenBio');
    if (btnBio) {
      btnBio.addEventListener('click', function() {
        var bioField = document.getElementById('profilBio');
        var loadingEl = document.getElementById('bioLoading');
        var errEl = document.getElementById('bioError');
        loadingEl.style.display = 'inline';
        errEl.style.display = 'none';
        btnBio.disabled = true;

        var nom = document.getElementById('profilNom') ? document.getElementById('profilNom').value : '';
        var prenom = document.getElementById('profilPrenom') ? document.getElementById('profilPrenom').value : '';
        var role = '<?= htmlspecialchars($userData['role'] ?? '') ?>';
        var niveau = document.getElementById('profilNiveau') ? document.getElementById('profilNiveau').value : '';
        var specialite = document.getElementById('profilSpecialite') ? document.getElementById('profilSpecialite').value : '';
        var classe = document.getElementById('profilClasse') ? document.getElementById('profilClasse').value : '';
        var etablissement = document.getElementById('profilEtablissement') ? document.getElementById('profilEtablissement').value : '';

        var prompt = "/no_think\nGenere une courte bio professionnelle (2-3 phrases max, en francais) pour un profil sur une plateforme educative." +
          "\nPrenom: " + prenom + "\nNom: " + nom + "\nRole: " + role +
          (niveau ? "\nNiveau: " + niveau : "") +
          (specialite ? "\nSpecialite: " + specialite : "") +
          (classe ? "\nClasse: " + classe : "") +
          (etablissement ? "\nEtablissement: " + etablissement : "") +
          "\n\nReponds uniquement avec le texte de la bio, sans guillemets, sans markdown.";

        callGroq(prompt, 'bio_generate')
        .then(function(rawText) {
          /* Clean: remove <think> blocks if any */
          var bio = rawText.replace(/<think>[\s\S]*?<\/think>/g, '').trim();
          bioField.value = bio;
          bioField.style.borderColor = '#10b981';
          setTimeout(function() { bioField.style.borderColor = ''; }, 3000);
        })
        .catch(function(err) {
          errEl.style.display = 'block';
          errEl.textContent = 'Erreur IA: ' + err.message;
        })
        .finally(function() {
          loadingEl.style.display = 'none';
          btnBio.disabled = false;
        });
      });
    }
    </script>
  </body>
</html>
