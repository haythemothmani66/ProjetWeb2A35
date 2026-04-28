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
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

$db = Config::getConnexion();
$search = trim($_GET['search'] ?? '');
$role   = trim($_GET['role'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

/* Build WHERE */
$where = "WHERE 1=1";
$params = [];
if ($search) {
    /* Combo search: nom+prenom, prenom+nom, nom, prenom, email */
    $where .= " AND (
        CONCAT(nom,' ',prenom) LIKE ? OR
        CONCAT(prenom,' ',nom) LIKE ? OR
        nom LIKE ? OR
        prenom LIKE ? OR
        email LIKE ?
    )";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}
if ($role) { $where .= " AND role = ?"; $params[] = $role; }

/* Count total */
$countStmt = $db->prepare("SELECT COUNT(*) FROM user {$where}");
$countStmt->execute($params);
$totalUsers = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalUsers / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

/* Fetch page */
$sql = "SELECT * FROM user {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

/* Export: fetch ALL for export (no pagination) */
$exportMode = $_GET['export'] ?? '';
if ($exportMode === 'excel' || $exportMode === 'pdf') {
    $allStmt = $db->prepare("SELECT * FROM user {$where} ORDER BY created_at DESC");
    $allStmt->execute($params);
    $allUsers = $allStmt->fetchAll();

    if ($exportMode === 'excel') {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="utilisateurs_edumatch.xls"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        echo "<table border='1'><tr><th>Nom</th><th>Prenom</th><th>Email</th><th>Telephone</th><th>Role</th><th>Statut</th><th>Inscrit le</th></tr>";
        foreach ($allUsers as $u) {
            echo "<tr>";
            echo "<td>".htmlspecialchars($u['nom'])."</td>";
            echo "<td>".htmlspecialchars($u['prenom'])."</td>";
            echo "<td>".htmlspecialchars($u['email'])."</td>";
            echo "<td>".htmlspecialchars($u['telephone'] ?? '')."</td>";
            echo "<td>".htmlspecialchars($u['role'])."</td>";
            echo "<td>".($u['statut']==1?'Actif':'Bloque')."</td>";
            echo "<td>".date('d/m/Y', strtotime($u['created_at']))."</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }
    if ($exportMode === 'pdf') {
        /* Generate HTML for PDF print */
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Liste Utilisateurs - EduMatch</title>';
        echo '<style>body{font-family:Arial,sans-serif;margin:20px;}h1{color:#333;font-size:20px;}table{width:100%;border-collapse:collapse;margin-top:15px;font-size:12px;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background:#6366f1;color:white;}.badge-actif{color:green;}.badge-bloque{color:red;}@media print{body{margin:0;}}</style>';
        echo '</head><body>';
        echo '<h1>Liste des utilisateurs - EduMatch</h1>';
        echo '<p>Exporte le '.date('d/m/Y H:i').' — '.$totalUsers.' utilisateur(s)</p>';
        echo '<table><tr><th>Nom</th><th>Prenom</th><th>Email</th><th>Telephone</th><th>Role</th><th>Statut</th><th>Inscrit le</th></tr>';
        foreach ($allUsers as $u) {
            $statusClass = $u['statut']==1 ? 'badge-actif' : 'badge-bloque';
            $statusText = $u['statut']==1 ? 'Actif' : 'Bloque';
            echo "<tr>";
            echo "<td>".htmlspecialchars($u['nom'])."</td>";
            echo "<td>".htmlspecialchars($u['prenom'])."</td>";
            echo "<td>".htmlspecialchars($u['email'])."</td>";
            echo "<td>".htmlspecialchars($u['telephone'] ?? '-')."</td>";
            echo "<td>".htmlspecialchars(ucfirst($u['role']))."</td>";
            echo "<td class='{$statusClass}'>{$statusText}</td>";
            echo "<td>".date('d/m/Y', strtotime($u['created_at']))."</td>";
            echo "</tr>";
        }
        echo '</table>';
        echo '<script>window.onload=function(){window.print();}</script>';
        echo '</body></html>';
        exit;
    }
}

$BO = '/gestion_users/view/backoffice/src';
/* Build query string for pagination links */
$qs = http_build_query(array_filter(['search'=>$search,'role'=>$role]));
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

        <!-- Header -->
        <div class="row mb-4 g-4 align-items-center">
          <div class="col-lg-6 col-12">
            <p class="text-uppercase text-secondary small mb-2">EduMatch BackOffice</p>
            <h1 class="mb-1">Gestion des Utilisateurs</h1>
            <p class="mb-0 text-secondary"><?= $totalUsers ?> utilisateur(s) au total</p>
            <!-- Export buttons -->
            <div class="d-flex gap-2 mt-2">
              <a href="?<?= $qs ?>&export=excel" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1">
                <i class="ti ti-file-spreadsheet"></i> Excel
              </a>
              <a href="?<?= $qs ?>&export=pdf" target="_blank" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                <i class="ti ti-file-type-pdf"></i> PDF
              </a>
            </div>
          </div>
          <div class="col-lg-6 col-12 text-lg-end">
            <a href="add-user.php" class="btn btn-dark"><i class="ti ti-user-plus me-1"></i>Ajouter</a>
          </div>
        </div>

        <!-- Search -->
        <div class="card card-lg mb-4">
          <div class="card-body">
            <form action="" method="GET" class="row g-3 align-items-end">
              <div class="col-lg-5 col-12">
                <label class="form-label">Rechercher</label>
                <input type="text" name="search" class="form-control" placeholder="Nom, prenom, nom prenom, prenom nom, email..." value="<?= htmlspecialchars($search) ?>">
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
        <div class="card card-lg mb-4">
          <div class="card-header border-bottom-0 d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0">Liste des utilisateurs</h5>
            <span class="badge text-primary-emphasis bg-primary-subtle"><?= $totalUsers ?> utilisateur(s)</span>
          </div>
          <div class="table-responsive">
            <table class="table text-nowrap mb-0 table-centered table-hover align-middle">
              <thead>
                <tr>
                  <th>Utilisateur</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Statut</th>
                  <th>Inscrit le</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="6" class="text-center py-5 text-secondary">Aucun utilisateur trouve</td></tr>
                <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($u['photo']) ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover;" onerror="this.src='/gestion_users/uploads/photos/default.png';">
                      <div>
                        <div class="fw-semibold"><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?></div>
                        <?php if($u['telephone']): ?><small class="text-secondary"><i class="ti ti-phone" style="font-size:11px;"></i> <?= htmlspecialchars($u['telephone']) ?></small><?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td class="text-secondary"><?= htmlspecialchars($u['email']) ?></td>
                  <td>
                    <?php $colors = ['admin'=>'danger','encadrant'=>'warning','etudiant'=>'info']; ?>
                    <span class="badge bg-<?= $colors[$u['role']] ?? 'secondary' ?> text-capitalize"><?= htmlspecialchars($u['role']) ?></span>
                  </td>
                  <td>
                    <?php if($u['token_verif'] !== null): ?>
                      <span class="badge bg-warning-subtle text-warning">Non verifie</span>
                    <?php elseif($u['statut']==1): ?>
                      <span class="badge bg-success-subtle text-success">Actif</span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger">Bloque</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-secondary"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                  <td>
                    <div class="d-flex justify-content-end gap-1">
                      <a href="view-user.php?id=<?= $u['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="Voir details"><i class="ti ti-eye text-info"></i></a>
                      <a href="edit-user.php?id=<?= $u['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="Modifier"><i class="ti ti-edit text-primary"></i></a>
                      <form action="/gestion_users/user/toggleStatut" method="POST" class="d-inline">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="<?= $u['statut']==1?'Bloquer':'Debloquer' ?>">
                          <i class="ti <?= $u['statut']==1?'ti-lock text-warning':'ti-lock-open text-success' ?>"></i>
                        </button>
                      </form>
                      <form action="/gestion_users/user/delete" method="POST" class="d-inline form-delete">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Supprimer"><i class="ti ti-trash text-danger"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
          <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-secondary">Page <?= $page ?> / <?= $totalPages ?></small>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page<=1?'disabled':'' ?>">
                  <a class="page-link" href="?page=<?= $page-1 ?>&<?= $qs ?>"><i class="ti ti-chevron-left"></i></a>
                </li>
                <?php for($p=1; $p<=$totalPages; $p++): ?>
                <li class="page-item <?= $p===$page?'active':'' ?>">
                  <a class="page-link" href="?page=<?= $p ?>&<?= $qs ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
                  <a class="page-link" href="?page=<?= $page+1 ?>&<?= $qs ?>"><i class="ti ti-chevron-right"></i></a>
                </li>
              </ul>
            </nav>
          </div>
          <?php endif; ?>
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
