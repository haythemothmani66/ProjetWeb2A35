<?php session_start();
require_once __DIR__ . '/../../../../../config/database.php';
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php'); exit;
}
/* Auto-refresh session */
if (empty($_SESSION['user_prenom'])) {
    $s = Config::getConnexion()->prepare("SELECT nom,prenom,photo FROM user WHERE id=? LIMIT 1");
    $s->execute([$_SESSION['user_id']]); $u=$s->fetch();
    if($u){$_SESSION['user_nom']=$u['nom'];$_SESSION['user_prenom']=$u['prenom'];$_SESSION['user_photo']=$u['photo'];}
}

$db = Config::getConnexion();

/* KPIs */
$totalUsers   = $db->query("SELECT COUNT(*) FROM user")->fetchColumn();
$activeUsers  = $db->query("SELECT COUNT(*) FROM user WHERE statut=1")->fetchColumn();
$blockedUsers = $db->query("SELECT COUNT(*) FROM user WHERE statut=0")->fetchColumn();
$unverified   = $db->query("SELECT COUNT(*) FROM user WHERE token_verif IS NOT NULL")->fetchColumn();

/* Users par role */
$rolesStmt = $db->query("SELECT role, COUNT(*) as cnt FROM user GROUP BY role ORDER BY role");
$rolesData = $rolesStmt->fetchAll();
$roleLabels = []; $roleCounts = [];
foreach($rolesData as $r){ $roleLabels[] = ucfirst($r['role']); $roleCounts[] = (int)$r['cnt']; }

/* Users currently online */
$onlineUsers = $db->query("SELECT COUNT(*) FROM user WHERE etat='online'")->fetchColumn();

