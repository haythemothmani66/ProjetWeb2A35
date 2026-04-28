<div id="miniSidebar">
  <div class="brand-logo">
    <a class="d-none d-md-flex align-items-center gap-2" href="/gestion_users/view/backoffice/src/pages/backoffice/users.php">
      <img src="/gestion_users/view/backoffice/src/assets/images/brand/logo/logo-icon.svg" alt="EduMatch logo" />
      <span class="fw-bold fs-4 site-logo-text">EduMatch</span>
    </a>
  </div>

  <ul class="navbar-nav flex-column">
    <li class="nav-item">
      <div class="nav-heading">Gestion Users</div>
      <hr class="mx-5 nav-line mb-1" />
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/gestion_users/view/backoffice/src/pages/backoffice/users.php">
        <span class="nav-icon"><i class="ti ti-users"></i></span>
        <span class="text">Liste utilisateurs</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/gestion_users/view/backoffice/src/pages/backoffice/add-user.php">
        <span class="nav-icon"><i class="ti ti-user-plus"></i></span>
        <span class="text">Ajouter utilisateur</span>
      </a>
    </li>

    <li class="nav-item">
      <div class="nav-heading mt-4">Compte</div>
      <hr class="mx-5 nav-line mb-1" />
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/gestion_users/view/template/profil.php">
        <span class="nav-icon"><i class="ti ti-user-circle"></i></span>
        <span class="text">Mon Profil</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/gestion_users/view/template/index.html">
        <span class="nav-icon"><i class="ti ti-home"></i></span>
        <span class="text">FrontOffice</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link text-danger" href="/gestion_users/auth/logout">
        <span class="nav-icon"><i class="ti ti-logout"></i></span>
        <span class="text">Déconnexion</span>
      </a>
    </li>

    <li>
      <div class="text-center py-5 upgrade-ui">
        <div>
          <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($_SESSION['user_photo'] ?? 'default.png') ?>" alt="Admin avatar" class="avatar avatar-md rounded-circle" style="object-fit:cover;" />
          <div class="my-3">
            <h5 class="mb-1 fs-6"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Admin') ?></h5>
            <span class="text-secondary">Administrateur</span>
          </div>
        </div>
      </div>
    </li>
  </ul>
</div>

<div class="offcanvasNav offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
  <div class="offcanvas-header">
    <a class="d-flex align-items-center gap-2" href="/gestion_users/view/backoffice/src/pages/backoffice/users.php">
      <img src="/gestion_users/view/backoffice/src/assets/images/brand/logo/logo-icon.svg" alt="EduMatch logo" />
      <span class="fw-bold fs-4 site-logo-text">EduMatch</span>
    </a>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="navbar-nav flex-column">
      <li class="nav-item"><a class="nav-link" href="/gestion_users/view/backoffice/src/pages/backoffice/users.php"><i class="ti ti-users me-2"></i><span>Utilisateurs</span></a></li>
      <li class="nav-item"><a class="nav-link" href="/gestion_users/view/backoffice/src/pages/backoffice/add-user.php"><i class="ti ti-user-plus me-2"></i><span>Ajouter</span></a></li>
      <li class="nav-item"><a class="nav-link" href="/gestion_users/view/template/profil.php"><i class="ti ti-user-circle me-2"></i><span>Mon Profil</span></a></li>
      <li class="nav-item"><a class="nav-link text-danger" href="/gestion_users/auth/logout"><i class="ti ti-logout me-2"></i><span>Déconnexion</span></a></li>
    </ul>
  </div>
</div>
