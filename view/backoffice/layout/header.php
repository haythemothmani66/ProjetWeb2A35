<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/config/database.php';

// Protection : seul admin peut acceder au backoffice
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}

// Refresh session si besoin
if (empty($_SESSION['user_prenom'])) {
    $s = Config::getConnexion()->prepare("SELECT nom,prenom,photo FROM user WHERE id=? LIMIT 1");
    $s->execute([$_SESSION['user_id']]);
    $u = $s->fetch();
    if ($u) {
        $_SESSION['user_nom']    = $u['nom'];
        $_SESSION['user_prenom'] = $u['prenom'];
        $_SESSION['user_photo']  = $u['photo'];
    }
}

$pageTitle = $pageTitle ?? 'EduMatch BackOffice';
$messages = $messages ?? [];
$BO = '/gestion_users/view/backoffice/src';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= htmlspecialchars($pageTitle) ?> | EduMatch Admin</title>
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
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/backoffice/src/partials_php/sidebar.php'; ?>
    <div id="content" class="position-relative h-100">
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/backoffice/src/partials_php/topbar.php'; ?>
      <div class="custom-container">

        <?php foreach ($messages as $message): ?>
          <?php
          $type = (string)($message['type'] ?? 'info');
          $allowed = ['success', 'danger', 'warning', 'info'];
          if (!in_array($type, $allowed, true)) { $type = 'info'; }
          ?>
          <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars((string)($message['message'] ?? '')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endforeach; ?>