/* Last 10 connexions */
$last10Stmt = $db->query("
    SELECT ch.connected_at, ch.disconnected_at, ch.ip_address,
           u.nom, u.prenom, u.photo, u.role, u.etat
    FROM connexion_history ch
    JOIN user u ON u.id = ch.user_id
    ORDER BY ch.connected_at DESC
    LIMIT 10
");
$last10Connexions = $last10Stmt->fetchAll();

/* Users crees par mois (12 derniers mois) */
$monthsStmt = $db->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') as mois, COUNT(*) as cnt
    FROM user
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY mois ORDER BY mois
");
$monthsData = $monthsStmt->fetchAll();
$monthLabels = []; $monthCounts = [];
foreach($monthsData as $m){
    $monthLabels[] = date('M Y', strtotime($m['mois'].'-01'));
    $monthCounts[] = (int)$m['cnt'];
}

$BO = '/gestion_users/view/backoffice/src';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Tableau de bord | EduMatch Admin</title>
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

        <div class="row mb-6 g-6">
          <div class="col-12">
            <p class="text-uppercase text-secondary small mb-2">EduMatch BackOffice</p>
            <h1 class="mb-0">Tableau de bord</h1>
          </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-4 mb-6">
          <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:rgba(99,102,241,0.1);">
                  <i class="ti ti-users fs-3" style="color:#6366f1;"></i>
                </div>
                <div>
                  <h3 class="mb-0 fw-bold"><?= $totalUsers ?></h3>
                  <p class="mb-0 text-secondary small">Total utilisateurs</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:rgba(16,185,129,0.1);">
                  <i class="ti ti-user-check fs-3" style="color:#10b981;"></i>
                </div>
                <div>
                  <h3 class="mb-0 fw-bold"><?= $activeUsers ?></h3>
                  <p class="mb-0 text-secondary small">Utilisateurs actifs</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:rgba(239,68,68,0.1);">
                  <i class="ti ti-user-off fs-3" style="color:#ef4444;"></i>
                </div>
                <div>
                  <h3 class="mb-0 fw-bold"><?= $blockedUsers ?></h3>
                  <p class="mb-0 text-secondary small">Utilisateurs bloques</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:rgba(245,158,11,0.1);">
                  <i class="ti ti-mail-off fs-3" style="color:#f59e0b;"></i>
                </div>
                <div>
                  <h3 class="mb-0 fw-bold"><?= $unverified ?></h3>
                  <p class="mb-0 text-secondary small">Non verifies</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Online KPI -->
        <div class="row g-4 mb-6">
          <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #10b981 !important;">
              <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:rgba(16,185,129,0.15);">
                  <i class="ti ti-broadcast fs-3" style="color:#10b981;"></i>
                </div>
                <div>
                  <h3 class="mb-0 fw-bold" style="color:#10b981;"><?= $onlineUsers ?></h3>
                  <p class="mb-0 text-secondary small">Utilisateurs en ligne maintenant</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-6">
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="card-title mb-0">Inscriptions par mois</h5>
              </div>
              <div class="card-body">
                <canvas id="chartMonthly" height="300"></canvas>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="card-title mb-0">Repartition par role</h5>
              </div>
              <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="chartRoles" height="280"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- 10 derniers connexions -->
        <div class="row g-4 mb-6">
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-transparent border-0 pb-0 d-flex align-items-center gap-2">
                <i class="ti ti-history fs-4 text-primary"></i>
                <h5 class="card-title mb-0">10 dernieres connexions</h5>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover mb-0 align-middle">
                    <thead>
                      <tr>
                        <th>Utilisateur</th>
                        <th>Role</th>
                        <th>Connecte le</th>
                        <th>Deconnecte le</th>
                        <th>IP</th>
                        <th>Etat</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($last10Connexions)): ?>
                      <tr><td colspan="6" class="text-center py-4 text-secondary">Aucune connexion enregistree</td></tr>
                      <?php else: ?>
                      <?php foreach ($last10Connexions as $cx): ?>
                      <tr>
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($cx['photo']) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover;" onerror="this.src='/gestion_users/uploads/photos/default.png';">
                            <span class="fw-semibold"><?= htmlspecialchars($cx['prenom'].' '.$cx['nom']) ?></span>
                          </div>
                        </td>
                        <td>
                          <?php $colors = ['admin'=>'danger','encadrant'=>'warning','etudiant'=>'info','partenariat'=>'primary']; ?>
                          <span class="badge bg-<?= $colors[$cx['role']] ?? 'secondary' ?> text-capitalize"><?= htmlspecialchars($cx['role']) ?></span>
                        </td>
                        <td class="text-secondary small"><?= date('d/m/Y H:i', strtotime($cx['connected_at'])) ?></td>
                        <td class="text-secondary small">
                          <?php if ($cx['disconnected_at']): ?>
                            <?= date('d/m/Y H:i', strtotime($cx['disconnected_at'])) ?>
                          <?php else: ?>
                            <span class="badge bg-success-subtle text-success">Encore connecte</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-secondary small font-monospace"><?= htmlspecialchars($cx['ip_address'] ?? '-') ?></td>
                        <td>
                          <?php if ($cx['etat'] === 'online'): ?>
                            <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#10b981;" title="En ligne"></span>
                          <?php else: ?>
                            <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#9ca3af;" title="Hors ligne"></span>
                          <?php endif; ?>
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

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
  <script src="<?= $BO ?>/assets/js/main.js"></script>
  <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script>
  /* Monthly line chart */
  new Chart(document.getElementById('chartMonthly'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($monthLabels) ?>,
      datasets: [{
        label: 'Inscriptions',
        data: <?= json_encode($monthCounts) ?>,
        backgroundColor: 'rgba(99,102,241,0.7)',
        borderColor: '#6366f1',
        borderWidth: 2,
        borderRadius: 6,
        barPercentage: 0.6
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
  });
  /* Pie chart by role */
  new Chart(document.getElementById('chartRoles'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($roleLabels) ?>,
      datasets: [{
        data: <?= json_encode($roleCounts) ?>,
        backgroundColor: ['#ef4444','#f59e0b','#6366f1','#3b82f6'],
        borderWidth: 0,
        hoverOffset: 8
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } } }
    }
  });
  </script>
</body>
</html>
