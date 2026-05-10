<?php
/**
 * Navbar commune EduMatch — inclure dans toutes les pages frontoffice
 * Usage: include __DIR__ . '/_navbar.php'; (depuis view/template/)
 *   ou : include dirname(__DIR__) . '/template/_navbar.php'; (depuis view/frontoffice/)
 * 
 * Prerequis: session_start() et config/database.php deja charges
 */

// Base URL absolue pour les assets et liens
$baseUrl = '/gestion_users';

// Auto-refresh session data depuis la DB (role, nom, prenom, photo)
// Garantit que les changements en DB (role modifie par admin, etc.) sont toujours refletes
if (!empty($_SESSION['user_id'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/config/database.php';
    $stmt = Config::getConnexion()->prepare("SELECT nom, prenom, photo, role FROM user WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['user_nom']    = $u['nom'];
        $_SESSION['user_prenom'] = $u['prenom'];
        $_SESSION['user_photo']  = $u['photo'];
        $_SESSION['user_role']   = $u['role'];
    }
}
?>
<!-- START NAVBAR -->
<div id="navigation" class="navbar-light bg-faded site-navigation">
  <div class="container-fluid">
    <div class="row">
      <div class="col-20 align-self-center">
        <div class="site-logo">
          <a href="<?= $baseUrl ?>/view/template/index.php"><img src="<?= $baseUrl ?>/assets/img/logo.png" alt="EduMatch" style="height:50px;" /></a>
        </div>
      </div>

      <div class="col-60 d-flex">
        <nav id="main-menu">
          <ul>
            <li><a href="<?= $baseUrl ?>/view/template/index.php">HOME</a></li>
            <li><a href="#">ABOUT</a></li>
            <li class="menu-item-has-children">
              <a href="#">EDUFEED</a>
              <ul>
                <li><a href="<?= $baseUrl ?>/view/frontoffice/submit.php">Soumettre un devoir</a></li>
                <li><a href="<?= $baseUrl ?>/view/frontoffice/feed.php">Feed</a></li>
              </ul>
            </li>
            <li><a href="#">EVENEMENT</a></li>
            <li><a href="#">QUIZ</a></li>
            <li><a href="#">OFFRE D'EMPLOI</a></li>
          </ul>
        </nav>
      </div>

      <div class="col-20 d-none d-xl-block text-end align-self-center">
        <?php if (!empty($_SESSION['user_id'])): ?>
        <div class="header-group" style="justify-content: flex-end;">
          <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
          <a href="<?= $baseUrl ?>/view/backoffice/src/pages/backoffice/users.php" class="btn-backoffice"><i class="fas fa-tachometer-alt"></i> Backoffice</a>
          <?php endif; ?>
          <div class="user-dropdown" id="userDropdown">
            <img src="<?= $baseUrl ?>/uploads/photos/<?= htmlspecialchars($_SESSION['user_photo'] ?? 'default.png') ?>" alt="Photo" class="user-avatar" onerror="this.src='<?= $baseUrl ?>/uploads/photos/default.png';">
            <span class="user-name"><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></span>
            <i class="fas fa-chevron-down dropdown-caret"></i>
            <div class="user-dropdown-menu" id="userDropdownMenu">
              <a href="<?= $baseUrl ?>/view/template/profil.php"><i class="fas fa-user"></i> Mon Profil</a>
              <hr>
              <a href="<?= $baseUrl ?>/auth/logout"><i class="fas fa-sign-out-alt"></i> Deconnexion</a>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="header-group">
          <a href="<?= $baseUrl ?>/view/template/sign-in.php" class="header-btn">Connexion</a>
          <a href="<?= $baseUrl ?>/view/template/sign-up.php" class="btn_one">Inscription</a>
        </div>
        <?php endif; ?>
      </div>

      <!-- Mobile menu -->
      <ul class="mobile_menu">
        <li><a href="<?= $baseUrl ?>/view/template/index.php">Home</a></li>
        <li><a href="#">About</a></li>
        <li>
          <a href="#">Edufeed</a>
          <ul class="sub-menu">
            <li><a href="<?= $baseUrl ?>/view/frontoffice/submit.php">Soumettre un devoir</a></li>
            <li><a href="<?= $baseUrl ?>/view/frontoffice/feed.php">Feed</a></li>
          </ul>
        </li>
        <li><a href="#">Evenement</a></li>
        <li><a href="#">Quiz</a></li>
        <li><a href="#">Offre d'emploi</a></li>
        <?php if (!empty($_SESSION['user_id'])): ?>
        <li><a href="<?= $baseUrl ?>/view/template/profil.php">Mon Profil</a></li>
        <li><a href="<?= $baseUrl ?>/auth/logout">Deconnexion</a></li>
        <?php else: ?>
        <li><a href="<?= $baseUrl ?>/view/template/sign-in.php">Connexion</a></li>
        <li><a href="<?= $baseUrl ?>/view/template/sign-up.php">Inscription</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>
<!-- END NAVBAR -->

<script>
document.addEventListener('DOMContentLoaded', function() {
  var dd = document.getElementById('userDropdown');
  var menu = document.getElementById('userDropdownMenu');
  if (dd && menu) {
    dd.addEventListener('click', function(e) {
      e.stopPropagation();
      menu.classList.toggle('show');
    });
    document.addEventListener('click', function() {
      menu.classList.remove('show');
    });
  }
});
</script>
