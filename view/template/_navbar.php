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
<!-- STYLES NAVBAR (injectes ici pour etre toujours disponibles) -->
<style>
  .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; justify-content: flex-end; }
  .header-btn { background: transparent; color: #0b104a; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; border: 1px solid #525fe1; transition: all 0.3s ease; }
  .header-btn:hover { background: #525fe1; color: white; }
  .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; text-align: center; border: none; cursor: pointer; }
  .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
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
  /* Cacher le menu mobile par defaut en desktop (le JS jquery-simple-mobilemenu.js le transforme en hamburger sur mobile) */
  .mobile_menu { display: none; }
  @media (max-width: 991.98px) {
    .mobile_menu { display: block; }
  }
</style>
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
            <li><a href="<?= $baseUrl ?>/view/template/index.php">ACCUEIL</a></li>
            <li><a href="#">A PROPOS</a></li>
            <li class="menu-item-has-children">
              <a href="#">EDUFEED</a>
              <ul>
                <li><a href="<?= $baseUrl ?>/view/frontoffice/submit.php">Soumettre un devoir</a></li>
                <li><a href="<?= $baseUrl ?>/view/frontoffice/feed.php">Feed</a></li>
              </ul>
            </li>
            <li><a href="<?= $baseUrl ?>/public/index.php?url=Home/index">EVENEMENT</a></li>
            <li><a href="<?= $baseUrl ?>/controller/QuizController.php?espace=front&resource=courses&action=index">QUIZ</a></li>
            <li><a href="<?= $baseUrl ?>/controller/OffreEmploiController.php?espace=front&action=liste">OFFRE D'EMPLOI</a></li>
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
              <?php $_r = $_SESSION['user_role'] ?? ''; ?>
              <?php if ($_r === 'etudiant' || $_r === 'admin'): ?>
                <a href="<?= $baseUrl ?>/view/frontoffice/encadrants_list.php"><i class="fas fa-calendar-plus"></i> Reserver une seance</a>
                <a href="<?= $baseUrl ?>/view/frontoffice/mes_reservations.php"><i class="fas fa-calendar-check"></i> Mes reservations</a>
              <?php endif; ?>
              <?php if ($_r === 'encadrant' || $_r === 'admin'): ?>
                <a href="<?= $baseUrl ?>/view/encadrant/dashboard_reservations.php"><i class="fas fa-chalkboard-teacher"></i> Mes seances</a>
                <a href="<?= $baseUrl ?>/view/encadrant/disponibilites.php"><i class="fas fa-clock"></i> Mes disponibilites</a>
              <?php endif; ?>
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
        <li><a href="<?= $baseUrl ?>/view/template/index.php">Accueil</a></li>
        <li><a href="#">A propos</a></li>
        <li>
          <a href="#">Edufeed</a>
          <ul class="sub-menu">
            <li><a href="<?= $baseUrl ?>/view/frontoffice/submit.php">Soumettre un devoir</a></li>
            <li><a href="<?= $baseUrl ?>/view/frontoffice/feed.php">Feed</a></li>
          </ul>
        </li>
        <li><a href="<?= $baseUrl ?>/public/index.php?url=Home/index">Evenement</a></li>
        <li><a href="<?= $baseUrl ?>/controller/QuizController.php?espace=front&resource=courses&action=index">Quiz</a></li>
        <li><a href="<?= $baseUrl ?>/controller/OffreEmploiController.php?espace=front&action=liste">Offre d'emploi</a></li>
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
