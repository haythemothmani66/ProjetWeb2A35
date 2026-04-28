<?php session_start();
require_once __DIR__ . '/../../../../../config/database.php';
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php'); exit;
}
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

$db = Config::getConnexion();
$search = trim($_GET['search'] ?? '');
$role = trim($_GET['role'] ?? '');
$sql = "SELECT * FROM user WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }
if ($role) { $sql .= " AND role = ?"; $params[] = $role; }
$sql .= " ORDER BY created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$BO = '/gestion_users/view/backoffice/src';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Gestion Utilisateurs | EduMatch Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="<?= $BO ?>/assets/css/theme.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
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
        <!-- Flash messages -->
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <ul class="mb-0"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row mb-6 g-6 align-items-center">
          <div class="col-lg-8 col-12">
            <p class="text-uppercase text-secondary small mb-2">EduMatch BackOffice</p>
            <h1 class="mb-2">Gestion des Utilisateurs</h1>
            <p class="mb-0 text-secondary"><?= count($users) ?> utilisateur(s) au total</p>
          </div>
          <div class="col-lg-4 col-12 text-lg-end">
            <a href="add-user.php" class="btn btn-dark">
              <i class="ti ti-user-plus me-1"></i>Ajouter
            </a>
          </div>
        </div>

        <!-- Recherche -->
        <div class="card card-lg mb-6">
          <div class="card-body">
            <form action="" method="GET" class="row g-3 align-items-end">
              <div class="col-lg-5 col-12">
                <label class="form-label">Rechercher</label>
                <input type="text" name="search" class="form-control" placeholder="Rechercher nom, prenom, email..."
                       value="<?= htmlspecialchars($search) ?>">
              </div>
              <div class="col-lg-3 col-md-6 col-12">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                  <option value="">Tous les roles</option>
                  <option value="admin" <?= $role==='admin'?'selected':'' ?>>Admin</option>
                  <option value="encadrant" <?= $role==='encadrant'?'selected':'' ?>>Encadrant</option>
                  <option value="etudiant" <?= $role==='etudiant'?'selected':'' ?>>Etudiant</option>
                </select>
              </div>
              <div class="col-lg-4 col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark flex-grow-1"><i class="ti ti-filter me-1"></i>Filtrer</button>
                <a href="users.php" class="btn btn-white">Reset</a>
              </div>
            </form>
          </div>
        </div>

        <!-- Table -->
        <div class="card card-lg mb-6">
          <div class="card-header border-bottom-0 d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0">Liste des utilisateurs</h5>
            <span class="badge text-primary-emphasis bg-primary-subtle"><?= count($users) ?> utilisateur(s)</span>
          </div>
          <div class="table-responsive">
            <table class="table text-nowrap mb-0 table-centered table-hover align-middle">
              <thead>
                <tr>
                  <th>#</th><th>Photo</th><th>Nom Complet</th><th>Email</th><th>Role</th><th>Statut</th><th>Inscrit le</th><th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="8" class="text-center py-5 text-secondary">Aucun utilisateur trouve</td></tr>
                <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= $u['id'] ?></td>
                  <td><img src="/gestion_users/uploads/photos/<?= htmlspecialchars($u['photo']) ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover;"></td>
                  <td>
                    <div class="fw-semibold"><?= htmlspecialchars($u['nom'].' '.$u['prenom']) ?></div>
                    <?php if($u['telephone']): ?><small class="text-secondary"><?= htmlspecialchars($u['telephone']) ?></small><?php endif; ?>
                  </td>
                  <td class="text-secondary"><?= htmlspecialchars($u['email']) ?></td>
                  <td>
                    <?php $colors = ['admin'=>'danger','encadrant'=>'warning','etudiant'=>'info']; ?>
                    <span class="badge bg-<?= $colors[$u['role']] ?? 'secondary' ?>"><?= htmlspecialchars($u['role']) ?></span>
                  </td>
                  <td>
                    <?php if($u['statut']==1): ?>
                      <span class="badge bg-success-subtle text-success">Actif</span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger">Bloque</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-secondary"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                  <td>
                    <div class="d-flex justify-content-end gap-1">
                      <a href="edit-user.php?id=<?= $u['id'] ?>" class="btn btn-ghost btn-icon btn-sm"><i class="ti ti-edit text-primary"></i></a>
                      <form action="/gestion_users/user/toggleStatut" method="POST" class="d-inline">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-ghost btn-icon btn-sm">
                          <i class="ti <?= $u['statut']==1?'ti-lock text-warning':'ti-lock-open text-success' ?>"></i>
                        </button>
                      </form>
                      <form action="/gestion_users/user/delete" method="POST" class="d-inline form-delete">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-ghost btn-icon btn-sm"><i class="ti ti-trash text-danger"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
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
