<?php session_start();
require_once __DIR__ . '/../../../../../config/database.php';
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php'); exit;
}
if (empty($_SESSION['user_prenom'])) {
    $s = Config::getConnexion()->prepare("SELECT nom,prenom,photo FROM user WHERE id=? LIMIT 1");
    $s->execute([$_SESSION['user_id']]); $u=$s->fetch();
    if($u){$_SESSION['user_nom']=$u['nom'];$_SESSION['user_prenom']=$u['prenom'];$_SESSION['user_photo']=$u['photo'];}
}

$db = Config::getConnexion();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT u.*, p.bio_text, p.niveau, p.specialite, p.created_at AS profil_created FROM user u LEFT JOIN profil p ON p.user_id=u.id WHERE u.id=? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { $_SESSION['errors'] = ["Utilisateur introuvable."]; header('Location: users.php'); exit; }

$BO = '/gestion_users/view/backoffice/src';
$isVerified = ($user['token_verif'] === null);
$roleColors = ['admin'=>'danger','encadrant'=>'warning','etudiant'=>'info'];
$roleBg = $roleColors[$user['role']] ?? 'secondary';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Details <?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?> | EduMatch Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="<?= $BO ?>/assets/css/theme.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
  <script src="<?= $BO ?>/assets/js/vendors/color-modes.js"></script>
  <script>
    if(localStorage.getItem('sidebarExpanded')==='false'){document.documentElement.classList.add('collapsed');document.documentElement.classList.remove('expanded');}
    else{document.documentElement.classList.remove('collapsed');document.documentElement.classList.add('expanded');}
  </script>
  <style>
    .detail-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; margin-bottom: 4px; font-weight: 600; }
    .detail-value { font-size: 15px; font-weight: 500; }
    .profile-header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 16px; padding: 40px; color: white; position: relative; overflow: hidden; }
    .profile-header::before { content: ''; position: absolute; top: -50%; right: -20%; width: 400px; height: 400px; background: rgba(255,255,255,0.05); border-radius: 50%; }
    .profile-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,0.3); }
    .info-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
  </style>
</head>
<body>
  <div>
    <?php include __DIR__ . '/../../partials_php/sidebar.php'; ?>
    <div id="content" class="position-relative h-100">
      <?php include __DIR__ . '/../../partials_php/topbar.php'; ?>
      <div class="custom-container">

        <!-- Back button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <p class="text-uppercase text-secondary small mb-1">EduMatch BackOffice</p>
            <h1 class="mb-0">Details utilisateur</h1>
          </div>
          <div class="d-flex gap-2">
            <a href="edit-user.php?id=<?= $user['id'] ?>" class="btn btn-dark"><i class="ti ti-edit me-1"></i>Modifier</a>
            <a href="users.php" class="btn btn-white"><i class="ti ti-arrow-left me-1"></i>Retour</a>
          </div>
        </div>

        <!-- Profile Header Card -->
        <div class="profile-header mb-4">
          <div class="d-flex flex-column flex-md-row align-items-center gap-4">
            <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($user['photo']) ?>" class="profile-avatar" onerror="this.src='/gestion_users/uploads/photos/default.png';">
            <div class="text-center text-md-start">
              <h2 class="fw-bold mb-1"><?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?></h2>
              <p class="mb-2 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
              <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                <span class="badge bg-<?= $roleBg ?> fs-6"><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
                <?php if (!$isVerified): ?>
                  <span class="badge bg-warning fs-6">Non verifie</span>
                <?php elseif ($user['statut'] == 1): ?>
                  <span class="badge bg-success fs-6">Actif</span>
                <?php else: ?>
                  <span class="badge bg-danger fs-6">Bloque</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <!-- Informations personnelles -->
          <div class="col-lg-6">
            <div class="card info-card h-100">
              <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="fw-bold mb-0"><i class="ti ti-user me-2 text-primary"></i>Informations personnelles</h5>
              </div>
              <div class="card-body">
                <div class="row g-4">
                  <div class="col-6">
                    <div class="detail-label">Nom</div>
                    <div class="detail-value"><?= htmlspecialchars($user['nom']) ?></div>
                  </div>
                  <div class="col-6">
                    <div class="detail-label">Prenom</div>
                    <div class="detail-value"><?= htmlspecialchars($user['prenom']) ?></div>
                  </div>
                  <div class="col-6">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?= htmlspecialchars($user['email']) ?></div>
                  </div>
                  <div class="col-6">
                    <div class="detail-label">Telephone</div>
                    <div class="detail-value"><?= htmlspecialchars($user['telephone'] ?: '—') ?></div>
                  </div>
                  <div class="col-6">
                    <div class="detail-label">Role</div>
                    <div class="detail-value"><span class="badge bg-<?= $roleBg ?>"><?= htmlspecialchars(ucfirst($user['role'])) ?></span></div>
                  </div>
                  <div class="col-6">
                    <div class="detail-label">Photo</div>
                    <div class="detail-value"><?= htmlspecialchars($user['photo']) ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Statut & Securite -->
          <div class="col-lg-6">
            <div class="card info-card h-100">
              <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="fw-bold mb-0"><i class="ti ti-shield-check me-2 text-success"></i>Statut & Securite</h5>
              </div>
              <div class="card-body">
                <div class="row g-4">
                  <div class="col-6">
                    <div class="detail-label">Statut du compte</div>
                    <div class="detail-value">
                      <?php if ($user['statut'] == 1): ?>
                        <span class="badge bg-success-subtle text-success">Actif</span>
                      <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger">Bloque</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="detail-label">Verification email</div>
                    <div class="detail-value">
                      <?php if ($isVerified): ?>
                        <span class="badge bg-success-subtle text-success"><i class="ti ti-check me-1"></i>Verifie</span>
                      <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning"><i class="ti ti-clock me-1"></i>En attente</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="detail-label">Date d'inscription</div>
                    <div class="detail-value"><?= date('d/m/Y a H:i', strtotime($user['created_at'])) ?></div>
                  </div>
                  <div class="col-6">
                    <div class="detail-label">ID utilisateur</div>
                    <div class="detail-value">#<?= $user['id'] ?></div>
                  </div>
                  <?php if ($user['reset_code']): ?>
                  <div class="col-6">
                    <div class="detail-label">Code reset actif</div>
                    <div class="detail-value"><span class="badge bg-info-subtle text-info"><?= htmlspecialchars($user['reset_code']) ?></span></div>
                  </div>
                  <?php endif; ?>
                  <?php if ($user['reset_expires']): ?>
                  <div class="col-6">
                    <div class="detail-label">Expiration reset</div>
                    <div class="detail-value"><?= date('d/m/Y H:i', strtotime($user['reset_expires'])) ?></div>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Profil (table profil) -->
        <div class="row g-4 mb-4">
          <div class="col-12">
            <div class="card info-card">
              <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="fw-bold mb-0"><i class="ti ti-id-badge me-2 text-warning"></i>Profil detaille</h5>
              </div>
              <div class="card-body">
                <div class="row g-4">
                  <div class="col-md-12">
                    <div class="detail-label">Biographie</div>
                    <div class="detail-value"><?= htmlspecialchars($user['bio_text'] ?: '— Non renseignee —') ?></div>
                  </div>
                  <?php if ($user['role'] === 'etudiant'): ?>
                  <div class="col-md-6">
                    <div class="detail-label">Niveau</div>
                    <div class="detail-value"><?= htmlspecialchars($user['niveau'] ?: '— Non renseigne —') ?></div>
                  </div>
                  <?php endif; ?>
                  <?php if ($user['role'] === 'encadrant'): ?>
                  <div class="col-md-6">
                    <div class="detail-label">Specialite</div>
                    <div class="detail-value"><?= htmlspecialchars($user['specialite'] ?: '— Non renseignee —') ?></div>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card info-card mb-4">
          <div class="card-body d-flex flex-wrap gap-2">
            <a href="edit-user.php?id=<?= $user['id'] ?>" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Modifier</a>
            <form action="/gestion_users/user/toggleStatut" method="POST" class="d-inline">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <button type="submit" class="btn <?= $user['statut']==1?'btn-warning':'btn-success' ?>">
                <i class="ti <?= $user['statut']==1?'ti-lock':'ti-lock-open' ?> me-1"></i><?= $user['statut']==1?'Bloquer':'Debloquer' ?>
              </button>
            </form>
            <form action="/gestion_users/user/delete" method="POST" class="d-inline form-delete">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Supprimer</button>
            </form>
            <a href="users.php" class="btn btn-white ms-auto"><i class="ti ti-arrow-left me-1"></i>Retour a la liste</a>
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
